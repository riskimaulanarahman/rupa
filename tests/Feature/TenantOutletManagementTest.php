<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscription;
use App\Models\Outlet;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantOutletManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(CheckSubscription::class);
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_owner_under_limit_can_open_create_outlet_form(): void
    {
        ['owner' => $owner] = $this->createTenantContext(2, ['active']);

        $this->actingAs($owner)
            ->get(route('tenant.outlets.create'))
            ->assertOk();
    }

    public function test_owner_at_limit_is_redirected_to_billing_when_opening_create_outlet_form(): void
    {
        ['owner' => $owner, 'tenant' => $tenant] = $this->createTenantContext(1, ['active']);

        $this->actingAs($owner)
            ->get(route('tenant.outlets.create'))
            ->assertRedirect(route('tenant.billing.index'))
            ->assertSessionHas('error', __('tenant.outlet_limit_reached', ['plan' => $tenant->plan->name]));
    }

    public function test_owner_at_limit_cannot_create_outlet_via_post(): void
    {
        ['owner' => $owner, 'tenant' => $tenant] = $this->createTenantContext(1, ['active']);

        $this->actingAs($owner)
            ->withSession(['_token' => 'test-token'])
            ->post(route('tenant.outlets.store'), [
                '_token' => 'test-token',
                'name' => 'Cabang Baru',
                'business_type' => 'clinic',
                'address' => 'Jl. Mawar No. 1',
                'city' => 'Makassar',
                'phone' => '08123456789',
                'email' => 'cabang-baru@example.com',
            ])
            ->assertRedirect(route('tenant.billing.index'))
            ->assertSessionHas('error', __('tenant.outlet_limit_reached', ['plan' => $tenant->plan->name]));

        $this->assertSame(1, Outlet::query()->where('tenant_id', $tenant->id)->count());
    }

    public function test_inactive_outlet_still_counts_toward_plan_quota(): void
    {
        ['owner' => $owner, 'tenant' => $tenant] = $this->createTenantContext(1, ['inactive']);

        $this->actingAs($owner)
            ->withSession(['_token' => 'test-token'])
            ->post(route('tenant.outlets.store'), [
                '_token' => 'test-token',
                'name' => 'Cabang Kedua',
                'business_type' => 'salon',
                'address' => 'Jl. Kenanga No. 2',
                'city' => 'Makassar',
                'phone' => '0812000000',
                'email' => 'cabang-kedua@example.com',
            ])
            ->assertRedirect(route('tenant.billing.index'))
            ->assertSessionHas('error', __('tenant.outlet_limit_reached', ['plan' => $tenant->plan->name]));

        $this->assertSame(1, Outlet::query()->where('tenant_id', $tenant->id)->count());
    }

    public function test_outlet_create_validation_rejects_too_long_address_and_invalid_email(): void
    {
        ['owner' => $owner, 'tenant' => $tenant] = $this->createTenantContext(3, ['active']);

        $this->actingAs($owner)
            ->from(route('tenant.outlets.create'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('tenant.outlets.store'), [
                '_token' => 'test-token',
                'name' => 'Cabang Validasi',
                'business_type' => 'clinic',
                'address' => str_repeat('A', 256),
                'city' => 'Makassar',
                'phone' => '08123456789',
                'email' => 'invalid-email',
            ])
            ->assertRedirect(route('tenant.outlets.create'))
            ->assertSessionHasErrors(['address', 'email']);

        $this->assertSame(1, Outlet::query()->where('tenant_id', $tenant->id)->count());
    }

    public function test_hq_and_outlet_pages_show_upgrade_cta_and_hide_add_button_when_quota_full(): void
    {
        ['owner' => $owner] = $this->createTenantContext(1, ['active']);

        $this->actingAs($owner)
            ->get(route('tenant.hq.index'))
            ->assertOk()
            ->assertDontSee(route('tenant.outlets.create'))
            ->assertSee(route('tenant.billing.index'))
            ->assertSee(__('tenant.upgrade_plan'));

        $this->actingAs($owner)
            ->get(route('tenant.outlets.index'))
            ->assertOk()
            ->assertDontSee(route('tenant.outlets.create'))
            ->assertSee(route('tenant.billing.index'))
            ->assertSee(__('tenant.upgrade_plan'));
    }

    public function test_owner_cannot_deactivate_last_active_outlet(): void
    {
        ['owner' => $owner, 'tenant' => $tenant] = $this->createTenantContext(1, ['active']);
        $outlet = Outlet::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->actingAs($owner)
            ->post(route('tenant.outlets.toggle', $outlet))
            ->assertRedirect()
            ->assertSessionHas('error', 'Minimal harus ada satu outlet aktif.');

        $this->assertSame(
            'active',
            Outlet::query()->whereKey($outlet->id)->value('status')
        );
    }

    public function test_deactivating_active_outlet_moves_session_to_another_active_outlet(): void
    {
        ['owner' => $owner, 'tenant' => $tenant] = $this->createTenantContext(3, ['active', 'active']);
        $outlets = Outlet::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->get();

        $deactivatedOutlet = $outlets[0];
        $fallbackOutlet = $outlets[1];

        $this->actingAs($owner)
            ->withSession([
                'active_outlet_id' => $deactivatedOutlet->id,
                'outlet_slug' => $deactivatedOutlet->slug,
            ])
            ->post(route('tenant.outlets.toggle', $deactivatedOutlet))
            ->assertRedirect()
            ->assertSessionHas('success', 'Status outlet berhasil diperbarui.')
            ->assertSessionHas('active_outlet_id', $fallbackOutlet->id)
            ->assertSessionHas('outlet_slug', $fallbackOutlet->slug);

        $this->assertSame(
            'inactive',
            Outlet::query()->whereKey($deactivatedOutlet->id)->value('status')
        );
    }

    /**
     * @param  array<int, string>  $existingOutletStatuses
     * @return array{owner: User, tenant: Tenant}
     */
    private function createTenantContext(int $maxOutlets, array $existingOutletStatuses): array
    {
        $suffix = Str::lower(Str::random(8));
        $plan = Plan::query()->create([
            'name' => 'Plan-'.$suffix,
            'slug' => 'plan-'.$suffix,
            'price_monthly' => 100000,
            'price_yearly' => 1000000,
            'max_outlets' => $maxOutlets,
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

        $firstOutlet = null;
        foreach ($existingOutletStatuses as $index => $status) {
            $createdOutlet = $tenant->outlets()->create([
                'name' => 'Outlet '.($index + 1).' '.$suffix,
                'slug' => 'outlet-'.($index + 1),
                'full_subdomain' => "tenant-{$suffix}-outlet-".($index + 1).'.rupa.test',
                'business_type' => 'clinic',
                'status' => $status,
                'address' => 'Jl. Contoh No. '.$index,
                'city' => 'Makassar',
                'phone' => '0812345678'.$index,
                'email' => "outlet-{$index}-{$suffix}@example.com",
            ]);

            $firstOutlet ??= $createdOutlet;
        }

        $owner = User::factory()->create([
            'name' => 'Owner User '.$suffix,
            'email' => "owner-user-{$suffix}@example.com",
            'role' => 'owner',
            'is_active' => true,
            'tenant_id' => $tenant->id,
            'outlet_id' => $firstOutlet?->id,
            'can_view_revenue' => true,
        ]);

        return [
            'owner' => $owner,
            'tenant' => $tenant,
        ];
    }
}
