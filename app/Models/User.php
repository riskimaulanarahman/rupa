<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Permissions\ModulePermissionResolver;
use App\Traits\BelongsToOutlet;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use BelongsToOutlet, BelongsToTenant, HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
        'can_view_revenue',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'can_view_revenue' => 'boolean',
        ];
    }

    public const ROLES = [
        'superadmin' => 'Super Admin',
        'owner' => 'Owner',
        'admin' => 'Admin',
        'beautician' => 'Beautician',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBeautician(): bool
    {
        return $this->role === 'beautician';
    }

    public function canViewRevenue(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isOwner()) {
            return true;
        }

        if ($this->isAdmin()) {
            return (bool) ($this->can_view_revenue ?? true);
        }

        return false;
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }

        return in_array($this->role, $roles);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeStaff($query)
    {
        return $query->whereIn('role', ['admin', 'beautician']);
    }

    public function scopeBeauticians($query)
    {
        return $query->where('role', 'beautician');
    }

    public function canAccessModule(string $moduleKey): bool
    {
        return app(ModulePermissionResolver::class)->canAccessModuleForUser($this, $moduleKey);
    }

    /**
     * @return array<string, bool>
     */
    public function moduleAccess(): array
    {
        return app(ModulePermissionResolver::class)->moduleAccessForUser($this);
    }
}
