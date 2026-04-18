<?php

namespace App\Support\Permissions;

class ModulePermissionRegistry
{
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
}
