<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_403_URI = '/__tests/errors/403';

    private const TEST_419_URI = '/__tests/errors/419';

    private const TEST_500_URI = '/__tests/errors/500';

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');
        Setting::set('business_name', 'Rupa Test', 'string');

        Route::group([], function (): void {
            Route::get(self::TEST_403_URI, fn () => abort(403, 'Akses uji 403 ditolak.'));
            Route::get(self::TEST_419_URI, fn () => abort(419, 'Sesi habis saat uji.'));
            Route::get(self::TEST_500_URI, fn () => abort(500, 'Sensitive internal exception detail'));
        });
    }

    public function test_guest_404_renders_custom_error_page_and_home_cta(): void
    {
        $response = $this->get('/__tests/errors/missing-page');

        $response->assertStatus(404);
        $response->assertSee(__('errors.status.404.title'));
        $response->assertSee('href="'.route('home').'"', false);
    }

    public function test_abort_403_shows_detail_message_for_4xx(): void
    {
        $response = $this->get(self::TEST_403_URI);

        $response->assertStatus(403);
        $response->assertSee(__('errors.status.403.title'));
        $response->assertSee(__('errors.detail_label'));
        $response->assertSee('Akses uji 403 ditolak.');
    }

    public function test_abort_419_shows_page_expired_copy(): void
    {
        $response = $this->get(self::TEST_419_URI);

        $response->assertStatus(419);
        $response->assertSee(__('errors.status.419.title'));
        $response->assertSee(__('errors.status.419.description'));
    }

    public function test_web_user_without_revenue_access_gets_appointments_cta(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'can_view_revenue' => false,
        ]);

        $response = $this->actingAs($user)->get(self::TEST_403_URI);

        $response->assertStatus(403);
        $response->assertSee('href="'.route('appointments.index').'"', false);
    }

    public function test_owner_gets_dashboard_cta(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_view_revenue' => false,
        ]);

        $response = $this->actingAs($owner)->get(self::TEST_403_URI);

        $response->assertStatus(403);
        $response->assertSee('href="'.route('dashboard').'"', false);
    }

    public function test_customer_guard_gets_portal_dashboard_cta(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'price_monthly' => 100000,
            'price_yearly' => 1000000,
            'max_outlets' => 1,
            'trial_days' => 14,
            'sort_order' => 1,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Tenant Error Test',
            'slug' => 'tenant-error-test',
            'plan_id' => $plan->id,
            'owner_name' => 'Owner Error',
            'owner_email' => 'owner-error@example.com',
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
            'is_read_only' => false,
        ]);

        $outlet = Outlet::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Outlet Error',
            'slug' => 'outlet-error',
            'full_subdomain' => 'tenant-error-test.rupa.test',
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Error Test',
        ]);

        $customer = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'password' => 'password',
        ]);

        $response = $this->actingAs($customer, 'customer')->get(self::TEST_403_URI);

        $response->assertStatus(403);
        $response->assertSee('href="'.route('outlet.customer.dashboard', ['outletSlug' => $outlet->slug]).'"', false);
    }

    public function test_abort_500_uses_custom_page_without_leaking_detail_message(): void
    {
        $response = $this->get(self::TEST_500_URI);

        $response->assertStatus(500);
        $response->assertSee(__('errors.status.500.title'));
        $response->assertDontSee('Sensitive internal exception detail');
        $response->assertDontSee(__('errors.detail_label'));
    }
}
