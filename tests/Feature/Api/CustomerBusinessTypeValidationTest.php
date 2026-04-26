<?php

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerBusinessTypeValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        clear_business_cache();
    }

    public function test_clinic_customer_creation_requires_profile_type(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet('clinic');
        $user = $this->createUser($tenant->id, $outlet->id);

        $this->actingAs($user, 'sanctum')
            ->withHeaders(['X-Outlet-Slug' => $outlet->slug])
            ->postJson('/api/v1/customers', [
                'name' => 'Nadia Clinic',
                'phone' => '081234567892',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['skin_type']);
    }

    public function test_salon_customer_creation_accepts_hair_profile_values(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet('salon');
        $user = $this->createUser($tenant->id, $outlet->id);

        $this->actingAs($user, 'sanctum')
            ->withHeaders(['X-Outlet-Slug' => $outlet->slug])
            ->postJson('/api/v1/customers', [
                'name' => 'Salsa Salon',
                'phone' => '081234567893',
                'skin_type' => 'curly',
                'skin_concerns' => ['dandruff', 'hair_loss'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.skin_type', 'curly')
            ->assertJsonPath('data.skin_concerns.0', 'dandruff');
    }

    public function test_barbershop_customer_creation_does_not_require_profile_type(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet('barbershop');
        $user = $this->createUser($tenant->id, $outlet->id);

        $this->actingAs($user, 'sanctum')
            ->withHeaders(['X-Outlet-Slug' => $outlet->slug])
            ->postJson('/api/v1/customers', [
                'name' => 'Bara Barber',
                'phone' => '081234567894',
            ])
            ->assertCreated()
            ->assertJsonPath('data.skin_type', null);
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

    private function createUser(int $tenantId, int $outletId): User
    {
        return User::factory()->create([
            'tenant_id' => $tenantId,
            'outlet_id' => $outletId,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
