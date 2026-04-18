<?php

namespace Tests\Feature;

use App\Models\OperatingHour;
use App\Models\Outlet;
use App\Models\OutletLandingContent;
use App\Models\Plan;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletLandingPageTest extends TestCase
{
    use RefreshDatabase;

    private Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price_monthly' => 400000,
            'price_yearly' => 4000000,
            'max_outlets' => 5,
            'trial_days' => 14,
            'sort_order' => 2,
            'is_active' => true,
            'is_featured' => true,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Tenant Landing Test',
            'slug' => 'tenant-landing-test',
            'plan_id' => $plan->id,
            'owner_name' => 'Owner Landing',
            'owner_email' => 'owner-landing@example.com',
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
            'is_read_only' => false,
        ]);

        $this->outlet = Outlet::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Outlet Landing',
            'slug' => 'outlet-landing',
            'full_subdomain' => 'tenant-landing-test.rupa.test',
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Melati No. 12',
            'phone' => '081234000123',
            'email' => 'outlet-landing@example.com',
        ]);
    }

    public function test_outlet_landing_shows_professional_sections_and_customer_staff_login_buttons(): void
    {
        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'name' => 'Facial',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Service::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Facial Glow',
            'price' => 150000,
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        OperatingHour::query()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'day_of_week' => 1,
            'open_time' => '09:00',
            'close_time' => '18:00',
            'is_closed' => false,
        ]);

        $response = $this->get(route('outlet.landing.show', ['outletSlug' => $this->outlet->slug]));

        $response->assertStatus(200);
        $response->assertSeeText('Layanan & Harga');
        $response->assertSeeText('Pertanyaan Umum');
        $response->assertSeeText('Login Pelanggan');
        $response->assertSeeText('Login Staff');
        $response->assertSee(route('outlet.customer.login', ['outletSlug' => $this->outlet->slug]), false);
        $response->assertSee(route('outlet.login', ['outletSlug' => $this->outlet->slug]), false);
    }

    public function test_outlet_landing_displays_service_category_and_rupiah_price(): void
    {
        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'name' => 'Body Treatment',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Service::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'category_id' => $category->id,
            'name' => 'Body Massage Premium',
            'price' => 250000,
            'duration_minutes' => 90,
            'is_active' => true,
        ]);

        $response = $this->get(route('outlet.landing.show', ['outletSlug' => $this->outlet->slug]));

        $response->assertStatus(200);
        $response->assertSeeText('Body Treatment');
        $response->assertSeeText('Body Massage Premium');
        $response->assertSeeText('Rp 250.000');
    }

    public function test_outlet_landing_falls_back_when_testimonials_or_faq_json_invalid(): void
    {
        OutletLandingContent::query()->updateOrCreate(
            ['outlet_id' => $this->outlet->id, 'key' => 'testimonials_json'],
            ['section' => 'testimonials', 'value' => '{invalid', 'type' => 'json']
        );

        OutletLandingContent::query()->updateOrCreate(
            ['outlet_id' => $this->outlet->id, 'key' => 'faqs_json'],
            ['section' => 'faq', 'value' => '{invalid', 'type' => 'json']
        );

        $response = $this->get(route('outlet.landing.show', ['outletSlug' => $this->outlet->slug]));

        $response->assertStatus(200);
        $response->assertSeeText('Pelayanan sangat detail dan hasilnya memuaskan. Saya selalu kembali ke outlet ini.');
        $response->assertSeeText('Apakah perlu reservasi sebelum datang?');
    }

    public function test_outlet_landing_hides_gallery_nav_when_gallery_is_empty(): void
    {
        $response = $this->get(route('outlet.landing.show', ['outletSlug' => $this->outlet->slug]));

        $response->assertStatus(200);
        $response->assertDontSee('href="#galeri"', false);
    }

    public function test_outlet_landing_staff_action_falls_back_to_panel_for_admin_without_revenue_access(): void
    {
        $admin = User::query()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'name' => 'Admin Non Revenue',
            'email' => 'admin-non-revenue@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'phone' => '081233344455',
            'is_active' => true,
            'can_view_revenue' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('outlet.landing.show', ['outletSlug' => $this->outlet->slug]));

        $response->assertStatus(200);
        $response->assertSeeText('Panel Staff');
        $response->assertSee(route('appointments.index'), false);
    }
}
