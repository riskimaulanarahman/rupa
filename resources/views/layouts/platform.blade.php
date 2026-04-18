<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <script>
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
                document.documentElement.style.backgroundColor = '#111827';
                document.documentElement.style.colorScheme = 'dark';
            }
        })();
    </script>
    <style>
        html.dark, html.dark body { background-color: #111827 !important; }
        body { opacity: 0; }
        body.loaded { opacity: 1; transition: opacity 0.1s ease-in; }
    </style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Platform Admin') - {{ brand_name() }}</title>

    <meta name="theme-color" content="#e11d48">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ brand_name() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
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
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 dark:text-gray-100" x-data="{ sidebarOpen: false }" onload="this.classList.add('loaded')">
<script>document.body.classList.add('loaded');</script>
@php
    $platformType = $businessType ?? 'clinic';
    $logoSolid = match($platformType) {
        'salon' => 'bg-purple-500 shadow-purple-200/50',
        'barbershop' => 'bg-blue-500 shadow-blue-200/50',
        default => 'bg-rose-500 shadow-rose-200/50',
    };
    $activeClass = match($platformType) {
        'salon' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
        'barbershop' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        default => 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
    };
    $roleBadgeClass = match($platformType) {
        'salon' => 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
        'barbershop' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        default => 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
    };
    $navItems = [
        ['name' => 'Dashboard', 'route' => 'platform.dashboard', 'icon' => 'home'],
        ['name' => 'Tenants', 'route' => 'platform.tenants.index', 'icon' => 'users'],
        ['name' => 'Permissions', 'route' => 'platform.permissions.defaults', 'icon' => 'collection'],
        ['name' => 'Plans', 'route' => 'platform.plans.index', 'icon' => 'gift'],
        ['name' => 'Billing', 'route' => 'platform.billing.index', 'icon' => 'credit-card'],
        ['name' => 'Revenue', 'route' => 'platform.revenue.index', 'icon' => 'chart-bar'],
        ['name' => 'Branding', 'route' => 'platform.branding.favicon', 'icon' => 'cog'],
        ['name' => 'Landing', 'route' => 'platform.landing.index', 'icon' => 'sparkles'],
    ];
@endphp

<div class="flex min-h-screen">
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/50 z-40 hidden max-lg:block"
        @click="sidebarOpen = false"
    ></div>

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out lg:static lg:inset-0"
    >
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
                <a href="{{ route('platform.dashboard') }}" class="flex items-center gap-2">
                    @if(brand_logo())
                        <img src="{{ brand_logo() }}" alt="{{ brand_name() }}" class="h-8 w-auto">
                    @else
                        <div class="w-8 h-8 {{ $logoSolid }} rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                    @endif
                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ brand_name() }} Platform</span>
                </a>
                <button @click="sidebarOpen = false" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 max-lg:block hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                @foreach($navItems as $item)
                    @php
                        $isActive = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route'] . '.*');
                    @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $isActive ? $activeClass : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}"
                    >
                        @include('components.icons.' . $item['icon'])
                        {{ $item['name'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </aside>

    <div class="flex-1">
        <header class="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors">
            <div class="flex items-center justify-between h-16 px-6 max-md:px-4">
                <div class="flex items-center gap-4">
                    <button
                        @click="sidebarOpen = true"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 max-lg:block hidden"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@yield('page-title', 'Platform Admin')</h1>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                        class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                        :title="darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                    >
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ auth()->user()->name }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-tight {{ $roleBadgeClass }}">Super Admin</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H9m4 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1" />
                            </svg>
                            <span class="hidden sm:inline">Sign out</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
            <div class="mx-auto w-full max-w-7xl space-y-6">
                @if(session('success'))
                    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>

@stack('scripts')
@if(brand_custom_script('body'))
    {!! brand_custom_script('body') !!}
@endif
</body>
</html>
