<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Branding\BrandIconGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FaviconBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_uses_default_favicon_when_custom_favicon_not_set(): void
    {
        Setting::setGlobal('platform_brand_logo_favicon', '');
        clear_brand_cache();

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('href="'.asset('favicon.ico').'"', false);
    }

    public function test_login_page_uses_platform_global_favicon_when_setting_exists(): void
    {
        Setting::setGlobal('platform_brand_logo_favicon', 'branding/custom-favicon.png');
        clear_brand_cache();

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('href="'.asset('storage/branding/custom-favicon.png').'"', false);
    }

    public function test_owner_favicon_setting_is_ignored_when_resolving_favicon(): void
    {
        Setting::set('brand_logo_favicon', 'branding/outlet-favicon.png');
        Setting::setGlobal('platform_brand_logo_favicon', '');
        clear_brand_cache();

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('href="'.asset('favicon.ico').'"', false);
        $response->assertDontSee('href="'.asset('storage/branding/outlet-favicon.png').'"', false);
    }

    public function test_branding_update_triggers_icon_generation_when_logo_uploaded(): void
    {
        Storage::fake('public');
        $owner = $this->createOwnerContext();

        $generatorFake = new class extends BrandIconGenerator
        {
            public bool $called = false;

            public function generate(string $source = 'auto', bool $force = false): array
            {
                $this->called = ($source === 'auto' && $force === true);

                return ['source' => '/tmp/source.png', 'generated' => ['favicon.ico']];
            }
        };

        $this->app->instance(BrandIconGenerator::class, $generatorFake);

        $response = $this->actingAs($owner)
            ->from(route('settings.branding'))
            ->post(route('settings.branding.update'), [
                'brand_logo_path' => UploadedFile::fake()->image('logo.png', 400, 400),
            ]);

        $response->assertRedirect(route('settings.branding'));
        $response->assertSessionHas('success');
        $this->assertNotEmpty(Setting::get('brand_logo_path'));
        $this->assertTrue($generatorFake->called);
    }

    public function test_owner_branding_update_rejects_favicon_upload(): void
    {
        Storage::fake('public');
        $owner = $this->createOwnerContext();

        $response = $this->actingAs($owner)
            ->from(route('settings.branding'))
            ->post(route('settings.branding.update'), [
                'brand_logo_favicon' => UploadedFile::fake()->image('favicon.png', 128, 128),
            ]);

        $response->assertRedirect(route('settings.branding'));
        $response->assertSessionHasErrors('brand_logo_favicon');
    }

    public function test_owner_cannot_remove_favicon_from_branding_endpoint(): void
    {
        $owner = $this->createOwnerContext();

        $this->actingAs($owner)
            ->post(route('settings.branding.remove-logo'), ['type' => 'favicon'])
            ->assertForbidden();
    }

    public function test_platform_favicon_update_triggers_icon_generation(): void
    {
        Storage::fake('public');
        $superAdmin = $this->createSuperAdmin();

        $generatorFake = new class extends BrandIconGenerator
        {
            public bool $called = false;

            public function generate(string $source = 'auto', bool $force = false): array
            {
                $this->called = ($source === 'auto' && $force === true);

                return ['source' => '/tmp/source.png', 'generated' => ['favicon.ico']];
            }
        };

        $this->app->instance(BrandIconGenerator::class, $generatorFake);

        $response = $this->actingAs($superAdmin)
            ->from(route('platform.branding.favicon'))
            ->post(route('platform.branding.favicon.update'), [
                'platform_brand_logo_favicon' => UploadedFile::fake()->image('platform-favicon.png', 128, 128),
            ]);

        $response->assertRedirect(route('platform.branding.favicon'));
        $response->assertSessionHas('success');
        $savedPath = (string) Setting::getGlobal('platform_brand_logo_favicon');
        $this->assertNotEmpty($savedPath);
        $this->assertStringStartsWith('branding/platform-favicon-', $savedPath);
        $this->assertTrue(File::exists(public_path($savedPath)));
        $this->assertTrue($generatorFake->called);

        File::delete(public_path($savedPath));
    }

    public function test_owner_branding_update_skips_icon_generation_when_platform_global_favicon_exists(): void
    {
        Storage::fake('public');
        $owner = $this->createOwnerContext();
        Setting::setGlobal('platform_brand_logo_favicon', 'branding/platform-favicon.png');
        clear_brand_cache();

        $generatorFake = new class extends BrandIconGenerator
        {
            public bool $called = false;

            public function generate(string $source = 'auto', bool $force = false): array
            {
                $this->called = true;

                return ['source' => '/tmp/source.png', 'generated' => ['favicon.ico']];
            }
        };

        $this->app->instance(BrandIconGenerator::class, $generatorFake);

        $response = $this->actingAs($owner)
            ->from(route('settings.branding'))
            ->post(route('settings.branding.update'), [
                'brand_logo_path' => UploadedFile::fake()->image('owner-logo.png', 256, 256),
            ]);

        $response->assertRedirect(route('settings.branding'));
        $response->assertSessionHas('success');
        $this->assertNotEmpty(Setting::get('brand_logo_path'));
        $this->assertFalse($generatorFake->called);
    }

    private function createOwnerContext(): User
    {
        $plan = Plan::query()->create([
            'name' => 'Starter Favicon Test',
            'slug' => 'starter-favicon-test',
            'price_monthly' => 100000,
            'price_yearly' => 1000000,
            'max_outlets' => 1,
            'trial_days' => 14,
            'sort_order' => 1,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Tenant Favicon Test',
            'slug' => 'tenant-favicon-test',
            'plan_id' => $plan->id,
            'owner_name' => 'Owner Favicon',
            'owner_email' => 'owner-favicon@example.com',
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
            'is_read_only' => false,
        ]);

        $outlet = Outlet::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Outlet Favicon',
            'slug' => 'outlet-favicon-test',
            'full_subdomain' => 'tenant-favicon-test.rupa.test',
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Favicon No. 1',
        ]);

        return User::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role' => 'owner',
            'is_active' => true,
            'can_view_revenue' => true,
        ]);
    }

    private function createSuperAdmin(): User
    {
        return User::factory()->create([
            'email' => 'platform-favicon-superadmin@example.com',
            'role' => 'superadmin',
            'is_active' => true,
            'tenant_id' => null,
            'outlet_id' => null,
        ]);
    }
}
