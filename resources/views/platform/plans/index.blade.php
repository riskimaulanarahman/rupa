@extends('layouts.platform')

@section('title', 'Manajemen Paket Langganan')
@section('page-title', 'Plans')

@section('content')
<div class="space-y-6">
    <section class="flex flex-col gap-3 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Pricing Plans</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Kelola paket langganan SaaS, harga, dan batasan outlet.</p>
        </div>
        <a href="{{ route('platform.plans.create') }}" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-rose-500">
            Tambah Paket
        </a>
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @forelse($plans as $plan)
            <div class="relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 {{ !$plan->is_active ? 'opacity-60' : '' }}">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $plan->name }}</h3>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $plan->slug }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            @if($plan->is_featured)
                                <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-rose-700">Featured</span>
                            @endif
                            @if(!$plan->is_active)
                                <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-gray-700 dark:bg-gray-700 dark:text-gray-200">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 flex items-baseline gap-1">
                        <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Rp</span>
                        <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">/bulan</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Atau Rp{{ number_format($plan->price_yearly, 0, ',', '.') }} /tahun</p>

                    <div class="mt-5 space-y-2 border-t border-gray-100 pt-4 text-sm dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Max Outlets</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $plan->max_outlets ?? 'Unlimited' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Trial Days</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $plan->trial_days }} Hari</span>
                        </div>
                    </div>
                </div>

                <div class="mt-auto flex items-center gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-700">
                    <a href="{{ route('platform.plans.edit', $plan) }}" class="inline-flex flex-1 items-center justify-center rounded-lg bg-gray-100 py-2 text-sm font-semibold text-gray-900 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Edit
                    </a>
                    <form action="{{ route('platform.plans.destroy', $plan) }}" method="POST" class="flex-none" onsubmit="return confirm('Hapus paket ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg p-2 text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-900/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border-2 border-dashed border-gray-200 bg-white py-16 text-center dark:border-gray-700 dark:bg-gray-800">
                <p class="font-medium text-gray-500 dark:text-gray-400">Belum ada paket yang dibuat.</p>
                <a href="{{ route('platform.plans.create') }}" class="mt-4 inline-block font-semibold text-rose-600 hover:underline">Buat Paket Pertama →</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
