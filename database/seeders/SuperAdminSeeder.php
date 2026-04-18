<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'superadmin@rupa.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'phone' => '081122334455',
                'is_active' => true,
                'tenant_id' => null,
                'outlet_id' => null,
            ]
        );
    }
}
