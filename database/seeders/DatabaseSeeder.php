<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin Account
        $admin = User::firstOrCreate([
            'email' => 'admin@invetaris.local'
        ], [
            'name' => 'Administrator',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create Staff Account
        $staff = User::firstOrCreate([
            'email' => 'staff@invetaris.local'
        ], [
            'name' => 'Staff User',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        // Create Employee records for admin and staff
        Employee::firstOrCreate([
            'user_id' => $admin->id
        ], [
            'nik' => '1234567890',
            'name' => $admin->name,
            'gender' => 'Laki-Laki',
            'phone' => '+62812345678901',
            'position' => 'Administrator',
        ]);

        Employee::firstOrCreate([
            'user_id' => $staff->id
        ], [
            'nik' => '0987654321',
            'name' => $staff->name,
            'gender' => 'Perempuan',
            'phone' => '+62812345678902',
            'position' => 'Staff',
        ]);

        // Create additional employee records without user accounts
        Employee::factory(8)->create();
    }
}
