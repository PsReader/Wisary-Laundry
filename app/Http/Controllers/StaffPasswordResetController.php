<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class StaffPasswordResetController extends Controller
{
    /**
     * Atur ulang kata sandi akun (Staf atau Super Admin) oleh Super Admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage', User::class);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);

        $user->update([
            'password' => $validated['password'],
        ]);

        Audit::record($request->user(), 'user.password_reset', $user, null, [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return back()->with('success', "Kata sandi {$user->name} berhasil diatur ulang.");
    }
}
