<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTenantModuleAccessRequest;
use App\Models\OutletRoleModulePermission;
use App\Models\Plan;
use App\Models\Tenant;
use App\Support\Permissions\ModulePermissionRegistry;
use App\Support\Permissions\ModulePermissionResolver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function __construct(
        private readonly ModulePermissionRegistry $modulePermissionRegistry,
        private readonly ModulePermissionResolver $modulePermissionResolver
    ) {}

    public function index(Request $request)
    {
        $tenants = Tenant::with('plan')
            ->withCount('outlets')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('owner_email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20);

        return view('platform.tenants.index', compact('tenants'));
    }

    public function show(Request $request, Tenant $tenant)
    {
        $tenant->load([
            'outlets',
            'plan',
            'invoices' => fn ($query) => $query->with('plan')->latest(),
        ]);
        $plans = Plan::orderBy('sort_order')->get();

        $roles = $this->modulePermissionRegistry->managedRoles();
        $modules = $this->modulePermissionRegistry->modules();
        $lockedMatrix = $this->modulePermissionRegistry->lockedPermissionMatrix();
        $selectedOutlet = $this->resolveSelectedOutlet($request, $tenant);
        $permissionMatrix = $selectedOutlet
            ? $this->modulePermissionResolver->outletPermissionMatrix($selectedOutlet)
            : [];

        return view('platform.tenants.show', compact(
            'tenant',
            'plans',
            'roles',
            'modules',
            'lockedMatrix',
            'selectedOutlet',
            'permissionMatrix'
        ));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'status' => ['required', Rule::in(['trial', 'active', 'suspended', 'expired', 'cancelled'])],
            'subscription_ends_at' => ['nullable', 'date'],
            'is_read_only' => ['nullable', 'boolean'],
        ]);

        $tenant->update([
            'plan_id' => (int) $validated['plan_id'],
            'status' => $validated['status'],
            'subscription_ends_at' => $validated['subscription_ends_at'] ?? null,
            'is_read_only' => (bool) ($validated['is_read_only'] ?? false),
        ]);

        return back()->with('success', 'Konfigurasi tenant berhasil diperbarui.');
    }

    public function toggleStatus(Tenant $tenant)
    {
        $tenant->update([
            'status' => $tenant->status === 'active' ? 'suspended' : 'active',
        ]);

        return back()->with('success', 'Status tenant berhasil diperbarui.');
    }

    public function updateModuleAccess(UpdateTenantModuleAccessRequest $request, Tenant $tenant)
    {
        $validated = $request->validated();
        $outlet = $tenant->outlets()->whereKey((int) $validated['outlet_id'])->firstOrFail();
        $permissions = $this->modulePermissionRegistry->normalizePermissionMatrix($validated['permissions']);

        foreach ($permissions as $role => $rolePermissions) {
            foreach ($rolePermissions as $moduleKey => $isAllowed) {
                OutletRoleModulePermission::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'outlet_id' => $outlet->id,
                        'role' => $role,
                        'module_key' => $moduleKey,
                    ],
                    [
                        'is_allowed' => $isAllowed,
                    ]
                );
            }
        }

        return redirect()
            ->route('platform.tenants.show', [
                'tenant' => $tenant,
                'permissions_outlet' => $outlet->id,
            ])
            ->with('success', 'Permission outlet berhasil diperbarui.');
    }

    private function resolveSelectedOutlet(Request $request, Tenant $tenant)
    {
        $requestedOutletId = (int) $request->integer('permissions_outlet', 0);
        if ($requestedOutletId > 0) {
            $selected = $tenant->outlets->firstWhere('id', $requestedOutletId);
            if ($selected) {
                return $selected;
            }
        }

        return $tenant->outlets->first();
    }
}
