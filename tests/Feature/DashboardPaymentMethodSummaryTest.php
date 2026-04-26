<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardPaymentMethodSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-04-26 10:00:00');

        Setting::set('setup_completed', true, 'boolean');
        Setting::set('business_type', 'clinic', 'string');
        Setting::set('business_name', 'Test Clinic', 'string');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_exposes_payment_method_revenue_breakdown_for_last_7_days(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_view_revenue' => false,
        ]);
        $customer = Customer::factory()->create();

        $this->createTransactionWithPayments($owner, $customer, 'paid', [
            ['method' => 'cash', 'amount' => 100000, 'paid_at' => '2026-04-24 09:00:00'],
            ['method' => 'qris', 'amount' => 50000, 'paid_at' => '2026-04-24 09:05:00'],
        ]);

        $this->createTransactionWithPayments($owner, $customer, 'paid', [
            ['method' => 'transfer', 'amount' => 200000, 'paid_at' => '2026-04-22 14:00:00'],
        ]);

        $this->createTransactionWithPayments($owner, $customer, 'partial', [
            ['method' => 'debit_card', 'amount' => 75000, 'paid_at' => '2026-04-24 10:00:00'],
        ], totalAmount: 150000);

        $this->createTransactionWithPayments($owner, $customer, 'paid', [
            ['method' => 'credit_card', 'amount' => 300000, 'paid_at' => '2026-04-18 12:00:00'],
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee(__('dashboard.payment_methods_7_days'))
            ->assertViewHas('paymentMethodRevenueByDay', function (array $summary): bool {
                if ($summary['has_data'] !== true) {
                    return false;
                }

                if (count($summary['rows']) !== 7) {
                    return false;
                }

                $expectedMethods = array_map(
                    fn (string $key, string $label): array => ['key' => $key, 'label' => $label],
                    array_keys(Transaction::PAYMENT_METHODS),
                    array_values(Transaction::PAYMENT_METHODS),
                );

                if ($summary['methods'] !== $expectedMethods) {
                    return false;
                }

                $targetRow = collect($summary['rows'])->firstWhere('date', '2026-04-24');
                $transferRow = collect($summary['rows'])->firstWhere('date', '2026-04-22');
                $emptyRow = collect($summary['rows'])->firstWhere('date', '2026-04-26');

                if (! is_array($targetRow) || ! is_array($transferRow) || ! is_array($emptyRow)) {
                    return false;
                }

                return $targetRow['cash'] === 100000.0
                    && $targetRow['qris'] === 50000.0
                    && $targetRow['debit_card'] === 0.0
                    && $targetRow['total'] === 150000.0
                    && $targetRow['total_harian'] === 150000.0
                    && $transferRow['transfer'] === 200000.0
                    && $transferRow['total'] === 200000.0
                    && $emptyRow['total'] === 0.0
                    && $emptyRow['cash'] === 0.0
                    && $emptyRow['qris'] === 0.0;
            });
    }

    public function test_dashboard_shows_empty_state_when_no_payment_method_revenue_exists(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'can_view_revenue' => false,
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee(__('dashboard.no_payment_method_revenue'))
            ->assertViewHas('paymentMethodRevenueByDay', function (array $summary): bool {
                return $summary['has_data'] === false
                    && count($summary['rows']) === 7
                    && collect($summary['rows'])->every(fn (array $row): bool => $row['total'] === 0.0);
            });
    }

    /**
     * @param  array<int, array{method: string, amount: int|float, paid_at: string}>  $payments
     */
    private function createTransactionWithPayments(
        User $cashier,
        Customer $customer,
        string $status,
        array $payments,
        ?float $totalAmount = null,
    ): Transaction {
        $paidAmount = array_sum(array_column($payments, 'amount'));
        $totalAmount ??= $paidAmount;

        $transaction = Transaction::query()->create([
            'customer_id' => $customer->id,
            'cashier_id' => $cashier->id,
            'subtotal' => $totalAmount,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'change_amount' => max(0, $paidAmount - $totalAmount),
            'status' => $status,
            'paid_at' => $status === 'paid'
                ? Carbon::parse($payments[array_key_last($payments)]['paid_at'])
                : null,
        ]);

        foreach ($payments as $payment) {
            $transaction->payments()->create([
                'received_by' => $cashier->id,
                'payment_method' => $payment['method'],
                'amount' => $payment['amount'],
                'paid_at' => Carbon::parse($payment['paid_at']),
            ]);
        }

        return $transaction;
    }
}
