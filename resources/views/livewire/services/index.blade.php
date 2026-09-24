<div class="space-y-4">
    @if (auth()->user()->isSuperAdmin())
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <p class="text-sm text-neutral-600">
                Layanan nonaktif tidak muncul pada pesanan baru, tetapi riwayat pesanan lama tetap tersimpan.
            </p>
            <x-button wire:click="create">Tambah Layanan</x-button>
        </div>
    @endif

    <x-card>
        @if ($this->services->isEmpty())
            <div class="py-12 text-center">
                <p class="text-neutral-500">Belum ada layanan terdaftar.</p>
                @if (auth()->user()->isSuperAdmin())
                    <div class="mt-4 flex justify-center">
                        <x-button wire:click="create">Tambah Layanan Pertama</x-button>
                    </div>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                            <th class="py-3 pr-4 font-semibold">Layanan</th>
                            <th class="py-3 pr-4 font-semibold">Kategori</th>
                            <th class="py-3 pr-4 font-semibold">Tipe Harga</th>
                            <th class="py-3 pr-4 font-semibold">Harga</th>
                            <th class="py-3 pr-4 font-semibold">Estimasi</th>
                            <th class="py-3 pr-4 font-semibold">Status</th>
                            <th class="py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->services as $service)
                            <tr class="border-b border-neutral-100 last:border-0">
                                <td class="py-3 pr-4 font-medium">{{ $service->name }}</td>
                                <td class="py-3 pr-4 text-neutral-600">{{ $service->category }}</td>
                                <td class="py-3 pr-4 text-neutral-600">{{ \App\Models\Service::pricingTypeLabel($service->pricing_type) }}</td>
                                <td class="py-3 pr-4 font-medium">{{ $service->price_label }}</td>
                                <td class="py-3 pr-4 text-neutral-600">{{ \App\Models\Service::estimateLabel($service->estimated_days) }}</td>
                                <td class="py-3 pr-4">
                                    <x-badge color="{{ $service->is_active ? 'bg-success-100 text-success-700' : 'bg-danger-100 text-danger-700' }}">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                                </td>
                                <td class="py-3 text-right whitespace-nowrap">
                                    @if (auth()->user()->isSuperAdmin())
                                        <div class="inline-flex items-center gap-1">
                                            <x-button variant="text" size="sm" wire:click="edit({{ $service->id }})">Edit</x-button>
                                            <x-button variant="text" size="sm" wire:click="toggle({{ $service->id }})">{{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</x-button>
                                        </div>
                                    @else
                                        <span class="text-xs text-neutral-400">Baca saja</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    @if (auth()->user()->isSuperAdmin())
        <x-modal name="service-form" :show="false" focusable>
        <form wire:submit="save" class="p-6">
            <h2 class="text-lg font-semibold text-primary-900">{{ $formMode === 'edit' ? 'Edit Layanan' : 'Tambah Layanan' }}</h2>
            <p class="mt-1 text-sm text-neutral-600">
                {{ $formMode === 'edit' ? 'Perubahan harga hanya berlaku untuk pesanan baru.' : 'Layanan baru akan muncul pada form pesanan setelah disimpan.' }}
            </p>

            <div class="mt-5 space-y-4">
                <x-input
                    type="text"
                    wire:model.blur="name"
                    label="Nama layanan"
                    required
                    autocomplete="off"
                    :error="$errors->first('name')"
                />
                <div>
                    <x-label for="category" required>Kategori</x-label>
                    <input
                        id="category"
                        type="text"
                        wire:model.blur="category"
                        list="service-categories"
                        required
                        class="w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-neutral-950 min-h-12 lg:min-h-10 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-0 transition {{ $errors->first('category') ? 'border-danger-700 focus:ring-danger-700' : 'border-neutral-300 focus:ring-primary-700' }}"
                    >
                    <datalist id="service-categories">
                        @foreach ($this->existing_categories as $category)
                            <option value="{{ $category }}"></option>
                        @endforeach
                    </datalist>
                    @if ($errors->first('category'))
                        <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('category') }}</p>
                    @endif
                </div>
                <x-select
                    wire:model.blur="pricing_type"
                    name="pricing_type"
                    label="Tipe harga"
                    required
                    :options="['kg' => 'Per Kilogram', 'unit' => 'Per Unit']"
                    :error="$errors->first('pricing_type')"
                />
                <x-input
                    type="number"
                    wire:model.blur="price"
                    label="Harga"
                    required
                    min="1"
                    step="1"
                    autocomplete="off"
                    helptext="Harga dalam Rupiah. Per kilogram atau per unit sesuai tipe harga."
                    :error="$errors->first('price')"
                />
                <x-select
                    wire:model.blur="estimated_days"
                    name="estimated_days"
                    label="Estimasi selesai"
                    required
                    :options="['0' => 'Hari yang sama', '1' => '1 hari kalender', '2' => '2 hari kalender', '3' => '3 hari kalender', '4' => '4 hari kalender', '5' => '5 hari kalender', '6' => '6 hari kalender', '7' => '7 hari kalender']"
                    :error="$errors->first('estimated_days')"
                />
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button" x-data @click="$dispatch('close-modal', {})" class="flex items-center justify-center px-4 py-3 min-h-12 lg:min-h-10 text-sm font-medium text-neutral-700 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Batal
                </button>
                <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">{{ $formMode === 'edit' ? 'Simpan Perubahan' : 'Simpan Layanan' }}</x-button>
            </div>
        </form>
        </x-modal>
    @endif
</div>