<?php

namespace Tests\Feature\Api;

use App\Models\ModulePermissionDefault;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModulePermissionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');
        Setting::set('business_name', 'Test Clinic', 'string');
    }

    public function test_login_and_profile_return_module_access_payload(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'email' => 'module-access@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'module-access@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'module_access',
                    ],
                ],
            ])
            ->assertJsonPath('data.user.module_access.transactions', true);

        foreach ([
            'appointments' => true,
            'customers' => true,
            'dashboard' => true,
            'transactions' => true,
            'services' => false,
            'settings' => false,
            'reports' => false,
            'loyalty' => false,
        ] as $moduleKey => $expected) {
            $loginResponse->assertJsonPath("data.user.module_access.{$moduleKey}", $expected);
        }

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'module_access',
                ],
            ])
            ->assertJsonPath('data.module_access.transactions', true)
            ->assertJsonPath('data.module_access.settings', false)
            ->assertJsonPath('data.module_access.reports', false);
    }

    public function test_api_module_route_returns_403_when_permission_is_denied(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        ModulePermissionDefault::query()->updateOrCreate(
            ['role' => 'admin', 'module_key' => 'customers'],
            ['is_allowed' => false]
        );

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/customers')
            ->assertForbidden()
            ->assertJsonPath('module', 'customers');
    }

    public function test_api_rejects_non_owner_when_spoofing_other_outlet_header(): void
    {
        [$tenant, $outletA] = $this->createTenantWithOutlet();
        $outletB = $tenant->outlets()->create([
            'name' => 'Outlet B',
            'slug' => 'outlet-b-'.Str::lower(Str::random(5)),
            'full_subdomain' => 'outlet-b-'.Str::lower(Str::random(5)).'.rupa.test',
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Outlet B',
            'city' => 'Makassar',
            'phone' => '081234567891',
            'email' => 'outlet-b@example.com',
        ]);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletA->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->withHeaders([
                'X-Outlet-Slug' => $outletB->slug,
            ])
            ->getJson('/api/v1/profile')
            ->assertForbidden();
    }

    public function test_api_rejects_owner_when_spoofing_outlet_from_other_tenant(): void
    {
        [$tenantA, $outletA] = $this->createTenantWithOutlet();
        [, $outletB] = $this->createTenantWithOutlet();

        $owner = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'outlet_id' => $outletA->id,
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->actingAs($owner, 'sanctum')
            ->withHeaders([
                'X-Outlet-Slug' => $outletB->slug,
            ])
            ->getJson('/api/v1/profile')
            ->assertForbidden();
    }

    /**
     * @return array{0: Tenant, 1: \App\Models\Outlet}
     */
    private function createTenantWithOutlet(): array
    {
        $suffix = Str::lower(Str::random(8));

        $plan = Plan::query()->create([
            'name' => 'Plan-'.$suffix,
            'slug' => 'plan-'.$suffix,
            'price_monthly' => 100000,
            'price_yearly' => 1000000,
            'max_outlets' => 5,
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

        $outlet = $tenant->outlets()->create([
            'name' => 'Outlet '.$suffix,
            'slug' => 'outlet-'.$suffix,
            'full_subdomain' => "tenant-{$suffix}.rupa.test",
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Test '.$suffix,
            'city' => 'Makassar',
            'phone' => '081234567890',
            'email' => "outlet-{$suffix}@example.com",
        ]);

        return [$tenant, $outlet];
    }
}
