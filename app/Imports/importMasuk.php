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
