<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class PenjualanSeeder extends Seeder
{
    public function run()
    {
        $data = [];
        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'penjualan_id' => $i,
                'user_id' => rand(1, 3),
                'pembeli' => 'Customer ' . $i,
                'penjualan_kode' => 'PJ' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'penjualan_tanggal' => Date::now(),
            ];
        }

        DB::table('t_penjualan')->insert($data);
    }
}
