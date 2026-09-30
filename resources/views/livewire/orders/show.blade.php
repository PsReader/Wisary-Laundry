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
                {{ $order->customer->name }}
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
                <x-badge :status="\App\Models\Order::statusLabel($order->status)">{{ \App\Models\Order::statusLabel($order->status) }}</x-badge>
                <x-badge :status="\App\Models\Order::paymentLabel($order->payment_status)">{{ \App\Models\Order::paymentLabel($order->payment_status) }}</x-badge>
                <x-badge :status="\App\Models\Order::handoverLabel($order->handover_status)">{{ \App\Models\Order::handoverLabel($order->handover_status) }}</x-badge>
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

    {{-- Timeline --}}
    <x-card title="Riwayat">
        @php
            $timeline = collect();

            foreach ($order->statusHistories as $h) {
                $timeline->push([
                    'type' => 'status',
                    'time' => $h->created_at,
                    'label' => ($h->from_status ? \App\Models\Order::statusLabel($h->from_status).' → ' : '').\App\Models\Order::statusLabel($h->to_status),
                    'note' => $h->note,
                    'user' => $h->user?->name,
                ]);
            }

            foreach ($order->payments as $p) {
                $timeline->push([
                    'type' => 'payment',
                    'time' => $p->received_at,
                    'label' => 'Bayar '.format_rupiah($p->amount),
                    'note' => \App\Models\Payment::methodLabel($p->payment_method).($p->note ? ' — '.$p->note : ''),
                    'user' => $p->user?->name,
                ]);
            }

            foreach ($order->refunds as $r) {
                $timeline->push([
                    'type' => 'refund',
                    'time' => $r->refunded_at,
                    'label' => 'Refund '.format_rupiah($r->amount),
                    'note' => \App\Models\Payment::methodLabel($r->method).' — '.$r->reason,
                    'user' => $r->processedBy?->name,
                ]);
            }

            foreach ($order->handoverHistories as $h) {
                $timeline->push([
                    'type' => 'handover',
                    'time' => $h->occurred_at,
                    'label' => match($h->handover_action) {
                        'sudah_diambil' => 'Sudah Diambil',
                        'sudah_diantar' => 'Sudah Diantar',
                        'gagal' => 'Delivery Gagal',
                        default => $h->handover_action,
                    },
                    'note' => $h->failure_reason ?? $h->note,
                    'user' => $h->recordedBy?->name,
                ]);
            }

            foreach ($order->priceAdjustments as $a) {
                $timeline->push([
                    'type' => 'adjustment',
                    'time' => $a->created_at,
                    'label' => 'Harga diubah: '.format_rupiah($a->previous_amount).' → '.format_rupiah($a->new_amount),
                    'note' => $a->reason,
                    'user' => $a->user?->name,
                ]);
            }

            $timeline = $timeline->sortByDesc('time')->values();
        @endphp

        @if ($timeline->isEmpty())
            <p class="text-sm text-neutral-500 py-4 text-center">Belum ada riwayat.</p>
        @else
            <ol class="space-y-3">
                @foreach ($timeline as $entry)
                    <li class="flex items-start gap-3 rounded-lg bg-neutral-50 px-3 py-2.5">
                        <div class="shrink-0 mt-0.5">
                            @if ($entry['type'] === 'status')
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                </span>
                            @elseif ($entry['type'] === 'payment')
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-success-100 text-success-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                            @elseif ($entry['type'] === 'refund')
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-danger-100 text-danger-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                </span>
                            @elseif ($entry['type'] === 'handover')
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-info-100 text-info-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-warning-100 text-warning-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                </span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-neutral-900">{{ $entry['label'] }}</p>
                            @if ($entry['note'])
                                <p class="text-xs text-neutral-500 mt-0.5">{{ $entry['note'] }}</p>
                            @endif
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-xs text-neutral-600">{{ format_datetime_wib($entry['time']) }}</p>
                            @if ($entry['user'])
                                <p class="text-xs text-neutral-500">{{ $entry['user'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </x-card>

{{-- ── Modals ────────────────────────────────────────────────────── --}}

@include('partials.handover-modal')

{{-- Cancel modal --}}
<x-modal name="cancel-modal" :show="false" focusable>
    <form wire:submit="submitCancel" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">Batalkan Pesanan</h2>
        @if ($cancelOrderId)
            @php $cancelOrder = \App\Models\Order::find($cancelOrderId); @endphp
            @if ($cancelOrder)
                <p class="mt-1 text-sm text-neutral-600">№ {{ $cancelOrder->order_number }} — {{ $cancelOrder->customer->name }}</p>
            @endif
        @endif
        <p class="mt-3 rounded-lg bg-danger-50 px-3 py-2 text-sm text-danger-700">
            Pesanan akan berhenti diproses dan hilang dari daftar pesanan aktif. Riwayat tetap tersimpan, dan Super Admin dapat memulihkannya.
        </p>

        <div class="mt-5">
            <x-label for="cancellation_reason" required>Alasan pembatalan</x-label>
            <textarea
                id="cancellation_reason"
                wire:model.blur="cancellationReason"
                rows="3"
                class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-0 transition {{ $errors->first('cancellationReason') ? 'border-danger-700 focus:ring-danger-700' : 'border-neutral-300 focus:ring-primary-700' }}"
                placeholder="Contoh: kualitas hasil kurang memuaskan"
            ></textarea>
            @if ($errors->first('cancellationReason'))
                <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('cancellationReason') }}</p>
            @endif
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" variant="destructive" class="sm:min-w-32" wire:loading.attr="disabled">Batalkan Pesanan</x-button>
        </div>
    </form>
</x-modal>

{{-- Payment modal --}}
<x-modal name="payment-modal" :show="false" focusable>
    <form wire:submit="submitPayment" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">Catat Pembayaran</h2>
        @if ($paymentOrderId)
            @php $paymentOrder = \App\Models\Order::find($paymentOrderId); @endphp
            @if ($paymentOrder)
                <p class="mt-1 text-sm text-neutral-600">№ {{ $paymentOrder->order_number }} — {{ $paymentOrder->customer->name }}</p>
            @endif
        @endif

        <div class="mt-5 space-y-4">
            <x-input
                type="number"
                wire:model.blur="paymentAmount"
                label="Jumlah bayar"
                required
                min="1"
                step="1000"
                placeholder="40000"
                helptext="Maksimal sisa tagihan pesanan."
                :error="$errors->first('paymentAmount')"
            />
            <div>
                <x-label for="payment_method" required>Metode pembayaran</x-label>
                <select
                    id="payment_method"
                    wire:model.blur="paymentMethod"
                    required
                    class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition {{ $errors->first('paymentMethod') ? 'border-danger-700 focus:ring-danger-700' : 'border-neutral-300 focus:ring-primary-700' }}"
                >
                    @foreach (\App\Models\Payment::methods() as $method)
                        <option value="{{ $method }}">{{ \App\Models\Payment::methodLabel($method) }}</option>
                    @endforeach
                </select>
                @if ($errors->first('paymentMethod'))
                    <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('paymentMethod') }}</p>
                @endif
            </div>
            <div>
                <x-label for="payment_note">Catatan (opsional)</x-label>
                <textarea
                    id="payment_note"
                    wire:model.blur="paymentNote"
                    rows="2"
                    class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-0 transition {{ $errors->first('paymentNote') ? 'border-danger-700 focus:ring-danger-700' : 'border-neutral-300 focus:ring-primary-700' }}"
                ></textarea>
                @if ($errors->first('paymentNote'))
                    <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('paymentNote') }}</p>
                @endif
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Simpan Pembayaran</x-button>
        </div>
    </form>
