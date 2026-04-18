@extends('layouts.dashboard')

@section('title', 'Tagihan & Langganan')
@section('page-title', 'Tagihan & Langganan')

@section('content')
<div class="space-y-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-bold leading-6 text-gray-900">Tagihan & Langganan</h1>
            <p class="mt-2 text-sm text-gray-700">Kelola paket langganan, metode pembayaran, dan riwayat tagihan jaringan Anda.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Current Plan Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 bg-gray-50/50 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest">Paket Saat Ini</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-sm font-bold text-rose-600 border border-rose-100 mb-2">
                            {{ $tenant->plan->name ?? 'Trial' }}
                        </span>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($tenant->plan->price_monthly ?? 0, 0, ',', '.') }}<span class="text-sm font-normal text-gray-500">/bulan</span></p>
                    </div>

                    <div class="pt-4 border-t border-gray-50 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Batas Outlet:</span>
                            <span class="font-bold text-gray-900">{{ $tenant->plan->max_outlets ?? 'Unlimited' }} Outlet</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Status:</span>
                            <span class="font-bold uppercase {{ $tenant->status === 'active' ? 'text-green-600' : 'text-rose-600' }}">{{ $tenant->status }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Berakhir Pada:</span>
                            <span class="font-bold text-gray-900">{{ $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('d M Y') : ($tenant->trial_ends_at ? $tenant->trial_ends_at->format('d M Y') : '-') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-rose-600 rounded-2xl p-6 text-white shadow-lg shadow-rose-200">
                <h4 class="font-bold text-lg mb-2">Butuh Bantuan?</h4>
                <p class="text-rose-100 text-sm mb-4">Tim support kami siap membantu Anda 24/7 terkait masalah pembayaran atau upgrade paket.</p>
                <a href="#" class="block w-full text-center py-2 bg-white text-rose-600 rounded-xl text-sm font-bold hover:bg-rose-50 transition-colors">Hubungi Support</a>
            </div>
        </div>

        <!-- Plans & Invoices -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Upgrade Options -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 flex justify-between items-center border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest">Pilihan Paket</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($plans as $plan)
                        <div class="p-5 border rounded-2xl {{ $plan->id === $tenant->plan_id ? 'border-rose-500 bg-rose-50/30' : 'border-gray-100 hover:border-rose-200 transition-colors' }}">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $plan->name }}</h4>
                                    <p class="text-xs text-gray-500">Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}/bln</p>
                                </div>
                                @if($plan->id === $tenant->plan_id)
                                    <span class="bg-rose-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Active</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600 mb-4">{{ $plan->description ?? 'Hingga '.$plan->max_outlets.' outlet dengan semua fitur premium.' }}</p>
                            
                            @if($plan->id !== $tenant->plan_id)
                                <form action="{{ route('tenant.billing.switch-plan') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="w-full py-2 bg-white border border-rose-600 text-rose-600 text-xs font-bold rounded-lg hover:bg-rose-600 hover:text-white transition-all">Pilih {{ $plan->name }}</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Bank Accounts -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest">Rekening Tujuan Pembayaran</h3>
                </div>
                <div class="p-6 space-y-3">
                    @forelse($bankAccounts ?? [] as $account)
                        <div class="rounded-xl border border-gray-100 p-4">
                            <p class="text-sm font-bold text-gray-900">{{ $account->bank_name }}</p>
                            <p class="text-sm text-gray-600">No. Rek: <span class="font-mono text-gray-900">{{ $account->account_number }}</span></p>
                            <p class="text-sm text-gray-600">A/N: <span class="font-medium text-gray-900">{{ $account->account_name }}</span></p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Rekening bank belum tersedia. Silakan hubungi superadmin.</p>
                    @endforelse
                </div>
            </div>

            <!-- Invoice History -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest">Riwayat Tagihan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Periode</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Tagihan</th>
                                <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Timeline</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $invoice->billing_period_label }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 text-[10px] font-bold rounded-full uppercase {{ $invoice->status_color }}">
                                            {{ $invoice->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                        <div class="space-y-1 text-xs text-right">
                                            <p>Dibuat: {{ $invoice->created_at?->format('d M Y') }}</p>
                                            <p>Upload bukti: {{ $invoice->payment_proof_at?->format('d M Y H:i') ?? '-' }}</p>
                                            <p>Lunas: {{ $invoice->paid_at?->format('d M Y H:i') ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(in_array($invoice->status, ['pending', 'overdue', 'awaiting_verification'], true))
                                            <form action="{{ route('tenant.billing.submit-payment', $invoice) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                                                @csrf
                                                <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full text-xs text-gray-600">
                                                <textarea name="payment_note" rows="2" placeholder="Catatan pembayaran (opsional)" class="w-full rounded-lg border-gray-300 text-xs focus:border-rose-500 focus:ring-rose-500">{{ old('payment_note') }}</textarea>
                                                <button type="submit" class="w-full rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white hover:bg-rose-700 transition">
                                                    {{ $invoice->status === 'awaiting_verification' ? 'Upload Ulang Bukti' : 'Upload Bukti Bayar' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs font-semibold text-gray-400 uppercase">Tidak ada aksi</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($invoice->rejection_reason)
                                    <tr>
                                        <td colspan="5" class="px-6 pb-4 text-xs text-red-600">
                                            Alasan reject terakhir: {{ $invoice->rejection_reason }}
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">Belum ada riwayat tagihan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
