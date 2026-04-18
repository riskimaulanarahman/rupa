@extends('layouts.landing')

@section('title', ($outlet->name ?? brand_name()) . ' - Landing Outlet')

@section('content')
@php
    $heroBadge = $contentMap->get('hero_badge') ?: 'Outlet Resmi';
    $heroImage = $contentMap->get('hero_image');
    $heroTitle = $contentMap->get('hero_title') ?: $outlet->name;
    $heroSubtitle = $contentMap->get('hero_subtitle') ?: 'Selamat datang di halaman resmi outlet kami.';
    $bookingLabel = $contentMap->get('booking_button_label') ?: 'Booking Online';

    $servicesTitle = $contentMap->get('section_services_title') ?: 'Layanan & Harga';
    $galleryTitle = $contentMap->get('section_gallery_title') ?: 'Galeri Outlet';
    $aboutTitle = $contentMap->get('section_about_title') ?: 'Tentang Outlet';
    $aboutText = $contentMap->get('about_text') ?: ($outlet->address ? 'Outlet berlokasi di ' . $outlet->address . ' dengan layanan profesional dan nyaman.' : 'Konten tentang outlet belum diatur.');

    $featuresTitle = $contentMap->get('section_features_title') ?: 'Kenapa Memilih Kami';
    $features = [
        [
            'title' => $contentMap->get('feature_1_title') ?: 'Terapis Berpengalaman',
            'desc' => $contentMap->get('feature_1_desc') ?: 'Tim profesional dengan standar layanan tinggi.',
        ],
        [
            'title' => $contentMap->get('feature_2_title') ?: 'Produk Berkualitas',
            'desc' => $contentMap->get('feature_2_desc') ?: 'Menggunakan produk pilihan yang aman dan terpercaya.',
        ],
        [
            'title' => $contentMap->get('feature_3_title') ?: 'Tempat Nyaman',
            'desc' => $contentMap->get('feature_3_desc') ?: 'Suasana outlet bersih, nyaman, dan private.',
        ],
    ];

    $testimonialsTitle = $contentMap->get('section_testimonials_title') ?: 'Testimoni Pelanggan';
    $faqTitle = $contentMap->get('section_faq_title') ?: 'Pertanyaan Umum';

    $ctaTitle = $contentMap->get('section_cta_title') ?: 'Siap Reservasi Treatment Anda?';
    $ctaSubtitle = $contentMap->get('section_cta_subtitle') ?: 'Pilih jadwal terbaik Anda dan booking online sekarang.';

    $contactPhone = $contentMap->get('contact_phone') ?: ($outlet->phone ?? '-');
    $contactEmail = $contentMap->get('contact_email') ?: ($outlet->email ?? '-');
    $contactAddress = $contentMap->get('contact_address') ?: ($outlet->address ?? '-');

    $hasServices = isset($serviceCategories) && $serviceCategories->isNotEmpty();
    $hasGallery = ! empty($galleryImages);
@endphp

