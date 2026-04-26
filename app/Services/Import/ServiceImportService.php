<?php

namespace App\Services\Import;

use App\Models\Service;
use App\Models\ServiceCategory;

class ServiceImportService extends BaseImportService
{
    public function getRequiredColumns(): array
    {
        return ['name', 'category', 'duration_minutes'];
    }

    public function getAvailableColumns(): array
    {
        return [
            'name' => 'Nama layanan (wajib)',
            'category' => 'Nama kategori (wajib, akan dibuat otomatis jika tidak ada)',
            'description' => 'Deskripsi layanan',
            'pricing_mode' => 'Mode harga (opsional: fixed/range, default: fixed)',
            'price' => 'Harga tunggal (wajib untuk fixed, angka tanpa format)',
            'price_min' => 'Harga minimum (wajib untuk range, angka tanpa format)',
            'price_max' => 'Harga maksimum (wajib untuk range, angka tanpa format)',
            'incentive' => 'Insentif (opsional, angka tanpa format)',
            'duration_minutes' => 'Durasi dalam menit (wajib)',
            'is_active' => 'Status aktif (1/0, yes/no, aktif/tidak)',
        ];
    }

    public function getSampleData(): array
    {
        return [
            [
                'name' => 'Facial Basic',
                'category' => 'Facial',
                'description' => 'Perawatan wajah dasar dengan pembersihan dan masker',
                'pricing_mode' => 'fixed',
                'price' => '150000',
                'price_min' => '',
                'price_max' => '',
                'incentive' => '25000',
                'duration_minutes' => '60',
                'is_active' => '1',
            ],
            [
                'name' => 'Chemical Peeling',
                'category' => 'Facial',
                'description' => 'Peeling wajah dengan bahan kimia untuk mengangkat sel kulit mati',
                'pricing_mode' => 'fixed',
                'price' => '350000',
                'price_min' => '',
                'price_max' => '',
                'incentive' => '50000',
                'duration_minutes' => '45',
                'is_active' => '1',
            ],
            [
                'name' => 'Laser Hair Removal',
                'category' => 'Laser',
                'description' => 'Penghilangan bulu dengan teknologi laser',
                'pricing_mode' => 'range',
                'price' => '',
                'price_min' => '500000',
                'price_max' => '750000',
                'incentive' => '75000',
                'duration_minutes' => '30',
                'is_active' => '1',
            ],
        ];
    }

