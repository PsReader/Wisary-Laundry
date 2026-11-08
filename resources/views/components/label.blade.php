@props([
    'for' => null,
    'required' => false,
])

<label {{ $attributes->merge(['for' => $for])->class(['block text-sm font-semibold text-neutral-950']) }}>
    {{ $slot }}
    @if ($required)
        <span class="text-danger-700" aria-hidden="true"> *</span>
    @endif
</label>
