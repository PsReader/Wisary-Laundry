@props(['paginator', 'label' => 'items'])

@if ($paginator->total() > 0)
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4">
        <p class="text-xs text-neutral-500">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} {{ $label }}
        </p>
        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="previousPage"
                @disabled($paginator->onFirstPage())
                class="inline-flex items-center justify-center px-4 py-2.5 min-h-12 text-sm font-medium rounded-lg border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                Sebelumnya
            </button>
            <button
                type="button"
                wire:click="nextPage"
                @disabled(! $paginator->hasMorePages())
                class="inline-flex items-center justify-center px-4 py-2.5 min-h-12 text-sm font-medium rounded-lg border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                Berikutnya
            </button>
        </div>
    </div>
@endif
