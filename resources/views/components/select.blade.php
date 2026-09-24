@props([
    'label' => null,
    'required' => false,
    'error' => null,
    'name' => null,
    'id' => null,
    'options' => [],
    'placeholder' => null,
])

@php
    $fieldId = $id ?? $name;
    $base = 'w-full rounded-lg border bg-white px-3 py-2.5 text-base sm:text-sm text-neutral-950 min-h-12 lg:min-h-10 focus:outline-none focus:ring-2 focus:ring-offset-0 transition';
    $bordered = $error ? ' border-danger-700 focus:ring-danger-700' : ' border-neutral-300 focus:ring-primary-700';
@endphp

<div {{ $attributes->only(['class'])->merge(['class' => 'w-full']) }}>
    @if ($label)
        <x-label for="{{ $fieldId }}" :required="$required">{{ $label }}</x-label>
        <div class="mt-1.5"></div>
    @endif

    <select name="{{ $name }}" id="{{ $fieldId }}" {{ $attributes->except(['class', 'label', 'required', 'error', 'name', 'id', 'options', 'placeholder']) }} class="{{ $base . $bordered }}">
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>

    @if ($error)
        <p class="mt-1 text-xs text-danger-700" role="alert">{{ $error }}</p>
    @endif
</div>