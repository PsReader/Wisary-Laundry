<div class="space-y-4 pb-48 lg:pb-0">
    <form wire:submit="save" class="space-y-4">
        {{-- Pelanggan --}}
        <x-card title="Pelanggan">
            @if ($this->selectedCustomer)
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div class="min-w-0">
                        <p class="font-semibold text-neutral-950">{{ $this->selectedCustomer->name }}</p>
                        <p class="mt-0.5 text-sm text-neutral-600">{{ $this->selectedCustomer->whatsapp }}</p>
                        @if ($this->selectedCustomer->address)
                            <p class="mt-0.5 text-sm text-neutral-500">{{ $this->selectedCustomer->address }}</p>
                        @endif
                    </div>
                    <x-button variant="text" size="sm" type="button" wire:click="resetCustomer">Ganti</x-button>
                </div>
                <p class="mt-3 text-xs text-neutral-500">
                    Nomor WhatsApp pelanggan: <strong>{{ $this->selectedCustomer->whatsapp }}</strong>
                </p>
            @else
                <x-input
                    type="search"
                    wire:model.live.debounce.400ms="customer_search"
                    label="Cari pelanggan"
                    placeholder="Nama atau nomor WhatsApp"
                    autocomplete="off"
                />

                @if (trim($this->customer_search) !== '')
                    <div class="mt-3 space-y-2">
                        @forelse ($this->customerResults as $customer)
                            <button
                                type="button"
                                wire:click="selectCustomer({{ $customer->id }})"
                                class="w-full min-h-12 text-left rounded-lg border border-neutral-200 bg-white px-4 py-3 hover:border-primary-700 hover:bg-primary-50 transition-colors"
                            >
                                <p class="font-medium text-neutral-950">{{ $customer->name }}</p>
                                <p class="text-sm text-neutral-600">{{ $customer->whatsapp }}</p>
                                @if ($customer->address)
                                    <p class="text-xs text-neutral-500">{{ $customer->address }}</p>
                                @endif
                            </button>
                        @empty
                            <p class="text-sm text-neutral-500">Tidak ada pelanggan yang cocok. Tambahkan pelanggan baru di bawah.</p>
                        @endforelse
                    </div>
                @else
                    <p class="mt-2 text-xs text-neutral-500">Ketik nama atau nomor WhatsApp untuk mencari, atau tambahkan pelanggan baru.</p>
                @endif

                @error('customer_id')
                    <p class="mt-2 text-xs text-danger-700" role="alert">{{ $message }}</p>
                @enderror

                <div class="mt-3">
                    <x-button variant="secondary" type="button" wire:click="openNewCustomer" block>Tambah Pelanggan Baru</x-button>
                </div>
            @endif
        </x-card>

        {{-- Layanan --}}
        <x-card title="Layanan">
            <div class="space-y-4">
                @forelse ($this->items as $index => $item)
                    @php
                        $rowService = $this->services->firstWhere('id', (int) ($item['service_id'] ?? 0));
                        $billedWeight = $this->itemBilledWeight($item);
                    @endphp
                    <div class="rounded-xl border border-neutral-200 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Layanan {{ $index + 1 }}</p>
                            <button
                                type="button"
                                wire:click="removeItem({{ $index }})"
                                class="inline-flex items-center justify-center min-h-12 px-3 text-sm font-semibold text-danger-700 hover:bg-danger-50 rounded-lg transition-colors"
                            >
                                Hapus
                            </button>
                        </div>

                        @if ($rowService)
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-neutral-950">{{ $rowService->name }}</p>
                                    <p class="mt-0.5 text-sm text-neutral-600">
                                        {{ $rowService->price_label }}
                                        @if ($rowService->pricing_type === \App\Models\Service::PRICING_TYPE_KG)
                                            · per kilogram
                                        @else
                                            · per unit
                                        @endif
                                    </p>
                                </div>
                                <x-button variant="text" size="sm" type="button" wire:click="changeService({{ $index }})">Ganti</x-button>
                            </div>

                            @if ($rowService->pricing_type === \App\Models\Service::PRICING_TYPE_KG)
                                <x-input
                                    type="number"
                                    step="0.1"
                                    min="0.1"
                                    wire:model.blur="items.{{ $index }}.actual_weight"
                                    label="Berat aktual"
                                    placeholder="mis. 3.5"
                                    autocomplete="off"
                                    helptext="Berat ditampilkan, dibulatkan ke atas, dengan minimum tagihan 4 kg."
                                    :error="$errors->first('items.'.$index.'.actual_weight')"
                                />
                            @else
                                <x-input
                                    type="number"
                                    step="1"
                                    min="1"
                                    wire:model.blur="items.{{ $index }}.quantity"
                                    label="Jumlah unit"
                                    placeholder="mis. 2"
                                    autocomplete="off"
                                    helptext="Dihitung per unit, tanpa aturan minimum berat."
                                    :error="$errors->first('items.'.$index.'.quantity')"
                                />
                            @endif

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                <p class="text-sm text-neutral-500">{{ $rowService->price_label }}</p>
                                <div class="text-right">
                                    @if ($billedWeight !== null)
                                        <p class="text-sm text-neutral-600">
                                            Berat aktual {{ number_format((float) $item['actual_weight'], 1, ',', '.') }} kg · ditagihkan {{ $billedWeight }} kg
                                        </p>
                                    @endif
                                    <p class="text-sm font-semibold text-neutral-950">Subtotal: {{ format_rupiah($this->itemSubtotal($item)) }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-neutral-500">Layanan belum dipilih — pilih kembali dari panel di bawah.</p>
                        @endif

                        <x-input
                            type="text"
                            wire:model.blur="items.{{ $index }}.note"
                            label="Catatan item (opsional)"
                            autocomplete="off"
                            :error="$errors->first('items.'.$index.'.note')"
                        />
                    </div>
                @empty
                    <p class="text-sm text-neutral-500">Belum ada layanan — pilih layanan di bawah.</p>
                @endforelse

                <div class="rounded-xl border border-dashed border-primary-700/40 bg-primary-50/50 p-4 space-y-3">
                    <x-input
                        type="search"
                        wire:model.live.debounce.400ms="service_search"
                        label="Cari layanan"
                        placeholder="Nama atau kategori layanan"
                        autocomplete="off"
                    />

                    <div class="space-y-2">
                        @forelse ($this->serviceResults as $service)
                            <button
                                type="button"
                                wire:click="addService({{ $service->id }})"
                                wire:key="service-option-{{ $service->id }}"
                                class="w-full min-h-12 text-left rounded-lg border border-neutral-200 bg-white px-4 py-3 hover:border-primary-700 hover:bg-primary-50 transition-colors"
                            >
                                <p class="font-medium text-neutral-950">{{ $service->name }}</p>
                                <p class="text-sm text-neutral-600">{{ $service->category }} · {{ $service->price_label }} · {{ $service->pricing_type === \App\Models\Service::PRICING_TYPE_KG ? 'per kilogram' : 'per unit' }}</p>
                            </button>
                        @empty
                            <p class="text-sm text-neutral-500">Tidak ada layanan yang cocok atau semua layanan sudah dipilih.</p>
                        @endforelse

                    </div>
                </div>

                @error('items')
                    <p class="text-xs text-danger-700" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        {{-- Metode & Catatan --}}
        <x-card title="Metode & Catatan">
            <div class="space-y-4">
                <div>
                    <x-label for="method" required>Metode serah terima</x-label>
                    <div class="mt-1.5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex cursor-pointer items-center justify-center rounded-lg border px-4 py-3 min-h-12 text-sm font-semibold transition-colors {{ $method === \App\Models\Order::METHOD_DIAMBIL ? 'border-primary-700 bg-primary-50 text-primary-800' : 'border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50' }}">
                            <input type="radio" name="method" value="{{ \App\Models\Order::METHOD_DIAMBIL }}" wire:model.live="method" class="sr-only" />
                            Diambil Pelanggan
                        </label>
                        <label class="relative flex cursor-pointer items-center justify-center rounded-lg border px-4 py-3 min-h-12 text-sm font-semibold transition-colors {{ $method === \App\Models\Order::METHOD_DIANTAR ? 'border-primary-700 bg-primary-50 text-primary-800' : 'border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50' }}">
                            <input type="radio" name="method" value="{{ \App\Models\Order::METHOD_DIANTAR }}" wire:model.live="method" class="sr-only" />
                            Diantar
                        </label>
                    </div>
                    @error('method')
                        <p class="mt-1 text-xs text-danger-700" role="alert">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-neutral-500">Rencana awal — bisa diubah saat serah terima jika pelanggan berubah keputusan.</p>
                </div>

                @if ($method === \App\Models\Order::METHOD_DIANTAR)
                    <x-textarea
                        label="Alamat pengantaran"
                        required
                        wire:model.blur="delivery_address"
                        rows="3"
                        placeholder="Alamat lengkap tujuan pengantaran"
                        helptext="Pengantaran tidak dikenakan biaya tambahan."
                        :error="$errors->first('delivery_address')"
                    />
                @endif

                <x-textarea
                    label="Catatan pesanan"
                    wire:model.blur="notes"
                    rows="3"
                    placeholder="Catatan tambahan untuk pesanan ini (opsional)"
                    :error="$errors->first('notes')"
                />
            </div>
        </x-card>

        {{-- Ringkasan sticky --}}
        <div class="sticky bottom-16 lg:bottom-4 z-20 rounded-xl border border-neutral-200 bg-white p-4 shadow-lg">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0 space-y-1">
                    @if ($this->estimatedCompletion)
                        <p class="text-xs text-neutral-500">
                            Estimasi selesai: <span class="font-semibold text-neutral-900">{{ format_date($this->estimatedCompletion) }}</span>
                        </p>
                    @endif
                    <p class="text-xs text-neutral-500">Total perkiraan</p>
                    <p class="text-xl font-bold text-primary-900">{{ format_rupiah($this->totalPreview) }}</p>
                </div>
                <x-button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-not-allowed">Simpan Pesanan</x-button>
            </div>
        </div>
    </form>

    {{-- Modal pelanggan baru --}}
    <x-modal name="new-customer" :show="false" focusable>
        <form wire:submit="saveNewCustomer" class="p-6">
            <h2 class="text-xl font-semibold text-primary-900">Tambah Pelanggan Baru</h2>
            <p class="mt-1 text-sm text-neutral-600">Pelanggan akan langsung terpilih setelah disimpan.</p>

            <div class="mt-5 space-y-4">
                <x-input
                    type="text"
                    wire:model.blur="customer_name"
                    label="Nama pelanggan"
                    required
                    autocomplete="off"
                    :error="$errors->first('customer_name')"
                />
                <x-input
                    type="tel"
                    wire:model.blur="customer_whatsapp"
                    label="Nomor WhatsApp"
                    required
                    placeholder="08xxxxxxxxxx"
                    autocomplete="off"
                    helptext="Awali dengan 08 dan 8–13 digit angka."
                    :error="$errors->first('customer_whatsapp')"
                />
                <x-textarea
                    label="Alamat utama"
                    wire:model.blur="customer_address"
                    rows="3"
                    :error="$errors->first('customer_address')"
                />
                <x-textarea
                    label="Catatan pelanggan"
                    wire:model.blur="customer_notes"
                    rows="3"
                    :error="$errors->first('customer_notes')"
                />
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
                <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Simpan Pelanggan</x-button>
            </div>
        </form>
    </x-modal>
</div>
