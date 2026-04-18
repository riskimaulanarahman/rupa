<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Routes that must remain writable even when subscription is expired/read-only.
     *
     * @var array<int, string>
     */
    private array $recoveryRoutes = [
        'tenant.billing.*',
        'tenant.outlets.*',
        'tenant.hq.*',
        'logout',
        'outlet.logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('subscription.expired') || $request->routeIs('platform.*')) {
            return $next($request);
        }

        if (! app()->has('tenant') || ! app()->has('outlet')) {
            return $next($request);
        }

        $tenant = app('tenant');
        $outlet = app('outlet');
        $isRecoveryRoute = $request->routeIs(...$this->recoveryRoutes);

        // Check if tenant is suspended
        if ($tenant->status === 'suspended') {
            abort(403, 'Akun Anda ditangguhkan. Silakan hubungi pusat bantuan.');
        }

        // Check if outlet is inactive
        if ($outlet->status === 'inactive' && ! $isRecoveryRoute) {
            abort(403, 'Cabang ini sedang dinonaktifkan.');
        }

        // Check for expiration / read-only
        if ($tenant->is_read_only || $tenant->status === 'expired') {
            // Allow only GET requests to view data, or specific routes
            if (! $request->isMethod('GET') && ! $isRecoveryRoute) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Masa langganan habis. Silakan perpanjang untuk melanjutkan.'], 403);
                }

                return redirect()->route('subscription.expired');
            }
        }

        return $next($request);
    }
}
