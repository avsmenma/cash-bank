<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ubah nama dua sumber dana Bank Mandiri agar lebih ringkas & sesuai istilah
 * yang dipakai user (OPEX / Cadangan MTN). Pencocokan lewat nomor rekening di
 * akhir nama (stabil) supaya tetap kena walau prefiks nama sedikit berbeda.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('sumber_dana')
            ->where('nama_sumber_dana', 'like', '%146-00-9702740-8')
            ->update(['nama_sumber_dana' => 'PT. Bank Mandiri (OPEX) * 146-00-9702740-8']);

        DB::table('sumber_dana')
            ->where('nama_sumber_dana', 'like', '%146-00-1201420-0')
            ->update(['nama_sumber_dana' => 'PT. Bank Mandiri (Cadangan MTN) * 146-00-1201420-0']);
    }

    public function down(): void
    {
        DB::table('sumber_dana')
            ->where('nama_sumber_dana', 'like', '%146-00-9702740-8')
            ->update(['nama_sumber_dana' => 'PT. Bank Mandiri Cab. Pontianak (Opex) * 146-00-9702740-8']);

        DB::table('sumber_dana')
            ->where('nama_sumber_dana', 'like', '%146-00-1201420-0')
            ->update(['nama_sumber_dana' => 'PT. Bank Mandiri Cab. Pontianak (Rekg Overdraft) * 146-00-1201420-0']);
    }
};
