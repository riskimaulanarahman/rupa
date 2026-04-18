<?php

namespace App\Http\Middleware;

use App\Support\Auth\LoginRedirectResolver;
use App\Support\Permissions\ModulePermissionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanAccessModuleMiddleware
{
    public function __construct(
        private readonly LoginRedirectResolver $loginRedirectResolver,
        private readonly ModulePermissionResolver $modulePermissionResolver
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
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

        if (! $this->modulePermissionResolver->canAccessModuleForUser($user, $moduleKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke modul ini.',
                    'module' => $moduleKey,
                ], 403);
            }

            abort(403, 'Anda tidak memiliki akses ke modul ini.');
        }

        return $next($request);
    }
}
