<div class="space-y-4">
        <p class="text-sm text-neutral-500">{{ $this->cancelledOrders->count() }} pesanan dibatalkan</p>

        <div>
            <x-label for="search">Cari pesanan</x-label>
            <input
                id="search"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="No. pesanan atau nama pelanggan"
                class="mt-1.5 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary-700 transition"
            >
        </div>

        @if ($this->cancelledOrders->isEmpty())
            <x-card>
                <div class="py-10 text-center">
                    <p class="text-neutral-500">Tidak ada pesanan batal yang ditemukan.</p>
                </div>
            </x-card>
        @else
            <div class="space-y-4">
                @foreach ($this->cancelledOrders as $order)
                    <div class="rounded-xl border border-neutral-200 bg-white p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs text-neutral-500">№ {{ $order->order_number }}</p>
                                <p class="font-semibold text-neutral-950">{{ $order->customer->name }}</p>
                                <div class="mt-1">
                                    <x-badge status="Dibatalkan">Dibatalkan</x-badge>
                                </div>
                            </div>
                            <x-button
                                type="button"
                                variant="secondary"
                                wire:click="startRestore({{ $order->id }})"
                                class="shrink-0"
                            >
                                Pulihkan
                            </x-button>
                        </div>

                        <div class="rounded-lg bg-danger-50 px-3 py-2 text-sm text-danger-700">
                            {{ $order->cancellation_reason }}
                        </div>

                        <dl class="text-xs text-neutral-600 space-y-0.5">
                            <div class="flex justify-between gap-3">
                                <dt>Dibatalkan oleh</dt>
                                <dd class="text-right">{{ $order->cancelledBy?->name ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Waktu</dt>
                                <dd class="text-right">{{ $order->cancelled_at ? format_datetime_wib($order->cancelled_at) : '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>
        @endif

        <x-modal name="restore-modal" :show="false" focusable>
            <form wire:submit="submitRestore" class="p-6">
                <h2 class="text-xl font-semibold text-primary-900">Pulihkan Pesanan</h2>
                @if ($restoreOrderId)
                    @php $restoreOrder = \App\Models\Order::find($restoreOrderId); @endphp
                    @if ($restoreOrder)
                        <p class="mt-1 text-sm text-neutral-600">№ {{ $restoreOrder->order_number }} — {{ $restoreOrder->customer->name }}</p>
                    @endif
                @endif
                <p class="mt-3 rounded-lg bg-primary-50 px-3 py-2 text-sm text-neutral-700">
                    Pesanan akan kembali ke status <strong>Diterima</strong> dan muncul kembali di layar proses. Riwayat pembatalan tetap tersimpan.
                </p>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <button type="button" x-data @click="$dispatch('close-modal', {})" class="flex items-center justify-center px-4 py-3 min-h-12 text-sm font-medium text-neutral-700 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                        Batal
                    </button>
                    <x-button type="submit" variant="primary" class="sm:min-w-32" wire:loading.attr="disabled">Pulihkan Pesanan</x-button>
                </div>
            </form>
        </x-modal>
    </div>