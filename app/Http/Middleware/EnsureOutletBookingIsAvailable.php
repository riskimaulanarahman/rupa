<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOutletBookingIsAvailable
{
    /**
     * Handle an incoming request.
     *
     * Public booking should not depend on setup wizard completion.
     * It is gated by booking availability at outlet level.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $outlet = outlet();

        // No outlet context: keep legacy flow unchanged.
        if (! $outlet) {
            return $next($request);
        }

        $bookingEnabled = (bool) Setting::get('booking_enabled', true);
        if ($bookingEnabled) {
            return $next($request);
        }

        $message = 'Booking online belum tersedia untuk outlet ini.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 503);
        }

        return response()->view('booking.unavailable', [
            'message' => $message,
            'backUrl' => route('outlet.landing.show', ['outletSlug' => $outlet->slug]),
        ], 503);
    }
}
