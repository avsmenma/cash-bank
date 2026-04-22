<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ubah kolom tahun dari tipe DATE menjadi SMALLINT (tahun integer).
     * Alasan: di MySQL strict mode (server Linux), memasukkan nilai "2026"
     * ke kolom DATE menyebabkan error. Kolom ini hanya menyimpan tahun (cth: 2026).
     */
    public function up(): void
    {
        // Hapus data lama yang mungkin tidak valid agar tidak crash saat alter
        DB::statement("UPDATE rencana_penerimas SET tahun = NULL WHERE tahun NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'");

        Schema::table('rencana_penerimas', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun')->nullable()->change();
        });
    }

    /**
     * Rollback: kembalikan ke DATE (hati-hati, data yang sudah ada mungkin tidak kompatibel)
     */
    public function down(): void
    {
        Schema::table('rencana_penerimas', function (Blueprint $table) {
            $table->date('tahun')->nullable()->change();
        });
    }
};
