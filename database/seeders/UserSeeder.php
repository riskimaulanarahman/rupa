<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $proTenant = Tenant::query()->where('slug', 'rupa-demo')->firstOrFail();
        $starterTenant = Tenant::query()->where('slug', 'rupa-starter')->firstOrFail();

        $proMainOutlet = Outlet::query()
            ->where('tenant_id', $proTenant->id)
            ->where('slug', 'main-outlet')
            ->firstOrFail();
        $proBranchOutlet = Outlet::query()
            ->where('tenant_id', $proTenant->id)
            ->where('slug', 'pro-branch-outlet')
            ->firstOrFail();
        $starterMainOutlet = Outlet::query()
            ->where('tenant_id', $starterTenant->id)
            ->where('slug', 'starter-main-outlet')
            ->firstOrFail();

        $users = [
            // OWNER PRO (2 outlet dalam 1 tenant)
            [
                'tenant_id' => $proTenant->id,
                'outlet_id' => $proMainOutlet->id,
                'name' => 'Owner Pro',
                'email' => 'owner.pro@rupa.test',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '081240001000',
                'is_active' => true,
            ],

            // 2 admin outlet PRO utama
            [
                'tenant_id' => $proTenant->id,
                'outlet_id' => $proMainOutlet->id,
                'name' => 'Admin Pro Main 1',
                'email' => 'admin.pro.main1@rupa.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081240001101',
                'is_active' => true,
            ],
            [
                'tenant_id' => $proTenant->id,
                'outlet_id' => $proMainOutlet->id,
                'name' => 'Admin Pro Main 2',
                'email' => 'admin.pro.main2@rupa.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081240001102',
                'is_active' => true,
            ],

            // 2 admin outlet PRO cabang
            [
                'tenant_id' => $proTenant->id,
                'outlet_id' => $proBranchOutlet->id,
                'name' => 'Admin Pro Branch 1',
                'email' => 'admin.pro.branch1@rupa.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081240001201',
                'is_active' => true,
            ],
            [
                'tenant_id' => $proTenant->id,
                'outlet_id' => $proBranchOutlet->id,
                'name' => 'Admin Pro Branch 2',
                'email' => 'admin.pro.branch2@rupa.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081240001202',
                'is_active' => true,
            ],

            // OWNER STARTER (1 outlet)
            [
                'tenant_id' => $starterTenant->id,
                'outlet_id' => $starterMainOutlet->id,
                'name' => 'Owner Starter',
                'email' => 'owner.starter@rupa.test',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '081240002000',
                'is_active' => true,
            ],

            // 1 admin outlet STARTER
            [
                'tenant_id' => $starterTenant->id,
                'outlet_id' => $starterMainOutlet->id,
                'name' => 'Admin Starter 1',
                'email' => 'admin.starter1@rupa.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081240002101',
                'is_active' => true,
            ],

            // Beautician untuk data demo tenant utama (dibutuhkan seeder appointment)
            [
                'tenant_id' => $proTenant->id,
                'outlet_id' => $proMainOutlet->id,
                'name' => 'Maya',
                'email' => 'maya@jagoflutter.com',
                'password' => Hash::make('password'),
                'role' => 'beautician',
                'phone' => '081234567892',
                'is_active' => true,
            ],
            [
                'tenant_id' => $proTenant->id,
                'outlet_id' => $proMainOutlet->id,
                'name' => 'Lisa',
                'email' => 'lisa@jagoflutter.com',
                'password' => Hash::make('password'),
                'role' => 'beautician',
                'phone' => '081234567893',
                'is_active' => true,
            ],
            [
                'tenant_id' => $proTenant->id,
                'outlet_id' => $proMainOutlet->id,
                'name' => 'Dr. Sarah',
                'email' => 'sarah@jagoflutter.com',
                'password' => Hash::make('password'),
                'role' => 'beautician',
                'phone' => '081234567894',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::withoutGlobalScopes()->updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
