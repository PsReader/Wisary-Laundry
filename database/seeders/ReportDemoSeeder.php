<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderHandoverHistory;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReportDemoSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::query()->where('role', User::ROLE_STAFF)->first() ?? User::first();
        $services = Service::query()->get()->keyBy('name');
        $customers = Customer::query()->limit(10)->get();

        if ($customers->count() < 10) {
            for ($i = $customers->count(); $i < 10; $i++) {
                $customers->push(Customer::query()->create([
                    'name' => 'Demo Pelanggan '.($i + 1),
                    'whatsapp' => '08123'.str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'address' => 'Jl. Demo No. '.($i + 1),
                ]));
            }
        }

        $now = Carbon::now('Asia/Jakarta');
        $defs = [
            ['cust' => 0, 'status' => Order::STATUS_SIAP_DIAMBIL, 'pay' => Order::PAYMENT_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['svc' => 'Cuci Lipat Reguler', 'w' => 4.2]], 'pm' => Payment::METHOD_TUNAI],
            ['cust' => 1, 'status' => Order::STATUS_SIAP_DIAMBIL, 'pay' => Order::PAYMENT_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['svc' => 'Cuci Setrika Reguler', 'w' => 5.0]], 'pm' => Payment::METHOD_TRANSFER],
            ['cust' => 2, 'status' => Order::STATUS_SEDANG_DISETRIKA, 'pay' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['svc' => 'Cuci Setrika Express', 'w' => 3.6]]],
            ['cust' => 3, 'status' => Order::STATUS_SIAP_DIAMBIL, 'pay' => Order::PAYMENT_LUNAS_SEBAGIAN, 'method' => Order::METHOD_DIANTAR, 'items' => [['svc' => 'Cuci Lipat Kilat', 'w' => 6.1]], 'paid' => 0.5, 'pm' => Payment::METHOD_TUNAI],
            ['cust' => 4, 'status' => Order::STATUS_SEDANG_DICUCI, 'pay' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['svc' => 'Cuci Lipat Reguler', 'w' => 4.8]]],
            ['cust' => 5, 'status' => Order::STATUS_SIAP_DIAMBIL, 'pay' => Order::PAYMENT_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['svc' => 'Setrika Reguler', 'w' => 2.5]], 'pm' => Payment::METHOD_TRANSFER],
            ['cust' => 6, 'status' => Order::STATUS_DITERIMA, 'pay' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['svc' => 'Cuci Setrika Reguler', 'w' => 7.0]]],
            ['cust' => 7, 'status' => Order::STATUS_SIAP_DIAMBIL, 'pay' => Order::PAYMENT_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['svc' => 'Seprei', 'q' => 2]], 'pm' => Payment::METHOD_TUNAI],
            ['cust' => 8, 'status' => Order::STATUS_SEDANG_DIKERINGKAN, 'pay' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['svc' => 'Cuci Lipat Express', 'w' => 5.2]]],
            ['cust' => 9, 'status' => Order::STATUS_SIAP_DIAMBIL, 'pay' => Order::PAYMENT_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['svc' => 'Cuci Lipat Reguler', 'w' => 4.0]], 'pm' => Payment::METHOD_TRANSFER],
            ['cust' => 0, 'status' => Order::STATUS_DITERIMA, 'pay' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['svc' => 'Cuci Lipat Reguler', 'w' => 6.0]]],
            ['cust' => 1, 'status' => Order::STATUS_SEDANG_DICUCI, 'pay' => Order::PAYMENT_LUNAS_SEBAGIAN, 'method' => Order::METHOD_DIAMBIL, 'items' => [['svc' => 'Cuci Setrika Reguler', 'w' => 4.5]], 'paid' => 0.3, 'pm' => Payment::METHOD_TUNAI],
            ['cust' => 2, 'status' => Order::STATUS_SIAP_DIAMBIL, 'pay' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['svc' => 'Cuci Lipat Kilat', 'w' => 5.5]]],
            ['cust' => 3, 'status' => Order::STATUS_SEDANG_DIKERINGKAN, 'pay' => Order::PAYMENT_BELUM_LUNAS, 'method' => Order::METHOD_DIAMBIL, 'items' => [['svc' => 'Cuci Setrika Express', 'w' => 4.2]]],
            ['cust' => 4, 'status' => Order::STATUS_DITERIMA, 'pay' => Order::PAYMENT_LUNAS, 'method' => Order::METHOD_DIANTAR, 'items' => [['svc' => 'Seprei', 'q' => 1]], 'pm' => Payment::METHOD_TRANSFER],
            ['cust' => 5, 'status' => Order::STATUS_SEDANG_DISETRIKA, 'pay' => Order::PAYMENT_LUNAS_SEBAGIAN, 'method' => Order::METHOD_DIAMBIL, 'items' => [['svc' => 'Cuci Lipat Reguler', 'w' => 5.0]], 'paid' => 0.6, 'pm' => Payment::METHOD_TUNAI],
        ];

        foreach ($defs as $i => $def) {
            $receivedAt = $now->copy()->subDays(rand(0, 6));
            $cust = $customers[$def['cust']];

            $handover = Order::HANDOVER_BELUM_DISERAHKAN;
            if ($i % 3 === 0 && in_array($def['pay'], [Order::PAYMENT_LUNAS, Order::PAYMENT_LUNAS_SEBAGIAN], true)) {
                $handover = $def['method'] === Order::METHOD_DIANTAR ? Order::HANDOVER_SUDAH_DIANTAR : Order::HANDOVER_SUDAH_DIAMBIL;
            }

            $order = Order::query()->create([
                'order_number' => sprintf('%04d', 9000 + $i).$receivedAt->format('dmY'),
                'customer_id' => $cust->id,
                'received_at' => $receivedAt,
                'status' => $def['status'],
                'handover_status' => $handover,
                'payment_status' => $def['pay'],
                'method' => $def['method'],
                'delivery_address' => $def['method'] === Order::METHOD_DIANTAR ? $cust->address : null,
                'estimated_completion_at' => $receivedAt->copy()->addDays(3),
                'total_price' => 0,
                'paid_amount' => 0,
                'payment_method' => $def['pm'] ?? null,
                'created_by' => $staff?->id,
            ]);

            $total = 0;
            foreach ($def['items'] as $itemDef) {
                $svc = $services[$itemDef['svc']] ?? $services->first();
                $actualWeight = $itemDef['w'] ?? null;
                $billedWeight = $actualWeight !== null ? max(4, (float) ceil($actualWeight)) : null;
                $quantity = $itemDef['q'] ?? 1;
                $subtotal = $svc->pricing_type === Service::PRICING_TYPE_KG
                    ? $billedWeight * (float) $svc->price
                    : $quantity * (float) $svc->price;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'service_id' => $svc->id,
                    'service_name' => $svc->name,
                    'pricing_type' => $svc->pricing_type,
                    'unit' => $svc->unit,
                    'price' => $svc->price,
                    'actual_weight' => $actualWeight,
                    'billed_weight' => $billedWeight,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $paid = $def['pay'] === Order::PAYMENT_LUNAS
                ? $total
                : ($def['pay'] === Order::PAYMENT_LUNAS_SEBAGIAN ? (int) round($total * ($def['paid'] ?? 0.5)) : 0);

            $order->update(['total_price' => $total, 'paid_amount' => $paid]);

            if ($paid > 0) {
                Payment::query()->create([
                    'order_id' => $order->id,
                    'user_id' => $staff?->id,
                    'amount' => $paid,
                    'payment_method' => $def['pm'] ?? Payment::METHOD_TUNAI,
                    'received_at' => $receivedAt,
                ]);
            }

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => $order->status,
                'user_id' => $staff?->id,
            ]);

            if ($handover !== Order::HANDOVER_BELUM_DISERAHKAN) {
                OrderHandoverHistory::query()->create([
                    'order_id' => $order->id,
                    'recorded_by' => $staff?->id,
                    'handover_action' => $handover === Order::HANDOVER_SUDAH_DIANTAR ? OrderHandoverHistory::ACTION_SUDAH_DIANTAR : OrderHandoverHistory::ACTION_SUDAH_DIAMBIL,
                    'occurred_at' => $receivedAt->copy()->addDays(1),
                ]);
            }
        }
    }
}
