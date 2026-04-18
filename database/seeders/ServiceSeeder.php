<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Seeders\Concerns\ResolvesDemoTenantOutlet;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    use ResolvesDemoTenantOutlet;

    public function run(): void
    {
        [$tenant, $outlet] = $this->ensureDemoContextBound();

        $facial = ServiceCategory::where('name', 'Facial')->first();
        $body = ServiceCategory::where('name', 'Body Treatment')->first();
        $laser = ServiceCategory::where('name', 'Laser & Light')->first();

        $services = [
            // Facial
            [
                'category_id' => $facial?->id,
                'name' => 'Facial Brightening',
                'description' => 'Treatment untuk mencerahkan wajah dengan bahan-bahan alami',
                'duration_minutes' => 60,
                'price' => 250000,
                'incentive' => 25000,
            ],
            [
                'category_id' => $facial?->id,
                'name' => 'Facial Acne Treatment',
                'description' => 'Treatment khusus untuk kulit berjerawat',
                'duration_minutes' => 90,
                'price' => 350000,
                'incentive' => 35000,
            ],
            [
                'category_id' => $facial?->id,
                'name' => 'Facial Anti Aging',
                'description' => 'Treatment anti penuaan dengan serum premium',
                'duration_minutes' => 75,
                'price' => 400000,
                'incentive' => 40000,
            ],
            [
                'category_id' => $facial?->id,
                'name' => 'Facial Deep Cleansing',
                'description' => 'Pembersihan mendalam untuk pori-pori',
                'duration_minutes' => 60,
                'price' => 200000,
                'incentive' => 20000,
            ],
            // Body
            [
                'category_id' => $body?->id,
                'name' => 'Body Scrub',
                'description' => 'Eksfoliasi seluruh tubuh dengan scrub alami',
                'duration_minutes' => 60,
                'price' => 300000,
                'incentive' => 30000,
            ],
            [
                'category_id' => $body?->id,
                'name' => 'Body Massage',
                'description' => 'Pijat relaksasi seluruh tubuh',
                'duration_minutes' => 90,
                'price' => 400000,
                'incentive' => 40000,
            ],
            [
                'category_id' => $body?->id,
                'name' => 'Body Whitening',
                'description' => 'Treatment mencerahkan kulit tubuh',
                'duration_minutes' => 75,
                'price' => 350000,
                'incentive' => 35000,
            ],
            // Laser
            [
                'category_id' => $laser?->id,
                'name' => 'Laser Toning',
                'description' => 'Laser untuk mencerahkan dan meratakan warna kulit',
                'duration_minutes' => 45,
                'price' => 500000,
                'incentive' => 50000,
            ],
            [
                'category_id' => $laser?->id,
                'name' => 'Laser Hair Removal',
                'description' => 'Penghilangan bulu permanen dengan laser',
                'duration_minutes' => 60,
                'price' => 600000,
                'incentive' => 60000,
            ],
        ];

        foreach ($services as $service) {
            if (! $service['category_id']) {
                continue;
            }

            Service::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'outlet_id' => $outlet->id,
                    'name' => $service['name'],
                    'category_id' => $service['category_id'],
                ],
                array_merge($service, [
                    'tenant_id' => $tenant->id,
                    'outlet_id' => $outlet->id,
                ])
            );
        }
    }
}
