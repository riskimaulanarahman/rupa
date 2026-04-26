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

class AppointmentCustomerPickerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');
        Setting::set('business_name', 'Test Clinic', 'string');
    }

    public function test_create_page_renders_searchable_customer_picker_and_store_accepts_customer_id(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $customer = Customer::factory()->create([
            'name' => 'Rina Search',
            'phone' => '081234567890',
        ]);
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get(route('appointments.create'));

        $response->assertOk();
        $response->assertSee('data-customer-picker', false);
        $response->assertSee('name="customer_id"', false);
        $response->assertSee('placeholder="Pilih pelanggan"', false);
        $response->assertSee('Rina Search', false);

        $storeResponse = $this->actingAs($owner)->post(route('appointments.store'), [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'source' => 'walk_in',
        ]);

        $storeResponse->assertRedirect();
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'start_time' => '10:00',
            'status' => 'pending',
        ]);
    }

    public function test_edit_page_preloads_selected_customer_label_in_searchable_picker(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $customer = Customer::factory()->create([
            'name' => 'Maya Editable',
            'phone' => '081298765432',
        ]);
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->create([
            'category_id' => $category->id,
            'duration_minutes' => 60,
            'is_active' => true,
        ]);
        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($owner)->get(route('appointments.edit', $appointment));

        $response->assertOk();
        $response->assertSee('data-customer-picker', false);
        $response->assertSee('value="Maya Editable - 081298765432"', false);
        $response->assertSee('value="'.$customer->id.'"', false);
    }
}
