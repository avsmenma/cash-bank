<?php

namespace App\Imports;

use App\Models\BankMasuk;
use App\Models\BankTujuan;
use App\Models\SumberDana;
use Illuminate\Support\Carbon;
use App\Models\JenisPembayaran;
use App\Models\KategoriKriteria;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithSheetIndex;


class importMasuk implements ToModel, WithHeadingRow
{
    use Importable;

    /** fingerprint dari data yang sudah ada di DB (untuk skip duplikat) */
    private array $existingKeys = [];

    public function __construct()
    {
        // Load semua data yang ada: tanggal + sumber_dana + uraian + debet
        $existing = \App\Models\BankMasuk::leftJoin('sumber_dana', 'bank_masuk.id_sumber_dana', '=', 'sumber_dana.id_sumber_dana')
            ->select('bank_masuk.tanggal', 'sumber_dana.nama_sumber_dana', 'bank_masuk.uraian', 'bank_masuk.debet')
            ->get();

        foreach ($existing as $row) {
            $key = $this->makeKey(
                $row->tanggal ?? '',
                $row->nama_sumber_dana ?? '',
                $row->uraian ?? '',
                (int)$row->debet
            );
            $this->existingKeys[$key] = true;
        }
    }

    private function makeKey(string $tanggal, string $sumber, string $uraian, int $debet): string
    {
        return md5($tanggal . '||' . strtolower(trim($sumber)) . '||' . strtolower(trim($uraian)) . '||' . $debet);
    }

    public function bindValue(Cell $cell, $value)
    {
        $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
        return true;
    }

    private function cleanText($text)
    {
        return trim(
            preg_replace('/\s+/', ' ',
                str_replace(["\r", "\n"], ' ', $text)
            )
        );
    }

    
    private function parseTanggal($val)
    {
        if ($val === null || $val === '') return null;

        // JIKA EXCEL SUDAH JADI NUMERIC
        if (is_numeric($val)) {
            $dt = \Carbon\Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $val)
            );

            // 🔥 PAKSA BALIK: anggap Excel salah bulan
            return \Carbon\Carbon::create(
                $dt->year,
                $dt->day,   // bulan = hari
                $dt->month  // hari = bulan
            )->format('Y-m-d');
        }

        // STRING (3/1/2025, 3-1-2025, dst)
        $val = str_replace(['-', '.'], '/', trim($val));

        return \Carbon\Carbon::createFromFormat('d/m/Y', $val)
            ->format('Y-m-d');
    }
    public function model(array $row)
    {
        // ambil text dulu
        $sumberDana      = $this->cleanText($row['sumber_dana'] ?? '');
        $bankTujuan      = $this->cleanText($row['bank_tujuan'] ?? '');
        $kategori        = $this->cleanText($row['kategori'] ?? '');
        $jenisPembayaran = $this->cleanText($row['jenis_pembayaran'] ?? '');
        $uraian          = $this->cleanText($row['uraian'] ?? '');
        $debet = isset($row['debet'])
            ? (int) str_replace(['.', ','], '', $row['debet'])
            : 0;
        $tanggal = $this->parseTanggal($row['tanggal'] ?? null);

        // ===== SKIP DUPLIKAT (opsi A) =====
        // Cari nama sumber dana yang cocok untuk fingerprint
        $sumberNama = '';
        if (!empty($sumberDana)) {
            $sd = SumberDana::where('nama_sumber_dana', 'LIKE', "%{$sumberDana}%")->first();
            $sumberNama = $sd ? $sd->nama_sumber_dana : $sumberDana;
        }
        $fingerprint = $this->makeKey($tanggal ?? '', $sumberNama, $uraian, $debet);
        if (isset($this->existingKeys[$fingerprint])) {
            return null; // skip duplikat
        }
        // Daftarkan ke cache agar tidak duplikat antar baris dalam 1 file
        $this->existingKeys[$fingerprint] = true;

        return new BankMasuk([
             'agenda_tahun'  => $row['agenda_tahun'] ?? null,
             'tanggal'       => $tanggal,

             'id_sumber_dana' => $sumberDana ? SumberDana::where('nama_sumber_dana', 'LIKE', "%{$sumberDana}%")
                ->value('id_sumber_dana') : null,

            'id_bank_tujuan' => $bankTujuan !== ''
                ? BankTujuan::where('nama_tujuan', 'LIKE', "%{$bankTujuan}%")->value('id_bank_tujuan')
                : null,

            'id_kategori_kriteria' => $kategori !== '' ? KategoriKriteria::where('nama_kriteria', 'LIKE', "%{$kategori}%")
                ->value('id_kategori_kriteria') : null,

            'id_jenis_pembayaran' => $jenisPembayaran !== '' ? JenisPembayaran::where('nama_jenis_pembayaran', 'LIKE', "%{$jenisPembayaran}%")
                ->value('id_jenis_pembayaran') : null,

            'penerima' => $row['penerima'] ?? null,
            'uraian'   => $uraian,

            'debet'       => $debet,
            'nilai_rupiah' => $debet,
            'kredit'      => 0,

            'jenis_pembayaran' => $row['jenis_pembayaran'] ?? null,
        ]);
    }
}
