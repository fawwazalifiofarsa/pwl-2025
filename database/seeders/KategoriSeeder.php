<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kategori_id' => 1,
                'kategori_kode' => 'PAKN',
                'kategori_nama' => 'Pakaian',
            ],
            [
                'kategori_id' => 2,
                'kategori_kode' => 'ELK',
                'kategori_nama' => 'Elektronik',
            ],
            [
                'kategori_id' => 3,
                'kategori_kode' => 'PRT',
                'kategori_nama' => 'Peralatan Rumah Tangga',
            ],
            [
                'kategori_id' => 4,
                'kategori_kode' => 'KPD',
                'kategori_nama' => 'Kecantikan dan Perawatan Diri',
            ],
            [
                'kategori_id' => 5,
                'kategori_kode' => 'MM',
                'kategori_nama' => 'Makanan dan Minuman',
            ],
        ];
        DB::table('m_kategori')->insert($data);
    }
}
