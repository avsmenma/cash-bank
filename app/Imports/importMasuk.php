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

    protected $referenceCache = [];

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

    private function normalizeReferenceText(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/\s+/', ' ', $value);

        return $value ?? '';
    }

    private function extractReferenceNumbers(?string $value): array
    {
        preg_match_all('/\b\d[\d\-\/\s]{5,}\d\b/', (string) $value, $matches);

        return collect($matches[0] ?? [])
            ->map(fn($number) => preg_replace('/\D+/', '', $number))
            ->filter(fn($number) => strlen($number) >= 6)
            ->unique()
            ->values()
            ->all();
    }

    private function getReferenceMap(string $modelClass, string $nameColumn, string $idColumn): array
    {
        $key = $modelClass . ':' . $nameColumn . ':' . $idColumn;
        if (!array_key_exists($key, $this->referenceCache)) {
            $this->referenceCache[$key] = $modelClass::pluck($idColumn, $nameColumn)->toArray();
        }

        return $this->referenceCache[$key];
    }

    private function findReferenceId(string $modelClass, string $nameColumn, string $idColumn, ?string $search)
    {
        $searchText = $this->normalizeReferenceText($search);
        if ($searchText === '') {
            return null;
        }

        $map = $this->getReferenceMap($modelClass, $nameColumn, $idColumn);
        $searchNumbers = $this->extractReferenceNumbers($search);

        if (!empty($searchNumbers)) {
            foreach ($map as $name => $id) {
                if (array_intersect($searchNumbers, $this->extractReferenceNumbers($name))) {
                    return $id;
                }
            }
        }

        foreach ($map as $name => $id) {
            $nameText = $this->normalizeReferenceText($name);
            if ($nameText === $searchText || str_contains($nameText, $searchText) || str_contains($searchText, $nameText)) {
                return $id;
            }
        }

        return null;
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

             'id_sumber_dana' => $this->findReferenceId(SumberDana::class, 'nama_sumber_dana', 'id_sumber_dana', $sumberDana),

            'id_bank_tujuan' => $this->findReferenceId(BankTujuan::class, 'nama_tujuan', 'id_bank_tujuan', $bankTujuan),

            'id_kategori_kriteria' => $this->findReferenceId(KategoriKriteria::class, 'nama_kriteria', 'id_kategori_kriteria', $kategori),

            'id_jenis_pembayaran' => $this->findReferenceId(JenisPembayaran::class, 'nama_jenis_pembayaran', 'id_jenis_pembayaran', $jenisPembayaran),

            'penerima' => $row['penerima'] ?? null,
            'uraian'   => $uraian,

            'debet'       => $debet,
            'nilai_rupiah' => $debet,
            'kredit'      => 0,

            'jenis_pembayaran' => $row['jenis_pembayaran'] ?? null,
        ]);
    }
}
