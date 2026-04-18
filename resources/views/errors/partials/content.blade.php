@php
    $originalDetail = trim((string) $detailMessage);
    $normalizedDetail = strtolower($originalDetail);
    $genericMessages = [
        '',
        'forbidden',
        'not found',
        'page expired',
        'too many requests',
        'service unavailable',
        'server error',
        'unauthorized',
    ];
    $friendlyDetail = $originalDetail;

    if (str_contains($normalizedDetail, 'csrf token mismatch')) {
        $friendlyDetail = __('errors.details.csrf_token_mismatch');
    } elseif (str_contains($normalizedDetail, 'page expired')) {
        $friendlyDetail = __('errors.details.page_expired');
    } elseif (str_contains($normalizedDetail, 'method is not supported for route')) {
        $friendlyDetail = __('errors.details.method_not_allowed');
    } elseif (str_contains($normalizedDetail, 'too many requests') || str_contains($normalizedDetail, 'too many attempts')) {
        $friendlyDetail = __('errors.details.too_many_requests');
    } elseif (str_contains($normalizedDetail, 'unauthenticated')) {
        $friendlyDetail = __('errors.details.unauthenticated');
    } elseif (str_contains($normalizedDetail, 'unauthorized') || str_contains($normalizedDetail, 'forbidden')) {
        $friendlyDetail = __('errors.details.unauthorized');
    } elseif (str_contains($normalizedDetail, 'not found')) {
        $friendlyDetail = __('errors.details.not_found');
    }

    $technicalIndicators = [
        'exception',
        'sqlstate',
        'stack trace',
        'route [',
        'target class',
        'undefined',
        'call to',
        'attempt to',
        'line ',
        ' in /',
    ];

    $hasTechnicalIndicator = false;
    foreach ($technicalIndicators as $indicator) {
        if (str_contains($normalizedDetail, $indicator)) {
            $hasTechnicalIndicator = true;
            break;
        }
    }

    if ($friendlyDetail === $originalDetail && $hasTechnicalIndicator) {
        $friendlyDetail = __('errors.details.generic');
    }

    $normalizedFriendlyDetail = strtolower(trim((string) $friendlyDetail));
    $canShowDetail = (bool) $showDetail
        && $normalizedFriendlyDetail !== ''
        && ! in_array($normalizedFriendlyDetail, $genericMessages, true);
@endphp

<div class="text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br {{ $theme->gradient ?? 'from-rose-400 to-rose-500' }} text-white shadow-lg mb-5">
        @if($icon === 'shield')
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 4v5c0 5-3 8-7 9-4-1-7-4-7-9V7l7-4z" />
            </svg>
        @elseif($icon === 'compass')
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3a9 9 0 100 18 9 9 0 000-18z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.5 8.5l-2.2 6.2-6.2 2.2 2.2-6.2 6.2-2.2z" />
            </svg>
        @elseif($icon === 'clock')
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke-width="1.8"></circle>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v5l3 2" />
            </svg>
        @elseif($icon === 'bolt')
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 2L4 14h6l-1 8 9-12h-6l1-8z" />
            </svg>
        @elseif($icon === 'server')
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="4" y="4" width="16" height="6" rx="1.5" stroke-width="1.8"></rect>
                <rect x="4" y="14" width="16" height="6" rx="1.5" stroke-width="1.8"></rect>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h.01M8 17h.01" />
            </svg>
        @elseif($icon === 'wrench')
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.7 6.3a4 4 0 01-5.4 5.4L4 17l3 3 5.3-5.3a4 4 0 005.4-5.4L14.7 6.3z" />
            </svg>
        @else
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l2.8 5.7L21 9.6l-4.5 4.4L17.6 21 12 18l-5.6 3 1.1-7-4.5-4.4 6.2-.9L12 3z" />
            </svg>
        @endif
    </div>

    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $theme->badge ?? 'bg-rose-100 text-rose-700' }}">
        Error {{ $code }}
    </span>

    <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $title }}</h1>
    <p class="mt-3 text-base text-gray-600 dark:text-gray-300">{{ $description }}</p>

    @if($canShowDetail)
        <div class="mt-6 text-left rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800/60 dark:bg-amber-900/20">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">{{ __('errors.detail_label') }}</p>
            <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">{{ $friendlyDetail }}</p>
        </div>
    @endif

    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ $primaryActionUrl }}" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3 rounded-xl text-sm font-semibold text-white transition {{ $theme->button ?? 'bg-rose-500 hover:bg-rose-600' }}">
            {{ $primaryActionLabel }}
        </a>
        <button
            type="button"
            class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3 rounded-xl text-sm font-semibold border transition {{ $theme->buttonOutline ?? 'border-rose-200 text-rose-700 hover:bg-rose-50' }}"
            onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='{{ $secondaryActionUrl }}'; }"
        >
            {{ __('errors.actions.back') }}
        </button>
    </div>
</div>
