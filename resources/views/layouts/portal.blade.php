<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('portal.my_account')) - {{ brand_name() }}</title>
    @if(brand_logo('favicon'))
        <link rel="icon" type="image/x-icon" href="{{ brand_logo('favicon') }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand-primary: {{ brand_color('primary') }};
            --brand-primary-hover: {{ brand_color('primary_hover') }};
            --brand-primary-light: {{ brand_color('primary_light') }};
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 antialiased min-h-screen">
    @auth('customer')
        <div class="flex min-h-screen" x-data="{ sidebarOpen: false }">
            <!-- Mobile sidebar backdrop -->
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden" @click="sidebarOpen = false"></div>

            <!-- Sidebar -->
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-xl transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
                <!-- Logo -->
                <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700">
                    <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-3">
                        @if(brand_logo('logo'))
                            <img src="{{ brand_logo('logo') }}" alt="{{ brand_name() }}" class="h-8">
                        @else
                            <span class="text-xl font-semibold text-primary-600">{{ brand_name() }}</span>
                        @endif
                    </a>
                    <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="px-4 py-6 space-y-1">
                    <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('portal.dashboard') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        {{ __('portal.dashboard') }}
                    </a>

                    <a href="{{ route('portal.profile') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('portal.profile*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        {{ __('portal.my_profile') }}
                    </a>

                    <a href="{{ route('portal.appointments') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('portal.appointments*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ __('portal.my_appointments') }}
                    </a>

                    <a href="{{ route('portal.treatments') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('portal.treatments*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ __('portal.treatment_history') }}
                    </a>

                    <a href="{{ route('portal.packages') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('portal.packages*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        {{ __('portal.my_packages') }}
                    </a>

                    <a href="{{ route('portal.loyalty') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('portal.loyalty*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ __('portal.loyalty_points') }}
                    </a>

                    <a href="{{ route('portal.transactions') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('portal.transactions*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        {{ __('portal.transactions') }}
                    </a>
                </nav>

                <!-- Book Now Button -->
                <div class="px-4 mt-4">
                    <a href="{{ route('booking.index') }}" class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ __('portal.book_appointment') }}
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Top Header -->
                <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between h-16 px-4 lg:px-8">
                        <!-- Left Side: Menu button + Page Title -->
                        <div class="flex items-center gap-3">
                            <!-- Mobile menu button -->
                            <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>

                            <!-- Page Title -->
                            <h1 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-white">
                                @yield('page-title', __('portal.dashboard'))
                            </h1>
                        </div>

                        <!-- Right Side -->
                        <div class="flex items-center gap-4">
                            <!-- Dark Mode Toggle -->
                            <button @click="darkMode = !darkMode" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </button>

                            <!-- User Menu -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-primary-700 dark:text-primary-300">{{ substr(auth('customer')->user()->name, 0, 1) }}</span>
                                    </div>
                                    <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-300">{{ auth('customer')->user()->name }}</span>
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                                    <a href="{{ route('portal.profile') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        {{ __('portal.my_profile') }}
                                    </a>
                                    <hr class="my-1 border-gray-200 dark:border-gray-700">
                                    <form action="{{ route('portal.logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            {{ __('portal.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-4 lg:p-8">
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-xl">
                            <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-xl">
                            <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    @else
        @yield('content')
    @endauth

    @stack('scripts')
</body>
</html>
