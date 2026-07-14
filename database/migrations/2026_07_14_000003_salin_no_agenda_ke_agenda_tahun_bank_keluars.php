<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Perbaikan data: import template bank keluar sebelumnya menyimpan nomor
     * agenda hanya ke kolom no_agenda, padahal tabel & fitur lain membaca
     * agenda_tahun — akibatnya kolom Agenda tampil kosong. Salin nilainya
     * untuk baris yang terlanjur terimpor tanpa agenda_tahun.
     */
    public function up(): void
    {
        DB::table('bank_keluars')
            ->whereNotNull('no_agenda')
            ->where('no_agenda', '!=', '')
            ->where(function ($q) {
                $q->whereNull('agenda_tahun')->orWhere('agenda_tahun', '');
            })
            ->update(['agenda_tahun' => DB::raw('no_agenda')]);
    }

    public function down(): void
    {
        // Perbaikan data satu arah — tidak di-rollback.
    }
};
