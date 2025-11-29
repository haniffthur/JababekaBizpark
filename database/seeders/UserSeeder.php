<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // <-- Import model User
use Illuminate\Support\Facades\Hash; // <-- Import Hash

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat User ADMIN
        User::create([
            'name' => 'Admin Gudang',
            'email' => 'admin@gudang.com',
            'password' => Hash::make('password'), // Ganti 'password' dengan password aman
            'role' => 'admin',
            'email_verified_at' => now(), // Langsung verifikasi email
        ]);

      

        // Opsional: Buat 5 member palsu lainnya menggunakan factory
        // User::factory(5)->create([
        //     'role' => 'member'
        // ]);
    }
}