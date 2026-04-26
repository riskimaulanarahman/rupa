<?php

namespace App\Support\Permissions;

class ModulePermissionRegistry
{
    /**
     * @var array<string, array<int, string>>
     */
    private const HARD_LOCKED_ALLOWED_MODULES_BY_ROLE = [
        'admin' => [
            'dashboard',
            'appointments',
            'customers',
            'transactions',
        ],
    ];

    /**
     * @return array<int, string>
     */
    public function managedRoles(): array
    {
        return ['owner', 'admin', 'beautician'];
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    public function modules(): array
    {
        return [
            ['key' => 'dashboard', 'label' => 'Dashboard'],
            ['key' => 'appointments', 'label' => 'Jadwal'],
            ['key' => 'customers', 'label' => 'Pelanggan'],
            ['key' => 'treatment_records', 'label' => 'Treatment Records'],
            ['key' => 'service_categories', 'label' => 'Kategori Layanan'],
            ['key' => 'services', 'label' => 'Layanan'],
            ['key' => 'products', 'label' => 'Produk'],
            ['key' => 'packages', 'label' => 'Paket'],
            ['key' => 'customer_packages', 'label' => 'Paket Pelanggan'],
            ['key' => 'transactions', 'label' => 'Transaksi / Checkout'],
            ['key' => 'loyalty', 'label' => 'Loyalty'],
            ['key' => 'reports', 'label' => 'Laporan'],
            ['key' => 'outlets', 'label' => 'Outlets'],
            ['key' => 'import_data', 'label' => 'Import Data'],
            ['key' => 'staff', 'label' => 'Staff'],
            ['key' => 'settings', 'label' => 'Pengaturan'],
            ['key' => 'billing', 'label' => 'Billing'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function moduleKeys(): array
    {
        return array_column($this->modules(), 'key');
    }

    public function isKnownModule(string $moduleKey): bool
    {
        return in_array($moduleKey, $this->moduleKeys(), true);
    }

    public function isManagedRole(string $role): bool
    {
        return in_array($role, $this->managedRoles(), true);
    }

    /**
     * @return array<int, string>|null
     */
    public function hardLockedAllowedModulesForRole(string $role): ?array
    {
        return self::HARD_LOCKED_ALLOWED_MODULES_BY_ROLE[$role] ?? null;
    }

    public function roleHasHardLockedModules(string $role): bool
    {
        return $this->hardLockedAllowedModulesForRole($role) !== null;
    }

    public function isModuleAssignableToRole(string $role, string $moduleKey): bool
    {
        $allowedModules = $this->hardLockedAllowedModulesForRole($role);
        if ($allowedModules === null) {
            return true;
        }

        return in_array($moduleKey, $allowedModules, true);
    }

    public function isRoleModuleLocked(string $role, string $moduleKey): bool
    {
        return $this->roleHasHardLockedModules($role)
            && ! $this->isModuleAssignableToRole($role, $moduleKey);
    }

    /**
     * @param  array<string, array<string, mixed>>  $permissions
     * @return array<string, array<string, bool>>
     */
    public function normalizePermissionMatrix(array $permissions): array
    {
        $normalized = [];

        foreach ($this->managedRoles() as $role) {
            foreach ($this->moduleKeys() as $moduleKey) {
                $requested = (bool) data_get($permissions, "{$role}.{$moduleKey}", false);
                $normalized[$role][$moduleKey] = $this->isModuleAssignableToRole($role, $moduleKey)
                    ? $requested
                    : false;
            }
        }

        return $normalized;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function lockedPermissionMatrix(): array
    {
        $matrix = [];

        foreach ($this->managedRoles() as $role) {
            foreach ($this->moduleKeys() as $moduleKey) {
                $matrix[$role][$moduleKey] = $this->isRoleModuleLocked($role, $moduleKey);
            }
        }

        return $matrix;
    }
}
