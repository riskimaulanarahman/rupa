<?php

namespace App\Models;

use App\Traits\BelongsToOutlet;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use BelongsToOutlet, BelongsToTenant, HasFactory, SoftDeletes;

    public const PRICING_MODE_FIXED = 'fixed';

    public const PRICING_MODE_RANGE = 'range';

    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'category_id',
        'name',
        'description',
        'duration_minutes',
        'pricing_mode',
        'price',
        'price_min',
        'price_max',
        'incentive',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'pricing_mode' => 'string',
            'price' => 'decimal:2',
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
            'incentive' => 'decimal:2',
            'duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $service): void {
            $pricingMode = $service->pricing_mode ?: self::PRICING_MODE_FIXED;
            $service->pricing_mode = $pricingMode;

            if ($pricingMode === self::PRICING_MODE_RANGE) {
                $priceMin = $service->price_min ?? $service->price ?? 0;
                $priceMax = $service->price_max ?? $priceMin;

                $service->price_min = $priceMin;
                $service->price_max = $priceMax;
                $service->price = $priceMin;

                return;
            }

            $price = $service->price ?? $service->price_min ?? 0;
            $service->price = $price;
            $service->price_min = $price;
            $service->price_max = $price;
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->has_price_range) {
            return $this->formatCurrency($this->price_min).' - '.$this->formatCurrency($this->price_max);
        }

        return $this->formatCurrency($this->price);
    }

    public function getFormattedIncentiveAttribute(): string
    {
        return $this->formatCurrency($this->incentive ?? 0);
    }

    public function getFormattedDurationAttribute(): string
    {
        return $this->duration_minutes.' min';
    }

    public function getHasPriceRangeAttribute(): bool
    {
        return $this->pricing_mode === self::PRICING_MODE_RANGE
            && $this->price_min !== null
            && $this->price_max !== null
            && (float) $this->price_max > (float) $this->price_min;
    }

    protected function formatCurrency(float|int|string|null $amount): string
    {
        return 'Rp '.number_format((float) ($amount ?? 0), 0, ',', '.');
    }
}
