<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.login') }} - {{ $outlet?->name ?? brand_name() }}</title>
    <link rel="icon" type="image/x-icon" href="{{ brand_logo('favicon') ?? asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-rose-50 via-white to-orange-50" x-data="{ showPassword: false }">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ $outlet?->name ?? brand_name() }}</h1>
                <p class="mt-2 text-sm text-gray-500">Login outlet untuk akses dashboard internal.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8">
                <div class="text-center mb-8">
                    <h2 class="text-xl font-bold text-gray-900">{{ __('auth.login_title') }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Outlet: {{ $outlet?->name ?? '-' }}</p>
                </div>

                <form method="POST" action="{{ route('outlet.login.submit', ['outletSlug' => $outletSlug]) }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('auth.email') }}</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-400 transition @error('email') border-red-400 @enderror"
                            placeholder="email@example.com"
                            required
                            autofocus
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('auth.password') }}</label>
                        <div class="relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                name="password"
                                class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-400 transition @error('password') border-red-400 @enderror"
                                placeholder="{{ __('auth.enter_password') }}"
                                required
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                            >
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-rose-500 border-gray-300 rounded focus:ring-rose-500/20">
                        <label for="remember" class="ml-2 text-sm text-gray-600">{{ __('auth.remember_me') }}</label>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-rose-400 to-rose-500 text-white font-semibold rounded-xl hover:from-rose-500 hover:to-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-500/50 transition shadow-lg shadow-rose-200/50 hover:shadow-rose-300/50"
                    >
                        {{ __('auth.login') }}
                    </button>
                </form>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('outlet.landing.show', ['outletSlug' => $outletSlug]) }}" class="text-sm text-rose-600 hover:text-rose-700">
                    Kembali ke halaman outlet
                </a>
            </div>
        </div>
    </div>
</body>
</html>
