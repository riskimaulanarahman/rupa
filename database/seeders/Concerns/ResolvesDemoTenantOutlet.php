<?php

namespace Database\Seeders\Concerns;

use App\Models\Outlet;
use App\Models\Tenant;
use RuntimeException;

trait ResolvesDemoTenantOutlet
{
    /**
     * @return array{Tenant, Outlet}
     */
    protected function ensureDemoContextBound(): array
    {
        $tenant = app()->has('tenant') && app('tenant') instanceof Tenant
            ? app('tenant')
            : Tenant::query()->where('slug', 'rupa-demo')->first();

        if (! $tenant) {
            throw new RuntimeException('Demo tenant "rupa-demo" tidak ditemukan. Jalankan TenantSeeder terlebih dahulu.');
        }

        $outlet = app()->has('outlet') && app('outlet') instanceof Outlet
            ? app('outlet')
            : Outlet::query()
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
            throw new RuntimeException('Demo outlet tidak ditemukan. Jalankan OutletSeeder terlebih dahulu.');
        }

        app()->instance('tenant', $tenant);
        app()->instance('tenant_id', $tenant->id);
        app()->instance('outlet', $outlet);
        app()->instance('outlet_id', $outlet->id);

        return [$tenant, $outlet];
    }
}
