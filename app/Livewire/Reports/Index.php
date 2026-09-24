<?php

namespace App\Livewire\Reports;

use App\Models\AuditLog;
use App\Models\LoginActivity;
use App\Models\Order;
use App\Models\OrderHandoverHistory;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Index extends Component
{
    public string $from = '';

    public string $to = '';

    public string $error = '';

    public function mount(): void
    {
        if ($this->from === '' || $this->to === '') {
            $this->setPeriod('month');
        }
    }

    protected function queryString(): array
    {
        return ['from' => ['except' => ''], 'to' => ['except' => '']];
    }

    public function setPeriod(string $preset): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $this->error = '';

        match ($preset) {
            'today' => [$this->from, $this->to] = [$now->toDateString(), $now->toDateString()],
            'week' => [$this->from, $this->to] = [$now->copy()->subDays(6)->toDateString(), $now->toDateString()],
            'month' => [$this->from, $this->to] = [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'last_month' => [$this->from, $this->to] = [$now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            default => null,
        };
    }

    public function updatedFrom(): void
    {
        $this->reconcile();
    }

    public function updatedTo(): void
    {
        $this->reconcile();
    }

    protected function reconcile(): void
    {
        $this->error = '';

        if ($this->from !== '' && $this->to !== '' && $this->from > $this->to) {
            $this->error = 'Tanggal awal tidak boleh melebihi tanggal akhir.';
        }
    }

    /**
     * Rentang laporan inklusif dalam waktu Asia/Jakarta. Null jika filter
     * tidak valid.
     *
     * @return array{Carbon, Carbon}|null
     */
    public function range(): ?array
    {
        $this->reconcile();

        if ($this->from === '' || $this->to === '' || $this->error !== '') {
            return null;
        }

        return [
            Carbon::parse($this->from)->setTimezone('Asia/Jakarta')->startOfDay(),
            Carbon::parse($this->to)->setTimezone('Asia/Jakarta')->endOfDay(),
        ];
    }

    public function render(): View
    {
        $range = $this->range();

        return view('livewire.reports.index', [
            'range' => $range,
            'data' => $range === null ? null : $this->dataset($range[0], $range[1]),
        ]);
    }

    /**
     * Menghitung seluruh dataset laporan untuk rentang periode tertentu.
     */
    protected function dataset(Carbon $from, Carbon $to): array
    {
        $orders = Order::query()
            ->whereBetween('received_at', [$from, $to])
            ->with(['customer', 'refunds'])
            ->get();

        $orderIds = $orders->pluck('id');

        // 1. Jumlah pesanan per periode.
        $ordersByStatus = $orders->countBy('status')
            ->sortKeysDesc()
            ->mapWithKeys(fn (int $count, string $status) => [Order::statusLabel($status) => $count]);

        $ordersByDay = $orders->groupBy(fn (Order $order) => $order->received_at?->toDateString())
            ->map->count()
            ->sortKeys();

        // 2. Pendapatan (dari pembayaran yang diterima).
        $payments = Payment::query()->whereBetween('received_at', [$from, $to])->get();

        $revenueByDay = $payments->groupBy(fn (Payment $payment) => $payment->received_at?->toDateString())
            ->map(fn ($group) => (float) $group->sum('amount'))
            ->sortKeys();

        $revenueByWeek = $revenueByDay
            ->mapToGroups(fn (float $amount, string $day) => [Carbon::parse($day)->startOfWeek()->toDateString() => $amount])
            ->map(fn ($group) => (float) $group->sum())
            ->sortKeys();

        $revenueByMonth = $revenueByDay
            ->mapToGroups(fn (float $amount, string $day) => [Carbon::parse($day)->format('Y-m') => $amount])
            ->map(fn ($group) => (float) $group->sum())
            ->sortKeys();

        $revenueTotal = (float) $payments->sum('amount');

        // 3. Layanan paling sering digunakan.
        $topServices = OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->get()
            ->groupBy('service_name')
            ->map(fn ($group) => [
                'name' => (string) $group->first()->service_name,
                'order_count' => $group->pluck('order_id')->unique()->count(),
                'quantity' => $group->sum('quantity'),
                'weight' => (float) $group->sum('billed_weight'),
                'subtotal' => (float) $group->sum('subtotal'),
            ])
            ->sortByDesc('subtotal')
            ->take(10)
            ->values();

        // 4. Pesanan belum lunas.
        $unpaidOrders = $orders
            ->whereIn('payment_status', [Order::PAYMENT_BELUM_LUNAS, Order::PAYMENT_LUNAS_SEBAGIAN])
            ->sortByDesc(fn (Order $order) => $order->remainingAmount)
            ->values();

        // 5. Pickup dan delivery serta aksi serah terima.
        $ordersByMethod = [
            'diambil' => $orders->where('method', Order::METHOD_DIAMBIL)->count(),
            'diantar' => $orders->where('method', Order::METHOD_DIANTAR)->count(),
        ];

        $handovers = OrderHandoverHistory::query()
            ->whereBetween('occurred_at', [$from, $to])
            ->with(['order.customer', 'recordedBy'])
            ->orderBy('occurred_at')
            ->get();

        $handoverByAction = $handovers->countBy('handover_action');

        // 6. Pesanan yang sudah diserahterimakan.
        $handedOverOrders = $orders
            ->where('handover_status', '!=', Order::HANDOVER_BELUM_DISERAHKAN)
            ->values();

        // 7. Pesanan dibatalkan (menurut waktu pembatalan).
        $cancelledOrders = Order::query()
            ->whereBetween('cancelled_at', [$from, $to])
            ->with(['customer', 'cancelledBy'])
            ->orderBy('cancelled_at')
            ->get();

        // 8. Delivery gagal.
        $failedDeliveries = $handovers
            ->where('handover_action', OrderHandoverHistory::ACTION_GAGAL)
            ->values();

        // 9. Riwayat refund.
        $refunds = Refund::query()
            ->whereBetween('refunded_at', [$from, $to])
            ->with(['order.customer', 'processedBy'])
            ->orderBy('refunded_at')
            ->get();

        // 10. Aktivitas staf.
        $staffActivity = $this->staffActivity($from, $to);

        return compact(
            'ordersByStatus',
            'ordersByDay',
            'revenueByDay',
            'revenueByWeek',
            'revenueByMonth',
            'revenueTotal',
            'topServices',
            'unpaidOrders',
            'ordersByMethod',
            'handovers',
            'handoverByAction',
            'handedOverOrders',
            'cancelledOrders',
            'failedDeliveries',
            'refunds',
            'staffActivity',
        );
    }

    /**
     * Ledger aktivitas per staf dalam periode.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function staffActivity(Carbon $from, Carbon $to): array
    {
        $users = User::query()
            ->whereIn('role', [User::ROLE_STAFF, User::ROLE_SUPER_ADMIN])
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $ordersCreated = Order::query()
            ->whereBetween('received_at', [$from, $to])
            ->pluck('created_by')
            ->countBy();

        $statusChanges = OrderStatusHistory::query()
            ->whereBetween('created_at', [$from, $to])
            ->pluck('user_id')
            ->countBy();

        $handoverChanges = OrderHandoverHistory::query()
            ->whereBetween('occurred_at', [$from, $to])
            ->pluck('recorded_by')
            ->countBy();

        $payments = Payment::query()
            ->whereBetween('received_at', [$from, $to])
            ->get(['user_id', 'amount'])
            ->groupBy('user_id');

        $refunds = Refund::query()
            ->whereBetween('refunded_at', [$from, $to])
            ->get(['processed_by', 'amount'])
            ->groupBy('processed_by');

        $audits = AuditLog::query()
            ->whereIn('action', ['order.cancelled', 'order.restored'])
            ->whereBetween('created_at', [$from, $to])
            ->pluck('user_id')
            ->countBy();

        $logins = LoginActivity::query()
            ->where('type', LoginActivity::TYPE_LOGIN)
            ->whereBetween('created_at', [$from, $to])
            ->pluck('user_id')
            ->countBy();

        $result = [];

        foreach ($users as $user) {
            $paymentGroup = $payments[$user->id] ?? collect();
            $refundGroup = $refunds[$user->id] ?? collect();

            $result[] = [
                'user' => $user,
                'orders_created' => $ordersCreated[$user->id] ?? 0,
                'status_changes' => $statusChanges[$user->id] ?? 0,
                'handovers' => $handoverChanges[$user->id] ?? 0,
                'payments_count' => $paymentGroup->count(),
                'payments_total' => (float) $paymentGroup->sum('amount'),
                'refunds_count' => $refundGroup->count(),
                'refunds_total' => (float) $refundGroup->sum('amount'),
                'cancellations' => $audits[$user->id] ?? 0,
                'logins' => $logins[$user->id] ?? 0,
            ];
        }

        return $result;
    }
}
