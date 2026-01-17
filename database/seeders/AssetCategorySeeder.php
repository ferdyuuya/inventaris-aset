<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Printer',
                'code' => 'PRN',
                'description' => 'Perangkat printer dan mesin cetak dokumen',
            ],
            [
                'name' => 'Komputer',
                'code' => 'KMP',
                'description' => 'Perangkat komputer desktop dan personal computer',
            ],
            [
                'name' => 'Laptop',
                'code' => 'LAP',
                'description' => 'Perangkat laptop dan notebook',
            ],
            [
                'name' => 'Monitor',
                'code' => 'MON',
                'description' => 'Layar monitor untuk komputer dan workstation',
            ],
            [
                'name' => 'Keyboard',
                'code' => 'KBD',
                'description' => 'Perangkat keyboard dan input device',
            ],
            [
                'name' => 'Mouse',
                'code' => 'MUS',
                'description' => 'Perangkat mouse dan pointing device',
            ],
            [
                'name' => 'Router',
                'code' => 'RTR',
                'description' => 'Perangkat jaringan router dan access point',
            ],
            [
                'name' => 'Server',
                'code' => 'SRV',
                'description' => 'Perangkat server dan network storage',
            ],
            [
                'name' => 'Scanner',
                'code' => 'SCN',
                'description' => 'Perangkat scanner untuk pemindaian dokumen',
            ],
            [
                'name' => 'Proyektor',
                'code' => 'PRJ',
                'description' => 'Perangkat proyektor untuk presentasi',
            ],
        ];

        foreach ($categories as $category) {
            AssetCategory::firstOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}
