<?php

namespace App\Services;

use App\Models\OutletInvoice;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Check all tenants for expired trials or subscriptions.
     */
    public function updateSubscriptionStatuses(): void
    {
        Tenant::where('status', 'trial')
            ->where('trial_ends_at', '<', now())
            ->update(['status' => 'expired']);

        Tenant::where('status', 'active')
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Generate invoices for active tenants.
     */
    public function processBilling(): void
    {
        $activeTenants = Tenant::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('subscription_ends_at')
                    ->orWhere('subscription_ends_at', '<=', now()->addDays(2));
            })
            ->with('plan')
            ->get();

        foreach ($activeTenants as $tenant) {
            $this->generateInvoiceForTenant($tenant);
        }
    }

    /**
     * Generate an invoice for a specific tenant based on their active outlets.
     */
    public function generateInvoiceForTenant(Tenant $tenant): ?OutletInvoice
    {
        $plan = $tenant->plan;
        if (! $plan) {
            return null;
        }

        $billingPeriod = now()->format('Y-m');
        $activeOutletCount = $tenant->activeOutletCount();
        $amount = (int) $plan->price_monthly;

        return DB::transaction(function () use ($tenant, $plan, $billingPeriod, $activeOutletCount, $amount) {
            $existingInvoice = OutletInvoice::query()
                ->where('tenant_id', $tenant->id)
                ->where('billing_period', $billingPeriod)
                ->first();

            if ($existingInvoice) {
                return $existingInvoice;
            }

            return OutletInvoice::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'billing_period' => $billingPeriod,
                'outlet_count' => $activeOutletCount,
                'plan_price' => (int) $plan->price_monthly,
                'total_amount' => $amount,
                'status' => 'pending',
                'due_date' => now()->addDays(7),
                'notes' => 'Auto-generated monthly billing.',
            ]);
        });
    }

    /**
     * Mark an invoice as paid and update tenant subscription status.
     */
    public function recordPayment(OutletInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice->refresh();

            if ($invoice->status !== 'paid') {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            $tenant = $invoice->tenant;
            if (! $tenant) {
                return;
            }

            $currentEnd = $tenant->subscription_ends_at;
            $subscriptionEndsAt = now()->endOfMonth();

            if ($invoice->billing_period) {
                $periodDate = Carbon::createFromFormat('Y-m', $invoice->billing_period)->startOfMonth();
                $subscriptionEndsAt = $periodDate->copy()->endOfMonth();
            }

            if ($currentEnd && $currentEnd->isFuture()) {
                $subscriptionEndsAt = $currentEnd->copy()->addMonth();
            }

            $tenant->update([
                'plan_id' => $invoice->plan_id,
                'status' => 'active',
                'subscription_ends_at' => $subscriptionEndsAt,
                'is_read_only' => false,
            ]);
        });
    }
}
