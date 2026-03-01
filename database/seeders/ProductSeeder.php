<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $skincare = ProductCategory::where('name', 'Skincare')->first();
        $bodycare = ProductCategory::where('name', 'Body Care')->first();
        $makeup = ProductCategory::where('name', 'Makeup')->first();
        $suplemen = ProductCategory::where('name', 'Suplemen')->first();

        $products = [
            // Skincare
            [
                'category_id' => $skincare?->id,
                'name' => 'Brightening Serum',
                'sku' => 'SKC-001',
                'description' => 'Serum pencerah dengan Vitamin C dan Niacinamide',
                'price' => 185000,
                'cost_price' => 85000,
                'stock' => 50,
                'min_stock' => 10,
                'unit' => 'pcs',
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $skincare?->id,
                'name' => 'Acne Spot Treatment',
                'sku' => 'SKC-002',
                'description' => 'Gel untuk mengatasi jerawat dengan Salicylic Acid',
                'price' => 95000,
                'cost_price' => 40000,
                'stock' => 30,
                'min_stock' => 5,
                'unit' => 'pcs',
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $skincare?->id,
                'name' => 'Moisturizer SPF 30',
                'sku' => 'SKC-003',
                'description' => 'Pelembab harian dengan perlindungan UV',
                'price' => 145000,
                'cost_price' => 65000,
                'stock' => 40,
                'min_stock' => 8,
                'unit' => 'pcs',
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $skincare?->id,
                'name' => 'Anti Aging Night Cream',
                'sku' => 'SKC-004',
                'description' => 'Krim malam dengan Retinol untuk anti penuaan',
                'price' => 275000,
                'cost_price' => 120000,
                'stock' => 25,
                'min_stock' => 5,
                'unit' => 'pcs',
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $skincare?->id,
                'name' => 'Facial Wash Gentle',
                'sku' => 'SKC-005',
                'description' => 'Sabun muka lembut untuk semua jenis kulit',
                'price' => 85000,
                'cost_price' => 35000,
                'stock' => 60,
                'min_stock' => 15,
                'unit' => 'pcs',
                'is_active' => true,
                'track_stock' => true,
            ],
            // Body Care
            [
                'category_id' => $bodycare?->id,
                'name' => 'Body Lotion Whitening',
                'sku' => 'BDC-001',
                'description' => 'Lotion pencerah kulit tubuh',
                'price' => 125000,
                'cost_price' => 55000,
                'stock' => 35,
                'min_stock' => 10,
                'unit' => 'pcs',
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $bodycare?->id,
                'name' => 'Body Scrub Coffee',
                'sku' => 'BDC-002',
                'description' => 'Scrub tubuh dengan ekstrak kopi',
                'price' => 95000,
                'cost_price' => 40000,
                'stock' => 20,
                'min_stock' => 5,
                'unit' => 'pcs',
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $bodycare?->id,
                'name' => 'Hand & Body Serum',
                'sku' => 'BDC-003',
                'description' => 'Serum intensif untuk tangan dan tubuh',
                'price' => 165000,
                'cost_price' => 70000,
                'stock' => 25,
                'min_stock' => 5,
                'unit' => 'pcs',
                'is_active' => true,
                'track_stock' => true,
            ],
            // Makeup
            [
                'category_id' => $makeup?->id,
                'name' => 'BB Cream Natural',
                'sku' => 'MKP-001',
                'description' => 'BB Cream dengan coverage natural',
                'price' => 175000,
                'cost_price' => 75000,
                'stock' => 30,
                'min_stock' => 8,
                'unit' => 'pcs',
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $makeup?->id,
                'name' => 'Lip Tint Rose',
                'sku' => 'MKP-002',
                'description' => 'Lip tint dengan warna natural',
                'price' => 85000,
                'cost_price' => 35000,
                'stock' => 40,
                'min_stock' => 10,
                'unit' => 'pcs',
                'is_active' => true,
                'track_stock' => true,
            ],
            // Suplemen
            [
                'category_id' => $suplemen?->id,
                'name' => 'Collagen Drink',
                'sku' => 'SPL-001',
                'description' => 'Minuman kolagen untuk kulit kenyal (1 box isi 14)',
                'price' => 350000,
                'cost_price' => 180000,
                'stock' => 20,
                'min_stock' => 5,
                'unit' => 'box',
                'is_active' => true,
                'track_stock' => true,
            ],
            [
                'category_id' => $suplemen?->id,
                'name' => 'Vitamin E Capsule',
                'sku' => 'SPL-002',
                'description' => 'Kapsul Vitamin E untuk kulit sehat (60 kapsul)',
                'price' => 195000,
                'cost_price' => 90000,
                'stock' => 25,
                'min_stock' => 5,
                'unit' => 'botol',
                'is_active' => true,
                'track_stock' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
