@extends('layouts.landing')

@section('title', 'Pendaftaran Akun - ' . $plan->name)

@section('content')
<div class="relative min-h-screen pt-32 pb-20 overflow-hidden">
    <div class="max-w-4xl mx-auto px-8 max-lg:px-6 max-sm:px-4 relative z-10">
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-5">
                <!-- Sidebar Info -->
                <div class="md:col-span-2 bg-gray-50 p-10 border-r border-gray-100">
                    @php /** @var \App\Models\Plan $plan */ @endphp
                    <div class="inline-flex items-center px-3 py-1 bg-rose-100 text-rose-600 text-[10px] font-bold uppercase tracking-widest rounded-full mb-6">
                        Paket Terpilih
                    </div>
                    <h2 class="text-2xl font-display font-bold text-gray-900 mb-2">{{ $plan->name }}</h2>
                    <p class="text-sm text-gray-500 mb-8">{{ $plan->description }}</p>

                    <div class="space-y-4">
                        @foreach($plan->features as $feature)
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-xs text-gray-600 font-medium">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-12 pt-8 border-t border-gray-200">
                        <div class="flex items-baseline gap-1">
                            <span class="text-sm font-semibold text-gray-900">Rp{{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                            <span class="text-xs text-gray-500">/bulan</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 italic">* Belum termasuk PPN jika ada</p>
                    </div>

                    <a href="{{ route('register.index') }}" class="inline-flex mt-6 text-xs font-bold text-rose-600 hover:text-rose-700 transition-colors uppercase tracking-widest">
                        ← Ganti Paket
                    </a>
                </div>

                <!-- Form Area -->
                <div class="md:col-span-3 p-10">
                    <form action="{{ route('register.store') }}" method="POST" x-data="{ subdomain: '' }">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-rose-500 text-white flex items-center justify-center text-sm">1</span>
                            Informasi Bisnis
                        </h3>

                        <div class="space-y-5 mb-10">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Bisnis</label>
                                <input type="text" name="business_name" value="{{ old('business_name') }}" required 
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none"
                                       placeholder="Contoh: Klinik Cantik Sejahtera">
                                @error('business_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Bisnis</label>
                                    <select name="business_type" required 
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none bg-white">
                                        <option value="clinic" {{ old('business_type') == 'clinic' ? 'selected' : '' }}>Klinik Kecantikan</option>
                                        <option value="salon" {{ old('business_type') == 'salon' ? 'selected' : '' }}>Salon / Spa</option>
                                        <option value="barbershop" {{ old('business_type') == 'barbershop' ? 'selected' : '' }}>Barbershop</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Subdomain</label>
                                    <div class="relative flex items-center">
                                        <input type="text" name="subdomain" x-model="subdomain" value="{{ old('subdomain') }}" required 
                                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none pr-32"
                                               placeholder="klinik-anda">
                                        <div class="absolute right-3 px-2 py-1 bg-gray-100 rounded text-[10px] font-bold text-gray-500 border border-gray-200 pointer-events-none">
                                            .{{ parse_url(config('app.url'), PHP_URL_HOST) }}
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1">Alamat akses website Anda nantinya</p>
                                    @error('subdomain') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-rose-500 text-white flex items-center justify-center text-sm">2</span>
                            Akun Pemilik (Owner)
                        </h3>

                        <div class="space-y-5 mb-10">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="owner_name" value="{{ old('owner_name') }}" required 
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none"
                                       placeholder="Nama Pemilik / Penanggung Jawab">
                                @error('owner_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                                <input type="email" name="owner_email" value="{{ old('owner_email') }}" required 
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none"
                                       placeholder="email@bisnisanda.com">
                                @error('owner_email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                                    <input type="password" name="password" required 
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none">
                                    @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password</label>
                                    <input type="password" name="password_confirmation" required 
                                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full py-4 bg-gray-900 text-white font-bold rounded-2xl hover:bg-black hover:shadow-xl transition-all duration-300">
                                Selesaikan Pendaftaran
                            </button>
                            <p class="text-center text-[10px] text-gray-400 mt-4 leading-relaxed px-6">
                                Dengan mendaftar, Anda menyetujui <a href="#" class="underline">Syarat & Ketentuan</a> serta <a href="#" class="underline">Kebijakan Privasi</a> kami.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
