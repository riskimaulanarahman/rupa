<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');
    }

    public function test_staff_report_api_includes_incentive(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'incentive' => 30000,
        ]);
        $customer = Customer::factory()->create();

        Appointment::factory()->create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => now()->format('Y-m-d'),
            'status' => 'completed',
            'completed_incentive' => 30000,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/reports/staff?period=custom&start_date='.now()->format('Y-m-d').'&end_date='.now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'appointments', 'completed', 'revenue', 'incentive'],
                ],
            ]);

        $staffItem = collect($response->json('data'))
            ->firstWhere('id', $staff->id);

        $this->assertNotNull($staffItem);
        $this->assertEquals(30000, (int) ($staffItem['incentive'] ?? 0));
    }

    public function test_service_api_resource_includes_incentive_fields(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'incentive' => 45000,
            'is_active' => true,
        ]);

        $indexResponse = $this->actingAs($user, 'sanctum')->getJson('/api/v1/services');
        $indexResponse->assertStatus(200)
            ->assertJsonPath('data.0.incentive', 45000);

        $showResponse = $this->actingAs($user, 'sanctum')->getJson('/api/v1/services/'.$service->id);
        $showResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'incentive', 'formatted_incentive'],
            ])
            ->assertJsonPath('data.incentive', 45000);
    }
}
