@props(['type' => 'success', 'title' => null])

@php
    $types = [
        'success' => 'bg-success-700',
        'error' => 'bg-danger-700',
        'warning' => 'bg-warning-700',
        'info' => 'bg-info-700',
    ];
    $text = match ($type) {
        'error' => 'bg-danger-700',
        'warning' => 'bg-warning-700',
        'info' => 'bg-info-700',
        default => 'bg-success-700',
    };
@endphp

<div
    data-toast
    {{ $attributes->merge(['class' => 'fixed top-4 right-4 z-50 hidden rounded-lg ' . $text . ' text-white text-sm font-medium px-4 py-3 shadow-lg']) }}
    role="status"
>
    {{ $slot }}
</div>