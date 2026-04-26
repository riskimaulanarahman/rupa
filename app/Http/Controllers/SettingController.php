<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Branding\BrandIconGenerator;
use App\Services\OperatingHoursService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private readonly BrandIconGenerator $brandIconGenerator,
        private readonly OperatingHoursService $operatingHoursService
    ) {}

    public function index(): View
    {
        return view('settings.index');
    }

    public function clinic(): View
    {
        $settings = [
            'business_type' => business_type() ?? Setting::getForCurrentContext('business_type', 'clinic'),
            'business_name' => Setting::getForCurrentContext('business_name', ''),
            'business_address' => Setting::getForCurrentContext('business_address', ''),
            'business_phone' => Setting::getForCurrentContext('business_phone', ''),
            'business_email' => Setting::getForCurrentContext('business_email', ''),
            'clinic_logo' => Setting::getForCurrentContext('clinic_logo', ''),
            'tax_percentage' => Setting::getForCurrentContext('tax_percentage', 0),
            'invoice_prefix' => Setting::getForCurrentContext('invoice_prefix', 'INV'),
            'slot_duration' => Setting::getForCurrentContext('slot_duration', 30),
        ];

        $businessTypes = config('business.types');

        return view('settings.clinic', compact('settings', 'businessTypes'));
    }

    public function updateClinic(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_type' => ['required', 'string', 'in:clinic,salon,barbershop'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'business_phone' => ['nullable', 'string', 'max:20'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'clinic_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'tax_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'slot_duration' => ['required', 'integer', 'min:15', 'max:120'],
        ]);

        $newLogoPath = null;
        $oldLogo = Setting::getForCurrentContext('clinic_logo');

        if ($request->hasFile('clinic_logo')) {
            $newLogoPath = $request->file('clinic_logo')->store('settings', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $newLogoPath) {
                $outlet = outlet();
                if ($outlet) {
                    $outlet->forceFill([
                        'business_type' => $validated['business_type'],
                    ])->save();
                }

                Setting::setForCurrentContext('business_type', $validated['business_type']);
                Setting::setForCurrentContext('business_name', $validated['business_name']);
                Setting::setForCurrentContext('business_address', $validated['business_address'] ?? null);
                Setting::setForCurrentContext('business_phone', $validated['business_phone'] ?? null);
                Setting::setForCurrentContext('business_email', $validated['business_email'] ?? null);
                Setting::setForCurrentContext('tax_percentage', $validated['tax_percentage'], 'integer');
                Setting::setForCurrentContext('invoice_prefix', $validated['invoice_prefix']);
                Setting::setForCurrentContext('slot_duration', $validated['slot_duration'], 'integer');

                if ($newLogoPath !== null) {
                    Setting::setForCurrentContext('clinic_logo', $newLogoPath);
                }
            });
        } catch (\Throwable $e) {
            if ($newLogoPath !== null) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $e;
        }

        if ($newLogoPath !== null && $oldLogo && $oldLogo !== $newLogoPath) {
            Storage::disk('public')->delete($oldLogo);
        }

        clear_business_cache();

        return back()->with('success', __('setting.updated'));
    }

    public function hours(): View
    {
        $this->abortIfHoursContextMissing();

        $hours = $this->operatingHoursService->getWeeklyScheduleForCurrentOutlet();

        return view('settings.hours', compact('hours'));
    }

    public function updateHours(Request $request): RedirectResponse
    {
        $this->abortIfHoursContextMissing();

        $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.is_closed' => ['boolean'],
            'hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'hours.*.close_time' => ['nullable', 'date_format:H:i'],
        ]);

        $hours = collect($request->input('hours', []))
            ->map(fn (array $data, string|int $dayOfWeek): array => [
                'day_of_week' => (int) $dayOfWeek,
                'is_closed' => $data['is_closed'] ?? false,
                'open_time' => $data['open_time'] ?? null,
                'close_time' => $data['close_time'] ?? null,
            ])
            ->values()
            ->all();

        $this->operatingHoursService->upsertWeeklyScheduleForCurrentOutlet($hours);

        return back()->with('success', 'Jam operasional berhasil diperbarui.');
    }

    private function abortIfHoursContextMissing(): void
    {
        abort_unless(
            $this->operatingHoursService->hasCurrentOutletContext(),
            400,
            'Konteks outlet wajib tersedia untuk jam operasional.'
        );
    }

    public function branding(): View
    {
        $settings = [
            // App Identity
            'brand_app_name' => Setting::get('brand_app_name', ''),
            'brand_app_tagline' => Setting::get('brand_app_tagline', ''),
            'brand_app_tagline_id' => Setting::get('brand_app_tagline_id', ''),
            'brand_app_description' => Setting::get('brand_app_description', ''),
            'brand_app_description_id' => Setting::get('brand_app_description_id', ''),

            // Logo
            'brand_logo_path' => Setting::get('brand_logo_path', ''),
            'brand_logo_show_text' => Setting::get('brand_logo_show_text', true),

            // Contact
            'brand_contact_email' => Setting::get('brand_contact_email', ''),
            'brand_contact_phone' => Setting::get('brand_contact_phone', ''),
            'brand_contact_whatsapp' => Setting::get('brand_contact_whatsapp', ''),
            'brand_contact_address' => Setting::get('brand_contact_address', ''),

            // Social Media
            'brand_social_facebook' => Setting::get('brand_social_facebook', ''),
            'brand_social_instagram' => Setting::get('brand_social_instagram', ''),
            'brand_social_twitter' => Setting::get('brand_social_twitter', ''),
            'brand_social_tiktok' => Setting::get('brand_social_tiktok', ''),

            // Footer
            'brand_footer_copyright' => Setting::get('brand_footer_copyright', ''),
            'brand_footer_copyright_id' => Setting::get('brand_footer_copyright_id', ''),
            'brand_footer_show_powered_by' => Setting::get('brand_footer_show_powered_by', true),
            'brand_footer_powered_by_text' => Setting::get('brand_footer_powered_by_text', ''),
            'brand_footer_powered_by_url' => Setting::get('brand_footer_powered_by_url', ''),

            // Custom Scripts
            'brand_custom_head_scripts' => Setting::get('brand_custom_head_scripts', ''),
            'brand_custom_body_scripts' => Setting::get('brand_custom_body_scripts', ''),
            'brand_custom_custom_css' => Setting::get('brand_custom_custom_css', ''),
        ];

        return view('settings.branding', compact('settings'));
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $request->validate([
            'brand_app_name' => ['nullable', 'string', 'max:255'],
            'brand_app_tagline' => ['nullable', 'string', 'max:255'],
            'brand_app_tagline_id' => ['nullable', 'string', 'max:255'],
            'brand_app_description' => ['nullable', 'string', 'max:1000'],
            'brand_app_description_id' => ['nullable', 'string', 'max:1000'],
            'brand_logo_path' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'brand_logo_favicon' => ['prohibited'],
            'brand_logo_show_text' => ['nullable', 'boolean'],
            'brand_contact_email' => ['nullable', 'email', 'max:255'],
            'brand_contact_phone' => ['nullable', 'string', 'max:50'],
            'brand_contact_whatsapp' => ['nullable', 'string', 'max:50'],
            'brand_contact_address' => ['nullable', 'string', 'max:500'],
            'brand_social_facebook' => ['nullable', 'url', 'max:255'],
            'brand_social_instagram' => ['nullable', 'url', 'max:255'],
            'brand_social_twitter' => ['nullable', 'url', 'max:255'],
            'brand_social_tiktok' => ['nullable', 'url', 'max:255'],
            'brand_footer_copyright' => ['nullable', 'string', 'max:255'],
            'brand_footer_copyright_id' => ['nullable', 'string', 'max:255'],
            'brand_footer_show_powered_by' => ['nullable', 'boolean'],
            'brand_footer_powered_by_text' => ['nullable', 'string', 'max:100'],
            'brand_footer_powered_by_url' => ['nullable', 'url', 'max:255'],
            'brand_custom_head_scripts' => ['nullable', 'string', 'max:10000'],
            'brand_custom_body_scripts' => ['nullable', 'string', 'max:10000'],
            'brand_custom_custom_css' => ['nullable', 'string', 'max:10000'],
        ]);

        // Text fields
        $textFields = [
            'brand_app_name', 'brand_app_tagline', 'brand_app_tagline_id',
            'brand_app_description', 'brand_app_description_id',
            'brand_contact_email', 'brand_contact_phone', 'brand_contact_whatsapp',
            'brand_contact_address', 'brand_social_facebook', 'brand_social_instagram',
            'brand_social_twitter', 'brand_social_tiktok', 'brand_footer_copyright',
            'brand_footer_copyright_id', 'brand_footer_powered_by_text',
            'brand_footer_powered_by_url', 'brand_custom_head_scripts',
            'brand_custom_body_scripts', 'brand_custom_custom_css',
        ];

        foreach ($textFields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field) ?? '');
            }
        }

        // Boolean fields
        Setting::set('brand_logo_show_text', $request->boolean('brand_logo_show_text'), 'boolean');
        Setting::set('brand_footer_show_powered_by', $request->boolean('brand_footer_show_powered_by'), 'boolean');

        $shouldGenerateIcons = false;

        // Handle logo upload
        if ($request->hasFile('brand_logo_path')) {
            $oldLogo = Setting::get('brand_logo_path');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('brand_logo_path')->store('branding', 'public');
            Setting::set('brand_logo_path', $path);
            $shouldGenerateIcons = true;
        }

        // Clear branding cache
        clear_brand_cache();

        $platformFaviconConfigured = $this->hasPlatformGlobalFavicon();
        if ($shouldGenerateIcons && ! $platformFaviconConfigured) {
            try {
                $this->brandIconGenerator->generate('auto', true);
            } catch (\Throwable $e) {
                Log::warning('Brand icon generation failed after branding update.', [
                    'message' => $e->getMessage(),
                ]);

                return back()
                    ->with('success', __('setting.branding_updated'))
                    ->with('error', 'Branding disimpan, tetapi gagal generate favicon/icon. Jalankan perintah branding:generate-icons.');
            }
        }

        return back()->with('success', __('setting.branding_updated'));
    }

    public function removeLogo(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'logo');

        if ($type === 'favicon') {
            abort(403, 'Favicon hanya dapat diubah oleh superadmin.');
        }

        $oldLogo = Setting::get('brand_logo_path');
        if ($oldLogo) {
            Storage::disk('public')->delete($oldLogo);
            Setting::set('brand_logo_path', '');
        }

        clear_brand_cache();

        return back()->with('success', __('setting.logo_removed'));
    }

    private function hasPlatformGlobalFavicon(): bool
    {
        $platformFavicon = Setting::getGlobal('platform_brand_logo_favicon');

        return is_string($platformFavicon) && $platformFavicon !== '';
    }
}
