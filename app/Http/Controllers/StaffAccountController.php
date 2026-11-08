<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class StaffAccountController extends Controller
{
    /**
     * Buat akun Staf baru oleh Super Admin.
     *
     * Super Admin hanya dibuat melalui seeder/CLI; form ini selalu membuat role staff.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        Audit::record($request->user(), 'user.created', $user, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ]);

        return back()->with('success', "Akun {$user->name} berhasil dibuat.");
    }

    /**
     * Aktifkan / nonaktifkan akun (kecuali Super Admin yang sedang login, dirinya sendiri).
     */
    public function toggleActive(Request $request): RedirectResponse
    {
        $this->authorize('manage', User::class);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $willBeActive = ! $user->is_active;

        if (! $willBeActive && $user->isSuperAdmin() && User::activeSuperAdminCount() <= 1) {
            return back()->with('error', 'Super Admin harus tetap memiliki minimal satu akun aktif.');
        }

        $user->update(['is_active' => $willBeActive]);

        Audit::record($request->user(), $willBeActive ? 'user.activated' : 'user.deactivated', $user, [
            'is_active' => ! $willBeActive,
        ], [
            'is_active' => $willBeActive,
        ]);

        $status = $willBeActive ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }
}
