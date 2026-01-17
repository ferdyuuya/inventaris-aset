<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetGenerationService
{
    /**
     * Generate asset records from procurement
     *
     * @param array $procurementData Array containing procurement information
     * @return int Number of assets created
     * @throws \Exception
     */
    public function generateAssets(array $procurementData): int
    {
        Log::info('Asset Generation Started', [
            'procurement_name' => $procurementData['name'] ?? null,
            'category_id' => $procurementData['asset_category_id'] ?? null,
            'quantity' => $procurementData['quantity'] ?? null,
        ]);

        return DB::transaction(function () use ($procurementData) {
            try {
                $category = AssetCategory::findOrFail($procurementData['asset_category_id']);
                $year = $procurementData['procurement_date']->year;
                $quantity = (int)$procurementData['quantity'];

                Log::info('Asset Generation Details', [
                    'category_code' => $category->code,
                    'year' => $year,
                    'quantity' => $quantity,
                    'supplier_id' => $procurementData['supplier_id'] ?? null,
                ]);

                // Get the next sequence number for this category and year
                $nextSequence = $this->getNextSequenceNumber($category->code, $year);
                
                Log::info('Next Sequence Calculated', [
                    'next_sequence' => $nextSequence,
                    'category_code' => $category->code,
                    'year' => $year,
                ]);

                // Generate assets
                $assetsCreated = 0;
                for ($i = 0; $i < $quantity; $i++) {
                    $sequenceNumber = $nextSequence + $i;
                    $assetCode = $this->generateAssetCode(
                        $category->code,
                        $year,
                        $sequenceNumber
                    );

                    Log::debug('Creating Asset', [
                        'asset_code' => $assetCode,
                        'name' => $procurementData['name'],
                        'category_id' => $procurementData['asset_category_id'],
                    ]);

                    Asset::create([
                        'asset_code' => $assetCode,
                        'name' => $procurementData['name'],
                        'category_id' => $procurementData['asset_category_id'],
                        'location_id' => $procurementData['location_id'],
                        'supplier_id' => $procurementData['supplier_id'] ?? null,
                        'purchase_date' => $procurementData['procurement_date'],
                        'purchase_price' => (float)$procurementData['unit_price'],
                        'invoice_number' => $procurementData['invoice_number'] ?? null,
                        'condition' => 'baik',
                        'is_available' => true,
                        'status' => 'aktif',
                    ]);

                    $assetsCreated++;
                }

                Log::info('Asset Generation Completed', [
                    'total_assets_created' => $assetsCreated,
                    'category_code' => $category->code,
                    'year' => $year,
                ]);

                return $assetsCreated;
            } catch (\Exception $e) {
                Log::error('Asset Generation Failed', [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Get the next sequence number for a category in a given year
     *
     * Algorithm:
     * 1. Find all assets matching pattern: CATEGORYCODE-YEAR-*
     * 2. Extract the last asset code (highest sequence number)
     * 3. Return next sequence number (last + 1)
     * 4. If no assets exist for this category/year, start from 1
     *
     * @param string $categoryCode Category code (e.g., 'PRN')
     * @param int $year Year (e.g., 2026)
     * @return int Next sequence number to use
     */
    private function getNextSequenceNumber(string $categoryCode, int $year): int
    {
        // Find the last asset with this category code and year
        // Pattern: PRN-2026-%
        $pattern = $categoryCode . '-' . $year . '-%';

        $lastAsset = Asset::where('asset_code', 'like', $pattern)
            ->orderBy('asset_code', 'desc')
            ->first();

        if (!$lastAsset) {
            // First asset for this category in this year
            Log::debug('First Asset in Year', [
                'category_code' => $categoryCode,
                'year' => $year,
            ]);
            return 1;
        }

        // Extract sequence number from asset code
        // Format: PRN-2026-000015
        $parts = explode('-', $lastAsset->asset_code);
        if (count($parts) === 3) {
            $sequenceNumber = intval($parts[2]);
            Log::debug('Sequence Extracted', [
                'last_asset_code' => $lastAsset->asset_code,
                'sequence_number' => $sequenceNumber,
                'next_sequence' => $sequenceNumber + 1,
            ]);
            return $sequenceNumber + 1;
        }

        Log::warning('Invalid Asset Code Format', [
            'asset_code' => $lastAsset->asset_code,
            'parts_count' => count($parts),
        ]);

        return 1;
    }

    /**
     * Generate asset code in format: CATEGORYCODE-YEAR-XXXXXX
     *
     * @param string $categoryCode Category code (e.g., 'PRN')
     * @param int $year Year (e.g., 2026)
     * @param int $sequence Sequence number
     * @return string Formatted asset code (e.g., 'PRN-2026-000001')
     */
    private function generateAssetCode(string $categoryCode, int $year, int $sequence): string
    {
        return sprintf(
            '%s-%d-%06d',
            $categoryCode,
            $year,
            $sequence
        );
    }
}
