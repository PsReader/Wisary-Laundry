@php
    $handoverOrder = \App\Models\Order::find($this->handoverOrderId);
    $isPickup = $this->handoverAction === 'sudah_diambil';
    $isDelivery = $this->handoverAction === 'sudah_diantar';
    $isFailed = $this->handoverAction === 'gagal';
    $needAddress = $isDelivery && $handoverOrder !== null && trim((string) $handoverOrder->delivery_address) === '';
    $title = $isPickup ? 'Tandai Sudah Diambil' : ($isDelivery ? 'Tandai Sudah Diantar' : 'Catat Gagal Delivery');
@endphp

<x-modal name="handover-modal" :show="false" focusable>
    <form wire:submit="submitHandover" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">{{ $title }}</h2>
        @if ($handoverOrder)
            <p class="mt-1 text-sm text-neutral-600">
                № {{ $handoverOrder->order_number }} — {{ $handoverOrder->customer->name }}
            </p>
        @endif

        <div class="mt-5 space-y-4">
            @if ($isFailed)
                <x-textarea
                    label="Alasan gagal delivery"
                    required
                    wire:model.blur="handoverFailureReason"
                    rows="3"
                    placeholder="Contoh: pelanggan tidak di rumah"
                    :error="$errors->first('handoverFailureReason')"
                />
            @elseif ($needAddress)
                <x-textarea
                    label="Alamat pengantaran"
                    required
                    wire:model.blur="handoverAddress"
                    rows="3"
                    :error="$errors->first('handoverAddress')"
                />
            @elseif ($isDelivery && $handoverOrder)
                <p class="text-sm text-neutral-600">
                    <span class="font-semibold text-neutral-800">Alamat pengantaran:</span>
                    {{ $handoverOrder->delivery_address }}
                </p>
            @endif

            <x-textarea
                label="Catatan {{ $isFailed ? 'kegagalan' : 'serah terima' }} (opsional)"
                wire:model.blur="handoverNote"
                rows="2"
                :error="$errors->first('handoverNote')"
            />
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">{{ $title }}</x-button>
        </div>
    </form>
</x-modal>
