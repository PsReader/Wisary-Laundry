<?php

namespace Tests\Feature;

use App\Livewire\Orders\Create;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class OrderEstimateTest extends TestCase
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

    public function test_reguler_order_stores_estimate_three_calendar_days_ahead(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));

        $service = Service::factory()->weightBased()->create(['estimated_days' => 3, 'is_same_day' => false]);
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '2', 'quantity' => 1, 'note' => ''],
            ])
            ->call('save');

        $order = Order::firstOrFail();

        $this->assertTrue(
            $order->estimated_completion_at->equalTo(Carbon::parse('2026-09-12 00:00:00', 'Asia/Jakarta'))
        );
    }

    public function test_kilat_always_shows_same_day_estimate_without_cutoff_tools(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 14:59:59', 'Asia/Jakarta'));

        $service = Service::factory()->weightBased()->create(['estimated_days' => 0, 'is_same_day' => true]);
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '2', 'quantity' => 1, 'note' => ''],
            ])
            ->assertSee('Estimasi selesai')
            ->assertSee('9 September 2026')
            ->assertDontSee('cutoff')
            ->assertDontSee('manual_estimate')
            ->call('save');

        $order = Order::firstOrFail();

        $this->assertTrue(
            $order->estimated_completion_at->equalTo(Carbon::parse('2026-09-09 00:00:00', 'Asia/Jakarta'))
        );
    }

    public function test_kilat_created_late_at_night_still_promises_same_day(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 23:59:00', 'Asia/Jakarta'));

        $service = Service::factory()->weightBased()->create(['estimated_days' => 0, 'is_same_day' => true]);
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '2', 'quantity' => 1, 'note' => ''],
            ])
            ->assertSee('Estimasi selesai')
            ->assertSee('9 September 2026')
            ->call('save');

        $order = Order::firstOrFail();

        $this->assertTrue(
            $order->estimated_completion_at->equalTo(Carbon::parse('2026-09-09 00:00:00', 'Asia/Jakarta'))
        );
    }
}
