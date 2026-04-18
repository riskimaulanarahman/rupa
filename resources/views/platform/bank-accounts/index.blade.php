@extends('layouts.platform')

@section('title', 'Rekening Bank')
@section('page-title', 'Rekening Bank')

@section('content')
<div class="space-y-6">
    <section class="flex flex-col gap-3 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rekening Bank Platform</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Kelola rekening bank tujuan pembayaran tenant.</p>
        </div>
        <a href="{{ route('platform.bank-accounts.create') }}" class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition">
            Tambah Rekening
        </a>
    </section>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Bank</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">No. Rekening</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Atas Nama</th>
                        <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($accounts as $account)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-5 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $account->bank_name }}</td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $account->account_number }}</td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $account->account_name }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex rounded-full px-2 py-1 text-[10px] font-bold uppercase {{ $account->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-sm">
                                <a href="{{ route('platform.bank-accounts.edit', $account) }}" class="font-semibold text-rose-600 hover:text-rose-700">Edit</a>
                                <form action="{{ route('platform.bank-accounts.destroy', $account) }}" method="POST" class="inline ml-3" onsubmit="return confirm('Hapus rekening ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-red-600 hover:text-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada rekening bank.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $accounts->links() }}</div>
</div>
@endsection
