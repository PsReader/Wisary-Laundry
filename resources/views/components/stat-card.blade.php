@props([
    'label' => null,
    'value' => null,
    'indicator' => null,
    'icon' => null,
    'tone' => 'primary',
    'solid' => false,
])

@php
    $tones = [
        'primary' => ['dot' => 'bg-primary-700', 'text' => 'text-primary-700', 'iconBg' => 'bg-primary-50 text-primary-700'],
        'success' => ['dot' => 'bg-success-700', 'text' => 'text-success-700', 'iconBg' => 'bg-success-50 text-success-700'],
        'warning' => ['dot' => 'bg-warning-700', 'text' => 'text-warning-700', 'iconBg' => 'bg-warning-50 text-warning-700'],
        'danger' => ['dot' => 'bg-danger-700', 'text' => 'text-danger-700', 'iconBg' => 'bg-danger-50 text-danger-700'],
        'info' => ['dot' => 'bg-info-700', 'text' => 'text-info-700', 'iconBg' => 'bg-info-50 text-info-700'],
        'neutral' => ['dot' => 'bg-neutral-400', 'text' => 'text-neutral-600', 'iconBg' => 'bg-neutral-100 text-neutral-600'],
    ];
    $t = $tones[$tone] ?? $tones['primary'];
@endphp

<div {{ $attributes->merge(['class' => ($solid ? 'bg-primary-700 border-primary-700' : 'bg-white border-neutral-100').' rounded-xl border shadow-sm p-4 sm:p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <span class="min-w-0 text-sm font-semibold {{ $solid ? 'text-primary-100' : 'text-neutral-700' }}">{{ $label }}</span>
        @if ($icon)
            <span class="inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-full {{ $solid ? 'bg-white/15 text-white' : $t['iconBg'] }}" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="{{ $icon }}"/>
                </svg>
            </span>
        @endif
    </div>
    <p class="mt-2 text-2xl sm:text-3xl font-bold {{ $solid ? 'text-white' : 'text-primary-900' }}">{{ $value }}</p>
    @if ($indicator)
        <p class="mt-2 flex items-center gap-1.5 text-xs font-medium {{ $solid ? 'text-primary-100' : ($indicator['class'] ?? $t['text']) }}">
            <span class="h-2 w-2 rounded-full {{ $solid ? 'bg-primary-100' : ($indicator['dot'] ?? $t['dot']) }}"></span>
            {{ $indicator['text'] ?? '' }}
        </p>
    @endif
</div>