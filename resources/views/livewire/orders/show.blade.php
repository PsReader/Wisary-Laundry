@php
    $order = $this->order;
    $paidNetCents = $order->paidInCents() - $order->refundedInCents();
    $isSuperAdmin = auth()->user()->isSuperAdmin();
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <p class="text-sm text-neutral-600">
                <a href="{{ route('customers.show', $order->customer) }}" class="hover:text-primary-700 hover:underline">{{ $order->customer->name }}</a>
                <a href="tel:+62{{ substr($order->customer->whatsapp, 1) }}" class="inline-flex items-center min-h-11 text-primary-700 hover:underline ml-1">{{ $order->customer->whatsapp }}</a>
            </p>
            <p class="mt-1 text-xs text-neutral-500">
                Dibuat {{ format_datetime_wib($order->received_at) }} oleh {{ $order->createdBy?->name ?? '—' }}
            </p>
        </div>
        <div class="flex flex-col sm:items-end gap-3">
            <div>
                <p class="text-sm text-neutral-500">Total</p>
                <p class="text-2xl sm:text-3xl font-bold text-primary-900">{{ format_rupiah((float) $order->total_price) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-badge :status="$order->displayStatusLabel()" />
                <x-badge :status="\App\Models\Order::paymentLabel($order->payment_status)" />
                <x-badge color="bg-neutral-100 text-neutral-700">{{ \App\Models\Order::methodLabel($order->method) }}</x-badge>
            </div>
        </div>
    </div>

    {{-- Action buttons --}}
    @php
        $flow = $order->status !== \App\Models\Order::STATUS_DIBATALKAN
            ? $this->flowButtons
            : [];
        $canCancel = $order->status !== \App\Models\Order::STATUS_DIBATALKAN
            && $order->handover_status === \App\Models\Order::HANDOVER_BELUM_DISERAHKAN;
        $canHandover = $order->status === \App\Models\Order::STATUS_SIAP_DIAMBIL
            && $order->handover_status === \App\Models\Order::HANDOVER_BELUM_DISERAHKAN;
    @endphp

    @if ($flow || $canCancel || $canHandover || $isSuperAdmin)
        <x-card>
            <div class="flex flex-wrap gap-2">
                @foreach ($flow as $target)
                    <button
                        type="button"
                        wire:click="changeStatus({{ $order->id }}, '{{ $target }}')"
                        class="inline-flex items-center justify-center rounded-lg bg-primary-700 px-4 py-2.5 min-h-12 text-sm font-semibold text-white transition-colors hover:bg-primary-800"
                    >
                        {{ \App\Models\Order::statusLabel($target) }}
                    </button>
                @endforeach

                @if ($canHandover)
                    @if ($order->method === \App\Models\Order::METHOD_DIANTAR)
                        <button
                            type="button"
                            wire:click="startHandover({{ $order->id }}, 'sudah_diantar')"
                            class="inline-flex items-center justify-center rounded-lg bg-primary-700 px-4 py-2.5 min-h-12 text-sm font-semibold text-white transition-colors hover:bg-primary-800"
                        >
                            Tandai Sudah Diantar
                        </button>
                        <button
                            type="button"
                            wire:click="startHandover({{ $order->id }}, 'gagal')"
                            class="inline-flex items-center justify-center rounded-lg bg-white border border-neutral-300 px-4 py-2.5 min-h-12 text-sm font-semibold text-neutral-700 transition-colors hover:bg-neutral-50"
                        >
                            Catat Gagal Delivery
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="startHandover({{ $order->id }}, 'sudah_diambil')"
                            class="inline-flex items-center justify-center rounded-lg bg-primary-700 px-4 py-2.5 min-h-12 text-sm font-semibold text-white transition-colors hover:bg-primary-800"
                        >
                            Tandai Sudah Diambil
                        </button>
                    @endif
                @endif

                @if ($canCancel)
                    <button
                        type="button"
                        wire:click="startCancel({{ $order->id }})"
                        class="inline-flex items-center justify-center rounded-lg bg-danger-700 px-4 py-2.5 min-h-12 text-sm font-semibold text-white transition-colors hover:bg-danger-600"
                    >
                        Batalkan Pesanan
                    </button>
                @endif

                @if ($isSuperAdmin)
                    <button
                        type="button"
                        wire:click="startAdjustPrice({{ $order->id }})"
                        class="inline-flex items-center justify-center rounded-lg bg-white border border-neutral-300 px-4 py-2.5 min-h-12 text-sm font-semibold text-neutral-700 transition-colors hover:bg-neutral-50"
                    >
                        Sesuaikan Harga
                    </button>
                    <button
                        type="button"
                        wire:click="startOverrideEstimate({{ $order->id }})"
                        class="inline-flex items-center justify-center rounded-lg bg-white border border-neutral-300 px-4 py-2.5 min-h-12 text-sm font-semibold text-neutral-700 transition-colors hover:bg-neutral-50"
                    >
                        Ubah Estimasi
                    </button>
                @endif
            </div>
        </x-card>
    @endif

    {{-- Order items --}}
    <x-card title="Detail Layanan">
        {{-- Table (tablet+) --}}
        <div class="hidden sm:block overflow-x-auto scrollbar-chip">
            <table class="w-full text-sm min-w-[480px]">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-neutral-500 border-b border-neutral-100">
                        <th class="py-3 pr-4 font-semibold">Layanan</th>
                        <th class="py-3 pr-4 font-semibold">Tipe</th>
                        <th class="py-3 pr-4 font-semibold text-right">Harga</th>
                        <th class="py-3 pr-4 font-semibold text-right">Berat/Jumlah</th>
                        <th class="py-3 pr-4 font-semibold text-right">Ditagihkan</th>
                        <th class="py-3 font-semibold text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b border-neutral-100 last:border-0">
                            <td class="py-3 pr-4">
                                <p class="font-medium">{{ $item->service_name }}</p>
                                @if ($item->note)
                                    <p class="text-xs text-neutral-500 mt-0.5">{{ $item->note }}</p>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-neutral-600">{{ $item->pricing_type === 'kg' ? 'Kilogram' : 'Unit' }}</td>
                            <td class="py-3 pr-4 text-right text-neutral-600">{{ format_rupiah((float) $item->price) }}/{{ $item->unit }}</td>
                            <td class="py-3 pr-4 text-right">
                                @if ($item->pricing_type === 'kg')
                                    {{ $item->actual_weight }} kg
                                @else
                                    {{ $item->quantity }} unit
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-right font-medium">
                                @if ($item->pricing_type === 'kg')
                                    {{ $item->billed_weight }} kg
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3 text-right font-semibold">{{ format_rupiah((float) $item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-neutral-200">
                        <td colspan="5" class="py-3 pr-4 text-right font-semibold text-neutral-950">Total</td>
                        <td class="py-3 text-right font-bold text-lg text-primary-900">{{ format_rupiah((float) $order->total_price) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Cards (mobile): money must never be off-screen --}}
        <div class="sm:hidden space-y-3">
            @foreach ($order->items as $item)
                <div class="rounded-xl border border-neutral-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium">{{ $item->service_name }}</p>
                            <p class="text-xs text-neutral-500">
                                {{ $item->pricing_type === 'kg' ? 'Kilogram' : 'Unit' }} · {{ format_rupiah((float) $item->price) }}/{{ $item->unit }}
                            </p>
                        </div>
                        <p class="shrink-0 font-semibold text-right">{{ format_rupiah((float) $item->subtotal) }}</p>
                    </div>
                    <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        <dt class="text-neutral-500">Berat/Jumlah</dt>
                        <dd class="text-right font-medium">
                            @if ($item->pricing_type === 'kg')
                                {{ $item->actual_weight }} kg
                            @else
                                {{ $item->quantity }} unit
                            @endif
                        </dd>
                        <dt class="text-neutral-500">Ditagihkan</dt>
                        <dd class="text-right font-medium">
                            @if ($item->pricing_type === 'kg')
                                {{ $item->billed_weight }} kg
                            @else
                                —
                            @endif
                        </dd>
                    </dl>
                    @if ($item->note)
                        <p class="mt-2 text-xs text-neutral-500">{{ $item->note }}</p>
                    @endif
                </div>
            @endforeach
            <div class="flex items-center justify-between rounded-xl bg-primary-50 p-4">
                <span class="font-semibold text-primary-900">Total</span>
                <span class="font-bold text-lg text-primary-900">{{ format_rupiah((float) $order->total_price) }}</span>
            </div>
        </div>
    </x-card>

    {{-- Payment summary --}}
    <x-card title="Pembayaran">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <p class="text-xs uppercase tracking-wide font-semibold text-neutral-500">Total Tagihan</p>
                <p class="mt-1 text-xl font-bold text-primary-900">{{ format_rupiah((float) $order->total_price) }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide font-semibold text-neutral-500">Sudah Dibayar</p>
                <p class="mt-1 text-xl font-bold text-success-700">{{ format_rupiah((float) $order->paid_amount) }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide font-semibold text-neutral-500">Sisa Tagihan</p>
                <p class="mt-1 text-xl font-bold {{ $order->remainingAmount > 0 ? 'text-danger-700' : 'text-success-700' }}">{{ format_rupiah($order->remainingAmount) }}</p>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            @if ($order->remainingAmount > 0)
                <button
                    type="button"
                    wire:click="startPayment({{ $order->id }})"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-700 px-4 py-2.5 min-h-12 text-sm font-semibold text-white transition-colors hover:bg-primary-800"
                >
                    Catat Pembayaran
                </button>
            @endif
            @if ($paidNetCents > 0)
                <button
                    type="button"
                    wire:click="startRefund({{ $order->id }})"
                    class="inline-flex items-center justify-center rounded-lg bg-white border border-neutral-300 px-4 py-2.5 min-h-12 text-sm font-semibold text-neutral-700 transition-colors hover:bg-neutral-50"
                >
                    Kirim Refund
                </button>
            @endif
        </div>

        @if ($order->payments->isNotEmpty() || $order->refunds->isNotEmpty())
            <div class="mt-4 border-t border-neutral-100 pt-4">
                <details class="group">
                    <summary class="flex cursor-pointer items-center justify-between text-sm font-semibold text-primary-700 list-none min-h-12">
                        Riwayat pembayaran
                        <span class="text-neutral-500 group-open:hidden">Buka</span>
                        <span class="text-neutral-500 hidden group-open:inline">Tutup</span>
                    </summary>
                    <ol class="mt-3 space-y-2">
                        @forelse ($order->payments->sortByDesc('received_at') as $payment)
                            <li class="flex items-baseline justify-between gap-3 rounded-lg bg-neutral-50 px-3 py-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-success-700">Bayar {{ format_rupiah($payment->amount) }}</p>
                                    <p class="text-xs text-neutral-500">{{ \App\Models\Payment::methodLabel($payment->payment_method) }}@if ($payment->note) — {{ $payment->note }}@endif</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-xs text-neutral-600">{{ format_datetime_wib($payment->received_at) }}</p>
                                    @if ($payment->user)
                                        <p class="text-xs text-neutral-500">{{ $payment->user->name }}</p>
                                    @endif
                                </div>
                            </li>
                        @empty
                        @endforelse

                        @forelse ($order->refunds->sortByDesc('refunded_at') as $refund)
                            <li class="flex items-baseline justify-between gap-3 rounded-lg bg-danger-50/60 px-3 py-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-danger-700">Refund {{ format_rupiah($refund->amount) }}</p>
                                    <p class="text-xs text-neutral-500">{{ \App\Models\Payment::methodLabel($refund->method) }} — {{ $refund->reason }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-xs text-neutral-600">{{ format_datetime_wib($refund->refunded_at) }}</p>
                                    @if ($refund->processedBy)
                                        <p class="text-xs text-neutral-500">{{ $refund->processedBy->name }}</p>
                                    @endif
                                </div>
                            </li>
                        @empty
                        @endforelse
                    </ol>
                </details>
            </div>
        @endif
    </x-card>

    {{-- Delivery info --}}
    @if ($order->method === \App\Models\Order::METHOD_DIANTAR)
        <x-card title="Informasi Delivery">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs uppercase tracking-wide font-semibold text-neutral-500">Alamat Pengantaran</dt>
                    <dd class="mt-1 text-neutral-700">{{ $order->delivery_address ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide font-semibold text-neutral-500">Metode</dt>
                    <dd class="mt-1">{{ \App\Models\Order::methodLabel($order->method) }}</dd>
                </div>
            </dl>
        </x-card>
    @endif

    {{-- Notes --}}
    @if ($order->notes)
        <x-card title="Catatan Pesanan">
            <p class="text-sm text-neutral-700 whitespace-pre-line">{{ $order->notes }}</p>
        </x-card>
    @endif

    {{-- Cancellation info --}}
    @if ($order->status === \App\Models\Order::STATUS_DIBATALKAN)
        <x-card>
            <div class="rounded-xl bg-danger-50 border border-danger-200 p-4">
                <h3 class="text-base font-semibold text-danger-700">Pesanan Dibatalkan</h3>
                <dl class="mt-2 space-y-1 text-sm">
                    <div class="flex gap-2">
                        <dt class="text-neutral-500">Alasan:</dt>
                        <dd class="text-neutral-700">{{ $order->cancellation_reason }}</dd>
                    </div>
                    @if ($order->cancelledBy)
                        <div class="flex gap-2">
                            <dt class="text-neutral-500">Oleh:</dt>
                            <dd class="text-neutral-700">{{ $order->cancelledBy->name }}</dd>
                        </div>
                    @endif
                    @if ($order->cancelled_at)
                        <div class="flex gap-2">
                            <dt class="text-neutral-500">Waktu:</dt>
                            <dd class="text-neutral-700">{{ format_datetime_wib($order->cancelled_at) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </x-card>
    @endif

    {{-- Estimasi --}}
    @if ($order->estimated_completion_at)
        <x-card title="Estimasi Selesai">
            <p class="text-lg font-semibold text-primary-900">{{ format_date($order->estimated_completion_at) }}</p>
        </x-card>
    @endif

    @include('livewire.orders.partials.timeline')

{{-- ── Modals ────────────────────────────────────────────────────── --}}

@include('partials.handover-modal')

@include('livewire.orders.partials.cancel-modal')

@include('partials.payment-modal')

@include('partials.refund-modal')

@include('livewire.orders.partials.price-adjustment-modal')

@include('livewire.orders.partials.estimate-override-modal')

</div>
