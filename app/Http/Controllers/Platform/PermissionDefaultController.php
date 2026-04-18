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

        return view('platform.permissions.defaults', compact('roles', 'modules', 'matrix'));
    }

    public function update(UpdateModulePermissionDefaultsRequest $request)
    {
        $permissions = $request->validated('permissions', []);
        $roles = $this->modulePermissionRegistry->managedRoles();
        $modules = $this->modulePermissionRegistry->moduleKeys();

        foreach ($roles as $role) {
            foreach ($modules as $moduleKey) {
                $isAllowed = (bool) data_get($permissions, "{$role}.{$moduleKey}", false);

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
