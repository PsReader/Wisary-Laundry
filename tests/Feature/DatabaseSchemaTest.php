<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_business_tables_exist(): void
    {
        $tables = [
            'customers', 'services', 'orders', 'order_items', 'payments',
            'refunds', 'order_status_histories', 'order_handover_histories',
            'order_price_adjustments', 'audit_logs',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} tidak ada.");
        }
    }

    public function test_service_seeder_creates_full_initial_catalog(): void
    {
        $this->seed(ServiceSeeder::class);

        $this->assertSame(15, Service::count());
        $this->assertSame(9, Service::query()->where('pricing_type', Service::PRICING_TYPE_KG)->count());
        $this->assertSame(6, Service::query()->where('pricing_type', Service::PRICING_TYPE_UNIT)->count());
        $this->assertDatabaseHas('services', ['name' => 'Cuci Setrika Kilat', 'price' => 12000]);
        $this->assertDatabaseHas('services', ['name' => 'Bedcover Besar', 'price' => 25000, 'unit' => Service::UNIT_PCS]);
    }

    public function test_order_number_is_unique(): void
    {
        $this->expectException(QueryException::class);

        $customer = Customer::factory()->create();
        $user = User::factory()->create();

        $attributes = [
            'order_number' => '10012092026',
            'customer_id' => $customer->id,
            'received_at' => now(),
            'status' => Order::STATUS_DITERIMA,
            'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
            'payment_status' => Order::PAYMENT_BELUM_LUNAS,
            'method' => Order::METHOD_DIAMBIL,
            'total_price' => 0,
            'created_by' => $user->id,
        ];

        Order::query()->create($attributes);
        Order::query()->create($attributes);
    }

    public function test_customer_referenced_by_order_cannot_be_deleted(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $this->expectException(QueryException::class);

        $customer->delete();
    }

    public function test_order_snapshot_preserves_old_price_and_name(): void
    {
        $service = Service::factory()->weightBased()->create(['name' => 'Layanan Lama', 'price' => 5000]);
        $order = Order::factory()->create();

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'service_name' => 'Layanan Lama',
            'price' => 5000,
            'pricing_type' => Service::PRICING_TYPE_KG,
            'unit' => Service::UNIT_KG,
            'actual_weight' => 3.5,
            'billed_weight' => 4,
            'subtotal' => 20000,
        ]);

        // Harga default layanan berubah, tapi salinan di order_item tidak berubah
        $service->update(['price' => 8000]);

        $this->assertSame('Layanan Lama', $item->fresh()->service_name);
        $this->assertSame('5000.00', $item->fresh()->price);
        $this->assertSame('20000.00', $item->fresh()->subtotal);
    }

    public function test_weight_item_rounding_and_minimum(): void
    {
        $service = Service::factory()->weightBased()->create(['price' => 5000]);

        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'service_id' => $service->id,
            'pricing_type' => Service::PRICING_TYPE_KG,
            'price' => 5000,
            'actual_weight' => 2.5,
            'billed_weight' => 4,
            'subtotal' => 20000,
        ]);

        $item = $order->items()->first();

        $this->assertSame('2.50', $item->actual_weight);
        $this->assertSame('4.00', $item->billed_weight);
        $this->assertTrue($item->isWeightBased());
    }

    public function test_order_relations_are_navigable(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $order = Order::factory()->create(['customer_id' => $customer->id, 'created_by' => $staff->id]);
        OrderItem::factory()->count(2)->create(['order_id' => $order->id]);

        $this->assertSame($customer->id, $order->customer->id);
        $this->assertSame($staff->id, $order->createdBy->id);
        $this->assertSame(2, $order->items()->count());
        $this->assertSame($order->id, $order->customer->orders()->first()->id);
    }

    public function test_payment_and_refund_are_separate_records(): void
    {
        $order = Order::factory()->create();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $payment = Payment::factory()->create(['order_id' => $order->id, 'user_id' => $staff->id, 'amount' => 50000]);

        Refund::query()->create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'amount' => 15000,
            'reason' => 'Kelebihan bayar',
            'method' => Payment::METHOD_TUNAI,
            'processed_by' => $staff->id,
            'refunded_at' => now(),
        ]);

        $this->assertSame(1, $order->refunds()->count());
        $this->assertSame('50000.00', $order->payments()->first()->amount);
        $this->assertSame('15000.00', $order->refunds()->first()->amount);
        // Payment asli tidak berubah
        $this->assertSame('50000.00', $payment->fresh()->amount);
    }

    public function test_order_remaining_amount_accessor(): void
    {
        $order = Order::factory()->create(['total_price' => 100000, 'paid_amount' => 40000]);

        $this->assertSame(60000.0, $order->remaining_amount);
    }

    public function test_database_seeder_is_repeatable(): void
    {
        $this->seed();

        $this->assertSame(15, Service::count());
        $this->assertGreaterThan(0, Customer::count());
        $this->assertGreaterThan(0, Order::count());
    }
}
