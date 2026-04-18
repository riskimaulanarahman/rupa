<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Support\Branding\BrandIconGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class BrandIconGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private string $outputRoot;

    private string $tempRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = bin2hex(random_bytes(4));
        $this->outputRoot = storage_path("framework/testing/icons-output-{$suffix}");
        $this->tempRoot = storage_path("framework/testing/icons-temp-{$suffix}");

        config([
            'branding.icons.output_root' => $this->outputRoot,
            'branding.icons.temp_root' => $this->tempRoot,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->outputRoot);
        File::deleteDirectory($this->tempRoot);
        File::delete(storage_path('app/public/branding/test-logo.png'));
        File::delete(storage_path('app/public/branding/test-platform-favicon.png'));
        File::delete(storage_path('app/public/branding/test-owner-logo.png'));

        parent::tearDown();
    }

    public function test_generate_creates_all_expected_icon_files_with_correct_dimensions(): void
    {
        File::ensureDirectoryExists(storage_path('app/public/branding'));
        $sourcePath = storage_path('app/public/branding/test-logo.png');
        $this->createSamplePng($sourcePath, 640, 320);

        Setting::set('brand_logo_path', 'branding/test-logo.png');
        clear_brand_cache();

        $result = app(BrandIconGenerator::class)->generate('logo', true);

        $this->assertSame($sourcePath, $result['source']);

        $expectedPngs = [
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

        foreach ($expectedPngs as $path => $size) {
            $absolute = $this->outputRoot.'/'.$path;
            $this->assertFileExists($absolute);
            $this->assertGreaterThan(0, filesize($absolute));

            $dimension = getimagesize($absolute);
            $this->assertIsArray($dimension);
            $this->assertSame($size, $dimension[0]);
            $this->assertSame($size, $dimension[1]);
        }

        $icoPath = $this->outputRoot.'/favicon.ico';
        $this->assertFileExists($icoPath);
        $this->assertGreaterThan(0, filesize($icoPath));
    }

    public function test_generate_throws_when_source_missing_and_does_not_overwrite_existing_icon(): void
    {
        File::ensureDirectoryExists($this->outputRoot);
        $faviconPath = $this->outputRoot.'/favicon.ico';
        file_put_contents($faviconPath, 'sentinel');

        Setting::set('brand_logo_path', '');
        Setting::set('brand_logo_favicon', '');
        clear_brand_cache();

        $this->expectException(RuntimeException::class);
        app(BrandIconGenerator::class)->generate('auto', true);

        $this->assertSame('sentinel', (string) file_get_contents($faviconPath));
    }

    public function test_auto_source_prefers_platform_global_favicon(): void
    {
        File::ensureDirectoryExists(storage_path('app/public/branding'));

        $globalSourcePath = storage_path('app/public/branding/test-platform-favicon.png');
        $ownerSourcePath = storage_path('app/public/branding/test-owner-logo.png');

        $this->createSamplePng($globalSourcePath, 256, 256);
        $this->createSamplePng($ownerSourcePath, 640, 320);

        Setting::setGlobal('platform_brand_logo_favicon', 'branding/test-platform-favicon.png');
        Setting::set('brand_logo_path', 'branding/test-owner-logo.png');
        clear_brand_cache();

        $result = app(BrandIconGenerator::class)->generate('auto', true);

        $this->assertSame($globalSourcePath, $result['source']);
    }

    private function createSamplePng(string $path, int $width, int $height): void
    {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefilledrectangle($image, 0, 0, $width, $height, $transparent);

        $pink = imagecolorallocatealpha($image, 230, 57, 70, 0);
        imagefilledellipse($image, (int) floor($width / 2), (int) floor($height / 2), (int) floor($width * 0.7), (int) floor($height * 0.7), $pink);

        imagepng($image, $path);
        imagedestroy($image);
    }
}
