<div class="space-y-3">
    @forelse ($this->unpaidOrders as $order)
        @php
            $paidNetCents = $order->paidInCents() - $order->refundedInCents();
        @endphp
        <div class="rounded-lg border border-neutral-200 bg-white p-4 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-neutral-500">№ {{ $order->order_number }}</p>
                    <p class="font-semibold text-neutral-950">{{ $order->customer->name }}</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $order->payment_status === \App\Models\Order::PAYMENT_BELUM_LUNAS ? 'bg-warning-100 text-warning-700' : 'bg-warning-100 text-warning-700' }}">
                            {{ \App\Models\Order::paymentLabel($order->payment_status) }}
                        </span>
                        <span class="text-xs text-neutral-500">Total {{ format_rupiah($order->total_price) }}</span>
                    </div>
                    <p class="mt-1 text-sm font-semibold text-danger-700">Sisa tagihan: {{ format_rupiah($order->remainingAmount) }}</p>
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                <button
                    type="button"
                    wire:click="startPayment({{ $order->id }})"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-700 px-3 py-2.5 min-h-12 text-sm font-semibold text-white transition-colors hover:bg-primary-800"
                >
                    Catat Pembayaran
                </button>
                @if ($paidNetCents > 0)
                    <button
                        type="button"
                        wire:click="startRefund({{ $order->id }})"
                        class="inline-flex items-center justify-center rounded-lg bg-white border border-neutral-300 px-3 py-2.5 min-h-12 text-sm font-semibold text-neutral-700 transition-colors hover:bg-neutral-50"
                    >
                        Kirim Refund
                    </button>
                @endif
            </div>

            @if ($order->payments->isNotEmpty() || $order->refunds->isNotEmpty())
                <div class="border-t border-neutral-100 pt-3">
                    <details class="group">
                        <summary class="flex cursor-pointer items-center justify-between text-sm font-semibold text-primary-700 list-none min-h-10">
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
                                <li class="flex items-baseline justify-between gap-3 rounded-lg bg-red-50/60 px-3 py-2">
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
            <a href="{{ route('orders') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-700 hover:text-primary-800 min-h-10">
                Lihat semua
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </a>
        </div>
    @endif

    <x-modal name="payment-modal" :show="false" focusable>
        <form wire:submit="submitPayment" class="p-6">
            <h2 class="text-lg font-semibold text-primary-900">Catat Pembayaran</h2>
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
                        @foreach ($this->paymentMethods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
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
                <button type="button" x-data @click="$dispatch('close-modal', {})" class="flex items-center justify-center px-4 py-3 min-h-12 lg:min-h-10 text-sm font-medium text-neutral-700 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Batal
                </button>
                <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Simpan Pembayaran</x-button>
            </div>
        </form>
    </x-modal>

    <x-modal name="refund-modal" :show="false" focusable>
        <form wire:submit="submitRefund" class="p-6">
            <h2 class="text-lg font-semibold text-primary-900">Kirim Refund</h2>
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
                        @foreach ($this->paymentMethods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @if ($errors->first('refundMethod'))
                        <p class="mt-1 text-xs text-danger-700" role="alert">{{ $errors->first('refundMethod') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button" x-data @click="$dispatch('close-modal', {})" class="flex items-center justify-center px-4 py-3 min-h-12 lg:min-h-10 text-sm font-medium text-neutral-700 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors">
                    Batal
                </button>
                <x-button type="submit" class="sm:min-w-32" wire:loading.attr="disabled">Simpan Refund</x-button>
            </div>
        </form>
    </x-modal>
</div>