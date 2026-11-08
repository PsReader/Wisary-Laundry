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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Laporan')]
class Index extends Component
{
    use WithPagination;

    public string $from = '';

    public string $to = '';

    public string $error = '';

    public string $tab = 'keuangan';

    public function mount(): void
    {
        if ($this->from === '' || $this->to === '') {
            $this->setPeriod('month');
        }
    }

    public function updatedTab(): void
    {
        $this->resetPage();
    }

    public function updatedFrom(): void
    {
        $this->resetPage();
        $this->validateDateRange();
    }

    public function updatedTo(): void
    {
        $this->resetPage();
        $this->validateDateRange();
    }

    protected function queryString(): array
    {
        return ['from' => ['except' => ''], 'to' => ['except' => ''], 'tab' => ['except' => 'keuangan'], 'page' => ['except' => 1]];
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    #[Computed]
    public function activePeriod(): ?string
    {
        $now = Carbon::now('Asia/Jakarta');

        foreach (['today', 'week', 'month', 'last_month'] as $preset) {
            if ([$this->from, $this->to] === $this->presetDates($preset, $now)) {
                return $preset;
            }
        }

        return null;
    }

    public function setPeriod(string $preset): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $this->error = '';
        $dates = $this->presetDates($preset, $now);

        if ($dates !== null) {
            [$this->from, $this->to] = $dates;
        }
    }

