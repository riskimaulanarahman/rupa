<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('customer')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('outlet.customer.login', [
                'outletSlug' => $this->resolveOutletSlug($request),
            ]));
        }

        $customer = Auth::guard('customer')->user();
        $currentOutletId = (int) (outlet_id() ?? 0);
        $customerOutletId = (int) ($customer?->outlet_id ?? 0);

        if ($currentOutletId > 0 && $customerOutletId > 0 && $customerOutletId !== $currentOutletId) {
            Auth::guard('customer')->logout();

            return redirect()->guest(route('outlet.customer.login', [
                'outletSlug' => $this->resolveOutletSlug($request),
            ]));
        }

        return $next($request);
    }

    private function resolveOutletSlug(Request $request): string
    {
        $routeSlug = $request->route('outletSlug');
        if (is_string($routeSlug) && $routeSlug !== '') {
            return $routeSlug;
        }

        $outlet = outlet();
        if ($outlet && is_string($outlet->slug) && $outlet->slug !== '') {
            return $outlet->slug;
        }

        abort(404, 'Outlet tidak ditemukan.');
    }
}