</x-modal>

{{-- Refund modal --}}
<x-modal name="refund-modal" :show="false" focusable>
    <form wire:submit="submitRefund" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">Kirim Refund</h2>
        @if ($refundOrderId)
            @php $refundOrder = \App\Models\Order::find($refundOrderId); @endphp
            @if ($refundOrder)
                <p class="mt-1 text-sm text-neutral-600">№ {{ $refundOrder->order_number }} — {{ $refundOrder->customer->name }}</p>
            @endif
        @endif

        <div class="mt-5 space-y-4">
            <x-input
                type="number"
                wire:model.blur="refundAmount"
                label="Jumlah refund"
                required
                min="1"
                step="1000"
                placeholder="40000"
                helptext="Maksimal total yang sudah dibayar."
                :error="$errors->first('refundAmount')"
            />
            <div>
                <x-label for="refund_reason" required>Alasan refund</x-label>
                <textarea
                    id="refund_reason"
                    wire:model.blur="refundReason"
                    rows="3"
                    class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-0 transition {{ $errors->first('refundReason') ? 'border-danger-700 focus:ring-danger-700' : 'border-neutral-300 focus:ring-primary-700' }}"
                    placeholder="Contoh: kelebihan bayar, pembatalan layanan"
                ></textarea>
                @if ($errors->first('refundReason'))
                    <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('refundReason') }}</p>
                @endif
            </div>
            <div>
                <x-label for="refund_method" required>Metode refund</x-label>
                <select
                    id="refund_method"
                    wire:model.blur="refundMethod"
                    required
                    class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 focus:outline-none focus:ring-2 focus:ring-offset-0 transition {{ $errors->first('refundMethod') ? 'border-danger-700 focus:ring-danger-700' : 'border-neutral-300 focus:ring-primary-700' }}"
                >
                    @foreach (\App\Models\Payment::methods() as $method)
                        <option value="{{ $method }}">{{ \App\Models\Payment::methodLabel($method) }}</option>
                    @endforeach
                </select>
                @if ($errors->first('refundMethod'))
                    <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('refundMethod') }}</p>
                @endif
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Simpan Refund</x-button>
        </div>
    </form>
