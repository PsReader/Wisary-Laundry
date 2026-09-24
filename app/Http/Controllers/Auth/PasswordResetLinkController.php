<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\PasswordResetOtp;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = Password::broker()->getUser(['email' => $request->email]);

        if ($user !== null) {
            $code = (string) random_int(100000, 999999);

            Cache::put(
                'password_reset_code.'.$user->email,
                [
                    'code' => Hash::make($code),
                    'expires_at' => now()->addMinutes(config('auth.passwords.users.expire'))->timestamp,
                    'attempts' => 0,
                ],
                now()->addMinutes(config('auth.passwords.users.expire')),
            );

            $user->notify(new PasswordResetOtp($code));

            Audit::record($user, 'user.password_reset_request', $user);
        }

        // Respon netral agar eksistensi email tidak bisa dideteksi.
        $request->session()->put('reset_email', $request->email);

        return back()->with('status', __('passwords.sent'));
    }
}