@props([
    'label' => null,
    'required' => false,
    'error' => null,
    'helptext' => null,
    'name' => null,
    'id' => null,
])

@php
    // Same label/field linkage as x-input: derive the id from the wire:model
    // binding when no name/id is given.
    $wireModel = $attributes->get('wire:model') ?: $attributes->get('wire:model.live') ?: $attributes->get('wire:model.blur') ?: $attributes->get('wire:model.live.debounce.300ms');
    $fieldId = $id ?? $name ?? (is_string($wireModel) ? $wireModel : null);
    $base = 'w-full rounded-lg border bg-white px-3 py-2.5 text-base sm:text-sm text-neutral-950 min-h-12 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-0 transition';
    $bordered = $error ? ' border-danger-700 focus:ring-danger-700' : ' border-neutral-300 focus:ring-primary-700';
@endphp

<div {{ $attributes->only(['class'])->merge(['class' => 'w-full']) }}>
    @if ($label)
        <x-label for="{{ $fieldId }}" :required="$required">{{ $label }}</x-label>
    @endif

    <textarea name="{{ $name }}" id="{{ $fieldId }}" {{ $attributes->except(['class', 'label', 'required', 'error', 'helptext', 'name', 'id']) }} class="{{ $base . $bordered . ($label ? ' mt-1.5' : '') }}">{{ $slot }}</textarea>

    @if ($helptext)
        <p class="mt-1 text-xs text-neutral-500">{{ $helptext }}</p>
    @endif

    @if ($error)
        <p class="mt-1 text-sm text-danger-700" role="alert">{{ $error }}</p>
    @endif
</div>
