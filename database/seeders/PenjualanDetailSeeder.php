<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanDetailSeeder extends Seeder
{
    public function run()
    {
        $data = [];
        for ($i = 1; $i <= 30; $i++) {
            $data[] = [
                'detail_id' => $i,
                'penjualan_id' => ceil($i / 3), // 3 barang per penjualan
                'barang_id' => rand(1, 15),
                'harga' => rand(50000, 100000),
                'jumlah' => rand(1, 5),
            ];
        }

        DB::table('t_penjualan_detail')->insert($data);
    }
}
