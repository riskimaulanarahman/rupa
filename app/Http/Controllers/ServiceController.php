<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Service::with('category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->latest()->paginate(10)->withQueryString();
        $categories = ServiceCategory::active()->ordered()->get();

        return view('services.index', compact('services', 'categories'));
    }

    public function create(): View
    {
        $categories = ServiceCategory::active()->ordered()->get();

        return view('services.create', compact('categories'));
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $data = $this->normalizePricingData($request->validated());
        $data['incentive'] = $data['incentive'] ?? 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        Service::create($data);

        return redirect()->route('services.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service): View
    {
        $categories = ServiceCategory::active()->ordered()->get();

        return view('services.edit', compact('service', 'categories'));
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $data = $this->normalizePricingData($request->validated());
        $data['incentive'] = $data['incentive'] ?? 0;

        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update($data);

        return redirect()->route('services.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if ($service->appointments()->count() > 0) {
            return back()->with('error', 'Layanan tidak dapat dihapus karena sudah memiliki appointment.');
        }

        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }

    public function toggleActive(Service $service): RedirectResponse
    {
        $service->update(['is_active' => !$service->is_active]);

        $status = $service->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Layanan berhasil {$status}.");
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizePricingData(array $data): array
    {
        $pricingMode = $data['pricing_mode'] ?? Service::PRICING_MODE_FIXED;
        $data['pricing_mode'] = $pricingMode;

        if ($pricingMode === Service::PRICING_MODE_RANGE) {
            $data['price_min'] = (float) ($data['price_min'] ?? 0);
            $data['price_max'] = (float) ($data['price_max'] ?? $data['price_min']);
            $data['price'] = $data['price_min'];

            return $data;
        }

        $data['price'] = (float) ($data['price'] ?? 0);
        $data['price_min'] = $data['price'];
        $data['price_max'] = $data['price'];

        return $data;
    }
}
