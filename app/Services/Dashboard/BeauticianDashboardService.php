<?php

namespace App\Services\Dashboard;

use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BeauticianDashboardService
{
    /**
     * @return array{
     *     summary: array{total_service_items: int, unique_services: int, total_incentive_paid: int},
     *     services: array<int, array{service_id: int|null, service_name: string, count: int, incentive_total: int}>,
     *     filters: array{period: string, start_date: string, end_date: string}
     * }
     */
    public function build(
        User $user,
        string $period = 'bulan_ini',
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        [$start, $end] = $this->resolvePeriod($period, $startDate, $endDate);

        $items = TransactionItem::query()
            ->with([
                'service:id,name,incentive',
                'package.service:id,name,incentive',
                'customerPackage.package.service:id,name,incentive',
            ])
            ->where('staff_id', $user->id)
            ->whereIn('item_type', ['service', 'package', 'customer_package'])
            ->whereHas('transaction', function ($query) use ($start, $end) {
                $query->paid()->whereBetween('paid_at', [$start, $end]);
            })
            ->get();

        $services = $this->aggregateServices($items);

        return [
            'summary' => [
                'total_service_items' => array_sum(array_column($services, 'count')),
                'unique_services' => count($services),
                'total_incentive_paid' => array_sum(array_column($services, 'incentive_total')),
            ],
            'services' => $services,
            'filters' => [
                'period' => $period,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function resolvePeriod(
        string $period = 'bulan_ini',
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        return match ($period) {
            'hari_ini' => [now()->startOfDay(), now()->endOfDay()],
            'minggu_ini' => [now()->startOfWeek(), now()->endOfWeek()],
            'tahun_ini' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                Carbon::parse($startDate ?: now()->startOfMonth()->toDateString())->startOfDay(),
                Carbon::parse($endDate ?: now()->toDateString())->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    /**
     * @param  Collection<int, TransactionItem>  $items
     * @return array<int, array{service_id: int|null, service_name: string, count: int, incentive_total: int}>
     */
    private function aggregateServices(Collection $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $resolvedService = $item->service
                ?? $item->package?->service
                ?? $item->customerPackage?->package?->service;

            $serviceId = $resolvedService?->id;
            $serviceName = $resolvedService?->name ?: ($item->item_name ?: 'Layanan');
            $key = $serviceId !== null ? 'service:'.$serviceId : 'name:'.mb_strtolower($serviceName);
            $quantity = max(1, (int) $item->quantity);
            $baseIncentive = (float) ($resolvedService?->incentive ?? 0);
            $incentiveTotal = (int) round($baseIncentive * $quantity);

            if (! array_key_exists($key, $grouped)) {
                $grouped[$key] = [
                    'service_id' => $serviceId,
                    'service_name' => $serviceName,
                    'count' => 0,
                    'incentive_total' => 0,
                ];
            }

            $grouped[$key]['count'] += $quantity;
            $grouped[$key]['incentive_total'] += $incentiveTotal;
        }

        $services = array_values($grouped);
        usort($services, static fn (array $a, array $b) => [$b['incentive_total'], $b['count'], $a['service_name']] <=> [$a['incentive_total'], $a['count'], $b['service_name']]);

        return $services;
    }
}
