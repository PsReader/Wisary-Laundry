<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-neutral-600">Laporan operasional dan aktivitas staf.</p>
            @if ($range !== null)
                <p class="mt-0.5 text-xs text-neutral-500">Periode {{ format_date($range[0]) }} — {{ format_date($range[1]) }}</p>
            @endif
        </div>
    </div>

    {{-- Filter periode --}}
    <x-card>
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="w-full lg:w-auto">
                <label class="block text-xs font-semibold text-neutral-600 mb-1.5">Periode</label>
                <div class="flex flex-wrap gap-2">
                    @php
                        $periodButtonBase = 'inline-flex items-center justify-center rounded-lg border px-3 py-2 min-h-12 text-xs font-semibold transition-colors';
                        $periodButtonActive = 'border-primary-700 bg-primary-700 text-white hover:bg-primary-800';
                        $periodButtonInactive = 'border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50';
                    @endphp
                    <button
                        type="button"
                        wire:click="setPeriod('today')"
                        aria-pressed="{{ $this->activePeriod === 'today' ? 'true' : 'false' }}"
                        class="{{ $periodButtonBase }} {{ $this->activePeriod === 'today' ? $periodButtonActive : $periodButtonInactive }}"
                    >Hari Ini</button>
                    <button
                        type="button"
                        wire:click="setPeriod('week')"
                        aria-pressed="{{ $this->activePeriod === 'week' ? 'true' : 'false' }}"
                        class="{{ $periodButtonBase }} {{ $this->activePeriod === 'week' ? $periodButtonActive : $periodButtonInactive }}"
                    >7 Hari</button>
                    <button
                        type="button"
                        wire:click="setPeriod('month')"
                        aria-pressed="{{ $this->activePeriod === 'month' ? 'true' : 'false' }}"
                        class="{{ $periodButtonBase }} {{ $this->activePeriod === 'month' ? $periodButtonActive : $periodButtonInactive }}"
                    >Bulan Ini</button>
                    <button
                        type="button"
                        wire:click="setPeriod('last_month')"
                        aria-pressed="{{ $this->activePeriod === 'last_month' ? 'true' : 'false' }}"
                        class="{{ $periodButtonBase }} {{ $this->activePeriod === 'last_month' ? $periodButtonActive : $periodButtonInactive }}"
                    >Bulan Lalu</button>
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <label for="from" class="block text-xs font-semibold text-neutral-600 mb-1.5">Dari</label>
                    <input type="date" id="from" wire:model.live="from"
                        class="w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition border-neutral-300 focus:ring-primary-700">
                </div>
                <div>
                    <label for="to" class="block text-xs font-semibold text-neutral-600 mb-1.5">Sampai</label>
                    <input type="date" id="to" wire:model.live="to"
                        class="w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition border-neutral-300 focus:ring-primary-700">
                </div>
            </div>
        </div>

        @if ($error !== '')
            <p class="mt-3 rounded-lg bg-danger-100 px-3 py-2 text-sm text-danger-700">{{ $error }}</p>
        @endif

    </x-card>

    @if ($range === null || $data === null)
        <x-card>
            <p class="py-6 text-center text-sm text-neutral-500">Pilih periode laporan untuk melihat datanya.</p>
        </x-card>
    @else
        {{-- Tabs --}}
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="setTab('keuangan')"
                class="inline-flex items-center justify-center rounded-lg px-4 py-2 min-h-12 text-sm font-semibold transition-colors {{ $tab === 'keuangan' ? 'bg-primary-700 text-white' : 'border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50' }}">
                Keuangan
            </button>
            <button type="button" wire:click="setTab('pengiriman')"
                class="inline-flex items-center justify-center rounded-lg px-4 py-2 min-h-12 text-sm font-semibold transition-colors {{ $tab === 'pengiriman' ? 'bg-primary-700 text-white' : 'border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50' }}">
                Pengiriman & Serah Terima
            </button>
            <button type="button" wire:click="setTab('aktivitas')"
                class="inline-flex items-center justify-center rounded-lg px-4 py-2 min-h-12 text-sm font-semibold transition-colors {{ $tab === 'aktivitas' ? 'bg-primary-700 text-white' : 'border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50' }}">
                Aktivitas
            </button>
        </div>

        @if ($tab === 'keuangan')
            {{-- Ringkasan Periode --}}
            <div class="space-y-3">
                <h3 class="text-base font-semibold text-neutral-900">Ringkasan Periode</h3>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    <x-stat-card label="Total Pesanan / Pendapatan" value="{{ $data['ordersByStatus']->sum() }} / {{ format_rupiah($data['revenueTotal']) }}" />
                    <x-stat-card label="Belum Lunas" :value="$data['unpaidOrders']->count()" />
                    <x-stat-card label="Refund" value="{{ format_rupiah($data['refunds']->sum('amount')) }}" />
                </div>
            </div>

            <x-card title="Jumlah Pesanan">
                <h3 class="mb-2 text-base font-semibold text-neutral-700">Per Status</h3>
                <table class="w-full text-sm">
                    <tbody>
                        @forelse ($data['ordersByStatus'] as $status => $count)
                            <tr class="border-b border-neutral-100 last:border-0">
                                <td class="py-2.5 pr-4 text-neutral-700">{{ $status }}</td>
                                <td class="py-2.5 text-right font-semibold text-primary-900">{{ $count }}</td>
                            </tr>
                        @empty
                            <tr><td class="py-6 text-center text-neutral-500">Tidak ada pesanan pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>

            {{-- Ringkasan Harian --}}
            <x-card>
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-neutral-900">Ringkasan Harian</h3>
                    <p class="mt-0.5 text-sm text-neutral-600">Pesanan & Pendapatan per Hari</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[320px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100 bg-neutral-50">
                                <th class="py-3 px-4 font-semibold">Tanggal</th>
                                <th class="py-3 px-4 font-semibold text-right">Jumlah Pesanan</th>
                                <th class="py-3 px-4 font-semibold text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $days = $data['ordersByDay']->keys()->merge($data['revenueByDay']->keys())->unique()->sort();
                            @endphp
                            @forelse ($days as $day)
                                <tr class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50/50">
                                    <td class="py-2.5 px-4 text-neutral-700">{{ format_date(\Carbon\Carbon::parse($day)) }}</td>
                                    <td class="py-2.5 px-4 text-right font-semibold text-primary-900">{{ $data['ordersByDay'][$day] ?? 0 }}</td>
                                    <td class="py-2.5 px-4 text-right font-semibold text-primary-900">{{ format_rupiah($data['revenueByDay'][$day] ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-neutral-500">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            {{-- Layanan terlaris --}}
            <x-card>
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-neutral-900">Layanan Paling Sering Digunakan</h3>
                    <p class="mt-0.5 text-sm text-neutral-600">Top 5 berdasarkan jumlah pesanan</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[480px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100 bg-neutral-50">
                                <th class="py-3 px-4 font-semibold">Layanan</th>
                                <th class="py-3 px-4 font-semibold text-right">Pesanan</th>
                                <th class="py-3 px-4 font-semibold text-right">Qty / Kg</th>
                                <th class="py-3 px-4 font-semibold text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['topServices'] as $service)
                                <tr class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50/50">
                                    <td class="py-3 px-4 font-medium text-neutral-800">{{ $service['name'] }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-600">{{ $service['order_count'] }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-600">
                                        @if ($service['quantity'] > 0)
                                            {{ number_format($service['quantity'], 0, ',', '.') }} unit
                                        @else
                                            {{ number_format($service['weight'], 1, ',', '.') }} kg
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold text-primary-900">{{ format_rupiah($service['subtotal']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-neutral-500">Belum ada layanan digunakan pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            {{-- Tagihan & Pembatalan --}}
            <div class="space-y-3">
                <h3 class="text-base font-semibold text-neutral-900">Tagihan & Pembatalan</h3>
            </div>

            {{-- Belum lunas --}}
            <x-card>
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-neutral-900">Pesanan Belum Lunas</h3>
                    <p class="mt-0.5 text-sm text-neutral-600">
                        {{ $data['unpaidOrders']->count() }} pesanan · sisa tagihan total
                        <span class="font-semibold text-primary-900">{{ format_rupiah($data['unpaidOrders']->sum(fn ($o) => $o->remainingAmount)) }}</span>
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[560px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100 bg-neutral-50">
                                <th class="py-3 px-4 font-semibold">Nomor</th>
                                <th class="py-3 px-4 font-semibold">Pelanggan</th>
                                <th class="py-3 px-4 font-semibold text-right">Total</th>
                                <th class="py-3 px-4 font-semibold text-right">Dibayar</th>
                                <th class="py-3 px-4 font-semibold text-right">Sisa</th>
                                <th class="py-3 px-4 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['unpaidOrdersPaginated'] as $order)
                                <tr class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50/50">
                                    <td class="py-3 px-4">
                                        <a href="{{ route('orders.show', $order) }}" class="font-semibold text-primary-700 hover:underline">№ {{ $order->order_number }}</a>
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700">{{ $order->customer?->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-600">{{ format_rupiah((float) $order->total_price) }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-600">{{ format_rupiah((float) $order->paid_amount) }}</td>
                                    <td class="py-3 px-4 text-right font-semibold text-danger-700">{{ format_rupiah($order->remainingAmount) }}</td>
                                    <td class="py-3 px-4">
                                        <x-badge :status="\App\Models\Order::paymentLabel($order->payment_status)">{{ \App\Models\Order::paymentLabel($order->payment_status) }}</x-badge>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-neutral-500">Semua pesanan lunas pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$data['unpaidOrdersPaginated']" label="pesanan" />
            </x-card>

            {{-- Dibatalkan --}}
            <x-card>
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-neutral-900">Pesanan Dibatalkan</h3>
                    <p class="mt-0.5 text-sm text-neutral-600">
                        {{ $data['cancelledOrders']->count() }} pesanan · nilai tagihan
                        <span class="font-semibold text-danger-700">{{ format_rupiah($data['cancelledOrders']->sum('total_price')) }}</span>
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[560px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100 bg-neutral-50">
                                <th class="py-3 px-4 font-semibold">Nomor</th>
                                <th class="py-3 px-4 font-semibold">Pelanggan</th>
                                <th class="py-3 px-4 font-semibold">Alasan</th>
                                <th class="py-3 px-4 font-semibold">Oleh</th>
                                <th class="py-3 px-4 font-semibold text-right">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['cancelledOrdersPaginated'] as $order)
                                <tr class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50/50">
                                    <td class="py-3 px-4">
                                        <a href="{{ route('orders.show', $order) }}" class="font-semibold text-primary-700 hover:underline">№ {{ $order->order_number }}</a>
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700">{{ $order->customer?->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-neutral-600">{{ $order->cancellation_reason }}</td>
                                    <td class="py-3 px-4 text-neutral-600">{{ $order->cancelledBy?->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-right text-xs text-neutral-500">{{ format_datetime_wib($order->cancelled_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-neutral-500">Tidak ada pembatalan pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$data['cancelledOrdersPaginated']" label="pesanan" />
            </x-card>

            {{-- Riwayat Refund --}}
            <x-card>
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-neutral-900">Riwayat Refund</h3>
                    <p class="mt-0.5 text-sm text-neutral-600">
                        Total refund
                        <span class="font-semibold text-danger-700">{{ format_rupiah($data['refunds']->sum('amount')) }}</span>
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[380px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100 bg-neutral-50">
                                <th class="py-3 px-4 font-semibold">Nomor</th>
                                <th class="py-3 px-4 font-semibold text-right">Nominal</th>
                                <th class="py-3 px-4 font-semibold">Alasan</th>
                                <th class="py-3 px-4 font-semibold">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['refundsPaginated'] as $refund)
                                <tr class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50/50">
                                    <td class="py-3 px-4">
                                        <a href="{{ route('orders.show', $refund->order) }}" class="font-semibold text-primary-700 hover:underline">№ {{ $refund->order?->order_number ?? '-' }}</a>
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold text-danger-700">{{ format_rupiah((float) $refund->amount) }}</td>
                                    <td class="py-3 px-4 text-neutral-600">{{ $refund->reason }}</td>
                                    <td class="py-3 px-4 text-xs text-neutral-500">{{ format_datetime_wib($refund->refunded_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-neutral-500">Tidak ada refund pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$data['refundsPaginated']" label="refund" />
            </x-card>
        @elseif ($tab === 'pengiriman')
            {{-- Ringkasan Pengiriman --}}
            <div class="space-y-3">
                <h3 class="text-base font-semibold text-neutral-900">Ringkasan Pengiriman</h3>
            </div>

            {{-- Pickup & delivery --}}
            <div class="grid gap-4 lg:grid-cols-3">
                <x-card class="h-full flex flex-col lg:col-span-1">
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-neutral-900">Pickup & Delivery</h3>
                    </div>
                    <div class="grid flex-1 min-h-0 grid-cols-1 grid-rows-2 gap-4">
                        <x-stat-card class="h-full" label="Akan Diambil" :value="$data['ordersByMethod']['diambil']" />
                        <x-stat-card class="h-full" label="Akan Diantar" :value="$data['ordersByMethod']['diantar']" />
                    </div>
                </x-card>

                <x-card class="lg:col-span-2">
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-neutral-900">Serah Terima</h3>
                        <p class="mt-0.5 text-sm text-neutral-600">{{ $data['handedOverOrders']->count() }} pesanan</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[320px]">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100 bg-neutral-50">
                                    <th class="py-3 px-4 font-semibold">Nomor</th>
                                    <th class="py-3 px-4 font-semibold">Pelanggan</th>
                                    <th class="py-3 px-4 font-semibold">Serah Terima</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data['handedOverOrdersPaginated'] as $order)
                                    <tr class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50/50">
                                        <td class="py-3 px-4">
                                            <a href="{{ route('orders.show', $order) }}" class="font-semibold text-primary-700 hover:underline">№ {{ $order->order_number }}</a>
                                        </td>
                                        <td class="py-3 px-4 text-neutral-700">{{ $order->customer?->name ?? '-' }}</td>
                                        <td class="py-3 px-4">
                                            <x-badge :status="\App\Models\Order::handoverLabel($order->handover_status)">{{ \App\Models\Order::handoverLabel($order->handover_status) }}</x-badge>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-8 text-center text-neutral-500">Belum ada pesanan diserahterimakan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <x-pagination :paginator="$data['handedOverOrdersPaginated']" label="pesanan" />
                </x-card>
            </div>

            <x-card>
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-neutral-900">Delivery Gagal</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[320px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100 bg-neutral-50">
                                <th class="py-3 px-4 font-semibold">Nomor</th>
                                <th class="py-3 px-4 font-semibold">Alasan</th>
                                <th class="py-3 px-4 font-semibold">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['failedDeliveriesPaginated'] as $event)
                                <tr class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50/50">
                                    <td class="py-3 px-4">
                                        <a href="{{ route('orders.show', $event->order) }}" class="font-semibold text-primary-700 hover:underline">№ {{ $event->order?->order_number ?? '-' }}</a>
                                    </td>
                                    <td class="py-3 px-4 text-neutral-600">{{ $event->failure_reason }}</td>
                                    <td class="py-3 px-4 text-xs text-neutral-500">{{ format_datetime_wib($event->occurred_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-8 text-center text-neutral-500">Tidak ada delivery gagal.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$data['failedDeliveriesPaginated']" label="kejadian" />
            </x-card>
        @elseif ($tab === 'aktivitas')
            <div class="space-y-3">
                <h3 class="text-base font-semibold text-neutral-900">Ringkasan Aktivitas Staf</h3>
            </div>
            <x-card>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[860px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100 bg-neutral-50">
                                <th class="py-3 px-4 font-semibold">Staf</th>
                                <th class="py-3 px-4 font-semibold text-right">Pesanan Dibuat</th>
                                <th class="py-3 px-4 font-semibold text-right">Transisi Status</th>
                                <th class="py-3 px-4 font-semibold text-right">Serah Terima</th>
                                <th class="py-3 px-4 font-semibold text-right">Bayar</th>
                                <th class="py-3 px-4 font-semibold text-right">Nilai Bayar</th>
                                <th class="py-3 px-4 font-semibold text-right">Refund</th>
                                <th class="py-3 px-4 font-semibold text-right">Nilai Refund</th>
                                <th class="py-3 px-4 font-semibold text-right">Batal/Pulih</th>
                                <th class="py-3 px-4 font-semibold text-right">Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['staffActivityPaginated'] as $entry)
                                <tr class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50/50">
                                    <td class="py-3 px-4">
                                        <p class="font-medium text-neutral-800">{{ $entry['user']->name }}</p>
                                        <p class="text-xs text-neutral-500">{{ $entry['user']->roleLabel() }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-right text-neutral-700">{{ $entry['orders_created'] }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-700">{{ $entry['status_changes'] }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-700">{{ $entry['handovers'] }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-700">{{ $entry['payments_count'] }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-700">{{ format_rupiah($entry['payments_total']) }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-700">{{ $entry['refunds_count'] }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-700">{{ format_rupiah($entry['refunds_total']) }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-700">{{ $entry['cancellations'] }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-700">{{ $entry['logins'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="py-8 text-center text-neutral-500">Tidak ada data aktivitas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$data['staffActivityPaginated']" label="staf" />
            </x-card>
        @endif
    @endif
</div>
