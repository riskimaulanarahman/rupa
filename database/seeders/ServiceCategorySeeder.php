<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Facial', 'icon' => '💆', 'sort_order' => 1],
            ['name' => 'Body Treatment', 'icon' => '🧴', 'sort_order' => 2],
            ['name' => 'Laser & Light', 'icon' => '✨', 'sort_order' => 3],
            ['name' => 'Injection', 'icon' => '💉', 'sort_order' => 4],
            ['name' => 'Hair & Scalp', 'icon' => '💇', 'sort_order' => 5],
            ['name' => 'Nail & Lash', 'icon' => '💅', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            ServiceCategory::create($category);
        }
    }
}
