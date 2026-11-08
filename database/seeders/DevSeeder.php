<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderHandoverHistory;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::query()->where('role', User::ROLE_STAFF)->first() ?? User::factory()->create(['role' => User::ROLE_STAFF]);
        $services = Service::query()->get();
        $catalog = $services->keyBy('name');

        $customers = collect([
            ['name' => 'Ani Rahmawati', 'whatsapp' => '081234567890', 'address' => 'Jl. Melati No. 12, Bekasi', 'notes' => 'Pelanggan setia, suka layanan Kilat'],
            ['name' => 'Bagus Prasetyo', 'whatsapp' => '081298765432', 'address' => 'Perum Griya Asri Blok C5, Bekasi', 'notes' => null],
            ['name' => 'Citra Lestari', 'whatsapp' => '085712345678', 'address' => 'Jl. Kenanga No. 3, Jakarta Timur', 'notes' => 'Lebih suka diantar sore hari'],
            ['name' => 'Dimas Nugroho', 'whatsapp' => '087811223344', 'address' => 'Jl. Anggrek No. 8, Depok', 'notes' => null],
            ['name' => 'Eka Puspita', 'whatsapp' => '081377889900', 'address' => 'Apartemen Green Park Tower B, Jakarta', 'notes' => 'Layanan unit Bedcover & Seprei'],
            ['name' => 'Fajar Hidayat', 'whatsapp' => '082122334455', 'address' => 'Jl. Mawar No. 21, Cibinong', 'notes' => null],
            ['name' => 'Gita Permata', 'whatsapp' => '085699887766', 'address' => 'Perum Vila Bogor Indah Blok E2', 'notes' => 'Sering menanyakan estimasi selesai'],
            ['name' => 'Hendra Wijaya', 'whatsapp' => '081155443322', 'address' => 'Jl. Cendana No. 5, Bekasi', 'notes' => null],
        ])->map(function (array $data) {
            return Customer::query()->create($data);
        });

        $orders = [
            ['customer' => 0, 'status' => Order::STATUS_DITERIMA, 'payment_status' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['service' => 'Cuci Setrika Reguler', 'actual_weight' => 3.0], ['service' => 'Seprei', 'quantity' => 2]]],
            ['customer' => 1, 'status' => Order::STATUS_SEDANG_DICUCI, 'payment_status' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['service' => 'Cuci Lipat Express', 'actual_weight' => 5.4]]],
            ['customer' => 2, 'status' => Order::STATUS_SIAP_DIAMBIL, 'payment_status' => Order::PAYMENT_LUNAS_SEBAGIAN, 'method' => Order::METHOD_DIAMBIL, 'paid' => 0.5, 'items' => [['service' => 'Setrika Kilat', 'actual_weight' => 4.6]], 'handover' => ['action' => OrderHandoverHistory::ACTION_SUDAH_DIAMBIL, 'days' => 3, 'note' => 'Diambil pelanggan']],
            ['customer' => 3, 'status' => Order::STATUS_SIAP_DIAMBIL, 'payment_status' => Order::PAYMENT_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['service' => 'Cuci Lipat Reguler', 'actual_weight' => 7.2], ['service' => 'Selimut Besar', 'quantity' => 1]], 'payment_method' => Payment::METHOD_TRANSFER, 'handover' => ['action' => OrderHandoverHistory::ACTION_SUDAH_DIANTAR, 'days' => 2, 'note' => 'Diterima langsung oleh pelanggan']],
            ['customer' => 4, 'status' => Order::STATUS_SEDANG_DIKERINGKAN, 'payment_status' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['service' => 'Cuci Setrika Kilat', 'actual_weight' => 2.5], ['service' => 'Bedcover Besar', 'quantity' => 2]]],
            ['customer' => 5, 'status' => Order::STATUS_DIBATALKAN, 'payment_status' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['service' => 'Setrika Reguler', 'actual_weight' => 6.0]], 'cancelled' => 'Pelanggan membatalkan karena jadwal'],
            ['customer' => 6, 'status' => Order::STATUS_SEDANG_DISETRIKA, 'payment_status' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['service' => 'Cuci Setrika Express', 'actual_weight' => 4.1]]],
            ['customer' => 7, 'status' => Order::STATUS_SIAP_DIAMBIL, 'payment_status' => Order::PAYMENT_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['service' => 'Setrika Express', 'actual_weight' => 8.0], ['service' => 'Seprei Set', 'quantity' => 2]], 'payment_method' => Payment::METHOD_TUNAI, 'handover' => ['action' => OrderHandoverHistory::ACTION_SUDAH_DIANTAR, 'days' => 1, 'note' => 'Diantar sore hari']],
            ['customer' => 0, 'status' => Order::STATUS_SIAP_DIAMBIL, 'payment_status' => Order::PAYMENT_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['service' => 'Cuci Lipat Reguler', 'actual_weight' => 4.8]], 'payment_method' => Payment::METHOD_TUNAI, 'handover' => ['action' => OrderHandoverHistory::ACTION_SUDAH_DIAMBIL, 'days' => 2, 'note' => 'Diambil pelanggan']],
            ['customer' => 2, 'status' => Order::STATUS_SIAP_DIAMBIL, 'payment_status' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['service' => 'Cuci Setrika Reguler', 'actual_weight' => 6.3]], 'handover' => ['action' => OrderHandoverHistory::ACTION_GAGAL, 'days' => 1, 'failure_reason' => 'Pelanggan tidak di rumah, akan diantar ulang']],
            ['customer' => 5, 'status' => Order::STATUS_SEDANG_DILIPAT, 'payment_status' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['service' => 'Cuci Lipat Kilat', 'actual_weight' => 3.4]]],
            ['customer' => 7, 'status' => Order::STATUS_SIAP_DIAMBIL, 'payment_status' => Order::PAYMENT_LUNAS_SEBAGIAN, 'method' => Order::METHOD_DIAMBIL, 'paid' => 0.4, 'items' => [['service' => 'Setrika Express', 'actual_weight' => 3.3]], 'payment_method' => Payment::METHOD_TRANSFER, 'handover' => ['action' => OrderHandoverHistory::ACTION_SUDAH_DIAMBIL, 'days' => 0, 'note' => 'Diambil pelanggan']],
        ];

        foreach ($orders as $index => $def) {
            $receivedAt = now()->subDays($index);

            $order = Order::query()->create([
                'order_number' => $this->orderNumber($index + 1, $receivedAt),
                'customer_id' => $customers[$def['customer']]->id,
                'received_at' => $receivedAt,
                'status' => $def['status'],
                'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
                'payment_status' => $def['payment_status'],
                'method' => $def['method'],
                'delivery_address' => $def['method'] === Order::METHOD_DIANTAR ? $customers[$def['customer']]->address : null,
                'estimated_completion_at' => now()->addDays(3),
                'total_price' => 0,
                'paid_amount' => 0,
                'payment_method' => $def['payment_method'] ?? null,
                'cancellation_reason' => $def['cancelled'] ?? null,
                'cancelled_at' => isset($def['cancelled']) ? now()->subDays($index) : null,
                'cancelled_by' => isset($def['cancelled']) ? $staff->id : null,
                'created_by' => $staff->id,
            ]);

            $total = 0;
            foreach ($def['items'] as $itemDef) {
                $service = $catalog[$itemDef['service']];
                $actualWeight = $itemDef['actual_weight'] ?? null;
                $billedWeight = $actualWeight !== null ? max(4, (float) ceil($actualWeight)) : null;
                $quantity = $itemDef['quantity'] ?? 1;
                $subtotal = $service->pricing_type === Service::PRICING_TYPE_KG
                    ? $billedWeight * (float) $service->price
                    : $quantity * (float) $service->price;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'pricing_type' => $service->pricing_type,
                    'unit' => $service->unit,
                    'price' => $service->price,
                    'actual_weight' => $actualWeight,
                    'billed_weight' => $billedWeight,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $paid = $def['payment_status'] === Order::PAYMENT_LUNAS
                ? $total
                : ($def['payment_status'] === Order::PAYMENT_LUNAS_SEBAGIAN ? (int) round($total * ($def['paid'] ?? 0.5)) : 0);

            $order->update(['total_price' => $total, 'paid_amount' => $paid]);

            if ($paid > 0) {
                Payment::query()->create([
                    'order_id' => $order->id,
                    'user_id' => $staff->id,
                    'amount' => $paid,
                    'payment_method' => $def['payment_method'] ?? Payment::METHOD_TUNAI,
                    'received_at' => $order->received_at,
                ]);
            }

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => $order->status,
                'user_id' => $staff->id,
            ]);

            if (isset($def['handover'])) {
                $handover = $def['handover'];

                OrderHandoverHistory::query()->create([
                    'order_id' => $order->id,
                    'handover_action' => $handover['action'],
                    'recorded_by' => $staff->id,
                    'occurred_at' => now()->subDays($handover['days'] ?? 0),
                    'note' => $handover['note'] ?? null,
                    'failure_reason' => $handover['failure_reason'] ?? null,
                ]);

                // 'gagal' sengaja tidak mengubah handover_status (pesanan masih menunggu serah terima).
                if ($handover['action'] !== OrderHandoverHistory::ACTION_GAGAL) {
                    $order->update(['handover_status' => $handover['action']]);
                }
            }
        }
    }

    private function orderNumber(int $seq, CarbonInterface $date): string
    {
        return sprintf('%04d', $seq).$date->format('dmY');
    }
}
