@extends('layouts.platform')

@section('title', 'Detail Tenant - ' . $tenant->name)
@section('page-title', 'Detail Tenant')

@section('content')
<div class="space-y-8">
    <section class="flex flex-col gap-4 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">{{ $tenant->name }}</h1>
            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold uppercase tracking-tight ring-1 ring-inset {{ $tenant->status === 'active' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20' }}">
                {{ $tenant->status }}
            </span>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('platform.permissions.defaults') }}" class="inline-flex w-full items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-rose-600 shadow-sm ring-1 ring-inset ring-rose-200 transition hover:bg-rose-50 dark:bg-gray-700 dark:text-rose-300 dark:ring-rose-500/40 dark:hover:bg-gray-600">
                Default Permission
            </a>
            <form action="{{ route('platform.tenants.toggle', $tenant) }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-600">
                    {{ $tenant->status === 'active' ? 'Suspend Tenant' : 'Activate Tenant' }}
                </button>
            </form>
            <a href="{{ route('platform.tenants.index') }}" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700">Kembali</a>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">Outlets ({{ $tenant->outlets->count() }})</h3>
                </div>
                <div class="px-6 py-4">
                    <ul role="list" class="divide-y divide-gray-100">
                        @forelse($tenant->outlets as $outlet)
                            <li class="py-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $outlet->name }}</span>
                                        <span class="text-xs font-semibold uppercase tracking-widest text-rose-500">{{ $outlet->business_type_label }}</span>
                                        <span class="text-xs text-gray-400 font-mono mt-1">{{ $outlet->full_subdomain }}</span>
                                    </div>
                                    <span class="inline-flex items-center self-start rounded-md px-2 py-1 text-[10px] font-semibold uppercase tracking-tight {{ $outlet->status === 'active' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-600/20 dark:bg-gray-700/60 dark:text-gray-300' }}">
                                        {{ $outlet->status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </li>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 py-4 italic">Belum ada outlet terdaftar.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">Permission Matrix Per Outlet</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Override permission per role untuk outlet terpilih.</p>
                        <p class="mt-2 text-xs font-medium text-amber-700 dark:text-amber-300">Role admin dikunci sistem agar hanya bisa memakai Dashboard, Jadwal, Pelanggan, dan Transaksi. Modul lain akan selalu tetap nonaktif walaupun request dikirim manual.</p>
                    </div>
                    <a href="{{ route('platform.permissions.defaults') }}" class="inline-flex items-center justify-center rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200 transition hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-300 dark:ring-rose-700/60">
                        Atur Default Global
                    </a>
                </div>

                <div class="px-6 py-4 space-y-4">
                    @if($tenant->outlets->isNotEmpty())
                        <form method="GET" action="{{ route('platform.tenants.show', $tenant) }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                            <div class="w-full sm:w-80">
                                <label for="permissions_outlet" class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Outlet</label>
                                <select id="permissions_outlet" name="permissions_outlet" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-rose-500 focus:ring-rose-500" onchange="this.form.submit()">
                                    @foreach($tenant->outlets as $outlet)
                                        <option value="{{ $outlet->id }}" @selected($selectedOutlet && $selectedOutlet->id === $outlet->id)>{{ $outlet->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <noscript>
                                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Pilih</button>
                            </noscript>
                        </form>

                        @if($selectedOutlet)
                            <form method="POST" action="{{ route('platform.tenants.module-access.update', $tenant) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="outlet_id" value="{{ $selectedOutlet->id }}">

                                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
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
                                                            $checked = (bool) data_get($permissionMatrix, "{$role}.{$module['key']}", false);
                                                            $locked = (bool) data_get($lockedMatrix, "{$role}.{$module['key']}", false);
                                                        @endphp
                                                        <td class="px-4 py-3 text-center">
                                                            <input type="hidden" name="permissions[{{ $role }}][{{ $module['key'] }}]" value="0">
                                                            <input
                                                                type="checkbox"
                                                                name="permissions[{{ $role }}][{{ $module['key'] }}]"
                                                                value="1"
                                                                class="h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500 {{ $locked ? 'cursor-not-allowed opacity-50' : '' }}"
                                                                @checked($checked)
                                                                @disabled($locked)
                                                            >
                                                            @if($locked)
                                                                <div class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-300">Locked</div>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                                        Simpan Permission Outlet
                                    </button>
                                </div>
                            </form>
                        @endif
                    @else
                        <p class="text-sm italic text-gray-500 dark:text-gray-400">Belum ada outlet untuk tenant ini.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">Riwayat Tagihan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] divide-y divide-gray-100 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Periode</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                                <th scope="col" class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($tenant->invoices as $invoice)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="whitespace-nowrap px-5 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->format('F Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-mono text-gray-900 dark:text-gray-100">
                                        Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-center">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-semibold uppercase tracking-tight {{ $invoice->status === 'paid' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20' }}">
                                            {{ $invoice->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-10 text-center text-sm italic text-gray-500 dark:text-gray-400">Belum ada invoice.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-widest leading-6">Informasi Owner</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Nama Lengkap</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $tenant->owner_name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Alamat Email</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $tenant->owner_email }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Tenant ID</p>
                        <p class="text-xs font-mono text-gray-500 dark:text-gray-400">ID-{{ str_pad($tenant->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-widest leading-6">Kelola SaaS</h3>
                </div>
                <form action="{{ route('platform.tenants.update', $tenant) }}" method="POST" class="px-6 py-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="plan_id" class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Paket</label>
                        <select id="plan_id" name="plan_id" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-rose-500 focus:ring-rose-500">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('plan_id', $tenant->plan_id) == $plan->id)>
                                    {{ $plan->name }} - Rp{{ number_format($plan->price_monthly, 0, ',', '.') }}/bulan
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status Tenant</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-rose-500 focus:ring-rose-500">
                            @foreach(['trial', 'active', 'suspended', 'expired', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $tenant->status) === $status)>{{ strtoupper($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="subscription_ends_at" class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Masa Langganan Berakhir</label>
                        <input
                            id="subscription_ends_at"
                            type="date"
                            name="subscription_ends_at"
                            value="{{ old('subscription_ends_at', optional($tenant->subscription_ends_at)->format('Y-m-d')) }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-rose-500 focus:ring-rose-500"
                        >
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input
                            type="checkbox"
                            name="is_read_only"
                            value="1"
                            @checked(old('is_read_only', $tenant->is_read_only))
                            class="rounded border-gray-300 text-rose-600 focus:ring-rose-500"
                        >
                        Tenant read-only
                    </label>

                    <button type="submit" class="w-full rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-rose-700">
                        Simpan Konfigurasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
