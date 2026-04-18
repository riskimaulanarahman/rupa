<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\OutletInvoice;
use App\Models\Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'trial_tenants' => Tenant::where('status', 'trial')->count(),
            'total_outlets' => Outlet::count(),
            'mrr' => $this->calculateMRR(),
        ];

        $recentTenants = Tenant::with('plan')->latest()->take(5)->get();
        $recentInvoices = OutletInvoice::with(['tenant', 'plan'])->latest()->take(5)->get();

        return view('platform.dashboard', compact('stats', 'recentTenants', 'recentInvoices'));
    }

    private function calculateMRR()
    {
        // Monthly Recurring Revenue calculation
        // Sum of price_monthly for all active tenants
        return Tenant::where('status', 'active')
            ->join('plans', 'tenants.plan_id', '=', 'plans.id')
            ->sum('plans.price_monthly');
    }
}
