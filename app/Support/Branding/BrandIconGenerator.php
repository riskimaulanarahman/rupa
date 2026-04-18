<?php

namespace App\Support\Branding;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;

class BrandIconGenerator
{
    /**
     * @var array<string, int>
     */
    private array $pngTargets = [
        'favicon-16x16.png' => 16,
        'favicon-32x32.png' => 32,
        'apple-touch-icon.png' => 180,
        'images/icons/icon-72x72.png' => 72,
        'images/icons/icon-96x96.png' => 96,
        'images/icons/icon-128x128.png' => 128,
        'images/icons/icon-144x144.png' => 144,
        'images/icons/icon-152x152.png' => 152,
        'images/icons/icon-192x192.png' => 192,
        'images/icons/icon-384x384.png' => 384,
        'images/icons/icon-512x512.png' => 512,
    ];

    /**
     * @return array{source:string, generated:array<int,string>}
     */
    public function generate(string $source = 'auto', bool $force = false): array
    {
        $sourcePath = $this->resolveSourcePath($source);
        if (! is_file($sourcePath)) {
            throw new RuntimeException("Source icon file not found: {$sourcePath}");
        }

        $outputRoot = $this->outputRoot();
        $tempRoot = $this->tempRoot();
        File::ensureDirectoryExists($tempRoot);

        $tempDir = $tempRoot.'/brand-icons-'.bin2hex(random_bytes(6));
        File::ensureDirectoryExists($tempDir);

        $generated = [];

        try {
            if ($this->canUseImagick()) {
                $this->generateWithImagick($sourcePath, $tempDir, $force, $generated);
            } else {
                $this->generateWithGd($sourcePath, $tempDir, $force, $generated);
            }

            foreach ($generated as $relativePath) {
                $sourceFile = $tempDir.'/'.$relativePath;
                $targetFile = $outputRoot.'/'.$relativePath;

                File::ensureDirectoryExists(dirname($targetFile));
                File::move($sourceFile, $targetFile);
            }
        } catch (Throwable $e) {
            File::deleteDirectory($tempDir);
            throw $e;
        }

        File::deleteDirectory($tempDir);

        return [
            'source' => $sourcePath,
            'generated' => $generated,
        ];
    }

    public function resolveSourcePath(string $source = 'auto'): string
    {
        $allowed = ['auto', 'logo', 'favicon'];
        if (! in_array($source, $allowed, true)) {
            throw new RuntimeException("Invalid source option '{$source}'. Allowed values: ".implode(', ', $allowed));
        }

        $candidates = [];
        if ($source === 'auto' || $source === 'favicon') {
            $candidates[] = $this->globalSettingValue('platform_brand_logo_favicon');
        }
        if ($source === 'auto' || $source === 'logo') {
            $candidates[] = $this->settingValue('brand_logo_path');
        }
        if ($source === 'auto' || $source === 'favicon') {
            $candidates[] = $this->settingValue('brand_logo_favicon');
        }
        if ($source === 'auto' || $source === 'logo') {
            $candidates[] = config('branding.logo.path');
        }

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $resolved = $this->resolveLocalPath($candidate);
            if ($resolved !== null && is_file($resolved) && filesize($resolved) > 0) {
                return $resolved;
            }
        }

