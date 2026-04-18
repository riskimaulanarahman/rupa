<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allFeatures = [
            'Semua Fitur Standard & Pro',
            'Dashboard Admin & Owner HQ',
            'Aplikasi Smartphone & Tablet',
            'Booking Online Website',
            'Sistem Loyalitas & Poin',
            'Manajemen Paket & Member',
            'Catatan Rekam Medik Klien',
            'Multi-user & Staff Tanpa Batas',
            'Laporan Analitik Lengkap',
            'Manajemen Layanan & Produk',
        ];

        \App\Models\Plan::updateOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Solusi lengkap untuk pengelolaan 1 outlet cabang.',
                'price_monthly' => 100000,
                'price_yearly' => 1000000,
                'max_outlets' => 1,
                'trial_days' => 14,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'features' => array_merge(['1 Cabang/Outlet'], $allFeatures),
            ]
        );

        \App\Models\Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'description' => 'Pilihan terbaik untuk bisnis dengan hingga 5 cabang.',
                'price_monthly' => 400000,
                'price_yearly' => 4000000,
                'max_outlets' => 5,
                'trial_days' => 14,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'features' => array_merge(['Hingga 5 Cabang/Outlet'], $allFeatures),
            ]
        );

        \App\Models\Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'Solusi tanpa batas untuk jaringan bisnis skala besar.',
                'price_monthly' => 800000,
                'price_yearly' => 8000000,
                'max_outlets' => null,
                'trial_days' => 14,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'features' => array_merge(['Unlimited Cabang/Outlet'], $allFeatures),
            ]
        );
    }
}
