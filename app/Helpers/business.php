<?php

use App\Models\LandingContent;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('business_cache_store')) {
    /**
     * Use a non-database cache store for runtime business metadata when available.
     */
    function business_cache_store(): \Illuminate\Contracts\Cache\Repository
    {
        $defaultStore = (string) config('cache.default', 'file');

        if ($defaultStore !== 'database') {
            return Cache::store($defaultStore);
        }

        return array_key_exists('file', config('cache.stores', []))
            ? Cache::store('file')
            : Cache::store($defaultStore);
    }
}

if (! function_exists('is_setup_completed')) {
    /**
     * Check if the initial setup has been completed.
     */
    function is_setup_completed(): bool
    {
        static $resolved = null;

        if ($resolved !== null) {
            return $resolved;
        }

        $resolver = function (): bool {
            try {
                return (bool) Setting::get('setup_completed', false);
            } catch (\Throwable $e) {
                return false;
            }
        };

        try {
            $resolved = (bool) business_cache_store()->remember('setup_completed', 60, $resolver);
        } catch (\Throwable $e) {
            $resolved = $resolver();
        }

        return $resolved;
    }
}

if (! function_exists('business_type')) {
    /**
     * Get the current business type.
     */
    function business_type(): ?string
    {
        static $resolved = false;
        static $cachedType = null;

        if ($resolved) {
            return $cachedType;
        }

        if (app()->has('outlet')) {
            return app('outlet')->business_type;
        }

        $resolver = function (): ?string {
            try {
                return Setting::get('business_type');
            } catch (\Throwable $e) {
                return null;
            }
        };

        try {
            $cachedType = business_cache_store()->remember('business_type', 60, $resolver);
        } catch (\Throwable $e) {
            $cachedType = $resolver();
        }

        $resolved = true;

        return $cachedType;
    }
}

if (! function_exists('is_valid_business_type')) {
    /**
     * Check whether the given business type exists in configuration.
     */
    function is_valid_business_type(?string $type): bool
    {
        return is_string($type) && in_array($type, array_keys(config('business.types', [])), true);
    }
}

if (! function_exists('reconcile_outlet_business_type')) {
    /**
     * Reconcile legacy business_type settings into the active outlet once.
     */
    function reconcile_outlet_business_type(): ?string
    {
        if (! app()->has('outlet')) {
            return null;
        }

        $outlet = app('outlet');
        $currentType = $outlet->business_type;
        $legacyType = Setting::get('business_type');

        if (! is_valid_business_type($legacyType) || $legacyType === $currentType) {
            return $currentType;
        }

        try {
            $outlet->forceFill([
                'business_type' => $legacyType,
            ])->save();

            $outlet->business_type = $legacyType;
            app()->instance('outlet', $outlet);
            clear_business_cache();

            return $legacyType;
        } catch (\Throwable $e) {
            report($e);

            return $currentType;
        }
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

if (! function_exists('business_config_for_type')) {
    /**
     * Get configuration for a specific business type.
     *
     * @param  string|null  $type  Explicit business type. Falls back to current type.
     * @param  string|null  $key  Dot notation key to get specific config value
     * @param  mixed  $default  Default value if key not found
     */
    function business_config_for_type(?string $type = null, ?string $key = null, mixed $default = null): mixed
    {
        $resolvedType = is_valid_business_type($type)
            ? $type
            : (business_type() ?? config('business.default', 'clinic'));

        $config = config("business.types.{$resolvedType}");

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
    function business_profile_fields(?string $type = null): array
    {
        return business_config_for_type($type, 'profile_fields', []);
    }
}

if (! function_exists('business_profile_options')) {
    /**
     * Get profile field options with localized labels.
     *
     * @param  string  $field  The field name ('type' or 'concerns')
     */
    function business_profile_options(string $field, ?string $type = null): array
    {
        $fields = business_profile_fields($type);
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

if (! function_exists('business_profile_option_keys')) {
    /**
     * Get raw option keys for a profile field.
     *
     * @return array<int, string>
     */
    function business_profile_option_keys(string $field, ?string $type = null): array
    {
        $fields = business_profile_fields($type);

        if (! isset($fields[$field]['options']) || ! is_array($fields[$field]['options'])) {
            return [];
        }

        return array_keys($fields[$field]['options']);
    }
}

if (! function_exists('business_profile_field_label')) {
    /**
     * Get localized label for a profile field.
     */
    function business_profile_field_label(string $field, ?string $type = null): string
    {
        $fields = business_profile_fields($type);
        $locale = app()->getLocale();
        $fieldConfig = $fields[$field] ?? [];

        if ($locale === 'en' && isset($fieldConfig['label_en'])) {
            return $fieldConfig['label_en'];
        }

        return $fieldConfig['label'] ?? $field;
    }
}

if (! function_exists('business_profile_field_required')) {
    /**
     * Determine whether a profile field is required for the current business type.
     */
    function business_profile_field_required(string $field, ?string $type = null): bool
    {
        $fields = business_profile_fields($type);

        return (bool) data_get($fields, "{$field}.required", false);
    }
}

if (! function_exists('clear_business_cache')) {
    /**
     * Clear cached business settings.
     * Call this when settings are updated.
     */
    function clear_business_cache(): void
    {
        foreach (['setup_completed', 'business_type'] as $key) {
            Cache::forget($key);

            if (array_key_exists('file', config('cache.stores', []))) {
                Cache::store('file')->forget($key);
            }
        }
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
        static $memoryCache = [];

        $locale = app()->getLocale();
        $cacheKey = "landing_content.{$locale}.{$key}";
        $dbText = $memoryCache[$cacheKey] ?? null;

        if (! array_key_exists($cacheKey, $memoryCache)) {
            $resolver = function () use ($key, $locale) {
                $content = LandingContent::query()->where('key', $key)->first();
                if (! $content) {
                    return null;
                }

                $value = $content->content[$locale] ?? $content->content['id'] ?? null;

                return is_string($value) && $value !== '' ? $value : null;
            };

            try {
                $dbText = business_cache_store()->remember($cacheKey, 300, $resolver);
            } catch (\Throwable $e) {
                try {
                    $dbText = $resolver();
                } catch (\Throwable $innerException) {
                    $dbText = null;
                }
            }

            $memoryCache[$cacheKey] = $dbText;
        }

        if (is_string($dbText) && $dbText !== '') {
            foreach ($replace as $replaceKey => $replaceValue) {
                $dbText = str_replace(':'.$replaceKey, (string) $replaceValue, $dbText);
            }

            return $dbText;
        }

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
