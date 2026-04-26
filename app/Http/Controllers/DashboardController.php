<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\BeauticianDashboardService;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly BeauticianDashboardService $beauticianDashboardService) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->isBeautician()) {
            $period = (string) $request->string('period', 'bulan_ini');
            $dashboard = $this->beauticianDashboardService->build(
                $user,
                $period,
                $request->string('start_date')->toString(),
                $request->string('end_date')->toString(),
            );

            return view('dashboard.beautician', [
                'dashboard' => $dashboard,
                'availablePeriods' => [
                    'hari_ini' => 'Hari Ini',
                    'minggu_ini' => 'Minggu Ini',
                    'bulan_ini' => 'Bulan Ini',
                    'tahun_ini' => 'Tahun Ini',
                    'custom' => 'Custom',
                ],
            ]);
        }

        $stats = $this->getStats();
        $revenueChart = $this->getRevenueChart(7);
        $paymentMethodRevenueByDay = $this->getPaymentMethodRevenueByDay(7);
        $popularServices = $this->getPopularServices(5);
        $todayAppointments = $this->getTodayAppointments();
        $recentTransactions = $this->getRecentTransactions(5);

        return view('dashboard.index', compact(
            'stats',
            'revenueChart',
            'paymentMethodRevenueByDay',
            'popularServices',
            'todayAppointments',
            'recentTransactions'
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function getStats(): array
    {
        $today = now()->toDateString();
        $weekAgo = now()->subDays(7)->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        return [
            'revenue_today' => Transaction::today()->paid()->sum('total_amount'),
            'revenue_month' => Transaction::whereDate('created_at', '>=', $monthStart)->paid()->sum('total_amount'),
            'appointments_today' => Appointment::whereDate('appointment_date', $today)->count(),
            'pending_appointments' => Appointment::whereDate('appointment_date', $today)
                ->where('status', 'pending')->count(),
            'new_customers_week' => Customer::where('created_at', '>=', $weekAgo)->count(),
            'total_customers' => Customer::count(),
            'completed_today' => Appointment::whereDate('appointment_date', $today)
                ->where('status', 'completed')->count(),
            'transactions_today' => Transaction::today()->count(),
            'transactions_pending' => Transaction::today()->where('status', 'pending')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getRevenueChart(int $days): array
    {
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            $data[] = (float) Transaction::whereDate('created_at', $date->toDateString())
                ->paid()
                ->sum('total_amount');
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * @return array{
     *     methods: array<int, array{key: string, label: string}>,
     *     rows: array<int, array<string, mixed>>,
     *     has_data: bool
     * }
     */
    private function getPaymentMethodRevenueByDay(int $days): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();
        $methodLabels = Transaction::PAYMENT_METHODS;

        $paymentsByDate = Payment::query()
            ->join('transactions', 'payments.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'paid')
            ->whereBetween('payments.paid_at', [$startDate, $endDate])
            ->selectRaw('DATE(payments.paid_at) as paid_date, payments.payment_method, SUM(payments.amount) as total')
            ->groupBy(DB::raw('DATE(payments.paid_at)'), 'payments.payment_method')
            ->get()
            ->groupBy('paid_date');

        $rows = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->toDateString();
            $row = [
                'date' => $dateKey,
                'label' => $date->format('d M'),
                'total' => 0.0,
                'total_harian' => 0.0,
            ];

            foreach (array_keys($methodLabels) as $methodKey) {
                $row[$methodKey] = 0.0;
            }

            foreach ($paymentsByDate->get($dateKey, collect()) as $payment) {
                $methodKey = (string) $payment->payment_method;
                $total = (float) $payment->total;

                if (! array_key_exists($methodKey, $methodLabels)) {
                    continue;
                }

                $row[$methodKey] = $total;
                $row['total'] += $total;
                $row['total_harian'] += $total;
            }

            $rows[] = $row;
        }

        return [
            'methods' => collect($methodLabels)
                ->map(fn (string $label, string $key): array => ['key' => $key, 'label' => $label])
                ->values()
                ->all(),
            'rows' => $rows,
            'has_data' => collect($rows)->contains(fn (array $row): bool => (float) $row['total'] > 0),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Transaction>
     */
    private function getRecentTransactions(int $limit)
    {
        return Transaction::with(['customer'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Service>
     */
    private function getPopularServices(int $limit)
    {
        return Service::query()
            ->withCount('appointments')
            ->orderByDesc('appointments_count')
            ->take($limit)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Appointment>
     */
    private function getTodayAppointments()
    {
        return Appointment::with(['customer', 'service', 'staff'])
            ->whereDate('appointment_date', today())
            ->orderBy('start_time')
            ->get();
    }
}
