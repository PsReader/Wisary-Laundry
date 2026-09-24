<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordResetOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_otp_code_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, PasswordResetOtp::class);
    }

    public function test_otp_code_request_is_generic_for_unknown_email(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'tidak-ada@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_password_can_be_reset_with_valid_otp(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $notification = Notification::sent($user, PasswordResetOtp::class)->first();

        $this->assertSame(6, strlen($notification->code));

        $response = $this->post('/reset-password', [
            'email' => $user->email,
            'code' => $notification->code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status')
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_wrong_otp_is_rejected(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $this->post('/reset-password', [
            'email' => $user->email,
            'code' => '123456',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('code');

        $this->assertFalse(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_expired_otp_is_rejected(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $notification = Notification::sent($user, PasswordResetOtp::class)->first();

        $this->travelTo(now()->addMinutes(11));

        $this->post('/reset-password', [
            'email' => $user->email,
            'code' => $notification->code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('code');

        $this->assertFalse(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_otp_is_invalidated_after_five_wrong_attempts(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $notification = Notification::sent($user, PasswordResetOtp::class)->first();

        for ($i = 0; $i < 6; $i++) {
            $this->post('/reset-password', [
                'email' => $user->email,
                'code' => '000000',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])->assertSessionHasErrors('code');
        }

        $this->post('/reset-password', [
            'email' => $user->email,
            'code' => $notification->code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('code');

        $this->assertFalse(Hash::check('new-password', $user->fresh()->password));
    }
}