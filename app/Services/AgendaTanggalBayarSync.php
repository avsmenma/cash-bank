<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Menyalin tanggal transaksi Bank Keluar ke kolom `tanggal_dibayar` milik
 * dokumen Agenda Online.
 *
 * Sinkron dijalankan MASSAL — satu query UPDATE per potongan kunci, bukan satu
 * query per baris. Versi per-baris yang lama membuat impor 3000+ baris timeout
 * karena setiap baris menembak database kedua, sehingga dulu dimatikan dan
 * celahnya tidak pernah ditutup.
 *
 * Aturan sinkron (keputusan pemilik):
 * - hanya MENGISI yang masih kosong — tanggal yang sudah ada di Agenda tidak
 *   pernah ditimpa, jadi aman diulang berapa kali pun;
 * - bila satu nomor agenda punya beberapa transaksi, dipakai tanggal TERAWAL;
 * - `status_pembayaran` TIDAK disentuh, supaya alur dokumen dan auto-forward
 *   ke Pembayaran tetap ditentukan operator.
 *
 * Kunci pencocokan: `bank_keluars.agenda_tahun` dibandingkan LANGSUNG dengan
 * `dokumens.nomor_agenda` — keduanya sama-sama komposit '{urut}_{tahun}'
 * (mis. '5006_2026'), jangan dipecah.
 */
class AgendaTanggalBayarSync
{
    /** Banyak nomor agenda per query UPDATE. */
    private const UKURAN_POTONGAN = 500;

    /**
     * Susun pasangan nomor agenda → tanggal terawal dari sekumpulan baris
     * Bank Keluar (boleh array siap-insert maupun objek/model).
     *
     * @param  iterable<array<string,mixed>|object>  $baris
     * @return array<string,string>
     */
    public function petakanTanggalTerawal(iterable $baris): array
    {
        $peta = [];

        foreach ($baris as $b) {
            $b = is_object($b) ? (array) $b : $b;

            $nomorAgenda = trim((string) ($b['agenda_tahun'] ?? ''));
            $tanggal = $b['tanggal'] ?? null;

            if ($nomorAgenda === '' || empty($tanggal)) {
                continue;
            }

            // Buang komponen jam bila ada: kolom tujuan bertipe DATE.
            $tanggal = substr((string) $tanggal, 0, 10);

            if (!isset($peta[$nomorAgenda]) || $tanggal < $peta[$nomorAgenda]) {
                $peta[$nomorAgenda] = $tanggal;
            }
        }

        return $peta;
    }

    /**
     * Sinkronkan sekumpulan baris Bank Keluar ke Agenda Online.
     *
     * Kegagalan sengaja tidak dilempar: penyimpanan Bank Keluar tidak boleh
     * batal hanya karena database kedua bermasalah. Sisa celahnya ditutup oleh
     * command terjadwal `dokumen:backfill-tanggal-bayar` di sisi Agenda.
     *
     * @param  iterable<array<string,mixed>|object>  $baris
     * @return int  jumlah dokumen yang tanggalnya baru terisi
     */
    public function sinkronkan(iterable $baris): int
    {
        $peta = $this->petakanTanggalTerawal($baris);

        if (empty($peta)) {
            return 0;
        }

        $terisi = 0;

        try {
            foreach (array_chunk($peta, self::UKURAN_POTONGAN, true) as $potongan) {
                $terisi += $this->jalankanUpdate($potongan);
            }

            Log::info('[CBSync] Sinkron massal tanggal bayar CB → AO.', [
                'nomor_agenda' => count($peta),
                'dokumen_terisi' => $terisi,
            ]);
        } catch (\Throwable $e) {
            Log::error('[CBSync] Sinkron massal tanggal bayar CB → AO GAGAL.', [
                'nomor_agenda' => count($peta),
                'error' => $e->getMessage(),
            ]);
        }

        return $terisi;
    }

    /**
     * Satu query UPDATE untuk seluruh potongan, memakai CASE agar tiap nomor
     * agenda dapat tanggalnya sendiri. Semua nilai lewat binding.
     *
     * @param  array<string,string>  $potongan
     */
    private function jalankanUpdate(array $potongan): int
    {
        $kasus = '';
        $ikatan = [];

        foreach ($potongan as $nomorAgenda => $tanggal) {
            $kasus .= ' WHEN ? THEN ?';
            // Kunci array PHP yang berupa angka otomatis jadi integer —
            // kembalikan ke teks agar cocok dengan kolom nomor_agenda.
            $ikatan[] = (string) $nomorAgenda;
            $ikatan[] = $tanggal;
        }

        $nomorAgendaList = array_map('strval', array_keys($potongan));
        $isian = implode(',', array_fill(0, count($nomorAgendaList), '?'));
        $ikatan = array_merge($ikatan, $nomorAgendaList);

        $sql = "UPDATE dokumens
                   SET tanggal_dibayar = CASE nomor_agenda{$kasus} END
                 WHERE tanggal_dibayar IS NULL
                   AND nomor_agenda IN ({$isian})";

        return DB::connection('mysql_agenda_online')->update($sql, $ikatan);
    }
}
