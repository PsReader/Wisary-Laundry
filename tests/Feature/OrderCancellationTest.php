<?php

namespace Tests\Feature;

use App\Livewire\Orders\Show;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
    }

    public function test_cancel_requires_a_reason(): void
    {
        $order = Order::factory()->create();

        try {
            $order->cancel('   ');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('cancellation_reason', $e->errors());
        }

        $this->assertNotSame(Order::STATUS_DIBATALKAN, $order->fresh()->status);
    }

    public function test_cancel_records_status_reason_time_and_actor(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 11:30:00', 'Asia/Jakarta'));
        $order = Order::factory()->create();
        $staff = $this->staff();

        $order->cancel('Pelanggan batal order', $staff);

        $this->assertSame(Order::STATUS_DIBATALKAN, $order->fresh()->status);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'cancellation_reason' => 'Pelanggan batal order',
            'cancelled_at' => '2026-09-09 11:30:00',
            'cancelled_by' => $staff->id,
        ]);

        $history = $order->statusHistories()->latest('id')->firstOrFail();
        $this->assertSame(Order::STATUS_DIBATALKAN, $history->to_status);
        $this->assertSame('Pelanggan batal order', $history->note);
        $this->assertSame((int) $staff->id, (int) $history->user_id);

        $audit = AuditLog::query()->where('action', 'order.cancelled')->where('model_id', $order->id)->firstOrFail();
        $this->assertSame(['status' => Order::STATUS_DIBATALKAN, 'reason' => 'Pelanggan batal order'], $audit->after);
    }

    public function test_cancelled_order_cannot_be_cancelled_again(): void
    {
        $order = Order::factory()->cancelled()->create();

        try {
            $order->cancel('Dua kali batal');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }

    public function test_cancel_is_blocked_after_handover(): void
    {
        $order = Order::factory()->handedOver(Order::HANDOVER_SUDAH_DIAMBIL)->create();

        try {
            $order->cancel('Mau batal setelah diambil');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }

        $this->assertNotSame(Order::STATUS_DIBATALKAN, $order->fresh()->status);
    }

    public function test_cancelled_orders_are_visible_in_list_with_badge_and_separable_by_filter(): void
    {
        $staff = $this->staff();
        $active = Order::factory()->create();
        $cancelled = Order::factory()->cancelled('Pelanggan mengundurkan diri')->create();

        $this->actingAs($staff)
            ->get(route('orders').'?tab=cancelled')
            ->assertOk()
            ->assertSee($cancelled->order_number)
            ->assertSee('Dibatalkan');
    }

    public function test_restore_returns_order_to_diterima_and_preserves_cancellation_history(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 12:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->create();
        $staff = $this->staff();
        $order->cancel('Batal dulu', $staff);
        $admin = $this->superAdmin();

        $order->restore($admin);

        $fresh = $order->fresh();
        $this->assertSame(Order::STATUS_DITERIMA, $fresh->status);
        $this->assertNull($fresh->cancellation_reason);
        $this->assertNull($fresh->cancelled_at);
        $this->assertNull($fresh->cancelled_by);

        $history = $order->statusHistories()->orderBy('id')->get();
        $this->assertSame(2, $history->count());
        $this->assertSame(Order::STATUS_DIBATALKAN, $history[0]->to_status);
        $this->assertSame('Batal dulu', $history[0]->note);
        $this->assertSame(Order::STATUS_DIBATALKAN, $history[1]->from_status);
        $this->assertSame(Order::STATUS_DITERIMA, $history[1]->to_status);
        $this->assertSame('Pesanan dipulihkan', $history[1]->note);
        $this->assertSame((int) $admin->id, (int) $history[1]->user_id);

        $cancellation = $order->statusHistories()->where('to_status', Order::STATUS_DIBATALKAN)->firstOrFail();
        $this->assertSame((int) $staff->id, (int) $cancellation->user_id);

        $audit = AuditLog::query()->where('action', 'order.restored')->where('model_id', $order->id)->firstOrFail();
        $this->assertSame(['status' => Order::STATUS_DIBATALKAN, 'reason' => 'Batal dulu'], $audit->before);
        $this->assertSame(['status' => Order::STATUS_DITERIMA], $audit->after);
        $this->assertSame((int) $admin->id, (int) $audit->user_id);
    }

    public function test_restore_is_blocked_for_non_cancelled_order(): void
    {
        $order = Order::factory()->create();

        try {
            $order->restore();
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }

        $this->assertSame(Order::STATUS_DITERIMA, $order->fresh()->status);
        $this->assertSame(0, $order->statusHistories()->count());
        $this->assertSame(0, AuditLog::query()->where('action', 'order.restored')->count());
    }

    public function test_cancel_via_livewire_validates_reason_and_redirects(): void
    {
        $order = Order::factory()->create();
        $staff = $this->staff();

        Livewire::actingAs($staff)
            ->test(Show::class, ['order' => $order])
            ->set('cancelOrderId', $order->id)
            ->set('cancellationReason', '')
            ->call('submitCancel');

        $this->assertNotSame(Order::STATUS_DIBATALKAN, $order->fresh()->status);

        Livewire::actingAs($staff)
            ->test(Show::class, ['order' => $order])
            ->set('cancelOrderId', $order->id)
            ->set('cancellationReason', 'Pelanggan membatalkan')
            ->call('submitCancel')
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertSame(Order::STATUS_DIBATALKAN, $order->fresh()->status);
        $this->assertSame('Pelanggan membatalkan', $order->fresh()->cancellation_reason);
    }

    public function test_restore_via_livewire_redirects_with_success(): void
    {
        $cancelled = Order::factory()->cancelled('Batal')->create();

        Livewire::actingAs($this->superAdmin())
            ->test(Show::class, ['order' => $cancelled])
            ->call('openRestore')
            ->call('submitRestore')
            ->assertRedirect(route('orders.show', $cancelled))
            ->assertSessionHas('success');

        $this->assertSame(Order::STATUS_DITERIMA, $cancelled->fresh()->status);
    }
}