    protected function processRow(array $row, int $rowNumber): array
    {
        $name = $this->cleanValue($row['name'] ?? null);
        $categoryName = $this->cleanValue($row['category'] ?? null);
        $durationRaw = $row['duration_minutes'] ?? null;

        // Validate required fields
        if (empty($name)) {
            return ['success' => false, 'message' => 'Nama layanan wajib diisi.'];
        }

        if (empty($categoryName)) {
            return ['success' => false, 'message' => 'Kategori wajib diisi.'];
        }

        $pricing = $this->resolvePricingData($row);
        if (isset($pricing['error'])) {
            return ['success' => false, 'message' => $pricing['error']];
        }

        $incentive = null;
        if (array_key_exists('incentive', $row)) {
            $incentiveRaw = $this->cleanValue($row['incentive'] ?? null);
            if ($incentiveRaw !== null && $incentiveRaw !== '') {
                $incentive = $this->parseNumber($row['incentive']);
                if ($incentive === null || $incentive < 0) {
                    return ['success' => false, 'message' => "Insentif tidak valid: {$row['incentive']}. Masukkan angka positif."];
                }
            }
        }

        $duration = $this->parseNumber($durationRaw);
        if ($duration === null || $duration <= 0) {
            return ['success' => false, 'message' => "Durasi tidak valid: {$durationRaw}. Masukkan angka positif."];
        }

        // Find or create category
        $category = ServiceCategory::where('name', $categoryName)->first();

        if (! $category) {
            $maxSortOrder = ServiceCategory::max('sort_order') ?? 0;
            $category = ServiceCategory::create([
                'name' => $categoryName,
                'description' => null,
                'icon' => null,
                'sort_order' => $maxSortOrder + 1,
                'is_active' => true,
            ]);
        }

        // Check for existing service with same name in same category
        $existingService = Service::withTrashed()
            ->where('name', $name)
            ->where('category_id', $category->id)
            ->first();

        if ($existingService) {
            if ($existingService->trashed()) {
                $existingService->restore();
            }

            // Update existing service
            $existingService->update([
                'description' => $this->cleanValue($row['description'] ?? null) ?? $existingService->description,
                'pricing_mode' => $pricing['pricing_mode'],
                'price' => $pricing['price'],
                'price_min' => $pricing['price_min'],
                'price_max' => $pricing['price_max'],
                'incentive' => $incentive ?? $existingService->incentive,
                'duration_minutes' => (int) $duration,
                'is_active' => isset($row['is_active']) ? $this->parseBoolean($row['is_active']) : $existingService->is_active,
            ]);

            return [
                'success' => true,
                'skipped' => true,
                'message' => "Layanan '{$name}' sudah ada dalam kategori '{$categoryName}', data diperbarui.",
                'data' => $existingService,
            ];
        }

        // Create new service
        $service = Service::create([
            'category_id' => $category->id,
            'name' => $name,
            'description' => $this->cleanValue($row['description'] ?? null),
            'pricing_mode' => $pricing['pricing_mode'],
            'price' => $pricing['price'],
            'price_min' => $pricing['price_min'],
            'price_max' => $pricing['price_max'],
            'incentive' => $incentive ?? 0,
            'duration_minutes' => (int) $duration,
            'is_active' => isset($row['is_active']) ? $this->parseBoolean($row['is_active']) : true,
        ]);

        return [
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan.',
            'data' => $service,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{pricing_mode?: string, price?: float|int, price_min?: float|int, price_max?: float|int, error?: string}
     */
    protected function resolvePricingData(array $row): array
    {
        $pricingModeRaw = strtolower((string) ($this->cleanValue($row['pricing_mode'] ?? null) ?? ''));
        $priceRaw = $row['price'] ?? null;
        $priceMinRaw = $row['price_min'] ?? null;
        $priceMaxRaw = $row['price_max'] ?? null;

        $hasRangeValues = $this->cleanValue($priceMinRaw) !== null || $this->cleanValue($priceMaxRaw) !== null;

        $pricingMode = match ($pricingModeRaw) {
            '', 'fixed' => $hasRangeValues ? Service::PRICING_MODE_RANGE : Service::PRICING_MODE_FIXED,
            'range' => Service::PRICING_MODE_RANGE,
            default => null,
        };

        if ($pricingMode === null) {
            return ['error' => "Mode harga tidak valid: {$pricingModeRaw}. Gunakan fixed atau range."];
        }

        if ($pricingMode === Service::PRICING_MODE_RANGE) {
            $priceMin = $this->parseNumber($priceMinRaw);
            $priceMax = $this->parseNumber($priceMaxRaw);

            if ($priceMin === null || $priceMin < 0) {
                return ['error' => "Harga minimum tidak valid: {$priceMinRaw}. Masukkan angka positif."];
            }

            if ($priceMax === null || $priceMax < 0) {
                return ['error' => "Harga maksimum tidak valid: {$priceMaxRaw}. Masukkan angka positif."];
            }

            if ($priceMax < $priceMin) {
                return ['error' => 'Harga maksimum tidak boleh lebih kecil dari harga minimum.'];
            }

            return [
                'pricing_mode' => Service::PRICING_MODE_RANGE,
                'price' => $priceMin,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
            ];
        }

        $price = $this->parseNumber($priceRaw);
        if ($price === null || $price < 0) {
            return ['error' => "Harga tidak valid: {$priceRaw}. Masukkan angka positif."];
        }

        return [
            'pricing_mode' => Service::PRICING_MODE_FIXED,
            'price' => $price,
            'price_min' => $price,
            'price_max' => $price,
        ];
    }
}
