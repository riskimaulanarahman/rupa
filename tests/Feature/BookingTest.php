<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Plan;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

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

        $tenant = Tenant::query()->create([
            'name' => 'Tenant Booking Test',
            'slug' => 'tenant-booking-test',
            'plan_id' => $plan->id,
            'owner_name' => 'Owner Booking',
            'owner_email' => 'owner-booking@example.com',
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
            'is_read_only' => false,
        ]);

        $this->outlet = Outlet::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Outlet Booking',
            'slug' => 'outlet-booking',
            'full_subdomain' => 'tenant-booking-test.rupa.test',
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Booking',
        ]);

        Cache::forget('setup_completed');
        Setting::withoutGlobalScopes()->updateOrCreate(
            ['key' => 'setup_completed'],
            ['tenant_id' => $tenant->id, 'outlet_id' => $this->outlet->id, 'value' => '1', 'type' => 'boolean']
        );
        Setting::withoutGlobalScopes()->updateOrCreate(
            ['key' => 'business_type'],
            ['tenant_id' => $tenant->id, 'outlet_id' => $this->outlet->id, 'value' => 'clinic', 'type' => 'string']
        );
        Setting::withoutGlobalScopes()->updateOrCreate(
            ['key' => 'business_name'],
            ['tenant_id' => $tenant->id, 'outlet_id' => $this->outlet->id, 'value' => 'Test Clinic', 'type' => 'string']
        );
    }

    public function test_booking_index_page_is_accessible(): void
    {
        // Create some service categories and services
        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
        ]);
        Service::factory()->create([
            'category_id' => $category->id,
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
        ]);

        $response = $this->get($this->outletRoute('index'));

        $response->assertStatus(200);
        $response->assertViewIs('booking.index');
        $response->assertDontSee('name="staff_id"', false);
    }

    public function test_outlet_booking_does_not_redirect_to_setup_when_setup_not_completed(): void
    {
        Cache::forget('setup_completed');
        Setting::withoutGlobalScopes()->updateOrCreate(
            ['key' => 'setup_completed'],
            ['tenant_id' => $this->outlet->tenant_id, 'outlet_id' => $this->outlet->id, 'value' => '0', 'type' => 'boolean']
        );

        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
        ]);
        Service::factory()->create([
            'category_id' => $category->id,
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
        ]);

        $response = $this->get($this->outletRoute('index'));

        $response->assertStatus(200);
        $response->assertViewIs('booking.index');
    }

    public function test_outlet_booking_shows_unavailable_page_when_booking_disabled(): void
    {
        Setting::withoutGlobalScopes()->updateOrCreate(
            ['key' => 'booking_enabled'],
            ['tenant_id' => $this->outlet->tenant_id, 'outlet_id' => $this->outlet->id, 'value' => '0', 'type' => 'boolean']
        );

        $response = $this->get($this->outletRoute('index'));

        $response->assertStatus(503);
        $response->assertViewIs('booking.unavailable');
    }

    public function test_booking_slots_returns_available_slots(): void
    {
        // Create a service for the slots query
        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
        ]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
            'duration_minutes' => 60,
        ]);

        // Create operating hours for all days
        for ($day = 0; $day <= 6; $day++) {
            \App\Models\OperatingHour::create([
                'tenant_id' => $this->outlet->tenant_id,
                'outlet_id' => $this->outlet->id,
                'day_of_week' => $day,
                'open_time' => '09:00',
                'close_time' => '17:00',
                'is_closed' => false,
            ]);
        }

        $tomorrow = now()->addDay()->format('Y-m-d');

        $response = $this->getJson($this->outletRoute('slots', [
            'date' => $tomorrow,
            'service_id' => $service->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['slots', 'morning', 'afternoon']);
    }

    public function test_booking_slots_requires_valid_date(): void
    {
        $response = $this->getJson($this->outletRoute('slots', ['date' => 'invalid-date']));

        $response->assertStatus(422);
    }

    public function test_booking_store_creates_appointment(): void
    {
        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
        ]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
            'duration_minutes' => 60,
        ]);

        $tomorrow = now()->addDay()->format('Y-m-d');

        $response = $this->post($this->outletRoute('store'), [
            'name' => 'Test Customer',
            'phone' => '081234567890',
            'email' => 'customer@test.com',
            'service_id' => $service->id,
            'appointment_date' => $tomorrow,
            'start_time' => '10:00',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check customer created
        $this->assertDatabaseHas('customers', [
            'name' => 'Test Customer',
            'phone' => '081234567890',
        ]);

        // Check appointment created
        $this->assertDatabaseHas('appointments', [
            'service_id' => $service->id,
            'outlet_id' => $this->outlet->id,
            'staff_id' => null,
            'status' => 'pending',
            'source' => 'online',
        ]);
    }

    public function test_booking_store_uses_existing_customer(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'phone' => '081234567890',
            'name' => 'Old Name',
        ]);

        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
        ]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'is_active' => true,
            'duration_minutes' => 60,
        ]);

        $tomorrow = now()->addDay()->format('Y-m-d');

        $this->post($this->outletRoute('store'), [
            'name' => 'Updated Name',
            'phone' => '081234567890',
            'service_id' => $service->id,
            'appointment_date' => $tomorrow,
            'start_time' => '10:00',
        ]);

        // Customer should be updated, not duplicated
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_booking_store_validates_required_fields(): void
    {
        $response = $this->post($this->outletRoute('store'), []);

        $response->assertSessionHasErrors(['name', 'phone', 'service_id', 'appointment_date', 'start_time']);
    }

    public function test_booking_confirmation_page_shows_appointment(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $appointment = Appointment::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);

        $response = $this->get($this->outletRoute('confirmation', ['appointment' => $appointment]));

        $response->assertStatus(200);
        $response->assertViewIs('booking.confirmation');
        $response->assertViewHas('appointment');
    }

    public function test_booking_status_page_is_accessible(): void
    {
        $response = $this->get($this->outletRoute('status'));

        $response->assertStatus(200);
        $response->assertViewIs('booking.status');
    }

    public function test_booking_status_shows_customer_appointments(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'phone' => '081234567890',
        ]);
        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        Appointment::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $response = $this->get($this->outletRoute('status', ['phone' => '081234567890']));

        $response->assertStatus(200);
        $response->assertViewHas('appointments');
    }

    public function test_booking_cancel_cancels_pending_appointment(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $appointment = Appointment::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'status' => 'pending',
        ]);

        $response = $this->post($this->outletRoute('cancel', ['appointment' => $appointment]));

        $response->assertRedirect($this->outletRoute('index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_booking_cancel_fails_for_completed_appointment(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $appointment = Appointment::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'status' => 'completed',
        ]);

        $response = $this->post($this->outletRoute('cancel', ['appointment' => $appointment]));

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed',
        ]);
    }

    public function test_booking_cancel_fails_if_too_late(): void
    {
        $customer = Customer::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $category = ServiceCategory::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
        ]);

        // Create appointment starting in 1 hour (less than 2 hour policy)
        $appointment = Appointment::factory()->create([
            'tenant_id' => $this->outlet->tenant_id,
            'outlet_id' => $this->outlet->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_date' => now()->format('Y-m-d'),
            'start_time' => now()->addHour()->format('H:i'),
            'status' => 'pending',
        ]);

        $response = $this->post($this->outletRoute('cancel', ['appointment' => $appointment]));

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'pending',
        ]);
    }

    public function test_booking_route_without_outlet_context_returns_not_found(): void
    {
        $response = $this->get(route('booking.index'));
        $response->assertNotFound();
    }

    private function outletRoute(string $name, array $params = []): string
    {
        return route("outlet.booking.{$name}", array_merge([
            'outletSlug' => $this->outlet->slug,
        ], $params));
    }
}
