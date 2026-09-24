<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Support\Csv;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function orders(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);

        $filename = 'pesanan_'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        $rows = Order::query()
            ->with(['customer', 'createdBy'])
            ->whereBetween('received_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('received_at')
            ->get()
            ->map(fn (Order $order) => [
                $order->order_number,
                $order->customer?->name ?? '-',
                $order->customer?->whatsapp ?? '',
                $order->received_at?->format('d/m/Y H:i'),
                Order::statusLabel((string) $order->status),
                Order::handoverLabel((string) $order->handover_status),
                Order::paymentLabel((string) $order->payment_status),
                Order::methodLabel((string) $order->method),
                number_format((float) $order->total_price, 0, ',', '.'),
                number_format((float) $order->paid_amount, 0, ',', '.'),
                number_format((float) $order->remainingAmount, 0, ',', '.'),
                $order->estimated_completion_at?->format('d/m/Y'),
                $order->delivery_address ?? '',
                $order->cancellation_reason ?? '',
                $order->createdBy?->name ?? '-',
            ]);

        return $this->stream($filename, [
            'Nomor Pesanan', 'Pelanggan', 'WhatsApp', 'Diterima', 'Status',
            'Serah Terima', 'Pembayaran', 'Metode', 'Total', 'Dibayar', 'Sisa',
            'Estimasi Selesai', 'Alamat Pengantaran', 'Alasan Batal', 'Dibuat Oleh',
        ], $rows);
    }

    public function payments(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);

        $filename = 'pembayaran_'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        $rows = Payment::query()
            ->with(['order', 'user'])
            ->whereBetween('received_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('received_at')
            ->get()
            ->map(fn (Payment $payment) => [
                $payment->id,
                $payment->order?->order_number ?? '-',
                $payment->user?->name ?? '-',
                number_format((float) $payment->amount, 0, ',', '.'),
                Payment::methodLabel((string) $payment->payment_method),
                $payment->received_at?->format('d/m/Y H:i'),
                $payment->note ?? '',
            ]);

        return $this->stream($filename, [
            'ID', 'Nomor Pesanan', 'Diterima Oleh', 'Nominal', 'Metode', 'Waktu', 'Catatan',
        ], $rows);
    }

    public function customers(): StreamedResponse
    {
        $filename = 'pelanggan_'.Carbon::now('Asia/Jakarta')->format('Ymd').'.csv';

        $rows = Customer::query()
            ->withCount('orders')
            ->withSum('orders', 'total_price')
            ->orderBy('name')
            ->get()
            ->map(fn (Customer $customer) => [
                $customer->id,
                $customer->name,
                $customer->whatsapp ?? '',
                $customer->address ?? '',
                $customer->notes ?? '',
                $customer->created_at?->format('d/m/Y'),
                $customer->orders_count,
                number_format((float) $customer->orders_sum_total_price, 0, ',', '.'),
            ]);

        return $this->stream($filename, [
            'ID', 'Nama', 'WhatsApp', 'Alamat', 'Catatan', 'Pelanggan Sejak',
            'Jumlah Pesanan', 'Total Tagihan',
        ], $rows);
    }

    /**
     * Periode laporan ekspor. Default: bulan berjalan. Mengembalikan range
     * inklusif dalam waktu Asia/Jakarta.
     *
     * @return array{Carbon, Carbon}
     */
    protected function period(Request $request): array
    {
        $now = Carbon::now('Asia/Jakarta');

        $from = $request->has('from')
            ? Carbon::parse($request->string('from')->toString())->setTimezone('Asia/Jakarta')
            : $now->copy()->startOfMonth();

        $to = $request->has('to')
            ? Carbon::parse($request->string('to')->toString())->setTimezone('Asia/Jakarta')
            : $now->copy()->endOfMonth();

        if ($from > $to) {
            abort(422, 'Periode laporan tidak valid.');
        }

        return [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
    }

    protected function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $callback = Csv::streamDownload($filename, $headers, $rows);

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
