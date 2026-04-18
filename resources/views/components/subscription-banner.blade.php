@php
    $tenant = tenant();
    if (!$tenant) return;

    $isTrial = $tenant->isOnTrial();
    $endsAt = $tenant->subscription_ends_at ?? $tenant->trial_ends_at;
    
    if (!$endsAt) return;
    
    $daysLeft = now()->diffInDays($endsAt, false);
    $showBanner = $daysLeft <= 7 && $daysLeft >= 0;
    $isExpired = $daysLeft < 0 || $tenant->is_read_only || $tenant->status === 'expired';
@endphp

@if($isExpired)
    <div class="bg-rose-600">
        <div class="max-w-7xl mx-auto py-2 px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between flex-wrap">
                <div class="w-0 flex-1 flex items-center">
                    <span class="flex p-2 rounded-lg bg-rose-800">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </span>
                    <p class="ml-3 font-bold text-white truncate text-xs sm:text-sm">
                        <span class="md:hidden">Langganan berakhir!</span>
                        <span class="hidden md:inline">Masa langganan Anda telah berakhir. Fitur tulis dinonaktifkan.</span>
                    </p>
                </div>
                <div class="order-3 mt-2 flex-shrink-0 w-full sm:order-2 sm:mt-0 sm:w-auto">
                    <a href="{{ route('subscription.expired') }}" class="flex items-center justify-center px-4 py-1.5 border border-transparent rounded-lg shadow-sm text-xs font-bold text-rose-600 bg-white hover:bg-rose-50 transition-colors uppercase tracking-tight">
                        Perpanjang Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
@elseif($showBanner)
    <div class="bg-amber-500">
        <div class="max-w-7xl mx-auto py-2 px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between flex-wrap">
                <div class="w-0 flex-1 flex items-center">
                    <span class="flex p-2 rounded-lg bg-amber-600 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p class="ml-3 font-medium text-white truncate text-xs sm:text-sm">
                        <span class="md:hidden">Sisa {{ $daysLeft }} hari lagi!</span>
                        <span class="hidden md:inline">Langganan Anda akan berakhir dalam <strong>{{ $daysLeft }} hari</strong>.</span>
                    </p>
                </div>
                <div class="order-3 mt-2 flex-shrink-0 w-full sm:order-2 sm:mt-0 sm:w-auto">
                    <a href="{{ route('tenant.billing.index') }}" class="flex items-center justify-center px-4 py-1.5 border border-transparent rounded-lg shadow-sm text-xs font-bold text-amber-600 bg-white hover:bg-amber-50 transition-colors uppercase tracking-tight">
                        Upgrade Paket
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
