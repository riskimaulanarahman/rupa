<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantHQController extends Controller
{
    /**
     * Show HQ Dashboard (Owner only)
     */
    public function index()
    {
        $tenant = tenant();
        $outlets = $tenant->outlets()->withCount(['customers', 'appointments'])->get();

        return view('tenant.hq.index', compact('tenant', 'outlets'));
    }

    /**
     * List all outlets
     */
    public function outlets()
    {
        $outlets = tenant()->outlets()->latest()->paginate(10);

        return view('tenant.outlets.index', compact('outlets'));
    }

    /**
     * Show create outlet form
     */
    public function createOutlet()
    {
        $tenant = tenant();

        if (! $tenant || ! $tenant->canAddOutlet()) {
            return $this->redirectToBillingForPlanLimit($tenant);
        }

        return view('tenant.outlets.create');
    }

    /**
     * Store new outlet
     */
    public function storeOutlet(Request $request)
    {
        $tenant = tenant();

        if (! $tenant || ! $tenant->canAddOutlet()) {
            return $this->redirectToBillingForPlanLimit($tenant);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_type' => 'required|in:clinic,salon,barbershop',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        $limitReachedPlanName = null;

        $outlet = DB::transaction(function () use ($tenant, $validated, &$limitReachedPlanName) {
            /** @var Tenant|null $lockedTenant */
            $lockedTenant = Tenant::query()
                ->with('plan')
                ->lockForUpdate()
                ->find($tenant->id);

            if (! $lockedTenant || ! $lockedTenant->canAddOutlet()) {
                $limitReachedPlanName = $lockedTenant?->plan?->name;

                return null;
            }

            $slug = Outlet::generateUniquePublicSlug($validated['name']);

            return $lockedTenant->outlets()->create([
                'name' => $validated['name'],
                'slug' => $slug,
                'full_subdomain' => $lockedTenant->slug.'-'.$slug.'.'.config('app.domain', 'rupa.id'),
                'business_type' => $validated['business_type'],
                'status' => 'active',
                'address' => $validated['address'],
                'city' => $validated['city'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
            ]);
        });

        if (! $outlet) {
            return $this->redirectToBillingForPlanLimit($tenant, $limitReachedPlanName);
        }

        return redirect()
            ->route('tenant.outlets.index')
            ->with('success', __('tenant.outlet_created_success', ['name' => $outlet->name]));
    }

    /**
     * Toggle outlet status
     */
    public function toggleOutlet(Request $request, Outlet $outlet): RedirectResponse
    {
        // Ensure outlet belongs to tenant
        if ($outlet->tenant_id !== tenant_id()) {
            abort(403);
        }

        $tenant = tenant();
        if (! $tenant) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        $nextStatus = $outlet->status === 'active' ? 'inactive' : 'active';
        $isDeactivating = $nextStatus === 'inactive';

        if ($isDeactivating) {
            $activeOutletCount = (int) $tenant->outlets()->where('status', 'active')->count();
            if ($activeOutletCount <= 1) {
                return back()->with('error', 'Minimal harus ada satu outlet aktif.');
            }
        }

        $outlet->update([
            'status' => $nextStatus,
        ]);

        $activeOutletId = (int) $request->session()->get('active_outlet_id');
        if ($isDeactivating && $activeOutletId === (int) $outlet->id) {
            $fallbackOutlet = $tenant->outlets()
                ->where('status', 'active')
                ->whereKeyNot($outlet->id)
                ->orderBy('name')
                ->first();

            if ($fallbackOutlet) {
                $request->session()->put('active_outlet_id', $fallbackOutlet->id);
                $request->session()->put('outlet_slug', $fallbackOutlet->slug);
            }
        }

        return back()->with('success', 'Status outlet berhasil diperbarui.');
    }

    private function redirectToBillingForPlanLimit(?Tenant $tenant, ?string $planName = null): RedirectResponse
    {
        $planName = $planName ?? $tenant?->plan?->name ?? '-';

        return redirect()
            ->route('tenant.billing.index')
            ->with('error', __('tenant.outlet_limit_reached', ['plan' => $planName]));
    }
}
