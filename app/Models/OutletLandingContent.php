<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class OutletLandingContent extends Model
{
    protected $fillable = [
        'outlet_id',
        'section',
        'key',
        'value',
        'type',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function getBySection(int $outletId, string $section): Collection
    {
        return self::query()
            ->where('outlet_id', $outletId)
            ->where('section', $section)
            ->orderBy('key')
            ->get();
    }

    public static function getValue(int $outletId, string $key, mixed $default = null): mixed
    {
        $content = self::query()
            ->where('outlet_id', $outletId)
            ->where('key', $key)
            ->first();

        if (! $content) {
            return $default;
        }

        if ($content->type === 'json') {
            $decoded = json_decode((string) $content->value, true);

            return is_array($decoded) ? $decoded : $default;
        }

        return $content->value ?? $default;
    }
}
