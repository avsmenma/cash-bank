<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update existing records where id_bank_tujuan was null
        $dasal = DB::table('bank_tujuan')->where('nama_tujuan', 'like', '%DASAL%')->value('id_bank_tujuan');
        $pppbb = DB::table('bank_tujuan')->where('nama_tujuan', 'like', '%PPPBB%')->value('id_bank_tujuan');
        $pelaihari = DB::table('bank_tujuan')->where('nama_tujuan', 'like', '%PELAIHARI%')->value('id_bank_tujuan');
        $pandawa = DB::table('bank_tujuan')->where('nama_tujuan', 'like', '%PANDAWA%')->value('id_bank_tujuan');

        if ($dasal) {
            DB::table('bank_masuk')
                ->where('tanggal', '2026-07-21')
                ->where('debet', 35200000)
                ->whereNull('id_bank_tujuan')
                ->update(['id_bank_tujuan' => $dasal]);
        }

        if ($pppbb) {
            DB::table('bank_masuk')
                ->where('tanggal', '2026-07-21')
                ->where('debet', 13719961)
                ->whereNull('id_bank_tujuan')
                ->update(['id_bank_tujuan' => $pppbb]);
        }

        if ($pelaihari) {
            DB::table('bank_masuk')
                ->where('tanggal', '2026-07-21')
                ->where('debet', 11845000)
                ->whereNull('id_bank_tujuan')
                ->update(['id_bank_tujuan' => $pelaihari]);
        }

        if ($pandawa) {
            DB::table('bank_masuk')
                ->where('tanggal', '2026-07-21')
                ->where('debet', 297189)
                ->whereNull('id_bank_tujuan')
                ->update(['id_bank_tujuan' => $pandawa]);
        }

        // 2. Insert missing July 27 transactions if not already present
        $tajati = DB::table('bank_tujuan')->where('nama_tujuan', 'like', '%TAJATI%')->value('id_bank_tujuan');
        $ugkst = DB::table('bank_tujuan')->where('nama_tujuan', 'like', '%UGKST%')->value('id_bank_tujuan');
        $tabara = DB::table('bank_tujuan')->where('nama_tujuan', 'like', '%TABARA%')->value('id_bank_tujuan');
        $sumberDanaOpex = DB::table('sumber_dana')->where('nama_sumber_dana', 'like', '%9702740%')->value('id_sumber_dana') ?? 1;

        if ($tajati && !DB::table('bank_masuk')->where('tanggal', '2026-07-27')->where('id_bank_tujuan', $tajati)->where('debet', 100750000)->exists()) {
            DB::table('bank_masuk')->insert([
                'tanggal' => '2026-07-27',
                'id_sumber_dana' => $sumberDanaOpex,
                'id_bank_tujuan' => $tajati,
                'uraian' => 'Pembayaran atas Permintaan uang kerja untuk Kebutuhan BBM Dexlite',
                'debet' => 100750000,
                'nilai_rupiah' => 100750000,
                'kredit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($ugkst && !DB::table('bank_masuk')->where('tanggal', '2026-07-27')->where('id_bank_tujuan', $ugkst)->where('debet', 17733565)->exists()) {
            DB::table('bank_masuk')->insert([
                'tanggal' => '2026-07-27',
                'id_sumber_dana' => $sumberDanaOpex,
                'id_bank_tujuan' => $ugkst,
                'uraian' => 'Pembayaran biaya Kunjungan Region Head sesuai Memo Elektronik No. 5UKS/5BSH/eM-28/VII/2026 tgl. 02 Juli 2026',
                'debet' => 17733565,
                'nilai_rupiah' => 17733565,
                'kredit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($tabara && !DB::table('bank_masuk')->where('tanggal', '2026-07-27')->where('id_bank_tujuan', $tabara)->where('debet', 9625000)->exists()) {
            DB::table('bank_masuk')->insert([
                'tanggal' => '2026-07-27',
                'id_sumber_dana' => $sumberDanaOpex,
                'id_bank_tujuan' => $tabara,
                'uraian' => 'Pembayaran atas Permintaan uang kerja untuk Kebutuhan Pelumas/Oli',
                'debet' => 9625000,
                'nilai_rupiah' => 9625000,
                'kredit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
