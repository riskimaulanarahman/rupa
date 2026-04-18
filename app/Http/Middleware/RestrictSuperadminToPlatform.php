<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictSuperadminToPlatform
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if (! $user instanceof User || ! $user->isSuperAdmin()) {
            return $next($request);
        }

        if ($request->routeIs('platform.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Superadmin hanya dapat mengakses halaman platform.',
            ], 403);
        }

        return redirect()->route('platform.dashboard');
    }
}
