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
    // Mirrors x-input: derive the id from the wire:model binding when no
    // name/id is given, so <label for> always resolves to a real control.
    $wireModel = $attributes->get('wire:model') ?: $attributes->get('wire:model.live') ?: $attributes->get('wire:model.blur') ?: $attributes->get('wire:model.live.debounce.300ms');
    $fieldId = $id ?? $name ?? (is_string($wireModel) ? $wireModel : null);
    $base = 'w-full rounded-lg border bg-white px-3 py-2.5 text-base sm:text-sm text-neutral-950 min-h-12 focus:outline-none focus:ring-2 focus:ring-offset-0 transition';
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
        <p class="mt-1 text-sm text-danger-700" role="alert">{{ $error }}</p>
    @endif
</div>