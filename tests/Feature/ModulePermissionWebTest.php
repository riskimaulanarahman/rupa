<?php

namespace Tests\Feature;

use App\Models\ModulePermissionDefault;
use App\Models\OutletRoleModulePermission;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\ModulePermissionResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModulePermissionWebTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, string>
     */
    private array $roles = ['owner', 'admin', 'beautician'];

    /**
     * @var array<int, string>
     */
    private array $modules = [
        'dashboard',
        'appointments',
        'customers',
        'treatment_records',
        'service_categories',
        'services',
        'products',
        'packages',
        'customer_packages',
        'transactions',
        'loyalty',
        'reports',
        'outlets',
        'import_data',
        'staff',
        'settings',
        'billing',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');
        Setting::set('business_name', 'Test Clinic', 'string');
    }

    public function test_superadmin_can_save_global_default_permission_matrix(): void
    {
        $superadmin = $this->createSuperAdmin();
        $matrix = $this->permissionMatrix(false);
        $matrix['owner']['dashboard'] = true;
        $matrix['owner']['reports'] = true;
        $matrix['admin']['appointments'] = true;

        $response = $this->actingAs($superadmin)->put(
            route('platform.permissions.defaults.update'),
            ['permissions' => $matrix]
        );

        $response->assertRedirect(route('platform.permissions.defaults'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('module_permission_defaults', [
            'role' => 'admin',
            'module_key' => 'appointments',
            'is_allowed' => true,
        ]);
        $this->assertDatabaseHas('module_permission_defaults', [
            'role' => 'admin',
            'module_key' => 'settings',
            'is_allowed' => false,
        ]);
    }

    public function test_superadmin_can_save_outlet_override_matrix_from_tenant_detail(): void
    {
        $superadmin = $this->createSuperAdmin();
        [$tenant, $outlet] = $this->createTenantWithOutlet();

        $matrix = $this->permissionMatrix(false);
        $matrix['admin']['appointments'] = true;
        $matrix['owner']['settings'] = true;

        $response = $this->actingAs($superadmin)->put(
            route('platform.tenants.module-access.update', $tenant),
            [
                'outlet_id' => $outlet->id,
                'permissions' => $matrix,
            ]
        );

        $response->assertRedirect(route('platform.tenants.show', [
            'tenant' => $tenant,
            'permissions_outlet' => $outlet->id,
        ]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('outlet_role_module_permissions', [
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role' => 'admin',
            'module_key' => 'appointments',
            'is_allowed' => true,
        ]);
        $this->assertDatabaseHas('outlet_role_module_permissions', [
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role' => 'admin',
            'module_key' => 'settings',
            'is_allowed' => false,
        ]);
    }

    public function test_permission_resolver_priority_is_override_then_default_then_legacy_fallback(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role' => 'admin',
            'is_active' => true,
            'can_view_revenue' => true,
        ]);

        ModulePermissionDefault::query()
            ->where('role', 'admin')
            ->where('module_key', 'customers')
            ->update(['is_allowed' => true]);

        OutletRoleModulePermission::query()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role' => 'admin',
            'module_key' => 'customers',
            'is_allowed' => false,
        ]);

        $resolver = app(ModulePermissionResolver::class);
        $this->assertFalse($resolver->canAccessModuleForUser($admin, 'customers'));

        OutletRoleModulePermission::query()
            ->where('outlet_id', $outlet->id)
            ->where('role', 'admin')
            ->where('module_key', 'customers')
            ->delete();
        ModulePermissionDefault::query()
            ->where('role', 'admin')
            ->where('module_key', 'customers')
            ->update(['is_allowed' => false]);

        $this->assertFalse($resolver->canAccessModuleForUser($admin, 'customers'));

        OutletRoleModulePermission::query()
            ->where('outlet_id', $outlet->id)
            ->where('role', 'admin')
            ->where('module_key', 'import_data')
            ->delete();
        ModulePermissionDefault::query()
            ->where('role', 'admin')
            ->where('module_key', 'import_data')
            ->delete();

        $this->assertTrue($resolver->canAccessModuleForUser($admin, 'import_data'));
    }

    public function test_web_routes_return_403_when_module_not_allowed(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role' => 'admin',
            'is_active' => true,
            'can_view_revenue' => true,
        ]);

        ModulePermissionDefault::query()->updateOrCreate(
            ['role' => 'admin', 'module_key' => 'settings'],
            ['is_allowed' => false]
        );

        $this->actingAs($admin)
            ->get(route('settings.index'))
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

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
            'tenant_id' => null,
            'outlet_id' => null,
        ]);
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function permissionMatrix(bool $allowed): array
    {
        $matrix = [];
        foreach ($this->roles as $role) {
            foreach ($this->modules as $moduleKey) {
                $matrix[$role][$moduleKey] = $allowed;
            }
        }

        return $matrix;
    }
}
