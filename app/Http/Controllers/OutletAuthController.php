<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Support\Auth\LoginRedirectResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OutletAuthController extends Controller
{
    public function __construct(private readonly LoginRedirectResolver $loginRedirectResolver) {}

    public function showLogin(Request $request): View
    {
        return view('outlet.auth.login', [
            'outlet' => outlet(),
            'outletSlug' => $request->route('outletSlug'),
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $outlet = outlet();

        if (! $outlet) {
            abort(404, 'Outlet tidak ditemukan.');
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])->withInput($request->only('email'));
        }

        $user = Auth::user();

        if (! $user || ! $user->is_active) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
            ])->withInput($request->only('email'));
        }

        if ($user->isSuperAdmin()) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Akun superadmin harus login dari halaman utama.',
            ])->withInput($request->only('email'));
        }

        $tenantMismatch = (int) $user->tenant_id !== (int) $outlet->tenant_id;
        if ($tenantMismatch) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Akun Anda tidak terdaftar pada outlet ini.',
            ])->withInput($request->only('email'));
        }

        if (! $user->isOwner() && (int) $user->outlet_id !== (int) $outlet->id) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Akun Anda tidak memiliki akses ke outlet ini.',
            ])->withInput($request->only('email'));
        }

        $request->session()->regenerate();
        $request->session()->put('active_outlet_id', $outlet->id);
        $request->session()->put('outlet_slug', $outlet->slug);

        $response = null;
        if ($user->isBeautician()) {
            $response = redirect()->route('dashboard');
        } elseif (! $user->canViewRevenue()) {
            $response = redirect()->route('appointments.index');
        } else {
            $response = redirect()->route('dashboard');
        }

        return $response->withCookie(cookie(
            LoginRedirectResolver::STAFF_OUTLET_COOKIE,
            $outlet->slug,
            LoginRedirectResolver::COOKIE_MINUTES
        ));
    }

    public function logout(Request $request): RedirectResponse
    {
        $redirectUrl = $this->loginRedirectResolver->staffLoginUrl($request);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to($redirectUrl);
    }
}
