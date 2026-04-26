<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Transaction;
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
        $user = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'beautician', 'is_active' => true]);
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'incentive' => 30000,
        ]);
        $customer = Customer::factory()->create();
        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_date' => now()->format('Y-m-d'),
            'status' => 'completed',
        ]);
        $transaction = Transaction::query()->create([
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'cashier_id' => $user->id,
            'subtotal' => 200000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 200000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $transaction->items()->create([
            'item_type' => 'service',
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'item_name' => $service->name,
            'quantity' => 1,
            'unit_price' => 200000,
            'discount' => 0,
            'total_price' => 200000,
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

    public function test_staff_report_api_uses_customer_package_service_incentive(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'beautician', 'is_active' => true]);
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'incentive' => 18000,
        ]);
        $package = Package::query()->create([
            'name' => 'Paket Usage',
            'description' => 'Usage package',
            'service_id' => $service->id,
            'total_sessions' => 5,
            'original_price' => 500000,
            'package_price' => 400000,
            'validity_days' => 45,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $customer = Customer::factory()->create();
        $customerPackage = CustomerPackage::query()->create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'sold_by' => $user->id,
            'price_paid' => 400000,
            'sessions_total' => 5,
            'sessions_used' => 0,
            'purchased_at' => now()->toDateString(),
            'expires_at' => now()->addDays(45)->toDateString(),
            'status' => 'active',
            'notes' => 'Test API package',
        ]);
        $transaction = Transaction::query()->create([
            'customer_id' => $customer->id,
            'cashier_id' => $user->id,
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $transaction->items()->create([
            'item_type' => 'customer_package',
            'customer_package_id' => $customerPackage->id,
            'staff_id' => $staff->id,
            'item_name' => 'Paket Usage (Pakai Sesi)',
            'quantity' => 2,
            'unit_price' => 0,
            'discount' => 0,
            'total_price' => 0,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/reports/staff?period=custom&start_date='.now()->format('Y-m-d').'&end_date='.now()->format('Y-m-d'));

        $response->assertStatus(200);

        $staffItem = collect($response->json('data'))
            ->firstWhere('id', $staff->id);

        $this->assertNotNull($staffItem);
        $this->assertEquals(36000, (int) ($staffItem['incentive'] ?? 0));
    }

    public function test_service_api_resource_includes_incentive_fields(): void
    {
        $user = User::factory()->create(['role' => 'owner', 'is_active' => true]);
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
