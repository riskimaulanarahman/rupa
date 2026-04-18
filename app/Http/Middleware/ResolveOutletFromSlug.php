<?php

namespace App\Http\Middleware;

use App\Models\Outlet;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveOutletFromSlug
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $outletSlug = $request->route('outletSlug');

        if (! is_string($outletSlug) || $outletSlug === '') {
            abort(404);
        }

        if (Outlet::isReservedSlug($outletSlug)) {
            abort(404);
        }

        $matchedOutlets = Outlet::query()
            ->with('tenant')
            ->where('slug', $outletSlug)
            ->where('status', 'active')
            ->limit(2)
            ->get();

        if ($matchedOutlets->count() > 1) {
            abort(404, 'Slug outlet tidak unik. Hubungi administrator.');
        }

        $outlet = $matchedOutlets->first();

        if (! $outlet || ! $outlet->tenant) {
            abort(404, 'Outlet tidak ditemukan.');
        }

        app()->instance('tenant', $outlet->tenant);
        app()->instance('outlet', $outlet);
        app()->instance('tenant_id', $outlet->tenant_id);
        app()->instance('outlet_id', $outlet->id);

        $request->session()->put('outlet_slug', $outlet->slug);
        View::share('currentOutlet', $outlet);

        return $next($request);
    }
}
