<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT. Teknologi Indonesia',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'phone' => '+62-21-5555-1234',
                'email' => 'sales@teknologi-indonesia.co.id',
            ],
            [
                'name' => 'CV. Komputer Terpadu',
                'address' => 'Jl. Gatot Subroto No. 456, Jakarta Selatan',
                'phone' => '+62-21-7777-5678',
                'email' => 'info@komputer-terpadu.com',
            ],
            [
                'name' => 'PT. Perangkat Digital Nusantara',
                'address' => 'Jl. Ahmad Yani No. 789, Bandung',
                'phone' => '+62-22-8888-9012',
                'email' => 'contact@perangkat-digital.co.id',
            ],
            [
                'name' => 'Toko Elektronik Maju Jaya',
                'address' => 'Jl. Diponegoro No. 234, Surabaya',
                'phone' => '+62-31-6666-3456',
                'email' => 'sales@majujaya.co.id',
            ],
            [
                'name' => 'PT. Solusi Teknologi Terpercaya',
                'address' => 'Jl. Imam Bonjol No. 567, Medan',
                'phone' => '+62-61-4444-7890',
                'email' => 'support@solusi-teknologi.com',
            ],
            [
                'name' => 'CV. Distributor Aset IT',
                'address' => 'Jl. Cendrawasih No. 890, Yogyakarta',
                'phone' => '+62-274-9999-2345',
                'email' => 'order@distributor-aset-it.co.id',
            ],
            [
                'name' => 'PT. Perangkat Kantor Modern',
                'address' => 'Jl. Pemuda No. 012, Makassar',
                'phone' => '+62-411-3333-6789',
                'email' => 'inquiry@perangkat-kantor.co.id',
            ],
            [
                'name' => 'Toko Grosir Komputer Elektronik',
                'address' => 'Jl. Merdeka No. 345, Semarang',
                'phone' => '+62-24-2222-0123',
                'email' => 'sales@toko-grosir-komputer.com',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['name' => $supplier['name']],
                $supplier
            );
        }
    }
}
