<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NAMA_KPJ = '8102910000000000002 - KPJ';

    public function up(): void
    {
        // 1. Bersihkan 'Bank 408' jika ada dan tidak memiliki transaksi
        $b408 = DB::table('bank_tujuan')->where('nama_tujuan', 'Bank 408')->first();
        if ($b408) {
            $hasMasuk = DB::table('bank_masuk')->where('id_bank_tujuan', $b408->id_bank_tujuan)->exists();
            $hasKeluar = DB::table('bank_keluars')->where('id_bank_tujuan', $b408->id_bank_tujuan)->exists();
            if (!$hasMasuk && !$hasKeluar) {
                DB::table('bank_tujuan')->where('id_bank_tujuan', $b408->id_bank_tujuan)->delete();
            }
        }

        // 2. Tambah master Bank Tujuan: 8102910000000000002 - KPJ jika belum ada
        $ada = DB::table('bank_tujuan')->where('nama_tujuan', self::NAMA_KPJ)->exists();
        if (!$ada) {
            DB::table('bank_tujuan')->insert([
                'nama_tujuan' => self::NAMA_KPJ,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('bank_tujuan')->where('nama_tujuan', self::NAMA_KPJ)->delete();
    }
};
