{{-- Price adjustment modal (Super Admin) --}}
<x-modal name="adjust-price-modal" :show="false" focusable>
    <form wire:submit="submitAdjustPrice" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">Sesuaikan Harga Final</h2>
        @if ($adjustPriceOrderId)
            @php $adjustOrder = \App\Models\Order::find($adjustPriceOrderId); @endphp
            @if ($adjustOrder)
                <p class="mt-1 text-sm text-neutral-600">№ {{ $adjustOrder->order_number }} — {{ $adjustOrder->customer->name }}</p>
                <p class="mt-2 text-sm text-neutral-500">Harga saat ini: <span class="font-semibold text-neutral-700">{{ format_rupiah((float) $adjustOrder->total_price) }}</span></p>
            @endif
        @endif
        <p class="mt-2 rounded-lg bg-warning-100 px-3 py-2 text-xs text-warning-700">
            Harga baru akan menggantikan total pesanan. Alasan perubahan akan tercatat dalam riwayat.
        </p>

        <div class="mt-5 space-y-4">
            <x-input
                type="number"
                wire:model.blur="adjustPriceNewAmount"
                label="Harga baru (Rp)"
                required
                min="0"
                step="1"
                placeholder="40000"
                :error="$errors->first('adjustPriceNewAmount')"
            />
            <x-textarea
                label="Alasan perubahan"
                required
                wire:model.blur="adjustPriceReason"
                rows="3"
                placeholder="Contoh: koreksi harga karena promo"
                :error="$errors->first('adjustPriceReason')"
            />
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Sesuaikan Harga</x-button>
        </div>
    </form>
</x-modal>
