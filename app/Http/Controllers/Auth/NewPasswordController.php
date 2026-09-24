<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Audit;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Password::broker()->getUser(['email' => $request->email]);

        if ($user === null) {
            throw ValidationException::withMessages(['code' => __('passwords.token')]);
        }

        $key = 'password_reset_code.'.$user->email;
        $entry = Cache::get($key);

        if ($entry === null || $entry['expires_at'] < now()->timestamp) {
            Cache::forget($key);
            $request->session()->forget('reset_email');

            throw ValidationException::withMessages(['code' => __('passwords.expired')]);
        }

        if (! Hash::check($request->code, $entry['code'])) {
            $entry['attempts']++;

            if ($entry['attempts'] >= 5) {
                Cache::forget($key);
                $request->session()->forget('reset_email');

                throw ValidationException::withMessages(['code' => __('passwords.expired')]);
            }

            $remaining = $entry['expires_at'] - now()->timestamp;
            Cache::put($key, $entry, $remaining);

            throw ValidationException::withMessages(['code' => __('passwords.token')]);
        }

        Cache::forget($key);
        $request->session()->forget('reset_email');

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        Audit::record($user, 'user.password_reset', $user, null, ['name' => $user->name]);

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', __('passwords.reset'));
    }
}