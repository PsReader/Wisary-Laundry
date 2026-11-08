<x-app-layout
    title="{{ $customer->name }}"
    :breadcrumbs="[['label' => 'Pelanggan', 'url' => route('customers')], ['label' => $customer->name]]"
>
    @php
        $orderCount = $customer->orders()->count();
        $activeOrders = $customer->orders()->where('status', '!=', \App\Models\Order::STATUS_DIBATALKAN);
        $totalValue = $activeOrders->sum('total_price');
        $unpaidCount = $activeOrders
            ->whereNotIn('payment_status', [\App\Models\Order::PAYMENT_LUNAS, \App\Models\Order::PAYMENT_DIKEMBALIKAN])
            ->count();
        $orders = $customer->orders()->with('items')->latest('received_at')->get();
    @endphp

    <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6">
        <x-stat-card label="Total Pesanan" :value="$orderCount" />
        <x-stat-card label="Total Nilai" :value="format_rupiah($totalValue)" :indicator="['text' => 'Tanpa batal', 'dot' => 'bg-success-700', 'class' => 'text-success-700']" />
        <x-stat-card label="Belum Lunas" :value="$unpaidCount" :indicator="['text' => 'Perlu tagih', 'dot' => 'bg-warning-700', 'class' => 'text-warning-700']" />
    </div>

    <x-card title="Informasi Pelanggan">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div>
                <dt class="text-xs uppercase tracking-wide font-semibold text-neutral-500">WhatsApp</dt>
                <dd class="mt-1 font-medium">
                    <a href="tel:+62{{ substr($customer->whatsapp, 1) }}" class="text-primary-700 hover:underline">{{ $customer->whatsapp }}</a>
                </dd>
            </div>
            <div>
                <dt class="text-xs uppercase tracking-wide font-semibold text-neutral-500">Alamat utama</dt>
                <dd class="mt-1 text-neutral-700">{{ $customer->address ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs uppercase tracking-wide font-semibold text-neutral-500">Catatan</dt>
                <dd class="mt-1 text-neutral-700 whitespace-pre-line">{{ $customer->notes ?? '—' }}</dd>
            </div>
        </dl>
    </x-card>

    <div class="mt-6">
        <x-card title="Riwayat Pesanan">
            @if ($orders->isEmpty())
                <div class="py-10 text-center">
                    <p class="text-neutral-500">Belum ada pesanan untuk pelanggan ini.</p>
                    <div class="mt-4 flex justify-center">
                        <x-button href="{{ route('orders.create') }}">Buat Pesanan</x-button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[560px]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                                <th class="py-3 pr-4 font-semibold">Tanggal</th>
                                <th class="py-3 pr-4 font-semibold">Nomor Pesanan</th>
                                <th class="py-3 pr-4 font-semibold">Layanan</th>
                                <th class="py-3 pr-4 font-semibold">Total</th>
                                <th class="py-3 pr-4 font-semibold">Status</th>
                                <th class="py-3 font-semibold">Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr class="border-b border-neutral-100 last:border-0">
                                    <td class="py-3 pr-4 text-neutral-600">{{ $order->received_at->translatedFormat('d M Y') }}</td>
                                    <td class="py-3 pr-4 font-medium">{{ $order->order_number }}</td>
                                    <td class="py-3 pr-4 text-neutral-600">
                                        @php
                                            $names = $order->items->map(fn ($item) => $item->service_name.($item->quantity > 1 ? ' (x'.$item->quantity.')' : ''))->values();
                                        @endphp
                                        {{ $names->take(2)->join(', ') }}{{ $names->count() > 2 ? ', …' : '' }}
                                    </td>
                                    <td class="py-3 pr-4 font-medium">{{ format_rupiah((float) $order->total_price) }}</td>
                                    <td class="py-3 pr-4"><x-badge :status="\App\Models\Order::statusLabel($order->status)" /></td>
                                    <td class="py-3"><x-badge :status="\App\Models\Order::paymentLabel($order->payment_status)" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>