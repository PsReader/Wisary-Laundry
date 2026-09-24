<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\PendingPayment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentRefundTest extends TestCase
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

    public function test_full_payment_marks_order_lunas(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->create(['total_price' => 50000]);
        $staff = $this->staff();

        $order->recordPayment(50000, Payment::METHOD_TUNAI, note: 'Lunas di counter', user: $staff);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'paid_amount' => 50000,
            'payment_status' => Order::PAYMENT_LUNAS,
            'payment_method' => Payment::METHOD_TUNAI,
        ]);
        $this->assertSame(0, (int) round($order->remainingAmount));

        $payment = $order->payments()->firstOrFail();
        $this->assertSame((int) $staff->id, (int) $payment->user_id);
        $this->assertSame('Lunas di counter', $payment->note);
        $this->assertTrue($payment->received_at->equalTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta')));
    }

    public function test_multiple_partial_payments_accumulate_to_lunas(): void
    {
        $order = Order::factory()->create(['total_price' => 100000]);

        $order->recordPayment(30000, Payment::METHOD_TRANSFER);
        $this->assertSame(Order::PAYMENT_LUNAS_SEBAGIAN, $order->fresh()->payment_status);
        $this->assertSame(70000, (int) round($order->fresh()->remainingAmount));

        $order->recordPayment(20000, Payment::METHOD_TUNAI);
        $this->assertSame(Order::PAYMENT_LUNAS_SEBAGIAN, $order->fresh()->payment_status);
        $this->assertSame(50000, (int) round($order->fresh()->remainingAmount));

        $order->recordPayment(50000, Payment::METHOD_KARTU);
        $this->assertSame(Order::PAYMENT_LUNAS, $order->fresh()->payment_status);
        $this->assertSame(0, (int) round($order->fresh()->remainingAmount));
        $this->assertSame(3, $order->payments()->count());
    }

    public function test_overpayment_is_rejected(): void
    {
        $order = Order::factory()->create(['total_price' => 50000]);

        try {
            $order->recordPayment(60000, Payment::METHOD_TUNAI);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('amount', $e->errors());
        }

        $this->assertSame(0, $order->payments()->count());
        $this->assertSame(Order::PAYMENT_BELUM_LUNAS, $order->fresh()->payment_status);
    }

    public function test_refund_requires_reason(): void
    {
        $order = Order::factory()->create(['total_price' => 50000]);
        $order->recordPayment(50000, Payment::METHOD_TUNAI);

        try {
            $order->recordRefund(10000, reason: '  ', method: Payment::METHOD_TUNAI);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }

        $this->assertSame(0, $order->refunds()->count());
    }

    public function test_partial_refund_changes_status_to_lunas_sebagian_and_updates_remaining(): void
    {
        $order = Order::factory()->create(['total_price' => 50000]);
        $order->recordPayment(50000, Payment::METHOD_TUNAI);
        $this->assertSame(0, (int) round($order->remainingAmount));

        $order->recordRefund(10000, reason: 'Kelebihan bayar', method: Payment::METHOD_TUNAI);

        $this->assertSame(Order::PAYMENT_LUNAS_SEBAGIAN, $order->fresh()->payment_status);
        $this->assertSame(10000, (int) round($order->fresh()->remainingAmount));

        $refund = $order->refunds()->firstOrFail();
        $this->assertSame('Kelebihan bayar', $refund->reason);
    }

    public function test_full_refund_marks_order_dikembalikan(): void
    {
        $order = Order::factory()->create(['total_price' => 50000]);
        $order->recordPayment(50000, Payment::METHOD_TUNAI);

        $order->recordRefund(50000, reason: 'Pembatalan', method: Payment::METHOD_TUNAI);

        $this->assertSame(Order::PAYMENT_DIKEMBALIKAN, $order->fresh()->payment_status);
        $this->assertSame(50000, (int) round($order->fresh()->remainingAmount));
    }

    public function test_refund_cannot_exceed_total_paid(): void
    {
        $order = Order::factory()->create(['total_price' => 50000]);
        $order->recordPayment(30000, Payment::METHOD_TUNAI);
        $order->recordRefund(20000, reason: 'Sebagian', method: Payment::METHOD_TUNAI);

        try {
            $order->recordRefund(20000, reason: 'Lebih dari bayar', method: Payment::METHOD_TUNAI);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('amount', $e->errors());
        }

        $this->assertSame(1, $order->refunds()->count());
        $this->assertSame(40000, (int) round($order->fresh()->remainingAmount));
    }

    public function test_refund_does_not_remove_original_payment_history(): void
    {
        $order = Order::factory()->create(['total_price' => 50000]);
        $order->recordPayment(50000, Payment::METHOD_TUNAI);
        $order->recordRefund(50000, reason: 'Pengembalian penuh', method: Payment::METHOD_TRANSFER);

        $this->assertSame(1, $order->payments()->count());
        $this->assertSame(1, $order->refunds()->count());
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'amount' => 50000]);
        $this->assertDatabaseHas('refunds', ['order_id' => $order->id, 'amount' => 50000, 'method' => Payment::METHOD_TRANSFER]);
    }

    public function test_dashboard_lists_unpaid_orders_with_remaining(): void
    {
        $order = Order::factory()->partial(20000)->create(['total_price' => 50000]);

        $this->actingAs($this->staff())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum Lunas')
            ->assertSee($order->customer->name)
            ->assertSee('Catat Pembayaran')
            ->assertSee(format_rupiah($order->remainingAmount));
    }

    public function test_payment_via_livewire_records_and_redirects(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->create(['total_price' => 50000]);
        $staff = $this->staff();

        Livewire::actingAs($staff)
            ->test(PendingPayment::class)
            ->set('paymentOrderId', $order->id)
            ->set('paymentAmount', 30000)
            ->set('paymentMethod', Payment::METHOD_TRANSFER)
            ->call('submitPayment')
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'paid_amount' => 30000,
            'payment_status' => Order::PAYMENT_LUNAS_SEBAGIAN,
            'payment_method' => Payment::METHOD_TRANSFER,
        ]);

        $payment = $order->payments()->firstOrFail();
        $this->assertSame((int) $staff->id, (int) $payment->user_id);
        $this->assertTrue($payment->received_at->equalTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta')));
    }

    public function test_refund_via_livewire_records_and_redirects(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->create(['total_price' => 50000]);
        $order->recordPayment(50000, Payment::METHOD_TUNAI);
        $staff = $this->staff();

        Livewire::actingAs($staff)
            ->test(PendingPayment::class)
            ->set('refundOrderId', $order->id)
            ->set('refundAmount', 10000)
            ->set('refundReason', 'Kelebihan tarif')
            ->set('refundMethod', Payment::METHOD_TRANSFER)
            ->call('submitRefund')
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertSame(Order::PAYMENT_LUNAS_SEBAGIAN, $order->fresh()->payment_status);

        $refund = $order->refunds()->firstOrFail();
        $this->assertSame('Kelebihan tarif', $refund->reason);
        $this->assertSame((int) $staff->id, (int) $refund->processed_by);
        $this->assertTrue($refund->refunded_at->equalTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta')));
    }

    public function test_payment_timeline_is_visible_on_dashboard(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->create(['total_price' => 50000]);
        $staff = $this->staff();

        $order->recordPayment(50000, Payment::METHOD_TUNAI, user: $staff);
        $order->recordRefund(10000, reason: 'Selisih', method: Payment::METHOD_TRANSFER, user: $staff);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Riwayat pembayaran')
            ->assertSee('Bayar '.format_rupiah(50000))
            ->assertSee('Refund '.format_rupiah(10000))
            ->assertSee('9 September 2026 · 10.00 WIB');
    }
}
