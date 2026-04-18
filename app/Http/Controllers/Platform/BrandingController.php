<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Branding\BrandIconGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function __construct(private readonly BrandIconGenerator $brandIconGenerator) {}

    public function favicon(): View
    {
        $faviconPath = Setting::getGlobal('platform_brand_logo_favicon', '');

        return view('platform.branding.favicon', [
            'faviconPath' => $faviconPath,
            'faviconUrl' => $this->resolveFaviconUrl($faviconPath),
        ]);
    }

    public function updateFavicon(Request $request): RedirectResponse
    {
        $request->validate([
            'platform_brand_logo_favicon' => ['required', 'image', 'mimes:ico,png,jpg,jpeg,webp', 'max:512'],
        ]);

        $oldFavicon = Setting::getGlobal('platform_brand_logo_favicon');
        $this->deleteLocalFaviconFile($oldFavicon);

        /** @var UploadedFile $uploaded */
        $uploaded = $request->file('platform_brand_logo_favicon');
        $path = $this->storeFaviconInPublic($uploaded);
        Setting::setGlobal('platform_brand_logo_favicon', $path);
        clear_brand_cache();

        try {
            $this->brandIconGenerator->generate('auto', true);
        } catch (\Throwable $e) {
            Log::warning('Platform favicon icon generation failed.', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->with('success', 'Favicon default platform berhasil disimpan.')
                ->with('error', 'Favicon tersimpan, tetapi gagal generate icon set. Jalankan branding:generate-icons.');
        }

        return back()->with('success', 'Favicon default platform berhasil diperbarui.');
    }

    public function removeFavicon(): RedirectResponse
    {
        $oldFavicon = Setting::getGlobal('platform_brand_logo_favicon');

        if (is_string($oldFavicon) && $oldFavicon !== '') {
            $this->deleteLocalFaviconFile($oldFavicon);
            Setting::setGlobal('platform_brand_logo_favicon', '');
        }

        clear_brand_cache();

        return back()->with('success', 'Favicon default platform berhasil dihapus.');
    }

    private function storeFaviconInPublic(UploadedFile $uploaded): string
    {
        $relativeDir = 'branding';
        $targetDir = public_path($relativeDir);
        File::ensureDirectoryExists($targetDir);

        $extension = strtolower($uploaded->getClientOriginalExtension() ?: $uploaded->extension() ?: 'png');
        $filename = 'platform-favicon-'.now()->format('YmdHis').'-'.Str::random(8).'.'.$extension;

        $uploaded->move($targetDir, $filename);

        return $relativeDir.'/'.$filename;
    }

    private function deleteLocalFaviconFile(mixed $path): void
    {
        if (! is_string($path) || $path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $relative = ltrim($path, '/');
        $publicPath = public_path($relative);
        $storagePath = storage_path('app/public/'.$relative);

        if (File::exists($publicPath)) {
            File::delete($publicPath);
        }

        if (File::exists($storagePath)) {
            File::delete($storagePath);
        }
    }

    private function resolveFaviconUrl(mixed $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relative = ltrim($path, '/');

        if (File::exists(public_path($relative))) {
            return asset($relative);
        }

        if (str_starts_with($relative, 'branding/')) {
            return asset('storage/'.$relative);
        }

        return asset($relative);
    }
}
