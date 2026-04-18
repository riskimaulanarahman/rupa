@extends('layouts.platform')

@section('title', 'Edit Paket - ' . $plan->name)
@section('page-title', 'Edit Paket')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <section class="flex flex-col gap-3 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Edit Paket: {{ $plan->name }}</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Perbarui detail harga dan konfigurasi paket.</p>
        </div>
        <a href="{{ route('platform.plans.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-600">Batal</a>
    </section>

    <form action="{{ route('platform.plans.update', $plan) }}" method="POST" class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        @csrf
        @method('PUT')
        <div class="px-4 py-8 sm:p-8">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label for="name" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Nama Paket</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" required
                           class="mt-2 block h-12 w-full rounded-lg border border-gray-300 px-4 text-gray-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>

                <div class="lg:col-span-4">
                    <label for="slug" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Slug (Unique ID)</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $plan->slug) }}" required
                           class="mt-2 block h-12 w-full rounded-lg border border-gray-300 px-4 text-gray-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>

                <div class="lg:col-span-4">
                    <label for="max_outlets" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Max Outlets</label>
                    <input type="number" name="max_outlets" id="max_outlets" value="{{ old('max_outlets', $plan->max_outlets) }}" placeholder="Kosongkan jika tak terbatas"
                           class="mt-2 block h-12 w-full rounded-lg border border-gray-300 px-4 text-gray-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>

                <div class="lg:col-span-4">
                    <label for="price_monthly" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Harga Bulanan (Rp)</label>
                    <input type="number" name="price_monthly" id="price_monthly" value="{{ old('price_monthly', $plan->price_monthly) }}" required
                           class="mt-2 block h-12 w-full rounded-lg border border-gray-300 px-4 text-gray-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>

                <div class="lg:col-span-4">
                    <label for="price_yearly" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Harga Tahunan (Rp)</label>
                    <input type="number" name="price_yearly" id="price_yearly" value="{{ old('price_yearly', $plan->price_yearly) }}" required
                           class="mt-2 block h-12 w-full rounded-lg border border-gray-300 px-4 text-gray-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>

                <div class="lg:col-span-4">
                    <label for="trial_days" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Trial Days</label>
                    <input type="number" name="trial_days" id="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" required
                           class="mt-2 block h-12 w-full rounded-lg border border-gray-300 px-4 text-gray-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>

                <div class="lg:col-span-4">
                    <label for="sort_order" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" required
                           class="mt-2 block h-12 w-full rounded-lg border border-gray-300 px-4 text-gray-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                </div>

                <div class="lg:col-span-8">
                    <label for="description" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Deskripsi Singkat</label>
                    <textarea id="description" name="description" rows="5"
                              class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">{{ old('description', $plan->description) }}</textarea>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Status Paket</p>
                        <div class="mt-4 space-y-4">
                            <label for="is_active" class="flex items-center gap-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-600">
                                Aktifkan Paket
                            </label>
                            <label for="is_featured" class="flex items-center gap-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                <input id="is_featured" name="is_featured" type="checkbox" value="1" {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-600">
                                Highlight sebagai Unggulan
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end border-t border-gray-200 px-4 py-4 sm:px-8 dark:border-gray-700">
            <button type="submit" class="rounded-lg bg-rose-600 px-6 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700">Perbarui Paket</button>
        </div>
    </form>
</div>
@endsection
