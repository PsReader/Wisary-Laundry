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
        <h2 class="text-lg font-semibold text-primary-900">{{ $title }}</h2>
        @if ($handoverOrder)
            <p class="mt-1 text-sm text-neutral-600">
                № {{ $handoverOrder->order_number }} — {{ $handoverOrder->customer->name }}
            </p>
        @endif

        <div class="mt-5 space-y-4">
            @if ($isFailed)
                <div>
                    <x-label for="handover_failure_reason" required>Alasan gagal delivery</x-label>
                    <textarea
                        id="handover_failure_reason"
                        wire:model.blur="handoverFailureReason"
                        rows="3"
                        class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-0 transition {{ $errors->first('handoverFailureReason') ? 'border-danger-700 focus:ring-danger-700' : 'border-neutral-300 focus:ring-primary-700' }}"
                        placeholder="Contoh: pelanggan tidak di rumah"
                    ></textarea>
                    @if ($errors->first('handoverFailureReason'))
                        <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('handoverFailureReason') }}</p>
                    @endif
                </div>
            @elseif ($needAddress)
                <div>
                    <x-label for="handover_address" required>Alamat pengantaran</x-label>
                    <textarea
                        id="handover_address"
                        wire:model.blur="handoverAddress"
                        rows="3"
                        class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-0 transition {{ $errors->first('handoverAddress') ? 'border-danger-700 focus:ring-danger-700' : 'border-neutral-300 focus:ring-primary-700' }}"
                    ></textarea>
                    @if ($errors->first('handoverAddress'))
                        <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('handoverAddress') }}</p>
                    @endif
                </div>
            @elseif ($isDelivery && $handoverOrder)
                <p class="text-sm text-neutral-600">
                    <span class="font-semibold text-neutral-800">Alamat pengantaran:</span>
                    {{ $handoverOrder->delivery_address }}
                </p>
            @endif

            <div>
                <x-label for="handover_note">Catatan {{ $isFailed ? 'kegagalan' : 'serah terima' }} (opsional)</x-label>
                <textarea
                    id="handover_note"
                    wire:model.blur="handoverNote"
                    rows="2"
                    class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-0 transition {{ $errors->first('handoverNote') ? 'border-danger-700 focus:ring-danger-700' : 'border-neutral-300 focus:ring-primary-700' }}"
                ></textarea>
                @if ($errors->first('handoverNote'))
                    <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('handoverNote') }}</p>
                @endif
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <button type="button" x-data @click="$dispatch('close-modal', {})" class="flex items-center justify-center px-4 py-3 min-h-12 lg:min-h-10 text-sm font-medium text-neutral-700 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                Batal
            </button>
            <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">{{ $title }}</x-button>
        </div>
    </form>
</x-modal>