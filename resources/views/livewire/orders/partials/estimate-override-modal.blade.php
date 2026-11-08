{{-- Estimate override modal (Super Admin) --}}
<x-modal name="override-estimate-modal" :show="false" focusable>
    <form wire:submit="submitOverrideEstimate" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">Ubah Estimasi Selesai</h2>
        @if ($overrideEstimateOrderId)
            @php $estOrder = \App\Models\Order::find($overrideEstimateOrderId); @endphp
            @if ($estOrder)
                <p class="mt-1 text-sm text-neutral-600">№ {{ $estOrder->order_number }} — {{ $estOrder->customer->name }}</p>
                @if ($estOrder->estimated_completion_at)
                    <p class="mt-2 text-sm text-neutral-500">Estimasi saat ini: <span class="font-semibold text-neutral-700">{{ format_date($estOrder->estimated_completion_at) }}</span></p>
                @endif
            @endif
        @endif

        <div class="mt-5">
            <x-input
                type="date"
                wire:model.blur="overrideEstimateDate"
                label="Estimasi baru"
                required
                :error="$errors->first('overrideEstimateDate')"
            />
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Simpan Estimasi</x-button>
        </div>
    </form>
</x-modal>
