@extends('layouts.landing')

@section('title', 'Pendaftaran Berhasil - ' . brand_name())

@section('content')
<div class="relative min-h-screen pt-32 pb-20 overflow-hidden flex items-center justify-center">
    <div class="max-w-2xl mx-auto px-8 max-lg:px-6 max-sm:px-4 relative z-10">
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-rose-200/50 border border-gray-100 p-12 text-center">
            <!-- Success Icon -->
            <div class="w-24 h-24 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
                <svg class="w-12 h-12 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-3xl font-display font-bold text-gray-900 mb-4">
                Yay! Pendaftaran Berhasil 🎉
            </h1>
            <p class="text-gray-600 mb-10">
                Selamat! Bisnis <strong>{{ $name }}</strong> Anda kini telah terdaftar di {{ brand_name() }}. 
                Dashboard manajemen Anda sudah siap digunakan.
            </p>

            <!-- Action Box -->
            <div class="bg-rose-50 rounded-3xl p-8 mb-10 border border-rose-100">
                <p class="text-xs font-bold text-rose-600 uppercase tracking-widest mb-3"> Dashboard Unit Anda:</p>
                <div class="flex items-center justify-center gap-2 mb-2">
                    <span class="text-2xl font-display font-bold text-gray-900 underline decoration-rose-300 decoration-4 underline-offset-4">
                        {{ $host }}
                    </span>
                    <button @click="navigator.clipboard.writeText('https://{{ $host }}'); alert('Link disalin!')" 
                            class="p-2 hover:bg-rose-100 rounded-lg transition-colors" title="Salin Link">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-rose-400 font-medium">Gunakan email & password yang Anda buat tadi untuk login.</p>
            </div>

            <a href="https://{{ $host }}/login" 
               class="inline-block w-full py-4 bg-gray-900 text-white font-bold rounded-2xl hover:bg-black hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                Buka Dashboard Sekarang →
            </a>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <p class="text-xs text-gray-400">
                    Kami juga telah mengirimkan detail akses ke email Anda. 
                    Belum menerima? <a href="#" class="text-rose-500 font-semibold hover:underline">Kirim ulang email</a>
                </p>
            </div>
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('home') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
