<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerBusinessTypeFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        clear_business_cache();
    }

    public function test_customer_create_form_uses_salon_profile_labels_for_salon_outlet(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet('salon');
        $owner = $this->createOwner($tenant->id, $outlet->id);

        $this->actingAs($owner)
            ->withSession([
                'active_outlet_id' => $outlet->id,
                'outlet_slug' => $outlet->slug,
            ])
            ->get(route('customers.create'))
            ->assertOk()
            ->assertSee('Profil Rambut')
            ->assertSee('Tipe Rambut')
            ->assertDontSee('Tipe Kulit');
    }

    /**
     * @return array{0: Tenant, 1: \App\Models\Outlet}
     */
    private function createTenantWithOutlet(string $businessType): array
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
            'business_type' => $businessType,
            'status' => 'active',
            'address' => 'Jl. Test '.$suffix,
            'city' => 'Makassar',
            'phone' => '081234567890',
            'email' => "outlet-{$suffix}@example.com",
        ]);

        return [$tenant, $outlet];
    }

    private function createOwner(int $tenantId, int $outletId): User
    {
        return User::factory()->create([
            'tenant_id' => $tenantId,
            'outlet_id' => $outletId,
            'role' => 'owner',
            'is_active' => true,
            'can_view_revenue' => true,
        ]);
    }
}
