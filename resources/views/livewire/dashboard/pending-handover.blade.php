<div class="space-y-3">
    @forelse ($this->pendingOrders as $order)
        @php
            $methodLabel = \App\Models\Order::methodLabel($order->method);
        @endphp
        <div class="rounded-lg border border-neutral-200 bg-white p-4 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-neutral-500">№ {{ $order->order_number }}</p>
                    <p class="font-semibold text-neutral-950">{{ $order->customer->name }}</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->method === \App\Models\Order::METHOD_DIANTAR ? 'bg-info-100 text-info-700' : 'bg-neutral-100 text-neutral-700' }}">
                            {{ $methodLabel }}
                        </span>
                        @if ($order->estimated_completion_at)
                            <span class="text-xs text-neutral-500">Selesai: {{ format_date($order->estimated_completion_at) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                @if ($order->method === \App\Models\Order::METHOD_DIANTAR)
                    <button
                        type="button"
                        wire:click="startHandover({{ $order->id }}, 'sudah_diantar')"
                        class="inline-flex items-center justify-center rounded-lg bg-primary-700 px-3 py-2.5 min-h-12 text-sm font-semibold text-white transition-colors hover:bg-primary-800"
                    >
                        Tandai Sudah Diantar
                    </button>
                    <button
                        type="button"
                        wire:click="startHandover({{ $order->id }}, 'gagal')"
                        class="inline-flex items-center justify-center rounded-lg bg-white border border-neutral-300 px-3 py-2.5 min-h-12 text-sm font-semibold text-neutral-700 transition-colors hover:bg-neutral-50"
                    >
                        Catat Gagal Delivery
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="startHandover({{ $order->id }}, 'sudah_diambil')"
                        class="inline-flex items-center justify-center rounded-lg bg-primary-700 px-3 py-2.5 min-h-12 text-sm font-semibold text-white transition-colors hover:bg-primary-800"
                    >
                        Tandai Sudah Diambil
                    </button>
                @endif
            </div>
        </div>
    @empty
        <div class="py-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-8 w-8 text-neutral-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 12l2 2 4-4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
            </svg>
            <p class="mt-2 text-neutral-500">Tidak ada pesanan yang menunggu serah terima.</p>
        </div>
    @endforelse

    @if ($this->pendingOrders->isNotEmpty())
        <div class="pt-1 text-right">
            <a href="{{ route('orders') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-700 hover:text-primary-800 min-h-10">
                Lihat semua
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </a>
        </div>
    @endif

    @include('partials.handover-modal')
</div>