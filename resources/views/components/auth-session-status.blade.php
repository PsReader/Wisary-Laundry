@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-success-700 bg-success-100 border border-success-200 rounded-xl px-4 py-3']) }}>
        {{ $status }}
    </div>
@endif
