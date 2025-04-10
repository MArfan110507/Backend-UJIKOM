<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    // Hapus data lama agar tidak duplikat
    DB::table('users')->where('email', 'admin@gmail.com')->delete();

    // Buat admin baru
    DB::table('users')->insert([
        'admin_id' => 1,
        'user_id' => null,
        'name' => 'Admin',
        'email' => 'admin@gmail.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

}

