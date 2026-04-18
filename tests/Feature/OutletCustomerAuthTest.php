<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletCustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    private Outlet $outletA;

    private Outlet $outletB;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'price_monthly' => 100000,
            'price_yearly' => 1000000,
            'max_outlets' => 2,
            'trial_days' => 14,
            'sort_order' => 1,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Tenant Customer Auth',
            'slug' => 'tenant-customer-auth',
            'plan_id' => $plan->id,
            'owner_name' => 'Owner Customer Auth',
            'owner_email' => 'owner-customer-auth@example.com',
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
            'is_read_only' => false,
        ]);

        $this->outletA = Outlet::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Outlet A',
            'slug' => 'outlet-a',
            'full_subdomain' => 'tenant-customer-auth-a.rupa.test',
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Outlet A',
        ]);

        $this->outletB = Outlet::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Outlet B',
            'slug' => 'outlet-b',
            'full_subdomain' => 'tenant-customer-auth-b.rupa.test',
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Outlet B',
        ]);
    }

    public function test_customer_login_route_is_available_per_outlet_slug(): void
    {
        $response = $this->get(route('outlet.customer.login', [
            'outletSlug' => $this->outletA->slug,
        ]));

        $response->assertStatus(200);
    }

    public function test_legacy_portal_login_route_is_not_available(): void
    {
        $response = $this->get('/portal/login');

        $response->assertNotFound();
    }

    public function test_customer_can_login_on_matching_outlet(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->outletA->tenant_id,
            'outlet_id' => $this->outletA->id,
            'email' => 'customer-a@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('outlet.customer.login.submit', [
            'outletSlug' => $this->outletA->slug,
        ]), [
            'email' => $customer->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('outlet.customer.dashboard', [
            'outletSlug' => $this->outletA->slug,
        ]));
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_customer_cannot_login_on_different_outlet_with_same_credentials(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->outletA->tenant_id,
            'outlet_id' => $this->outletA->id,
            'email' => 'customer-outlet-a@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('outlet.customer.login.submit', [
            'outletSlug' => $this->outletB->slug,
        ]), [
            'email' => $customer->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('customer');
    }

    public function test_authenticated_customer_is_forced_to_relogin_when_opening_other_outlet_dashboard(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->outletA->tenant_id,
            'outlet_id' => $this->outletA->id,
            'email' => 'customer-session-a@example.com',
            'password' => 'password123',
        ]);

        $response = $this
            ->actingAs($customer, 'customer')
            ->get(route('outlet.customer.dashboard', [
                'outletSlug' => $this->outletB->slug,
            ]));

        $response->assertRedirect(route('outlet.customer.login', [
            'outletSlug' => $this->outletB->slug,
        ]));
        $this->assertGuest('customer');
    }

    public function test_register_from_outlet_sets_customer_tenant_and_outlet(): void
    {
        $response = $this->post(route('outlet.customer.register.submit', [
            'outletSlug' => $this->outletA->slug,
        ]), [
            'name' => 'Pelanggan Baru',
            'email' => 'pelanggan-baru@example.com',
            'phone' => '081234000456',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('outlet.customer.dashboard', [
            'outletSlug' => $this->outletA->slug,
        ]));

        $this->assertDatabaseHas('customers', [
            'email' => 'pelanggan-baru@example.com',
            'tenant_id' => $this->outletA->tenant_id,
            'outlet_id' => $this->outletA->id,
        ]);
    }
}
