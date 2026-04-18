<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();

        return view('platform.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('platform.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:plans,slug',
            'price_monthly' => 'required|integer|min:0',
            'price_yearly' => 'required|integer|min:0',
            'max_outlets' => 'nullable|integer|min:1',
            'trial_days' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'required|integer|min:0',
            'features' => 'nullable|array',
        ]);

        Plan::create(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ]));

        return redirect()->route('platform.plans.index')->with('success', 'Paket berhasil dibuat.');
    }

    public function edit(Plan $plan)
    {
        return view('platform.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:plans,slug,'.$plan->id,
            'price_monthly' => 'required|integer|min:0',
            'price_yearly' => 'required|integer|min:0',
            'max_outlets' => 'nullable|integer|min:1',
            'trial_days' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'required|integer|min:0',
            'features' => 'nullable|array',
        ]);

        $plan->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ]));

        return redirect()->route('platform.plans.index')->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->tenants()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus paket yang masih digunakan oleh tenant.');
        }

        $plan->delete();

        return redirect()->route('platform.plans.index')->with('success', 'Paket berhasil dihapus.');
    }
}
