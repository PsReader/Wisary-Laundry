<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-primary-900">Laporan Operasional</h2>
            @if ($range !== null)
                <p class="text-xs text-neutral-500">Periode {{ format_date($range[0]) }} — {{ format_date($range[1]) }}</p>
            @endif
        </div>
    </div>

    {{-- Filter periode --}}
    <x-card>
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="w-full lg:w-auto">
                <label class="block text-xs font-semibold text-neutral-600 mb-1.5">Periode</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="setPeriod('today')"
                        class="inline-flex items-center justify-center rounded-lg bg-primary-700 px-3 py-2 min-h-12 text-xs font-semibold text-white transition-colors hover:bg-primary-800">Hari Ini</button>
                    <button type="button" wire:click="setPeriod('week')"
                        class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-3 py-2 min-h-12 text-xs font-semibold text-neutral-700 transition-colors hover:bg-neutral-50">7 Hari</button>
                    <button type="button" wire:click="setPeriod('month')"
                        class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-3 py-2 min-h-12 text-xs font-semibold text-neutral-700 transition-colors hover:bg-neutral-50">Bulan Ini</button>
                    <button type="button" wire:click="setPeriod('last_month')"
                        class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-3 py-2 min-h-12 text-xs font-semibold text-neutral-700 transition-colors hover:bg-neutral-50">Bulan Lalu</button>
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

        {{-- Ekspor --}}
        <div class="mt-4 border-t border-neutral-100 pt-4">
            <p class="text-xs font-semibold text-neutral-600 mb-2">Ekspor Data (CSV)</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.export.orders', ['from' => $from, 'to' => $to]) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-3 py-2 min-h-12 text-xs font-semibold text-neutral-700 transition-colors hover:bg-neutral-50">Pesanan</a>
                <a href="{{ route('reports.export.payments', ['from' => $from, 'to' => $to]) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-3 py-2 min-h-12 text-xs font-semibold text-neutral-700 transition-colors hover:bg-neutral-50">Pembayaran</a>
                <a href="{{ route('reports.export.customers') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-neutral-300 bg-white px-3 py-2 min-h-12 text-xs font-semibold text-neutral-700 transition-colors hover:bg-neutral-50">Pelanggan</a>
            </div>
        </div>
    </x-card>

    @if ($range === null || $data === null)
        <x-card>
            <p class="py-6 text-center text-sm text-neutral-500">Pilih periode laporan untuk melihat datanya.</p>
        </x-card>
    @else
        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card label="Total Pesanan" :value="$data['ordersByStatus']->sum()" />
            <x-stat-card label="Pendapatan" value="{{ format_rupiah($data['revenueTotal']) }}" />
            <x-stat-card label="Belum Lunas" :value="$data['unpaidOrders']->count()" />
            <x-stat-card label="Dibatalkan" :value="$data['cancelledOrders']->count()" />
        </div>

        {{-- Jumlah pesanan per status + per hari --}}
        <x-card title="Jumlah Pesanan">
            <div class="grid gap-6 lg:grid-cols-2">
                <div>
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
                </div>
                <div>
                    <h3 class="mb-2 text-base font-semibold text-neutral-700">Per Hari</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[240px]">
                            <tbody>
                                @forelse ($data['ordersByDay'] as $day => $count)
                                    <tr class="border-b border-neutral-100 last:border-0">
                                        <td class="py-2.5 pr-4 text-neutral-700">{{ format_date(\Carbon\Carbon::parse($day)) }}</td>
                                        <td class="py-2.5 text-right font-semibold text-primary-900">{{ $count }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-6 text-center text-neutral-500">Tidak ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Pendapatan --}}
        <x-card title="Pendapatan (Pembayaran Diterima)">
            <div class="grid gap-6 lg:grid-cols-3">
                <div>
                    <h3 class="mb-2 text-base font-semibold text-neutral-700">Harian</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[200px]">
                            <tbody>
                                @forelse ($data['revenueByDay'] as $day => $amount)
                                    <tr class="border-b border-neutral-100 last:border-0">
                                        <td class="py-2.5 pr-4 text-neutral-700">{{ format_date(\Carbon\Carbon::parse($day)) }}</td>
                                        <td class="py-2.5 text-right font-semibold text-primary-900">{{ format_rupiah($amount) }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-6 text-center text-neutral-500">Tidak ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h3 class="mb-2 text-base font-semibold text-neutral-700">Mingguan</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[200px]">
                            <tbody>
                                @forelse ($data['revenueByWeek'] as $week => $amount)
                                    <tr class="border-b border-neutral-100 last:border-0">
                                        <td class="py-2.5 pr-4 text-neutral-700">Mulai {{ format_date(\Carbon\Carbon::parse($week)) }}</td>
                                        <td class="py-2.5 text-right font-semibold text-primary-900">{{ format_rupiah($amount) }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-6 text-center text-neutral-500">Tidak ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h3 class="mb-2 text-base font-semibold text-neutral-700">Bulanan</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[120px]">
                            <tbody>
                                @forelse ($data['revenueByMonth'] as $month => $amount)
                                    <tr class="border-b border-neutral-100 last:border-0">
                                        <td class="py-2.5 pr-4 text-neutral-700">{{ \Carbon\Carbon::parse($month)->locale('id')->translatedFormat('F Y') }}</td>
                                        <td class="py-2.5 text-right font-semibold text-primary-900">{{ format_rupiah($amount) }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-6 text-center text-neutral-500">Tidak ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Layanan terlaris --}}
        <x-card title="Layanan Paling Sering Digunakan">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[480px]">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                            <th class="py-3 pr-4 font-semibold">Layanan</th>
                            <th class="py-3 pr-4 font-semibold text-right">Pesanan</th>
                            <th class="py-3 pr-4 font-semibold text-right">Qty / Kg</th>
                            <th class="py-3 font-semibold text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['topServices'] as $service)
                            <tr class="border-b border-neutral-100 last:border-0">
                                <td class="py-3 pr-4 font-medium text-neutral-800">{{ $service['name'] }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-600">{{ $service['order_count'] }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-600">
                                    @if ($service['quantity'] > 0)
                                        {{ number_format($service['quantity'], 0, ',', '.') }} unit
                                    @else
                                        {{ number_format($service['weight'], 1, ',', '.') }} kg
                                    @endif
                                </td>
                                <td class="py-3 text-right font-semibold text-primary-900">{{ format_rupiah($service['subtotal']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-neutral-500">Belum ada layanan digunakan pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Belum lunas --}}
        <x-card title="Pesanan Belum Lunas">
            <p class="mb-3 text-sm text-neutral-600">
                {{ $data['unpaidOrders']->count() }} pesanan · sisa tagihan total
                <span class="font-semibold text-primary-900">{{ format_rupiah($data['unpaidOrders']->sum(fn ($o) => $o->remainingAmount)) }}</span>
            </p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[560px]">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                            <th class="py-3 pr-4 font-semibold">Nomor</th>
                            <th class="py-3 pr-4 font-semibold">Pelanggan</th>
                            <th class="py-3 pr-4 font-semibold text-right">Total</th>
                            <th class="py-3 pr-4 font-semibold text-right">Dibayar</th>
                            <th class="py-3 pr-4 font-semibold text-right">Sisa</th>
                            <th class="py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['unpaidOrders'] as $order)
                            <tr class="border-b border-neutral-100 last:border-0">
                                <td class="py-3 pr-4">
                                    <a href="{{ route('orders.show', $order) }}" class="font-semibold text-primary-700 hover:underline">№ {{ $order->order_number }}</a>
                                </td>
                                <td class="py-3 pr-4 text-neutral-700">{{ $order->customer?->name ?? '-' }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-600">{{ format_rupiah((float) $order->total_price) }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-600">{{ format_rupiah((float) $order->paid_amount) }}</td>
                                <td class="py-3 pr-4 text-right font-semibold text-danger-700">{{ format_rupiah($order->remainingAmount) }}</td>
                                <td class="py-3">
                                    <x-badge :status="\App\Models\Order::paymentLabel($order->payment_status)">{{ \App\Models\Order::paymentLabel($order->payment_status) }}</x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-neutral-500">Semua pesanan lunas pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Pickup & delivery --}}
        <div class="grid gap-4 lg:grid-cols-2">
            <x-card title="Pickup & Delivery">
                <div class="grid grid-cols-2 gap-4">
                    <x-stat-card label="Diambil" :value="$data['ordersByMethod']['diambil']" />
                    <x-stat-card label="Diantar" :value="$data['ordersByMethod']['diantar']" />
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm min-w-[320px]">
                        <tbody>
                            @forelse ($data['handoverByAction'] as $action => $count)
                                <tr class="border-b border-neutral-100 last:border-0">
                                    <td class="py-2.5 pr-4 text-neutral-700">{{ \App\Models\OrderHandoverHistory::actionLabel($action) }}</td>
                                    <td class="py-2.5 text-right font-semibold text-primary-900">{{ $count }}</td>
                                </tr>
                            @empty
                                <tr><td class="py-6 text-center text-neutral-500">Belum ada serah terima pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <x-card title="Sudah Diserahterimakan">
                <p class="mb-3 text-sm text-neutral-600">{{ $data['handedOverOrders']->count() }} pesanan</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[320px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                                <th class="py-3 pr-4 font-semibold">Nomor</th>
                                <th class="py-3 pr-4 font-semibold">Pelanggan</th>
                                <th class="py-3 font-semibold">Serah Terima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['handedOverOrders'] as $order)
                                <tr class="border-b border-neutral-100 last:border-0">
                                    <td class="py-3 pr-4">
                                        <a href="{{ route('orders.show', $order) }}" class="font-semibold text-primary-700 hover:underline">№ {{ $order->order_number }}</a>
                                    </td>
                                    <td class="py-3 pr-4 text-neutral-700">{{ $order->customer?->name ?? '-' }}</td>
                                    <td class="py-3">
                                        <x-badge :status="\App\Models\Order::handoverLabel($order->handover_status)">{{ \App\Models\Order::handoverLabel($order->handover_status) }}</x-badge>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-8 text-center text-neutral-500">Belum ada pesanan diserahterimakan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        {{-- Dibatalkan --}}
        <x-card title="Pesanan Dibatalkan">
            <p class="mb-3 text-sm text-neutral-600">
                {{ $data['cancelledOrders']->count() }} pesanan · nilai tagihan
                <span class="font-semibold text-danger-700">{{ format_rupiah($data['cancelledOrders']->sum('total_price')) }}</span>
            </p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[560px]">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                            <th class="py-3 pr-4 font-semibold">Nomor</th>
                            <th class="py-3 pr-4 font-semibold">Pelanggan</th>
                            <th class="py-3 pr-4 font-semibold">Alasan</th>
                            <th class="py-3 pr-4 font-semibold">Oleh</th>
                            <th class="py-3 font-semibold text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['cancelledOrders'] as $order)
                            <tr class="border-b border-neutral-100 last:border-0">
                                <td class="py-3 pr-4">
                                    <a href="{{ route('orders.show', $order) }}" class="font-semibold text-primary-700 hover:underline">№ {{ $order->order_number }}</a>
                                </td>
                                <td class="py-3 pr-4 text-neutral-700">{{ $order->customer?->name ?? '-' }}</td>
                                <td class="py-3 pr-4 text-neutral-600">{{ $order->cancellation_reason }}</td>
                                <td class="py-3 pr-4 text-neutral-600">{{ $order->cancelledBy?->name ?? '-' }}</td>
                                <td class="py-3 text-right text-xs text-neutral-500">{{ format_datetime_wib($order->cancelled_at) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-neutral-500">Tidak ada pembatalan pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Delivery gagal + Refund --}}
        <div class="grid gap-4 lg:grid-cols-2">
            <x-card title="Delivery Gagal">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[320px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                                <th class="py-3 pr-4 font-semibold">Nomor</th>
                                <th class="py-3 pr-4 font-semibold">Alasan</th>
                                <th class="py-3 font-semibold">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['failedDeliveries'] as $event)
                                <tr class="border-b border-neutral-100 last:border-0">
                                    <td class="py-3 pr-4">
                                        <a href="{{ route('orders.show', $event->order) }}" class="font-semibold text-primary-700 hover:underline">№ {{ $event->order?->order_number ?? '-' }}</a>
                                    </td>
                                    <td class="py-3 pr-4 text-neutral-600">{{ $event->failure_reason }}</td>
                                    <td class="py-3 text-xs text-neutral-500">{{ format_datetime_wib($event->occurred_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-8 text-center text-neutral-500">Tidak ada delivery gagal.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <x-card title="Riwayat Refund">
                <p class="mb-3 text-sm text-neutral-600">
                    Total refund
                    <span class="font-semibold text-danger-700">{{ format_rupiah($data['refunds']->sum('amount')) }}</span>
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[380px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                                <th class="py-3 pr-4 font-semibold">Nomor</th>
                                <th class="py-3 pr-4 font-semibold text-right">Nominal</th>
                                <th class="py-3 pr-4 font-semibold">Alasan</th>
                                <th class="py-3 font-semibold">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['refunds'] as $refund)
                                <tr class="border-b border-neutral-100 last:border-0">
                                    <td class="py-3 pr-4">
                                        <a href="{{ route('orders.show', $refund->order) }}" class="font-semibold text-primary-700 hover:underline">№ {{ $refund->order?->order_number ?? '-' }}</a>
                                    </td>
                                    <td class="py-3 pr-4 text-right font-semibold text-danger-700">{{ format_rupiah((float) $refund->amount) }}</td>
                                    <td class="py-3 pr-4 text-neutral-600">{{ $refund->reason }}</td>
                                    <td class="py-3 text-xs text-neutral-500">{{ format_datetime_wib($refund->refunded_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-neutral-500">Tidak ada refund pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        {{-- Aktivitas staf --}}
        <x-card title="Aktivitas Staf">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[860px]">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                            <th class="py-3 pr-4 font-semibold">Staf</th>
                            <th class="py-3 pr-4 font-semibold text-right">Pesanan Dibuat</th>
                            <th class="py-3 pr-4 font-semibold text-right">Transisi Status</th>
                            <th class="py-3 pr-4 font-semibold text-right">Serah Terima</th>
                            <th class="py-3 pr-4 font-semibold text-right">Bayar</th>
                            <th class="py-3 pr-4 font-semibold text-right">Nilai Bayar</th>
                            <th class="py-3 pr-4 font-semibold text-right">Refund</th>
                            <th class="py-3 pr-4 font-semibold text-right">Nilai Refund</th>
                            <th class="py-3 pr-4 font-semibold text-right">Batal/Pulih</th>
                            <th class="py-3 font-semibold text-right">Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['staffActivity'] as $entry)
                            <tr class="border-b border-neutral-100 last:border-0">
                                <td class="py-3 pr-4">
                                    <p class="font-medium text-neutral-800">{{ $entry['user']->name }}</p>
                                    <p class="text-xs text-neutral-500">{{ $entry['user']->roleLabel() }}</p>
                                </td>
                                <td class="py-3 pr-4 text-right text-neutral-700">{{ $entry['orders_created'] }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-700">{{ $entry['status_changes'] }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-700">{{ $entry['handovers'] }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-700">{{ $entry['payments_count'] }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-700">{{ format_rupiah($entry['payments_total']) }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-700">{{ $entry['refunds_count'] }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-700">{{ format_rupiah($entry['refunds_total']) }}</td>
                                <td class="py-3 pr-4 text-right text-neutral-700">{{ $entry['cancellations'] }}</td>
                                <td class="py-3 text-right text-neutral-700">{{ $entry['logins'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="py-8 text-center text-neutral-500">Tidak ada data aktivitas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif
</div>