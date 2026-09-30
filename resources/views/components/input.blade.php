@props([
    'label' => null,
    'required' => false,
    'error' => null,
    'helptext' => null,
    'type' => 'text',
    'name' => null,
    'id' => null,
])

@php
    // Root fix for label/field linkage: most callers bind via wire:model with no
    // name/id. Derive a stable id from the binding so <label for> always points
    // at a real input (see x-select for the same logic).
    $wireModel = $attributes->get('wire:model') ?: $attributes->get('wire:model.live') ?: $attributes->get('wire:model.blur') ?: $attributes->get('wire:model.live.debounce.300ms');
    $fieldId = $id ?? $name ?? (is_string($wireModel) ? $wireModel : null);
    $base = 'w-full rounded-lg border bg-white px-3 py-2.5 text-base sm:text-sm text-neutral-950 min-h-12 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-0 transition';
    $bordered = $error ? ' border-danger-700 focus:ring-danger-700' : ' border-neutral-300 focus:ring-primary-700';
@endphp

<div {{ $attributes->only(['class'])->merge(['class' => 'w-full']) }}>
    @if ($label)
        <x-label for="{{ $fieldId }}" :required="$required">{{ $label }}</x-label>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $fieldId }}" {{ $attributes->except(['class', 'label', 'required', 'error', 'helptext', 'type', 'name', 'id']) }} class="{{ $base . $bordered }}">

    @if ($helptext)
        <p class="mt-1 text-xs text-neutral-500">{{ $helptext }}</p>
    @endif

    @if ($error)
        <p class="mt-1 text-sm text-danger-700" role="alert">{{ $error }}</p>
    @endif
</div>