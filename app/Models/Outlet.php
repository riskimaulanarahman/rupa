<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Outlet extends Model
{
    /** @use HasFactory<\Database\Factories\OutletFactory> */
    use HasFactory, SoftDeletes;

    public const RESERVED_SLUGS = [
        'api',
        'appointments',
        'billing',
        'booking',
        'customers',
        'dashboard',
        'hq',
        'imports',
        'landing',
        'login',
        'logout',
        'platform',
        'portal',
        'register',
        'reports',
        'services',
        'settings',
        'setup',
        'staff',
        'transactions',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'full_subdomain',
        'custom_domain',
        'business_type',
        'status',
        'address',
        'city',
        'phone',
        'email',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function landingContents(): HasMany
    {
        return $this->hasMany(OutletLandingContent::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the full business configuration for this outlet's business type.
     *
     * @return array<string, mixed>
     */
    public function getBusinessConfig(?string $key = null, mixed $default = null): mixed
    {
        $config = config("business.types.{$this->business_type}", []);

        if ($key === null) {
            return $config;
        }

        return data_get($config, $key, $default);
    }

    /**
     * Check if a feature is enabled for this outlet's business type.
     */
    public function hasFeature(string $feature): bool
    {
        return (bool) $this->getBusinessConfig("features.{$feature}", false);
    }

    public function getBusinessTypeLabelAttribute(): string
    {
        return match ($this->business_type) {
            'clinic' => 'Klinik Kecantikan',
            'salon' => 'Salon',
            'barbershop' => 'Barbershop',
            default => ucfirst($this->business_type),
        };
    }

    public function getBusinessTypeIconAttribute(): string
    {
        return match ($this->business_type) {
            'clinic' => 'sparkles',
            'salon' => 'scissors',
            'barbershop' => 'scissors',
            default => 'building-storefront',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Aktif' : 'Nonaktif';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public static function isReservedSlug(string $slug): bool
    {
        return in_array(Str::lower($slug), self::RESERVED_SLUGS, true);
    }

    public static function generateUniquePublicSlug(string $value, ?int $ignoreOutletId = null): string
    {
        $slug = Str::slug($value);
        if ($slug === '') {
            $slug = 'outlet';
        }

        if (self::isReservedSlug($slug)) {
            $slug .= '-outlet';
        }

        $baseSlug = $slug;
        $counter = 2;

        while (
            self::isReservedSlug($slug)
            || self::query()
                ->when($ignoreOutletId, fn ($query) => $query->whereKeyNot($ignoreOutletId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
