<div class="space-y-4">

    {{-- Filters --}}
    <div class="flex flex-col gap-2">
        {{-- Search + date --}}
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-3">
            <x-input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nomor pesanan, nama, atau nomor WhatsApp..."
                label=""
                helptext=""
            />
            <div>
                <div class="flex flex-wrap gap-2">
                <input
                    type="date"
                    wire:model.live="receivedDateFilter"
                    aria-label="Filter tanggal diterima"
                    class="w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition border-neutral-300 focus:ring-primary-700"
                >
                @php
                    $todayStr = now('Asia/Jakarta')->toDateString();
                    $yesterdayStr = now('Asia/Jakarta')->subDay()->toDateString();
                    $dateChipBase = 'flex-1 rounded-lg border px-2 py-2 min-h-12 text-xs font-semibold transition-colors';
                @endphp
                <button
                    type="button"
                    wire:click="setDateFilter('today')"
                    class="{{ $dateChipBase }} {{ $receivedDateFilter === $todayStr ? 'border-primary-700 bg-primary-700 text-white' : 'border-neutral-300 bg-white text-neutral-700 hover:border-primary-700 hover:text-primary-700' }}"
                >
                    Hari Ini
                </button>
                <button
                    type="button"
                    wire:click="setDateFilter('yesterday')"
                    class="{{ $dateChipBase }} {{ $receivedDateFilter === $yesterdayStr ? 'border-primary-700 bg-primary-700 text-white' : 'border-neutral-300 bg-white text-neutral-700 hover:border-primary-700 hover:text-primary-700' }}"
                >
                    Kemarin
                </button>
                <button
                    type="button"
                    wire:click="setDateFilter('7d')"
                    class="{{ $dateChipBase }} {{ $recent7Filter === '7' ? 'border-primary-700 bg-primary-700 text-white' : 'border-neutral-300 bg-white text-neutral-700 hover:border-primary-700 hover:text-primary-700' }}"
                >
                    7 Hari
                </button>
                </div>
            </div>
        </div>

        {{-- Status pills --}}
        <div class="flex gap-2 flex-nowrap overflow-x-auto scrollbar-chip pb-1 sm:flex-wrap sm:overflow-visible sm:pb-0">
            <button
                type="button"
                wire:click="setStatusFilter('')"
                class="flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-2.5 min-h-12 text-sm font-semibold transition-colors {{ $this->statusFilter === '' ? 'border-primary-700 bg-primary-700 text-white' : 'border-neutral-200 bg-white text-neutral-700 hover:border-primary-700 hover:text-primary-700' }}"
            >
                Semua ({{ array_sum($this->statusCounts) }})
            </button>
            @foreach (\App\Models\Order::statuses() as $status)
                <button
                    type="button"
                    wire:click="setStatusFilter('{{ $status }}')"
                    class="flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-2.5 min-h-12 text-sm font-semibold transition-colors {{ $this->statusFilter === $status ? 'border-primary-700 bg-primary-700 text-white' : 'border-neutral-200 bg-white text-neutral-700 hover:border-primary-700 hover:text-primary-700' }}"
                >
                    {{ \App\Models\Order::statusLabel($status) }} ({{ $this->statusCounts[$status] ?? 0 }})
                </button>
            @endforeach
        </div>

        {{-- Filter disclosure --}}
        <details class="group">
            <summary class="list-none flex items-center justify-between gap-2 rounded-lg border border-neutral-300 bg-white px-3 py-2.5 min-h-12 text-sm font-semibold text-neutral-700 transition-colors hover:border-primary-700 hover:text-primary-700 cursor-pointer">
                <span class="flex items-center gap-2">
                    Filter
                    @php $activeFilterCount = collect([$paymentFilter, $handoverFilter, $methodFilter])->filter(fn ($v) => (string) $v !== '')->count(); @endphp
                    @if ($activeFilterCount > 0)
                        <span class="rounded-full bg-primary-700 px-1.5 py-0.5 text-xs font-bold text-white">{{ $activeFilterCount }}</span>
                    @endif
                </span>
                <span class="text-neutral-400 transition-transform group-open:rotate-180" aria-hidden="true">▾</span>
            </summary>
            <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2">
                <select
                    wire:model.live="paymentFilter"
                    class="w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition border-neutral-300 focus:ring-primary-700"
                >
                    <option value="">Semua Pembayaran</option>
                    @foreach (\App\Models\Order::paymentStatuses() as $status)
                        <option value="{{ $status }}">{{ \App\Models\Order::paymentLabel($status) }}</option>
                    @endforeach
                </select>

            <select
                wire:model.live="handoverFilter"
                class="w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition border-neutral-300 focus:ring-primary-700"
            >
                <option value="">Semua Penyerahan</option>
                @foreach (\App\Models\Order::handoverStatuses() as $status)
                    <option value="{{ $status }}">{{ \App\Models\Order::handoverLabel($status) }}</option>
                @endforeach
            </select>

            <select
                wire:model.live="methodFilter"
                class="w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition border-neutral-300 focus:ring-primary-700"
            >
                <option value="">Semua Metode</option>
                <option value="{{ \App\Models\Order::METHOD_DIAMBIL }}">{{ \App\Models\Order::methodLabel(\App\Models\Order::METHOD_DIAMBIL) }}</option>
                <option value="{{ \App\Models\Order::METHOD_DIANTAR }}">{{ \App\Models\Order::methodLabel(\App\Models\Order::METHOD_DIANTAR) }}</option>
            </select>
        </div>
        </details>
    </div>

    {{-- Results count --}}
    <p class="text-xs text-neutral-500">{{ $orders->total() }} pesanan ditemukan</p>

    {{-- Table (desktop) --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-sm min-w-[720px]">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                    <th class="py-3 pr-4 font-semibold">
                        <button wire:click="toggleSort('order_number')" class="hover:text-primary-700 transition-colors">
                            Nomor
                            @if ($this->sortBy === 'order_number')
                                <span class="ml-0.5">{{ $this->sortDir === 'desc' ? '↓' : '↑' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="py-3 pr-4 font-semibold">Pelanggan</th>
                    <th class="py-3 pr-4 font-semibold">Status</th>
                    <th class="py-3 pr-4 font-semibold">Pembayaran</th>
                    <th class="py-3 pr-4 font-semibold text-right">Total</th>
                    <th class="py-3 pr-4 font-semibold">
                        <button wire:click="toggleSort('received_at')" class="hover:text-primary-700 transition-colors">
                            Tanggal
                            @if ($this->sortBy === 'received_at')
                                <span class="ml-0.5">{{ $this->sortDir === 'desc' ? '↓' : '↑' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="py-3 font-semibold">Metode</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50 transition-colors cursor-pointer"
                        onclick="window.location='{{ route('orders.show', $order) }}'"
                    >
                        <td class="py-3 pr-4">
                            <a href="{{ route('orders.show', $order) }}" class="font-semibold text-primary-700 hover:underline" onclick="event.stopPropagation()">№ {{ $order->order_number }}</a>
                        </td>
                        <td class="py-3 pr-4">
                            <p class="font-medium">{{ $order->customer->name }}</p>
                            <p class="text-xs text-neutral-500">{{ $order->customer->whatsapp }}</p>
                        </td>
                        <td class="py-3 pr-4">
                            <x-badge :status="\App\Models\Order::statusLabel($order->status)">{{ \App\Models\Order::statusLabel($order->status) }}</x-badge>
                        </td>
                        <td class="py-3 pr-4">
                            <x-badge :status="\App\Models\Order::paymentLabel($order->payment_status)">{{ \App\Models\Order::paymentLabel($order->payment_status) }}</x-badge>
                        </td>
                        <td class="py-3 pr-4 text-right font-semibold text-primary-900">{{ format_rupiah((float) $order->total_price) }}</td>
                        <td class="py-3 pr-4 text-neutral-600 text-xs">{{ format_date($order->received_at) }}</td>
                        <td class="py-3">
                            <x-badge color="bg-neutral-100 text-neutral-700">{{ \App\Models\Order::methodLabel($order->method) }}</x-badge>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-neutral-500">
                            <p class="text-sm">Tidak ada pesanan ditemukan.</p>
                            @if ($this->hasFilters())
                                <button type="button" wire:click="resetFilters" class="mt-2 text-sm font-medium text-primary-700 hover:underline">Hapus filter</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cards (mobile) --}}
    <div class="sm:hidden space-y-3">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="block rounded-xl border border-neutral-200 bg-white p-4 shadow-xs hover:shadow-sm transition-shadow">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-primary-700">№ {{ $order->order_number }}</p>
                        <p class="text-sm font-medium text-neutral-700">{{ $order->customer->name }}</p>
                    </div>
                    <p class="shrink-0 font-semibold text-primary-900">{{ format_rupiah((float) $order->total_price) }}</p>
                </div>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <x-badge :status="\App\Models\Order::statusLabel($order->status)">{{ \App\Models\Order::statusLabel($order->status) }}</x-badge>
                    <x-badge :status="\App\Models\Order::paymentLabel($order->payment_status)">{{ \App\Models\Order::paymentLabel($order->payment_status) }}</x-badge>
                    <x-badge color="bg-neutral-100 text-neutral-700">{{ \App\Models\Order::methodLabel($order->method) }}</x-badge>
                </div>
                <p class="mt-2 text-xs text-neutral-500">{{ format_date($order->received_at) }}</p>
            </a>
        @empty
            <div class="rounded-xl border border-neutral-200 bg-white p-8 text-center text-neutral-500">
                <p class="text-sm">Tidak ada pesanan ditemukan.</p>
                @if ($this->hasFilters())
                    <button type="button" wire:click="resetFilters" class="mt-2 text-sm font-medium text-primary-700 hover:underline">Hapus filter</button>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($orders->hasPages())
    <div class="mt-4 -mx-4 overflow-x-auto px-4 scrollbar-chip [&_a]:inline-flex [&_a]:items-center [&_a]:min-h-12 [&_a]:px-3 [&_span]:inline-flex [&_span]:items-center [&_span]:min-h-12 [&_span]:px-3">
        {{ $orders->withQueryString()->links() }}
    </div>
    @endif
</div>
