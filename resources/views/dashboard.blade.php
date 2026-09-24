@php
    use App\Models\Order;

    $todayStart = now()->startOfDay();
    $now = now();

    $kpi = [
        'ordersToday' => Order::where('received_at', '>=', $todayStart)->count(),
        'inProgress' => Order::whereIn('status', [
            Order::STATUS_DITERIMA,
            Order::STATUS_SEDANG_DICUCI,
            Order::STATUS_SEDANG_DIKERINGKAN,
            Order::STATUS_SEDANG_DISETRIKA,
            Order::STATUS_SEDANG_DILIPAT,
        ])->count(),
        'readyForPickup' => Order::where('status', Order::STATUS_SIAP_DIAMBIL)
            ->where('handover_status', Order::HANDOVER_BELUM_DISERAHKAN)
            ->count(),
        'delivery' => Order::where('method', Order::METHOD_DIANTAR)
            ->where('handover_status', Order::HANDOVER_BELUM_DISERAHKAN)
            ->where('status', '!=', Order::STATUS_DIBATALKAN)
            ->count(),
        'unpaid' => Order::whereIn('payment_status', [Order::PAYMENT_BELUM_LUNAS, Order::PAYMENT_LUNAS_SEBAGIAN])
            ->where('status', '!=', Order::STATUS_DIBATALKAN)
            ->count(),
        'late' => Order::where('estimated_completion_at', '<', $now)
            ->where('handover_status', Order::HANDOVER_BELUM_DISERAHKAN)
            ->where('status', '!=', Order::STATUS_DIBATALKAN)
            ->count(),
        'revenueToday' => \App\Models\Payment::where('received_at', '>=', $todayStart)
            ->sum('amount'),
        'notHandedOver' => Order::where('handover_status', Order::HANDOVER_BELUM_DISERAHKAN)
            ->where('status', '!=', Order::STATUS_DIBATALKAN)
            ->count(),
    ];
@endphp

<x-app-layout title="Dasbor" :breadcrumbs="[['label' => 'Dasbor']]">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-primary-900">Ringkasan {{ format_date($todayStart) }}</h2>
        </div>
        <a href="{{ route('orders.create') }}" class="inline-flex items-center justify-center rounded-lg bg-primary-700 px-4 py-2.5 min-h-12 text-sm font-semibold text-white transition-colors hover:bg-primary-800">
            Buat Pesanan
        </a>
    </div>

    <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <x-stat-card
            label="Pesanan Hari Ini"
            :value="$kpi['ordersToday']"
            icon="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"
            tone="primary"
        />
        <x-stat-card
            label="Sedang Diproses"
            :value="$kpi['inProgress']"
            icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
            tone="info"
        />
        <x-stat-card
            label="Siap Diambil"
            :value="$kpi['readyForPickup']"
            icon="M9 12l2 2 4-4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"
            tone="success"
            :indicator="['text' => 'Belum Diserahkan', 'dot' => 'bg-warning-700', 'class' => 'text-warning-700']"
        />
        <x-stat-card
            label="Delivery"
            :value="$kpi['delivery']"
            icon="M21 8v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10M21 8l-5-5v5M9 21v-2a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"
            tone="info"
        />
        <x-stat-card
            label="Belum Lunas"
            :value="$kpi['unpaid']"
            icon="M19 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2zM16 12h.01"
            tone="warning"
            :indicator="['text' => 'Perlu tindakan', 'dot' => 'bg-danger-700', 'class' => 'text-danger-700']"
        />
        <x-stat-card
            label="Terlambat"
            :value="$kpi['late']"
            icon="M12 8h.01M12 12v4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"
            tone="danger"
            :indicator="['text' => 'Lewat estimasi', 'dot' => 'bg-danger-700', 'class' => 'text-danger-700']"
        />
        <x-stat-card
            class="col-span-2 lg:col-span-1"
            label="Pendapatan Hari Ini"
            value="{{ format_rupiah((float) $kpi['revenueToday']) }}"
            icon="M2 6h20v12H2zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM6 9h.01M18 15h.01"
            solid
        />
        <x-stat-card
            class="col-span-2 lg:col-span-1"
            label="Menunggu Serah Terima"
            :value="$kpi['notHandedOver']"
            icon="M21 8l-9-5-9 5v8l9 5 9-5V8zM3 8l9 5 9-5M12 13v8"
            tone="neutral"
        />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card title="Perlu Ditindaklanjuti — Menunggu Serah Terima ({{ $kpi['readyForPickup'] }})">
            <livewire:dashboard.pending-handover />
        </x-card>

        <x-card title="Perlu Ditindaklanjuti — Belum Lunas ({{ $kpi['unpaid'] }})">
            <livewire:dashboard.pending-payment />
        </x-card>
    </div>
</x-app-layout>