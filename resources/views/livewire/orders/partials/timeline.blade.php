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
