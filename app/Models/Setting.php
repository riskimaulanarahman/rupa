<?php

namespace App\Models;

use App\Traits\BelongsToOutlet;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use BelongsToOutlet, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'key',
        'value',
        'type',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $tenantId = tenant_id() ?? 0;
        $outletId = outlet_id() ?? 0;
        $cacheKey = "setting.{$tenantId}.{$outletId}.{$key}";

        $setting = Cache::remember($cacheKey, 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (! $setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        $storedValue = self::prepareStoredValue($value, $type);

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'type' => $type]
        );

        $tenantId = tenant_id() ?? 0;
        $outletId = outlet_id() ?? 0;
        Cache::forget("setting.{$tenantId}.{$outletId}.{$key}");
    }

    public static function getGlobal(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting.global.{$key}";

        $setting = Cache::remember($cacheKey, 3600, function () use ($key) {
            return static::query()
                ->withoutGlobalScopes()
                ->where('key', $key)
                ->whereNull('tenant_id')
                ->whereNull('outlet_id')
                ->first();
        });

        if (! $setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    public static function setGlobal(string $key, mixed $value, string $type = 'string'): void
    {
        $storedValue = self::prepareStoredValue($value, $type);

        static::query()
            ->withoutGlobalScopes()
            ->updateOrCreate(
                [
                    'key' => $key,
                    'tenant_id' => null,
                    'outlet_id' => null,
                ],
                ['value' => $storedValue, 'type' => $type]
            );

        self::forgetGlobal($key);
    }

    public static function forgetGlobal(string $key): void
    {
        Cache::forget("setting.global.{$key}");
    }

    private static function prepareStoredValue(mixed $value, string $type): string
    {
        return match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    private static function castValue(?string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode((string) $value, true),
            default => $value,
        };
    }
}
