<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedUser(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF, 'is_active' => true]);
    }

    public function test_active_session_persists_last_activity(): void
    {
        $user = $this->authenticatedUser();

        $this->actingAs($user)->get('/dashboard');

        $this->assertAuthenticated();
        $this->assertNotNull(session('last_activity_at'));
    }

    public function test_idle_session_is_logged_out(): void
    {
        $user = $this->authenticatedUser();

        $this->actingAs($user)->get('/dashboard');

        $expired = time() - ((config('app.idle_timeout') + 10) * 60);
        session(['last_activity_at' => $expired]);

        $this->get('/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHas('status');

        $this->assertGuest();
    }

    public function test_recent_activity_keeps_session_authenticated(): void
    {
        $user = $this->authenticatedUser();

        $this->actingAs($user)->get('/dashboard');
        session(['last_activity_at' => time() - ((config('app.idle_timeout') - 1) * 60)]);

        $this->get('/dashboard')->assertOk();

        $this->assertAuthenticated();
    }
}
