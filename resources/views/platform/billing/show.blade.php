@extends('layouts.platform')

@section('title', 'Detail Invoice')
@section('page-title', 'Detail Invoice')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <section class="flex flex-col gap-4 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Invoice {{ $invoice->id }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $invoice->tenant?->name ?? '-' }} • {{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->format('F Y') }}</p>
        </div>
        <a href="{{ route('platform.billing.index') }}" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">Kembali</a>
    </section>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Informasi Billing</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Tenant</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->tenant?->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Plan</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->plan?->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Billing Period</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->billing_period }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Outlet Count</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->outlet_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Due Date</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->due_date?->format('d M Y H:i') ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nominal</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Plan Price</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">Rp{{ number_format($invoice->plan_price, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Total Amount</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                    <dd><span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-semibold uppercase {{ $invoice->status_color }}">{{ $invoice->status }}</span></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Paid At</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->paid_at?->format('d M Y H:i') ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Submitted At</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->payment_proof_at?->format('d M Y H:i') ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Approved By</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $invoice->approvedBy?->name ?? '-' }}</dd>
                </div>
            </dl>

            @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                <form action="{{ route('platform.billing.markPaid', $invoice) }}" method="POST" class="mt-6">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                        Tandai Lunas
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bukti Pembayaran</h3>

        @if($invoice->payment_proof_url)
            <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/30">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">File bukti pembayaran telah diupload tenant.</p>
                <a href="{{ $invoice->payment_proof_url }}" target="_blank" class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition">
                    Lihat / Download Bukti
                </a>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">Tenant belum mengupload bukti pembayaran.</p>
        @endif

        @if($invoice->payment_note)
            <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Catatan Tenant</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $invoice->payment_note }}</p>
            </div>
        @endif

        @if(in_array($invoice->status, ['awaiting_verification', 'pending', 'overdue'], true))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <form action="{{ route('platform.billing.approve', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 transition">
                        Approve Pembayaran
                    </button>
                </form>

                <form action="{{ route('platform.billing.reject', $invoice) }}" method="POST" class="space-y-2">
                    @csrf
                    <textarea name="rejection_reason" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-rose-500 focus:ring-rose-500" placeholder="Alasan reject (wajib)">{{ old('rejection_reason') }}</textarea>
                    <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500 transition">
                        Reject Pembayaran
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if($invoice->notes)
        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Catatan</h3>
            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">{{ $invoice->notes }}</p>
        </div>
    @endif
</div>
@endsection
