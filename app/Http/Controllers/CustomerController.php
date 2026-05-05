<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('skin_type')) {
            $query->where('skin_type', $request->skin_type);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['name', 'total_visits', 'total_spent', 'last_visit', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle referral code
        if (! empty($data['referral_code']) && config('referral.enabled', true)) {
            $referrer = Customer::where('referral_code', $data['referral_code'])->first();
            if ($referrer) {
                $data['referred_by_id'] = $referrer->id;
            }
        }
        unset($data['referral_code']);

        Customer::create($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function show(Customer $customer): View
    {
        $customer->loadCount('appointments');
        $customer->load([
            'treatmentRecords' => function ($query) {
                $query->with(['appointment.service', 'staff'])
                    ->latest()
                    ->limit(10);
            },
            'packages' => function ($query) {
                $query->with('package')
                    ->latest()
                    ->limit(5);
            },
        ]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil dihapus.');
    }

    /**
     * Get customer loyalty points (internal API)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerPoints(Customer $customer)
    {
        return response()->json([
            'points' => $customer->loyalty_points,
            'lifetime_points' => $customer->lifetime_points,
            'tier' => $customer->loyalty_tier,
        ]);
    }

    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:20', 'regex:/^08[0-9]{8,13}$/', Rule::unique('customers', 'phone')],
                'email' => ['nullable', 'email', 'max:255'],
                'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            ],
            ['phone.unique' => 'Nomor telepon sudah terdaftar.']
        );

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
            ],
        ], 201);
    }
}
