<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'supplier_id' => 1,
                'supplier_kode' => 'NJG',
                'supplier_nama' => 'Nathanael Juan Gracedo',
                'supplier_alamat' => 'Kediri',
            ],
            [
                'supplier_id' => 2,
                'supplier_kode' => 'AK',
                'supplier_nama' => 'Ahmad Kasim',
                'suplier_alamat' => 'Blitar',
            ],
            [
                'supplier_id' => 3,
                'supplier_kode' => 'AM',
                'supplier_nama' => 'Ah Meng',
                'supplier_alamat' => 'Surabaya',
            ],
        ];
        DB::table('m_supplier')->insert($data);
    }
}
