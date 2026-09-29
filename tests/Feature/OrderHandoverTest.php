<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\PendingHandover;
use App\Livewire\Orders\Show;
use App\Models\Order;
use App\Models\OrderHandoverHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OrderHandoverTest extends TestCase
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

    public function test_pickup_handover_records_history_and_updates_status(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->ready()->pickup()->create();
        $staff = $this->staff();

        $order->recordHandover(OrderHandoverHistory::ACTION_SUDAH_DIAMBIL, note: 'Sesuai nota', user: $staff);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'handover_status' => Order::HANDOVER_SUDAH_DIAMBIL,
            'method' => Order::METHOD_DIAMBIL,
        ]);

        $history = $order->handoverHistories()->firstOrFail();

        $this->assertSame(OrderHandoverHistory::ACTION_SUDAH_DIAMBIL, $history->handover_action);
        $this->assertSame((int) $staff->id, (int) $history->recorded_by);
        $this->assertSame('Sesuai nota', $history->note);
        $this->assertNull($history->failure_reason);
        $this->assertTrue($history->occurred_at->equalTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta')));
    }

    public function test_delivery_handover_with_address_succeeds(): void
    {
        $order = Order::factory()->ready()->delivery()->create();

        $order->recordHandover(OrderHandoverHistory::ACTION_SUDAH_DIANTAR);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'handover_status' => Order::HANDOVER_SUDAH_DIANTAR,
            'method' => Order::METHOD_DIANTAR,
            'delivery_address' => 'Jl. Contoh No. 1, Jakarta',
        ]);
    }

    public function test_delivery_handover_requires_address(): void
    {
        $order = Order::factory()->ready()->delivery('')->create();

        try {
            $order->recordHandover(OrderHandoverHistory::ACTION_SUDAH_DIANTAR);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('delivery_address', $e->errors());
        }

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
        ]);

        $order->recordHandover(OrderHandoverHistory::ACTION_SUDAH_DIANTAR, deliveryAddress: 'Perum Griya Asri Blok C2 No. 5');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'handover_status' => Order::HANDOVER_SUDAH_DIANTAR,
            'delivery_address' => 'Perum Griya Asri Blok C2 No. 5',
        ]);
    }

    public function test_method_is_reconciled_when_action_differs_from_plan(): void
    {
        $pickupThenDelivered = Order::factory()->ready()->pickup()->create();
        $pickupThenDelivered->recordHandover(OrderHandoverHistory::ACTION_SUDAH_DIANTAR, deliveryAddress: 'Jl. Baru No. 2');

        $this->assertDatabaseHas('orders', [
            'id' => $pickupThenDelivered->id,
            'handover_status' => Order::HANDOVER_SUDAH_DIANTAR,
            'method' => Order::METHOD_DIANTAR,
            'delivery_address' => 'Jl. Baru No. 2',
        ]);

        $history = $pickupThenDelivered->handoverHistories()->firstOrFail();
        $this->assertStringContainsString('Metode disesuaikan: '.Order::methodLabel(Order::METHOD_DIANTAR), (string) $history->note);

        $deliveryThenPickedUp = Order::factory()->ready()->delivery()->create();
        $deliveryThenPickedUp->recordHandover(OrderHandoverHistory::ACTION_SUDAH_DIAMBIL);

        $this->assertDatabaseHas('orders', [
            'id' => $deliveryThenPickedUp->id,
            'handover_status' => Order::HANDOVER_SUDAH_DIAMBIL,
            'method' => Order::METHOD_DIAMBIL,
        ]);

        $history = $deliveryThenPickedUp->handoverHistories()->firstOrFail();
        $this->assertStringContainsString('Metode disesuaikan: '.Order::methodLabel(Order::METHOD_DIAMBIL), (string) $history->note);
    }

    public function test_failed_delivery_requires_reason(): void
    {
        $order = Order::factory()->ready()->delivery()->create();

        try {
            $order->recordHandover(OrderHandoverHistory::ACTION_GAGAL, failureReason: '   ');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('failure_reason', $e->errors());
        }

        $this->assertDatabaseMissing('order_handover_histories', ['order_id' => $order->id]);
    }

    public function test_failed_delivery_keeps_order_pending_then_retry_succeeds(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->ready()->delivery()->create();
        $staff = $this->staff();

        $order->recordHandover(OrderHandoverHistory::ACTION_GAGAL, failureReason: 'Pelanggan tidak di rumah', user: $staff);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
        ]);

        $failed = $order->handoverHistories()->firstOrFail();
        $this->assertSame(OrderHandoverHistory::ACTION_GAGAL, $failed->handover_action);
        $this->assertSame('Pelanggan tidak di rumah', $failed->failure_reason);
        $this->assertSame((int) $staff->id, (int) $failed->recorded_by);
        $this->assertTrue($failed->occurred_at->equalTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta')));

        $order->recordHandover(OrderHandoverHistory::ACTION_SUDAH_DIANTAR);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'handover_status' => Order::HANDOVER_SUDAH_DIANTAR,
        ]);

        $this->assertSame(2, $order->handoverHistories()->count());
    }

    public function test_handover_blocked_when_order_is_not_ready(): void
    {
        $order = Order::factory()->received()->pickup()->create();

        try {
            $order->recordHandover(OrderHandoverHistory::ACTION_SUDAH_DIAMBIL);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('handover_action', $e->errors());
        }

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
        ]);
        $this->assertSame(0, OrderHandoverHistory::count());
    }

    public function test_order_detail_shows_handover_actions_when_ready(): void
    {
        $pickupOrder = Order::factory()->ready()->pickup()->create();
        $deliveryOrder = Order::factory()->ready()->delivery()->create();

        $this->actingAs($this->staff())
            ->get(route('orders.show', $pickupOrder))
            ->assertOk()
            ->assertSee($pickupOrder->customer->name)
            ->assertSee('Tandai Sudah Diambil');

        $this->actingAs($this->staff())
            ->get(route('orders.show', $deliveryOrder))
            ->assertOk()
            ->assertSee($deliveryOrder->customer->name)
            ->assertSee('Tandai Sudah Diantar')
            ->assertSee('Catat Gagal Delivery');
    }

    public function test_staff_can_submit_handover_from_order_detail(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->ready()->pickup()->create();
        $staff = $this->staff();

        Livewire::actingAs($staff)
            ->test(Show::class, ['order' => $order])
            ->set('handoverOrderId', $order->id)
            ->set('handoverAction', OrderHandoverHistory::ACTION_SUDAH_DIAMBIL)
            ->set('handoverNote', 'Diambil pelanggan')
            ->call('submitHandover')
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'handover_status' => Order::HANDOVER_SUDAH_DIAMBIL,
        ]);

        $history = $order->handoverHistories()->firstOrFail();
        $this->assertSame(OrderHandoverHistory::ACTION_SUDAH_DIAMBIL, $history->handover_action);
        $this->assertSame((int) $staff->id, (int) $history->recorded_by);
        $this->assertSame('Diambil pelanggan', $history->note);
        $this->assertTrue($history->occurred_at->equalTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta')));
    }

    public function test_staff_can_submit_failed_delivery_from_order_detail(): void
    {
        $order = Order::factory()->ready()->delivery()->create();
        $staff = $this->staff();

        Livewire::actingAs($staff)
            ->test(Show::class, ['order' => $order])
            ->set('handoverOrderId', $order->id)
            ->set('handoverAction', OrderHandoverHistory::ACTION_GAGAL)
            ->set('handoverFailureReason', 'Alamat tidak ditemukan')
            ->call('submitHandover')
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
        ]);

        $history = $order->handoverHistories()->firstOrFail();
        $this->assertSame(OrderHandoverHistory::ACTION_GAGAL, $history->handover_action);
        $this->assertSame('Alamat tidak ditemukan', $history->failure_reason);
    }

    public function test_dashboard_lists_pending_handover_orders(): void
    {
        $order = Order::factory()->ready()->pickup()->create();

        $this->actingAs($this->staff())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Menunggu Serah Terima')
            ->assertSee($order->customer->name)
            ->assertSee('Tandai Sudah Diambil');
    }

    public function test_dashboard_can_complete_handover(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->ready()->pickup()->create();
        $staff = $this->staff();

        Livewire::actingAs($staff)
            ->test(PendingHandover::class)
            ->set('handoverOrderId', $order->id)
            ->set('handoverAction', OrderHandoverHistory::ACTION_SUDAH_DIAMBIL)
            ->call('submitHandover')
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'handover_status' => Order::HANDOVER_SUDAH_DIAMBIL,
        ]);
    }

    public function test_handover_cannot_be_recorded_twice(): void
    {
        $order = Order::factory()->handedOver(OrderHandoverHistory::ACTION_SUDAH_DIAMBIL)->create();

        try {
            $order->recordHandover(OrderHandoverHistory::ACTION_SUDAH_DIAMBIL);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('handover_action', $e->errors());
        }

        // `gagal` tidak mengubah handover_status, jadi tanpa penjaga status ini
        // pesanan yang sudah diserahkan masih bisa dicatat gagal.
        try {
            $order->recordHandover(OrderHandoverHistory::ACTION_GAGAL, failureReason: 'Pelanggan tidak ada');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('handover_action', $e->errors());
        }

        $this->assertSame(0, $order->handoverHistories()->count());
        $this->assertSame(Order::HANDOVER_SUDAH_DIAMBIL, $order->fresh()->handover_status);
    }
}
