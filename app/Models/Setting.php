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

        try {
            $setting = self::cacheStore()->remember($cacheKey, 3600, function () use ($key) {
                return static::where('key', $key)->first();
            });
        } catch (\Throwable) {
            $setting = static::where('key', $key)->first();
        }

        if (! $setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    public static function getForCurrentContext(string $key, mixed $default = null): mixed
    {
        $value = static::get($key);

        if ($value !== null) {
            return $value;
        }

        return static::getGlobal($key, $default);
    }

    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        $storedValue = self::prepareStoredValue($value, $type);

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'type' => $type]
        );

        self::forgetCacheKey("setting.".(tenant_id() ?? 0).'.'.(outlet_id() ?? 0).".{$key}");
    }

    public static function setForCurrentContext(string $key, mixed $value, string $type = 'string'): void
    {
        $storedValue = self::prepareStoredValue($value, $type);
        $tenantId = tenant_id();
        $outletId = outlet_id();

        $existing = static::query()
            ->withoutGlobalScopes()
            ->where('key', $key)
            ->first();

        $oldTenantId = (int) ($existing?->tenant_id ?? 0);
        $oldOutletId = (int) ($existing?->outlet_id ?? 0);

        static::query()
            ->withoutGlobalScopes()
            ->updateOrCreate(
                ['key' => $key],
                [
                    'tenant_id' => $tenantId,
                    'outlet_id' => $outletId,
                    'value' => $storedValue,
                    'type' => $type,
                ]
            );

        self::forgetCacheKey("setting.{$oldTenantId}.{$oldOutletId}.{$key}");
        self::forgetCacheKey('setting.'.($tenantId ?? 0).'.'.($outletId ?? 0).".{$key}");
        static::forgetGlobal($key);
    }

    public static function getGlobal(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting.global.{$key}";

        try {
            $setting = self::cacheStore()->remember($cacheKey, 3600, function () use ($key) {
                return static::query()
                    ->withoutGlobalScopes()
                    ->where('key', $key)
                    ->whereNull('tenant_id')
                    ->whereNull('outlet_id')
                    ->first();
            });
        } catch (\Throwable) {
            $setting = static::query()
                ->withoutGlobalScopes()
                ->where('key', $key)
                ->whereNull('tenant_id')
                ->whereNull('outlet_id')
                ->first();
        }

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
        self::forgetCacheKey("setting.global.{$key}");
    }

    private static function cacheStore(): \Illuminate\Contracts\Cache\Repository
    {
        $defaultStore = (string) config('cache.default', 'file');

        if ($defaultStore !== 'database') {
            return Cache::store($defaultStore);
        }

        return array_key_exists('file', config('cache.stores', []))
            ? Cache::store('file')
            : Cache::store($defaultStore);
    }

    private static function forgetCacheKey(string $key): void
    {
        Cache::forget($key);

        $defaultStore = (string) config('cache.default', 'file');
        if ($defaultStore === 'database' && array_key_exists('file', config('cache.stores', []))) {
            Cache::store('file')->forget($key);
        }
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
