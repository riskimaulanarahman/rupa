<?php

namespace Tests\Feature;

use App\Models\Setting;
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
