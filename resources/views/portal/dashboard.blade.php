@extends('layouts.portal')

@section('title', __('portal.dashboard'))
@section('page-title', __('portal.dashboard'))

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-6 text-white">
        <h2 class="text-2xl font-bold">{{ __('portal.welcome_greeting', ['name' => $customer->name]) }}</h2>
        <p class="mt-2 text-primary-100">{{ __('portal.welcome_message') }}</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 max-sm:gap-2">
        <!-- Loyalty Points -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 max-sm:p-3 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 max-sm:gap-2">
                <div class="w-10 h-10 max-sm:w-8 max-sm:h-8 bg-yellow-100 dark:bg-yellow-900/50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 max-sm:w-4 max-sm:h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('portal.loyalty_points') }}</p>
                    <p class="text-xl max-sm:text-lg font-bold text-gray-900 dark:text-white">{{ format_number($customer->loyalty_points) }}</p>
                </div>
            </div>
            <div class="mt-3 max-sm:mt-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $customer->loyalty_tier_color }}">
                    {{ $customer->loyalty_tier_label }}
                </span>
            </div>
        </div>

        <!-- Total Visits -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 max-sm:p-3 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 max-sm:gap-2">
                <div class="w-10 h-10 max-sm:w-8 max-sm:h-8 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 max-sm:w-4 max-sm:h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('portal.total_visits') }}</p>
                    <p class="text-xl max-sm:text-lg font-bold text-gray-900 dark:text-white">{{ $customer->total_visits ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Active Packages -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 max-sm:p-3 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 max-sm:gap-2">
                <div class="w-10 h-10 max-sm:w-8 max-sm:h-8 bg-purple-100 dark:bg-purple-900/50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 max-sm:w-4 max-sm:h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('portal.active_packages') }}</p>
                    <p class="text-xl max-sm:text-lg font-bold text-gray-900 dark:text-white">{{ $activePackages->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Total Spent -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 max-sm:p-3 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 max-sm:gap-2">
                <div class="w-10 h-10 max-sm:w-8 max-sm:h-8 bg-green-100 dark:bg-green-900/50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 max-sm:w-4 max-sm:h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('portal.total_spent') }}</p>
                    <p class="text-lg max-sm:text-base font-bold text-gray-900 dark:text-white truncate">{{ $customer->formatted_total_spent }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 max-sm:gap-3">
        <!-- Upcoming Appointments -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('portal.upcoming_appointments') }}</h3>
                    <a href="{{ route('portal.appointments') }}" class="text-sm text-primary-600 hover:text-primary-700">{{ __('portal.view_all') }}</a>
                </div>
            </div>
            <div class="p-6">
                @if($upcomingAppointments->count() > 0)
                    <div class="space-y-4">
                        @foreach($upcomingAppointments as $appointment)
                            <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/50 rounded-xl flex items-center justify-center">
                                    <span class="text-sm font-bold text-primary-700 dark:text-primary-300">{{ $appointment->appointment_date->format('d') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $appointment->service->name ?? '-' }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ format_date($appointment->appointment_date) }} - {{ $appointment->start_time ? \Carbon\Carbon::parse($appointment->start_time)->format('H:i') : '-' }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $appointment->status === 'confirmed' ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300' }}">
                                    {{ __('appointments.status_' . $appointment->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="mt-4 text-gray-500 dark:text-gray-400">{{ __('portal.no_upcoming_appointments') }}</p>
                        <a href="{{ route('booking.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
                            {{ __('portal.book_now') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Treatments -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('portal.recent_treatments') }}</h3>
                    <a href="{{ route('portal.treatments') }}" class="text-sm text-primary-600 hover:text-primary-700">{{ __('portal.view_all') }}</a>
                </div>
            </div>
            <div class="p-6">
                @if($recentTreatments->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentTreatments as $treatment)
                            <a href="{{ route('portal.treatments.show', $treatment) }}" class="block p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $treatment->appointment?->service?->name ?? '-' }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ format_date($treatment->created_at) }}
                                        </p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-4 text-gray-500 dark:text-gray-400">{{ __('portal.no_treatments_yet') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Active Packages -->
    @if($activePackages->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('portal.active_packages') }}</h3>
                    <a href="{{ route('portal.packages') }}" class="text-sm text-primary-600 hover:text-primary-700">{{ __('portal.view_all') }}</a>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($activePackages as $customerPackage)
                        <a href="{{ route('portal.packages.show', $customerPackage) }}" class="block p-4 border border-gray-200 dark:border-gray-700 rounded-xl hover:border-primary-500 transition-colors">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $customerPackage->package?->name }}</h4>
                            <div class="mt-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('portal.remaining_sessions') }}</span>
                                    <span class="font-semibold text-primary-600">{{ $customerPackage->remaining_sessions }} / {{ $customerPackage->total_sessions }}</span>
                                </div>
                                <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $customerPackage->total_sessions > 0 ? ($customerPackage->remaining_sessions / $customerPackage->total_sessions) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            @if($customerPackage->expires_at)
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('portal.expires') }}: {{ format_date($customerPackage->expires_at) }}
                                </p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
