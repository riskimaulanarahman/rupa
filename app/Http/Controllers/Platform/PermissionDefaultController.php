<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateModulePermissionDefaultsRequest;
use App\Models\ModulePermissionDefault;
use App\Support\Permissions\ModulePermissionRegistry;
use App\Support\Permissions\ModulePermissionResolver;

class PermissionDefaultController extends Controller
{
    public function __construct(
        private readonly ModulePermissionRegistry $modulePermissionRegistry,
        private readonly ModulePermissionResolver $modulePermissionResolver
    ) {}

    public function index()
    {
        $roles = $this->modulePermissionRegistry->managedRoles();
        $modules = $this->modulePermissionRegistry->modules();
        $matrix = $this->modulePermissionResolver->defaultPermissionMatrix();
        $lockedMatrix = $this->modulePermissionRegistry->lockedPermissionMatrix();

        return view('platform.permissions.defaults', compact('roles', 'modules', 'matrix', 'lockedMatrix'));
    }

    public function update(UpdateModulePermissionDefaultsRequest $request)
    {
        $permissions = $this->modulePermissionRegistry->normalizePermissionMatrix(
            $request->validated('permissions', [])
        );

        foreach ($permissions as $role => $rolePermissions) {
            foreach ($rolePermissions as $moduleKey => $isAllowed) {

                ModulePermissionDefault::query()->updateOrCreate(
                    [
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
            ->route('platform.permissions.defaults')
            ->with('success', 'Default permission berhasil diperbarui.');
    }
}
