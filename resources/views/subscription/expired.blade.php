@extends('layouts.dashboard')

@section('title', 'Masa Langganan Berakhir')
@section('page-title', 'Masa Langganan Berakhir')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
    <div class="max-w-3xl w-full text-center space-y-8">
        <div class="space-y-4">
            <div class="inline-flex items-center justify-center p-4 bg-rose-100 rounded-full text-rose-600 mb-4 animate-bounce">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight sm:text-5xl">Masa Langganan Berakhir</h1>
            <p class="text-xl text-gray-500 max-w-lg mx-auto">
                Halo <strong>{{ tenant()->name }}</strong>, akses tulis Anda telah dinonaktifkan sementara karena masa langganan atau trial telah habis.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
            @foreach($plans as $plan)
                <div class="relative flex flex-col p-8 bg-white border border-gray-200 rounded-2xl shadow-sm transform transition hover:scale-105 {{ $plan->id === tenant()->plan_id ? 'border-rose-500 ring-2 ring-rose-500' : '' }}">
                    @if($plan->is_featured)
                        <div class="absolute top-0 right-6 -translate-y-1/2 rounded-full bg-rose-500 px-3 py-1 text-xs font-bold text-white uppercase tracking-wider">Terpopuler</div>
                    @endif
                    
                    <div class="mb-5">
                        <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                        <div class="mt-4 flex items-baseline text-gray-900">
                            <span class="text-sm font-semibold">Rp</span>
                            <span class="text-3xl font-extrabold tracking-tight">{{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                            <span class="ml-1 text-sm font-medium text-gray-500">/bln</span>
                        </div>
                    </div>

                    <ul role="list" class="mb-8 space-y-4 text-sm text-left">
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-gray-600 font-medium">Hingga <strong>{{ $plan->max_outlets ?? '∞' }}</strong> Outlet</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 text-gray-600">Semua Fitur Premium</span>
                        </li>
                        <li class="flex items-center text-gray-400">
                            <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-3 italic">Tanpa biaya per-staff</span>
                        </li>
                    </ul>

                    <form action="{{ route('tenant.billing.switch-plan') }}" method="POST" class="mt-auto">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <button type="submit" class="w-full rounded-xl py-3 px-6 text-sm font-bold transition-all shadow-sm {{ $plan->id === tenant()->plan_id ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-rose-600 text-white hover:bg-rose-700 active:scale-95' }}" {{ $plan->id === tenant()->plan_id ? 'disabled' : '' }}>
                            {{ $plan->id === tenant()->plan_id ? 'Paket Saat Ini' : 'Pilih Paket' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="mt-12 pt-12 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                Punya pertanyaan tentang tagihan? <a href="#" class="text-rose-600 font-bold hover:underline">Hubungi Tim Support Kami</a>
            </p>
        </div>
    </div>
</div>
@endsection
