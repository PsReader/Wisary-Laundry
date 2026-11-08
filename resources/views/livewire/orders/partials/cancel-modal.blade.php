{{-- Cancel modal --}}
<x-modal name="cancel-modal" :show="false" focusable>
    <form wire:submit="submitCancel" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">Batalkan Pesanan</h2>
        @if ($cancelOrderId)
            @php $cancelOrder = \App\Models\Order::find($cancelOrderId); @endphp
            @if ($cancelOrder)
                <p class="mt-1 text-sm text-neutral-600">№ {{ $cancelOrder->order_number }} — {{ $cancelOrder->customer->name }}</p>
            @endif
        @endif
        <p class="mt-3 rounded-lg bg-danger-50 px-3 py-2 text-sm text-danger-700">
            Pesanan akan berhenti diproses dan hilang dari daftar pesanan aktif. Riwayat tetap tersimpan, dan Super Admin dapat memulihkannya.
        </p>

        <div class="mt-5">
            <x-textarea
                label="Alasan pembatalan"
                required
                wire:model.blur="cancellationReason"
                rows="3"
                placeholder="Contoh: kualitas hasil kurang memuaskan"
                :error="$errors->first('cancellationReason')"
            />
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" variant="destructive" class="sm:min-w-32" wire:loading.attr="disabled">Batalkan Pesanan</x-button>
        </div>
    </form>
</x-modal>
