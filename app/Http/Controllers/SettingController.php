<?php

namespace App\Http\Controllers;

use App\Models\OperatingHour;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index');
    }

    public function clinic(): View
    {
        $settings = [
            'business_type' => Setting::get('business_type', 'clinic'),
            'business_name' => Setting::get('business_name', ''),
            'business_address' => Setting::get('business_address', ''),
            'business_phone' => Setting::get('business_phone', ''),
            'business_email' => Setting::get('business_email', ''),
            'clinic_logo' => Setting::get('clinic_logo', ''),
            'tax_percentage' => Setting::get('tax_percentage', 0),
            'invoice_prefix' => Setting::get('invoice_prefix', 'INV'),
            'slot_duration' => Setting::get('slot_duration', 30),
        ];

        $businessTypes = config('business.types');

        return view('settings.clinic', compact('settings', 'businessTypes'));
    }

    public function updateClinic(Request $request): RedirectResponse
    {
        $request->validate([
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

        Setting::set('business_type', $request->business_type);
        Setting::set('business_name', $request->business_name);
        Setting::set('business_address', $request->business_address);
        Setting::set('business_phone', $request->business_phone);
        Setting::set('business_email', $request->business_email);
        Setting::set('tax_percentage', $request->tax_percentage, 'integer');
        Setting::set('invoice_prefix', $request->invoice_prefix);
        Setting::set('slot_duration', $request->slot_duration, 'integer');

        if ($request->hasFile('clinic_logo')) {
            $oldLogo = Setting::get('clinic_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('clinic_logo')->store('settings', 'public');
            Setting::set('clinic_logo', $path);
        }

        // Clear business cache so theme changes take effect immediately
        clear_business_cache();

        return back()->with('success', __('setting.updated'));
    }

    public function hours(): View
    {
        $hours = OperatingHour::orderBy('day_of_week')->get();

        if ($hours->isEmpty()) {
            for ($i = 0; $i < 7; $i++) {
                OperatingHour::create([
                    'day_of_week' => $i,
                    'open_time' => $i === 0 ? null : '09:00',
                    'close_time' => $i === 0 ? null : '18:00',
                    'is_closed' => $i === 0,
                ]);
            }
            $hours = OperatingHour::orderBy('day_of_week')->get();
        }

        return view('settings.hours', compact('hours'));
    }

    public function updateHours(Request $request): RedirectResponse
    {
        $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.is_closed' => ['boolean'],
            'hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'hours.*.close_time' => ['nullable', 'date_format:H:i'],
        ]);

        foreach ($request->hours as $dayOfWeek => $data) {
            OperatingHour::updateOrCreate(
                ['day_of_week' => $dayOfWeek],
                [
                    'is_closed' => $data['is_closed'] ?? false,
                    'open_time' => ($data['is_closed'] ?? false) ? null : ($data['open_time'] ?? null),
                    'close_time' => ($data['is_closed'] ?? false) ? null : ($data['close_time'] ?? null),
                ]
            );
        }

        return back()->with('success', 'Jam operasional berhasil diperbarui.');
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
            'brand_logo_favicon' => Setting::get('brand_logo_favicon', ''),
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
            'brand_logo_favicon' => ['nullable', 'image', 'mimes:ico,png,jpg,webp', 'max:512'],
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

        // Handle logo upload
        if ($request->hasFile('brand_logo_path')) {
            $oldLogo = Setting::get('brand_logo_path');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('brand_logo_path')->store('branding', 'public');
            Setting::set('brand_logo_path', $path);
        }

        // Handle favicon upload
        if ($request->hasFile('brand_logo_favicon')) {
            $oldFavicon = Setting::get('brand_logo_favicon');
            if ($oldFavicon) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $path = $request->file('brand_logo_favicon')->store('branding', 'public');
            Setting::set('brand_logo_favicon', $path);
        }

        // Clear branding cache
        clear_brand_cache();

        return back()->with('success', __('setting.branding_updated'));
    }

    public function removeLogo(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'logo');

        if ($type === 'favicon') {
            $oldFavicon = Setting::get('brand_logo_favicon');
            if ($oldFavicon) {
                Storage::disk('public')->delete($oldFavicon);
                Setting::set('brand_logo_favicon', '');
            }
        } else {
            $oldLogo = Setting::get('brand_logo_path');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
                Setting::set('brand_logo_path', '');
            }
        }

        clear_brand_cache();

        return back()->with('success', __('setting.logo_removed'));
    }
}
