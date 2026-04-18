@extends('layouts.platform')

@section('title', 'Manajemen Tenants')
@section('page-title', 'Tenants')

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Tenants</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Daftar semua partner bisnis yang terdaftar di platform.</p>
        </div>
    </section>

    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form action="{{ route('platform.tenants.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="h-12 w-full rounded-lg border border-gray-200 pl-11 pr-4 text-sm text-gray-900 outline-none transition focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                    placeholder="Cari tenant atau email owner"
                >
                <div class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            <button type="submit" class="inline-flex h-12 items-center justify-center rounded-lg bg-rose-600 px-5 text-sm font-semibold text-white transition hover:bg-rose-700 sm:w-36">
                Filter
            </button>
            @if(request('search'))
                <a href="{{ route('platform.tenants.index') }}" class="inline-flex h-12 items-center justify-center rounded-lg bg-gray-100 px-5 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600 sm:w-28">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col" class="w-[26%] px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Bisnis & Subdomain</th>
                        <th scope="col" class="w-[24%] px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Owner</th>
                        <th scope="col" class="w-[14%] px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Paket</th>
                        <th scope="col" class="w-[12%] px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th scope="col" class="w-[8%] px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Outlet</th>
                        <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($tenants as $tenant)
                        @php
                            $statusClasses = [
                                'active' => 'bg-green-50 text-green-700 ring-green-600/20',
                                'trial' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                'expired' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                                'suspended' => 'bg-gray-100 text-gray-700 ring-gray-600/20',
                                'cancelled' => 'bg-gray-100 text-gray-700 ring-gray-600/20',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-5 py-3 text-sm">
                                <div class="flex flex-col">
                                    <span class="truncate font-semibold text-gray-900 dark:text-gray-100">{{ $tenant->name }}</span>
                                    <span class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $tenant->slug }}.{{ parse_url(config('app.url'), PHP_URL_HOST) }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-900 dark:text-gray-100">
                                <div class="flex flex-col">
                                    <span class="truncate font-medium">{{ $tenant->owner_name }}</span>
                                    <span class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $tenant->owner_email }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm">
                                <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-semibold uppercase tracking-tight text-rose-700 ring-1 ring-inset ring-rose-600/10">
                                    {{ $tenant->plan->name ?? 'No Plan' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-center">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-semibold uppercase tracking-tight ring-1 ring-inset {{ $statusClasses[$tenant->status] ?? $statusClasses['suspended'] }}">
                                    {{ $tenant->status }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $tenant->outlets_count ?? $tenant->outlets()->count() }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('platform.tenants.show', $tenant) }}" class="font-semibold text-rose-600 transition hover:text-rose-800">
                                        Detail
                                    </a>
                                    <form action="{{ route('platform.tenants.toggle', $tenant) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="font-semibold text-gray-600 transition hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">
                                            {{ $tenant->status === 'active' ? 'Suspend' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm italic text-gray-500 dark:text-gray-400">Data tenant tidak ditemukan untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $tenants->links() }}
    </div>
</div>
@endsection
