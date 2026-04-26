<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceIncentiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');
    }

    public function test_can_store_service_with_incentive(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $category = ServiceCategory::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->post(route('services.store'), [
            'category_id' => $category->id,
            'name' => 'Facial Test Incentive',
            'description' => 'Testing incentive on create',
            'duration_minutes' => 60,
            'pricing_mode' => Service::PRICING_MODE_FIXED,
            'price' => 200000,
            'incentive' => 30000,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('services', [
            'name' => 'Facial Test Incentive',
            'price' => 200000,
            'incentive' => 30000,
        ]);
    }

    public function test_can_update_service_incentive(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $category = ServiceCategory::factory()->create(['is_active' => true]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'incentive' => 10000,
        ]);

        $response = $this->actingAs($user)->put(route('services.update', $service), [
            'category_id' => $category->id,
            'name' => $service->name,
            'description' => $service->description,
            'duration_minutes' => $service->duration_minutes,
            'pricing_mode' => Service::PRICING_MODE_FIXED,
            'price' => $service->price,
            'incentive' => 45000,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'incentive' => 45000,
        ]);
    }

    public function test_edit_service_view_passes_decimal_prices_through_safe_js_encoding(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $category = ServiceCategory::factory()->create(['is_active' => true]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'price' => 50000,
            'incentive' => 15000,
        ]);

        $response = $this->actingAs($user)->get(route('services.edit', $service));

        $response->assertOk();
        $response->assertSee('x-data="currencyInput(\'50000.00\')"', false);
        $response->assertSee('x-data="currencyInput(\'15000.00\')"', false);
    }

    public function test_updating_service_without_changing_price_keeps_decimal_cast_value_stable(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $category = ServiceCategory::factory()->create(['is_active' => true]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'price' => 50000,
            'incentive' => 15000,
        ]);

        $response = $this->actingAs($user)->put(route('services.update', $service), [
            'category_id' => $category->id,
            'name' => $service->name,
            'description' => 'Description updated only',
            'duration_minutes' => $service->duration_minutes,
            'pricing_mode' => Service::PRICING_MODE_FIXED,
            'price' => 50000,
            'incentive' => 15000,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'description' => 'Description updated only',
            'price' => 50000,
            'incentive' => 15000,
        ]);
    }
}
