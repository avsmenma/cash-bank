<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id_jenis_pembayaran' => 1, 'nama_jenis_pembayaran' => 'Karyawan'],
            ['id_jenis_pembayaran' => 2, 'nama_jenis_pembayaran' => 'Mitra'],
            ['id_jenis_pembayaran' => 3, 'nama_jenis_pembayaran' => 'dropping'],
            ['id_jenis_pembayaran' => 4, 'nama_jenis_pembayaran' => 'MPN'],
            ['id_jenis_pembayaran' => 7, 'nama_jenis_pembayaran' => 'Lainnya'],
        ];

        foreach ($data as $item) {
            DB::table('jenis_pembayarans')->updateOrInsert(
                ['id_jenis_pembayaran' => $item['id_jenis_pembayaran']],
                array_merge($item, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
