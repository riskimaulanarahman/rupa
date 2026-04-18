<?php

use App\Support\Auth\LoginRedirectResolver;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => app(LoginRedirectResolver::class)->staffLoginUrl($request));

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\IdentifyTenant::class,
            \App\Http\Middleware\CheckSubscription::class,
            \App\Http\Middleware\RestrictSuperadminToPlatform::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'setup.required' => \App\Http\Middleware\EnsureSetupIsRequired::class,
            'setup.completed' => \App\Http\Middleware\EnsureSetupIsCompleted::class,
            'outlet.booking.available' => \App\Http\Middleware\EnsureOutletBookingIsAvailable::class,
            'customer.auth' => \App\Http\Middleware\EnsureCustomerIsAuthenticated::class,
            'customer.guest' => \App\Http\Middleware\RedirectIfCustomerAuthenticated::class,
            'feature' => \App\Http\Middleware\CheckFeature::class,
            'revenue.access' => \App\Http\Middleware\CanViewRevenueMiddleware::class,
            'module.access' => \App\Http\Middleware\CanAccessModuleMiddleware::class,
            'tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'tenant.api' => \App\Http\Middleware\Api\IdentifyTenantFromHeader::class,
            'resolve.outlet' => \App\Http\Middleware\ResolveOutletFromSlug::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('rupa:process-billing')->dailyAt('00:01');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
