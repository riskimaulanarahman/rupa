<?php

namespace App\Support\Permissions;

use App\Models\ModulePermissionDefault;
use App\Models\Outlet;
use App\Models\OutletRoleModulePermission;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ModulePermissionResolver
{
    /**
     * @var array<string, array<string, bool>>
     */
    private array $defaultRolePermissionCache = [];

    /**
     * @var array<string, array<string, bool>>
     */
    private array $outletRolePermissionCache = [];

    private ?bool $outletPermissionTableExists = null;

    public function __construct(private readonly ModulePermissionRegistry $registry) {}

    public function canAccessModuleForUser(?User $user, string $moduleKey): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $this->registry->isKnownModule($moduleKey)) {
            return false;
        }

        $role = strtolower((string) $user->role);
        $outletId = (int) (outlet_id() ?? $user->outlet_id ?? 0);

        $isAllowed = $this->resolvePermissionValue($role, $moduleKey, $outletId);
        if (! $isAllowed) {
            return false;
        }

        if (in_array($moduleKey, ['dashboard', 'reports'], true) && ! $user->canViewRevenue()) {
            return false;
        }

        return $this->isFeatureAvailable($moduleKey);
    }

    /**
     * @return array<string, bool>
     */
    public function moduleAccessForUser(?User $user): array
    {
        $result = [];
        foreach ($this->registry->moduleKeys() as $moduleKey) {
            $result[$moduleKey] = $this->canAccessModuleForUser($user, $moduleKey);
        }

        return $result;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function defaultPermissionMatrix(): array
    {
        $matrix = [];

        foreach ($this->registry->managedRoles() as $role) {
            $roleDefaults = $this->getDefaultRolePermissions($role);
            foreach ($this->registry->moduleKeys() as $moduleKey) {
                $matrix[$role][$moduleKey] = $roleDefaults[$moduleKey]
                    ?? $this->legacyFallbackByRole($role, $moduleKey);
            }
        }

        return $matrix;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function outletPermissionMatrix(Outlet $outlet): array
    {
        $matrix = [];
        foreach ($this->registry->managedRoles() as $role) {
            $roleOverrides = $this->getOutletRolePermissions((int) $outlet->id, $role);
            $roleDefaults = $this->getDefaultRolePermissions($role);

            foreach ($this->registry->moduleKeys() as $moduleKey) {
                if (array_key_exists($moduleKey, $roleOverrides)) {
                    $matrix[$role][$moduleKey] = $roleOverrides[$moduleKey];
                    continue;
                }

                $matrix[$role][$moduleKey] = $roleDefaults[$moduleKey]
                    ?? $this->legacyFallbackByRole($role, $moduleKey);
            }
        }

        return $matrix;
    }

    private function resolvePermissionValue(string $role, string $moduleKey, int $outletId): bool
    {
        if ($this->registry->isManagedRole($role) && $outletId > 0) {
            $outletPermissions = $this->getOutletRolePermissions($outletId, $role);
            if (array_key_exists($moduleKey, $outletPermissions)) {
                return (bool) $outletPermissions[$moduleKey];
            }
        }

        if ($this->registry->isManagedRole($role)) {
            $defaultPermissions = $this->getDefaultRolePermissions($role);
            if (array_key_exists($moduleKey, $defaultPermissions)) {
                return (bool) $defaultPermissions[$moduleKey];
            }
        }

        return $this->legacyFallbackByRole($role, $moduleKey);
    }

    private function legacyFallbackByRole(string $role, string $moduleKey): bool
    {
        return match ($moduleKey) {
            'dashboard', 'reports' => in_array($role, ['owner', 'admin'], true),
            'settings', 'import_data' => in_array($role, ['owner', 'admin'], true),
            'staff', 'outlets', 'billing' => $role === 'owner',
            default => in_array($role, ['owner', 'admin', 'beautician'], true),
        };
    }

    /**
     * @return array<string, bool>
     */
    private function getDefaultRolePermissions(string $role): array
    {
        if (array_key_exists($role, $this->defaultRolePermissionCache)) {
            return $this->defaultRolePermissionCache[$role];
        }

        $rows = ModulePermissionDefault::query()
            ->where('role', $role)
            ->get(['module_key', 'is_allowed']);

        $permissions = [];
        foreach ($rows as $row) {
            $permissions[$row->module_key] = (bool) $row->is_allowed;
        }

        $this->defaultRolePermissionCache[$role] = $permissions;

        return $permissions;
    }

    /**
     * @return array<string, bool>
     */
    private function getOutletRolePermissions(int $outletId, string $role): array
    {
        $cacheKey = $outletId.':'.$role;
        if (array_key_exists($cacheKey, $this->outletRolePermissionCache)) {
            return $this->outletRolePermissionCache[$cacheKey];
        }

        if (! $this->hasOutletPermissionTable()) {
            $this->outletRolePermissionCache[$cacheKey] = [];

            return [];
        }

        $rows = OutletRoleModulePermission::query()
            ->where('outlet_id', $outletId)
            ->where('role', $role)
            ->get(['module_key', 'is_allowed']);

        $permissions = [];
        foreach ($rows as $row) {
            $permissions[$row->module_key] = (bool) $row->is_allowed;
        }

        $this->outletRolePermissionCache[$cacheKey] = $permissions;

        return $permissions;
    }

    private function hasOutletPermissionTable(): bool
    {
        if ($this->outletPermissionTableExists !== null) {
            return $this->outletPermissionTableExists;
        }

        return $this->outletPermissionTableExists = Schema::hasTable('outlet_role_module_permissions');
    }

    private function isFeatureAvailable(string $moduleKey): bool
    {
        $featureKey = match ($moduleKey) {
            'treatment_records' => 'treatment_records',
            'products' => 'products',
            'packages' => 'packages',
            'customer_packages' => 'customer_packages',
            'loyalty' => 'loyalty',
            default => null,
        };

        if ($featureKey === null) {
            return true;
        }

        if (! function_exists('has_feature')) {
            return true;
        }

        return has_feature($featureKey);
    }
}
