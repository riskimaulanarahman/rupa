<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('brand')) {
    /**
     * Get a branding configuration value.
     * First checks database settings, then falls back to config file.
     *
     * @param  string  $key  Dot notation key (e.g., 'app.name', 'colors.primary')
     * @param  mixed  $default  Default value if not found
     */
    function brand(string $key, mixed $default = null): mixed
    {
        return Cache::remember("brand.{$key}", 300, function () use ($key, $default) {
            // First try to get from database settings
            $settingKey = 'brand_'.str_replace('.', '_', $key);

            try {
                $dbValue = Setting::get($settingKey);
                if ($dbValue !== null && $dbValue !== '') {
                    return $dbValue;
                }
            } catch (\Exception $e) {
                // Database might not be ready
            }

            // Fall back to config file
            return config("branding.{$key}", $default);
        });
    }
}

if (! function_exists('brand_name')) {
    /**
     * Get the application brand name.
     */
    function brand_name(): string
    {
        return brand('app.name', 'GlowUp');
    }
}

if (! function_exists('brand_tagline')) {
    /**
     * Get the application tagline based on locale.
     */
    function brand_tagline(): string
    {
        $locale = app()->getLocale();
        $key = $locale === 'id' ? 'app.tagline_id' : 'app.tagline';

        return brand($key, 'Beauty & Wellness Management');
    }
}

if (! function_exists('brand_description')) {
    /**
     * Get the application description based on locale.
     */
    function brand_description(): string
    {
        $locale = app()->getLocale();
        $key = $locale === 'id' ? 'app.description_id' : 'app.description';

        return brand($key, '');
    }
}

if (! function_exists('brand_logo')) {
    /**
     * Get the logo URL or path.
     *
     * @param  string  $type  'main', 'favicon', 'email', 'invoice'
     */
    function brand_logo(string $type = 'main'): ?string
    {
        $key = match ($type) {
            'favicon' => 'logo.favicon',
            'email' => 'email.logo_url',
            'invoice' => 'invoice.logo_path',
            default => 'logo.path',
        };

        $path = brand($key);

        if (! $path) {
            return null;
        }

        // If it's a full URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // If it's a storage path, generate URL
        if (str_starts_with($path, 'branding/')) {
            return asset('storage/'.$path);
        }

        return asset($path);
    }
}

if (! function_exists('brand_color')) {
    /**
     * Get a brand color value.
     *
     * @param  string  $name  Color name (primary, secondary, accent, etc.)
     */
    function brand_color(string $name = 'primary'): string
    {
        return brand("colors.{$name}", '#f43f5e');
    }
}

if (! function_exists('brand_tailwind')) {
    /**
     * Get Tailwind CSS class for theming.
     *
     * @param  string  $name  Class type (primary, gradient_from, gradient_to)
     */
    function brand_tailwind(string $name = 'primary'): string
    {
        return brand("tailwind.{$name}", 'rose');
    }
}

if (! function_exists('brand_contact')) {
    /**
     * Get contact information.
     *
     * @param  string  $type  Contact type (email, phone, whatsapp, address)
     */
    function brand_contact(string $type): ?string
    {
        return brand("contact.{$type}");
    }
}

if (! function_exists('brand_social')) {
    /**
     * Get social media link.
     *
     * @param  string  $platform  Platform name (facebook, instagram, twitter, etc.)
     */
    function brand_social(string $platform): ?string
    {
        return brand("social.{$platform}");
    }
}

if (! function_exists('brand_socials')) {
    /**
     * Get all configured social media links.
     */
    function brand_socials(): array
    {
        $platforms = ['facebook', 'instagram', 'twitter', 'youtube', 'tiktok', 'linkedin'];
        $socials = [];

        foreach ($platforms as $platform) {
            $url = brand_social($platform);
            if ($url) {
                $socials[$platform] = $url;
            }
        }

        return $socials;
    }
}

if (! function_exists('brand_copyright')) {
    /**
     * Get the copyright text with replacements.
     */
    function brand_copyright(): string
    {
        $locale = app()->getLocale();
        $key = $locale === 'id' ? 'footer.copyright_id' : 'footer.copyright';

        $text = brand($key, '© :year :app_name. All rights reserved.');

        return str_replace(
            [':year', ':app_name'],
            [date('Y'), brand_name()],
            $text
        );
    }
}

if (! function_exists('brand_feature')) {
    /**
     * Check if a feature is enabled.
     *
     * @param  string  $feature  Feature name
     */
    function brand_feature(string $feature): bool
    {
        return (bool) brand("features.{$feature}", false);
    }
}

if (! function_exists('brand_custom_script')) {
    /**
     * Get custom script for injection.
     *
     * @param  string  $location  Script location (head, body)
     */
    function brand_custom_script(string $location = 'head'): ?string
    {
        $key = $location === 'body' ? 'custom.body_scripts' : 'custom.head_scripts';

        return brand($key);
    }
}

if (! function_exists('brand_custom_css')) {
    /**
     * Get custom CSS.
     */
    function brand_custom_css(): ?string
    {
        return brand('custom.custom_css');
    }
}

if (! function_exists('clear_brand_cache')) {
    /**
     * Clear all branding cache.
     */
    function clear_brand_cache(): void
    {
        // Get all potential cache keys
        $keys = [
            'app.name', 'app.tagline', 'app.tagline_id', 'app.description', 'app.description_id',
            'logo.path', 'logo.favicon', 'logo.width', 'logo.height', 'logo.show_text',
            'colors.primary', 'colors.primary_hover', 'colors.primary_light',
            'colors.secondary', 'colors.accent', 'colors.success', 'colors.warning',
            'colors.danger', 'colors.info',
            'tailwind.primary', 'tailwind.gradient_from', 'tailwind.gradient_to',
            'contact.email', 'contact.phone', 'contact.whatsapp', 'contact.address',
            'social.facebook', 'social.instagram', 'social.twitter', 'social.youtube',
            'social.tiktok', 'social.linkedin',
            'footer.copyright', 'footer.copyright_id', 'footer.show_powered_by',
            'footer.powered_by_text', 'footer.powered_by_url',
            'features.allow_registration', 'features.show_language_switcher',
            'features.show_dark_mode', 'features.show_notifications',
            'custom.head_scripts', 'custom.body_scripts', 'custom.custom_css',
        ];

        foreach ($keys as $key) {
            Cache::forget("brand.{$key}");
        }
    }
}
