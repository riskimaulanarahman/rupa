<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicBookingRequest;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private AppointmentService $appointmentService
    ) {}

    /**
     * Show the public booking page.
     */
    public function index(Request $request): View
    {
        $outlet = $this->requireOutletContext($request);

        $categories = ServiceCategory::query()
            ->when($outlet?->id, function ($query) use ($outlet) {
                $query->where('outlet_id', $outlet->id);
            })
            ->active()
            ->ordered()
            ->with(['services' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->get();

        $beauticians = User::query()
            ->when($outlet?->id, function ($query) use ($outlet) {
                $query->where('outlet_id', $outlet->id);
            })
            ->where('role', 'beautician')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get logged in customer if any
        $loggedInCustomer = Auth::guard('customer')->user();

        return view('booking.index', array_merge(
            compact('categories', 'beauticians', 'loggedInCustomer'),
            $this->bookingViewData($request)
        ));
    }

    /**
     * Get available time slots for a given date and optional service/staff.
     */
    public function slots(Request $request): JsonResponse
    {
        $outlet = $this->requireOutletContext($request);
        $outletId = $outlet?->id;

        $serviceExistsRule = Rule::exists('services', 'id');
        $staffExistsRule = Rule::exists('users', 'id');

        if ($outletId) {
            $serviceExistsRule = $serviceExistsRule->where(fn ($query) => $query->where('outlet_id', $outletId));
            $staffExistsRule = $staffExistsRule->where(fn ($query) => $query->where('outlet_id', $outletId));
        }

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'service_id' => ['nullable', $serviceExistsRule],
            'staff_id' => ['nullable', $staffExistsRule],
        ]);

        $date = \Carbon\Carbon::parse($request->input('date'));
        $serviceId = $request->input('service_id');
        $staffId = $request->input('staff_id');

        if (! $serviceId) {
            return response()->json([
                'slots' => [],
                'morning' => [],
                'afternoon' => [],
            ]);
        }

        $slots = $this->appointmentService->getAvailableSlots(
            $date,
            (int) $serviceId,
            $outletId,
            $staffId ? (int) $staffId : null
        );

        // Group slots by morning/afternoon
        $morning = [];
        $afternoon = [];

        foreach ($slots as $slot) {
            $hour = (int) substr($slot, 0, 2);
            if ($hour < 12) {
                $morning[] = $slot;
            } else {
                $afternoon[] = $slot;
            }
        }

        return response()->json([
            'slots' => $slots,
            'morning' => $morning,
            'afternoon' => $afternoon,
        ]);
    }

    /**
     * Store a new booking from the public form.
     */
    public function store(PublicBookingRequest $request): RedirectResponse
    {
        $outlet = $this->requireOutletContext($request);
        $outletId = $outlet?->id;
        $tenantId = $outlet?->tenant_id;
        $validated = $request->validated();

        // Find referrer if referral code provided
        $referrerId = null;
        if (! empty($validated['referral_code']) && config('referral.enabled', true)) {
            $referrer = Customer::query()
                ->where('referral_code', $validated['referral_code'])
                ->when($outletId, function ($query) use ($outletId) {
                    $query->where('outlet_id', $outletId);
                })
                ->first();
            if ($referrer) {
                $referrerId = $referrer->id;
            }
        }

        $customer = Customer::query()
            ->where('phone', $validated['phone'])
            ->when($outletId, function ($query) use ($outletId) {
                $query->where('outlet_id', $outletId);
            })
            ->first();

        if (! $customer) {
            $customer = Customer::create([
                'tenant_id' => $tenantId,
                'outlet_id' => $outletId,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'referred_by_id' => $referrerId,
            ]);
        }

        // Update customer name/email if they already exist
        if ($customer->wasRecentlyCreated === false) {
            $customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? $customer->email,
            ]);
        }

        // Get service duration
        $service = Service::query()
            ->whereKey($validated['service_id'])
            ->when($outletId, function ($query) use ($outletId) {
                $query->where('outlet_id', $outletId);
            })
            ->firstOrFail();
        $endTime = $this->appointmentService->calculateEndTime(
            $validated['start_time'],
            $service->duration_minutes
        );

        // Create appointment
        $appointment = Appointment::create([
            'tenant_id' => $tenantId,
            'outlet_id' => $outletId,
            'customer_id' => $customer->id,
            'service_id' => $validated['service_id'],
            'staff_id' => $validated['staff_id'] ?? null,
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $endTime,
            'status' => 'pending',
            'source' => 'online',
            'notes' => $validated['notes'] ?? null,
        ]);

        $routeParams = array_merge(
            $this->bookingRouteParams($request),
            ['appointment' => $appointment]
        );

        return redirect()
            ->route($this->bookingRouteName($request, 'confirmation'), $routeParams)
            ->with('success', __('booking.success'));
    }

    /**
     * Show booking confirmation page.
     */
    public function confirmation(Request $request, mixed $outletSlugOrAppointment, ?Appointment $appointment = null): View
    {
        if ($outletSlugOrAppointment instanceof Appointment) {
            $appointment = $outletSlugOrAppointment;
        }

        if (! $appointment) {
            abort(404);
        }

        $outlet = $this->requireOutletContext($request);
        $this->ensureAppointmentBelongsToOutlet($appointment, $outlet);

        $appointment->load(['customer', 'service', 'staff']);

        return view('booking.confirmation', array_merge(
            compact('appointment'),
            $this->bookingViewData($request)
        ));
    }

    /**
     * Check booking status by phone number.
     */
    public function checkStatus(Request $request): View
    {
        $outlet = $this->requireOutletContext($request);
        $outletId = $outlet?->id;
        $appointments = collect();

        if ($request->filled('phone')) {
            $customer = Customer::query()
                ->where('phone', $request->phone)
                ->when($outletId, function ($query) use ($outletId) {
                    $query->where('outlet_id', $outletId);
                })
                ->first();

            if ($customer) {
                $appointments = Appointment::query()
                    ->where('customer_id', $customer->id)
                    ->when($outletId, function ($query) use ($outletId) {
                        $query->where('outlet_id', $outletId);
                    })
                    ->where('appointment_date', '>=', now()->toDateString())
                    ->with(['service', 'staff'])
                    ->orderBy('appointment_date')
                    ->orderBy('start_time')
                    ->limit(10)
                    ->get();
            }
        }

        return view('booking.status', array_merge(
            compact('appointments'),
            $this->bookingViewData($request)
        ));
    }

    /**
     * Cancel a booking (by customer via confirmation link).
     */
    public function cancel(Request $request, mixed $outletSlugOrAppointment, ?Appointment $appointment = null): RedirectResponse
    {
        if ($outletSlugOrAppointment instanceof Appointment) {
            $appointment = $outletSlugOrAppointment;
        }

        if (! $appointment) {
            abort(404);
        }

        $outlet = $this->requireOutletContext($request);
        $this->ensureAppointmentBelongsToOutlet($appointment, $outlet);

        // Only allow cancellation of pending/confirmed appointments
        if (! in_array($appointment->status, ['pending', 'confirmed'])) {
            return back()->with('error', __('booking.cannot_cancel'));
        }

        // Only allow cancellation at least 2 hours before appointment
        $appointmentDateTime = \Carbon\Carbon::parse(
            $appointment->appointment_date->format('Y-m-d').' '.$appointment->start_time
        );
        if (now()->diffInHours($appointmentDateTime, false) < 2) {
            return back()->with('error', __('booking.cancel_too_late'));
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_reason' => __('booking.cancelled_by_customer'),
        ]);

        return redirect()
            ->route($this->bookingRouteName($request, 'index'), $this->bookingRouteParams($request))
            ->with('success', __('booking.cancelled_success'));
    }

    private function resolveOutletContext(Request $request): ?Outlet
    {
        if (app()->has('outlet')) {
            /** @var Outlet */
            return app('outlet');
        }

        $outletSlug = $request->route('outletSlug');
        if (! is_string($outletSlug) || $outletSlug === '') {
            return null;
        }

        $matchedOutlets = Outlet::query()
            ->with('tenant')
            ->active()
            ->where('slug', $outletSlug)
            ->limit(2)
            ->get();

        if ($matchedOutlets->count() !== 1) {
            return null;
        }

        return $matchedOutlets->first();
    }

    private function requireOutletContext(Request $request): Outlet
    {
        $outlet = $this->resolveOutletContext($request);
        if (! $outlet || ! $outlet->tenant || ! $outlet->isActive()) {
            abort(404, 'Outlet tidak ditemukan.');
        }

        return $outlet;
    }

    /**
     * @return array<string, string>
     */
    private function bookingRouteParams(Request $request): array
    {
        $outletSlug = $request->route('outletSlug');
        if (is_string($outletSlug) && $outletSlug !== '') {
            return ['outletSlug' => $outletSlug];
        }

        return [];
    }

    private function bookingRoutePrefix(Request $request): string
    {
        return $request->route('outletSlug') ? 'outlet.booking' : 'booking';
    }

    private function bookingRouteName(Request $request, string $route): string
    {
        return "{$this->bookingRoutePrefix($request)}.{$route}";
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingViewData(Request $request): array
    {
        $routeParams = $this->bookingRouteParams($request);
        $isOutletRoute = ! empty($routeParams);

        return [
            'bookingRoutePrefix' => $isOutletRoute ? 'outlet.booking' : 'booking',
            'bookingRouteParams' => $routeParams,
            'bookingHomeUrl' => $isOutletRoute
                ? route('outlet.landing.show', $routeParams)
                : route('home'),
        ];
    }

    private function ensureAppointmentBelongsToOutlet(Appointment $appointment, ?Outlet $outlet): void
    {
        if (! $outlet) {
            return;
        }

        if ((int) $appointment->outlet_id !== (int) $outlet->id) {
            abort(404);
        }
    }
}
