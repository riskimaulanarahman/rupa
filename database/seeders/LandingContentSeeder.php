<?php

namespace Database\Seeders;

use App\Models\LandingContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class LandingContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idTranslations = require lang_path('id/landing.php');
        $enTranslations = require lang_path('en/landing.php');

        $definitions = [
            ['key' => 'hero_badge', 'section' => 'Hero', 'description' => 'Badge kecil di atas judul utama hero.'],
            ['key' => 'hero_title', 'section' => 'Hero', 'description' => 'Judul utama di halaman depan.'],
            ['key' => 'hero_subtitle', 'section' => 'Hero', 'description' => 'Sub-judul di bawah judul utama hero.'],
            ['key' => 'features_badge', 'section' => 'Features', 'description' => 'Badge di atas judul seksi fitur.'],
            ['key' => 'features_title', 'section' => 'Features', 'description' => 'Judul utama seksi fitur.'],
            ['key' => 'features_subtitle', 'section' => 'Features', 'description' => 'Sub-judul seksi fitur.'],
            ['key' => 'mobile_tablet_desc', 'section' => 'Mobile Apps', 'description' => 'Deskripsi singkat untuk tablet showcase.'],
            ['key' => 'solutions_subtitle', 'section' => 'Solutions', 'description' => 'Sub-judul seksi solusi.'],
            ['key' => 'solution_dashboard_feature2', 'section' => 'Solutions', 'description' => 'Poin fitur dashboard nomor 2.'],
            ['key' => 'testimonials_title', 'section' => 'Testimonials', 'description' => 'Judul seksi testimoni.'],
            ['key' => 'testimonial1_text', 'section' => 'Testimonials', 'description' => 'Isi testimoni pertama.'],
            ['key' => 'testimonial1_clinic', 'section' => 'Testimonials', 'description' => 'Sumber testimoni pertama.'],
            ['key' => 'testimonial2_text', 'section' => 'Testimonials', 'description' => 'Isi testimoni kedua.'],
            ['key' => 'testimonial2_clinic', 'section' => 'Testimonials', 'description' => 'Sumber testimoni kedua.'],
            ['key' => 'testimonial3_text', 'section' => 'Testimonials', 'description' => 'Isi testimoni ketiga.'],
            ['key' => 'testimonial3_clinic', 'section' => 'Testimonials', 'description' => 'Sumber testimoni ketiga.'],
            ['key' => 'footer_desc', 'section' => 'Footer', 'description' => 'Deskripsi singkat pada footer landing page.'],
        ];

        foreach ($definitions as $definition) {
            $key = $definition['key'];

            $idValue = Arr::get($idTranslations, $key, '');
            $enValue = Arr::get($enTranslations, $key, '');

            LandingContent::updateOrCreate(
                ['key' => $key],
                [
                    'section' => $definition['section'],
                    'description' => $definition['description'],
                    'content' => [
                        'id' => is_string($idValue) && $idValue !== '' ? $idValue : $key,
                        'en' => is_string($enValue) && $enValue !== '' ? $enValue : $key,
                    ],
                ]
            );
        }
    }
}
