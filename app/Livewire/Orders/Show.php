<?php

namespace App\Livewire\Orders;

use App\Livewire\Concerns\HandoverActions;
use App\Livewire\Concerns\PaymentActions;
use App\Models\Order;
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
    use PaymentActions;

    public int $orderId;

    // Cancel
    public ?int $cancelOrderId = null;

    public ?string $cancellationReason = null;

    // Restore
    public ?string $restoreNote = null;

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
        // Dynamic title (order number); the layout renders this as the page <h1>.
        return view('livewire.orders.show')
            ->title('Pesanan '.$this->order->order_number);
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

    // ── Restore ────────────────────────────────────────────────────

    public function openRestore(): void
    {
        $this->restoreNote = null;
        $this->resetValidation(['restoreNote']);

        $this->dispatch('open-modal', 'restore-modal');
    }

    public function submitRestore(): RedirectResponse
    {
        $order = $this->order;

        $this->authorize('restore', $order);

        try {
            $order->restore(auth()->user());
        } catch (ValidationException $e) {
            $message = $e->errors()['status'][0] ?? 'Pemulihan pesanan gagal.';

            session()->flash('error', $message);

            return redirect()->route('orders.show', $order);
        }

        session()->flash('success', 'Pesanan '.$order->order_number.' dipulihkan.');

        return redirect()->route('orders.show', $order);
    }

    // ── Handover ───────────────────────────────────────────────────

    protected function handoverRedirect(): string
    {
        return route('orders.show', $this->orderId);
    }

    // ── Payment ────────────────────────────────────────────────────

    protected function paymentRedirect(Order $order): string
    {
        return route('orders.show', $order);
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
