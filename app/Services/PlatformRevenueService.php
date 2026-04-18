<?php

namespace App\Services;

use App\Models\OutletInvoice;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlatformRevenueService
{
    public function build(string $period = 'this_month', string $status = 'all'): array
    {
        [$startDate, $endDate] = $this->getPeriodDates($period);

        $tenants = Tenant::with(['plan'])
            ->withCount('outlets')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->get();

        if ($tenants->isEmpty()) {
            return [
                'tenants' => collect(),
                'totals' => [
                    'gmv_paid' => 0,
                    'transactions' => 0,
                    'active_tenants_with_transactions' => 0,
                    'subscription_revenue_paid' => 0,
                    'mrr_snapshot' => 0,
                    'outlets' => 0,
                    'tenants' => 0,
                ],
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];
        }

        $tenantIds = $tenants->pluck('id');

        $transactionAgg = DB::table('transactions')
            ->select(
                'tenant_id',
                DB::raw('SUM(total_amount) as gmv_paid'),
                DB::raw('COUNT(*) as transactions_count')
            )
            ->whereIn('tenant_id', $tenantIds)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('tenant_id')
            ->get()
            ->keyBy('tenant_id');

        $subscriptionPaidAgg = DB::table('outlet_invoices')
            ->select('tenant_id', DB::raw('SUM(total_amount) as subscription_revenue_paid'))
            ->whereIn('tenant_id', $tenantIds)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->groupBy('tenant_id')
            ->get()
            ->keyBy('tenant_id');

        $invoicePeriodAgg = DB::table('outlet_invoices')
            ->select('tenant_id', DB::raw('SUM(total_amount) as invoice_period_total'))
            ->whereIn('tenant_id', $tenantIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('tenant_id')
            ->get()
            ->keyBy('tenant_id');

        $latestInvoiceStatus = OutletInvoice::query()
            ->whereIn('tenant_id', $tenantIds)
            ->orderByDesc('created_at')
            ->get(['tenant_id', 'status', 'created_at'])
            ->unique('tenant_id')
            ->keyBy('tenant_id');

        $tenantRows = $tenants->map(function ($tenant) use (
            $transactionAgg,
            $subscriptionPaidAgg,
            $invoicePeriodAgg,
            $latestInvoiceStatus
        ) {
            $trx = $transactionAgg->get($tenant->id);
            $sub = $subscriptionPaidAgg->get($tenant->id);
            $inv = $invoicePeriodAgg->get($tenant->id);
            $latest = $latestInvoiceStatus->get($tenant->id);

            $gmvPaid = (int) round((float) ($trx->gmv_paid ?? 0));
            $transactions = (int) ($trx->transactions_count ?? 0);
            $subscriptionPaid = (int) round((float) ($sub->subscription_revenue_paid ?? 0));
            $invoicePeriodTotal = (int) round((float) ($inv->invoice_period_total ?? 0));

            return (object) [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'status' => $tenant->status,
                'plan_name' => $tenant->plan?->name ?? '-',
                'plan_price_monthly' => (int) ($tenant->plan?->price_monthly ?? 0),
                'outlets_count' => (int) $tenant->outlets_count,
                'gmv_paid' => $gmvPaid,
                'transactions_count' => $transactions,
                'subscription_revenue_paid' => $subscriptionPaid,
                'invoice_period_total' => $invoicePeriodTotal,
                'latest_invoice_status' => $latest->status ?? '-',
                'total_revenue' => $gmvPaid + $subscriptionPaid,
            ];
        })->sortByDesc('total_revenue')->values();

        return [
            'tenants' => $tenantRows,
            'totals' => $this->calculateTotals($tenantRows),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    private function calculateTotals(Collection $tenantRows): array
    {
        return [
            'gmv_paid' => (int) $tenantRows->sum('gmv_paid'),
            'transactions' => (int) $tenantRows->sum('transactions_count'),
            'active_tenants_with_transactions' => (int) $tenantRows
                ->where('status', 'active')
                ->where('transactions_count', '>', 0)
                ->count(),
            'subscription_revenue_paid' => (int) $tenantRows->sum('subscription_revenue_paid'),
            'mrr_snapshot' => (int) $tenantRows
                ->where('status', 'active')
                ->sum(fn ($row) => (int) $row->plan_price_monthly),
            'outlets' => (int) $tenantRows->sum('outlets_count'),
            'tenants' => (int) $tenantRows->count(),
        ];
    }

    private function getPeriodDates(string $period): array
    {
        return match ($period) {
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'all_time' => [now()->subYears(10), now()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
