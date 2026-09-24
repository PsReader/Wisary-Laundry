<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'pedrogans16@gmail.com',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

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
                'password' => 'password',
                'role' => User::ROLE_STAFF,
            ]);
        }
    }
}
