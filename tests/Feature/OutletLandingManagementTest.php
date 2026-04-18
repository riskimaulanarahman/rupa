<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\OutletLandingContent;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletLandingManagementTest extends TestCase
{
    use RefreshDatabase;

    private Outlet $outlet;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::query()->create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price_monthly' => 400000,
            'price_yearly' => 4000000,
            'max_outlets' => 5,
            'trial_days' => 14,
            'sort_order' => 2,
            'is_active' => true,
            'is_featured' => true,
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Tenant Landing Management Test',
            'slug' => 'tenant-landing-management-test',
            'plan_id' => $plan->id,
            'owner_name' => 'Owner Landing',
            'owner_email' => 'owner-landing-management@example.com',
            'status' => 'active',
            'subscription_ends_at' => now()->addMonth(),
            'is_read_only' => false,
        ]);

        $this->outlet = Outlet::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Outlet Landing Management',
            'slug' => 'outlet-landing-management',
            'full_subdomain' => 'tenant-landing-management-test.rupa.test',
            'business_type' => 'clinic',
            'status' => 'active',
        ]);

        $this->owner = User::query()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $this->outlet->id,
            'name' => 'Owner Test',
            'email' => 'owner-test@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'phone' => '081299001122',
            'is_active' => true,
        ]);
    }

    public function test_owner_can_update_new_landing_fields_and_json_payloads(): void
    {
        OutletLandingContent::query()->updateOrCreate(
            ['outlet_id' => $this->outlet->id, 'key' => 'hero_image'],
            ['section' => 'hero', 'value' => 'outlet-landing/existing-hero.jpg', 'type' => 'image']
        );

        $response = $this->actingAs($this->owner)->put(route('settings.landing.update'), [
            'hero_badge' => 'Premium Care',
            'hero_title' => 'Glow Experience',
            'hero_subtitle' => 'Perawatan modern untuk hasil maksimal.',
            'section_services_title' => 'Daftar Layanan',
            'section_gallery_title' => 'Foto Outlet',
            'section_about_title' => 'Tentang Kami',
            'about_text' => 'Kami fokus pada kualitas pelayanan.',
            'section_features_title' => 'Keunggulan Kami',
            'feature_1_title' => 'Terapis Ahli',
            'feature_1_desc' => 'Semua terapis bersertifikat.',
            'feature_2_title' => 'Produk Premium',
            'feature_2_desc' => 'Produk aman dan terpercaya.',
            'feature_3_title' => 'Ruang Nyaman',
            'feature_3_desc' => 'Privasi pelanggan terjaga.',
            'section_testimonials_title' => 'Ulasan Pelanggan',
            'testimonial_1_name' => 'Maya',
            'testimonial_1_role' => 'Karyawan',
            'testimonial_1_quote' => 'Sangat memuaskan.',
            'testimonial_2_name' => 'Dina',
            'testimonial_2_role' => 'Wiraswasta',
            'testimonial_2_quote' => 'Pelayanannya ramah.',
            'testimonial_3_name' => 'Rosa',
            'testimonial_3_role' => 'Ibu Rumah Tangga',
            'testimonial_3_quote' => 'Pasti kembali lagi.',
            'section_faq_title' => 'FAQ',
            'faq_1_question' => 'Apakah wajib booking?',
            'faq_1_answer' => 'Disarankan booking dulu.',
            'faq_2_question' => 'Bisa walk in?',
            'faq_2_answer' => 'Bisa, sesuai ketersediaan.',
            'faq_3_question' => 'Jam buka?',
            'faq_3_answer' => 'Setiap hari kerja.',
            'contact_phone' => '081299334455',
            'contact_email' => 'outlet@example.com',
            'contact_address' => 'Jl. Mawar No. 10',
            'booking_button_label' => 'Booking Sekarang',
            'section_cta_title' => 'Mulai Perawatan Anda',
            'section_cta_subtitle' => 'Pilih jadwal paling nyaman untuk Anda.',
        ]);

        $response->assertRedirect(route('settings.landing.edit'));

        $this->assertDatabaseHas('outlet_landing_contents', [
            'outlet_id' => $this->outlet->id,
            'key' => 'hero_badge',
            'value' => 'Premium Care',
        ]);

        $this->assertDatabaseHas('outlet_landing_contents', [
            'outlet_id' => $this->outlet->id,
            'key' => 'hero_image',
            'value' => 'outlet-landing/existing-hero.jpg',
        ]);

        $testimonialsJson = OutletLandingContent::query()
            ->where('outlet_id', $this->outlet->id)
            ->where('key', 'testimonials_json')
            ->value('value');

        $faqsJson = OutletLandingContent::query()
            ->where('outlet_id', $this->outlet->id)
            ->where('key', 'faqs_json')
            ->value('value');

        $testimonials = json_decode((string) $testimonialsJson, true);
        $faqs = json_decode((string) $faqsJson, true);

        $this->assertIsArray($testimonials);
        $this->assertCount(3, $testimonials);
        $this->assertSame('Maya', $testimonials[0]['name']);
        $this->assertSame('Pasti kembali lagi.', $testimonials[2]['quote']);

        $this->assertIsArray($faqs);
        $this->assertCount(3, $faqs);
        $this->assertSame('Apakah wajib booking?', $faqs[0]['question']);
        $this->assertSame('Setiap hari kerja.', $faqs[2]['answer']);
    }

    public function test_partial_update_does_not_reset_existing_testimonials_and_faqs(): void
    {
        OutletLandingContent::query()->updateOrCreate(
            ['outlet_id' => $this->outlet->id, 'key' => 'testimonials_json'],
            [
                'section' => 'testimonials',
                'value' => json_encode([
                    ['name' => 'A', 'role' => 'R1', 'quote' => 'Q1'],
                    ['name' => 'B', 'role' => 'R2', 'quote' => 'Q2'],
                    ['name' => 'C', 'role' => 'R3', 'quote' => 'Q3'],
                ], JSON_UNESCAPED_UNICODE),
                'type' => 'json',
            ]
        );

        OutletLandingContent::query()->updateOrCreate(
            ['outlet_id' => $this->outlet->id, 'key' => 'faqs_json'],
            [
                'section' => 'faq',
                'value' => json_encode([
                    ['question' => 'F1', 'answer' => 'A1'],
                    ['question' => 'F2', 'answer' => 'A2'],
                    ['question' => 'F3', 'answer' => 'A3'],
                ], JSON_UNESCAPED_UNICODE),
                'type' => 'json',
            ]
        );

        $response = $this->actingAs($this->owner)->put(route('settings.landing.update'), [
            'hero_title' => 'Updated Hero Only',
        ]);

        $response->assertRedirect(route('settings.landing.edit'));

        $testimonialsJson = OutletLandingContent::query()
            ->where('outlet_id', $this->outlet->id)
            ->where('key', 'testimonials_json')
            ->value('value');
        $faqsJson = OutletLandingContent::query()
            ->where('outlet_id', $this->outlet->id)
            ->where('key', 'faqs_json')
            ->value('value');

        $testimonials = json_decode((string) $testimonialsJson, true);
        $faqs = json_decode((string) $faqsJson, true);

        $this->assertSame('A', $testimonials[0]['name']);
        $this->assertSame('Q3', $testimonials[2]['quote']);
        $this->assertSame('F1', $faqs[0]['question']);
        $this->assertSame('A3', $faqs[2]['answer']);
    }
}
