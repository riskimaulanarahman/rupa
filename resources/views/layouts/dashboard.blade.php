<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <!-- Critical: Set dark background BEFORE anything else renders -->
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
        /* Immediate dark mode background - prevents white flash */
        html.dark, html.dark body { background-color: #111827 !important; }
        /* Hide body until styles loaded to prevent flash */
        body { opacity: 0; }
        body.loaded { opacity: 1; transition: opacity 0.1s ease-in; }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ brand_name() }}</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#e11d48">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ brand_name() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

    @if(brand_logo('favicon'))
        <link rel="icon" type="image/x-icon" href="{{ brand_logo('favicon') }}">
    @endif
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
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay (Mobile) -->
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

        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            @include('components.header')

            <!-- Page Content -->
            <main class="p-6 max-md:p-4">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    @if(brand_custom_script('body'))
        {!! brand_custom_script('body') !!}
    @endif

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registered:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>
