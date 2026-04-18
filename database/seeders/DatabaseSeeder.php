<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Base data
            PlanSeeder::class,
            TenantSeeder::class,
            OutletSeeder::class,
            SuperAdminSeeder::class,
            LandingContentSeeder::class,
        ]);

        $this->bindDemoTenantOutletContext();

        $this->call([
            // Tenant/outlet scoped data
            UserSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            SettingSeeder::class,
            OperatingHourSeeder::class,
            OutletLandingContentSeeder::class,
            BankAccountSeeder::class,

            // Customers
            CustomerSeeder::class,

            // Packages
            PackageSeeder::class,

            // Products
            ProductCategorySeeder::class,
            ProductSeeder::class,

            // Appointments (depends on customers, services, users)
            AppointmentSeeder::class,

            // Transactions (depends on appointments, packages, customers)
            TransactionSeeder::class,
        ]);
    }

    private function bindDemoTenantOutletContext(): void
    {
        $tenant = Tenant::query()->where('slug', 'rupa-demo')->first();
        if (! $tenant) {
            throw new RuntimeException('Demo tenant "rupa-demo" tidak ditemukan setelah TenantSeeder.');
        }

        $outlet = Outlet::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', 'main-outlet')
            ->first();

        if (! $outlet) {
            $outlet = Outlet::query()
                ->where('tenant_id', $tenant->id)
                ->orderBy('id')
                ->first();
        }

        if (! $outlet) {
            throw new RuntimeException('Demo outlet tidak ditemukan setelah OutletSeeder.');
        }

        app()->instance('tenant', $tenant);
        app()->instance('tenant_id', $tenant->id);
        app()->instance('outlet', $outlet);
        app()->instance('outlet_id', $outlet->id);
    }
}
