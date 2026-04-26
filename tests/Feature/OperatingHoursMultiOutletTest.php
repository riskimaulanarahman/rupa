<?php

namespace Tests\Feature;

use App\Models\OperatingHour;
use App\Models\Outlet;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\OperatingHourSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OperatingHoursMultiOutletTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_update_hours_is_scoped_per_outlet(): void
    {
        [$tenant, $outletA, $outletB] = $this->createTenantWithTwoOutlets();
        $owner = $this->createOwner($tenant->id, $outletA->id);

        OperatingHour::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletB->id,
            'day_of_week' => 0,
            'open_time' => null,
            'close_time' => null,
            'is_closed' => true,
        ]);

        $this->actingAs($owner, 'sanctum')
            ->withHeaders($this->apiHeadersForOutlet($outletA))
            ->putJson('/api/v1/settings/hours', [
                'operating_hours' => [
                    [
                        'day_of_week' => 0,
                        'open_time' => '08:00',
                        'close_time' => '16:00',
                        'is_closed' => false,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonCount(7, 'data')
            ->assertJsonPath('data.0.day_of_week', 0);

        $this->assertDatabaseHas('operating_hours', [
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletA->id,
            'day_of_week' => 0,
            'open_time' => '08:00',
            'close_time' => '16:00',
            'is_closed' => false,
        ]);

        $this->assertDatabaseHas('operating_hours', [
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletB->id,
            'day_of_week' => 0,
            'is_closed' => true,
        ]);
    }

    public function test_api_hours_get_returns_complete_week_for_outlet_without_existing_data(): void
    {
        [, $outletA] = $this->createTenantWithTwoOutlets();

        $this->withHeaders($this->apiHeadersForOutlet($outletA))
            ->getJson('/api/v1/settings/hours')
            ->assertOk()
            ->assertJsonCount(7, 'data')
            ->assertJsonPath('data.0.day_of_week', 0)
            ->assertJsonPath('data.6.day_of_week', 6)
            ->assertJsonPath('data.0.is_closed', true)
            ->assertJsonPath('data.1.open_time', '09:00');

        $this->assertSame(
            7,
            OperatingHour::withoutGlobalScopes()
                ->where('tenant_id', $outletA->tenant_id)
                ->where('outlet_id', $outletA->id)
                ->count()
        );
    }

    public function test_direct_hours_endpoints_require_outlet_context(): void
    {
        [$tenant, $outletA] = $this->createTenantWithTwoOutlets();
        $owner = $this->createOwner($tenant->id, $outletA->id);

        $this->withHeaders([
            'X-Tenant-Slug' => $tenant->slug,
        ])->getJson('/api/v1/settings/hours')
            ->assertStatus(400)
            ->assertJsonPath('message', 'Konteks outlet wajib tersedia untuk jam operasional.');

        $this->actingAs($owner, 'sanctum')
            ->withHeaders([
                'X-Tenant-Slug' => $tenant->slug,
            ])->putJson('/api/v1/settings/hours', [
                'operating_hours' => [
                    [
                        'day_of_week' => 1,
                        'open_time' => '09:00',
                        'close_time' => '18:00',
                        'is_closed' => false,
                    ],
                ],
            ])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Konteks outlet wajib tersedia untuk jam operasional.');

        $this->assertSame(0, OperatingHour::withoutGlobalScopes()->count());
    }

    public function test_aggregate_settings_returns_empty_operating_hours_without_outlet_context(): void
    {
        [$tenant] = $this->createTenantWithTwoOutlets();

        $this->withHeaders([
            'X-Tenant-Slug' => $tenant->slug,
        ])->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('data.operating_hours', []);
    }

    public function test_web_settings_hours_fills_missing_days_only_for_active_outlet(): void
    {
        [$tenant, $outletA, $outletB] = $this->createTenantWithTwoOutlets();
        $owner = $this->createOwner($tenant->id, $outletA->id);

        OperatingHour::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletA->id,
            'day_of_week' => 1,
            'open_time' => '10:00',
            'close_time' => '18:00',
            'is_closed' => false,
        ]);

        OperatingHour::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletB->id,
            'day_of_week' => 1,
            'open_time' => '11:00',
            'close_time' => '19:00',
            'is_closed' => false,
        ]);

        $this->actingAs($owner)
            ->withSession([
                'active_outlet_id' => $outletA->id,
                'outlet_slug' => $outletA->slug,
            ])
            ->get(route('settings.hours'))
            ->assertOk();

        $this->assertSame(
            7,
            OperatingHour::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('outlet_id', $outletA->id)
                ->count()
        );

        $this->assertSame(
            1,
            OperatingHour::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('outlet_id', $outletB->id)
                ->count()
        );
    }

    public function test_legacy_global_rows_are_ignored_for_scoped_reads_and_writes(): void
    {
        [$tenant, $outletA] = $this->createTenantWithTwoOutlets();
        $owner = $this->createOwner($tenant->id, $outletA->id);

        OperatingHour::withoutGlobalScopes()->create([
            'tenant_id' => null,
            'outlet_id' => null,
            'day_of_week' => 0,
            'open_time' => '06:00',
            'close_time' => '12:00',
            'is_closed' => false,
        ]);

        $this->actingAs($owner, 'sanctum')
            ->withHeaders($this->apiHeadersForOutlet($outletA))
            ->putJson('/api/v1/settings/hours', [
                'operating_hours' => [
                    [
                        'day_of_week' => 0,
                        'open_time' => '08:30',
                        'close_time' => '17:30',
                        'is_closed' => false,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.0.open_time', '08:30');

        $this->assertDatabaseHas('operating_hours', [
            'tenant_id' => null,
            'outlet_id' => null,
            'day_of_week' => 0,
            'open_time' => '06:00',
            'close_time' => '12:00',
        ]);

        $this->assertDatabaseHas('operating_hours', [
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletA->id,
            'day_of_week' => 0,
            'open_time' => '08:30',
            'close_time' => '17:30',
        ]);
    }

    public function test_api_update_hours_rejects_duplicate_days_in_payload(): void
    {
        [$tenant, $outletA] = $this->createTenantWithTwoOutlets();
        $owner = $this->createOwner($tenant->id, $outletA->id);

        $this->actingAs($owner, 'sanctum')
            ->withHeaders($this->apiHeadersForOutlet($outletA))
            ->putJson('/api/v1/settings/hours', [
                'operating_hours' => [
                    [
                        'day_of_week' => 1,
                        'open_time' => '09:00',
                        'close_time' => '18:00',
                        'is_closed' => false,
                    ],
                    [
                        'day_of_week' => 1,
                        'open_time' => '10:00',
                        'close_time' => '19:00',
                        'is_closed' => false,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['operating_hours.1.day_of_week']);
    }

    public function test_operating_hour_seeder_only_updates_bound_outlet(): void
    {
        [$tenant, $outletA, $outletB] = $this->createTenantWithTwoOutlets();

        app()->instance('tenant', $tenant);
        app()->instance('tenant_id', $tenant->id);
        app()->instance('outlet', $outletA);
        app()->instance('outlet_id', $outletA->id);

        OperatingHour::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletB->id,
            'day_of_week' => 0,
            'open_time' => '12:00',
            'close_time' => '20:00',
            'is_closed' => false,
        ]);

        $this->seed(OperatingHourSeeder::class);

        $this->assertSame(
            7,
            OperatingHour::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('outlet_id', $outletA->id)
                ->count()
        );

        $this->assertDatabaseHas('operating_hours', [
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletB->id,
            'day_of_week' => 0,
            'open_time' => '12:00',
            'close_time' => '20:00',
            'is_closed' => false,
        ]);
    }

    /**
     * @return array{0: Tenant, 1: Outlet, 2: Outlet}
     */
    private function createTenantWithTwoOutlets(): array
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

        $outletA = $tenant->outlets()->create([
            'name' => 'Outlet A '.$suffix,
            'slug' => 'outlet-a-'.$suffix,
            'full_subdomain' => "outlet-a-{$suffix}.rupa.test",
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Outlet A',
            'city' => 'Makassar',
            'phone' => '081234567890',
            'email' => "outlet-a-{$suffix}@example.com",
        ]);

        $outletB = $tenant->outlets()->create([
            'name' => 'Outlet B '.$suffix,
            'slug' => 'outlet-b-'.$suffix,
            'full_subdomain' => "outlet-b-{$suffix}.rupa.test",
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Outlet B',
            'city' => 'Makassar',
            'phone' => '081234567891',
            'email' => "outlet-b-{$suffix}@example.com",
        ]);

        return [$tenant, $outletA, $outletB];
    }

    /**
     * @return array<string, string>
     */
    private function apiHeadersForOutlet(Outlet $outlet): array
    {
        return [
            'X-Tenant-Slug' => $outlet->tenant->slug,
            'X-Outlet-Slug' => $outlet->slug,
        ];
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
