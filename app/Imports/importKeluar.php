<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\bankKeluar;
use App\Models\BankTujuan;
use App\Models\SumberDana;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use App\Models\JenisPembayaran;
use App\Models\KategoriKriteria;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class importKeluar implements ToModel, WithHeadingRow, WithChunkReading, WithCalculatedFormulas
{
    use Importable;

    public function chunkSize(): int
    {
        return 500;
    }

    private function cleanText($text)
    {
        if ($text === null || $text === '')
            return '';

        // Force ke string untuk handle scientific notation
        $text = (string) $text;

        return trim(
            preg_replace(
                '/\s+/',
                ' ',
                str_replace(["\r", "\n"], ' ', $text)
            )
        );
    }

    private function parseTanggal($val)
    {
        if ($val === null || $val === '')
            return null;
        if (is_numeric($val) && $val > 1000) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $val);
                return $dt->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
        $val = str_replace(['-', '.'], '/', trim((string) $val));

        // Validasi format
        if (!preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $val)) {
            return null;
        }
        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y', $val)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse nilai angka dari format Indonesia (titik sebagai ribuan, koma sebagai desimal)
     */
    private function parseNilai($val)
    {
        if ($val === null || $val === '')
            return 0;

        // Jika sudah numeric, kembalikan langsung
        if (is_numeric($val)) {
            return (int) $val;
        }

        // Hapus titik ribuan dan ganti koma desimal dengan titik
        $cleaned = str_replace(['.', ','], ['', '.'], (string) $val);

        return (int) floatval($cleaned);
    }

    public function model(array $row)
    {
        $agendaTahun = $this->cleanText($row['agenda_tahun'] ?? '');
        $sumberDana = $this->cleanText($row['sumber_dana'] ?? '');
        $bankTujuan = $this->cleanText($row['bank_tujuan'] ?? '');
        $kategori = $this->cleanText($row['kategori'] ?? '');
        $subKategori = $this->cleanText($row['sub_kriteria'] ?? '');
        $itemSubKriteria = $this->cleanText($row['item_sub_kriteria'] ?? '');
        $jenisPembayaran = $this->cleanText($row['jenis_pembayaran'] ?? '');
        $penerima = $this->cleanText($row['penerima'] ?? '');
        $uraian = $this->cleanText($row['uraian'] ?? '');

        // Kolom Debet dari Excel → kredit di database
        $kredit = $this->parseNilai($row['debet'] ?? 0);

        // =====================================
        // LOGIC UNTUK UPDATE AGENDA ONLINE
        // =====================================
        $dokumenId = null;

        // Jika ada agenda_tahun dan mengandung "_2026", update status di Agenda Online
        if (!empty($agendaTahun) && strpos($agendaTahun, '_2026') !== false) {
            try {
                // Cari dokumen berdasarkan nomor_agenda yang sama persis
                $dokumen = DB::connection('mysql_agenda_online')
                    ->table('dokumens')
                    ->where('nomor_agenda', $agendaTahun)
                    ->first();

                if ($dokumen) {
                    $dokumenId = $dokumen->id;

                    // Update status menjadi sudah_dibayar
                    DB::connection('mysql_agenda_online')
                        ->table('dokumens')
                        ->where('id', $dokumen->id)
                        ->update([
                            'status_pembayaran' => 'sudah_dibayar',
                            'dibayar' => $kredit,
                            'tanggal_dibayar' => $this->parseTanggal($row['tanggal'] ?? null),
                        ]);

                    \Log::info("Agenda Online updated: {$agendaTahun} -> sudah_dibayar");
                } else {
                    \Log::warning("Dokumen not found in Agenda Online: {$agendaTahun}");
                }
            } catch (\Exception $e) {
                \Log::error("Error updating Agenda Online for {$agendaTahun}: " . $e->getMessage());
            }
        }

        // =====================================
        // CREATE BANK KELUAR RECORD
        // =====================================
        return new bankKeluar([
            'agenda_tahun' => $agendaTahun ?: null,
            'dokumen_id' => $dokumenId,
            'tanggal' => $this->parseTanggal($row['tanggal'] ?? null),

            'id_sumber_dana' => $sumberDana !== ''
                ? SumberDana::where('nama_sumber_dana', 'LIKE', "%{$sumberDana}%")->value('id_sumber_dana')
                : null,

            'id_bank_tujuan' => $bankTujuan !== ''
                ? BankTujuan::where('nama_tujuan', 'LIKE', "%{$bankTujuan}%")->value('id_bank_tujuan')
                : null,

            'id_kategori_kriteria' => $kategori !== ''
                ? KategoriKriteria::where('nama_kriteria', 'LIKE', "%{$kategori}%")->value('id_kategori_kriteria')
                : null,

            'id_sub_kriteria' => $subKategori !== ''
                ? SubKriteria::where('nama_sub_kriteria', 'LIKE', "%{$subKategori}%")->value('id_sub_kriteria')
                : null,

            'id_item_sub_kriteria' => $itemSubKriteria !== ''
                ? ItemSubKriteria::where('nama_item_sub_kriteria', 'LIKE', "%{$itemSubKriteria}%")->value('id_item_sub_kriteria')
                : null,

            'id_jenis_pembayaran' => $jenisPembayaran !== ''
                ? JenisPembayaran::where('nama_jenis_pembayaran', 'LIKE', "%{$jenisPembayaran}%")->value('id_jenis_pembayaran')
                : null,

            'penerima' => $penerima ?: null,
            'uraian' => $uraian ?: null,

            'kredit' => $kredit,
            'debet' => 0,
            'nilai_rupiah' => $kredit,
        ]);
    }
}