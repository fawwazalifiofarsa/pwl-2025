<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'barang_id' => 1,
                'kategori_id' =>  1,
                'barang_kode' => 'KMJ',
                'barang_nama' => 'Kemeja',
                'harga_beli' => 70000,
                'harga_jual' => 75000,
            ],
            [
                'barang_id' => 2,
                'kategori_id' => 1,
                'barang_kode' => 'KS',
                'barang_nama' => 'Kaos',
                'harga_beli' => 50000,
                'harga_jual' => 55000,
            ],
            [
                'barang_id' => 3,
                'kategori_id' => 1,
                'barang_kode' => 'CL',
                'barang_nama' => 'Celana',
                'harga_beli' => 60000,
                'harga_jual' => 65000, 
            ],
            [
                'barang_id' => 4,
                'kategori_id' => 2,
                'barang_kode' => 'KPS',
                'barang_nama' => 'Kipas Angin',
                'harga_beli' => 80000,
                'harga_jual' => 85000,
            ],
            [
                'barang_id' => 5,
                'kategori_id' => 2,
                'barang_kode' => 'STK',
                'barang_nama' => 'Setrika',
                'harga_beli' => 70000,
                'harga_jual' => 75000,
            ],
            [
                'barang_id' => 6,
                'kategori_id' => 2,
                'barang_kode' => 'KLKS',
                'barang_nama' => 'Kulkas',
                'harga_beli' => 350000,
                'harga_jual' => 375000,
            ],
            [
                'barang_id' => 7,
                'kategori_id' => 3,
                'barang_kode' => 'GYNG',
                'barang_nama' => 'Gayung',
                'harga_beli' => 25000,
                'harga_jual' => 26000,
            ],
            [
                'barang_id' => 8,
                'kategori_id' => 3,
                'barang_kode' => 'SP',
                'barang_nama' => 'Sapu',
                'harga_beli' => 15000,
                'harga_jual' => 17000,
            ],
            [
                'barang_id' => 9,
                'kategori_id' => 3,
                'barang_kode' => 'KMCNG',
                'barang_nama' => 'Kemucing',
                'harga_beli' => 15000,
                'harga_jual' => 17000,
            ],
            [
                'barang_id' => 10,
                'kategori_id' => 4,
                'barang_kode' => 'FW',
                'barang_nama' => 'Face Wash',
                'harga_beli' => 45000,
                'harga_jual' => 50000,
            ],
            [
                'barang_id' => 11,
                'kategori_id' => 4,
                'barang_kode' => 'SS',
                'barang_nama' => 'SuncScreen',
                'harga_beli' => 30000,
                'harga_jual' => 35000,
            ],
            [
                'barang_id' => 12,
                'kategori_id' => 4,
                'barang_kode' => 'MM',
                'barang_nama' => 'Moisturizer',
                'harga_beli' => 40000,
                'harga_jual' => 45000,
            ],
            [
                'barang_id' => 13,
                'kategori_id' => 5,
                'barang_kode' => 'HBR',
                'barang_nama' => 'Hamburger',
                'harga_beli' => 30000,
                'harga_jual' => 33000,
            ],
            [
                'barang_id' => 14,
                'kategori_id' => 5,
                'barang_kode' => 'NGT',
                'barang_nama' => 'Nugget',
                'harga_beli' => 23000,
                'harga_jual' => 25000,
            ],
            [
                'barang_id' => 15,
                'kategori_id' => 5,
                'barang_kode' => 'FF',
                'barang_nama' => 'French Fries',
                'harga_beli' => 21000,
                'harga_jual' => 23000,
            ]
        ];
        DB::table('m_barang')->insert($data);
    }
}
