<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Service;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $facialBrightening = Service::where('name', 'Facial Brightening')->first();
        $facialAcne = Service::where('name', 'Facial Acne Treatment')->first();
        $facialAntiAging = Service::where('name', 'Facial Anti Aging')->first();
        $laserToning = Service::where('name', 'Laser Toning')->first();
        $bodyMassage = Service::where('name', 'Body Massage')->first();

        $packages = [
            [
                'name' => 'Paket Glowing 5 Sesi',
                'description' => 'Paket perawatan wajah untuk hasil glowing maksimal. Termasuk 5x Facial Brightening dengan harga hemat.',
                'service_id' => $facialBrightening?->id,
                'total_sessions' => 5,
                'original_price' => 1250000, // 5 x 250.000
                'package_price' => 1000000,  // Diskon 20%
                'validity_days' => 90,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Paket Glowing 10 Sesi',
                'description' => 'Paket lengkap perawatan wajah untuk hasil glowing sempurna. Termasuk 10x Facial Brightening dengan diskon spesial.',
                'service_id' => $facialBrightening?->id,
                'total_sessions' => 10,
                'original_price' => 2500000, // 10 x 250.000
                'package_price' => 1800000,  // Diskon 28%
                'validity_days' => 180,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Paket Acne Fighter 6 Sesi',
                'description' => 'Solusi lengkap untuk kulit berjerawat. 6x treatment acne dengan harga terjangkau.',
                'service_id' => $facialAcne?->id,
                'total_sessions' => 6,
                'original_price' => 2100000, // 6 x 350.000
                'package_price' => 1680000,  // Diskon 20%
                'validity_days' => 120,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Paket Anti Aging Premium',
                'description' => 'Paket premium untuk perawatan anti penuaan. 8x treatment dengan serum terbaik.',
                'service_id' => $facialAntiAging?->id,
                'total_sessions' => 8,
                'original_price' => 3200000, // 8 x 400.000
                'package_price' => 2400000,  // Diskon 25%
                'validity_days' => 180,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Paket Laser Glow 4 Sesi',
                'description' => 'Paket laser toning untuk kulit cerah merata. 4 sesi dengan hasil maksimal.',
                'service_id' => $laserToning?->id,
                'total_sessions' => 4,
                'original_price' => 2000000, // 4 x 500.000
                'package_price' => 1600000,  // Diskon 20%
                'validity_days' => 120,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Paket Relaxation 5 Sesi',
                'description' => 'Paket pijat relaksasi untuk tubuh segar dan bugar. Cocok untuk yang sibuk.',
                'service_id' => $bodyMassage?->id,
                'total_sessions' => 5,
                'original_price' => 2000000, // 5 x 400.000
                'package_price' => 1500000,  // Diskon 25%
                'validity_days' => 90,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}
