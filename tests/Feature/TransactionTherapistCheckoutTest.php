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

class TransactionTherapistCheckoutTest extends TestCase
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

    public function test_checkout_page_preselects_appointment_staff_and_limits_therapist_assignment_to_supported_item_types(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $staff = User::factory()->create(['role' => 'beautician', 'is_active' => true]);
        $customer = Customer::factory()->create();
        $service = $this->createService();
        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($owner)->get(route('transactions.create', [
            'appointment_id' => $appointment->id,
        ]));

        $response->assertOk();
        $response->assertSee('staff_id', false);
        $response->assertSee('"staff_id":'.$staff->id, false);
        $response->assertSee("['service', 'package', 'customer_package'].includes(item.item_type)", false);
    }

    public function test_transaction_store_requires_therapist_for_service_items(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $customer = Customer::factory()->create();
        $service = $this->createService();

        $response = $this->actingAs($owner)->post(route('transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [[
                'item_type' => 'service',
                'service_id' => $service->id,
                'item_name' => $service->name,
                'quantity' => 1,
                'unit_price' => $service->price,
                'discount' => 0,
            ]],
        ]);

        $response->assertSessionHasErrors(['items.0.staff_id']);
    }

    public function test_transaction_store_persists_therapist_for_service_package_and_customer_package_items(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $staff = User::factory()->create(['role' => 'beautician', 'is_active' => true]);
        $customer = Customer::factory()->create();
        $service = $this->createService();
        $package = $this->createPackage($service);
        $customerPackage = $this->createCustomerPackage($customer, $package, $owner);

        $response = $this->actingAs($owner)->post(route('transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'item_type' => 'service',
                    'service_id' => $service->id,
                    'staff_id' => $staff->id,
                    'item_name' => $service->name,
                    'quantity' => 1,
                    'unit_price' => 150000,
                    'discount' => 0,
                ],
                [
                    'item_type' => 'package',
                    'package_id' => $package->id,
                    'staff_id' => $staff->id,
                    'item_name' => $package->name,
                    'quantity' => 1,
                    'unit_price' => 300000,
                    'discount' => 0,
                ],
                [
                    'item_type' => 'customer_package',
                    'customer_package_id' => $customerPackage->id,
                    'staff_id' => $staff->id,
                    'item_name' => $customerPackage->package->name.' (Pakai Sesi)',
                    'quantity' => 1,
                    'unit_price' => 0,
                    'discount' => 0,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transaction = Transaction::query()->latest()->firstOrFail();

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'item_type' => 'service',
            'service_id' => $service->id,
            'staff_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'item_type' => 'package',
            'package_id' => $package->id,
            'staff_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'item_type' => 'customer_package',
            'customer_package_id' => $customerPackage->id,
            'staff_id' => $staff->id,
        ]);
    }

    public function test_transaction_store_allows_non_therapist_items_without_staff(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $customer = Customer::factory()->create();

        $response = $this->actingAs($owner)->post(route('transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [[
                'item_type' => 'other',
                'item_name' => 'Biaya Tambahan',
                'quantity' => 1,
                'unit_price' => 25000,
                'discount' => 0,
            ]],
        ]);

        $response->assertRedirect();

        $transaction = Transaction::query()->latest()->firstOrFail();
        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'item_type' => 'other',
            'staff_id' => null,
        ]);
    }

    public function test_transaction_detail_shows_assigned_therapist_name(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $staff = User::factory()->create(['role' => 'beautician', 'is_active' => true, 'name' => 'Therapist Detail']);
        $customer = Customer::factory()->create();
        $transaction = Transaction::query()->create([
            'customer_id' => $customer->id,
            'cashier_id' => $owner->id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'status' => 'pending',
        ]);

        $transaction->items()->create([
            'item_type' => 'service',
            'item_name' => 'Facial Detail',
            'quantity' => 1,
            'unit_price' => 100000,
            'discount' => 0,
            'total_price' => 100000,
            'staff_id' => $staff->id,
        ]);

        $response = $this->actingAs($owner)->get(route('transactions.show', $transaction));

        $response->assertOk();
        $response->assertSee('Therapist Detail');
    }

    private function createService(array $attributes = []): Service
    {
        $category = ServiceCategory::factory()->create();

        return Service::factory()->create(array_merge([
            'category_id' => $category->id,
            'name' => 'Facial Treatment',
            'price' => 150000,
            'duration_minutes' => 60,
            'is_active' => true,
            'incentive' => 25000,
        ], $attributes));
    }

    private function createPackage(Service $service): Package
    {
        return Package::query()->create([
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
    }

    private function createCustomerPackage(Customer $customer, Package $package, User $seller): CustomerPackage
    {
        return CustomerPackage::query()->create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'sold_by' => $seller->id,
            'price_paid' => $package->package_price,
            'sessions_total' => $package->total_sessions,
            'sessions_used' => 0,
            'purchased_at' => now()->toDateString(),
            'expires_at' => now()->addDays($package->validity_days)->toDateString(),
            'status' => 'active',
            'notes' => 'Test package',
        ]);
    }
}
