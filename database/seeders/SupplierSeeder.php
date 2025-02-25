<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['supplier_id' => 1, 'supplier_kode' => 'SUP01', 'supplier_nama' => 'Supplier A', 'supplier_alamat' => 'Jl. A No. 1'],
            ['supplier_id' => 2, 'supplier_kode' => 'SUP02', 'supplier_nama' => 'Supplier B', 'supplier_alamat' => 'Jl. B No. 2'],
            ['supplier_id' => 3, 'supplier_kode' => 'SUP03', 'supplier_nama' => 'Supplier C', 'supplier_alamat' => 'Jl. C No. 3'],
        ];

        DB::table('m_supplier')->insert($data);
    }
}
