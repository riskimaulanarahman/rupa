<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Owner',
            'email' => 'owner@jagoflutter.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@jagoflutter.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Maya',
            'email' => 'maya@jagoflutter.com',
            'password' => bcrypt('password'),
            'role' => 'beautician',
            'phone' => '081234567892',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Lisa',
            'email' => 'lisa@jagoflutter.com',
            'password' => bcrypt('password'),
            'role' => 'beautician',
            'phone' => '081234567893',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Dr. Sarah',
            'email' => 'sarah@jagoflutter.com',
            'password' => bcrypt('password'),
            'role' => 'beautician',
            'phone' => '081234567894',
            'is_active' => true,
        ]);
    }
}
