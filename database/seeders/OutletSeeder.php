<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proTenant = Tenant::query()->where('slug', 'rupa-demo')->firstOrFail();
        $starterTenant = Tenant::query()->where('slug', 'rupa-starter')->firstOrFail();

        $outlets = [
            // PRO tenant -> 2 outlet
            [
                'tenant_id' => $proTenant->id,
                'name' => 'Pro Main Outlet',
                'slug' => 'main-outlet',
                'full_subdomain' => 'pro-main.rupa.local',
                'business_type' => 'clinic',
                'status' => 'active',
                'address' => 'Jl. Pro Utama No. 1',
                'city' => 'Jakarta',
                'phone' => '081230001001',
                'email' => 'pro-main@rupa.test',
            ],
            [
                'tenant_id' => $proTenant->id,
                'name' => 'Pro Branch Outlet',
                'slug' => 'pro-branch-outlet',
                'full_subdomain' => 'pro-branch.rupa.local',
                'business_type' => 'clinic',
                'status' => 'active',
                'address' => 'Jl. Pro Cabang No. 2',
                'city' => 'Bandung',
                'phone' => '081230001002',
                'email' => 'pro-branch@rupa.test',
            ],

            // STARTER tenant -> 1 outlet
            [
                'tenant_id' => $starterTenant->id,
                'name' => 'Starter Main Outlet',
                'slug' => 'starter-main-outlet',
                'full_subdomain' => 'starter-main.rupa.local',
                'business_type' => 'clinic',
                'status' => 'active',
                'address' => 'Jl. Starter No. 1',
                'city' => 'Surabaya',
                'phone' => '081230002001',
                'email' => 'starter-main@rupa.test',
            ],
        ];

        foreach ($outlets as $outletData) {
            Outlet::updateOrCreate(
                [
                    'tenant_id' => $outletData['tenant_id'],
                    'slug' => $outletData['slug'],
                ],
                $outletData
            );
        }
    }
}
