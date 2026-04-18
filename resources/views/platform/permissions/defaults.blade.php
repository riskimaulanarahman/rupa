@extends('layouts.platform')

@section('title', 'Default Permission Global')
@section('page-title', 'Default Permission')

@section('content')
<div class="space-y-6">
    <section class="flex flex-col gap-3 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Default Permission Global</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Template akses menu awal untuk semua tenant/outlet. Override per outlet tetap dikelola dari detail tenant.</p>
        </div>
        <a href="{{ route('platform.tenants.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-600">
            Kembali ke Tenant
        </a>
    </section>

    @if($errors->any())
        <div class="rounded-lg bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-inset ring-rose-200 dark:bg-rose-900/20 dark:text-rose-300 dark:ring-rose-700/60">
            <p class="font-semibold">Gagal menyimpan perubahan.</p>
            <ul class="mt-1 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form method="POST" action="{{ route('platform.permissions.defaults.update') }}">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] divide-y divide-gray-100 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Module</th>
                            @foreach($roles as $role)
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ strtoupper($role) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($modules as $module)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $module['label'] }}</td>
                                @foreach($roles as $role)
                                    @php
                                        $checked = (bool) data_get($matrix, "{$role}.{$module['key']}", false);
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        <input type="hidden" name="permissions[{{ $role }}][{{ $module['key'] }}]" value="0">
                                        <input
                                            type="checkbox"
                                            name="permissions[{{ $role }}][{{ $module['key'] }}]"
                                            value="1"
                                            class="h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500"
                                            @checked($checked)
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end border-t border-gray-100 px-6 py-4 dark:border-gray-700">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                    Simpan Default Global
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
