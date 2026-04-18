<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Get tenant info by slug (for app initialization)
     */
    public function show(string $slug): JsonResponse
    {
        $tenant = Tenant::where('slug', $slug)
            ->with(['plan'])
            ->first();

        if (! $tenant) {
            return response()->json(['message' => 'Tenant tidak ditemukan.'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'status' => $tenant->status,
                'plan' => [
                    'name' => $tenant->plan->name ?? null,
                    'max_outlets' => $tenant->plan->max_outlets ?? null,
                ],
            ],
        ]);
    }

    /**
     * Get list of outlets for the current authenticated user's tenant
     */
    public function outlets(Request $request): JsonResponse
    {
        $tenant = tenant();
        if (! $tenant) {
            return response()->json(['message' => 'Tenant context missing.'], 403);
        }

        $user = $request->user();

        $outlets = $tenant->outlets()
            ->active()
            ->when($user && ! $user->isOwner(), function ($query) use ($user) {
                $query->whereKey($user->outlet_id);
            })
            ->get(['id', 'name', 'slug', 'full_subdomain', 'business_type', 'address']);

        return response()->json([
            'data' => $outlets,
        ]);
    }

    /**
     * Switch current outlet (mobile context helper)
     */
    public function switchOutlet(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_slug' => 'required|string|exists:outlets,slug',
        ]);

        $outlet = Outlet::where('slug', $request->outlet_slug)
            ->where('tenant_id', tenant_id())
            ->first();

        if (! $outlet) {
            return response()->json(['message' => 'Outlet tidak ditemukan di tenant ini.'], 404);
        }

        $user = $request->user();
        if ($user && ! $user->isOwner() && (int) ($user->outlet_id ?? 0) !== (int) $outlet->id) {
            return response()->json(['message' => 'Anda tidak dapat mengganti outlet ini.'], 403);
        }

        return response()->json([
            'message' => 'Outlet switched successfully',
            'data' => [
                'id' => $outlet->id,
                'name' => $outlet->name,
                'slug' => $outlet->slug,
                'business_type' => $outlet->business_type,
            ],
        ]);
    }
}
