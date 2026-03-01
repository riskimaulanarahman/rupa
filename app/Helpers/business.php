<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('is_setup_completed')) {
    /**
     * Check if the initial setup has been completed.
     */
    function is_setup_completed(): bool
    {
        return Cache::remember('setup_completed', 60, function () {
            try {
                return (bool) Setting::get('setup_completed', false);
            } catch (\Exception $e) {
                // Database might not be ready yet
                return false;
            }
        });
    }
}

if (! function_exists('business_type')) {
    /**
     * Get the current business type.
     */
    function business_type(): ?string
    {
        return Cache::remember('business_type', 60, function () {
            try {
                return Setting::get('business_type');
            } catch (\Exception $e) {
                return null;
            }
        });
    }
}

if (! function_exists('business_config')) {
    /**
     * Get configuration for the current business type.
     *
     * @param  string|null  $key  Dot notation key to get specific config value
     * @param  mixed  $default  Default value if key not found
     */
    function business_config(?string $key = null, mixed $default = null): mixed
    {
        $type = business_type() ?? config('business.default', 'clinic');
        $config = config("business.types.{$type}");

        if ($key === null) {
            return $config;
        }

        return data_get($config, $key, $default);
    }
}

if (! function_exists('business_label')) {
    /**
     * Get a localized label for the current business type.
     *
     * @param  string  $key  The label key (e.g., 'staff_label', 'service_label')
     */
    function business_label(string $key): string
    {
        $locale = app()->getLocale();
        $config = business_config();

        if (! $config) {
            return $key;
        }

        // Check for locale-specific key first (e.g., 'name_en')
        $localeKey = $key.'_'.$locale;
        if (isset($config[$localeKey])) {
            return $config[$localeKey];
        }

        // Fall back to default key
        return $config[$key] ?? $key;
    }
}

if (! function_exists('business_theme')) {
    /**
     * Get theme configuration for the current business type.
     *
     * @param  string|null  $key  Specific theme key (e.g., 'primary', 'button')
     */
    function business_theme(?string $key = null): mixed
    {
        $theme = business_config('theme', []);

        if ($key === null) {
            return $theme;
        }

        return $theme[$key] ?? null;
    }
}

if (! function_exists('business_profile_fields')) {
    /**
     * Get customer profile fields configuration for the current business type.
     */
    function business_profile_fields(): array
    {
        return business_config('profile_fields', []);
    }
}

if (! function_exists('business_profile_options')) {
    /**
     * Get profile field options with localized labels.
     *
     * @param  string  $field  The field name ('type' or 'concerns')
     */
    function business_profile_options(string $field): array
    {
        $fields = business_profile_fields();
        $locale = app()->getLocale();

        if (! isset($fields[$field]['options'])) {
            return [];
        }

        $options = [];
        foreach ($fields[$field]['options'] as $key => $labels) {
            $options[$key] = $labels[$locale] ?? $labels['id'] ?? $key;
        }

        return $options;
    }
}

if (! function_exists('clear_business_cache')) {
    /**
     * Clear cached business settings.
     * Call this when settings are updated.
     */
    function clear_business_cache(): void
    {
        Cache::forget('setup_completed');
        Cache::forget('business_type');
    }
}

if (! function_exists('staff_role_label')) {
    /**
     * Get the staff role label based on business type.
     * Maps 'beautician' role to appropriate label (Hairstylist, Barber, etc.)
     *
     * @param  string  $role  The role key
     * @param  bool  $plural  Whether to return plural form
     */
    function staff_role_label(string $role, bool $plural = false): string
    {
        // Owner and Admin are universal
        if (in_array($role, ['owner', 'admin'])) {
            return __("staff.role_{$role}");
        }

        // For beautician/staff role, use business-specific label
        if ($role === 'beautician') {
            $label = business_config($plural ? 'staff_label_plural' : 'staff_label');

            return $label ?? __('staff.role_beautician');
        }

        return __("staff.role_{$role}") ?? ucfirst($role);
    }
}

if (! function_exists('business_staff_label')) {
    /**
     * Get the staff label for the current business type.
     *
     * @param  bool  $plural  Whether to return plural form
     */
    function business_staff_label(bool $plural = false): string
    {
        $key = $plural ? 'staff_label_plural' : 'staff_label';

        return business_config($key) ?? __('staff.role_beautician');
    }
}

if (! function_exists('has_feature')) {
    /**
     * Check if a feature is enabled for the current business type.
     *
     * @param  string  $feature  The feature key (e.g., 'treatment_records', 'packages')
     */
    function has_feature(string $feature): bool
    {
        return (bool) business_config("features.{$feature}", false);
    }
}

if (! function_exists('business_features')) {
    /**
     * Get all features configuration for the current business type.
     */
    function business_features(): array
    {
        return business_config('features', []);
    }
}

if (! function_exists('landing_text')) {
    /**
     * Get landing page text based on business type.
     * Falls back to default translation if business-specific not found.
     *
     * @param  string  $key  Translation key (e.g., 'hero_badge')
     * @param  array  $replace  Replacement values
     */
    function landing_text(string $key, array $replace = []): string
    {
        $type = business_type() ?? 'clinic';

        // Try business-specific key first (e.g., landing.salon.hero_badge)
        $businessKey = "landing.{$type}.{$key}";
        $translation = __($businessKey, $replace);

        // If business-specific not found, fall back to default
        if ($translation === $businessKey) {
            return __("landing.{$key}", $replace);
        }

        return $translation;
    }
}
