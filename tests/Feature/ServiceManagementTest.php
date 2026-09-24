<?php

namespace Tests\Feature;

use App\Livewire\Services\Index;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
    }

    public function test_initial_catalog_is_seeded(): void
    {
        $this->seed(ServiceSeeder::class);

        $this->assertSame(15, Service::count());
        $this->assertSame(9, Service::query()->where('pricing_type', Service::PRICING_TYPE_KG)->count());
        $this->assertSame(6, Service::query()->where('pricing_type', Service::PRICING_TYPE_UNIT)->count());
    }

    public function test_super_admin_can_open_services_page_and_manage(): void
    {
        $this->seed(ServiceSeeder::class);

        $this->actingAs($this->superAdmin())
            ->get('/services')
            ->assertOk()
            ->assertSee('Cuci Setrika Kilat')
            ->assertSee('Rp12.000/kg')
            ->assertSee('Tambah Layanan')
            ->assertSee('Nonaktifkan');
    }

    public function test_staff_can_view_services_as_read_only(): void
    {
        $this->seed(ServiceSeeder::class);

        $response = $this->actingAs($this->staff())
            ->get('/services')
            ->assertOk()
            ->assertSee('Cuci Lipat Reguler')
            ->assertSee('Rp3.000/kg')
            ->assertSee('Baca saja');

        $response->assertDontSee('Tambah Layanan');
        $response->assertDontSee('Nonaktifkan');
    }

    public function test_staff_cannot_trigger_management_actions(): void
    {
        $this->seed(ServiceSeeder::class);
        $service = Service::firstOrFail();

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->call('create')
            ->assertForbidden();

        Livewire::actingAs($this->staff())
            ->test(Index::class)
            ->call('toggle', $service->id)
            ->assertForbidden();

        $this->assertTrue($service->fresh()->is_active);
    }

    public function test_super_admin_can_create_weight_service(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(Index::class)
            ->set('name', 'Cuci Karpet')
            ->set('category', 'Barang khusus')
            ->set('pricing_type', Service::PRICING_TYPE_KG)
            ->set('price', '15000')
            ->set('estimated_days', '3')
            ->call('save')
            ->assertRedirect(route('services'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'name' => 'Cuci Karpet',
            'pricing_type' => Service::PRICING_TYPE_KG,
            'unit' => Service::UNIT_KG,
            'estimated_days' => 3,
            'is_same_day' => false,
            'is_active' => true,
        ]);
    }

    public function test_same_day_service_sets_is_same_day(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(Index::class)
            ->set('name', 'Hemat Kilat')
            ->set('category', 'Cuci Lipat')
            ->set('pricing_type', Service::PRICING_TYPE_KG)
            ->set('price', '9000')
            ->set('estimated_days', '0')
            ->call('save')
            ->assertRedirect(route('services'));

        $this->assertDatabaseHas('services', [
            'name' => 'Hemat Kilat',
            'estimated_days' => 0,
            'is_same_day' => true,
        ]);
    }

    public function test_unit_service_uses_unit_satuan(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(Index::class)
            ->set('name', 'Gorden Besar')
            ->set('category', 'Barang khusus')
            ->set('pricing_type', Service::PRICING_TYPE_UNIT)
            ->set('price', '35000')
            ->set('estimated_days', '3')
            ->call('save')
            ->assertRedirect(route('services'));

        $this->assertDatabaseHas('services', [
            'name' => 'Gorden Besar',
            'pricing_type' => Service::PRICING_TYPE_UNIT,
            'unit' => Service::UNIT_PCS,
        ]);
    }

    public function test_validation_requires_valid_fields(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(Index::class)
            ->set('name', '')
            ->set('pricing_type', 'bukan-tipe')
            ->set('price', '0')
            ->set('estimated_days', '-1')
            ->call('save')
            ->assertHasErrors(['name', 'pricing_type', 'price', 'estimated_days']);
    }

    public function test_service_name_must_be_unique(): void
    {
        Service::factory()->create(['name' => 'Layanan Satu']);

        Livewire::actingAs($this->superAdmin())
            ->test(Index::class)
            ->set('name', 'Layanan Satu')
            ->set('category', 'Cuci Lipat')
            ->set('pricing_type', Service::PRICING_TYPE_KG)
            ->set('price', '5000')
            ->set('estimated_days', '3')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_super_admin_can_edit_service_keeping_own_name(): void
    {
        $service = Service::factory()->create(['name' => 'Layanan Dua', 'price' => 8000]);

        Livewire::actingAs($this->superAdmin())
            ->test(Index::class)
            ->call('edit', $service->id)
            ->assertSet('name', 'Layanan Dua')
            ->set('price', '10000')
            ->call('save')
            ->assertRedirect(route('services'));

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Layanan Dua',
            'price' => 10000,
        ]);
    }

    public function test_super_admin_can_toggle_service_active(): void
    {
        $service = Service::factory()->create(['is_active' => true]);

        Livewire::actingAs($this->superAdmin())
            ->test(Index::class)
            ->call('toggle', $service->id)
            ->assertRedirect(route('services'));

        $this->assertFalse($service->fresh()->is_active);

        Livewire::actingAs($this->superAdmin())
            ->test(Index::class)
            ->call('toggle', $service->id);

        $this->assertTrue($service->fresh()->is_active);
    }

    public function test_active_scope_excludes_inactive_services(): void
    {
        Service::factory()->count(2)->create(['is_active' => true]);
        Service::factory()->create(['is_active' => false]);

        $this->assertSame(2, Service::active()->count());
    }
}
