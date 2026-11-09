<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SEED_ADMIN_PASSWORD', app()->environment('production') ? null : 'password');

        if ($password === null) {
            throw new RuntimeException('Set SEED_ADMIN_PASSWORD sebelum menjalankan AuthSeeder di production.');
        }

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'pedrogans16@gmail.com',
            'password' => $password,
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // Akun staf demo hanya untuk development; di production staf dibuat
        // lewat halaman Manajemen Staf.
        if (app()->environment('production')) {
            return;
        }

        $staff = [
            ['name' => 'Putri Aulia', 'email' => 'simanjuntakpedrosa@gmail.com'],
            ['name' => 'Rizky Ramadhan', 'email' => 'readerswill001@gmail.com'],
            ['name' => 'Sari Dewi', 'email' => 'sari.dewi@wisary.laundry'],
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@wisary.laundry'],
        ];

        foreach ($staff as $member) {
            User::factory()->create([
                'name' => $member['name'],
                'email' => $member['email'],
                'password' => $password,
                'role' => User::ROLE_STAFF,
            ]);
        }
    }
}
