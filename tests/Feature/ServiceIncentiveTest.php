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
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = ServiceCategory::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->post(route('services.store'), [
            'category_id' => $category->id,
            'name' => 'Facial Test Incentive',
            'description' => 'Testing incentive on create',
            'duration_minutes' => 60,
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
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
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
}
