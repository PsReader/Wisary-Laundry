<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_STAFF = 'staff';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function roleLabel(): string
    {
        return $this->isSuperAdmin() ? 'Super Admin' : 'Staf';
    }

    /**
     * Batas akun aktif: maksimal satu Super Admin dan empat Staf.
     */
    public static function activeSuperAdminCount(): int
    {
        return self::query()->where('role', self::ROLE_SUPER_ADMIN)->where('is_active', true)->count();
    }

    public static function activeStaffCount(): int
    {
        return self::query()->where('role', self::ROLE_STAFF)->where('is_active', true)->count();
    }

    public static function activeAccountCount(): int
    {
        return self::query()->where('is_active', true)->count();
    }

    /**
     * Cek apakah masih ada slot untuk akun aktif baru dengan role tertentu.
     */
    public static function accountSlotAvailable(string $role): bool
    {
        return match ($role) {
            self::ROLE_SUPER_ADMIN => self::activeSuperAdminCount() < 1,
            self::ROLE_STAFF => self::activeStaffCount() < 4,
            default => false,
        };
    }
}
