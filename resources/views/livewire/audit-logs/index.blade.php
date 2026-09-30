<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-primary-900">Audit Log</h2>
            <p class="text-xs text-neutral-500">Jejak aktivitas pengguna: pesanan, pembayaran, layanan, akun, dan login.</p>
        </div>
    </div>

    {{-- Filter --}}
    <x-card>
        <div class="flex flex-wrap items-end gap-2">
            <div>
                <label for="from" class="block text-xs font-semibold text-neutral-600 mb-1.5">Dari</label>
                <input type="date" id="from" wire:model.live.debounce.300ms="from"
                    class="w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition border-neutral-300 focus:ring-primary-700">
            </div>
            <div>
                <label for="to" class="block text-xs font-semibold text-neutral-600 mb-1.5">Sampai</label>
                <input type="date" id="to" wire:model.live.debounce.300ms="to"
                    class="w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition border-neutral-300 focus:ring-primary-700">
            </div>
            <div>
                <label for="group" class="block text-xs font-semibold text-neutral-600 mb-1.5">Jenis Aksi</label>
                <select id="group" wire:model.live="group"
                    class="w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition border-neutral-300 focus:ring-primary-700">
                    <option value="">Semua Jenis</option>
                    @foreach ($groups as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="user" class="block text-xs font-semibold text-neutral-600 mb-1.5">Pengguna</label>
                <select id="user" wire:model.live="user"
                    class="w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition border-neutral-300 focus:ring-primary-700">
                    <option value="">Semua Pengguna</option>
                    @foreach ($users as $userOption)
                        <option value="{{ $userOption->id }}">{{ $userOption->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" wire:click="resetFilters"
                class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-3 py-2.5 min-h-12 text-xs font-semibold text-neutral-700 transition-colors hover:bg-neutral-50">Reset</button>
        </div>
    </x-card>

    @php
        $badgeColors = [
            'order.created' => 'bg-success-100 text-success-700',
            'order.status' => 'bg-info-100 text-info-700',
            'order.handover' => 'bg-info-100 text-info-700',
            'order.payment' => 'bg-success-100 text-success-700',
            'order.refund' => 'bg-danger-100 text-danger-700',
            'order.price_adjust' => 'bg-warning-100 text-warning-700',
            'order.estimate_overridden' => 'bg-warning-100 text-warning-700',
            'customer' => 'bg-primary-100 text-primary-800',
            'service' => 'bg-primary-100 text-primary-800',
            'user' => 'bg-primary-100 text-primary-800',
            'login' => 'bg-success-100 text-success-700',
            'logout' => 'bg-neutral-100 text-neutral-700',
            'login_failed' => 'bg-danger-100 text-danger-700',
        ];
    @endphp

    {{-- Daftar audit --}}
    <x-card>
        @if ($feed->isEmpty())
            <p class="py-8 text-center text-sm text-neutral-500">Tidak ada aktivitas pada periode dan filter ini.</p>
        @else
            <div class="divide-y divide-neutral-100">
                @foreach ($feed as $entry)
                    <div class="flex flex-col gap-1 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-start sm:gap-4">
                        <div class="shrink-0 sm:w-40">
                            <p class="text-xs font-medium text-neutral-500">{{ format_datetime_wib($entry['at']) }}</p>
                            {{-- Waktu & pengguna ditampilkan berdampingan di layar kecil --}}
                            <p class="text-xs text-neutral-500 sm:hidden">oleh {{ $entry['user'] }}</p>
                        </div>
                        <div class="flex flex-1 items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeColors[$entry['type']] ?? 'bg-neutral-100 text-neutral-700' }}">
                                        {{ $groups[$entry['type']] ?? $entry['type'] }}
                                    </span>
                                    <span class="hidden text-xs text-neutral-500 sm:inline">oleh {{ $entry['user'] }}</span>
                                </div>
                                <p class="text-sm text-neutral-800">{{ $entry['detail'] }}</p>
                            </div>
                            @if ($entry['orderId'] !== null)
                                <a href="{{ route('orders.show', $entry['orderId']) }}" class="shrink-0 text-xs font-semibold text-primary-700 hover:underline">№ {{ $entry['orderNumber'] }}</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>
</div>