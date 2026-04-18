<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Auth\LoginRedirectResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletAuthRedirectTest extends TestCase
{
    use RefreshDatabase;

    private Outlet $outlet;

    private User $staff;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::query()->create([
            'name' => 'Starter Redirect Test',
            'slug' => 'starter-redirect-test',
            'price_monthly' => 100000,
            'price_yearly' => 1000000,
            'max_outlets' => 1,
            'trial_days' => 14,
            'sort_order' => 1,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Tenant Redirect Test',
            'slug' => 'tenant-redirect-test',
            'plan_id' => $plan->id,
            'owner_name' => 'Owner Redirect',
            'owner_email' => 'owner-redirect@example.com',
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
            'is_read_only' => false,
        ]);

        $this->outlet = Outlet::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Outlet Redirect Test',
            'slug' => 'outlet-redirect-test',
            'full_subdomain' => 'tenant-redirect-test.rupa.test',
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Redirect No. 1',
        ]);

        $this->staff = User::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $this->outlet->id,
            'role' => 'owner',
            'is_active' => true,
            'can_view_revenue' => true,
        ]);

        $this->customer = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $this->outlet->id,
            'password' => 'password123',
        ]);
    }

    public function test_staff_logout_uses_session_outlet_slug_for_redirect(): void
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['outlet_slug' => $this->outlet->slug])
            ->post(route('logout'));

        $response->assertRedirect(route('outlet.login', ['outletSlug' => $this->outlet->slug]));
    }

    public function test_staff_logout_uses_cookie_outlet_slug_when_session_missing(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
            'tenant_id' => null,
            'outlet_id' => null,
        ]);

        $response = $this->actingAs($superAdmin)
            ->withCookie(LoginRedirectResolver::STAFF_OUTLET_COOKIE, $this->outlet->slug)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_staff_route_redirects_to_outlet_login_using_cookie(): void
    {
        $response = $this->withCookie(LoginRedirectResolver::STAFF_OUTLET_COOKIE, $this->outlet->slug)
            ->get(route('dashboard'));

        $response->assertRedirect(route('outlet.login', ['outletSlug' => $this->outlet->slug]));
    }

    public function test_guest_platform_route_ignores_outlet_cookie_and_redirects_to_global_login(): void
    {
        $response = $this->withCookie(LoginRedirectResolver::STAFF_OUTLET_COOKIE, $this->outlet->slug)
            ->get(route('platform.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_customer_route_redirects_to_outlet_customer_login(): void
    {
        $response = $this->get(route('outlet.customer.dashboard', ['outletSlug' => $this->outlet->slug]));

        $response->assertRedirect(route('outlet.customer.login', ['outletSlug' => $this->outlet->slug]));
    }

    public function test_customer_logout_redirects_to_outlet_customer_login(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->post(route('outlet.customer.logout', ['outletSlug' => $this->outlet->slug]));

        $response->assertRedirect(route('outlet.customer.login', ['outletSlug' => $this->outlet->slug]));
    }
}
