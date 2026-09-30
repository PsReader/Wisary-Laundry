@props(['type' => 'success'])

@php
    $text = match ($type) {
        'error' => 'bg-danger-700',
        'warning' => 'bg-warning-700',
        'info' => 'bg-info-700',
        default => 'bg-success-700',
    };
@endphp

{{-- Error/warning announced assertively; success/info stay polite. Error toasts are also
     exempted from auto-dismiss in app.js so the cause stays readable until dismissed. --}}
<div
    data-toast
    data-toast-type="{{ $type }}"
    {{ $attributes->merge(['class' => 'fixed top-4 right-4 z-50 hidden rounded-lg ' . $text . ' text-white text-sm font-medium px-4 py-3 shadow-lg']) }}
    role="{{ in_array($type, ['error', 'warning'], true) ? 'alert' : 'status' }}"
>
    {{ $slot }}
</div>
