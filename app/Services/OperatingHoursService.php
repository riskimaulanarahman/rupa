<?php

namespace App\Services;

use App\Models\OperatingHour;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OperatingHoursService
{
    /**
     * @var array<int, array<string, int|string|bool|null>>
     */
    private const DEFAULT_WEEKLY_SCHEDULE = [
        ['day_of_week' => 0, 'open_time' => null, 'close_time' => null, 'is_closed' => true],
        ['day_of_week' => 1, 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
        ['day_of_week' => 2, 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
        ['day_of_week' => 3, 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
        ['day_of_week' => 4, 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
        ['day_of_week' => 5, 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
        ['day_of_week' => 6, 'open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => false],
    ];

    public function hasCurrentOutletContext(): bool
    {
        return tenant_id() !== null && outlet_id() !== null;
    }

    public function ensureWeeklyScheduleForCurrentOutlet(): Collection
    {
        [$tenantId, $outletId] = $this->requireCurrentContext();

        return $this->ensureWeeklyScheduleForContext($tenantId, $outletId);
    }

    public function getWeeklyScheduleForCurrentOutlet(): Collection
    {
        [$tenantId, $outletId] = $this->requireCurrentContext();

        return $this->getWeeklyScheduleForContext($tenantId, $outletId);
    }

    /**
     * @param  array<int, array<string, mixed>>  $hours
     */
    public function upsertWeeklyScheduleForCurrentOutlet(array $hours): Collection
    {
        [$tenantId, $outletId] = $this->requireCurrentContext();

        return $this->upsertWeeklyScheduleForContext($tenantId, $outletId, $hours);
    }

    public function ensureWeeklyScheduleForContext(int $tenantId, int $outletId): Collection
    {
        foreach (self::DEFAULT_WEEKLY_SCHEDULE as $defaultHour) {
            OperatingHour::withoutGlobalScopes()->firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'outlet_id' => $outletId,
                    'day_of_week' => $defaultHour['day_of_week'],
                ],
                [
                    'open_time' => $defaultHour['open_time'],
                    'close_time' => $defaultHour['close_time'],
                    'is_closed' => $defaultHour['is_closed'],
                ]
            );
        }

        return $this->fetchWeeklyScheduleForContext($tenantId, $outletId);
    }

    public function getWeeklyScheduleForContext(int $tenantId, int $outletId): Collection
    {
        $this->ensureWeeklyScheduleForContext($tenantId, $outletId);

        return $this->fetchWeeklyScheduleForContext($tenantId, $outletId);
    }

    /**
     * @param  array<int, array<string, mixed>>  $hours
     */
    public function upsertWeeklyScheduleForContext(int $tenantId, int $outletId, array $hours): Collection
    {
        DB::transaction(function () use ($tenantId, $outletId, $hours) {
            foreach ($hours as $hourData) {
                $isClosed = (bool) ($hourData['is_closed'] ?? false);

                OperatingHour::withoutGlobalScopes()->updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'outlet_id' => $outletId,
                        'day_of_week' => (int) $hourData['day_of_week'],
                    ],
                    [
                        'open_time' => $isClosed ? null : ($hourData['open_time'] ?? null),
                        'close_time' => $isClosed ? null : ($hourData['close_time'] ?? null),
                        'is_closed' => $isClosed,
                    ]
                );
            }
        });

        return $this->getWeeklyScheduleForContext($tenantId, $outletId);
    }

    /**
     * @return array{int, int}
     */
    private function requireCurrentContext(): array
    {
        $tenantId = tenant_id();
        $outletId = outlet_id();

        if ($tenantId === null || $outletId === null) {
            throw new RuntimeException('Konteks outlet wajib tersedia untuk jam operasional.');
        }

        return [$tenantId, $outletId];
    }

    private function fetchWeeklyScheduleForContext(int $tenantId, int $outletId): Collection
    {
        return OperatingHour::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->orderBy('day_of_week')
            ->get();
    }
}
