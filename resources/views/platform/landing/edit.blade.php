@extends('layouts.platform')

@section('title', 'Edit Konten Landing Page')
@section('page-title', 'Edit Landing Content')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <section class="rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <nav class="flex" aria-label="Breadcrumb">
            <ol role="list" class="flex flex-wrap items-center gap-2 sm:gap-4">
                <li>
                    <div>
                        <a href="{{ route('platform.landing.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Landing Page</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-3 text-sm font-semibold text-gray-900 dark:text-gray-100" aria-current="page">Edit {{ $landingContent->key }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">Perbarui konten multibahasa untuk elemen landing page yang dipilih.</p>
    </section>

    <div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form action="{{ route('platform.landing.update', $landingContent) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="px-4 py-8 sm:p-8">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Informasi Konten</p>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Key Name</label>
                                    <input type="text" value="{{ $landingContent->key }}" disabled class="mt-2 block h-11 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Deskripsi</label>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $landingContent->description ?: '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-8">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Multi-language Content</h3>
                        <div class="mt-4 space-y-6">
                            <div>
                                <label for="content_id" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Bahasa Indonesia (ID)</label>
                                <textarea id="content_id" name="content[id]" rows="6" class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">{{ old('content.id', $landingContent->content['id'] ?? '') }}</textarea>
                            </div>

                            <div>
                                <label for="content_en" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">English (EN)</label>
                                <textarea id="content_en" name="content[en]" rows="6" class="mt-2 block w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">{{ old('content.en', $landingContent->content['en'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-8 dark:border-gray-700">
                <a href="{{ route('platform.landing.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-100 dark:ring-gray-600 dark:hover:bg-gray-600">Batal</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
