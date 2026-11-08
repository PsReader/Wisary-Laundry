<?php

namespace Tests\Feature;

use App\Livewire\Orders\Create;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
    }

    public function test_create_order_page_loads_for_staff(): void
    {
        $service = Service::factory()->weightBased()->create(['price' => 5000, 'name' => 'Cuci Lipat Reguler']);
        $customer = Customer::factory()->create(['name' => 'Budi Santoso', 'whatsapp' => '081234567890']);

        $this->actingAs($this->staff())
            ->get(route('orders.create'))
            ->assertOk()
            ->assertSee('Buat Pesanan')
            ->assertSee('Cuci Lipat Reguler')
            ->assertSee('Tambah Pelanggan Baru')
            ->assertSee('Simpan Pesanan');
    }

    public function test_staff_can_create_kg_order_with_rounding_and_minimum(): void
    {
        $service = Service::factory()->weightBased()->create(['price' => 5000]);
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '3.5', 'quantity' => 1, 'note' => ''],
            ])
            ->call('save')
            ->assertRedirect(route('orders'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'status' => Order::STATUS_DITERIMA,
            'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
            'payment_status' => Order::PAYMENT_BELUM_LUNAS,
            'total_price' => 20000,
        ]);

        $this->assertDatabaseHas('order_items', [
            'service_id' => $service->id,
            'service_name' => $service->name,
            'pricing_type' => Service::PRICING_TYPE_KG,
            'unit' => Service::UNIT_KG,
            'price' => 5000,
            'actual_weight' => 3.5,
            'billed_weight' => 4,
            'quantity' => 1,
            'subtotal' => 20000,
        ]);
    }

    public function test_weight_above_whole_number_is_billed_upwards(): void
    {
        $service = Service::factory()->weightBased()->create(['price' => 5000]);
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '4.6', 'quantity' => 1, 'note' => ''],
            ])
            ->call('save');

        $this->assertDatabaseHas('order_items', [
            'service_id' => $service->id,
            'actual_weight' => 4.6,
            'billed_weight' => 5,
            'subtotal' => 25000,
        ]);

        $this->assertDatabaseHas('orders', ['total_price' => 25000]);
    }

    public function test_kg_and_unit_services_can_be_combined(): void
    {
        $kg = Service::factory()->weightBased()->create(['price' => 5000]);
        $unit = Service::factory()->unitBased()->create(['price' => 12000]);
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $kg->id, 'actual_weight' => '4.6', 'quantity' => 1, 'note' => ''],
                ['service_id' => $unit->id, 'actual_weight' => '', 'quantity' => 3, 'note' => 'Bed cover'],
            ])
            ->call('save')
            ->assertRedirect(route('orders'));

        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseHas('orders', ['total_price' => 61000]);
        $this->assertDatabaseHas('order_items', [
            'service_id' => $kg->id,
            'billed_weight' => 5,
            'subtotal' => 25000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'service_id' => $unit->id,
            'actual_weight' => null,
            'billed_weight' => null,
            'quantity' => 3,
            'subtotal' => 36000,
            'note' => 'Bed cover',
        ]);
    }

    public function test_order_number_uses_sequential_and_date_format(): void
    {
        $customer = Customer::factory()->create();
        $service = Service::factory()->weightBased()->create(['price' => 5000]);

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '2', 'quantity' => 1, 'note' => ''],
            ])
            ->call('save');

        $datePart = now()->format('dmY');

        $this->assertDatabaseHas('orders', ['order_number' => '0001'.$datePart]);

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '2', 'quantity' => 1, 'note' => ''],
            ])
            ->call('save');

        $this->assertDatabaseHas('orders', ['order_number' => '0002'.$datePart]);
        $this->assertSame(2, Order::count());
    }

    public function test_delivery_method_requires_address_and_pickup_does_not(): void
    {
        $service = Service::factory()->weightBased()->create(['price' => 5000]);
        $customer = Customer::factory()->create();
        $items = [
            ['service_id' => $service->id, 'actual_weight' => '2', 'quantity' => 1, 'note' => ''],
        ];

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('method', Order::METHOD_DIANTAR)
            ->set('delivery_address', '')
            ->set('items', $items)
            ->call('save')
            ->assertHasErrors(['delivery_address'])
            ->assertSet('method', Order::METHOD_DIANTAR);

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('method', Order::METHOD_DIANTAR)
            ->set('delivery_address', 'Jl. Merdeka No. 10')
            ->set('items', $items)
            ->call('save')
            ->assertRedirect(route('orders'));

        $this->assertDatabaseHas('orders', [
            'method' => Order::METHOD_DIANTAR,
            'delivery_address' => 'Jl. Merdeka No. 10',
        ]);

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('method', Order::METHOD_DIAMBIL)
            ->set('delivery_address', 'Jl. Seharusnya Terabaikan')
            ->set('items', $items)
            ->call('save')
            ->assertRedirect(route('orders'));

        $this->assertDatabaseHas('orders', [
            'method' => Order::METHOD_DIAMBIL,
            'delivery_address' => null,
        ]);
    }

    public function test_validation_requires_customer_and_valid_items(): void
    {
        $service = Service::factory()->weightBased()->create(['price' => 5000]);

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '0', 'quantity' => 1, 'note' => ''],
            ])
            ->call('save')
            ->assertHasErrors(['customer_id', 'items.0.actual_weight']);

        $this->assertSame(0, Order::count());
    }

    public function test_inactive_service_is_rejected_with_validation_error(): void
    {
        $service = Service::factory()->weightBased()->create(['is_active' => false]);
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '2', 'quantity' => 1, 'note' => ''],
            ])
            ->call('save')
            ->assertHasErrors(['items.0.service_id']);

        $this->assertSame(0, Order::count());
    }

    public function test_form_state_is_preserved_when_validation_fails(): void
    {
        $service = Service::factory()->weightBased()->create(['price' => 5000]);
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('method', Order::METHOD_DIANTAR)
            ->set('customer_search', '')
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '3.2', 'quantity' => 1, 'note' => 'Catatan penting'],
            ])
            ->call('save')
            ->assertHasErrors(['delivery_address'])
            ->assertSet('customer_id', $customer->id)
            ->assertSet('items.0.service_id', $service->id)
            ->assertSet('items.0.actual_weight', '3.2')
            ->assertSet('items.0.note', 'Catatan penting');
    }

    public function test_new_customer_can_be_created_from_order_form(): void
    {
        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_name', 'Siti Aminah')
            ->set('customer_whatsapp', '081234567890')
            ->set('customer_address', 'Jl. Melati No. 5')
            ->call('saveNewCustomer');

        $customer = Customer::firstOrFail();

        $this->assertSame('Siti Aminah', $customer->name);
        $this->assertSame('081234567890', $customer->whatsapp);
        $this->assertSame('Jl. Melati No. 5', $customer->address);
    }

    public function test_new_customer_modal_prefills_from_search_term(): void
    {
        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_search', 'Siti Aminah')
            ->call('openNewCustomer')
            ->assertSet('customer_name', 'Siti Aminah')
            ->assertSet('customer_whatsapp', '');

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_search', '081234567890')
            ->call('openNewCustomer')
            ->assertSet('customer_name', '081234567890')
            ->assertSet('customer_whatsapp', '081234567890');
    }

    public function test_new_customer_validation_rejects_duplicate_whatsapp(): void
    {
        Customer::factory()->create(['whatsapp' => '081234567890']);

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_name', 'Orang Lain')
            ->set('customer_whatsapp', '081234567890')
            ->call('saveNewCustomer')
            ->assertHasErrors(['customer_whatsapp']);

        $this->assertSame(1, Customer::count());
    }

    public function test_staff_can_add_change_and_remove_service_rows(): void
    {
        $service = Service::factory()->weightBased()->create();

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->assertSet('items', [])
            ->call('addService', $service->id)
            ->assertSet('items.0.service_id', $service->id)
            ->call('changeService', 0)
            ->assertSet('items.0.service_id', '')
            ->call('removeItem', 0)
            ->assertSet('items', []);
    }

    public function test_snapshot_keeps_old_service_price_after_price_change(): void
    {
        $service = Service::factory()->weightBased()->create(['price' => 5000]);
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->staff())
            ->test(Create::class)
            ->set('customer_id', $customer->id)
            ->set('items', [
                ['service_id' => $service->id, 'actual_weight' => '2', 'quantity' => 1, 'note' => ''],
            ])
            ->call('save');

        $service->update(['price' => 9000]);

        $item = Order::firstOrFail()->items()->firstOrFail();

        $this->assertSame('5000.00', (string) $item->price);
        $this->assertSame(20000, (int) $item->subtotal);
    }
}
