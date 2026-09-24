@props(['active' => false, 'mobile' => false])

@php
$classes = $active
            ? 'flex w-full items-center gap-3 px-4 py-3 min-h-12 text-sm font-semibold text-white bg-primary-700/50 border-l-4 border-primary-400 rounded-lg transition-colors'
            : 'flex w-full items-center gap-3 px-4 py-3 min-h-12 text-sm font-medium text-primary-100 hover:bg-primary-700/40 hover:text-white border-l-4 border-transparent rounded-lg transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>