<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pesanan Batal')]
class CancelledOrders extends Component
{
    public string $search = '';

    public ?int $restoreOrderId = null;

    public function render(): View
    {
        return view('livewire.orders.cancelled-orders');
    }

    #[Computed]
    public function cancelledOrders(): Collection
    {
        return Order::query()
            ->with(['customer', 'cancelledBy'])
            ->where('status', Order::STATUS_DIBATALKAN)
            ->when(trim($this->search) !== '', function ($query) {
                $like = '%'.trim($this->search).'%';

                $query->where(function ($query) use ($like) {
                    $query->where('order_number', 'like', $like)
                        ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', $like));
                });
            })
            ->orderByDesc('cancelled_at')
            ->get();
    }

    public function startRestore(int $orderId): void
    {
        $this->restoreOrderId = $orderId;

        $this->dispatch('open-modal', 'restore-modal');
    }

    public function submitRestore(): RedirectResponse
    {
        $order = Order::findOrFail($this->restoreOrderId);

        $this->authorize('restore', $order);

        try {
            $order->restore();
        } catch (ValidationException $e) {
            session()->flash('error', $e->errors()['status'][0] ?? 'Pemulihan gagal.');

            return redirect()->route('orders.cancelled');
        }

        session()->flash('success', 'Pesanan '.$order->order_number.' dipulihkan menjadi '.Order::statusLabel($order->status).'.');

        return redirect()->route('orders.cancelled');
    }
}
