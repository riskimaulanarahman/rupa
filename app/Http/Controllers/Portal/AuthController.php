<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Outlet;
use App\Support\Auth\LoginRedirectResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly LoginRedirectResolver $loginRedirectResolver) {}

    public function showLogin(): View
    {
        return view('portal.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $outlet = $this->resolveOutlet();

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::query()
            ->where('email', $request->email)
            ->where('outlet_id', $outlet->id)
            ->first();

        if (! $customer || ! $customer->password) {
            return back()->withErrors([
                'email' => __('portal.invalid_credentials'),
            ])->withInput();
        }

        if (! Hash::check($request->password, $customer->password)) {
            return back()->withErrors([
                'email' => __('portal.invalid_credentials'),
            ])->withInput();
        }

        Auth::guard('customer')->login($customer, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()
            ->intended(route('outlet.customer.dashboard', ['outletSlug' => $outlet->slug]))
            ->with('success', __('portal.login_success'))
            ->withCookie(cookie(
                LoginRedirectResolver::CUSTOMER_OUTLET_COOKIE,
                $outlet->slug,
                LoginRedirectResolver::COOKIE_MINUTES
            ));
    }

    public function showRegister(): View
    {
        return view('portal.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $outlet = $this->resolveOutlet();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('customers', 'email')->where(fn ($query) => $query->where('outlet_id', $outlet->id)),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'unique:customers,phone',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code' => ['nullable', 'string', 'max:20'],
        ]);

        $referrerId = null;

        if ($request->filled('referral_code') && config('referral.enabled', true)) {
            $referrer = Customer::query()
                ->where('referral_code', $request->referral_code)
                ->where('outlet_id', $outlet->id)
                ->first();
            if ($referrer) {
                $referrerId = $referrer->id;
            }
        }

        $customer = Customer::create([
            'tenant_id' => $outlet->tenant_id,
            'outlet_id' => $outlet->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'referred_by_id' => $referrerId,
        ]);

        Auth::guard('customer')->login($customer);

        $request->session()->regenerate();

        return redirect()
            ->route('outlet.customer.dashboard', ['outletSlug' => $outlet->slug])
            ->with('success', __('portal.register_success'))
            ->withCookie(cookie(
                LoginRedirectResolver::CUSTOMER_OUTLET_COOKIE,
                $outlet->slug,
                LoginRedirectResolver::COOKIE_MINUTES
            ));
    }

    public function logout(Request $request): RedirectResponse
    {
        $redirectUrl = $this->loginRedirectResolver->customerLoginUrl($request);

        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to($redirectUrl)
            ->with('success', __('portal.logout_success'));
    }

    private function resolveOutlet(): Outlet
    {
        $outlet = outlet();
        if (! $outlet) {
            abort(404, 'Outlet tidak ditemukan.');
        }

        return $outlet;
    }
}
