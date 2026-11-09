<div class="space-y-4" >
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div class="w-full sm:max-w-xs">
            <x-input
                type="text"
                wire:model.live.debounce.400ms="search"
                label="Cari pelanggan"
                placeholder="Nama atau nomor WhatsApp"
                autocomplete="off"
            />
        </div>
        <x-button wire:click="create">Tambah Pelanggan</x-button>
    </div>

    <x-card>
        @if ($this->customers->total() === 0)
            <div class="py-12 text-center">
                @if (trim($search) === '')
                    <p class="text-neutral-500">Belum ada pelanggan terdaftar.</p>
                    <div class="mt-4 flex justify-center">
                        <x-button wire:click="create">Tambah Pelanggan Pertama</x-button>
                    </div>
                @else
                    <p class="text-neutral-500">Tidak ada pelanggan yang cocok dengan pencarian "<strong>{{ $search }}</strong>".</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[560px]">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                            <th class="py-3 pr-4 font-semibold">Pelanggan</th>
                            <th class="py-3 pr-4 font-semibold">WhatsApp</th>
                            <th class="py-3 pr-4 font-semibold">Alamat</th>
                            <th class="py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->customers as $customer)
                            <tr class="border-b border-neutral-100 last:border-0">
                                <td class="py-3 pr-4">
                                    <p class="font-medium"><a href="{{ route('customers.show', $customer) }}" class="text-primary-700 hover:underline">{{ $customer->name }}</a></p>
                                    <p class="text-xs text-neutral-500">{{ $customer->orders_count }} pesanan</p>
                                </td>
                                <td class="py-3 pr-4 text-neutral-600">{{ $customer->whatsapp }}</td>
                                <td class="py-3 pr-4 text-neutral-600">{{ $customer->address ?? '—' }}</td>
                                <td class="py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <x-button variant="text" size="sm" href="{{ route('customers.show', $customer) }}">Detail</x-button>
                                        <x-button variant="text" size="sm" wire:click="edit({{ $customer->id }})">Edit</x-button>
                                        @if (auth()->user()->isSuperAdmin())
                                            <x-button variant="text" size="sm" wire:click="confirmDelete({{ $customer->id }})">Hapus</x-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$this->customers" label="pelanggan" />
        @endif
    </x-card>

    <x-modal name="customer-form" :show="false" focusable>
        <form wire:submit="save" class="p-6">
            <h2 class="text-xl font-semibold text-primary-900">{{ $formMode === 'edit' ? 'Edit Pelanggan' : 'Tambah Pelanggan' }}</h2>
            <p class="mt-1 text-sm text-neutral-600">
                {{ $formMode === 'edit' ? 'Perbarui informasi kontak pelanggan.' : 'Pelanggan baru akan tersedia saat membuat pesanan.' }}
            </p>

            <div class="mt-5 space-y-4">
                <x-input
                    type="text"
                    wire:model.blur="name"
                    label="Nama pelanggan"
                    required
                    autocomplete="off"
                    :error="$errors->first('name')"
                />
                <x-input
                    type="tel"
                    wire:model.blur="whatsapp"
                    label="Nomor WhatsApp"
                    required
                    placeholder="08xxxxxxxxxx"
                    autocomplete="off"
                    helptext="Awali dengan 08 dan 8–13 digit angka."
                    :error="$errors->first('whatsapp')"
                />
                <x-textarea label="Alamat utama" wire:model.blur="address" rows="3" :error="$errors->first('address')" />
                <x-textarea label="Catatan pelanggan" wire:model.blur="notes" rows="3" :error="$errors->first('notes')" />
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
                <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">{{ $formMode === 'edit' ? 'Simpan Perubahan' : 'Simpan Pelanggan' }}</x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="confirm-delete" :show="false" focusable>
        <div class="p-6">
            <h2 class="text-xl font-semibold text-primary-900">Hapus Pelanggan</h2>
            <p class="mt-1 text-sm text-neutral-600">
                Yakin ingin menghapus <strong>{{ $this->delete_customer?->name ?? 'pelanggan ini' }}</strong>? Tindakan ini tidak dapat dibatalkan.
            </p>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
                <x-button type="button" variant="destructive" wire:click="delete">Ya, Hapus</x-button>
            </div>
        </div>
    </x-modal>
</div>