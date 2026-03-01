<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    /**
     * Handle an incoming request.
     *
     * Check if the requested feature is enabled for the current business type.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $feature  The feature to check (e.g., 'treatment_records', 'packages')
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! has_feature($feature)) {
            // For API requests, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('common.feature_not_available'),
                    'feature' => $feature,
                ], 403);
            }

            // For web requests, redirect with error message
            return redirect()
                ->route('dashboard')
                ->with('error', __('common.feature_not_available'));
        }

        return $next($request);
    }
}
