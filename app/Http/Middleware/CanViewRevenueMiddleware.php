<?php

namespace App\Http\Middleware;

use App\Support\Auth\LoginRedirectResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanViewRevenueMiddleware
{
    public function __construct(private readonly LoginRedirectResolver $loginRedirectResolver) {}

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->to($this->loginRedirectResolver->staffLoginUrl($request));
        }

        if (! $user->canViewRevenue()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke data revenue.',
                ], 403);
            }

            abort(403, 'Anda tidak memiliki akses ke data revenue.');
        }

        return $next($request);
    }
}