        throw new RuntimeException('No valid logo source found. Please set branding logo/favicon first.');
    }

    /**
     * @param  array<int, string>  $generated
     */
    private function generateWithImagick(string $sourcePath, string $tempDir, bool $force, array &$generated): void
    {
        foreach ($this->pngTargets as $relativePath => $size) {
            if (! $force && $this->targetHasContent($relativePath)) {
                continue;
            }

            $image = $this->renderImagickSquare($sourcePath, $size);
            $output = $tempDir.'/'.$relativePath;
            File::ensureDirectoryExists(dirname($output));
            $image->setImageFormat('png');
            $image->writeImage($output);
            $image->clear();
            $image->destroy();
            $generated[] = $relativePath;
        }

        if (! $force && $this->targetHasContent('favicon.ico')) {
            return;
        }

        $ico = new \Imagick;
        foreach ([16, 32, 48] as $size) {
            $image = $this->renderImagickSquare($sourcePath, $size);
            $image->setImageFormat('png');
            $ico->addImage($image);
            $image->clear();
            $image->destroy();
        }

        $icoPath = $tempDir.'/favicon.ico';
        $ico->writeImages($icoPath, true);
        $ico->clear();
        $ico->destroy();
        $generated[] = 'favicon.ico';
    }

    /**
     * @param  array<int, string>  $generated
     */
    private function generateWithGd(string $sourcePath, string $tempDir, bool $force, array &$generated): void
    {
        $blob = file_get_contents($sourcePath);
        if ($blob === false) {
            throw new RuntimeException('Unable to read source image for icon generation.');
        }

        $source = imagecreatefromstring($blob);
        if ($source === false) {
            throw new RuntimeException('Source image format is invalid for GD processing.');
        }

        foreach ($this->pngTargets as $relativePath => $size) {
            if (! $force && $this->targetHasContent($relativePath)) {
                continue;
            }

            $image = $this->renderGdSquare($source, $size);
            $output = $tempDir.'/'.$relativePath;
            File::ensureDirectoryExists(dirname($output));
            imagepng($image, $output);
            imagedestroy($image);
            $generated[] = $relativePath;
        }

        imagedestroy($source);

        if (! $force && $this->targetHasContent('favicon.ico')) {
            return;
        }

        if (is_executable('/opt/homebrew/bin/convert')) {
            $tmp16 = $tempDir.'/favicon-16x16.png';
            $tmp32 = $tempDir.'/favicon-32x32.png';
            if (! file_exists($tmp16) || ! file_exists($tmp32)) {
                throw new RuntimeException('GD fallback requires generated favicon PNG files before ICO conversion.');
            }
            $command = sprintf(
                '/opt/homebrew/bin/convert %s %s %s/favicon.ico',
                escapeshellarg($tmp16),
                escapeshellarg($tmp32),
                escapeshellarg($tempDir)
            );
            exec($command, $output, $resultCode);
            if ($resultCode !== 0) {
                throw new RuntimeException('Failed to generate favicon.ico with GD fallback.');
            }
            $generated[] = 'favicon.ico';

            return;
        }

        throw new RuntimeException('Imagick is unavailable and ImageMagick CLI is not installed for favicon.ico generation.');
    }

    private function renderImagickSquare(string $sourcePath, int $size): \Imagick
    {
        $source = new \Imagick;
        $source->readImage($sourcePath);
        $source->setImageBackgroundColor(new \ImagickPixel('transparent'));
        $source->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);
        $source->mergeImageLayers(\Imagick::LAYERMETHOD_MERGE);

        $padding = (float) config('branding.icons.padding', 0.11);
        $innerSize = max(1, (int) round($size * (1 - ($padding * 2))));

        $source->thumbnailImage($innerSize, $innerSize, true, true);

        $canvas = new \Imagick;
        $canvas->newImage($size, $size, new \ImagickPixel('transparent'));
        $canvas->setImageFormat('png');

        $x = (int) floor(($size - $source->getImageWidth()) / 2);
        $y = (int) floor(($size - $source->getImageHeight()) / 2);
        $canvas->compositeImage($source, \Imagick::COMPOSITE_DEFAULT, $x, $y);

        $source->clear();
        $source->destroy();

        return $canvas;
    }

    /**
     * @param  \GdImage|resource  $source
     * @return \GdImage|resource
     */
    private function renderGdSquare($source, int $size)
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $padding = (float) config('branding.icons.padding', 0.11);
        $innerSize = max(1, (int) round($size * (1 - ($padding * 2))));
        $scale = min($innerSize / $sourceWidth, $innerSize / $sourceHeight);
        $targetWidth = max(1, (int) floor($sourceWidth * $scale));
        $targetHeight = max(1, (int) floor($sourceHeight * $scale));
        $x = (int) floor(($size - $targetWidth) / 2);
        $y = (int) floor(($size - $targetHeight) / 2);

        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);

        imagecopyresampled(
            $canvas,
            $source,
            $x,
            $y,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        return $canvas;
    }

    private function canUseImagick(): bool
    {
        return class_exists(\Imagick::class) && extension_loaded('imagick');
    }

    private function outputRoot(): string
    {
        return (string) config('branding.icons.output_root', public_path());
    }

    private function tempRoot(): string
    {
        return (string) config('branding.icons.temp_root', storage_path('app/tmp'));
    }

    private function targetHasContent(string $relativePath): bool
    {
        $target = $this->outputRoot().'/'.$relativePath;

        return is_file($target) && filesize($target) > 0;
    }

    private function resolveLocalPath(string $path): ?string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return null;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            $urlPath = parse_url($trimmed, PHP_URL_PATH);
            if (! is_string($urlPath) || $urlPath === '') {
                return null;
            }
            $trimmed = ltrim($urlPath, '/');
        }

        if (str_starts_with($trimmed, 'branding/')) {
            $publicBranding = public_path($trimmed);
            if (is_file($publicBranding)) {
                return $publicBranding;
            }

            return storage_path('app/public/'.$trimmed);
        }

        if (str_starts_with($trimmed, 'storage/')) {
            return public_path($trimmed);
        }

        if (str_starts_with($trimmed, '/storage/')) {
            return public_path(ltrim($trimmed, '/'));
        }

        if (str_starts_with($trimmed, '/')) {
            return $trimmed;
        }

        $publicCandidate = public_path($trimmed);
        if (is_file($publicCandidate)) {
            return $publicCandidate;
        }

        $baseCandidate = base_path($trimmed);
        if (is_file($baseCandidate)) {
            return $baseCandidate;
        }

        return null;
    }

    private function settingValue(string $key): mixed
    {
        try {
            return Setting::get($key);
        } catch (Throwable) {
            return null;
        }
    }

    private function globalSettingValue(string $key): mixed
    {
        try {
            return Setting::getGlobal($key);
        } catch (Throwable) {
            return null;
        }
    }
}