<div class="relative min-h-screen bg-gradient-to-b from-rose-50/60 via-white to-white pb-24 md:pb-0">
    <header class="sticky top-0 z-40 border-b border-gray-100 bg-white shadow-sm">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('outlet.landing.show', ['outletSlug' => $outlet->slug]) }}" class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-600 text-white text-sm font-bold">
                    {{ strtoupper(substr($outlet->name, 0, 1)) }}
                </span>
                <span class="font-semibold text-gray-900">{{ $outlet->name }}</span>
            </a>

            <nav class="hidden items-center gap-1 rounded-full border border-gray-200 bg-gray-50 p-1 text-sm font-medium text-gray-600 md:flex">
                <a href="#layanan" class="rounded-full px-4 py-2 hover:bg-white hover:text-rose-600">Layanan</a>
                @if($hasGallery)
                    <a href="#galeri" class="rounded-full px-4 py-2 hover:bg-white hover:text-rose-600">Galeri</a>
                @endif
                <a href="#tentang" class="rounded-full px-4 py-2 hover:bg-white hover:text-rose-600">Tentang</a>
                <a href="#faq" class="rounded-full px-4 py-2 hover:bg-white hover:text-rose-600">FAQ</a>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                <a href="{{ $customerActionUrl }}" class="inline-flex items-center rounded-full border border-emerald-200 px-4 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 sm:text-sm">
                    {{ $customerActionLabel }}
                </a>
                <a href="{{ $staffActionUrl }}" class="inline-flex items-center rounded-full border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 sm:text-sm">
                    {{ $staffActionLabel }}
                </a>
                <a href="{{ $hasServices ? $bookingUrl : '#' }}" class="inline-flex items-center rounded-full px-4 py-2 text-xs font-semibold text-white sm:text-sm {{ $hasServices ? 'bg-rose-600 hover:bg-rose-700' : 'bg-gray-300 cursor-not-allowed pointer-events-none' }}">
                    {{ $bookingLabel }}
                </a>
            </div>

            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 md:hidden" @click="mobileMenu = !mobileMenu" :aria-expanded="mobileMenu.toString()" aria-label="Buka menu navigasi">
                <svg x-show="!mobileMenu" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm1 4a1 1 0 100 2h12a1 1 0 100-2H4z" clip-rule="evenodd" />
                </svg>
                <svg x-show="mobileMenu" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div x-show="mobileMenu" x-transition.origin.top class="border-t border-gray-100 bg-white md:hidden" @click.outside="mobileMenu = false">
            <nav class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-4 sm:px-6">
                <a href="#layanan" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700 hover:border-rose-200 hover:text-rose-600" @click="mobileMenu = false">Layanan</a>
                @if($hasGallery)
                    <a href="#galeri" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700 hover:border-rose-200 hover:text-rose-600" @click="mobileMenu = false">Galeri</a>
                @endif
                <a href="#tentang" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700 hover:border-rose-200 hover:text-rose-600" @click="mobileMenu = false">Tentang</a>
                <a href="#faq" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700 hover:border-rose-200 hover:text-rose-600" @click="mobileMenu = false">FAQ</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="mx-auto grid max-w-6xl grid-cols-1 gap-8 px-4 pb-14 pt-20 sm:px-6 sm:pb-16 sm:pt-24 lg:grid-cols-2 lg:pb-20 lg:pt-28">
            <div class="flex flex-col justify-center">
                <span class="inline-flex w-fit items-center rounded-full border border-rose-200 bg-rose-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-rose-700">
                    {{ $heroBadge }}
                </span>
                <h1 class="mt-4 text-4xl font-bold leading-tight text-gray-900 sm:text-5xl">{{ $heroTitle }}</h1>
                <p class="mt-5 text-lg leading-relaxed text-gray-600">{{ $heroSubtitle }}</p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ $hasServices ? $bookingUrl : '#' }}" class="inline-flex items-center rounded-xl px-6 py-3 text-sm font-semibold text-white {{ $hasServices ? 'bg-rose-600 hover:bg-rose-700' : 'bg-gray-300 cursor-not-allowed pointer-events-none' }}">
                        {{ $bookingLabel }}
                    </a>
                    <a href="#layanan" class="inline-flex items-center rounded-xl border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Lihat Layanan
                    </a>
                </div>
                @if(! $hasServices)
                    <p class="mt-4 text-sm font-medium text-amber-600">Layanan akan segera tersedia. Silakan hubungi outlet untuk informasi lebih lanjut.</p>
                @endif
            </div>

            <div class="relative overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-xl">
                @if($heroImage)
                    <img src="{{ asset('storage/' . $heroImage) }}" alt="{{ $outlet->name }}" class="h-full min-h-72 w-full object-cover">
                @else
                    <div class="flex min-h-72 h-full items-center justify-center bg-gradient-to-br from-rose-100 via-rose-50 to-white p-10 text-center">
                        <div>
                            <p class="text-sm uppercase tracking-widest text-rose-500">Professional Care</p>
                            <p class="mt-3 text-2xl font-semibold text-gray-900">{{ $outlet->name }}</p>
                            <p class="mt-2 text-gray-600">Perawatan berkualitas untuk kenyamanan Anda.</p>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <section id="layanan" class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900">{{ $servicesTitle }}</h2>
                <p class="mt-2 text-gray-600">Pilih layanan sesuai kebutuhan Anda dengan harga transparan.</p>
            </div>

            @if($hasServices)
                <div class="space-y-4">
                    @foreach($serviceCategories as $category)
                        <details class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm open:shadow-md">
                            <summary class="flex cursor-pointer items-center justify-between list-none">
                                <div>
                                    <p class="text-lg font-semibold text-gray-900">{{ $category->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $category->services->count() }} layanan tersedia</p>
                                </div>
                                <span class="text-gray-400 transition group-open:rotate-180">⌄</span>
                            </summary>

                            <div class="mt-4 divide-y divide-gray-100">
                                @foreach($category->services as $service)
                                    <div class="flex items-start justify-between gap-4 py-3">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $service->name }}</p>
                                            @if($service->description)
                                                <p class="mt-1 text-sm text-gray-500">{{ $service->description }}</p>
                                            @endif
                                            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-400">{{ $service->formatted_duration }}</p>
                                        </div>
                                        <p class="text-base font-semibold text-rose-600">{{ $service->formatted_price }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center">
                    <p class="text-gray-700 font-medium">Layanan akan segera tersedia.</p>
                    <p class="mt-2 text-sm text-gray-500">Silakan cek kembali atau hubungi outlet untuk jadwal layanan.</p>
                </div>
            @endif
        </section>

        @if($hasGallery)
            <section id="galeri" class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">{{ $galleryTitle }}</h2>
                    <p class="mt-2 text-gray-600">Suasana outlet dan hasil layanan kami.</p>
                </div>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach($galleryImages as $image)
                        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                            <img src="{{ asset('storage/' . $image) }}" alt="Galeri {{ $loop->iteration }}" class="h-40 w-full object-cover">
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="tentang" class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $aboutTitle }}</h2>
                    <p class="mt-2 text-sm text-gray-600">Kenali outlet kami dan alasan pelanggan mempercayakan treatment mereka kepada kami.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-rose-100 bg-rose-50/50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Tentang Outlet</p>
                        <p class="mt-3 leading-relaxed text-gray-700">{{ $aboutText }}</p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $featuresTitle }}</h3>
                        <div class="mt-4 space-y-3">
                            @foreach($features as $feature)
                                <div class="rounded-xl border border-gray-200 bg-white p-4">
                                    <p class="font-semibold text-gray-900">{{ $feature['title'] }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ $feature['desc'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <h2 class="text-3xl font-bold text-gray-900">{{ $testimonialsTitle }}</h2>
            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                @foreach($testimonials as $item)
                    <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <p class="text-sm leading-relaxed text-gray-600">“{{ $item['quote'] }}”</p>
                        <div class="mt-4 border-t border-gray-100 pt-3">
                            <p class="font-semibold text-gray-900">{{ $item['name'] }}</p>
                            <p class="text-xs uppercase tracking-wide text-gray-400">{{ $item['role'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="faq" class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <h2 class="text-3xl font-bold text-gray-900">{{ $faqTitle }}</h2>
            <div class="mt-6 space-y-3">
                @foreach($faqs as $item)
                    <details class="group rounded-2xl border border-gray-200 bg-white p-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left">
                            <span class="font-semibold text-gray-900">{{ $item['question'] }}</span>
                            <span class="text-gray-400 transition group-open:rotate-180">⌄</span>
                        </summary>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ $item['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </section>

        <section id="kontak" class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Kontak Outlet & Jam Operasional</h2>
                    <p class="mt-2 text-sm text-gray-600">Hubungi kami atau datang sesuai jadwal layanan outlet.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-rose-100 bg-rose-50/50 p-5">
                        <h3 class="text-lg font-semibold text-gray-900">Kontak Outlet</h3>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-xl bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Alamat</p>
                                <p class="mt-1 text-sm text-gray-700">{{ $contactAddress ?: '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Telepon</p>
                                <p class="mt-1 text-sm text-gray-700">{{ $contactPhone ?: '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Email</p>
                                <p class="mt-1 text-sm text-gray-700">{{ $contactEmail ?: '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <h3 class="text-lg font-semibold text-gray-900">Jam Operasional</h3>
                        @if($operatingHours->isNotEmpty())
                            <div class="mt-4 space-y-2">
                                @foreach($operatingHours as $hour)
                                    <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3">
                                        <span class="font-medium text-gray-700">{{ $hour->day_name_id }}</span>
                                        @if($hour->is_closed)
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Tutup</span>
                                        @else
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $hour->open_time . ' - ' . $hour->close_time }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-4 rounded-xl border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-500">Jam operasional belum tersedia.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="rounded-3xl bg-rose-600 p-8 text-center text-white shadow-xl sm:p-10">
                <h2 class="text-3xl font-bold">{{ $ctaTitle }}</h2>
                <p class="mt-3 text-rose-100">{{ $ctaSubtitle }}</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ $hasServices ? $bookingUrl : '#' }}" class="inline-flex items-center rounded-xl bg-white px-6 py-3 text-sm font-semibold text-rose-700 {{ $hasServices ? 'hover:bg-rose-50' : 'cursor-not-allowed pointer-events-none opacity-60' }}">
                        {{ $bookingLabel }}
                    </a>
                    <a href="{{ $customerActionUrl }}" class="inline-flex items-center rounded-xl border border-white/50 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">
                        {{ $customerActionLabel }}
                    </a>
                    <a href="{{ $staffActionUrl }}" class="inline-flex items-center rounded-xl border border-white/50 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">
                        {{ $staffActionLabel }}
                    </a>
                </div>
            </div>
        </section>
    </main>

    <div class="fixed bottom-4 left-4 right-4 z-40 md:hidden">
        <div class="rounded-2xl border border-gray-200 bg-white/95 p-3 shadow-xl backdrop-blur">
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ $hasServices ? $bookingUrl : '#' }}" class="inline-flex items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white {{ $hasServices ? 'bg-rose-600 hover:bg-rose-700' : 'bg-gray-300 cursor-not-allowed pointer-events-none' }}">
                    {{ $bookingLabel }}
                </a>
                <a href="{{ $customerActionUrl }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 px-4 py-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                    {{ $customerActionLabel }}
                </a>
            </div>
            <a href="{{ $staffActionUrl }}" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">
                {{ $staffActionLabel }}
            </a>
        </div>
    </div>
</div>
@endsection
