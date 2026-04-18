<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\Auth\LoginRedirectResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly LoginRedirectResolver $loginRedirectResolver) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                ])->withInput($request->only('email'));
            }

            $request->session()->regenerate();

            $response = null;
            if ($user->isSuperAdmin()) {
                $request->session()->forget('url.intended');
                $response = redirect()->route('platform.dashboard');
            } elseif (! $user->canViewRevenue()) {
                $request->session()->forget('url.intended');
                $response = redirect()->route('appointments.index');
            } else {
                $response = redirect()->intended(route('dashboard'));
            }

            $outletSlug = $this->resolveOutletSlugFromContext($request);
            if ($outletSlug !== null) {
                $response->withCookie(cookie(
                    LoginRedirectResolver::STAFF_OUTLET_COOKIE,
                    $outletSlug,
                    LoginRedirectResolver::COOKIE_MINUTES
                ));
            }

            return $response;
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $redirectUrl = $user && $user->isSuperAdmin()
            ? route('login')
            : $this->loginRedirectResolver->staffLoginUrl($request);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to($redirectUrl);
    }

    private function resolveOutletSlugFromContext(Request $request): ?string
    {
        $routeSlug = $request->route('outletSlug');
        if (is_string($routeSlug) && $routeSlug !== '') {
            return $routeSlug;
        }

        $contextOutlet = outlet();
        if ($contextOutlet && is_string($contextOutlet->slug) && $contextOutlet->slug !== '') {
            return $contextOutlet->slug;
        }

        if (! $request->hasSession()) {
            return null;
        }

        $sessionSlug = $request->session()->get('outlet_slug');

        return is_string($sessionSlug) && $sessionSlug !== '' ? $sessionSlug : null;
    }
}
