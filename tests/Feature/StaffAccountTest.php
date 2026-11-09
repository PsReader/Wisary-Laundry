<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAccountTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
    }

    public function test_super_admin_page_renders_create_and_toggle_ui(): void
    {
        $admin = $this->superAdmin();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);

        $this->actingAs($admin)->get('/staff')
            ->assertOk()
            ->assertSee('Tambah Staf')
            ->assertSee('Nonaktifkan')
            ->assertSee(route('staff.store'))
            ->assertSee($staff->name);
    }

    public function test_super_admin_can_create_staff_account(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('staff.store'), [
            'name' => 'Staf Baru',
            'email' => 'stafbaru@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'stafbaru@example.com',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);
    }

    public function test_staff_cannot_create_accounts(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);

        $this->actingAs($staff)->post(route('staff.store'), [
            'name' => 'Staf Baru',
            'email' => 'stafbaru@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertForbidden();
    }

    public function test_super_admin_can_create_staff_beyond_previous_limit(): void
    {
        $admin = $this->superAdmin();
        User::factory()->count(5)->create(['role' => User::ROLE_STAFF, 'is_active' => true]);

        $this->actingAs($admin)->get('/staff')
            ->assertOk()
            ->assertSee('Tambah Staf')
            ->assertDontSee('Batas Staf aktif sudah terpenuhi')
            ->assertDontSee('Maks. 4');

        $this->actingAs($admin)->post(route('staff.store'), [
            'name' => 'Staf Baru',
            'email' => 'stafbaru@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'stafbaru@example.com',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_toggle_staff_active_status(): void
    {
        $admin = $this->superAdmin();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);

        $this->actingAs($admin)->post(route('staff.toggle-active'), [
            'user_id' => $staff->id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertFalse($staff->fresh()->is_active);

        $this->actingAs($admin)->post(route('staff.toggle-active'), [
            'user_id' => $staff->id,
        ]);

        $this->assertTrue($staff->fresh()->is_active);
    }

    public function test_super_admin_cannot_deactivate_own_account(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('staff.toggle-active'), [
            'user_id' => $admin->id,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_last_active_super_admin_cannot_be_deactivated(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('staff.toggle-active'), [
            'user_id' => $admin->id,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_super_admin_can_activate_another_super_admin(): void
    {
        $admin = $this->superAdmin();
        $otherAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => false,
        ]);

        $this->actingAs($admin)->get('/staff')
            ->assertOk()
            ->assertSee('Aktifkan');

        $this->actingAs($admin)->post(route('staff.toggle-active'), [
            'user_id' => $otherAdmin->id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertTrue($otherAdmin->fresh()->is_active);
    }

    public function test_staff_cannot_toggle_active_status(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
        $other = User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);

        $this->actingAs($staff)->post(route('staff.toggle-active'), [
            'user_id' => $other->id,
        ])->assertForbidden();

        $this->assertTrue($other->fresh()->is_active);
    }

    public function test_super_admin_can_activate_staff_beyond_previous_limit(): void
    {
        $admin = $this->superAdmin();
        User::factory()->count(4)->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => false]);

        $this->actingAs($admin)->post(route('staff.toggle-active'), [
            'user_id' => $staff->id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertTrue($staff->fresh()->is_active);
    }

    public function test_deactivating_staff_clears_remember_token(): void
    {
        $admin = $this->superAdmin();
        $staff = User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
        $staff->forceFill(['remember_token' => 'persistent-token'])->save();

        $this->actingAs($admin)->post(route('staff.toggle-active'), [
            'user_id' => $staff->id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertNull($staff->fresh()->remember_token);
    }
}
