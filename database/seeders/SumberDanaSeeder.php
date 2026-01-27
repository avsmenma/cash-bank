<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SumberDanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id_sumber_dana' => 1, 'nama_sumber_dana' => 'PT. Bank Mandiri (Collection Account) * 146-00-044...'],
            ['id_sumber_dana' => 2, 'nama_sumber_dana' => 'PT. Bank Mandiri (Cadangan MTN) * 146-00-1201420-0'],
            ['id_sumber_dana' => 3, 'nama_sumber_dana' => 'PT. Bank Mandiri (OPEX) * 146-00-9702740-8'],
            ['id_sumber_dana' => 4, 'nama_sumber_dana' => 'PT. Bank Mandiri (Rekg Escrow) *146-00-1862042-2'],
            ['id_sumber_dana' => 5, 'nama_sumber_dana' => 'PT. Bank Mandiri (CAPEX) * 146-00-1923084-1'],
            ['id_sumber_dana' => 6, 'nama_sumber_dana' => 'PT. Bank Mandiri (Quick Win) * 146-00-0070130-5'],
        ];

        foreach ($data as $item) {
            DB::table('sumber_dana')->updateOrInsert(
                ['id_sumber_dana' => $item['id_sumber_dana']],
                array_merge($item, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
