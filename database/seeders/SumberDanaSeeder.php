<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SumberDanaSeeder extends Seeder
{
    public function run(): void
    {
        // UPDATE nama sumber dana sesuai data yang benar
        // Format: "Nama Bank * NoRek" agar dashboard bisa parsing no rekening
        $data = [
            ['id_sumber_dana' => 1, 'nama_sumber_dana' => 'PT. Bank Mandiri (OPEX) * 146-00-9702740-8'],
            ['id_sumber_dana' => 2, 'nama_sumber_dana' => 'PT. Bank Mandiri (Cadangan MTN) * 146-00-1201420-0'],
            ['id_sumber_dana' => 3, 'nama_sumber_dana' => 'PT. Bank Raya * 1001001363401'],
            ['id_sumber_dana' => 4, 'nama_sumber_dana' => 'PT. Bank Negara Indonesia - Cab. Pontianak * 2020388992'],
        ];

        foreach ($data as $item) {
            DB::table('sumber_dana')->updateOrInsert(
                ['id_sumber_dana' => $item['id_sumber_dana']],
                array_merge($item, ['updated_at' => now()])
            );
        }

        // Hapus sumber dana yang tidak dipakai (id 5, 6) jika ada
        DB::table('sumber_dana')->whereIn('id_sumber_dana', [5, 6])->delete();

        $this->command->info('Sumber dana berhasil diupdate: 4 bank.');
    }
}
