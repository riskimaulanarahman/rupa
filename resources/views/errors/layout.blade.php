@php
    use Illuminate\Support\Facades\Lang;
    use Illuminate\Support\Facades\Route;

    $resolvedCode = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (int) $exception->getStatusCode()
        : (int) ($code ?? 500);

    $statusGroup = $resolvedCode >= 500 ? '5xx' : '4xx';

    if (! isset($title) || blank($title)) {
        $titleKey = "errors.status.{$resolvedCode}.title";
        $fallbackTitleKey = "errors.status.{$statusGroup}.title";
        $title = Lang::has($titleKey) ? __($titleKey) : __($fallbackTitleKey);
    }

    if (! isset($description) || blank($description)) {
        $descriptionKey = "errors.status.{$resolvedCode}.description";
        $fallbackDescriptionKey = "errors.status.{$statusGroup}.description";
        $description = Lang::has($descriptionKey) ? __($descriptionKey) : __($fallbackDescriptionKey);
    }

    $showDetail = (bool) ($showDetail ?? $resolvedCode < 500);
    $detailMessage = trim((string) (isset($exception) ? $exception->getMessage() : ''));

    $defaultTheme = (object) [
        'button' => 'bg-rose-500 hover:bg-rose-600',
        'buttonOutline' => 'border-rose-200 text-rose-700 hover:bg-rose-50',
        'badge' => 'bg-rose-100 text-rose-700',
        'gradient' => 'from-rose-400 to-rose-500',
    ];
    $theme = isset($tc) ? $tc : $defaultTheme;

    $homeUrl = Route::has('home') ? route('home') : '/';

    if (auth('customer')->check() && Route::has('outlet.customer.dashboard')) {
        $customer = auth('customer')->user();
        $customerOutlet = outlet();

        if (! $customerOutlet && $customer) {
            $customerOutlet = $customer->relationLoaded('outlet')
                ? $customer->outlet
                : $customer->outlet()->first();
        }

        if ($customerOutlet && $customerOutlet->slug) {
            $primaryActionUrl = route('outlet.customer.dashboard', ['outletSlug' => $customerOutlet->slug]);
            $primaryActionLabel = __('errors.actions.go_portal');
        } else {
            $primaryActionUrl = $homeUrl;
            $primaryActionLabel = __('errors.actions.go_home');
        }
    } elseif (auth()->check()) {
        $user = auth()->user();
        $canViewRevenue = $user && method_exists($user, 'canViewRevenue') ? $user->canViewRevenue() : false;

        if ($canViewRevenue && Route::has('dashboard')) {
            $primaryActionUrl = route('dashboard');
            $primaryActionLabel = __('errors.actions.go_dashboard');
        } else {
            $primaryActionUrl = Route::has('appointments.index') ? route('appointments.index') : $homeUrl;
            $primaryActionLabel = __('errors.actions.go_appointments');
        }
    } else {
        $primaryActionUrl = $homeUrl;
        $primaryActionLabel = __('errors.actions.go_home');
    }

    $icon = $icon ?? 'spark';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $resolvedCode }} - {{ $title }} | {{ brand_name() }}</title>
    <link rel="icon" type="image/x-icon" href="{{ brand_logo('favicon') ?? asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand-primary: {{ brand_color('primary') }};
            --brand-primary-hover: {{ brand_color('primary_hover') }};
            --brand-primary-light: {{ brand_color('primary_light') }};
        }
    </style>
    @if(brand_custom_css())
        <style>{!! brand_custom_css() !!}</style>
    @endif
    @if(brand_custom_script('head'))
        {!! brand_custom_script('head') !!}
    @endif
</head>
<body class="min-h-screen bg-gradient-to-br from-rose-50 via-white to-orange-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800 antialiased">
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-rose-200/50 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -left-16 w-72 h-72 bg-orange-200/40 rounded-full blur-3xl"></div>
    </div>

    <main class="relative z-10 min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-2xl">
            <div class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-md rounded-3xl shadow-xl border border-white/70 dark:border-gray-800 p-8 md:p-10">
                @include('errors.partials.content', [
                    'code' => $resolvedCode,
                    'title' => $title,
                    'description' => $description,
                    'icon' => $icon,
                    'detailMessage' => $detailMessage,
                    'showDetail' => $showDetail,
                    'primaryActionUrl' => $primaryActionUrl,
                    'primaryActionLabel' => $primaryActionLabel,
                    'secondaryActionUrl' => $homeUrl,
                    'theme' => $theme,
                ])
            </div>
        </div>
    </main>

    @if(brand_custom_script('body'))
        {!! brand_custom_script('body') !!}
    @endif
</body>
</html>
