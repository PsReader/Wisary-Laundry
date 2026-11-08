{{-- Refund modal --}}
<x-modal name="refund-modal" :show="false" focusable>
    <form wire:submit="submitRefund" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">Kirim Refund</h2>
        @if ($refundOrderId)
            @php $refundOrder = \App\Models\Order::find($refundOrderId); @endphp
            @if ($refundOrder)
                <p class="mt-1 text-sm text-neutral-600">№ {{ $refundOrder->order_number }} — {{ $refundOrder->customer->name }}</p>
            @endif
        @endif

        <div class="mt-5 space-y-4">
            <x-input
                type="number"
                wire:model.blur="refundAmount"
                label="Jumlah refund"
                required
                min="1"
                step="1"
                placeholder="40000"
                helptext="Maksimal total yang sudah dibayar."
                :error="$errors->first('refundAmount')"
            />
            <x-textarea
                label="Alasan refund"
                required
                wire:model.blur="refundReason"
                rows="3"
                placeholder="Contoh: kelebihan bayar, pembatalan layanan"
                :error="$errors->first('refundReason')"
            />
            <x-select
                wire:model.blur="refundMethod"
                label="Metode refund"
                required
                :options="collect(\App\Models\Payment::methods())->mapWithKeys(fn ($method) => [$method => \App\Models\Payment::methodLabel($method)])->all()"
                :error="$errors->first('refundMethod')"
            />
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Simpan Refund</x-button>
        </div>
    </form>
</x-modal>
