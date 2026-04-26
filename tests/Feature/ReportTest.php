<?php

namespace Tests\Feature;

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

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');
        Setting::set('business_name', 'Test Clinic', 'string');
        Setting::set('feature_loyalty', true, 'boolean');
        Setting::set('feature_products', true, 'boolean');
    }

    public function test_report_index_page_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
    }

    public function test_revenue_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get(route('reports.revenue'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.revenue');
    }

    public function test_customers_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get(route('reports.customers'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.customers');
    }

    public function test_services_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get(route('reports.services'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.services');
    }

    public function test_appointments_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get(route('reports.appointments'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.appointments');
    }

    public function test_staff_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get(route('reports.staff'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.staff');
    }

    public function test_loyalty_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get(route('reports.loyalty'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.loyalty');
    }

    public function test_products_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get(route('reports.products'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.products');
    }

    public function test_reports_require_authentication(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_revenue_report_with_date_filter(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get(route('reports.revenue', [
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'period' => 'daily',
        ]));

        $response->assertStatus(200);
    }

    public function test_appointments_report_with_date_filter(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get(route('reports.appointments', [
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
    }

    public function test_staff_report_calculates_incentive_and_revenue_from_paid_service_transaction_items(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $staff = User::factory()->create(['role' => 'beautician', 'is_active' => true]);
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'incentive' => 25000,
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
            'subtotal' => 150000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 150000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $transaction->items()->create([
            'item_type' => 'service',
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'item_name' => $service->name,
            'quantity' => 1,
            'unit_price' => 150000,
            'discount' => 0,
            'total_price' => 150000,
        ]);

        $response = $this->actingAs($user)->get(route('reports.staff', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('summary', fn (array $summary): bool => (int) $summary['total_incentive'] === 25000);
        $response->assertViewHas('staffPerformance', function (array $staffPerformance) use ($staff): bool {
            return collect($staffPerformance)->contains(function (array $item) use ($staff): bool {
                return $item['staff']->id === $staff->id
                    && (int) $item['incentive'] === 25000
                    && (int) $item['revenue'] === 150000;
            });
        });
    }

    public function test_staff_report_uses_package_service_incentive_for_package_items(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $staff = User::factory()->create(['role' => 'beautician', 'is_active' => true]);
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'incentive' => 30000,
        ]);
        $package = Package::query()->create([
            'name' => 'Paket Facial',
            'description' => 'Paket 3 sesi',
            'service_id' => $service->id,
            'total_sessions' => 3,
            'original_price' => 450000,
            'package_price' => 300000,
            'validity_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $customer = Customer::factory()->create();
        $transaction = Transaction::query()->create([
            'customer_id' => $customer->id,
            'cashier_id' => $user->id,
            'subtotal' => 600000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 600000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $transaction->items()->create([
            'item_type' => 'package',
            'package_id' => $package->id,
            'staff_id' => $staff->id,
            'item_name' => $package->name,
            'quantity' => 2,
            'unit_price' => 300000,
            'discount' => 0,
            'total_price' => 600000,
        ]);

        $response = $this->actingAs($user)->get(route('reports.staff', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('staffPerformance', function (array $staffPerformance) use ($staff): bool {
            return collect($staffPerformance)->contains(function (array $item) use ($staff): bool {
                return $item['staff']->id === $staff->id
                    && (int) $item['incentive'] === 60000
                    && (int) $item['revenue'] === 600000;
            });
        });
    }

    public function test_completed_appointment_captures_incentive_snapshot_when_status_changes(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'incentive' => 40000,
        ]);

        $appointment = Appointment::factory()->create([
            'service_id' => $service->id,
            'status' => 'pending',
            'completed_incentive' => null,
        ]);

        $appointment->update(['status' => 'completed']);

        $this->assertEquals(40000, (int) $appointment->fresh()->completed_incentive);
    }
}
