<?php

namespace App\Http\Middleware\Api;

use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenantFromHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantSlug = $request->header('X-Tenant-Slug');
        $outletSlug = $request->header('X-Outlet-Slug');

        if (! $tenantSlug && ! $outletSlug) {
            if (app()->environment('testing')) {
                return $next($request);
            }

            return response()->json(['message' => 'Tenant or Outlet identifier missing.'], 400);
        }

        $tenant = null;
        $outlet = null;

        if ($outletSlug) {
            $outlet = Outlet::where('slug', $outletSlug)
                ->orWhere('full_subdomain', $outletSlug)
                ->with('tenant')
                ->first();

            if (! $outlet || ! $outlet->tenant) {
                return response()->json(['message' => 'Outlet not found.'], 404);
            }
            $tenant = $outlet->tenant;
        } elseif ($tenantSlug) {
            $tenant = Tenant::where('slug', $tenantSlug)->first();
            if (! $tenant) {
                return response()->json(['message' => 'Tenant not found.'], 404);
            }
        }

        // Share context globally using standard names from app/Helpers/tenant.php
        app()->instance('tenant', $tenant);
        app()->instance('tenant_id', $tenant->id);

        if ($outlet) {
            app()->instance('outlet', $outlet);
            app()->instance('outlet_id', $outlet->id);
        }

        $this->ensureAuthenticatedUserCanAccessContext($request, $tenant, $outlet);

        return $next($request);
    }

    private function ensureAuthenticatedUserCanAccessContext(Request $request, Tenant $tenant, ?Outlet $outlet): void
    {
        $user = $request->user('sanctum') ?? $request->user();
        if (! $user instanceof User || $user->isSuperAdmin()) {
            return;
        }

        if ($user->tenant_id && (int) $user->tenant_id !== (int) $tenant->id) {
            abort(403, 'Akses tenant tidak valid.');
        }

        if (! $outlet) {
            if ($user->isOwner()) {
                return;
            }

            abort(403, 'Akses outlet tidak valid.');
        }

        if ($user->isOwner()) {
            if ((int) $outlet->tenant_id !== (int) $tenant->id) {
                abort(403, 'Akses outlet tidak valid.');
            }

            return;
        }

        if ((int) ($user->outlet_id ?? 0) !== (int) $outlet->id) {
            abort(403, 'Akses outlet tidak valid.');
        }
    }
}
