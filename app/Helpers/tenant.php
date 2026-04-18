<?php

use App\Models\Outlet;
use App\Models\Tenant;

if (! function_exists('tenant')) {
    /**
     * Get the current tenant instance.
     */
    function tenant(): ?Tenant
    {
        return app()->has('tenant') ? app('tenant') : null;
    }
}

if (! function_exists('outlet')) {
    /**
     * Get the current outlet instance.
     */
    function outlet(): ?Outlet
    {
        return app()->has('outlet') ? app('outlet') : null;
    }
}

if (! function_exists('tenant_id')) {
    /**
     * Get the current tenant ID.
     */
    function tenant_id(): ?int
    {
        return app()->has('tenant_id') ? app('tenant_id') : null;
    }
}

if (! function_exists('outlet_id')) {
    /**
     * Get the current outlet ID.
     */
    function outlet_id(): ?int
    {
        return app()->has('outlet_id') ? app('outlet_id') : null;
    }
}

if (! function_exists('customer_route')) {
    /**
     * Generate outlet-scoped customer route URL.
     */
    function customer_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $routeName = str_starts_with($name, 'outlet.customer.')
            ? $name
            : "outlet.customer.{$name}";

        if (! is_array($parameters)) {
            $parameters = ['id' => $parameters];
        }

        if (! array_key_exists('outletSlug', $parameters)) {
            $contextOutlet = outlet();

            if (! $contextOutlet && auth('customer')->check()) {
                /** @var \App\Models\Customer $customer */
                $customer = auth('customer')->user();
                $contextOutlet = $customer->relationLoaded('outlet')
                    ? $customer->outlet
                    : $customer->outlet()->first();
            }

            if ($contextOutlet && is_string($contextOutlet->slug) && $contextOutlet->slug !== '') {
                $parameters['outletSlug'] = $contextOutlet->slug;
            }
        }

        return route($routeName, $parameters, $absolute);
    }
}

if (! function_exists('customer_home_url')) {
    /**
     * Resolve outlet-aware home URL for customer-facing pages.
     */
    function customer_home_url(): string
    {
        $contextOutlet = outlet();

        if (! $contextOutlet && auth('customer')->check()) {
            /** @var \App\Models\Customer $customer */
            $customer = auth('customer')->user();
            $contextOutlet = $customer->relationLoaded('outlet')
                ? $customer->outlet
                : $customer->outlet()->first();
        }

        if ($contextOutlet && is_string($contextOutlet->slug) && $contextOutlet->slug !== '') {
            return route('outlet.landing.show', ['outletSlug' => $contextOutlet->slug]);
        }

        return route('home');
    }
}

if (! function_exists('customer_booking_url')) {
    /**
     * Resolve booking URL for outlet customer pages.
     */
    function customer_booking_url(): string
    {
        $contextOutlet = outlet();

        if (! $contextOutlet && auth('customer')->check()) {
            /** @var \App\Models\Customer $customer */
            $customer = auth('customer')->user();
            $contextOutlet = $customer->relationLoaded('outlet')
                ? $customer->outlet
                : $customer->outlet()->first();
        }

        if ($contextOutlet && is_string($contextOutlet->slug) && $contextOutlet->slug !== '') {
            return route('outlet.booking.index', ['outletSlug' => $contextOutlet->slug]);
        }

        return route('booking.index');
    }
}
