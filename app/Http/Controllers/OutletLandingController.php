<?php

namespace App\Http\Controllers;

use App\Models\OperatingHour;
use App\Models\OutletLandingContent;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutletLandingController extends Controller
{
    public function show(Request $request): View
    {
        $outlet = outlet();
        if (! $outlet) {
            abort(404, 'Outlet tidak ditemukan.');
        }

        $contents = OutletLandingContent::query()
            ->where('outlet_id', $outlet->id)
            ->orderBy('section')
            ->orderBy('key')
            ->get();

        $contentMap = $contents->pluck('value', 'key');
        $galleryImages = $this->parseStringArrayJson($contentMap->get('gallery_images'));
        $testimonials = $this->parseTestimonials($contentMap->get('testimonials_json'));
        $faqs = $this->parseFaqs($contentMap->get('faqs_json'));

        $serviceCategories = ServiceCategory::query()
            ->active()
            ->ordered()
            ->with(['services' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('price')
                ->orderBy('name')])
            ->get()
            ->filter(fn ($category) => $category->services->isNotEmpty())
            ->values();

        $operatingHours = OperatingHour::query()
            ->orderBy('day_of_week')
            ->get();

        ['url' => $staffActionUrl, 'label' => $staffActionLabel] = $this->resolveStaffAction($outlet->id, $outlet->tenant_id, $outlet->slug);
        ['url' => $customerActionUrl, 'label' => $customerActionLabel] = $this->resolveCustomerAction($outlet->id, $outlet->slug);

        return view('outlet.landing.show', [
            'outlet' => $outlet,
            'contents' => $contents,
            'contentMap' => $contentMap,
            'serviceCategories' => $serviceCategories,
            'operatingHours' => $operatingHours,
            'galleryImages' => $galleryImages,
            'testimonials' => $testimonials,
            'faqs' => $faqs,
            'staffActionUrl' => $staffActionUrl,
            'staffActionLabel' => $staffActionLabel,
            'customerActionUrl' => $customerActionUrl,
            'customerActionLabel' => $customerActionLabel,
            'bookingUrl' => route('outlet.booking.index', ['outletSlug' => $outlet->slug]),
        ]);
    }

    /**
     * @return array{url: string, label: string}
     */
    private function resolveStaffAction(int $outletId, int $tenantId, string $outletSlug): array
    {
        $user = auth()->user();

        if (! $user) {
            return [
                'url' => route('outlet.login', ['outletSlug' => $outletSlug]),
                'label' => 'Login Staff',
            ];
        }

        if ($user->isSuperAdmin()) {
            return [
                'url' => route('platform.dashboard'),
                'label' => 'Platform',
            ];
        }

        $sameTenant = (int) ($user->tenant_id ?? 0) === $tenantId;
        if (! $sameTenant) {
            return [
                'url' => route('outlet.login', ['outletSlug' => $outletSlug]),
                'label' => 'Login Staff',
            ];
        }

        if ($user->canViewRevenue()) {
            return [
                'url' => route('dashboard'),
                'label' => 'Dashboard',
            ];
        }

        $sameOutlet = (int) ($user->outlet_id ?? 0) === $outletId;
        if ($sameOutlet || $user->isOwner()) {
            return [
                'url' => route('appointments.index'),
                'label' => 'Panel Staff',
            ];
        }

        return [
            'url' => route('outlet.login', ['outletSlug' => $outletSlug]),
            'label' => 'Login Staff',
        ];
    }

    /**
     * @return array{url: string, label: string}
     */
    private function resolveCustomerAction(int $outletId, string $outletSlug): array
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return [
                'url' => route('outlet.customer.login', ['outletSlug' => $outletSlug]),
                'label' => 'Login Pelanggan',
            ];
        }

        if ((int) ($customer->outlet_id ?? 0) !== $outletId) {
            return [
                'url' => route('outlet.customer.login', ['outletSlug' => $outletSlug]),
                'label' => 'Login Pelanggan',
            ];
        }

        return [
            'url' => route('outlet.customer.dashboard', ['outletSlug' => $outletSlug]),
            'label' => 'Akun Saya',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseStringArrayJson(mixed $rawValue): array
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
        $default = [
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

        return $this->parseStructuredJsonItems($rawValue, $default, ['name', 'role', 'quote']);
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    private function parseFaqs(mixed $rawValue): array
    {
        $default = [
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

        return $this->parseStructuredJsonItems($rawValue, $default, ['question', 'answer']);
    }

    /**
     * @param  array<int, array<string, string>>  $defaults
     * @param  array<int, string>  $fields
     * @return array<int, array<string, string>>
     */
    private function parseStructuredJsonItems(mixed $rawValue, array $defaults, array $fields): array
    {
        if (! is_string($rawValue) || $rawValue === '') {
            return $defaults;
        }

        $decoded = json_decode($rawValue, true);
        if (! is_array($decoded)) {
            return $defaults;
        }

        $items = array_values($decoded);
        $normalized = [];

        for ($i = 0; $i < count($defaults); $i++) {
            $defaultItem = $defaults[$i];
            $sourceItem = is_array($items[$i] ?? null) ? $items[$i] : [];
            $item = [];

            foreach ($fields as $field) {
                $value = $sourceItem[$field] ?? '';
                $item[$field] = is_string($value) && trim($value) !== ''
                    ? trim($value)
                    : $defaultItem[$field];
            }

            $normalized[] = $item;
        }

        return $normalized;
    }
}
