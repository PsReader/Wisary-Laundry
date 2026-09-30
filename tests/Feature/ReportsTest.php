<?php

namespace Tests\Feature;

use App\Livewire\Reports\Index;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
    }

    public function test_super_admin_can_open_reports_page(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);

        $component->assertOk();
        $component->assertSee('Laporan operasional, ekspor data, dan aktivitas staf.');
        $component->assertSee('Jumlah Pesanan');
        $component->assertSee('Aktivitas Staf');
    }

    public function test_staff_cannot_access_reports_page(): void
    {
        $this->actingAs($this->staff())
            ->get(route('reports'))
            ->assertForbidden();
    }

    public function test_staff_cannot_export_data(): void
    {
        $this->actingAs($this->staff())->get(route('reports.export.orders'))->assertForbidden();
        $this->actingAs($this->staff())->get(route('reports.export.payments'))->assertForbidden();
        $this->actingAs($this->staff())->get(route('reports.export.customers'))->assertForbidden();
    }

    public function test_order_totals_and_status_breakdown_for_period(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        Order::factory()->count(2)->create(['status' => Order::STATUS_DITERIMA]);
        Order::factory()->create(['status' => Order::STATUS_SIAP_DIAMBIL]);

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);

        $component->assertSee('Laporan operasional, ekspor data, dan aktivitas staf.');
        $component->assertSee('3', false);
        $component->assertSee('Diterima', false);
        $component->assertSee('Siap Diambil', false);
    }

    public function test_revenue_aggregated_daily_and_total(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $order = Order::factory()->create(['total_price' => 40000]);
        $order->recordPayment(40000, Payment::METHOD_TUNAI, null, $this->superAdmin());

        $this->travelTo(Carbon::parse('2026-09-11 09:00:00', 'Asia/Jakarta'));

        $second = Order::factory()->create(['total_price' => 30000]);
        $second->recordPayment(30000, Payment::METHOD_TRANSFER, null, $this->superAdmin());

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);

        $component->assertSee('Rp70.000', false);
        $component->assertSee('Rp40.000', false);
        $component->assertSee('Rp30.000', false);
    }

    public function test_unpaid_report_lists_final_prices(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $order = Order::factory()->partial(10000)->create(['total_price' => 50000]);

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);

        $component->assertSee('Pesanan Belum Lunas');
        $component->assertSee($order->order_number);
        $component->assertSee('Rp50.000', false);
        $component->assertSee('Rp10.000', false);
        $component->assertSee('Rp40.000', false);
    }

    public function test_top_services_aggregated_from_order_items(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $service = Service::factory()->weightBased()->create(['name' => 'Cuci Setrika']);
        $serviceId = $service->id;

        $order = Order::factory()->create(['total_price' => 20000]);

        $order->items()->create([
            'service_id' => $serviceId,
            'service_name' => 'Cuci Setrika',
            'pricing_type' => Service::PRICING_TYPE_KG,
            'unit' => Service::UNIT_KG,
            'price' => 5000,
            'billed_weight' => 4,
            'quantity' => 1,
            'subtotal' => 20000,
        ]);

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);

        $component->assertSee('Layanan Paling Sering Digunakan');
        $component->assertSee('Cuci Setrika');
        $component->assertSee('Rp20.000', false);
    }

    public function test_cancelled_orders_listed_by_cancellation_date(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $inPeriod = Order::factory()->ready()->create();
        $inPeriod->cancel('Pelanggan membatalkan', $this->superAdmin());

        $this->travelTo(Carbon::parse('2026-08-20 10:00:00', 'Asia/Jakarta'));

        $outside = Order::factory()->ready()->create();
        $outside->cancel('Batal lama', $this->superAdmin());

        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);

        $component->assertSee('Pesanan Dibatalkan');
        $component->assertSee($inPeriod->order_number);
        $component->assertDontSee($outside->order_number);
    }

    public function test_failed_deliveries_listed_from_handover_history(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $order = Order::factory()->ready()->delivery()->create();
        $order->recordHandover('gagal', null, 'Alamat tidak ditemukan', null, $this->superAdmin());

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);

        $component->assertSee('Delivery Gagal');
        $component->assertSee($order->order_number);
        $component->assertSee('Alamat tidak ditemukan');
    }

    public function test_refund_report_lists_refunds_in_period(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $order = Order::factory()->create(['total_price' => 50000]);
        $order->recordPayment(50000, Payment::METHOD_TUNAI, null, $this->superAdmin());
        $order->recordRefund(5000, 'Kelebihan pembayaran', Payment::METHOD_TUNAI, null, $this->superAdmin());

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);

        $component->assertSee('Riwayat Refund');
        $component->assertSee($order->order_number);
        $component->assertSee('Kelebihan pembayaran');
        $component->assertSee('Rp5.000', false);
    }

    public function test_staff_activity_summarized_per_user(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $actor = $this->superAdmin();

        Order::factory()->count(2)->create(['created_by' => $actor->id]);

        $component = Livewire::actingAs($actor)->test(Index::class);

        $component->assertSee('Aktivitas Staf');
        $component->assertSee($actor->name);
    }

    public function test_reports_page_defaults_to_current_month_period(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);

        $this->assertSame('2026-09-01', $component->get('from'));
        $this->assertSame('2026-09-30', $component->get('to'));
    }

    public function test_preset_buttons_change_period(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);
        $component->call('setPeriod', 'last_month');

        $this->assertSame('2026-08-01', $component->get('from'));
        $this->assertSame('2026-08-31', $component->get('to'));
    }

    public function test_invalid_period_shows_error(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);
        $component->set('from', '2026-09-15');
        $component->set('to', '2026-09-01');

        $component->assertSee('Tanggal awal tidak boleh melebihi tanggal akhir.');
    }

    public function test_orders_export_csv_includes_headers_and_order_rows(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $order = Order::factory()->create(['total_price' => 45000, 'paid_amount' => 0]);
        $order->paid_amount = 20000;
        $order->payment_status = Order::PAYMENT_LUNAS_SEBAGIAN;
        $order->save();

        $response = $this->actingAs($this->superAdmin())
            ->get(route('reports.export.orders', ['from' => '2026-09-01', 'to' => '2026-09-30']));

        $response->assertOk();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $response->streamedContent());
        $this->assertStringContainsString('Nomor Pesanan', $response->streamedContent());
        $this->assertStringContainsString($order->order_number, $response->streamedContent());
    }

    public function test_payments_export_csv_consistent_with_revenue(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $order = Order::factory()->create(['total_price' => 60000]);
        $order->recordPayment(60000, Payment::METHOD_TRANSFER, null, $this->superAdmin());

        $response = $this->actingAs($this->superAdmin())
            ->get(route('reports.export.payments', ['from' => '2026-09-01', 'to' => '2026-09-30']));

        $response->assertOk();
        $this->assertStringContainsString('60.000', $response->streamedContent());

        $component = Livewire::actingAs($this->superAdmin())->test(Index::class);
        $component->assertSee('Rp60.000', false);
    }

    public function test_customers_export_csv_includes_customer_rows(): void
    {
        $this->travelTo(Carbon::parse('2026-09-10 10:00:00', 'Asia/Jakarta'));

        $customer = Customer::factory()->create(['name' => 'Budi Santoso']);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('reports.export.customers'));

        $response->assertOk();
        $this->assertStringContainsString('Budi Santoso', $response->streamedContent());
        $this->assertStringContainsString($customer->whatsapp, $response->streamedContent());
    }
}
