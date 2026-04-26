<?php

namespace Tests\Feature;

use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ServicePricingRangeTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');

        $this->owner = User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->category = ServiceCategory::factory()->create([
            'is_active' => true,
        ]);
    }

    public function test_can_store_service_with_price_range(): void
    {
        $response = $this->actingAs($this->owner)->post(route('services.store'), [
            'category_id' => $this->category->id,
            'name' => 'Laser Glow',
            'description' => 'Harga tergantung area tindakan',
            'duration_minutes' => 45,
            'pricing_mode' => Service::PRICING_MODE_RANGE,
            'price_min' => 300000,
            'price_max' => 500000,
            'incentive' => 50000,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('services', [
            'name' => 'Laser Glow',
            'pricing_mode' => Service::PRICING_MODE_RANGE,
            'price' => 300000,
            'price_min' => 300000,
            'price_max' => 500000,
        ]);
    }

    public function test_can_update_service_from_range_to_fixed_price(): void
    {
        $service = Service::factory()->create([
            'category_id' => $this->category->id,
            'pricing_mode' => Service::PRICING_MODE_RANGE,
            'price' => 250000,
            'price_min' => 250000,
            'price_max' => 450000,
        ]);

        $response = $this->actingAs($this->owner)->put(route('services.update', $service), [
            'category_id' => $this->category->id,
            'name' => $service->name,
            'description' => $service->description,
            'duration_minutes' => $service->duration_minutes,
            'pricing_mode' => Service::PRICING_MODE_FIXED,
            'price' => 275000,
            'incentive' => $service->incentive,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('services.index'));
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'pricing_mode' => Service::PRICING_MODE_FIXED,
            'price' => 275000,
            'price_min' => 275000,
            'price_max' => 275000,
        ]);
    }

    public function test_service_range_validation_rejects_max_price_below_min_price(): void
    {
        $response = $this->actingAs($this->owner)->from(route('services.create'))->post(route('services.store'), [
            'category_id' => $this->category->id,
            'name' => 'Peeling Premium',
            'duration_minutes' => 60,
            'pricing_mode' => Service::PRICING_MODE_RANGE,
            'price_min' => 400000,
            'price_max' => 300000,
            'incentive' => 25000,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('services.create'));
        $response->assertSessionHasErrors(['price_max']);
        $this->assertDatabaseMissing('services', [
            'name' => 'Peeling Premium',
        ]);
    }

    public function test_service_resource_exposes_price_range_fields_and_keeps_effective_price(): void
    {
        $service = Service::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Facial Signature',
            'pricing_mode' => Service::PRICING_MODE_RANGE,
            'price' => 150000,
            'price_min' => 150000,
            'price_max' => 250000,
        ]);

        $resource = (new ServiceResource($service))->toArray(Request::create('/'));

        $this->assertSame(Service::PRICING_MODE_RANGE, $resource['pricing_mode']);
        $this->assertSame(150000.0, $resource['price']);
        $this->assertSame(150000.0, $resource['price_min']);
        $this->assertSame(250000.0, $resource['price_max']);
        $this->assertTrue($resource['has_price_range']);
        $this->assertSame('Rp 150.000 - Rp 250.000', $resource['formatted_price']);
    }
}
