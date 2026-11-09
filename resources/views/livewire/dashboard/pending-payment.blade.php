<div class="space-y-3">
    @forelse ($this->unpaidOrders as $order)
        @php
            $paidNetCents = $order->paidInCents() - $order->refundedInCents();
        @endphp
        <div class="rounded-lg border border-neutral-200 bg-white p-4 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-neutral-500">№ <a href="{{ route('orders.show', $order) }}" class="text-primary-700 hover:underline">{{ $order->order_number }}</a></p>
                    <p class="font-semibold text-neutral-950"><a href="{{ route('customers.show', $order->customer) }}" class="hover:text-primary-700 hover:underline">{{ $order->customer->name }}</a></p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <x-badge :status="\App\Models\Order::paymentLabel($order->payment_status)" />
                        <span class="text-xs text-neutral-500">Total {{ format_rupiah((float) $order->total_price) }}</span>
                    </div>
                    <p class="mt-1 text-sm font-semibold text-danger-700">Sisa tagihan: {{ format_rupiah($order->remainingAmount) }}</p>
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                <x-button wire:click="startPayment({{ $order->id }})">Catat Pembayaran</x-button>
                @if ($paidNetCents > 0)
                    <x-button variant="secondary" wire:click="startRefund({{ $order->id }})">Kirim Refund</x-button>
                @endif
            </div>

            @if ($order->payments->isNotEmpty() || $order->refunds->isNotEmpty())
                <div class="border-t border-neutral-100 pt-3">
                    <details class="group">
                        <summary class="flex cursor-pointer items-center justify-between text-sm font-semibold text-primary-700 list-none min-h-12">
                            Riwayat pembayaran
                            <span class="text-neutral-500 group-open:hidden">Buka</span>
                            <span class="text-neutral-500 hidden group-open:inline">Tutup</span>
                        </summary>
                        <ol class="mt-3 space-y-2">
                            @foreach ($order->payments->sortByDesc('received_at') as $payment)
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
                            @endforeach

                            @foreach ($order->refunds->sortByDesc('refunded_at') as $refund)
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
                            @endforeach
                        </ol>
                    </details>
                </div>
            @endif
        </div>
    @empty
        <div class="py-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-8 w-8 text-neutral-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2zM16 12h.01"/>
            </svg>
            <p class="mt-2 text-neutral-500">Tidak ada pesanan dengan tagihan belum lunas.</p>
        </div>
    @endforelse

    @if ($this->unpaidOrders->isNotEmpty())
        <div class="pt-1 text-right">
            <a href="{{ route('orders') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-700 hover:text-primary-800 min-h-12">
                Lihat semua
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </a>
        </div>
    @endif

    @include('partials.payment-modal')

    @include('partials.refund-modal')
</div>
