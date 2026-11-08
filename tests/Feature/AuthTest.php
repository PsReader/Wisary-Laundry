<?php

namespace Tests\Feature;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_internal_pages(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/orders')->assertRedirect('/login');
        $this->get('/staff')->assertRedirect('/login');
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_super_admin_can_login_and_reach_dashboard(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'password' => 'password']);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false, 'password' => 'password']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_ends_session_and_logs_activity(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $this->assertDatabaseHas('login_activities', [
            'user_id' => $user->id,
            'type' => LoginActivity::TYPE_LOGOUT,
        ]);
    }

    public function test_staff_cannot_access_super_admin_only_pages(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)->get('/staff')->assertForbidden();
        $this->actingAs($staff)->get('/reports')->assertForbidden();
        $this->actingAs($staff)->get('/dashboard')->assertOk();
    }

    public function test_super_admin_can_access_super_admin_only_pages(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $this->actingAs($admin)->get('/staff')->assertOk();
        $this->actingAs($admin)->get('/reports')->assertOk();
    }

    public function test_successful_login_is_recorded(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'password' => 'password']);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('login_activities', [
            'user_id' => $admin->id,
            'type' => LoginActivity::TYPE_LOGIN,
        ]);
    }

    public function test_failed_login_is_recorded(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('login_activities', [
            'email' => $admin->email,
            'type' => LoginActivity::TYPE_FAILED,
        ]);
    }

    public function test_super_admin_can_reset_another_users_password(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($admin)->post(route('staff.password-reset'), [
            'user_id' => $staff->id,
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertTrue(Hash::check('newsecret123', $staff->fresh()->password));
    }

    public function test_staff_cannot_reset_passwords(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $other = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->actingAs($staff)->post(route('staff.password-reset'), [
            'user_id' => $other->id,
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertForbidden();
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'password' => 'password']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(5, LoginActivity::where('type', LoginActivity::TYPE_FAILED)->count());
    }
}
