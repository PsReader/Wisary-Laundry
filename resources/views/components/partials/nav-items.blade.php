@props([
    'active' => 'dashboard',
    'mobile' => false,
])

@php
    $items = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
        ['route' => 'orders.create', 'label' => 'Buat Pesanan', 'icon' => 'M12 5v14M5 12h14'],
        ['route' => 'orders', 'label' => 'Daftar Pesanan', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
        ['route' => 'customers', 'label' => 'Pelanggan', 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM16 3.13a4 4 0 0 1 0 7.75'],
        ['route' => 'services', 'label' => 'Layanan & Harga', 'icon' => 'M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83zM7 7h.01'],
        ['route' => 'reports', 'label' => 'Laporan', 'icon' => 'M4 19V5M4 19h16M8 15v-3M12 15V9M16 15v-5', 'super_admin' => true],
        ['route' => 'audit-logs', 'label' => 'Audit Log', 'icon' => 'M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z', 'super_admin' => true],
        ['route' => 'staff', 'label' => 'Manajemen Staf', 'icon' => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'super_admin' => true],
    ];

    if (auth()->user() && ! auth()->user()->isSuperAdmin()) {
        $items = array_values(array_filter($items, fn ($item) => empty($item['super_admin'])));
    }
@endphp

<nav class="flex-1 space-y-1 px-2 py-4">
    @foreach ($items as $item)
        <x-nav-link
            :href="route($item['route'])"
            :active="request()->routeIs($item['route'])"
            :mobile="$mobile"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="{{ $item['icon'] }}"/>
            </svg>
            {{ $item['label'] }}
        </x-nav-link>
    @endforeach
</nav>