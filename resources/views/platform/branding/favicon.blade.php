@extends('layouts.platform')

@section('title', 'Platform Branding')
@section('page-title', 'Branding Platform')

@section('content')
    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200/70 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Platform Branding</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Favicon ini akan menjadi default global untuk seluruh sistem.
            </p>
        </section>

        <div class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Favicon Default Platform</h2>
            </div>

            <form action="{{ route('platform.branding.favicon.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label for="platform_brand_logo_favicon" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Favicon</label>
                    <input
                        id="platform_brand_logo_favicon"
                        name="platform_brand_logo_favicon"
                        type="file"
                        accept=".ico,.png,.jpg,.jpeg,.webp"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                        required
                    >
                    @error('platform_brand_logo_favicon')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 transition">
                        Simpan Default Favicon
                    </button>
                </div>
            </form>

            @if($faviconPath)
                <form action="{{ route('platform.branding.favicon.remove') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Hapus
                    </button>
                </form>
            @endif

            @if($faviconPath && $faviconUrl)
                <div class="mt-6 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Preview Favicon Aktif</p>
                    <div class="flex items-center gap-3">
                        <img
                            src="{{ $faviconUrl }}"
                            alt="Platform favicon"
                            class="h-10 w-10 rounded-md border border-gray-200 bg-white object-contain p-1 dark:border-gray-600"
                        >
                        <code class="text-xs text-gray-500 dark:text-gray-400">{{ $faviconPath }}</code>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
