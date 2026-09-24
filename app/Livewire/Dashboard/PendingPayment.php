<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PendingPayment extends Component
{
    public ?int $paymentOrderId = null;

    public $paymentAmount = null;

    public string $paymentMethod = Payment::METHOD_TUNAI;

    public ?string $paymentNote = null;

    public ?int $refundOrderId = null;

    public $refundAmount = null;

    public ?string $refundReason = null;

    public string $refundMethod = Payment::METHOD_TUNAI;

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
        return Order::query()
            ->with(['customer', 'payments.user', 'refunds.processedBy'])
            ->whereIn('payment_status', [Order::PAYMENT_BELUM_LUNAS, Order::PAYMENT_LUNAS_SEBAGIAN])
            ->orderBy('received_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function paymentMethods(): array
    {
        return array_combine(
            Payment::methods(),
            array_map(fn (string $m) => Payment::methodLabel($m), Payment::methods()),
        );
    }

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

            return redirect()->route('dashboard');
        }

        session()->flash('success', 'Pembayaran '.Payment::methodLabel($order->payment_method).' dicatat untuk '.$order->order_number.' (sisa '.format_rupiah($order->remainingAmount).').');

        return redirect()->route('dashboard');
    }

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

            return redirect()->route('dashboard');
        }

        session()->flash('success', 'Refund '.format_rupiah($data['refundAmount']).' dicatat untuk '.$order->order_number.' ('.Payment::methodLabel($order->refunds()->latest('id')->first()?->method ?? $this->refundMethod).').');

        return redirect()->route('dashboard');
    }
}
