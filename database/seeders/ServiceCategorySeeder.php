<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Database\Seeders\Concerns\ResolvesDemoTenantOutlet;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    use ResolvesDemoTenantOutlet;

    public function run(): void
    {
        [$tenant, $outlet] = $this->ensureDemoContextBound();

        $categories = [
            ['name' => 'Facial', 'icon' => '💆', 'sort_order' => 1],
            ['name' => 'Body Treatment', 'icon' => '🧴', 'sort_order' => 2],
            ['name' => 'Laser & Light', 'icon' => '✨', 'sort_order' => 3],
            ['name' => 'Injection', 'icon' => '💉', 'sort_order' => 4],
            ['name' => 'Hair & Scalp', 'icon' => '💇', 'sort_order' => 5],
            ['name' => 'Nail & Lash', 'icon' => '💅', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            ServiceCategory::updateOrCreate(
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
