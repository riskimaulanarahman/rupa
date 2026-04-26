<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SettingsBusinessTypeSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        clear_business_cache();
    }

    public function test_settings_page_reconciles_legacy_business_type_into_active_outlet(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet();
        $owner = $this->createOwner($tenant->id, $outlet->id);

        Setting::query()
            ->withoutGlobalScopes()
            ->updateOrCreate(
                ['key' => 'business_type'],
                [
                    'tenant_id' => $tenant->id,
                    'outlet_id' => $outlet->id,
                    'value' => 'salon',
                    'type' => 'string',
                ]
            );

        clear_business_cache();

        $this->actingAs($owner)
            ->withSession([
                'active_outlet_id' => $outlet->id,
                'outlet_slug' => $outlet->slug,
            ])
            ->get(route('settings.clinic'))
            ->assertOk()
            ->assertSee('value="salon"', false);

        $this->assertSame('salon', $outlet->fresh()->business_type);
    }

    public function test_settings_update_syncs_business_type_only_for_active_outlet(): void
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
        $owner = $this->createOwner($tenant->id, $outletA->id);

        $response = $this->actingAs($owner)
            ->withSession([
                'active_outlet_id' => $outletA->id,
                'outlet_slug' => $outletA->slug,
            ])
            ->post(route('settings.clinic.update'), [
                'business_type' => 'salon',
                'business_name' => 'Salon Glowup',
                'business_address' => 'Jl. Pengayoman',
                'business_phone' => '081234567890',
                'business_email' => 'salon@example.com',
                'tax_percentage' => 10,
                'invoice_prefix' => 'SAL',
                'slot_duration' => 30,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame('salon', $outletA->fresh()->business_type);
        $this->assertSame('clinic', $outletB->fresh()->business_type);

        $this->assertDatabaseHas('settings', [
            'key' => 'business_type',
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletA->id,
            'value' => 'salon',
        ]);
    }

    public function test_invalid_business_type_does_not_change_outlet_or_setting(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet();
        $owner = $this->createOwner($tenant->id, $outlet->id);

        Setting::query()
            ->withoutGlobalScopes()
            ->updateOrCreate(
                ['key' => 'business_type'],
                [
                    'tenant_id' => $tenant->id,
                    'outlet_id' => $outlet->id,
                    'value' => 'clinic',
                    'type' => 'string',
                ]
            );

        $response = $this->actingAs($owner)
            ->withSession([
                'active_outlet_id' => $outlet->id,
                'outlet_slug' => $outlet->slug,
            ])
            ->from(route('settings.clinic'))
            ->post(route('settings.clinic.update'), [
                'business_type' => 'spa',
                'business_name' => 'Glowup',
                'tax_percentage' => 10,
                'invoice_prefix' => 'INV',
                'slot_duration' => 30,
            ]);

        $response->assertRedirect(route('settings.clinic'));
        $response->assertSessionHasErrors('business_type');

        $this->assertSame('clinic', $outlet->fresh()->business_type);
        $this->assertDatabaseHas('settings', [
            'key' => 'business_type',
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'value' => 'clinic',
        ]);
    }

    public function test_public_settings_api_returns_business_type_for_requested_outlet(): void
    {
        [$tenant, $outlet] = $this->createTenantWithOutlet();

        $outlet->update([
            'business_type' => 'salon',
        ]);

        $this->withHeaders([
            'X-Outlet-Slug' => $outlet->slug,
        ])->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('data.business_type', 'salon');
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
