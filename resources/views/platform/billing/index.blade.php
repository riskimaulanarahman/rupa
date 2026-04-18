@extends('layouts.platform')

@section('title', 'Billing Tenant')
@section('page-title', 'Billing')

@section('content')
<div class="space-y-6">
    <section class="flex flex-col gap-4 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Billing Tenant</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Manajemen invoice subscription SaaS tenant.</p>
        </div>
        <a href="{{ route('platform.bank-accounts.index') }}" class="inline-flex items-center justify-center rounded-lg border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 transition">
            Kelola Rekening Bank
        </a>
    </section>

    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <select name="period" class="rounded-lg border-gray-300 text-sm focus:border-rose-500 focus:ring-rose-500">
                @foreach($periods as $periodKey => $periodLabel)
                    <option value="{{ $periodKey }}" @selected($period === $periodKey)>{{ $periodLabel }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-rose-500 focus:ring-rose-500">
                <option value="">Semua Status</option>
                @foreach(['pending', 'awaiting_verification', 'paid', 'overdue', 'cancelled'] as $invoiceStatus)
                    <option value="{{ $invoiceStatus }}" @selected(request('status') === $invoiceStatus)>{{ strtoupper($invoiceStatus) }}</option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">Filter</button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl bg-white dark:bg-gray-800 px-6 py-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Invoice</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Rp{{ number_format($totals['total_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl bg-white dark:bg-gray-800 px-6 py-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Lunas</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Rp{{ number_format($totals['paid_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl bg-white dark:bg-gray-800 px-6 py-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($totals['pending_count']) }}</p>
        </div>
        <div class="rounded-2xl bg-white dark:bg-gray-800 px-6 py-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Awaiting Verification</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($totals['awaiting_count']) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tenant</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Periode</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                        <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Due Date</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="whitespace-nowrap px-5 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->tenant?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->format('F Y') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-mono text-gray-900 dark:text-gray-100">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-center">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-semibold uppercase {{ $invoice->status_color }}">{{ $invoice->status }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm">
                                <a href="{{ route('platform.billing.show', $invoice) }}" class="font-semibold text-rose-600 hover:text-rose-800">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm italic text-gray-500 dark:text-gray-400">Belum ada invoice pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $invoices->links() }}
    </div>
</div>
@endsection
