<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransactionInvoiceNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_invoice_number_ignores_tenant_and_outlet_scopes(): void
    {
        [$tenant, $outletA, $outletB] = $this->createTenantWithTwoOutlets();
        $date = '2026-04-26 14:33:15';

        app()->instance('tenant_id', $tenant->id);
        app()->instance('outlet_id', $outletA->id);

        $customer = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletB->id,
        ]);
        $cashier = User::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletB->id,
        ]);

        Transaction::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outletB->id,
            'invoice_number' => 'INV202604260001',
            'customer_id' => $customer->id,
            'cashier_id' => $cashier->id,
            'subtotal' => 10000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 10000,
            'status' => 'pending',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $this->assertSame('INV202604260002', Transaction::generateInvoiceNumber($date));
    }

    public function test_generate_invoice_number_counts_soft_deleted_transactions(): void
    {
        $date = '2026-04-26 14:33:15';
        $customer = Customer::factory()->create();
        $cashier = User::factory()->create();

        $transaction = Transaction::create([
            'invoice_number' => 'INV202604260001',
            'customer_id' => $customer->id,
            'cashier_id' => $cashier->id,
            'subtotal' => 10000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 10000,
            'status' => 'pending',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $transaction->delete();

        $this->assertSame('INV202604260002', Transaction::generateInvoiceNumber($date));
    }

    public function test_transaction_insert_retries_when_invoice_number_collides(): void
    {
        $date = '2026-04-26 14:33:15';
        $customer = Customer::factory()->create();
        $cashier = User::factory()->create();

        Transaction::create([
            'invoice_number' => 'INV202604260001',
            'customer_id' => $customer->id,
            'cashier_id' => $cashier->id,
            'subtotal' => 10000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 10000,
            'status' => 'pending',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $transaction = new Transaction([
            'invoice_number' => 'INV202604260001',
            'customer_id' => $customer->id,
            'cashier_id' => $cashier->id,
            'subtotal' => 15000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 15000,
            'status' => 'pending',
        ]);
        $transaction->created_at = $date;
        $transaction->updated_at = $date;

        $transaction->save();

        $this->assertSame('INV202604260002', $transaction->invoice_number);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'invoice_number' => 'INV202604260002',
        ]);
    }

    /**
     * @return array{0: Tenant, 1: Outlet, 2: Outlet}
     */
    private function createTenantWithTwoOutlets(): array
    {
        $suffix = Str::lower(Str::random(8));

        $plan = Plan::query()->create([
            'name' => 'Plan-'.$suffix,
            'slug' => 'plan-'.$suffix,
            'price_monthly' => 100000,
            'price_yearly' => 1000000,
            'max_outlets' => 5,
            'trial_days' => 14,
            'sort_order' => 1,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Tenant '.$suffix,
            'slug' => 'tenant-'.$suffix,
            'plan_id' => $plan->id,
            'owner_name' => 'Owner '.$suffix,
            'owner_email' => "owner-{$suffix}@example.com",
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
            'is_read_only' => false,
        ]);

        $outletA = $tenant->outlets()->create([
            'name' => 'Outlet A '.$suffix,
            'slug' => 'outlet-a-'.$suffix,
            'full_subdomain' => "outlet-a-{$suffix}.rupa.test",
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Outlet A',
            'city' => 'Makassar',
            'phone' => '081234567890',
            'email' => "outlet-a-{$suffix}@example.com",
        ]);

        $outletB = $tenant->outlets()->create([
            'name' => 'Outlet B '.$suffix,
            'slug' => 'outlet-b-'.$suffix,
            'full_subdomain' => "outlet-b-{$suffix}.rupa.test",
            'business_type' => 'clinic',
            'status' => 'active',
            'address' => 'Jl. Outlet B',
            'city' => 'Makassar',
            'phone' => '081234567891',
            'email' => "outlet-b-{$suffix}@example.com",
        ]);

        return [$tenant, $outletA, $outletB];
    }
}
