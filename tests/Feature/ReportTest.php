<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
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
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
    }

    public function test_revenue_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('reports.revenue'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.revenue');
    }

    public function test_customers_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('reports.customers'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.customers');
    }

    public function test_services_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('reports.services'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.services');
    }

    public function test_appointments_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('reports.appointments'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.appointments');
    }

    public function test_staff_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('reports.staff'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.staff');
    }

    public function test_loyalty_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('reports.loyalty'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.loyalty');
    }

    public function test_products_report_is_accessible(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

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
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('reports.revenue', [
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'period' => 'daily',
        ]));

        $response->assertStatus(200);
    }

    public function test_appointments_report_with_date_filter(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('reports.appointments', [
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
    }

    public function test_staff_report_calculates_incentive_from_completed_appointments(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'incentive' => 25000,
        ]);
        $customer = Customer::factory()->create();

        Appointment::factory()->create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => now()->format('Y-m-d'),
            'status' => 'completed',
            'completed_incentive' => 25000,
        ]);

        $response = $this->actingAs($user)->get(route('reports.staff', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('summary', fn (array $summary): bool => (int) $summary['total_incentive'] === 25000);
        $response->assertViewHas('staffPerformance', function (array $staffPerformance) use ($staff): bool {
            return collect($staffPerformance)->contains(function (array $item) use ($staff): bool {
                return $item['staff']->id === $staff->id && (int) $item['incentive'] === 25000;
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
