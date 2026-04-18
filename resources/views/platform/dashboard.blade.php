@extends('layouts.platform')

@section('title', 'Platform Dashboard')
@section('page-title', 'Platform Dashboard')

@section('content')
@php
    $totalTenants = (int) ($stats['total_tenants'] ?? 0);
    $activeTenants = (int) ($stats['active_tenants'] ?? 0);
    $trialTenants = (int) ($stats['trial_tenants'] ?? 0);
    $totalOutlets = (int) ($stats['total_outlets'] ?? 0);
    $mrr = (int) ($stats['mrr'] ?? 0);

    $activeRate = $totalTenants > 0 ? round(($activeTenants / $totalTenants) * 100) : 0;
    $trialRate = $totalTenants > 0 ? round(($trialTenants / $totalTenants) * 100) : 0;
    $avgOutletPerTenant = $totalTenants > 0 ? number_format($totalOutlets / $totalTenants, 1) : '0.0';

    $recentInvoiceCount = $recentInvoices->count();
    $paidRecentInvoiceCount = $recentInvoices->where('status', 'paid')->count();
    $paidInvoiceRate = $recentInvoiceCount > 0 ? round(($paidRecentInvoiceCount / $recentInvoiceCount) * 100) : 0;
@endphp

<div class="space-y-6">
    <section class="rounded-3xl border border-rose-300/70 bg-rose-600 p-6 text-white shadow-lg sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-100">Superadmin Overview</p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Monitoring performa platform dalam satu layar</h2>
                <p class="mt-2 text-sm text-rose-50">Pantau pertumbuhan tenant, kualitas billing, dan peluang optimasi operasional tanpa pindah halaman.</p>
            </div>
            <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-3 lg:max-w-xl">
                <div class="rounded-2xl border border-rose-300 bg-rose-500 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wider text-rose-100">Active Rate</p>
                    <p class="mt-1 text-2xl font-bold">{{ $activeRate }}%</p>
                    <p class="text-xs text-rose-100">{{ number_format($activeTenants) }} dari {{ number_format($totalTenants) }} tenant</p>
                </div>
                <div class="rounded-2xl border border-rose-300 bg-rose-500 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wider text-rose-100">Trial Rate</p>
                    <p class="mt-1 text-2xl font-bold">{{ $trialRate }}%</p>
                    <p class="text-xs text-rose-100">{{ number_format($trialTenants) }} tenant sedang trial</p>
                </div>
                <div class="rounded-2xl border border-rose-300 bg-rose-500 px-4 py-3">
                    <p class="text-[11px] uppercase tracking-wider text-rose-100">Paid Invoice</p>
                    <p class="mt-1 text-2xl font-bold">{{ $paidInvoiceRate }}%</p>
                    <p class="text-xs text-rose-100">{{ $paidRecentInvoiceCount }}/{{ $recentInvoiceCount }} invoice terakhir</p>
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('platform.tenants.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                Kelola Tenant
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <a href="{{ route('platform.billing.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-500/90">
                Review Billing
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <a href="{{ route('platform.plans.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-500/90">
                Atur Paket
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Monthly Recurring Revenue</p>
                <span class="rounded-lg bg-rose-50 p-2 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-3.314 0-6 1.343-6 3s2.686 3 6 3 6-1.343 6-3-2.686-3-6-3zm0 0V5m0 9v5m-4-3h8"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Rp{{ number_format($mrr, 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Estimasi total pendapatan berulang tenant aktif.</p>
        </article>

        <article class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenants Aktif</p>
                <span class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ number_format($activeTenants, 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $activeRate }}% dari total tenant terdaftar.</p>
        </article>

        <article class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Masa Trial</p>
                <span class="rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ number_format($trialTenants, 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $trialRate }}% tenant ada di fase potensi konversi.</p>
        </article>

        <article class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-start justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Outlet</p>
                <span class="rounded-lg bg-sky-50 p-2 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h5M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ number_format($totalOutlets, 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Rata-rata {{ $avgOutletPerTenant }} outlet per tenant.</p>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:col-span-3">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-700">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Tenant Baru</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Pantau onboarding tenant terbaru.</p>
                </div>
                <a href="{{ route('platform.tenants.index') }}" class="text-xs font-semibold text-rose-600 transition hover:text-rose-700">Lihat Semua</a>
            </div>
            <div class="px-6 py-2">
                <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentTenants as $tenant)
                        <li class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0 flex items-center gap-3">
                                <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-gray-100 text-sm font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $tenant->name }}</p>
                                        <p class="whitespace-nowrap rounded-md px-1.5 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $tenant->status === 'active' ? 'bg-green-50 text-green-700 ring-green-600/20' : ($tenant->status === 'trial' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : 'bg-gray-100 text-gray-700 ring-gray-500/20') }}">
                                            {{ ucfirst($tenant->status) }}
                                        </p>
                                    </div>
                                    <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                        <p class="truncate">{{ $tenant->owner_email }}</p>
                                        <span class="hidden h-1 w-1 rounded-full bg-gray-300 sm:inline-block"></span>
                                        <p>{{ $tenant->plan->name ?? 'No Plan' }}</p>
                                        <span class="hidden h-1 w-1 rounded-full bg-gray-300 sm:inline-block"></span>
                                        <p>{{ $tenant->created_at?->diffForHumans() ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-none items-center">
                                <a href="{{ route('platform.tenants.show', $tenant) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-rose-200 hover:text-rose-600 dark:border-gray-600 dark:text-gray-200 dark:hover:border-rose-500/50 dark:hover:text-rose-300">Detail</a>
                            </div>
                        </li>
                    @empty
                        <li class="py-8 text-center text-sm italic text-gray-500 dark:text-gray-400">Belum ada tenant terdaftar.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:col-span-2">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 dark:border-gray-700">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Tagihan Terakhir</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Status invoice terbaru tenant.</p>
                </div>
                <a href="{{ route('platform.billing.index') }}" class="text-xs font-semibold text-rose-600 transition hover:text-rose-700">Buka Billing</a>
            </div>
            <div class="space-y-3 p-4">
                @forelse($recentInvoices as $invoice)
                    @php
                        $invoiceBadge = match($invoice->status) {
                            'paid' => 'bg-green-50 text-green-700 ring-green-600/20',
                            'awaiting_verification' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                            'overdue' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                            'cancelled' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
                            default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        };
                    @endphp
                    <article class="rounded-xl border border-gray-100 p-3 dark:border-gray-700">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->tenant->name ?? '-' }}</p>
                                <p class="mt-0.5 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->format('F Y') }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-semibold uppercase ring-1 ring-inset {{ $invoiceBadge }}">
                                {{ str_replace('_', ' ', $invoice->status) }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-end justify-between">
                            <p class="text-base font-bold text-gray-900 dark:text-gray-100">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                            <a href="{{ route('platform.billing.show', $invoice) }}" class="text-xs font-semibold text-rose-600 transition hover:text-rose-700">Lihat Detail</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm italic text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada tagihan.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
