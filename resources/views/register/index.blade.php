@extends('layouts.landing')

@section('title', 'Pilih Paket Langganan - ' . brand_name())

@section('content')
@php /** @var \App\Models\Plan $plan */ @endphp
<div class="relative min-h-screen pt-12 pb-12 overflow-hidden">
    <div class="max-w-5xl mx-auto px-8 max-lg:px-6 max-sm:px-4 relative z-10">
        @php /** @var \App\Models\Plan[] $plans */ @endphp
        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-10">
            <h1 class="text-4xl md:text-5xl font-display font-bold text-gray-900 leading-tight mb-4">
                Mulai Skalakan Bisnis Anda Bersama <span class="text-rose-600">{{ brand_name() }}</span>
            </h1>
            <p class="text-lg text-gray-600">
                Pilih paket yang paling sesuai dengan kebutuhan bisnis Anda. Semua paket termasuk trial gratis selama 14 hari.
            </p>
        </div>

        <!-- Pricing Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
            @foreach($plans as $plan)
                @php /** @var \App\Models\Plan $plan */ @endphp
                <div class="relative group bg-white rounded-3xl p-6 border border-gray-100 shadow-xl shadow-gray-200/50 hover:shadow-rose-200 transition-all duration-300 {{ $plan->is_featured ? 'ring-2 ring-rose-500 scale-105 z-10' : '' }}">
                    @if($plan->is_featured)
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-rose-500 text-white text-xs font-bold rounded-full uppercase tracking-widest shadow-lg shadow-rose-200">
                            Paling Populer
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                        <p class="text-sm text-gray-500 min-h-[40px]">{{ $plan->description }}</p>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-baseline gap-1">
                            <span class="text-sm font-semibold text-gray-900">Rp</span>
                            <span class="text-4xl font-bold text-gray-900">{{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                            <span class="text-sm text-gray-500">/bulan</span>
                        </div>
                        <p class="text-xs text-rose-500 mt-2 font-medium">Atau Rp{{ number_format($plan->price_yearly, 0, ',', '.') }} per tahun (Hemat 2 bulan!)</p>
                    </div>

                    <ul class="space-y-3 mb-6">
                        @foreach($plan->features as $feature)
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-rose-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-600">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('register.show', $plan) }}" 
                       class="block w-full text-center py-4 rounded-2xl font-bold transition-all duration-300 {{ $plan->is_featured ? 'bg-rose-500 text-white hover:bg-rose-600 shadow-lg shadow-rose-200' : 'bg-gray-50 text-gray-900 hover:bg-gray-100' }}">
                        Mulai Trial Gratis
                    </a>

                    <p class="text-center text-[10px] text-gray-400 mt-4 uppercase tracking-wider font-semibold">
                        Nol Biaya Komitmen • Batalkan Kapan Saja
                    </p>
                </div>
            @endforeach
        </div>

        <!-- FAQ/Trust -->
        <div class="mt-8 mb-12 text-center">
            <p class="text-gray-500 text-sm">
                Kustom sistem sesuai kebutuhan operasional ? 
                <a href="#" class="text-rose-600 font-bold hover:underline">Hubungi Tim Sales Kami</a>
            </p>
        </div>
    </div>
</div>
@endsection
