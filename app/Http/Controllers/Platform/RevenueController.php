<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\PlatformRevenueService;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function index(Request $request, PlatformRevenueService $revenueService)
    {
        $allowedPeriods = ['this_month', 'last_month', 'this_quarter', 'this_year', 'all_time'];
        $allowedStatuses = ['all', 'active', 'trial', 'suspended', 'expired', 'cancelled'];

        $period = $request->string('period')->toString() ?: 'this_month';
        $status = $request->string('status')->toString() ?: 'all';

        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'this_month';
        }

        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        $result = $revenueService->build($period, $status);
        $tenants = $result['tenants'];
        $totals = $result['totals'];
        $startDate = $result['startDate'];
        $endDate = $result['endDate'];

        $periods = [
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'this_quarter' => 'Kuartal Ini',
            'this_year' => 'Tahun Ini',
            'all_time' => 'Semua Waktu',
        ];

        $statuses = [
            'all' => 'Semua Status',
            'active' => 'Aktif',
            'trial' => 'Trial',
            'suspended' => 'Ditangguhkan',
            'expired' => 'Expired',
            'cancelled' => 'Dibatalkan',
        ];

        return view('platform.revenue.index', compact(
            'tenants',
            'totals',
            'period',
            'periods',
            'status',
            'statuses',
            'startDate',
            'endDate'
        ));
    }
}
