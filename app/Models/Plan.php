<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /** @use HasFactory<\Database\Factories\PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'max_outlets',
        'trial_days',
        'is_active',
        'is_featured',
        'sort_order',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'integer',
            'price_yearly' => 'integer',
            'max_outlets' => 'integer',
            'trial_days' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'features' => 'array',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function hasUnlimitedOutlets(): bool
    {
        return $this->max_outlets === null;
    }

    public function canAddOutlet(int $currentOutletCount): bool
    {
        if ($this->hasUnlimitedOutlets()) {
            return true;
        }

        return $currentOutletCount < $this->max_outlets;
    }

    public function getFormattedMonthlyPriceAttribute(): string
    {
        return 'Rp '.number_format($this->price_monthly, 0, ',', '.');
    }

    public function getFormattedYearlyPriceAttribute(): string
    {
        return 'Rp '.number_format($this->price_yearly, 0, ',', '.');
    }

    public function getYearlySavingsAttribute(): int
    {
        return ($this->price_monthly * 12) - $this->price_yearly;
    }
}
