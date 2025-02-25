<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class StokSeeder extends Seeder
{
    public function run()
    {
        $data = [];
        for ($i = 1; $i <= 15; $i++) {
            $data[] = [
                'stok_id' => $i,
                'supplier_id' => rand(1, 3),
                'barang_id' => $i,
                'user_id' => rand(1, 3),
                'stok_tanggal' => Date::now(),
                'stok_jumlah' => rand(10, 50),
            ];
        }

        DB::table('t_stok')->insert($data);
    }
}
