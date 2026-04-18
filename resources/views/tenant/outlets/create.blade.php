@extends('layouts.dashboard')

@section('title', 'Tambah Cabang Baru')
@section('page-title', 'Tambah Cabang Baru')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">Tambah Cabang Baru</h2>
            <p class="mt-1 text-sm text-gray-500">Daftarkan lokasi outlet baru untuk jaringan bisnis Anda.</p>
        </div>
    </div>

    <div class="bg-white shadow rounded-2xl border border-gray-100 overflow-hidden">
        <form action="{{ route('tenant.outlets.store') }}" method="POST" class="p-8 space-y-8">
            @csrf

            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-bold text-red-700">Terdapat kesalahan pada form.</p>
                    <ul class="mt-2 list-disc pl-5 text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-900">Nama Outlet</label>
                    <div class="mt-2">
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Contoh: Rupa Bintaro Sector 7" class="block w-full rounded-xl shadow-sm sm:text-sm px-4 py-3 @error('name') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-200 focus:border-rose-500 focus:ring-rose-500 @enderror" required>
                    </div>
                    @error('name')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ selectedBusinessType: @js(old('business_type', 'clinic')) }">
                    <label for="business_type" class="block text-sm font-bold text-gray-900">Tipe Bisnis</label>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <label for="business_type_clinic" class="relative cursor-pointer">
                            <input id="business_type_clinic" type="radio" name="business_type" value="clinic" x-model="selectedBusinessType" class="sr-only" required {{ old('business_type', 'clinic') === 'clinic' ? 'checked' : '' }}>
                            <div class="flex items-center justify-center min-h-[46px] px-2 border rounded-xl transition-colors"
                                :class="selectedBusinessType === 'clinic' ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-gray-200 text-gray-700 hover:bg-gray-50'">
                                <span class="text-sm font-bold">Klinik</span>
                            </div>
                        </label>
                        <label for="business_type_salon" class="relative cursor-pointer">
                            <input id="business_type_salon" type="radio" name="business_type" value="salon" x-model="selectedBusinessType" class="sr-only" {{ old('business_type') === 'salon' ? 'checked' : '' }}>
                            <div class="flex items-center justify-center min-h-[46px] px-2 border rounded-xl transition-colors"
                                :class="selectedBusinessType === 'salon' ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-gray-200 text-gray-700 hover:bg-gray-50'">
                                <span class="text-sm font-bold">Salon</span>
                            </div>
                        </label>
                        <label for="business_type_barbershop" class="relative cursor-pointer">
                            <input id="business_type_barbershop" type="radio" name="business_type" value="barbershop" x-model="selectedBusinessType" class="sr-only" {{ old('business_type') === 'barbershop' ? 'checked' : '' }}>
                            <div class="flex items-center justify-center min-h-[46px] px-2 border rounded-xl transition-colors"
                                :class="selectedBusinessType === 'barbershop' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-700 hover:bg-gray-50'">
                                <span class="text-sm font-bold">Barber</span>
                            </div>
                        </label>
                    </div>
                    @error('business_type')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <label for="address" class="block text-sm font-bold text-gray-900">Alamat Lengkap</label>
                    <div class="mt-2">
                        <textarea name="address" id="address" rows="3" class="block w-full rounded-xl shadow-sm sm:text-sm px-4 py-3 @error('address') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-200 focus:border-rose-500 focus:ring-rose-500 @enderror" required>{{ old('address') }}</textarea>
                    </div>
                    @error('address')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="city" class="block text-sm font-bold text-gray-900">Kota</label>
                    <div class="mt-2">
                        <input type="text" name="city" id="city" value="{{ old('city') }}" class="block w-full rounded-xl shadow-sm sm:text-sm px-4 py-3 @error('city') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-200 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    </div>
                    @error('city')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-bold text-gray-900">Nomor Telepon</label>
                    <div class="mt-2">
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="block w-full rounded-xl shadow-sm sm:text-sm px-4 py-3 @error('phone') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-200 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    </div>
                    @error('phone')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-gray-900">Email Outlet</label>
                    <div class="mt-2">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="block w-full rounded-xl shadow-sm sm:text-sm px-4 py-3 @error('email') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-gray-200 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                <a href="{{ route('tenant.outlets.index') }}" class="w-full sm:w-auto text-center bg-gray-50 text-gray-700 px-6 py-3 rounded-xl text-sm font-bold hover:bg-gray-100 transition-colors">Batal</a>
                <button type="submit" class="w-full sm:w-auto bg-rose-600 text-white px-8 py-3 rounded-xl text-sm font-bold hover:bg-rose-700 transition-all shadow-sm active:scale-95">Simpan Outlet</button>
            </div>
        </form>
    </div>
</div>
@endsection
