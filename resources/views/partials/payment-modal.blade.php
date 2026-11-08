{{-- Payment modal --}}
<x-modal name="payment-modal" :show="false" focusable>
    <form wire:submit="submitPayment" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">Catat Pembayaran</h2>
        @if ($paymentOrderId)
            @php $paymentOrder = \App\Models\Order::find($paymentOrderId); @endphp
            @if ($paymentOrder)
                <p class="mt-1 text-sm text-neutral-600">№ {{ $paymentOrder->order_number }} — {{ $paymentOrder->customer->name }}</p>
            @endif
        @endif

        <div class="mt-5 space-y-4">
            <x-input
                type="number"
                wire:model.blur="paymentAmount"
                label="Jumlah bayar"
                required
                min="1"
                step="1"
                placeholder="40000"
                helptext="Maksimal sisa tagihan pesanan."
                :error="$errors->first('paymentAmount')"
            />
            <x-select
                wire:model.blur="paymentMethod"
                label="Metode pembayaran"
                required
                :options="collect(\App\Models\Payment::methods())->mapWithKeys(fn ($method) => [$method => \App\Models\Payment::methodLabel($method)])->all()"
                :error="$errors->first('paymentMethod')"
            />
            <x-textarea
                label="Catatan (opsional)"
                wire:model.blur="paymentNote"
                rows="2"
                :error="$errors->first('paymentNote')"
            />
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Simpan Pembayaran</x-button>
        </div>
    </form>
</x-modal>
