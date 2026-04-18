<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Show plan selection
     */
    public function index(): View
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('register.index', compact('plans'));
    }

    /**
     * Show registration form for a specific plan
     */
    public function showForm(Plan $plan): View
    {
        return view('register.form', compact('plan'));
    }

    /**
     * Process registration: Create Tenant, Outlet, and Owner Account
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|in:clinic,salon,barbershop',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'subdomain' => 'required|string|alpha_dash|unique:tenants,slug|min:3',
        ], [
            'subdomain.unique' => 'Subdomain ini sudah digunakan.',
            'subdomain.alpha_dash' => 'Subdomain hanya boleh berisi huruf, angka, dan strip.',
        ]);

        return DB::transaction(function () use ($validated) {
            $plan = Plan::findOrFail($validated['plan_id']);

            // 1. Create Tenant (The Network Owner)
            $tenant = Tenant::create([
                'name' => $validated['business_name'],
                'slug' => strtolower($validated['subdomain']),
                'plan_id' => $plan->id,
                'owner_name' => $validated['owner_name'],
                'owner_email' => $validated['owner_email'],
                'status' => 'trial',
                'trial_ends_at' => now()->addDays($plan->trial_days),
            ]);

            // 2. Create the first Outlet (Main Branch)
            $mainDomain = parse_url(config('app.url'), PHP_URL_HOST);
            $fullSubdomain = "{$tenant->slug}.{$mainDomain}";
            $mainOutletSlug = Outlet::generateUniquePublicSlug($tenant->slug.'-pusat');

            $outlet = $tenant->outlets()->create([
                'tenant_id' => $tenant->id, // Redundant but explicit
                'name' => 'Pusat',
                'slug' => $mainOutletSlug,
                'full_subdomain' => $fullSubdomain,
                'business_type' => $validated['business_type'],
                'status' => 'active',
            ]);

            // 3. Create the Owner User Account
            // We bypass the global scopes here implicitly since we are creating the base user
            // But we must provide the IDs manually as they are NOT in the app container yet.
            $user = User::create([
                'tenant_id' => $tenant->id,
                'outlet_id' => $outlet->id,
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['password']),
                'role' => 'owner',
                'is_active' => true,
            ]);

            // Optional: Initialize sample data for this specific outlet if needed
            // (Similar to SetupController@createSampleData)

            return redirect()->route('register.success', [
                'host' => $fullSubdomain,
                'name' => $tenant->name,
            ]);
        });
    }

    /**
     * Show registration success page
     */
    public function success(Request $request): View
    {
        $host = $request->query('host');
        $name = $request->query('name');

        if (! $host) {
            return redirect()->route('home');
        }

        return view('register.success', compact('host', 'name'));
    }
}
