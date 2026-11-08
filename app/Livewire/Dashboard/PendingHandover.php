<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\HandoverActions;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PendingHandover extends Component
{
    use HandoverActions;

    public function render(): View
    {
        return view('livewire.dashboard.pending-handover');
    }

    /**
     * Pesanan yang sudah siap namun belum diserahterimakan, paling
     * mendesak (estimasi tercepat) tampil lebih dulu.
     */
    #[Computed]
    public function pendingOrders(): Collection
    {
        return Order::query()
            ->with('customer')
            ->where('status', Order::STATUS_SIAP_DIAMBIL)
            ->where('handover_status', Order::HANDOVER_BELUM_DISERAHKAN)
            ->orderByRaw('estimated_completion_at IS NULL, estimated_completion_at ASC')
            ->orderBy('received_at')
            ->limit(3)
            ->get();
    }

    protected function handoverRedirect(): string
    {
        return route('dashboard');
    }
}
