<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\LoginActivity;
use App\Models\Order;
use App\Models\OrderHandoverHistory;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Support\Audit;
use App\Support\AuditFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
    }

    private function order(User $user, array $attributes = []): Order
    {
        return Order::factory()->create(array_merge(['created_by' => $user->id], $attributes));
    }

    public function test_page_lists_order_creation_events(): void
    {
        $admin = $this->admin();
        $order = $this->order($admin, ['total_price' => 45000]);

        $this->actingAs($admin)->get('/audit-logs')
            ->assertOk()
            ->assertSee('Pesanan dibuat')
            ->assertSee($order->order_number)
            ->assertSee('Pesanan Dibuat');
    }

    public function test_feed_shows_status_change(): void
    {
        $admin = $this->admin();
        $order = $this->order($admin);
        $order->changeStatus(Order::STATUS_SEDANG_DICUCI, $admin);

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay(), null, AuditFeed::GROUP_ORDER_STATUS);

        $this->assertStringContainsString('Diterima', $feed->first()['detail']);
        $this->assertStringContainsString('Sedang Dicuci', $feed->first()['detail']);
    }

    public function test_feed_shows_handover_events(): void
    {
        $admin = $this->admin();
        $order = $this->order($admin, ['status' => Order::STATUS_SIAP_DIAMBIL, 'method' => Order::METHOD_DIAMBIL]);
        $order->recordHandover(OrderHandoverHistory::ACTION_SUDAH_DIAMBIL, note: 'Sesuai nota', user: $admin);

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay());

        $this->assertContains(AuditFeed::GROUP_ORDER_HANDOVER, $feed->pluck('type')->all());
        $this->assertStringContainsString('Serah terima', $feed->firstWhere('type', AuditFeed::GROUP_ORDER_HANDOVER)['detail']);
    }

    public function test_feed_shows_payments(): void
    {
        $admin = $this->admin();
        $order = $this->order($admin, ['total_price' => 50000]);
        $order->recordPayment(50000, Payment::METHOD_TUNAI, note: 'Lunas', user: $admin);

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay());

        $entry = $feed->firstWhere('type', AuditFeed::GROUP_ORDER_PAYMENT);
        $this->assertNotNull($entry);
        $this->assertStringContainsString('Pembayaran', $entry['detail']);
    }

    public function test_feed_shows_refunds(): void
    {
        $admin = $this->admin();
        $order = $this->order($admin, ['total_price' => 50000]);
        $order->recordPayment(50000, Payment::METHOD_TUNAI, user: $admin);
        $order->recordRefund(10000, 'Kelebihan bayar', Payment::METHOD_TUNAI, user: $admin);

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay());

        $entry = $feed->firstWhere('type', AuditFeed::GROUP_ORDER_REFUND);
        $this->assertNotNull($entry);
        $this->assertStringContainsString('Kelebihan bayar', $entry['detail']);
    }

    public function test_feed_shows_price_adjustments(): void
    {
        $admin = $this->admin();
        $order = $this->order($admin, ['total_price' => 50000]);
        $order->adjustFinalPrice(45000, 'Potongan loyaltas', $admin);

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay());

        $entry = $feed->firstWhere('type', AuditFeed::GROUP_ORDER_PRICE_ADJUST);
        $this->assertNotNull($entry);
        $this->assertStringContainsString('Harga final', $entry['detail']);
        $this->assertStringContainsString('Potongan loyaltas', $entry['detail']);
    }

    public function test_estimate_override_is_audited_and_listed(): void
    {
        $admin = $this->admin();
        $order = $this->order($admin);

        Audit::record($admin, 'order.estimate_overridden', $order, [
            'estimated_completion_at' => null,
        ], [
            'estimated_completion_at' => '2026-09-20',
        ]);

        $this->assertDatabaseCount('audit_logs', 1);

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay());

        $entry = $feed->firstWhere('type', AuditFeed::GROUP_ORDER_ESTIMATE);
        $this->assertNotNull($entry);
        $this->assertStringContainsString('Estimasi selesai', $entry['detail']);
    }

    public function test_feed_shows_customer_events(): void
    {
        $admin = $this->admin();
        $customer = Customer::factory()->create(['name' => 'Siti Aminah']);

        Audit::record($admin, 'customer.created', $customer, null, [
            'name' => $customer->name,
            'whatsapp' => $customer->whatsapp,
        ]);

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay());

        $entry = $feed->firstWhere('type', AuditFeed::GROUP_CUSTOMER);
        $this->assertNotNull($entry);
        $this->assertStringContainsString('Siti Aminah', $entry['detail']);
    }

    public function test_feed_shows_service_events(): void
    {
        $admin = $this->admin();
        $service = Service::factory()->create(['name' => 'Cuci Lipat']);

        Audit::record($admin, 'service.toggled', $service, ['is_active' => true], ['is_active' => false]);

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay());

        $entry = $feed->firstWhere('type', AuditFeed::GROUP_SERVICE);
        $this->assertNotNull($entry);
        $this->assertStringContainsString('Cuci Lipat', $entry['detail']);
    }

    public function test_staff_account_creation_is_audited(): void
    {
        $admin = $this->admin();
        $count = AuditLog::count();

        $this->actingAs($admin)->post(route('staff.store'), [
            'name' => 'Ani',
            'email' => 'ani@wisary.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame($count + 1, AuditLog::count());

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay(), null, AuditFeed::GROUP_USER);
        $this->assertStringContainsString('Ani', $feed->first()['detail'] ?? '');
    }

    public function test_password_reset_is_audited(): void
    {
        $admin = $this->admin();
        $staff = $this->staff();

        $this->actingAs($admin)->post(route('staff.password-reset'), [
            'user_id' => $staff->id,
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'user.password_reset',
        ]);
    }

    public function test_login_failed_and_logout_are_listed(): void
    {
        $admin = $this->admin();

        LoginActivity::query()->create([
            'user_id' => $admin->id,
            'type' => LoginActivity::TYPE_LOGIN,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 iPhone',
        ]);
        LoginActivity::query()->create([
            'user_id' => null,
            'type' => LoginActivity::TYPE_FAILED,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'email' => 'bob@wisary.test',
        ]);
        LoginActivity::query()->create([
            'user_id' => $admin->id,
            'type' => LoginActivity::TYPE_LOGOUT,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $this->actingAs($admin)->get('/audit-logs')
            ->assertSee('Berhasil masuk')
            ->assertSee('Percobaan login gagal untuk bob@wisary.test')
            ->assertSee('Keluar dari aplikasi');
    }

    public function test_feed_filters_by_type_group(): void
    {
        $admin = $this->admin();
        $this->order($admin);
        $customer = Customer::factory()->create();
        Audit::record($admin, 'customer.updated', $customer);

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay(), null, AuditFeed::GROUP_CUSTOMER);

        $this->assertNotEmpty($feed);
        $this->assertSame([AuditFeed::GROUP_CUSTOMER], $feed->pluck('type')->unique()->values()->all());
    }

    public function test_feed_filters_by_user(): void
    {
        $admin = $this->admin();
        $other = $this->staff();
        $this->order($admin);
        $this->order($other);

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay(), $admin->id);

        $this->assertNotEmpty($feed);
        $this->assertTrue($feed->every(fn ($row) => $row['userId'] === (int) $admin->id));
    }

    public function test_feed_filters_by_period(): void
    {
        $admin = $this->admin();

        $oldOrder = $this->order($admin);
        $oldOrder->setCreatedAt(now()->subDays(10));
        $oldOrder->save();

        $feed = AuditFeed::feed(now()->startOfDay(), now()->endOfDay(), null, AuditFeed::GROUP_ORDER_CREATED);

        $this->assertEmpty($feed);
    }

    public function test_staff_cannot_access_audit_logs(): void
    {
        $this->actingAs($this->staff())
            ->get('/audit-logs')
            ->assertForbidden()
            ->assertSee('Akses Ditolak');
    }

    public function test_route_exists_within_super_admin_group(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('audit-logs'))->assertOk();

        $this->assertTrue(Route::has('audit-logs'));
    }

    public function test_staff_denied_by_order_policies(): void
    {
        $staff = $this->staff();
        $order = $this->order($staff);
        $cancelled = $this->order($staff, ['status' => Order::STATUS_DIBATALKAN]);

        $this->assertFalse($staff->can('adjustPrice', $order));
        $this->assertFalse($staff->can('overrideEstimate', $order));

        $admin = $this->admin();
        $this->assertTrue($admin->can('adjustPrice', $order));
        $this->assertTrue($admin->can('overrideEstimate', $order));
        $this->assertTrue($admin->can('restore', $cancelled));
        $this->assertFalse($staff->can('restore', $cancelled));
    }

    public function test_staff_denied_by_service_and_user_policies(): void
    {
        $staff = $this->staff();
        $admin = $this->admin();

        $this->assertFalse($staff->can('create', Service::class));
        $this->assertTrue($admin->can('create', Service::class));
        $this->assertFalse($staff->can('manage', User::class));
        $this->assertTrue($admin->can('manage', User::class));

        $service = Service::factory()->create();
        $this->assertFalse($staff->can('update', $service));
        $this->assertTrue($admin->can('update', $service));
    }

    public function test_customer_can_be_managed_by_staff_and_deleted_only_by_admin(): void
    {
        $staff = $this->staff();
        $admin = $this->admin();
        $customer = Customer::factory()->create();

        $this->assertTrue($staff->can('create', Customer::class));
        $this->assertTrue($staff->can('update', $customer));
        $this->assertFalse($staff->can('delete', $customer));
        $this->assertTrue($admin->can('delete', $customer));
    }

    public function test_sensitive_records_have_no_delete_routes(): void
    {
        $routes = collect(Route::getRoutes()->getRoutesByName());

        foreach (['order', 'payment', 'refund', 'audit', 'customer'] as $segment) {
            foreach ($routes as $name => $route) {
                if (str_contains($name, $segment) && in_array('DELETE', $route->methods(), true)) {
                    $this->fail("Route {$name} allows DELETE on {$segment} records.");
                }
            }
        }

        $this->assertTrue(true);
    }

    public function test_500_page_is_generic_when_debug_disabled(): void
    {
        config(['app.debug' => false]);

        Route::get('/__throwing_route__', fn () => throw new \RuntimeException('Internal detail that must not leak'));

        $this->actingAs($this->admin())
            ->get('/__throwing_route__')
            ->assertStatus(500)
            ->assertSee('Terjadi Kesalahan')
            ->assertDontSee('Internal detail that must not leak');
    }

    public function test_404_page_is_custom(): void
    {
        $this->actingAs($this->admin())
            ->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan');
    }
}
