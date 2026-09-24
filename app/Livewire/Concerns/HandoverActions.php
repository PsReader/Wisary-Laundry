<?php

namespace App\Livewire\Concerns;

use App\Models\Order;
use App\Models\OrderHandoverHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

trait HandoverActions
{
    public ?int $handoverOrderId = null;

    public string $handoverAction = '';

    public ?string $handoverNote = null;

    public ?string $handoverFailureReason = null;

    public ?string $handoverAddress = null;

    public function startHandover(int $orderId, string $action): void
    {
        $this->handoverOrderId = $orderId;
        $this->handoverAction = $action;
        $this->handoverNote = null;
        $this->handoverFailureReason = null;
        $this->handoverAddress = null;
        $this->resetValidation(['handoverAction', 'handoverNote', 'handoverFailureReason', 'handoverAddress']);

        $this->dispatch('open-modal', 'handover-modal');
    }

    public function submitHandover(): RedirectResponse
    {
        $order = Order::findOrFail($this->handoverOrderId);

        $data = $this->validate($this->handoverRules($order, $this->handoverAction));

        try {
            $order->recordHandover(
                action: $this->handoverAction,
                note: $data['handoverNote'] ?? null,
                failureReason: $data['handoverFailureReason'] ?? null,
                deliveryAddress: $data['handoverAddress'] ?? null,
            );
        } catch (ValidationException $e) {
            $message = $e->errors()['handover_action'][0]
                ?? $e->errors()['failure_reason'][0]
                ?? $e->errors()['delivery_address'][0]
                ?? 'Serah terima gagal dicatat.';

            session()->flash('error', $message);

            return redirect()->to($this->handoverRedirect());
        }

        if ($this->handoverAction === OrderHandoverHistory::ACTION_GAGAL) {
            session()->flash('success', 'Delivery gagal dicatat ('.Order::methodLabel($order->method).').');

            return redirect()->to($this->handoverRedirect());
        }

        session()->flash('success', 'Pesanan '.$order->order_number.' ditandai '.Order::handoverLabel($order->handover_status).' ('.Order::methodLabel($order->method).').');

        return redirect()->to($this->handoverRedirect());
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function handoverRules(Order $order, string $action): array
    {
        $rules = [
            'handoverAction' => ['required', 'in:'.OrderHandoverHistory::ACTION_SUDAH_DIAMBIL.','.OrderHandoverHistory::ACTION_SUDAH_DIANTAR.','.OrderHandoverHistory::ACTION_GAGAL],
            'handoverNote' => ['nullable', 'string', 'max:2000'],
            'handoverFailureReason' => ['nullable', 'string', 'max:2000'],
            'handoverAddress' => ['nullable', 'string', 'max:1000'],
        ];

        if ($action === OrderHandoverHistory::ACTION_GAGAL) {
            $rules['handoverFailureReason'] = ['required', 'string', 'max:2000'];
        }

        if ($action === OrderHandoverHistory::ACTION_SUDAH_DIANTAR && trim((string) $order->delivery_address) === '') {
            $rules['handoverAddress'] = ['required', 'string', 'max:1000'];
        }

        return $rules;
    }

    abstract protected function handoverRedirect(): string;
}
