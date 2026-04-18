@extends('layouts.landing')

@section('title', 'Booking Belum Tersedia')

@section('content')
<div class="min-h-screen flex items-center justify-center px-6 py-16">
    <div class="max-w-xl w-full bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900">Booking Online Belum Tersedia</h1>
        <p class="mt-3 text-gray-600">{{ $message ?? 'Mohon maaf, saat ini booking online belum dapat digunakan.' }}</p>

        <div class="mt-8">
            <a href="{{ $backUrl ?? route('home') }}"
               class="inline-flex items-center px-5 py-3 rounded-xl bg-rose-600 text-white font-semibold hover:bg-rose-700 transition">
                Kembali
            </a>
        </div>
    </div>
</div>
@endsection

