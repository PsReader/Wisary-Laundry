@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'loading' => false,
    'block' => false,
])

@php
    $variants = [
        'primary' => 'bg-primary-700 text-white hover:bg-primary-600 focus-visible:ring-primary-700',
        'secondary' => 'bg-white text-neutral-950 border border-neutral-300 hover:bg-neutral-100 focus-visible:ring-neutral-500',
        'destructive' => 'bg-danger-700 text-white hover:bg-danger-600 focus-visible:ring-danger-700',
        'text' => 'bg-transparent text-primary-700 hover:bg-primary-50 focus-visible:ring-primary-700',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-5 py-3 text-base',
    ];

    $classes = implode(' ', [
        'inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition-colors',
        'min-h-12 lg:min-h-10',
        'disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:bg-opacity-100',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
        $block ? 'w-full' : '',
        $sizes[$size] ?? $sizes['md'],
        $variants[$variant] ?? $variants['primary'],
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <span class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" aria-hidden="true"></span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @disabled($loading)>
        @if ($loading)
            <span class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" aria-hidden="true"></span>
        @endif
        {{ $slot }}
    </button>
@endif