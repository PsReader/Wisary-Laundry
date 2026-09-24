<?php

namespace Tests\Feature;

use App\Livewire\Customers\Index;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
    }

    public function test_staff_can_open_customer_list_page(): void
    {
        $customer = Customer::factory()->create(['name' => 'Ani Rahmawati']);

        $this->actingAs($this->staff())
            ->get('/customers')
            ->assertOk()
            ->assertSee('Pelanggan')
            ->assertSee($customer->name)
            ->assertSee('Tambah Pelanggan');
    }

    public function test_list_shows_empty_state_when_no_customers(): void
    {
        $this->actingAs($this->staff())
            ->get('/customers')
            ->assertOk()
            ->assertSee('Belum ada pelanggan terdaftar.');
    }

    public function test_search_filters_customers_by_name_and_whatsapp(): void
    {
        Customer::factory()->create(['name' => 'Ayu Lestari', 'whatsapp' => '081111111111']);
        Customer::factory()->create(['name' => 'Budi Santoso', 'whatsapp' => '082222222222']);

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->set('search', 'Ayu')
            ->assertSee('Ayu Lestari')
            ->assertDontSee('Budi Santoso');

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->set('search', '082222')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Ayu Lestari');
    }

    public function test_staff_can_create_customer(): void
    {
        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->set('name', 'Citra Maharani')
            ->set('whatsapp', '083333333333')
            ->set('address', 'Jl. Mawar No. 1')
            ->call('save')
            ->assertRedirect(route('customers'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'name' => 'Citra Maharani',
            'whatsapp' => '083333333333',
        ]);
    }

    public function test_staff_can_edit_customer(): void
    {
        $customer = Customer::factory()->create(['name' => 'Lama', 'whatsapp' => '084444444444']);

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->call('edit', $customer->id)
            ->assertSet('whatsapp', $customer->whatsapp)
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertRedirect(route('customers'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_whatsapp_must_be_indonesian_mobile_number(): void
    {
        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->set('name', 'Invalid')
            ->set('whatsapp', '0811')
            ->call('save')
            ->assertHasErrors(['whatsapp']);

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->set('name', 'Invalid')
            ->set('whatsapp', '0211234567')
            ->call('save')
            ->assertHasErrors(['whatsapp']);
    }

    public function test_whatsapp_must_be_unique(): void
    {
        Customer::factory()->create(['whatsapp' => '085555555555']);

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->set('name', 'Duplikat')
            ->set('whatsapp', '085555555555')
            ->call('save')
            ->assertHasErrors(['whatsapp']);
    }

    public function test_edit_allows_keeping_own_whatsapp(): void
    {
        $customer = Customer::factory()->create(['whatsapp' => '085555555555']);

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->call('edit', $customer->id)
            ->set('name', 'Tetap')
            ->call('save')
            ->assertRedirect(route('customers'))
            ->assertSessionHas('success');
    }

    public function test_staff_cannot_delete_customer(): void
    {
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->call('confirmDelete', $customer->id)
            ->call('delete')
            ->assertForbidden();

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_super_admin_can_delete_customer_without_orders(): void
    {
        $customer = Customer::factory()->create(['name' => 'Dihapus']);

        Livewire::actingAs($this->superAdmin())
            ->test(Index::class)
            ->call('confirmDelete', $customer->id)
            ->call('delete')
            ->assertRedirect(route('customers'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_customer_with_orders_cannot_be_deleted(): void
    {
        $customer = Customer::factory()->create(['name' => 'Berkas']);

        Order::factory()->create(['customer_id' => $customer->id]);

        Livewire::actingAs($this->superAdmin())
            ->test(Index::class)
            ->call('confirmDelete', $customer->id)
            ->call('delete')
            ->assertRedirect(route('customers'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_customer_detail_shows_info_and_order_history(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Dewi Anggraini',
            'whatsapp' => '086666666666',
            'address' => 'Jl. Anggrek No. 7',
            'notes' => 'Sering diantar sore',
        ]);
        $order = Order::factory()->ready()->create([
            'customer_id' => $customer->id,
            'total_price' => 50000,
            'paid_amount' => 50000,
            'payment_status' => Order::PAYMENT_LUNAS,
        ]);

        $this->actingAs($this->staff())
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Dewi Anggraini')
            ->assertSee('086666666666')
            ->assertSee('Jl. Anggrek No. 7')
            ->assertSee($order->order_number)
            ->assertSee('Rp50.000')
            ->assertSee('Siap Diambil')
            ->assertSee('Lunas');
    }

    public function test_customer_detail_shows_empty_order_state(): void
    {
        $customer = Customer::factory()->create(['name' => 'Baru']);

        $this->actingAs($this->staff())
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Belum ada pesanan untuk pelanggan ini.');
    }
}
