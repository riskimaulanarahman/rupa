@extends('layouts.dashboard')

@section('title', __('setting.title'))
@section('page-title', __('setting.title'))

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Settings Menu -->
    <div class="grid grid-cols-2 max-sm:grid-cols-1 gap-4">
        <!-- Clinic Profile -->
        <a href="{{ route('settings.clinic') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:{{ $tc->borderHover ?? 'border-rose-200' }} dark:hover:border-gray-600 hover:shadow-md transition group">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 {{ $tc->bgLight ?? 'bg-rose-100' }} dark:bg-opacity-20 rounded-xl flex items-center justify-center group-hover:{{ $tc->bgMedium ?? 'bg-rose-200' }} dark:group-hover:bg-opacity-30 transition">
                    <svg class="w-6 h-6 {{ $tc->iconColor ?? 'text-rose-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('setting.clinic_profile') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('setting.clinic_profile_desc') }}</p>
                </div>
            </div>
        </a>

        <!-- Operating Hours -->
        <a href="{{ route('settings.hours') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:border-blue-200 dark:hover:border-gray-600 hover:shadow-md transition group">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-900/70 transition">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('setting.operating_hours') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('setting.operating_hours_desc') }}</p>
                </div>
            </div>
        </a>

        <!-- Branding & White-label -->
        <a href="{{ route('settings.branding') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:border-purple-200 dark:hover:border-gray-600 hover:shadow-md transition group">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-xl flex items-center justify-center group-hover:bg-purple-200 dark:group-hover:bg-purple-900/70 transition">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('setting.branding') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('setting.branding_desc') }}</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
