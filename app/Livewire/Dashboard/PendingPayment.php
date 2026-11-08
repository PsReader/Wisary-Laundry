<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\PaymentActions;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PendingPayment extends Component
{
    use PaymentActions;

    public function render(): View
    {
        return view('livewire.dashboard.pending-payment');
    }

    /**
     * Pesanan yang belum lunas kepenuhnya. Uang dapat ditarik kembali
     * (refund) sehingga order refund-penuh tidak ditampilkan.
     */
    #[Computed]
    public function unpaidOrders(): Collection
    {
        return Order::unpaid()
            ->with(['customer', 'payments.user', 'refunds.processedBy'])
            ->orderBy('received_at')
            ->limit(3)
            ->get();
    }

    protected function paymentRedirect(Order $order): string
    {
        return route('dashboard');
    }
}
