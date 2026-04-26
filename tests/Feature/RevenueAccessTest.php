<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');
        Setting::set('business_name', 'Test Clinic', 'string');
    }

    public function test_owner_can_access_revenue_modules_on_web(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_view_revenue' => false,
        ]);

        $this->actingAs($owner)->get(route('dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('reports.index'))->assertOk();
    }

    public function test_admin_with_revenue_access_can_access_dashboard_but_not_reports_on_web(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'can_view_revenue' => true,
        ]);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('reports.index'))->assertForbidden();
    }

    public function test_admin_without_revenue_access_is_forbidden_from_revenue_modules_on_web(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'can_view_revenue' => false,
        ]);

        $this->actingAs($admin)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('reports.index'))->assertForbidden();
    }

    public function test_admin_without_revenue_access_can_still_access_operational_modules_on_web(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'can_view_revenue' => false,
        ]);

        $this->actingAs($admin)->get(route('appointments.index'))->assertOk();
        $this->actingAs($admin)->get(route('transactions.index'))
            ->assertOk()
            ->assertDontSee(__('transaction.today'))
            ->assertDontSee(__('transaction.revenue'));
    }

    public function test_owner_and_allowed_admin_can_see_transaction_summary_cards(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_view_revenue' => false,
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'can_view_revenue' => true,
        ]);

        $this->actingAs($owner)->get(route('transactions.index'))
            ->assertOk()
            ->assertSee(__('transaction.today'))
            ->assertSee(__('transaction.paid'))
            ->assertSee(__('transaction.revenue'));

        $this->actingAs($admin)->get(route('transactions.index'))
            ->assertOk()
            ->assertSee(__('transaction.today'))
            ->assertSee(__('transaction.paid'))
            ->assertSee(__('transaction.revenue'));
    }

    public function test_owner_and_allowed_admin_can_access_expected_revenue_modules_on_api(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_view_revenue' => false,
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'can_view_revenue' => true,
        ]);

        $this->actingAs($owner, 'sanctum')->getJson('/api/v1/dashboard')->assertOk();
        $this->actingAs($owner, 'sanctum')->getJson('/api/v1/reports')->assertOk();

        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/dashboard')->assertOk();
        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/reports')->assertStatus(403);
    }

    public function test_admin_without_revenue_access_is_forbidden_from_revenue_modules_on_api(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'can_view_revenue' => false,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Anda tidak memiliki akses ke modul ini.')
            ->assertJsonPath('module', 'dashboard');

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/reports')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Anda tidak memiliki akses ke modul ini.')
            ->assertJsonPath('module', 'reports');
    }

    public function test_owner_can_set_admin_revenue_access_when_creating_staff(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->post(route('staff.store'), [
            'name' => 'Admin No Revenue',
            'email' => 'admin-norevenue@example.com',
            'phone' => '08123456789',
            'role' => 'admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => '1',
            'can_view_revenue' => '0',
        ]);

        $response->assertRedirect(route('staff.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'admin-norevenue@example.com',
            'role' => 'admin',
            'can_view_revenue' => false,
        ]);
    }

    public function test_non_admin_role_is_forced_to_no_revenue_access_on_staff_create_and_update(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->post(route('staff.store'), [
            'name' => 'Beautician One',
            'email' => 'beautician@example.com',
            'phone' => '0811111111',
            'role' => 'beautician',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => '1',
            'can_view_revenue' => '1',
        ])->assertRedirect(route('staff.index'));

        $staff = User::where('email', 'beautician@example.com')->firstOrFail();
        $this->assertFalse((bool) $staff->can_view_revenue);

        $this->actingAs($owner)->put(route('staff.update', $staff), [
            'name' => 'Admin Two',
            'email' => 'beautician@example.com',
            'phone' => '0811111111',
            'role' => 'admin',
            'is_active' => '1',
            'can_view_revenue' => '1',
        ])->assertRedirect(route('staff.index'));

        $staff->refresh();
        $this->assertTrue((bool) $staff->can_view_revenue);

        $this->actingAs($owner)->put(route('staff.update', $staff), [
            'name' => 'Owner Two',
            'email' => 'beautician@example.com',
            'phone' => '0811111111',
            'role' => 'owner',
            'is_active' => '1',
            'can_view_revenue' => '1',
        ])->assertRedirect(route('staff.index'));

        $staff->refresh();
        $this->assertFalse((bool) $staff->can_view_revenue);
    }

    public function test_login_redirects_admin_without_revenue_access_to_appointments(): void
    {
        $admin = User::factory()->create([
            'email' => 'restricted-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'can_view_revenue' => false,
            'is_active' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('appointments.index'));
    }

    public function test_beautician_is_redirected_to_dashboard_and_blocked_from_non_dashboard_modules(): void
    {
        $beautician = User::factory()->create([
            'email' => 'beautician-login@example.com',
            'password' => bcrypt('password'),
            'role' => 'beautician',
            'can_view_revenue' => false,
            'is_active' => true,
        ]);

        $this->post(route('login'), [
            'email' => $beautician->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($beautician)->get(route('dashboard'))->assertOk();
        $this->actingAs($beautician)->get(route('appointments.index'))->assertForbidden();
        $this->actingAs($beautician)->get(route('customers.index'))->assertForbidden();
        $this->actingAs($beautician)->get(route('transactions.index'))->assertForbidden();
        $this->actingAs($beautician)->get(route('reports.index'))->assertForbidden();

        $this->actingAs($beautician, 'sanctum')->getJson('/api/v1/dashboard/self')->assertOk();
        $this->actingAs($beautician, 'sanctum')->getJson('/api/v1/appointments')
            ->assertStatus(403)
            ->assertJsonPath('module', 'appointments');
        $this->actingAs($beautician, 'sanctum')->getJson('/api/v1/customers')
            ->assertStatus(403)
            ->assertJsonPath('module', 'customers');
        $this->actingAs($beautician, 'sanctum')->getJson('/api/v1/transactions')
            ->assertStatus(403)
            ->assertJsonPath('module', 'transactions');
        $this->actingAs($beautician, 'sanctum')->getJson('/api/v1/reports')
            ->assertStatus(403)
            ->assertJsonPath('module', 'reports');
    }

    public function test_beautician_self_dashboard_aggregates_paid_service_and_package_incentives(): void
    {
        $beautician = User::factory()->create([
            'role' => 'beautician',
            'can_view_revenue' => false,
            'is_active' => true,
        ]);
        $customer = Customer::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Facial Glow',
            'incentive' => 25000,
        ]);
        $package = Package::query()->create([
            'name' => 'Facial Package',
            'description' => 'Package',
            'service_id' => $service->id,
            'total_sessions' => 3,
            'original_price' => 450000,
            'package_price' => 400000,
            'validity_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $customerPackage = CustomerPackage::query()->create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'sold_by' => $beautician->id,
            'price_paid' => 400000,
            'sessions_total' => 3,
            'sessions_used' => 0,
            'purchased_at' => now()->toDateString(),
            'expires_at' => now()->addDays(30)->toDateString(),
            'status' => 'active',
        ]);

        $paidTransaction = Transaction::query()->create([
            'invoice_number' => 'INV-BEAUTICIAN-001',
            'customer_id' => $customer->id,
            'cashier_id' => $beautician->id,
            'subtotal' => 500000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 500000,
            'paid_amount' => 500000,
            'change_amount' => 0,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        TransactionItem::query()->create([
            'transaction_id' => $paidTransaction->id,
            'item_type' => 'service',
            'service_id' => $service->id,
            'staff_id' => $beautician->id,
            'item_name' => $service->name,
            'quantity' => 1,
            'unit_price' => 150000,
            'discount' => 0,
            'total_price' => 150000,
        ]);

        TransactionItem::query()->create([
            'transaction_id' => $paidTransaction->id,
            'item_type' => 'package',
            'package_id' => $package->id,
            'staff_id' => $beautician->id,
            'item_name' => $package->name,
            'quantity' => 1,
            'unit_price' => 150000,
            'discount' => 0,
            'total_price' => 150000,
        ]);

        TransactionItem::query()->create([
            'transaction_id' => $paidTransaction->id,
            'item_type' => 'customer_package',
            'customer_package_id' => $customerPackage->id,
            'staff_id' => $beautician->id,
            'item_name' => 'Pemakaian Facial Package',
            'quantity' => 2,
            'unit_price' => 100000,
            'discount' => 0,
            'total_price' => 200000,
        ]);

        $unpaidTransaction = Transaction::query()->create([
            'invoice_number' => 'INV-BEAUTICIAN-002',
            'customer_id' => $customer->id,
            'cashier_id' => $beautician->id,
            'subtotal' => 150000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 150000,
            'paid_amount' => 0,
            'change_amount' => 0,
            'status' => 'pending',
        ]);

        TransactionItem::query()->create([
            'transaction_id' => $unpaidTransaction->id,
            'item_type' => 'service',
            'service_id' => $service->id,
            'staff_id' => $beautician->id,
            'item_name' => $service->name,
            'quantity' => 3,
            'unit_price' => 50000,
            'discount' => 0,
            'total_price' => 150000,
        ]);

        $response = $this->actingAs($beautician, 'sanctum')
            ->getJson('/api/v1/dashboard/self?period=bulan_ini');

        $response->assertOk()
            ->assertJsonPath('data.summary.total_service_items', 4)
            ->assertJsonPath('data.summary.unique_services', 1)
            ->assertJsonPath('data.summary.total_incentive_paid', 100000)
            ->assertJsonPath('data.services.0.service_name', 'Facial Glow')
            ->assertJsonPath('data.services.0.count', 4)
            ->assertJsonPath('data.services.0.incentive_total', 100000);
    }

    public function test_user_resource_returns_can_view_revenue(): void
    {
        $admin = User::factory()->create([
            'email' => 'api-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'can_view_revenue' => false,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.can_view_revenue', false);
    }
}
