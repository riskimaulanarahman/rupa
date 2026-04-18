<?php

namespace Tests\Feature;

use App\Models\LandingContent;
use App\Models\OutletInvoice;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PlatformSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_platform_pages(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $tenant = $this->createTenant();

        $this->actingAs($superAdmin)->get(route('platform.dashboard'))->assertOk();
        $this->actingAs($superAdmin)->get(route('platform.tenants.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('platform.tenants.show', $tenant))->assertOk();
        $this->actingAs($superAdmin)->get(route('platform.plans.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('platform.billing.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('platform.revenue.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('platform.landing.index'))->assertOk();
    }

    public function test_non_superadmin_is_forbidden_from_platform_pages(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->actingAs($owner)->get(route('platform.dashboard'))->assertForbidden();
    }

    public function test_login_redirects_superadmin_to_platform_dashboard(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'superadmin-login@example.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => $superAdmin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('platform.dashboard'));
    }

    public function test_superadmin_is_redirected_from_non_platform_web_routes(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertRedirect(route('platform.dashboard'));

        $this->actingAs($superAdmin)
            ->get(route('appointments.index'))
            ->assertRedirect(route('platform.dashboard'));
    }

    public function test_superadmin_gets_forbidden_json_response_on_non_platform_routes(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)
            ->getJson(route('dashboard'))
            ->assertForbidden()
            ->assertJson([
                'message' => 'Superadmin hanya dapat mengakses halaman platform.',
            ]);
    }

    public function test_superadmin_logout_redirects_to_global_login(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)
            ->post(route('logout'))
            ->assertRedirect(route('login'));
    }

    public function test_superadmin_can_update_tenant_saas_configuration(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $tenant = $this->createTenant();
        $newPlan = Plan::query()->create([
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

        $this->actingAs($superAdmin)
            ->patch(route('platform.tenants.update', $tenant), [
                'plan_id' => $newPlan->id,
                'status' => 'active',
                'subscription_ends_at' => '2026-12-31',
                'is_read_only' => '1',
            ])
            ->assertSessionHas('success');

        $tenant->refresh();

        $this->assertSame($newPlan->id, $tenant->plan_id);
        $this->assertSame('active', $tenant->status);
        $this->assertSame('2026-12-31', $tenant->subscription_ends_at?->format('Y-m-d'));
        $this->assertTrue((bool) $tenant->is_read_only);
    }

    public function test_landing_text_uses_database_content_when_available(): void
    {
        LandingContent::query()->create([
            'key' => 'hero_badge',
            'section' => 'Hero',
            'description' => 'Hero badge',
            'content' => [
                'id' => 'Badge Custom Dari DB',
                'en' => 'Custom Badge From DB',
            ],
        ]);

        Cache::forget('landing_content.id.hero_badge');

        $this->get(route('home'))->assertSee('Badge Custom Dari DB');
    }

    public function test_route_list_platform_command_is_valid(): void
    {
        $this->artisan('route:list --path=platform')
            ->assertExitCode(0);
    }

    public function test_approve_via_email_link_can_only_be_used_once(): void
    {
        $tenant = $this->createTenant();
        $invoice = OutletInvoice::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $tenant->plan_id,
            'billing_period' => now()->format('Y-m'),
            'outlet_count' => 1,
            'plan_price' => 100000,
            'total_amount' => 100000,
            'status' => 'awaiting_verification',
            'due_date' => now()->addDays(7),
            'approve_email_token' => 'approve-token-1',
            'reject_email_token' => 'reject-token-1',
        ]);

        $url = URL::temporarySignedRoute(
            'platform.billing.approve-via-email',
            now()->addHour(),
            ['invoice' => $invoice->id, 'token' => 'approve-token-1']
        );

        $this->get($url)
            ->assertOk()
            ->assertSee('Invoice berhasil di-approve melalui email.');

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertNull($invoice->approve_email_token);
        $this->assertNull($invoice->reject_email_token);
        $this->assertNotNull($invoice->approve_email_used_at);

        $this->get($url)
            ->assertOk()
            ->assertSee('Token verifikasi sudah tidak berlaku.');
    }

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'email' => 'superadmin@example.com',
            'role' => 'superadmin',
            'is_active' => true,
            'tenant_id' => null,
            'outlet_id' => null,
        ]);
    }

    private function createTenant(): Tenant
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

        return Tenant::query()->create([
            'name' => 'Tenant Test',
            'slug' => 'tenant-test',
            'plan_id' => $plan->id,
            'owner_name' => 'Owner Test',
            'owner_email' => 'owner-test@example.com',
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'is_read_only' => false,
        ]);
    }
}
