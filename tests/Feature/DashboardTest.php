<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_counts_only_orders_past_their_estimate_as_late(): void
    {
        $this->travelTo(Carbon::parse('2026-11-11 06:47:00', 'Asia/Jakarta'));

        Order::factory()->create([
            'estimated_completion_at' => Carbon::parse('2026-11-10 00:00:00', 'Asia/Jakarta'),
        ]);
        Order::factory()->ready()->create([
            'estimated_completion_at' => Carbon::parse('2026-11-11 00:00:00', 'Asia/Jakarta'),
        ]);
        Order::factory()->create([
            'estimated_completion_at' => Carbon::parse('2026-11-12 00:00:00', 'Asia/Jakarta'),
        ]);
        Order::factory()->cancelled()->create([
            'estimated_completion_at' => Carbon::parse('2026-11-09 00:00:00', 'Asia/Jakarta'),
        ]);
        Order::factory()->handedOver(Order::HANDOVER_SUDAH_DIAMBIL)->create([
            'estimated_completion_at' => Carbon::parse('2026-11-09 00:00:00', 'Asia/Jakarta'),
        ]);
        Order::factory()->create(['estimated_completion_at' => null]);

        $response = $this->actingAs($this->staff())->get(route('dashboard'));

        $response->assertOk();
        $this->assertKpiValue($response->getContent(), 'Terlambat', '1');
    }

    public function test_dashboard_summary_cards_reflect_orders_payments_and_revenue(): void
    {
        $this->travelTo(Carbon::parse('2026-11-11 11:30:00', 'Asia/Jakarta'));

        $processingOrder = Order::factory()->create([
            'received_at' => Carbon::parse('2026-11-11 09:00:00', 'Asia/Jakarta'),
            'estimated_completion_at' => Carbon::parse('2026-11-14 00:00:00', 'Asia/Jakarta'),
            'total_price' => 100000,
        ]);
        $readyOrder = Order::factory()->ready()->paid()->create([
            'received_at' => Carbon::parse('2026-11-11 10:00:00', 'Asia/Jakarta'),
            'estimated_completion_at' => Carbon::parse('2026-11-11 00:00:00', 'Asia/Jakarta'),
            'total_price' => 50000,
            'paid_amount' => 50000,
        ]);

        Payment::factory()->create([
            'order_id' => $processingOrder->id,
            'amount' => 12000,
            'received_at' => Carbon::parse('2026-11-11 10:30:00', 'Asia/Jakarta'),
        ]);
        Payment::factory()->create([
            'order_id' => $readyOrder->id,
            'amount' => 5000,
            'received_at' => Carbon::parse('2026-11-10 10:30:00', 'Asia/Jakarta'),
        ]);

        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertKpiValue($html, 'Pesanan Hari Ini', '2');
        $this->assertKpiValue($html, 'Sedang Diproses', '1');
        $this->assertKpiValue($html, 'Siap Diambil', '1');
        $this->assertKpiValue($html, 'Belum Lunas', '1');
        $this->assertKpiValue($html, 'Terlambat', '0');
        $this->assertKpiValue($html, 'Pendapatan Hari Ini', format_rupiah(12000));
    }

    private function assertKpiValue(string $html, string $label, string $value): void
    {
        $pattern = '/<span\b[^>]*>\s*'.preg_quote($label, '/').'\s*<\/span>.*?<p\b[^>]*>\s*'
            .preg_quote($value, '/').'\s*<\/p>/s';

        $this->assertMatchesRegularExpression($pattern, $html, "Expected the {$label} KPI to show {$value}.");
    }

    private function staff(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);
    }
}
