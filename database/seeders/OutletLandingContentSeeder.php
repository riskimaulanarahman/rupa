<?php

namespace Database\Seeders;

use App\Models\OutletLandingContent;
use Database\Seeders\Concerns\ResolvesDemoTenantOutlet;
use Illuminate\Database\Seeder;

class OutletLandingContentSeeder extends Seeder
{
    use ResolvesDemoTenantOutlet;

    public function run(): void
    {
        [, $outlet] = $this->ensureDemoContextBound();

        $defaults = [
            'hero_badge' => ['section' => 'hero', 'value' => 'Outlet Resmi', 'type' => 'text'],
            'hero_title' => ['section' => 'hero', 'value' => $outlet->name, 'type' => 'text'],
            'hero_subtitle' => ['section' => 'hero', 'value' => 'Selamat datang di halaman resmi outlet kami.', 'type' => 'text'],
            'hero_image' => ['section' => 'hero', 'value' => '', 'type' => 'image'],
            'section_services_title' => ['section' => 'services', 'value' => 'Layanan & Harga', 'type' => 'text'],
            'section_gallery_title' => ['section' => 'gallery', 'value' => 'Galeri Outlet', 'type' => 'text'],
            'section_about_title' => ['section' => 'about', 'value' => 'Tentang Outlet', 'type' => 'text'],
            'about_text' => ['section' => 'about', 'value' => 'Kami menyediakan layanan perawatan terbaik untuk pelanggan Anda.', 'type' => 'text'],
            'section_features_title' => ['section' => 'features', 'value' => 'Kenapa Memilih Kami', 'type' => 'text'],
            'feature_1_title' => ['section' => 'features', 'value' => 'Terapis Berpengalaman', 'type' => 'text'],
            'feature_1_desc' => ['section' => 'features', 'value' => 'Tim profesional dengan standar layanan tinggi.', 'type' => 'text'],
            'feature_2_title' => ['section' => 'features', 'value' => 'Produk Berkualitas', 'type' => 'text'],
            'feature_2_desc' => ['section' => 'features', 'value' => 'Menggunakan produk pilihan yang aman dan terpercaya.', 'type' => 'text'],
            'feature_3_title' => ['section' => 'features', 'value' => 'Tempat Nyaman', 'type' => 'text'],
            'feature_3_desc' => ['section' => 'features', 'value' => 'Suasana outlet bersih, nyaman, dan private.', 'type' => 'text'],
            'section_testimonials_title' => ['section' => 'testimonials', 'value' => 'Testimoni Pelanggan', 'type' => 'text'],
            'testimonials_json' => [
                'section' => 'testimonials',
                'value' => json_encode([
                    [
                        'name' => 'Alya Putri',
                        'role' => 'Pelanggan Reguler',
                        'quote' => 'Pelayanan sangat detail dan hasilnya memuaskan. Saya selalu kembali ke outlet ini.',
                    ],
                    [
                        'name' => 'Rina Kusuma',
                        'role' => 'Karyawan Swasta',
                        'quote' => 'Tempatnya nyaman, staff ramah, dan harga sebanding dengan kualitas treatment.',
                    ],
                    [
                        'name' => 'Dewi Lestari',
                        'role' => 'Ibu Rumah Tangga',
                        'quote' => 'Booking mudah, jadwal tepat waktu, dan hasil perawatan terasa sejak kunjungan pertama.',
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'type' => 'json',
            ],
            'section_faq_title' => ['section' => 'faq', 'value' => 'Pertanyaan Umum', 'type' => 'text'],
            'faqs_json' => [
                'section' => 'faq',
                'value' => json_encode([
                    [
                        'question' => 'Apakah perlu reservasi sebelum datang?',
                        'answer' => 'Disarankan reservasi agar jadwal treatment sesuai waktu yang Anda inginkan.',
                    ],
                    [
                        'question' => 'Metode pembayaran apa saja yang tersedia?',
                        'answer' => 'Kami menerima tunai, transfer bank, kartu debit, dan QRIS.',
                    ],
                    [
                        'question' => 'Apakah bisa konsultasi sebelum treatment?',
                        'answer' => 'Bisa. Tim kami akan membantu rekomendasi treatment sesuai kebutuhan Anda.',
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'type' => 'json',
            ],
            'contact_phone' => ['section' => 'contact', 'value' => $outlet->phone ?? '', 'type' => 'text'],
            'contact_email' => ['section' => 'contact', 'value' => $outlet->email ?? '', 'type' => 'text'],
            'contact_address' => ['section' => 'contact', 'value' => $outlet->address ?? '', 'type' => 'text'],
            'booking_button_label' => ['section' => 'hero', 'value' => 'Booking Online', 'type' => 'text'],
            'section_cta_title' => ['section' => 'cta', 'value' => 'Siap Reservasi Treatment Anda?', 'type' => 'text'],
            'section_cta_subtitle' => ['section' => 'cta', 'value' => 'Pilih jadwal terbaik Anda dan booking online sekarang.', 'type' => 'text'],
            'gallery_images' => ['section' => 'gallery', 'value' => json_encode([]), 'type' => 'json'],
        ];

        foreach ($defaults as $key => $content) {
            OutletLandingContent::query()->updateOrCreate(
                ['outlet_id' => $outlet->id, 'key' => $key],
                [
                    'section' => $content['section'],
                    'value' => $content['value'],
                    'type' => $content['type'],
                ]
            );
        }
    }
}
