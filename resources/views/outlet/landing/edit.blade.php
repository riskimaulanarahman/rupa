@extends('layouts.dashboard')

@section('title', 'Pengaturan Landing Outlet')
@section('page-title', 'Landing Outlet')

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-bold text-gray-900">Landing Page Outlet</h1>
        <p class="mt-2 text-sm text-gray-600">
            Atur tampilan halaman publik outlet Anda.
            <a href="{{ $landingUrl }}" target="_blank" class="font-semibold text-rose-600 hover:text-rose-700">Lihat Landing</a>
        </p>
        <p class="mt-1 text-sm text-gray-600">
            Link booking outlet: <span class="font-mono text-gray-900">{{ $bookingUrl }}</span>
        </p>
    </div>

    <form action="{{ route('settings.landing.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Hero Section</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Badge Hero</label>
                <input type="text" name="hero_badge" value="{{ old('hero_badge', $contentMap->get('hero_badge', 'Outlet Resmi')) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Hero</label>
                <input type="text" name="hero_title" value="{{ old('hero_title', $contentMap->get('hero_title', $outlet->name)) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle Hero</label>
                <textarea name="hero_subtitle" rows="3">{{ old('hero_subtitle', $contentMap->get('hero_subtitle')) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Label Tombol Booking</label>
                <input type="text" name="booking_button_label" value="{{ old('booking_button_label', $contentMap->get('booking_button_label', 'Booking Online')) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Hero</label>
                <input type="file" name="hero_image" accept="image/*">
                @if($contentMap->get('hero_image'))
                    <div class="mt-3 rounded-xl overflow-hidden border border-gray-100 max-w-xl">
                        <img src="{{ asset('storage/' . $contentMap->get('hero_image')) }}" alt="Hero Image" class="w-full h-48 object-cover">
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Layanan</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Section Layanan</label>
                <input type="text" name="section_services_title" value="{{ old('section_services_title', $contentMap->get('section_services_title', 'Layanan & Harga')) }}">
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Galeri</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Section Galeri</label>
                <input type="text" name="section_gallery_title" value="{{ old('section_gallery_title', $contentMap->get('section_gallery_title', 'Galeri Outlet')) }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Gambar Galeri (multiple)</label>
                <input type="file" name="gallery_images[]" accept="image/*" multiple>
            </div>
            @if(!empty($galleryImages))
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($galleryImages as $image)
                        <div class="rounded-xl overflow-hidden border border-gray-100">
                            <img src="{{ asset('storage/' . $image) }}" alt="Gallery {{ $loop->iteration }}" class="w-full h-28 object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Tentang Outlet</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Section Tentang</label>
                <input type="text" name="section_about_title" value="{{ old('section_about_title', $contentMap->get('section_about_title', 'Tentang Outlet')) }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="about_text" rows="4">{{ old('about_text', $contentMap->get('about_text')) }}</textarea>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Keunggulan</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Section Keunggulan</label>
                <input type="text" name="section_features_title" value="{{ old('section_features_title', $contentMap->get('section_features_title', 'Kenapa Memilih Kami')) }}">
            </div>
            @for($i = 1; $i <= 3; $i++)
                <div class="rounded-xl border border-gray-100 p-4 space-y-3">
                    <p class="text-sm font-semibold text-gray-800">Keunggulan {{ $i }}</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                        <input type="text" name="feature_{{ $i }}_title" value="{{ old('feature_' . $i . '_title', $contentMap->get('feature_' . $i . '_title')) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="feature_{{ $i }}_desc" rows="2">{{ old('feature_' . $i . '_desc', $contentMap->get('feature_' . $i . '_desc')) }}</textarea>
                    </div>
                </div>
            @endfor
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Testimoni</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Section Testimoni</label>
                <input type="text" name="section_testimonials_title" value="{{ old('section_testimonials_title', $contentMap->get('section_testimonials_title', 'Testimoni Pelanggan')) }}">
            </div>
            @for($i = 1; $i <= 3; $i++)
                <div class="rounded-xl border border-gray-100 p-4 space-y-3">
                    <p class="text-sm font-semibold text-gray-800">Testimoni {{ $i }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                            <input type="text" name="testimonial_{{ $i }}_name" value="{{ old('testimonial_' . $i . '_name', $testimonials[$i - 1]['name'] ?? '') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role/Profesi</label>
                            <input type="text" name="testimonial_{{ $i }}_role" value="{{ old('testimonial_' . $i . '_role', $testimonials[$i - 1]['role'] ?? '') }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Isi Testimoni</label>
                        <textarea name="testimonial_{{ $i }}_quote" rows="3">{{ old('testimonial_' . $i . '_quote', $testimonials[$i - 1]['quote'] ?? '') }}</textarea>
                    </div>
                </div>
            @endfor
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">FAQ</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Section FAQ</label>
                <input type="text" name="section_faq_title" value="{{ old('section_faq_title', $contentMap->get('section_faq_title', 'Pertanyaan Umum')) }}">
            </div>
            @for($i = 1; $i <= 3; $i++)
                <div class="rounded-xl border border-gray-100 p-4 space-y-3">
                    <p class="text-sm font-semibold text-gray-800">FAQ {{ $i }}</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan</label>
                        <input type="text" name="faq_{{ $i }}_question" value="{{ old('faq_' . $i . '_question', $faqs[$i - 1]['question'] ?? '') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jawaban</label>
                        <textarea name="faq_{{ $i }}_answer" rows="3">{{ old('faq_' . $i . '_answer', $faqs[$i - 1]['answer'] ?? '') }}</textarea>
                    </div>
                </div>
            @endfor
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">Kontak</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $contentMap->get('contact_phone', $outlet->phone)) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $contentMap->get('contact_email', $outlet->email)) }}">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea name="contact_address" rows="3">{{ old('contact_address', $contentMap->get('contact_address', $outlet->address)) }}</textarea>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500">CTA Akhir</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul CTA</label>
                <input type="text" name="section_cta_title" value="{{ old('section_cta_title', $contentMap->get('section_cta_title', 'Siap Reservasi Treatment Anda?')) }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle CTA</label>
                <textarea name="section_cta_subtitle" rows="2">{{ old('section_cta_subtitle', $contentMap->get('section_cta_subtitle', 'Pilih jadwal terbaik Anda dan booking online sekarang.')) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
