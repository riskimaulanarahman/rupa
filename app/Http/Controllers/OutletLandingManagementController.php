<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\OutletLandingContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OutletLandingManagementController extends Controller
{
    public function edit(): View
    {
        $outlet = $this->resolveOutlet();

        $contents = $outlet->landingContents()
            ->orderBy('section')
            ->orderBy('key')
            ->get();

        $contentMap = $contents->pluck('value', 'key');
        $galleryImages = $this->parseGalleryImages($contentMap->get('gallery_images'));
        $testimonials = $this->parseTestimonials($contentMap->get('testimonials_json'));
        $faqs = $this->parseFaqs($contentMap->get('faqs_json'));

        return view('outlet.landing.edit', [
            'outlet' => $outlet,
            'contents' => $contents,
            'contentMap' => $contentMap,
            'galleryImages' => $galleryImages,
            'testimonials' => $testimonials,
            'faqs' => $faqs,
            'bookingUrl' => route('outlet.booking.index', ['outletSlug' => $outlet->slug]),
            'landingUrl' => route('outlet.landing.show', ['outletSlug' => $outlet->slug]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $outlet = $this->resolveOutlet();
        $existingContentMap = $outlet->landingContents()->pluck('value', 'key');

        $validated = $request->validate([
            'hero_badge' => ['nullable', 'string', 'max:120'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:1000'],
            'section_services_title' => ['nullable', 'string', 'max:120'],
            'section_gallery_title' => ['nullable', 'string', 'max:120'],
            'section_about_title' => ['nullable', 'string', 'max:120'],
            'about_text' => ['nullable', 'string'],
            'section_features_title' => ['nullable', 'string', 'max:120'],
            'feature_1_title' => ['nullable', 'string', 'max:120'],
            'feature_1_desc' => ['nullable', 'string', 'max:300'],
            'feature_2_title' => ['nullable', 'string', 'max:120'],
            'feature_2_desc' => ['nullable', 'string', 'max:300'],
            'feature_3_title' => ['nullable', 'string', 'max:120'],
            'feature_3_desc' => ['nullable', 'string', 'max:300'],
            'section_testimonials_title' => ['nullable', 'string', 'max:120'],
            'testimonial_1_name' => ['nullable', 'string', 'max:120'],
            'testimonial_1_role' => ['nullable', 'string', 'max:120'],
            'testimonial_1_quote' => ['nullable', 'string', 'max:400'],
            'testimonial_2_name' => ['nullable', 'string', 'max:120'],
            'testimonial_2_role' => ['nullable', 'string', 'max:120'],
            'testimonial_2_quote' => ['nullable', 'string', 'max:400'],
            'testimonial_3_name' => ['nullable', 'string', 'max:120'],
            'testimonial_3_role' => ['nullable', 'string', 'max:120'],
            'testimonial_3_quote' => ['nullable', 'string', 'max:400'],
            'section_faq_title' => ['nullable', 'string', 'max:120'],
            'faq_1_question' => ['nullable', 'string', 'max:200'],
            'faq_1_answer' => ['nullable', 'string', 'max:500'],
            'faq_2_question' => ['nullable', 'string', 'max:200'],
            'faq_2_answer' => ['nullable', 'string', 'max:500'],
            'faq_3_question' => ['nullable', 'string', 'max:200'],
            'faq_3_answer' => ['nullable', 'string', 'max:500'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:1000'],
            'booking_button_label' => ['nullable', 'string', 'max:100'],
            'section_cta_title' => ['nullable', 'string', 'max:120'],
            'section_cta_subtitle' => ['nullable', 'string', 'max:300'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $heroImage = OutletLandingContent::getValue($outlet->id, 'hero_image');
        if ($request->hasFile('hero_image')) {
            if (is_string($heroImage) && $heroImage !== '') {
                Storage::disk('public')->delete($heroImage);
            }

            $heroImage = $request->file('hero_image')->store("outlet-landing/{$outlet->id}/hero", 'public');
        }

        $galleryImages = OutletLandingContent::getValue($outlet->id, 'gallery_images', []);
        if (! is_array($galleryImages)) {
            $galleryImages = [];
        }

        if ($request->hasFile('gallery_images')) {
            foreach ($galleryImages as $existingImage) {
                if (is_string($existingImage) && $existingImage !== '') {
                    Storage::disk('public')->delete($existingImage);
                }
            }

            $galleryImages = [];
            foreach ((array) $request->file('gallery_images', []) as $file) {
                if (! $file) {
                    continue;
                }

                $galleryImages[] = $file->store("outlet-landing/{$outlet->id}/gallery", 'public');
            }
        }

        $existingTestimonials = $this->parseTestimonials($existingContentMap->get('testimonials_json'));
        $existingFaqs = $this->parseFaqs($existingContentMap->get('faqs_json'));

        $testimonials = $this->buildTestimonialsPayload($request, $validated, $existingTestimonials);
        $faqs = $this->buildFaqPayload($request, $validated, $existingFaqs);

        $this->upsertContent($outlet, [
            'hero_badge' => ['section' => 'hero', 'value' => $validated['hero_badge'] ?? 'Outlet Resmi', 'type' => 'text'],
            'hero_title' => ['section' => 'hero', 'value' => $validated['hero_title'] ?? $outlet->name, 'type' => 'text'],
            'hero_subtitle' => ['section' => 'hero', 'value' => $validated['hero_subtitle'] ?? '', 'type' => 'text'],
            'hero_image' => ['section' => 'hero', 'value' => is_string($heroImage) ? $heroImage : '', 'type' => 'image'],
            'section_services_title' => ['section' => 'services', 'value' => $validated['section_services_title'] ?? 'Layanan & Harga', 'type' => 'text'],
            'section_gallery_title' => ['section' => 'gallery', 'value' => $validated['section_gallery_title'] ?? 'Galeri Outlet', 'type' => 'text'],
            'section_about_title' => ['section' => 'about', 'value' => $validated['section_about_title'] ?? 'Tentang Outlet', 'type' => 'text'],
            'about_text' => ['section' => 'about', 'value' => $validated['about_text'] ?? '', 'type' => 'text'],
            'section_features_title' => ['section' => 'features', 'value' => $validated['section_features_title'] ?? 'Kenapa Memilih Kami', 'type' => 'text'],
            'feature_1_title' => ['section' => 'features', 'value' => $validated['feature_1_title'] ?? 'Terapis Berpengalaman', 'type' => 'text'],
            'feature_1_desc' => ['section' => 'features', 'value' => $validated['feature_1_desc'] ?? 'Tim profesional dengan standar layanan tinggi.', 'type' => 'text'],
            'feature_2_title' => ['section' => 'features', 'value' => $validated['feature_2_title'] ?? 'Produk Berkualitas', 'type' => 'text'],
            'feature_2_desc' => ['section' => 'features', 'value' => $validated['feature_2_desc'] ?? 'Menggunakan produk pilihan yang aman dan terpercaya.', 'type' => 'text'],
            'feature_3_title' => ['section' => 'features', 'value' => $validated['feature_3_title'] ?? 'Tempat Nyaman', 'type' => 'text'],
            'feature_3_desc' => ['section' => 'features', 'value' => $validated['feature_3_desc'] ?? 'Suasana outlet bersih, nyaman, dan private.', 'type' => 'text'],
            'section_testimonials_title' => ['section' => 'testimonials', 'value' => $validated['section_testimonials_title'] ?? 'Testimoni Pelanggan', 'type' => 'text'],
            'testimonials_json' => ['section' => 'testimonials', 'value' => json_encode($testimonials, JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            'section_faq_title' => ['section' => 'faq', 'value' => $validated['section_faq_title'] ?? 'Pertanyaan Umum', 'type' => 'text'],
            'faqs_json' => ['section' => 'faq', 'value' => json_encode($faqs, JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            'contact_phone' => ['section' => 'contact', 'value' => $validated['contact_phone'] ?? ($outlet->phone ?? ''), 'type' => 'text'],
            'contact_email' => ['section' => 'contact', 'value' => $validated['contact_email'] ?? ($outlet->email ?? ''), 'type' => 'text'],
            'contact_address' => ['section' => 'contact', 'value' => $validated['contact_address'] ?? ($outlet->address ?? ''), 'type' => 'text'],
            'booking_button_label' => ['section' => 'hero', 'value' => $validated['booking_button_label'] ?? 'Booking Online', 'type' => 'text'],
            'section_cta_title' => ['section' => 'cta', 'value' => $validated['section_cta_title'] ?? 'Siap Reservasi Treatment Anda?', 'type' => 'text'],
            'section_cta_subtitle' => ['section' => 'cta', 'value' => $validated['section_cta_subtitle'] ?? 'Pilih jadwal terbaik Anda dan booking online sekarang.', 'type' => 'text'],
            'gallery_images' => ['section' => 'gallery', 'value' => json_encode($galleryImages), 'type' => 'json'],
        ]);

        return redirect()
            ->route('settings.landing.edit')
            ->with('success', 'Konten landing outlet berhasil diperbarui.');
    }

    private function resolveOutlet(): Outlet
    {
        $outlet = outlet();
        if (! $outlet) {
            abort(404, 'Outlet aktif tidak ditemukan.');
        }

        return $outlet;
    }

    /**
     * @param  array<string, array{section: string, value: string, type: string}>  $contents
     */
    private function upsertContent(Outlet $outlet, array $contents): void
    {
        foreach ($contents as $key => $item) {
            OutletLandingContent::query()->updateOrCreate(
                ['outlet_id' => $outlet->id, 'key' => $key],
                [
                    'section' => $item['section'],
                    'value' => $item['value'],
                    'type' => $item['type'],
                ]
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function parseGalleryImages(mixed $rawValue): array
    {
        if (! is_string($rawValue) || $rawValue === '') {
            return [];
        }

        $decoded = json_decode($rawValue, true);
        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->filter(fn ($item) => is_string($item) && $item !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{name: string, role: string, quote: string}>
     */
    private function parseTestimonials(mixed $rawValue): array
    {
        $default = $this->defaultTestimonials();

        if (! is_string($rawValue) || $rawValue === '') {
            return $default;
        }

        $decoded = json_decode($rawValue, true);
        if (! is_array($decoded)) {
            return $default;
        }

        $items = array_values($decoded);
        $result = [];

        for ($i = 0; $i < 3; $i++) {
            $source = is_array($items[$i] ?? null) ? $items[$i] : [];
            $fallback = $default[$i];
            $result[] = [
                'name' => $this->normalizeOrFallback($source['name'] ?? null, $fallback['name']),
                'role' => $this->normalizeOrFallback($source['role'] ?? null, $fallback['role']),
                'quote' => $this->normalizeOrFallback($source['quote'] ?? null, $fallback['quote']),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    private function parseFaqs(mixed $rawValue): array
    {
        $default = $this->defaultFaqs();

        if (! is_string($rawValue) || $rawValue === '') {
            return $default;
        }

        $decoded = json_decode($rawValue, true);
        if (! is_array($decoded)) {
            return $default;
        }

        $items = array_values($decoded);
        $result = [];

        for ($i = 0; $i < 3; $i++) {
            $source = is_array($items[$i] ?? null) ? $items[$i] : [];
            $fallback = $default[$i];
            $result[] = [
                'question' => $this->normalizeOrFallback($source['question'] ?? null, $fallback['question']),
                'answer' => $this->normalizeOrFallback($source['answer'] ?? null, $fallback['answer']),
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, array{name: string, role: string, quote: string}>
     */
    private function buildTestimonialsPayload(Request $request, array $validated, array $existing): array
    {
        $hasAnyTestimonialField = false;
        for ($i = 1; $i <= 3; $i++) {
            if (
                $request->has("testimonial_{$i}_name")
                || $request->has("testimonial_{$i}_role")
                || $request->has("testimonial_{$i}_quote")
            ) {
                $hasAnyTestimonialField = true;
                break;
            }
        }

        if (! $hasAnyTestimonialField) {
            return $existing;
        }

        $defaults = $this->defaultTestimonials();
        $result = [];

        for ($i = 1; $i <= 3; $i++) {
            $fallback = $existing[$i - 1] ?? $defaults[$i - 1];
            $result[] = [
                'name' => $this->normalizeOrFallback($validated["testimonial_{$i}_name"] ?? null, $fallback['name']),
                'role' => $this->normalizeOrFallback($validated["testimonial_{$i}_role"] ?? null, $fallback['role']),
                'quote' => $this->normalizeOrFallback($validated["testimonial_{$i}_quote"] ?? null, $fallback['quote']),
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, array{question: string, answer: string}>
     */
    private function buildFaqPayload(Request $request, array $validated, array $existing): array
    {
        $hasAnyFaqField = false;
        for ($i = 1; $i <= 3; $i++) {
            if (
                $request->has("faq_{$i}_question")
                || $request->has("faq_{$i}_answer")
            ) {
                $hasAnyFaqField = true;
                break;
            }
        }

        if (! $hasAnyFaqField) {
            return $existing;
        }

        $defaults = $this->defaultFaqs();
        $result = [];

        for ($i = 1; $i <= 3; $i++) {
            $fallback = $existing[$i - 1] ?? $defaults[$i - 1];
            $result[] = [
                'question' => $this->normalizeOrFallback($validated["faq_{$i}_question"] ?? null, $fallback['question']),
                'answer' => $this->normalizeOrFallback($validated["faq_{$i}_answer"] ?? null, $fallback['answer']),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array{name: string, role: string, quote: string}>
     */
    private function defaultTestimonials(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    private function defaultFaqs(): array
    {
        return [
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
        ];
    }

    private function normalizeOrFallback(mixed $value, string $fallback): string
    {
        if (! is_string($value)) {
            return $fallback;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? $fallback : $trimmed;
    }
}
