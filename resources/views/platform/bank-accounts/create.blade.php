@extends('layouts.platform')

@section('title', 'Tambah Rekening Bank')
@section('page-title', 'Tambah Rekening Bank')

@section('content')
<div class="max-w-2xl space-y-6">
    <section class="rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Tambah Rekening Bank</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Input rekening tujuan pembayaran tenant agar dapat digunakan di halaman billing.</p>
    </section>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form action="{{ route('platform.bank-accounts.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Bank</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}" required class="w-full rounded-lg border-gray-300 focus:border-rose-500 focus:ring-rose-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor Rekening</label>
                <input type="text" name="account_number" value="{{ old('account_number') }}" required class="w-full rounded-lg border-gray-300 focus:border-rose-500 focus:ring-rose-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Pemilik Rekening</label>
                <input type="text" name="account_name" value="{{ old('account_name') }}" required class="w-full rounded-lg border-gray-300 focus:border-rose-500 focus:ring-rose-500">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                Aktif
            </label>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('platform.bank-accounts.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200">Batal</a>
                <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
