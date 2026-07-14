<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah master Bank Tujuan baru: 8102910000000000001 - KPB.
     * Lewat migration (bukan hanya seeder) agar ikut terpasang otomatis
     * saat server perusahaan git pull + php artisan migrate.
     */
    private const NAMA = '8102910000000000001 - KPB';

    public function up(): void
    {
        $ada = DB::table('bank_tujuan')->where('nama_tujuan', self::NAMA)->exists();
        if (!$ada) {
            DB::table('bank_tujuan')->insert([
                'nama_tujuan' => self::NAMA,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('bank_tujuan')->where('nama_tujuan', self::NAMA)->delete();
    }
};
