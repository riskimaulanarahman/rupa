@extends('layouts.dashboard')

@section('title', 'Manajemen Outlet - ' . tenant()->name)
@section('page-title', 'Manajemen Outlet')

@section('content')
@php
    $canAddOutlet = tenant()->canAddOutlet();
@endphp
<div class="space-y-6 max-sm:space-y-4">
    <div class="flex flex-row max-sm:flex-col items-center max-sm:items-start justify-between gap-4 max-sm:gap-3">
        <p class="text-gray-500 dark:text-gray-400 text-sm max-sm:text-xs">
            Kelola operasional dan status aktif setiap cabang dalam jaringan <strong>{{ tenant()->name }}</strong> ({{ $outlets->total() }} outlet)
        </p>
        <div>
            @if($canAddOutlet)
                <a href="{{ route('tenant.outlets.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 max-sm:px-3 max-sm:py-1.5 bg-rose-600 hover:bg-rose-500 text-white text-sm max-sm:text-xs font-medium rounded-lg transition">
                    {{ __('tenant.add_outlet') }}
                </a>
            @else
                <a href="{{ route('tenant.billing.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 max-sm:px-3 max-sm:py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm max-sm:text-xs font-medium rounded-lg transition">
                    {{ __('tenant.upgrade_plan') }}
                </a>
            @endif
        </div>
    </div>

    <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama & Lokasi</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">URL Akses</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($outlets as $outlet)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-5 py-3 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $outlet->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $outlet->city ?: 'Lokasi belum diisi' }}</p>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $outlet->business_type === 'clinic' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-400' : ($outlet->business_type === 'salon' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400') }}">
                                    {{ ucfirst($outlet->business_type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <a href="//{{ $outlet->full_subdomain }}/dashboard" target="_blank" rel="noopener noreferrer" class="text-xs font-mono text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                                    {{ $outlet->full_subdomain }}
                                </a>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium uppercase {{ $outlet->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $outlet->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <form action="{{ route('tenant.outlets.toggle', $outlet) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-sm font-medium {{ $outlet->status === 'active' ? 'text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300' : 'text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300' }}">
                                            {{ $outlet->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <a href="//{{ $outlet->full_subdomain }}/dashboard" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100">
                                        Buka Panel
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada outlet terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($outlets->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $outlets->links() }}
            </div>
        @endif
    </div>

    <div class="sm:hidden bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
        @forelse($outlets as $outlet)
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $outlet->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $outlet->city ?: 'Lokasi belum diisi' }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium uppercase {{ $outlet->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                        {{ $outlet->status }}
                    </span>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $outlet->business_type === 'clinic' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-400' : ($outlet->business_type === 'salon' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400') }}">
                        {{ ucfirst($outlet->business_type) }}
                    </span>
                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400 break-all">{{ $outlet->full_subdomain }}</span>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <form action="{{ route('tenant.outlets.toggle', $outlet) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm font-medium {{ $outlet->status === 'active' ? 'text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300' : 'text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300' }}">
                            {{ $outlet->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <a href="//{{ $outlet->full_subdomain }}/dashboard" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100">
                        Buka Panel
                    </a>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada outlet terdaftar.</p>
            </div>
        @endforelse
        </div>
        @if($outlets->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                {{ $outlets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