    /**
     * @return array{string, string}|null
     */
    private function presetDates(string $preset, Carbon $now): ?array
    {
        return match ($preset) {
            'today' => [$now->toDateString(), $now->toDateString()],
            'week' => [$now->copy()->subDays(6)->toDateString(), $now->toDateString()],
            'month' => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'last_month' => [
                $now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
            default => null,
        };
    }

    /**
     * @param  Collection<int, mixed>|array<int, mixed>  $collection
     */
    private function paginateCollection(Collection|array $collection, int $perPage = 10): LengthAwarePaginator
    {
        if (is_array($collection)) {
            $collection = collect($collection);
        }

        $page = $this->getPage();
        $items = $collection->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    protected function validateDateRange(): void
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
    public function reportRange(): ?array
    {
        $this->validateDateRange();

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
        $range = $this->reportRange();

        $data = $range === null ? null : $this->buildReportDataset($range[0], $range[1]);

        if ($data !== null) {
            $data['unpaidOrdersPaginated'] = $this->paginateCollection($data['unpaidOrders']);
            $data['cancelledOrdersPaginated'] = $this->paginateCollection($data['cancelledOrders']);
            $data['refundsPaginated'] = $this->paginateCollection($data['refunds']);
            $data['handedOverOrdersPaginated'] = $this->paginateCollection($data['handedOverOrders']);
            $data['failedDeliveriesPaginated'] = $this->paginateCollection($data['failedDeliveries']);
            $data['staffActivityPaginated'] = $this->paginateCollection($data['staffActivity']);
        }

        return view('livewire.reports.index', [
            'range' => $range,
            'data' => $data,
        ]);
    }

    /**
     * Menghitung seluruh dataset laporan untuk rentang periode tertentu.
     */
    protected function buildReportDataset(Carbon $from, Carbon $to): array
    {
        $receivedOrders = Order::query()
            ->whereBetween('received_at', [$from, $to])
            ->with(['customer', 'refunds'])
            ->get();

        $receivedOrderIds = $receivedOrders->pluck('id');

        // 1. Jumlah pesanan per periode.
        $ordersByStatus = $receivedOrders->countBy('status')
            ->sortKeysDesc()
            ->mapWithKeys(fn (int $count, string $status) => [Order::statusLabel($status) => $count]);

        $ordersByDay = $receivedOrders->groupBy(fn (Order $order) => $order->received_at?->toDateString())
            ->map->count()
            ->sortKeys();

        // 2. Pendapatan (dari pembayaran yang diterima).
        $receivedPayments = Payment::query()->whereBetween('received_at', [$from, $to])->get();

        $revenueByDay = $receivedPayments->groupBy(fn (Payment $payment) => $payment->received_at?->toDateString())
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

        $revenueTotal = (float) $receivedPayments->sum('amount');

        // 3. Layanan paling sering digunakan.
        $topServices = OrderItem::query()
            ->whereIn('order_id', $receivedOrderIds)
            ->get()
            ->groupBy('service_name')
            ->map(fn ($group) => [
                'name' => (string) $group->first()->service_name,
                'order_count' => $group->pluck('order_id')->unique()->count(),
                'quantity' => $group->sum('quantity'),
                'weight' => (float) $group->sum('billed_weight'),
                'subtotal' => (float) $group->sum('subtotal'),
            ])
            ->sortByDesc('order_count')
            ->take(5)
            ->values();

        // 4. Pesanan belum lunas.
        $unpaidOrders = $receivedOrders
            ->whereIn('payment_status', Order::unpaidPaymentStatuses())
            ->sortByDesc(fn (Order $order) => $order->remainingAmount)
            ->values();

        // 5. Pickup dan delivery serta aksi serah terima.
        $ordersByMethod = [
            'diambil' => $receivedOrders->where('method', Order::METHOD_DIAMBIL)->count(),
            'diantar' => $receivedOrders->where('method', Order::METHOD_DIANTAR)->count(),
        ];

        $handoverEvents = OrderHandoverHistory::query()
            ->whereBetween('occurred_at', [$from, $to])
            ->with(['order.customer', 'recordedBy'])
            ->orderBy('occurred_at')
            ->get();

        // 6. Pesanan yang sudah diserahterimakan.
        $handedOverOrders = $receivedOrders
            ->where('handover_status', '!=', Order::HANDOVER_BELUM_DISERAHKAN)
            ->values();

        // 7. Pesanan dibatalkan (menurut waktu pembatalan).
        $cancelledOrders = Order::query()
            ->whereBetween('cancelled_at', [$from, $to])
            ->with(['customer', 'cancelledBy'])
            ->orderBy('cancelled_at')
            ->get();

        // 8. Delivery gagal.
        $failedDeliveries = $handoverEvents
            ->where('handover_action', OrderHandoverHistory::ACTION_GAGAL)
            ->values();

        // 9. Riwayat refund.
        $refunds = Refund::query()
            ->whereBetween('refunded_at', [$from, $to])
            ->with(['order.customer', 'processedBy'])
            ->orderBy('refunded_at')
            ->get();

        // 10. Aktivitas staf.
        $staffActivity = $this->buildStaffActivitySummary($from, $to);

        return [
            'ordersByStatus' => $ordersByStatus,
            'ordersByDay' => $ordersByDay,
            'revenueByDay' => $revenueByDay,
            'revenueByWeek' => $revenueByWeek,
            'revenueByMonth' => $revenueByMonth,
            'revenueTotal' => $revenueTotal,
            'topServices' => $topServices,
            'unpaidOrders' => $unpaidOrders,
            'ordersByMethod' => $ordersByMethod,
            'handovers' => $handoverEvents,
            'handedOverOrders' => $handedOverOrders,
            'cancelledOrders' => $cancelledOrders,
            'failedDeliveries' => $failedDeliveries,
            'refunds' => $refunds,
            'staffActivity' => $staffActivity,
        ];
    }

    /**
     * Ledger aktivitas per staf dalam periode.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildStaffActivitySummary(Carbon $from, Carbon $to): array
    {
        $staffAndAdmins = User::query()
            ->whereIn('role', [User::ROLE_STAFF, User::ROLE_SUPER_ADMIN])
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $ordersCreatedByUser = Order::query()
            ->whereBetween('received_at', [$from, $to])
            ->pluck('created_by')
            ->countBy();

        $statusChangesByUser = OrderStatusHistory::query()
            ->whereBetween('created_at', [$from, $to])
            ->pluck('user_id')
            ->countBy();

        $handoversByUser = OrderHandoverHistory::query()
            ->whereBetween('occurred_at', [$from, $to])
            ->pluck('recorded_by')
            ->countBy();

        $paymentsByUser = Payment::query()
            ->whereBetween('received_at', [$from, $to])
            ->get(['user_id', 'amount'])
            ->groupBy('user_id');

        $refundsByUser = Refund::query()
            ->whereBetween('refunded_at', [$from, $to])
            ->get(['processed_by', 'amount'])
            ->groupBy('processed_by');

        $cancellationsByUser = AuditLog::query()
            ->whereIn('action', ['order.cancelled', 'order.restored'])
            ->whereBetween('created_at', [$from, $to])
            ->pluck('user_id')
            ->countBy();

        $successfulLoginsByUser = LoginActivity::query()
            ->where('type', LoginActivity::TYPE_LOGIN)
            ->whereBetween('created_at', [$from, $to])
            ->pluck('user_id')
            ->countBy();

        $staffSummaries = [];

        foreach ($staffAndAdmins as $user) {
            $userPayments = $paymentsByUser[$user->id] ?? collect();
            $userRefunds = $refundsByUser[$user->id] ?? collect();

            $staffSummaries[] = [
                'user' => $user,
                'orders_created' => $ordersCreatedByUser[$user->id] ?? 0,
                'status_changes' => $statusChangesByUser[$user->id] ?? 0,
                'handovers' => $handoversByUser[$user->id] ?? 0,
                'payments_count' => $userPayments->count(),
                'payments_total' => (float) $userPayments->sum('amount'),
                'refunds_count' => $userRefunds->count(),
                'refunds_total' => (float) $userRefunds->sum('amount'),
                'cancellations' => $cancellationsByUser[$user->id] ?? 0,
                'logins' => $successfulLoginsByUser[$user->id] ?? 0,
            ];
        }

        return $staffSummaries;
    }
}
