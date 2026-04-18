<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantBoundaryAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_blocks_authenticated_user_accessing_another_tenant_domain(): void
    {
        ['host' => $hostA] = $this->createTenantWithOwner('a');
        ['owner' => $ownerB] = $this->createTenantWithOwner('b');

        $this->setEnvironment('production');

        $this->actingAs($ownerB)
            ->withServerVariables(['HTTP_HOST' => $hostA])
            ->get("http://{$hostA}/outlets")
            ->assertForbidden()
            ->assertSee(__('tenant.tenant_mismatch_forbidden'));
    }

    public function test_local_environment_bypasses_tenant_mismatch_guard(): void
    {
        ['tenant' => $tenantA, 'host' => $hostA] = $this->createTenantWithOwner('a');
        ['owner' => $ownerB] = $this->createTenantWithOwner('b');

        $this->setEnvironment('local');

        $this->actingAs($ownerB)
            ->withServerVariables(['HTTP_HOST' => $hostA])
            ->get("http://{$hostA}/outlets")
            ->assertOk()
            ->assertSee($tenantA->name);
    }

    public function test_production_allows_access_when_user_tenant_matches_domain_tenant(): void
    {
        ['owner' => $ownerA, 'host' => $hostA] = $this->createTenantWithOwner('a');

        $this->setEnvironment('production');

        $this->actingAs($ownerA)
            ->withServerVariables(['HTTP_HOST' => $hostA])
            ->get("http://{$hostA}/outlets")
            ->assertOk();
    }

    public function test_public_outlet_route_returns_not_found_for_ambiguous_slug(): void
    {
        ['tenant' => $tenantA] = $this->createTenantWithOwner('a');
        ['tenant' => $tenantB] = $this->createTenantWithOwner('b');

        $tenantA->outlets()->firstOrFail()->update(['slug' => 'shared']);
        $tenantB->outlets()->firstOrFail()->update(['slug' => 'shared']);

        $this->get('/shared')->assertNotFound();
    }

    /**
     * @return array{tenant: Tenant, owner: User, host: string}
     */
    private function createTenantWithOwner(string $suffixSeed): array
    {
        $suffix = $suffixSeed.'-'.Str::lower(Str::random(6));

        $plan = Plan::query()->create([
            'name' => 'Plan-'.$suffix,
            'slug' => 'plan-'.$suffix,
            'price_monthly' => 100000,
            'price_yearly' => 1000000,
            'max_outlets' => 3,
            'trial_days' => 14,
            'sort_order' => 1,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Tenant '.$suffix,
            'slug' => 'tenant-'.$suffix,
            'plan_id' => $plan->id,
            'owner_name' => 'Owner '.$suffix,
            'owner_email' => "owner-{$suffix}@example.com",
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
            'is_read_only' => false,
        ]);

        $host = 'tenant-'.$suffix.'.rupa.test';
        $outlet = $tenant->outlets()->create([
            'name' => 'Outlet '.$suffix,
            'slug' => 'main',
            'full_subdomain' => $host,
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Domain '.$suffix,
            'city' => 'Makassar',
            'phone' => '08123456789',
            'email' => "outlet-{$suffix}@example.com",
        ]);

        $owner = User::factory()->create([
            'name' => 'Owner User '.$suffix,
            'email' => "owner-user-{$suffix}@example.com",
            'role' => 'owner',
            'is_active' => true,
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'can_view_revenue' => true,
        ]);

        return [
            'tenant' => $tenant,
            'owner' => $owner,
            'host' => $host,
        ];
    }

    private function setEnvironment(string $environment): void
    {
        $this->app['env'] = $environment;
        config(['app.env' => $environment]);
    }
}
