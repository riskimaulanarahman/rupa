@extends('layouts.platform')

@section('title', 'Revenue All Tenant')
@section('page-title', 'Revenue')

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Revenue All Tenant</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                GMV operasional dan revenue subscription tenant pada periode {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}.
            </p>
        </div>
    </section>

    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <select name="period" class="rounded-lg border-gray-300 text-sm focus:border-rose-500 focus:ring-rose-500">
                @foreach($periods as $periodKey => $periodLabel)
                    <option value="{{ $periodKey }}" @selected($period === $periodKey)>{{ $periodLabel }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-rose-500 focus:ring-rose-500">
                @foreach($statuses as $statusKey => $statusLabel)
                    <option value="{{ $statusKey }}" @selected($status === $statusKey)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">Terapkan</button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-2xl bg-white dark:bg-gray-800 px-6 py-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">GMV Paid</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Rp{{ number_format($totals['gmv_paid'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl bg-white dark:bg-gray-800 px-6 py-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subscription Paid</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Rp{{ number_format($totals['subscription_revenue_paid'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl bg-white dark:bg-gray-800 px-6 py-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">MRR Snapshot</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Rp{{ number_format($totals['mrr_snapshot'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl bg-white dark:bg-gray-800 px-6 py-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paid Transactions</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($totals['transactions']) }}</p>
        </div>
        <div class="rounded-2xl bg-white dark:bg-gray-800 px-6 py-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Tenants (Trx)</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($totals['active_tenants_with_transactions']) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenant</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Outlets</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">GMV Paid</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Trx</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sub Paid</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Invoice Period</th>
                        <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Latest Invoice</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="whitespace-nowrap px-5 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $tenant->name }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm uppercase text-gray-600 dark:text-gray-300">{{ $tenant->status }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm text-gray-900 dark:text-gray-100">{{ $tenant->outlets_count }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-mono text-gray-900 dark:text-gray-100">Rp{{ number_format($tenant->gmv_paid, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm text-gray-900 dark:text-gray-100">{{ number_format($tenant->transactions_count) }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-mono text-gray-900 dark:text-gray-100">Rp{{ number_format($tenant->subscription_revenue_paid, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-mono text-gray-900 dark:text-gray-100">Rp{{ number_format($tenant->invoice_period_total, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-center text-sm uppercase text-gray-600 dark:text-gray-300">{{ $tenant->latest_invoice_status }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm">
                                <a href="{{ route('platform.tenants.show', $tenant->id) }}" class="font-semibold text-rose-600 hover:text-rose-800">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-10 text-center text-sm italic text-gray-500 dark:text-gray-400">Belum ada data revenue tenant pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
