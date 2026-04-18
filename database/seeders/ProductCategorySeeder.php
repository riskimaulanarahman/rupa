<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Database\Seeders\Concerns\ResolvesDemoTenantOutlet;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    use ResolvesDemoTenantOutlet;

    public function run(): void
    {
        [$tenant, $outlet] = $this->ensureDemoContextBound();

        $categories = [
            [
                'name' => 'Skincare',
                'description' => 'Produk perawatan kulit wajah',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Body Care',
                'description' => 'Produk perawatan tubuh',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Makeup',
                'description' => 'Produk makeup dan kosmetik',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Suplemen',
                'description' => 'Suplemen kecantikan dan kesehatan',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'outlet_id' => $outlet->id,
                    'name' => $category['name'],
                ],
                array_merge($category, [
                    'tenant_id' => $tenant->id,
                    'outlet_id' => $outlet->id,
                ])
            );
        }
    }
}
