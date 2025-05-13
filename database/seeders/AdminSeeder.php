<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = [
            'firstName' => '',
            'lastName' => '',
            'contact' => '',
            'branch_code' => 'HO',
            'userName' => 'admin',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('123456'),
            'position' => '',
            'signature' => '',
            'remember_token' => '',
            'created_at' => now(),
            'updated_at' => now(),
            'role' => 'Admin',
            'profile_picture' => '',
            'branch' => 'Head Office',
            'employee_id' => 'Admin',

        ];

        DB::table('users')->insert($adminUser);
    }
}
