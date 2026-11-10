<?php

namespace Tests\Feature;

use App\Livewire\Orders\Create;
use App\Livewire\Orders\Index;
use App\Livewire\Orders\Show;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class StatusTransitionTest extends TestCase
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

    public function test_order_list_lists_orders_and_links_to_detail(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));

        $order = Order::factory()->create(['order_number' => '0001092026']);
        $customer = $order->customer;

        $this->actingAs($this->staff())
            ->get(route('orders'))
            ->assertOk()
            ->assertSee('0001092026')
            ->assertSee($customer->name)
            ->assertSee('Diterima')
            ->assertSee('pesanan ditemukan')
            ->assertSee(route('orders.show', $order));
    }

    public function test_staff_can_search_orders_by_whatsapp_number(): void
    {
        $order = Order::factory()->create(['order_number' => '0002090926']);
        $customer = $order->customer;

        $component = Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->set('search', $customer->whatsapp);

        $component->assertSee($order->order_number);
    }

    public function test_staff_can_filter_orders_by_received_date(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $today = Order::factory()->create(['order_number' => '0001090926']);
        $yesterday = Order::factory()->create([
            'order_number' => '0001090925',
            'received_at' => Carbon::parse('2026-09-08 10:00:00', 'Asia/Jakarta'),
        ]);

        $component = Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->set('receivedDateFilter', '2026-09-09');

        $component->assertSee($today->order_number);
        $component->assertDontSee($yesterday->order_number);
    }

    public function test_status_pills_filter_and_toggle_clear(): void
    {
        $ready = Order::factory()->ready()->create(['order_number' => '1111090926']);
        $received = Order::factory()->received()->create(['order_number' => '2222090926']);

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->assertSee('Siap Diambil (1)')
            ->call('setStatusFilter', Order::STATUS_SIAP_DIAMBIL)
            ->assertSet('statusFilter', Order::STATUS_SIAP_DIAMBIL)
            ->assertSee($ready->order_number)
            ->assertDontSee($received->order_number)
            ->call('setStatusFilter', Order::STATUS_SIAP_DIAMBIL)
            ->assertSet('statusFilter', '');
    }

    public function test_handed_over_order_shows_handover_status_instead_of_ready(): void
    {
        $ready = Order::factory()->ready()->pickup()->create(['order_number' => '5555090926']);
        $taken = Order::factory()->handedOver(Order::HANDOVER_SUDAH_DIAMBIL)->pickup()->create(['order_number' => '6666090926']);

        $this->assertSame('Siap Diambil', $ready->displayStatusLabel());
        $this->assertSame('Sudah Diambil', $taken->displayStatusLabel());

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->assertSee('Semua (2)')
            ->assertSee('Siap Diambil (1)')
            ->call('setStatusFilter', Order::STATUS_SIAP_DIAMBIL)
            ->assertSee($ready->order_number)
            ->assertDontSee($taken->order_number);

        $this->actingAs($this->staff())
            ->get(route('orders.show', $taken))
            ->assertSee('Sudah Diambil')
            ->assertDontSee('Siap Diambil');
    }

    public function test_unknown_sort_column_falls_back_instead_of_erroring(): void
    {
        $this->actingAs($this->staff())
            ->get(route('orders', ['sortBy' => 'not_a_column']))
            ->assertOk();
    }

    public function test_date_quick_chips_set_expected_ranges(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $today = Order::factory()->received()->create(['order_number' => '3333090926']);
        $old = Order::factory()->received()->create([
            'order_number' => '4444080926',
            'received_at' => Carbon::parse('2026-09-01 10:00:00', 'Asia/Jakarta'),
        ]);

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->call('setDateFilter', 'today')
            ->assertSet('receivedDateFilter', '2026-09-09')
            ->call('setDateFilter', '7d')
            ->assertSet('recent7Filter', '7')
            ->assertSet('receivedDateFilter', '')
            ->assertSee($today->order_number)
            ->assertDontSee($old->order_number);
    }

    public function test_clicking_selected_date_quick_chip_clears_the_date_filter(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $today = Order::factory()->received()->create(['order_number' => '3333090926']);
        $old = Order::factory()->received()->create([
            'order_number' => '4444080926',
            'received_at' => Carbon::parse('2026-09-01 10:00:00', 'Asia/Jakarta'),
        ]);

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->call('setDateFilter', 'today')
            ->assertSet('receivedDateFilter', '2026-09-09')
            ->assertSee($today->order_number)
            ->assertDontSee($old->order_number)
            ->call('setDateFilter', 'today')
            ->assertSet('receivedDateFilter', '')
            ->assertSet('recent7Filter', '')
            ->assertSee($today->order_number)
            ->assertSee($old->order_number);
    }

    public function test_clicking_selected_seven_day_chip_clears_the_date_filter(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $recent = Order::factory()->received()->create(['order_number' => '3333090926']);
        $old = Order::factory()->received()->create([
            'order_number' => '4444080926',
            'received_at' => Carbon::parse('2026-09-01 10:00:00', 'Asia/Jakarta'),
        ]);

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->call('setDateFilter', '7d')
            ->assertSet('recent7Filter', '7')
            ->assertSee($recent->order_number)
            ->assertDontSee($old->order_number)
            ->call('setDateFilter', '7d')
            ->assertSet('recent7Filter', '')
            ->assertSet('receivedDateFilter', '')
            ->assertSee($recent->order_number)
            ->assertSee($old->order_number);
    }

    public function test_reset_filters_clears_everything_and_hides_the_clear_action(): void
    {
        $order = Order::factory()->ready()->create(['order_number' => '5555090926']);

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->set('search', 'zzz-tidak-ada')
            ->set('statusFilter', Order::STATUS_SIAP_DIAMBIL)
            ->set('receivedDateFilter', '2020-01-01')
            ->assertDontSee($order->order_number)
            ->assertSee('Hapus filter')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', '')
            ->assertSet('paymentFilter', '')
            ->assertSet('handoverFilter', '')
            ->assertSet('methodFilter', '')
            ->assertSet('receivedDateFilter', '')
            ->assertSet('recent7Filter', '')
            ->assertSee($order->order_number)
            ->assertDontSee('Hapus filter');
    }

    public function test_staff_can_advance_status_and_history_is_recorded(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->create();
        $staff = $this->staff();

        Livewire::actingAs($staff)
            ->test(Show::class, ['order' => $order])
            ->call('changeStatus', $order->id, Order::STATUS_SEDANG_DIKERINGKAN)
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_SEDANG_DIKERINGKAN]);

        $history = $order->statusHistories()->firstOrFail();

        $this->assertSame(Order::STATUS_DITERIMA, $history->from_status);
        $this->assertSame(Order::STATUS_SEDANG_DIKERINGKAN, $history->to_status);
        $this->assertSame((int) $staff->id, (int) $history->user_id);
        $this->assertTrue($history->created_at->equalTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta')));
    }

    public function test_skip_ahead_is_allowed(): void
    {
        $order = Order::factory()->create();
        $staff = $this->staff();

        Livewire::actingAs($staff)
            ->test(Show::class, ['order' => $order])
            ->call('changeStatus', $order->id, Order::STATUS_SIAP_DIAMBIL)
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_SIAP_DIAMBIL]);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => Order::STATUS_DITERIMA,
            'to_status' => Order::STATUS_SIAP_DIAMBIL,
        ]);
    }

    public function test_backward_transition_is_rejected_without_changes(): void
    {
        $order = Order::factory()->create(['status' => Order::STATUS_SEDANG_DICUCI]);
        $staff = $this->staff();

        Livewire::actingAs($staff)
            ->test(Show::class, ['order' => $order])
            ->call('changeStatus', $order->id, Order::STATUS_DITERIMA)
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_SEDANG_DICUCI]);
        $this->assertSame(0, OrderStatusHistory::count());
    }

    public function test_timeline_is_visible_on_order_detail(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $order = Order::factory()->create();
        $staff = $this->staff();

        $order->statusHistories()->create([
            'from_status' => Order::STATUS_DITERIMA,
            'to_status' => Order::STATUS_SEDANG_DICUCI,
            'user_id' => $staff->id,
        ]);
        $order->update(['status' => Order::STATUS_SEDANG_DICUCI]);

        $this->actingAs($staff)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Diterima')
            ->assertSee('Sedang Dicuci')
            ->assertSee($staff->name)
            ->assertSee('9 September 2026 · 10.00 WIB');
    }

    public function test_order_creation_records_initial_diterima_history(): void
    {
        $this->travelTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta'));
        $service = Service::factory()->weightBased()->create(['price' => 5000]);
        $customer = Customer::factory()->create();
        $staff = $this->staff();

        Livewire::actingAs($staff)
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '2', 'quantity' => 1, 'note' => ''],
            ])
            ->call('save');

        $order = Order::firstOrFail();

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => Order::STATUS_DITERIMA,
            'user_id' => (int) $staff->id,
            'note' => 'Pesanan diterima',
        ]);

        $history = $order->statusHistories()->firstOrFail();
        $this->assertTrue($history->created_at->equalTo(Carbon::parse('2026-09-09 10:00:00', 'Asia/Jakarta')));

        $this->actingAs($staff)
            ->get(route('orders.show', $order))
            ->assertSee('Pesanan diterima')
            ->assertSee('Diterima');
    }

    /**
     * @param  array<int, array{service_name: string, category: string}>  $definitions
     */
    private function orderWithItems(array $definitions): Order
    {
        $order = Order::factory()->received()->create();

        foreach ($definitions as $definition) {
            $service = Service::factory()->weightBased()->create([
                'name' => $definition['service_name'],
                'category' => $definition['category'],
            ]);

            OrderItem::factory()->create([
                'order_id' => $order->id,
                'service_id' => $service->id,
                'service_name' => $definition['service_name'],
            ]);
        }

        return $order;
    }

    public function test_flow_buttons_skip_ironing_for_lipat_only_order(): void
    {
        $order = $this->orderWithItems([
            ['service_name' => 'Cuci Lipat Reguler', 'category' => 'Cuci Lipat'],
        ]);

        $this->actingAs($this->staff())
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Sedang Dilipat')
            ->assertDontSee('Sedang Disetrika');
    }

    public function test_flow_buttons_skip_folding_for_iron_only_order(): void
    {
        $order = $this->orderWithItems([
            ['service_name' => 'Setrika Reguler', 'category' => 'Setrika'],
        ]);

        $this->actingAs($this->staff())
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Sedang Disetrika')
            ->assertDontSee('Sedang Dilipat');
    }

    public function test_flow_buttons_for_cuci_setrika_order_iron_but_no_folding(): void
    {
        $order = $this->orderWithItems([
            ['service_name' => 'Cuci Setrika Reguler', 'category' => 'Cuci Setrika'],
        ]);

        $this->actingAs($this->staff())
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Sedang Disetrika')
            ->assertDontSee('Sedang Dilipat');
    }

    public function test_flow_buttons_for_barang_khusus_only_order_include_ironing(): void
    {
        $order = $this->orderWithItems([
            ['service_name' => 'Bedcover Besar', 'category' => 'Barang khusus'],
        ]);

        $this->actingAs($this->staff())
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Sedang Disetrika')
            ->assertDontSee('Sedang Dilipat');
    }

    public function test_flow_buttons_show_both_for_mixed_lipat_and_setrika_order(): void
    {
        $order = $this->orderWithItems([
            ['service_name' => 'Cuci Lipat Reguler', 'category' => 'Cuci Lipat'],
            ['service_name' => 'Setrika Reguler', 'category' => 'Setrika'],
        ]);

        $this->actingAs($this->staff())
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Sedang Disetrika')
            ->assertSee('Sedang Dilipat');
    }
}