</x-modal>

{{-- Price adjustment modal (Super Admin) --}}
<x-modal name="adjust-price-modal" :show="false" focusable>
    <form wire:submit="submitAdjustPrice" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">Sesuaikan Harga Final</h2>
        @if ($adjustPriceOrderId)
            @php $adjustOrder = \App\Models\Order::find($adjustPriceOrderId); @endphp
            @if ($adjustOrder)
                <p class="mt-1 text-sm text-neutral-600">№ {{ $adjustOrder->order_number }} — {{ $adjustOrder->customer->name }}</p>
                <p class="mt-2 text-sm text-neutral-500">Harga saat ini: <span class="font-semibold text-neutral-700">{{ format_rupiah((float) $adjustOrder->total_price) }}</span></p>
            @endif
        @endif
        <p class="mt-2 rounded-lg bg-warning-100 px-3 py-2 text-xs text-warning-700">
            Harga baru akan menggantikan total pesanan. Alasan perubahan akan tercatat dalam riwayat.
        </p>

        <div class="mt-5 space-y-4">
            <x-input
                type="number"
                wire:model.blur="adjustPriceNewAmount"
                label="Harga baru (Rp)"
                required
                min="0"
                step="1000"
                placeholder="40000"
                :error="$errors->first('adjustPriceNewAmount')"
            />
            <div>
                <x-label for="adjust_reason" required>Alasan perubahan</x-label>
                <textarea
                    id="adjust_reason"
                    wire:model.blur="adjustPriceReason"
                    rows="3"
                    class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 min-h-12 text-sm text-neutral-950 placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-offset-0 transition {{ $errors->first('adjustPriceReason') ? 'border-danger-700 focus:ring-danger-700' : 'border-neutral-300 focus:ring-primary-700' }}"
                    placeholder="Contoh: koreksi harga karena promo"
                ></textarea>
                @if ($errors->first('adjustPriceReason'))
                    <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('adjustPriceReason') }}</p>
                @endif
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Sesuaikan Harga</x-button>
        </div>
    </form>
</x-modal>

{{-- Estimate override modal (Super Admin) --}}
<x-modal name="override-estimate-modal" :show="false" focusable>
    <form wire:submit="submitOverrideEstimate" class="p-6">
        <h2 class="text-xl font-semibold text-primary-900">Ubah Estimasi Selesai</h2>
        @if ($overrideEstimateOrderId)
            @php $estOrder = \App\Models\Order::find($overrideEstimateOrderId); @endphp
            @if ($estOrder)
                <p class="mt-1 text-sm text-neutral-600">№ {{ $estOrder->order_number }} — {{ $estOrder->customer->name }}</p>
                @if ($estOrder->estimated_completion_at)
                    <p class="mt-2 text-sm text-neutral-500">Estimasi saat ini: <span class="font-semibold text-neutral-700">{{ format_date($estOrder->estimated_completion_at) }}</span></p>
                @endif
            @endif
        @endif

        <div class="mt-5">
            <x-input
                type="date"
                wire:model.blur="overrideEstimateDate"
                label="Estimasi baru"
                required
                :error="$errors->first('overrideEstimateDate')"
            />
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <x-button type="button" variant="secondary" x-data @click="$dispatch('close-modal', {})">Batal</x-button>
            <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Simpan Estimasi</x-button>
        </div>
    </form>
</x-modal>
</div>
