@extends('layouts.platform')

@section('title', 'Manajemen Landing Page')
@section('page-title', 'Landing Content')

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Landing Page Content</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Kelola teks dan konten yang muncul di halaman depan website Rupa.</p>
    </section>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[920px] divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Bagian / Kunci</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Deskripsi</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Konten (ID)</th>
                        <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @php $currentSection = null; @endphp
                    @foreach($contents as $content)
                        @if($currentSection !== $content->section)
                            @php $currentSection = $content->section; @endphp
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <td colspan="4" class="px-5 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ $currentSection }} Section
                                </td>
                            </tr>
                        @endif
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="whitespace-nowrap px-5 py-3 text-sm">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $content->key }}</div>
                            </td>
                            <td class="px-5 py-3 text-sm italic text-gray-500 dark:text-gray-400">
                                {{ $content->description }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ Str::limit($content->content['id'] ?? '-', 100) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm">
                                <a href="{{ route('platform.landing.edit', $content) }}" class="font-semibold text-rose-600 hover:text-rose-900">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
