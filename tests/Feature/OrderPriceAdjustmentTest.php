<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderPriceAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderPriceAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
    }

    public function test_adjustment_records_previous_and_new_amount_with_reason(): void
    {
        $order = Order::factory()->create(['total_price' => 50000]);
        $staff = $this->staff();

        $order->adjustFinalPrice(45000, 'Potongan untuk loyaltas', $staff);

        $adjustment = $order->priceAdjustments()->first();

        $this->assertSame(50000, (int) $adjustment->previous_amount);
        $this->assertSame(45000, (int) $adjustment->new_amount);
        $this->assertSame('Potongan untuk loyaltas', $adjustment->reason);
        $this->assertSame((int) $staff->id, (int) $adjustment->user_id);
        $this->assertNotNull($adjustment->created_at);
        $this->assertSame(45000, (int) $order->fresh()->total_price);
    }

    public function test_reason_is_mandatory(): void
    {
        $order = Order::factory()->create(['total_price' => 50000]);

        try {
            $order->adjustFinalPrice(40000, '   ');
            $this->fail('ValidationException expected for empty reason.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }

        $this->assertSame(0, OrderPriceAdjustment::count());
        $this->assertSame(50000, (int) $order->fresh()->total_price);
    }

    public function test_equal_amount_creates_no_record(): void
    {
        $order = Order::factory()->create(['total_price' => 50000]);

        $order->adjustFinalPrice(50000, 'Tidak ada perubahan');

        $this->assertSame(0, OrderPriceAdjustment::count());
        $this->assertSame(50000, (int) $order->fresh()->total_price);
    }

    public function test_new_price_accepted_even_below_paid_amount(): void
    {
        $order = Order::factory()->create(['total_price' => 50000, 'paid_amount' => 20000]);
        $staff = $this->staff();

        $order->adjustFinalPrice(10000, 'Penyesuaian manual', $staff);

        $this->assertSame(10000, (int) $order->fresh()->total_price);
        $this->assertSame(1, $order->priceAdjustments()->count());
    }

    public function test_multiple_adjustments_appended_in_order(): void
    {
        $order = Order::factory()->create(['total_price' => 50000]);
        $staff = $this->staff();

        $order->adjustFinalPrice(45000, 'Alasan pertama', $staff);
        $order->adjustFinalPrice(42000, 'Alasan kedua', $staff);

        $history = $order->priceAdjustments()->orderBy('id')->get();

        $this->assertCount(2, $history);
        $this->assertSame(50000, (int) $history[0]->previous_amount);
        $this->assertSame(45000, (int) $history[0]->new_amount);
        $this->assertSame(45000, (int) $history[1]->previous_amount);
        $this->assertSame(42000, (int) $history[1]->new_amount);
        $this->assertSame(42000, (int) $order->fresh()->total_price);
    }
}
