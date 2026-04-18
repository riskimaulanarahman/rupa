<?php

namespace App\Support\Auth;

use App\Models\Outlet;
use Illuminate\Http\Request;

class LoginRedirectResolver
{
    public const STAFF_OUTLET_COOKIE = 'last_outlet_slug';

    public const CUSTOMER_OUTLET_COOKIE = 'last_customer_outlet_slug';

    public const COOKIE_MINUTES = 43200; // 30 days

    /**
     * @var array<string, bool>
     */
    private array $slugValidationCache = [];

    public function resolveOutletSlug(Request $request): ?string
    {
        $candidates = [];

        $routeSlug = $request->route('outletSlug');
        if (is_string($routeSlug) && $routeSlug !== '') {
            $candidates[] = $routeSlug;
        }

        $contextOutlet = outlet();
        if ($contextOutlet && is_string($contextOutlet->slug) && $contextOutlet->slug !== '') {
            $candidates[] = $contextOutlet->slug;
        }

        if ($request->hasSession()) {
            $sessionSlug = $request->session()->get('outlet_slug');
            if (is_string($sessionSlug) && $sessionSlug !== '') {
                $candidates[] = $sessionSlug;
            }
        }

        $customerCookieSlug = $request->cookie(self::CUSTOMER_OUTLET_COOKIE);
        if (is_string($customerCookieSlug) && $customerCookieSlug !== '') {
            $candidates[] = $customerCookieSlug;
        }

        $staffCookieSlug = $request->cookie(self::STAFF_OUTLET_COOKIE);
        if (is_string($staffCookieSlug) && $staffCookieSlug !== '') {
            $candidates[] = $staffCookieSlug;
        }

        foreach ($candidates as $candidate) {
            $normalized = trim($candidate);
            if ($normalized !== '' && $this->isValidOutletSlug($normalized)) {
                return $normalized;
            }
        }

        return null;
    }

    public function staffLoginUrl(Request $request): string
    {
        if ($this->isPlatformRoute($request)) {
            return route('login');
        }

        $outletSlug = $this->resolveOutletSlug($request);
        if ($outletSlug !== null) {
            return route('outlet.login', ['outletSlug' => $outletSlug]);
        }

        return route('login');
    }

    public function customerLoginUrl(Request $request): string
    {
        $outletSlug = $this->resolveOutletSlug($request);
        if ($outletSlug !== null) {
            return route('outlet.customer.login', ['outletSlug' => $outletSlug]);
        }

        return route('login');
    }

    private function isPlatformRoute(Request $request): bool
    {
        $route = $request->route();

        return $route !== null && $route->named('platform.*');
    }

    private function isValidOutletSlug(string $slug): bool
    {
        if (array_key_exists($slug, $this->slugValidationCache)) {
            return $this->slugValidationCache[$slug];
        }

        $exists = Outlet::query()->where('slug', $slug)->exists();
        $this->slugValidationCache[$slug] = $exists;

        return $exists;
    }
}
