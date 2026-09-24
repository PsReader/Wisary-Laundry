<?php

namespace App\Livewire\Orders;

use App\Livewire\Concerns\HandoverActions;
use App\Models\Order;
use App\Models\Payment;
use App\Support\Audit;
use App\Support\StatusFlow;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use HandoverActions;

    public int $orderId;

    // Cancel
    public ?int $cancelOrderId = null;

    public ?string $cancellationReason = null;

    // Payment
    public ?int $paymentOrderId = null;

    public $paymentAmount = null;

    public string $paymentMethod = Payment::METHOD_TUNAI;

    public ?string $paymentNote = null;

    // Refund
    public ?int $refundOrderId = null;

    public $refundAmount = null;

    public ?string $refundReason = null;

    public string $refundMethod = Payment::METHOD_TUNAI;

    // Price adjustment
    public ?int $adjustPriceOrderId = null;

    public string $adjustPriceNewAmount = '';

    public ?string $adjustPriceReason = null;

    // Estimate override
    public ?int $overrideEstimateOrderId = null;

    public string $overrideEstimateDate = '';

    public function mount(Order $order): void
    {
        $this->orderId = $order->getKey();
    }

    public function render(): View
    {
        return view('livewire.orders.show');
    }

    #[Computed]
    public function order(): Order
    {
        return Order::query()
            ->with([
                'customer',
                'createdBy',
                'cancelledBy',
                'items.service',
                'payments.user',
                'refunds.processedBy',
                'statusHistories.user',
                'handoverHistories.recordedBy',
                'priceAdjustments.user',
            ])
            ->findOrFail($this->orderId);
    }

    #[Computed]
    public function flowButtons(): array
    {
        $flow = StatusFlow::nextStatuses((string) $this->order->status);

        if (! $this->orderHasIroning()) {
            $flow = array_values(array_diff($flow, [Order::STATUS_SEDANG_DISETRIKA]));
        }

        if (! $this->orderHasFolding()) {
            $flow = array_values(array_diff($flow, [Order::STATUS_SEDANG_DILIPAT]));
        }

        return $flow;
    }

    private function orderHasIroning(): bool
    {
        return $this->order->items->contains(function ($item) {
            return str_contains(strtolower((string) $item->service_name), 'setrika')
                || $item->service?->category === 'Barang khusus';
        });
    }

    private function orderHasFolding(): bool
    {
        return $this->order->items->contains(function ($item) {
            return str_contains(strtolower((string) $item->service_name), 'lipat');
        });
    }

    // ── Status Transition ──────────────────────────────────────────

    public function changeStatus(int $orderId, string $to): RedirectResponse
    {
        $order = Order::findOrFail($orderId);

        $this->authorize('update', $order);

        try {
            $order->changeStatus($to);
        } catch (ValidationException $e) {
            session()->flash('error', $e->errors()['status'][0] ?? 'Transisi status tidak diizinkan.');

            return redirect()->route('orders.show', $order);
        }

        session()->flash('success', 'Pesanan '.$order->order_number.' menjadi '.Order::statusLabel($order->status).'.');

        return redirect()->route('orders.show', $order);
    }

    // ── Cancel ─────────────────────────────────────────────────────

    public function startCancel(int $orderId): void
    {
        $this->cancelOrderId = $orderId;
        $this->cancellationReason = null;
        $this->resetValidation(['cancellationReason']);

        $this->dispatch('open-modal', 'cancel-modal');
    }

    public function submitCancel(): RedirectResponse
    {
        $order = Order::findOrFail($this->cancelOrderId);

        $this->authorize('cancel', $order);

        $data = $this->validate([
            'cancellationReason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $order->cancel($data['cancellationReason']);
        } catch (ValidationException $e) {
            $message = $e->errors()['cancellation_reason'][0]
                ?? $e->errors()['status'][0]
                ?? 'Pembatalan gagal.';

            session()->flash('error', $message);

            return redirect()->route('orders.show', $order);
        }

        session()->flash('success', 'Pesanan '.$order->order_number.' dibatalkan.');

        return redirect()->route('orders.show', $order);
    }

    // ── Handover ───────────────────────────────────────────────────

    protected function handoverRedirect(): string
    {
        return route('orders.show', $this->orderId);
    }

    // ── Payment ────────────────────────────────────────────────────

    public function startPayment(int $orderId): void
    {
        $order = Order::findOrFail($orderId);

        $this->paymentOrderId = $order->id;
        $this->paymentAmount = $order->remainingAmount > 0 ? (string) round($order->remainingAmount) : '';
        $this->paymentMethod = Payment::METHOD_TUNAI;
        $this->paymentNote = null;
        $this->resetValidation(['paymentOrderId', 'paymentAmount', 'paymentMethod', 'paymentNote']);

        $this->dispatch('open-modal', 'payment-modal');
    }

    public function submitPayment(): RedirectResponse
    {
        $order = Order::findOrFail($this->paymentOrderId);

        $this->authorize('update', $order);

        $max = (int) round($order->remainingAmount);

        $data = $this->validate([
            'paymentOrderId' => ['required', 'integer'],
            'paymentAmount' => ['required', 'numeric', 'gt:0', 'max:'.max($max, 1)],
            'paymentMethod' => ['required', 'in:'.implode(',', Payment::methods())],
            'paymentNote' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $order->recordPayment(
                amount: $data['paymentAmount'],
                paymentMethod: $data['paymentMethod'],
                note: $data['paymentNote'] ?? null,
            );
        } catch (ValidationException $e) {
            $message = $e->errors()['amount'][0]
                ?? $e->errors()['payment_method'][0]
                ?? 'Pembayaran gagal dicatat.';

            session()->flash('error', $message);

            return redirect()->route('orders.show', $order);
        }

        session()->flash('success', 'Pembayaran '.Payment::methodLabel($order->payment_method).' dicatat untuk '.$order->order_number.' (sisa '.format_rupiah($order->remainingAmount).').');

        return redirect()->route('orders.show', $order);
    }

    // ── Refund ─────────────────────────────────────────────────────

    public function startRefund(int $orderId): void
    {
        $order = Order::findOrFail($orderId);

        $this->refundOrderId = $order->id;
        $this->refundAmount = (string) round(($order->paidInCents() - $order->refundedInCents()) / 100);
        $this->refundReason = null;
        $this->refundMethod = Payment::METHOD_TUNAI;
        $this->resetValidation(['refundOrderId', 'refundAmount', 'refundReason', 'refundMethod']);

        $this->dispatch('open-modal', 'refund-modal');
    }

    public function submitRefund(): RedirectResponse
    {
        $order = Order::findOrFail($this->refundOrderId);

        $this->authorize('update', $order);

        $max = (int) round(($order->paidInCents() - $order->refundedInCents()) / 100);

        $data = $this->validate([
            'refundOrderId' => ['required', 'integer'],
            'refundAmount' => ['required', 'numeric', 'gt:0', 'max:'.max($max, 1)],
            'refundReason' => ['required', 'string', 'max:2000'],
            'refundMethod' => ['required', 'in:'.implode(',', Payment::methods())],
        ]);

        try {
            $order->recordRefund(
                amount: $data['refundAmount'],
                reason: $data['refundReason'],
                method: $data['refundMethod'],
            );
        } catch (ValidationException $e) {
            $message = $e->errors()['amount'][0]
                ?? $e->errors()['reason'][0]
                ?? $e->errors()['method'][0]
                ?? 'Refund gagal dicatat.';

            session()->flash('error', $message);

            return redirect()->route('orders.show', $order);
        }

        session()->flash('success', 'Refund '.format_rupiah($data['refundAmount']).' dicatat untuk '.$order->order_number.'.');

        return redirect()->route('orders.show', $order);
    }

    // ── Price Adjustment (Super Admin) ─────────────────────────────

    public function startAdjustPrice(int $orderId): void
    {
        $order = Order::findOrFail($orderId);

        $this->authorize('adjustPrice', $order);

        $this->adjustPriceOrderId = $order->id;
        $this->adjustPriceNewAmount = (string) (int) round((float) $order->total_price);
        $this->adjustPriceReason = null;
        $this->resetValidation(['adjustPriceNewAmount', 'adjustPriceReason']);

        $this->dispatch('open-modal', 'adjust-price-modal');
    }

    public function submitAdjustPrice(): RedirectResponse
    {
        $order = Order::findOrFail($this->adjustPriceOrderId);

        $this->authorize('adjustPrice', $order);

        $data = $this->validate([
            'adjustPriceNewAmount' => ['required', 'integer', 'min:0', 'max:100000000'],
            'adjustPriceReason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $order->adjustFinalPrice(
                newAmount: (int) $data['adjustPriceNewAmount'],
                reason: $data['adjustPriceReason'],
            );
        } catch (ValidationException $e) {
            $message = $e->errors()['reason'][0] ?? 'Penyesuaian harga gagal.';

            session()->flash('error', $message);

            return redirect()->route('orders.show', $order);
        }

        session()->flash('success', 'Harga final '.$order->order_number.' diubah menjadi '.format_rupiah($order->total_price).'. ');

        return redirect()->route('orders.show', $order);
    }

    // ── Estimate Override (Super Admin) ────────────────────────────

    public function startOverrideEstimate(int $orderId): void
    {
        $order = Order::findOrFail($orderId);

        $this->authorize('overrideEstimate', $order);

        $this->overrideEstimateOrderId = $order->id;
        $this->overrideEstimateDate = $order->estimated_completion_at
            ? $order->estimated_completion_at->format('Y-m-d')
            : now()->format('Y-m-d');
        $this->resetValidation(['overrideEstimateDate']);

        $this->dispatch('open-modal', 'override-estimate-modal');
    }

    public function submitOverrideEstimate(): RedirectResponse
    {
        $order = Order::findOrFail($this->overrideEstimateOrderId);

        $this->authorize('overrideEstimate', $order);

        $data = $this->validate([
            'overrideEstimateDate' => ['required', 'date'],
        ]);

        $before = $order->estimated_completion_at?->toDateString();
        $after = Carbon::parse($data['overrideEstimateDate'])->endOfDay();

        $order->update([
            'estimated_completion_at' => $after,
        ]);

        Audit::record(auth()->user(), 'order.estimate_overridden', $order, [
            'estimated_completion_at' => $before,
        ], [
            'estimated_completion_at' => $after->toDateString(),
        ]);

        session()->flash('success', 'Estimasi selesai '.$order->order_number.' diperbarui.');

        return redirect()->route('orders.show', $order);
    }
}
