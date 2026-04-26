@extends('layouts.dashboard')

@section('title', 'Dashboard Saya')
@section('page-title', 'Dashboard Saya')

@php
    $summary = $dashboard['summary'];
    $services = $dashboard['services'];
    $filters = $dashboard['filters'];
    $selectedPeriod = $filters['period'];
@endphp

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-500">Dashboard Beautician</p>
                <h2 class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">Ringkasan layanan dan insentif saya</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Data hanya menghitung layanan yang sudah masuk transaksi berstatus lunas.
                </p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="period" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Periode</label>
                    <select id="period" name="period" class="w-full rounded-xl border-gray-200 text-sm focus:border-rose-400 focus:ring-rose-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        @foreach($availablePeriods as $value => $label)
                            <option value="{{ $value }}" @selected($selectedPeriod === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="start_date" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mulai</label>
                    <input
                        id="start_date"
                        type="date"
                        name="start_date"
                        value="{{ $filters['start_date'] }}"
                        class="w-full rounded-xl border-gray-200 text-sm focus:border-rose-400 focus:ring-rose-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                </div>
                <div>
                    <label for="end_date" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sampai</label>
                    <input
                        id="end_date"
                        type="date"
                        name="end_date"
                        value="{{ $filters['end_date'] }}"
                        class="w-full rounded-xl border-gray-200 text-sm focus:border-rose-400 focus:ring-rose-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-600">
                        Terapkan
                    </button>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:text-gray-900 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-white">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Layanan Dikerjakan</p>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($summary['total_service_items']) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Layanan</p>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($summary['unique_services']) }}</p>
            </div>
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100 dark:bg-gray-800 dark:ring-rose-900/40">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Insentif Lunas</p>
                <p class="mt-3 text-3xl font-bold text-rose-600 dark:text-rose-400">{{ format_currency($summary['total_incentive_paid']) }}</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Daftar Layanan</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rekap layanan yang Anda kerjakan dalam periode terpilih.</p>
            </div>

            @if(count($services) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Layanan</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jumlah</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Subtotal Insentif</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($services as $service)
                                <tr>
                                    <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $service['service_name'] }}</td>
                                    <td class="px-5 py-4 text-right text-sm text-gray-600 dark:text-gray-300">{{ number_format($service['count']) }}</td>
                                    <td class="px-5 py-4 text-right text-sm font-semibold text-rose-600 dark:text-rose-400">{{ format_currency($service['incentive_total']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-5 py-12 text-center">
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Belum ada layanan lunas pada periode ini.</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Coba ubah periode filter untuk melihat data lainnya.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
