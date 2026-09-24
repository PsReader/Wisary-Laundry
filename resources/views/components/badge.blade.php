@props(['status' => null])

@php
    $styles = [
        'Diterima' => 'bg-primary-100 text-primary-800',
        'Sedang Dicuci' => 'bg-info-100 text-info-700',
        'Sedang Dikeringkan' => 'bg-info-100 text-info-700',
        'Sedang Disetrika' => 'bg-info-100 text-info-700',
        'Sedang Dilipat' => 'bg-info-100 text-info-700',
        'Siap Diambil' => 'bg-success-100 text-success-700',
        'Dibatalkan' => 'bg-danger-100 text-danger-700',
        'Belum Lunas' => 'bg-warning-100 text-warning-700',
        'Lunas Sebagian' => 'bg-warning-100 text-warning-700',
        'Lunas' => 'bg-success-100 text-success-700',
        'Dikembalikan' => 'bg-danger-100 text-danger-700',
        'Belum Diserahkan' => 'bg-neutral-100 text-neutral-700',
        'Sudah Diambil' => 'bg-success-100 text-success-700',
        'Sudah Diantar' => 'bg-info-100 text-info-700',
    ];

    $color = $status !== null ? ($styles[$status] ?? 'bg-neutral-100 text-neutral-700') : (string) $attributes->get('color', 'bg-neutral-100 text-neutral-700');
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ' . $color]) }}>
    {{ $slot }}
</span>