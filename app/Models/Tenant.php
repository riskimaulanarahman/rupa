<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'plan_id',
        'owner_name',
        'owner_email',
        'status',
        'trial_ends_at',
        'subscription_ends_at',
        'is_read_only',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'is_read_only' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function activeOutlets(): HasMany
    {
        return $this->hasMany(Outlet::class)->where(function ($query) {
            $query->where('status', '=', 'active');
        });
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(OutletInvoice::class);
    }

    public function activeOutletCount(): int
    {
        return $this->activeOutlets()->count();
    }

    public function currentOutletCountForPlan(): int
    {
        return $this->outlets()->count();
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active']);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isReadOnly(): bool
    {
        return $this->is_read_only;
    }

    public function getRemainingDays(): int
    {
        $endDate = $this->status === 'trial'
            ? $this->trial_ends_at
            : $this->subscription_ends_at;

        if (! $endDate) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($endDate, false));
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->isActive() && $this->getRemainingDays() <= $days;
    }

    public function canAddOutlet(): bool
    {
        if (! $this->plan) {
            return false;
        }

        return $this->plan->canAddOutlet($this->currentOutletCountForPlan());
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'trial' => 'Trial',
            'active' => 'Aktif',
            'suspended' => 'Ditangguhkan',
            'expired' => 'Expired',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'trial' => 'bg-blue-100 text-blue-700',
            'active' => 'bg-green-100 text-green-700',
            'suspended' => 'bg-yellow-100 text-yellow-700',
            'expired', 'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
