@extends('layouts.dashboard')

@section('title', 'Tenant HQ - Manajemen Jaringan')
@section('page-title', 'Tenant HQ')

@section('content')
@php
    $canAddOutlet = $tenant->canAddOutlet();
    $activeOutletId = outlet()?->id;
@endphp
<div class="space-y-6">
    <div class="sm:flex sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold leading-6 text-gray-900">Tenant HQ</h1>
            <p class="mt-2 text-sm text-gray-700">Manajemen pusat untuk seluruh jaringan outlet <strong>{{ $tenant->name }}</strong>.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-sm font-bold text-rose-600 border border-rose-100 uppercase tracking-tighter">
                {{ $tenant->plan->name ?? 'No Plan' }}
            </span>
            @if($canAddOutlet)
                <a href="{{ route('tenant.outlets.create') }}" class="block rounded-lg bg-rose-600 px-3.5 py-2.5 text-center text-sm font-bold text-white shadow-sm hover:bg-rose-500 transition-colors">{{ __('tenant.add_outlet') }}</a>
            @else
                <a href="{{ route('tenant.billing.index') }}" class="block rounded-lg bg-amber-500 px-3.5 py-2.5 text-center text-sm font-bold text-white shadow-sm hover:bg-amber-600 transition-colors">{{ __('tenant.upgrade_plan') }}</a>
            @endif
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-2xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-50 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Outlet</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-bold text-gray-900">{{ $outlets->count() }}</div>
                                <div class="ml-2 text-sm text-gray-500">dari {{ $tenant->plan->max_outlets ?? '∞' }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-2xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-rose-50 rounded-lg p-3">
                        <svg class="h-6 w-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Customers</dt>
                            <dd>
                                <div class="text-2xl font-bold text-gray-900">{{ number_format($outlets->sum('customers_count')) }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-2xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-50 rounded-lg p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Appts</dt>
                            <dd>
                                <div class="text-2xl font-bold text-gray-900">{{ number_format($outlets->sum('appointments_count')) }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-2xl border border-gray-100">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-50 rounded-lg p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Status Langganan</dt>
                            <dd class="flex items-center gap-2">
                                <span class="text-lg font-bold uppercase {{ $tenant->status === 'active' ? 'text-green-600' : 'text-rose-600' }}">{{ $tenant->status }}</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Outlet Performance / List -->
    <div class="bg-white shadow rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-base font-bold text-gray-900">Performa Jaringan Outlet</h3>
            <a href="{{ route('tenant.outlets.index') }}" class="text-sm font-bold text-rose-600 hover:text-rose-700">Manajemen Semua Outlet →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Outlet Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Type</th>
                        <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Customers</th>
                        <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Appointments</th>
                        <th scope="col" class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($outlets as $outlet)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900">{{ $outlet->name }}</span>
                                    <span class="text-[10px] font-mono text-gray-400 lowercase">{{ $outlet->slug }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-bold ring-1 ring-inset {{ $outlet->business_type === 'clinic' ? 'bg-rose-50 text-rose-700 ring-rose-600/20' : ($outlet->business_type === 'salon' ? 'bg-purple-50 text-purple-700 ring-purple-600/20' : 'bg-blue-50 text-blue-700 ring-blue-600/20') }}">
                                    {{ ucfirst($outlet->business_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                {{ number_format($outlet->customers_count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                {{ number_format($outlet->appointments_count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-xs font-bold uppercase {{ $outlet->status === 'active' ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $outlet->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @if((int) $outlet->id === (int) $activeOutletId)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700">
                                        Outlet Aktif
                                    </span>
                                @elseif($outlet->status === 'active')
                                    <form action="{{ route('tenant.outlets.switch', $outlet) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center rounded-lg bg-rose-600 px-3 py-1.5 text-[11px] font-bold text-white hover:bg-rose-700 transition-colors">
                                            Switch ke Outlet Ini
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] font-semibold text-gray-400 uppercase">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
