@props(['title' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-neutral-100 shadow-sm p-4 sm:p-6']) }}>
    @if ($title)
        <h2 class="text-lg font-bold text-primary-900 mb-4">{{ $title }}</h2>
    @endif
    {{ $slot }}
</div>