<?php

namespace App\Http\Middleware;

use App\Models\Outlet;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $mainDomain = parse_url(config('app.url'), PHP_URL_HOST);

        // Skip for main platform domain or local development
        if ($host === $mainDomain || $host === 'www.'.$mainDomain ||
            ($host === 'localhost' || $host === '127.0.0.1')) {
            $this->setContextFromAuthenticatedUser($request);

            return $next($request);
        }

        // Find outlet by subdomain
        $outlet = Outlet::where('full_subdomain', $host)
            ->orWhere('custom_domain', $host)
            ->with('tenant')
            ->first();

        if (! $outlet || ! $outlet->tenant) {
            abort(404, 'Bisnis tidak ditemukan.');
        }

        $user = auth()->guard('web')->user();
        $isTenantMismatch = $user
            && $user->tenant_id
            && (int) $user->tenant_id !== (int) $outlet->tenant_id;

        if ($isTenantMismatch && ! in_array(config('app.env'), ['local', 'testing'], true)) {
            $message = __('tenant.tenant_mismatch_forbidden');

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            abort(403, $message);
        }

        // Share context with app container
        app()->instance('tenant', $outlet->tenant);
        app()->instance('outlet', $outlet);
        app()->instance('tenant_id', $outlet->tenant_id);
        app()->instance('outlet_id', $outlet->id);

        return $next($request);
    }

    private function setContextFromAuthenticatedUser(Request $request): void
    {
        $user = auth()->guard('web')->user();

        if (! $user || ! $user->tenant_id) {
            return;
        }

        $tenant = $user->relationLoaded('tenant')
            ? $user->tenant
            : $user->tenant()->first();

        if (! $tenant) {
            return;
        }

        $outlet = null;

        if ($user->isOwner()) {
            $activeOutletId = (int) $request->session()->get('active_outlet_id');

            if ($activeOutletId > 0) {
                $outlet = $tenant->outlets()
                    ->whereKey($activeOutletId)
                    ->where('status', 'active')
                    ->first();
            }

            if (! $outlet) {
                $ownerOutlet = $user->relationLoaded('outlet')
                    ? $user->outlet
                    : $user->outlet()->first();

                if ($ownerOutlet && $ownerOutlet->status === 'active') {
                    $outlet = $ownerOutlet;
                }
            }

            if (! $outlet) {
                $outlet = $tenant->outlets()->active()->first() ?? $tenant->outlets()->first();
            }

            if ($outlet) {
                $request->session()->put('active_outlet_id', $outlet->id);
                $request->session()->put('outlet_slug', $outlet->slug);
            }
        } else {
            $outlet = $user->relationLoaded('outlet')
                ? $user->outlet
                : $user->outlet()->first();

            if (! $outlet && $user->outlet_id) {
                $outlet = $tenant->outlets()->whereKey($user->outlet_id)->first();
            }
        }

        app()->instance('tenant', $tenant);
        app()->instance('tenant_id', $tenant->id);

        if ($outlet) {
            app()->instance('outlet', $outlet);
            app()->instance('outlet_id', $outlet->id);
        }
    }
}
