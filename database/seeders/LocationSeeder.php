<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Gudang Utama',
                'description' => 'Ruang gudang utama untuk penyimpanan barang aset',
            ],
            [
                'name' => 'Ruang IT',
                'description' => 'Ruang khusus untuk perangkat IT dan server',
            ],
            [
                'name' => 'Ruang Kantor Lantai 1',
                'description' => 'Ruang kerja dan kantor di lantai pertama',
            ],
            [
                'name' => 'Ruang Kantor Lantai 2',
                'description' => 'Ruang kerja dan kantor di lantai kedua',
            ],
            [
                'name' => 'Ruang Rapat',
                'description' => 'Ruang pertemuan dan rapat untuk diskusi',
            ],
            [
                'name' => 'Ruang Meeting',
                'description' => 'Ruang meeting dengan perangkat presentasi',
            ],
            [
                'name' => 'Ruang Arsip',
                'description' => 'Ruang penyimpanan dokumen dan arsip penting',
            ],
            [
                'name' => 'Ruang Perpustakaan',
                'description' => 'Ruang perpustakaan dan referensi',
            ],
            [
                'name' => 'Ruang Server',
                'description' => 'Ruang pusat data dengan sistem pendingin',
            ],
            [
                'name' => 'Ruang Backup',
                'description' => 'Ruang penyimpanan backup dan disaster recovery',
            ],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(
                ['name' => $location['name']],
                $location
            );
        }
    }
}
