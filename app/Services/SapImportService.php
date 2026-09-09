<?php

namespace App\Services;

use App\Models\BankTujuan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SapImportService
{
    /**
     * Profit Center code to Bank Virtual Account alias mapping.
     */
    public static function getProfitCenterMap(): array
    {
        return [
            '5D01000001' => 'KPB',
            '5D01000002' => 'UGKB',
            '5D02000001' => 'UGKST',
            '5D03000001' => 'UGKT',
            '5E01000001' => 'GUNME',
            '5E02000001' => 'GUMAS',
            '5E03000001' => 'DEKAN',
            '5E04000001' => 'RIMBA',
            '5E05000001' => 'PPPBB',
            '5E06000001' => 'SINTANG',
            '5E07000001' => 'NGABANG',
            '5E08000001' => 'PARINDU',
            '5E09000001' => 'BAYAN',
            '5E10000001' => 'RAREN',
            '5E11000001' => 'DASAL',
            '5E12000001' => 'KUMAI',
            '5E13000001' => 'BALIN',
            '5E14000001' => 'PAMUKAN',
            '5E15000001' => 'PELAIHARI',
            '5E16000001' => 'TABARA',
            '5E17000001' => 'TAJATI',
            '5E18000001' => 'PANDAWA',
            '5E19000001' => 'LONGKALI',
            '5F01000001' => 'PAGUN',
            '5F04000001' => 'PARBA',
            '5F07000001' => 'PANGA',
            '5F08000001' => 'PAPAR',
            '5F09000001' => 'PAKEM',
            '5F11000001' => 'PRYBB',
            '5F14000001' => 'PAPAM',
            '5F15000001' => 'PALAI',
            '5F20000001' => 'TAMBA',
            '5F21000001' => 'PASAM',
            '5F22000001' => 'PALPI',
        ];
    }

    /**
     * Import and calculate SUMIF balances from uploaded file (CSV / XLSX / XLS).
     *
     * @param UploadedFile|string $file
     * @return array
     */
    public function import($file): array
    {
        $filePath = is_string($file) ? $file : $file->getRealPath();
        $extension = strtolower(is_string($file) ? pathinfo($file, PATHINFO_EXTENSION) : $file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'])) {
            $rows = $this->parseCsv($filePath);
        } else {
            $rows = $this->parseExcel($filePath);
        }

        if (empty($rows)) {
            throw new \RuntimeException('File tidak memiliki baris data atau kosong.');
        }

        // Cari index kolom Profit Center dan Amount
        $colMap = $this->detectColumns($rows);
        $prctrIdx = $colMap['prctr'];
        $amtIdx = $colMap['amount'];

        if ($prctrIdx === null || $amtIdx === null) {
            throw new \RuntimeException('Kolom Profit Center atau Amount tidak ditemukan dalam file.');
        }

        // Lakukan perhitungan SUMIF per Profit Center
        $totals = [];
        $dataRowCount = 0;
        $startIndex = $colMap['header_row_index'] + 1;

        for ($i = $startIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (!isset($row[$prctrIdx]) || !isset($row[$amtIdx])) {
                continue;
            }

            $prctr = strtoupper(trim((string) $row[$prctrIdx]));
            if ($prctr === '' || strcasecmp($prctr, 'profit center') === 0 || strcasecmp($prctr, 'prctr') === 0) {
                continue;
            }

            $amount = $this->parseAmount($row[$amtIdx]);
            $totals[$prctr] = ($totals[$prctr] ?? 0.0) + $amount;
            $dataRowCount++;
        }

        if (empty($totals)) {
            throw new \RuntimeException('Tidak ada data transaksi atau Profit Center valid yang terbaca dari file.');
        }

        // Sinkronisasi nilai Saldo SAP ke tabel bank_tujuan
        $profitCenterMap = self::getProfitCenterMap();
        $bankTujuans = BankTujuan::all();

        $updatedCount = 0;
        $updatedDetails = [];
        $unmatchedProfitCenters = [];

        DB::beginTransaction();
        try {
            foreach ($bankTujuans as $bt) {
                $alias = $this->extractAlias($bt->nama_tujuan);
                $matchedPrctr = null;

                // 1. Cocokkan dengan katalog mapping
                foreach ($profitCenterMap as $pCode => $pAlias) {
                    if (strcasecmp($pAlias, $alias) === 0) {
                        $matchedPrctr = $pCode;
                        break;
                    }
                }

                // 2. Fallback: jika nama_tujuan mengandung alias katalog
                if (!$matchedPrctr) {
                    foreach ($profitCenterMap as $pCode => $pAlias) {
                        if (stripos($bt->nama_tujuan, $pAlias) !== false) {
                            $matchedPrctr = $pCode;
                            break;
                        }
                    }
                }

                if ($matchedPrctr) {
                    // SUMIF: jika profit center ada di data, ambil nilainya; jika tidak ada, 0
                    $sum = $totals[$matchedPrctr] ?? 0.0;
                    $formattedSap = number_format($sum, 0, ',', '.');

                    $bt->sap = $formattedSap;
                    $bt->save();

                    $updatedCount++;
                    $updatedDetails[] = [
                        'id_bank_tujuan' => $bt->id_bank_tujuan,
                        'nama_tujuan' => $bt->nama_tujuan,
                        'profit_center' => $matchedPrctr,
                        'sap_formatted' => $formattedSap,
                        'sap_nilai' => $sum,
                    ];
                }
            }

            // Catat Profit Center di file yang tidak terpetakan ke bank_tujuan (mis. Kantor Direksi/Regional)
            $allMappedPrctrs = array_keys($profitCenterMap);
            foreach ($totals as $prctrCode => $amount) {
                if (!in_array($prctrCode, $allMappedPrctrs, true)) {
                    $unmatchedProfitCenters[] = [
                        'profit_center' => $prctrCode,
                        'total' => $amount,
                        'formatted' => number_format($amount, 0, ',', '.'),
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error saat menyimpan Saldo SAP import: ' . $e->getMessage());
            throw $e;
        }

        return [
            'success' => true,
            'message' => "Berhasil mengimpor Saldo SAP untuk {$updatedCount} Bank Virtual Account.",
            'total_rows_processed' => $dataRowCount,
            'distinct_profit_centers' => count($totals),
            'updated_count' => $updatedCount,
            'details' => $updatedDetails,
            'unmatched' => $unmatchedProfitCenters,
        ];
    }

    /**
     * Ekstrak alias unit dari nama_tujuan (e.g. "81029155528 - GUNME" -> "GUNME").
     */
    public function extractAlias(string $namaTujuan): string
    {
        $parts = explode('-', $namaTujuan);
        return trim((string) end($parts));
    }

    /**
     * Parse nominal numerik dari format SAP / Indonesia ke float.
     * Mendukung: 48.400.000, -620.000, (121.000.000), 12.304.974,00, dsb.
     */
    public function parseAmount($val): float
    {
        if (is_numeric($val) && !is_string($val)) {
            return (float) $val;
        }

        $val = trim((string) $val);
        if ($val === '' || $val === '-') {
            return 0.0;
        }

        $negative = false;
        if (str_starts_with($val, '(') && str_ends_with($val, ')')) {
            $negative = true;
            $val = trim($val, '()');
        } elseif (str_starts_with($val, '-')) {
            $negative = true;
            $val = ltrim($val, '-');
        }

        // Penanganan ribuan dan desimal:
        // Format SAP / Indonesia menggunakan titik '.' sebagai pemisah ribuan.
        if (strpos($val, '.') !== false && strpos($val, ',') !== false) {
            if (strrpos($val, ',') > strrpos($val, '.')) {
                // Contoh: 1.234.567,89
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            } else {
                // US format: 1,234,567.89
                $val = str_replace(',', '', $val);
            }
        } elseif (strpos($val, '.') !== false) {
            // Hanya titik: dalam ekspor SAP Indonesia, titik adalah ribuan
            $val = str_replace('.', '', $val);
        } elseif (strpos($val, ',') !== false) {
            $parts = explode(',', $val);
            if (count($parts) === 2 && strlen($parts[1]) !== 3) {
                $val = str_replace(',', '.', $val);
            } else {
                $val = str_replace(',', '', $val);
            }
        }

        $clean = (float) preg_replace('/[^0-9.-]/', '', $val);
        return $negative ? -$clean : $clean;
    }

    /**
     * Deteksi kolom Profit Center dan Amount dari baris data.
     */
    private function detectColumns(array $rows): array
    {
        $prctrIdx = null;
        $amtIdx = null;
        $headerRowIdx = 0;

        // Periksa 5 baris pertama untuk mencari baris header
        $maxScan = min(5, count($rows));
        for ($r = 0; $r < $maxScan; $r++) {
            $row = $rows[$r];
            foreach ($row as $i => $col) {
                $c = strtolower(trim((string) $col));
                if ($prctrIdx === null && (str_contains($c, 'profit center') || $c === 'prctr' || $c === 'profit_center')) {
                    $prctrIdx = $i;
                }
                if ($amtIdx === null && (str_contains($c, 'amount in local') || str_contains($c, 'amount') || $c === 'dmbtr' || $c === 'saldo sap')) {
                    $amtIdx = $i;
                }
            }

            if ($prctrIdx !== null && $amtIdx !== null) {
                $headerRowIdx = $r;
                break;
            }
        }

        // Fallback jika header tidak terdeteksi via string:
        // Berdasarkan template UP spreadsheet: Kolom D (index 3) & Kolom P (index 15)
        if ($prctrIdx === null) {
            $prctrIdx = 3;
        }
        if ($amtIdx === null) {
            $amtIdx = 15;
        }

        return [
            'prctr' => $prctrIdx,
            'amount' => $amtIdx,
            'header_row_index' => $headerRowIdx,
        ];
    }

    /**
     * Membaca CSV dengan deteksi otomatis delimiter (koma, titik koma, tab).
     */
    private function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Tidak dapat membuka file CSV.');
        }

        // Sniff delimiter
        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = ',';
        $semicolons = substr_count((string) $firstLine, ';');
        $commas = substr_count((string) $firstLine, ',');
        $tabs = substr_count((string) $firstLine, "\t");

        if ($semicolons > $commas && $semicolons > $tabs) {
            $delimiter = ';';
        } elseif ($tabs > $commas && $tabs > $semicolons) {
            $delimiter = "\t";
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Membaca XLSX / XLS, mengutamakan sheet "UP" bila ada.
     */
    private function parseExcel(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $sheet = null;
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (strcasecmp(trim($sheetName), 'UP') === 0 || stripos(trim($sheetName), 'UP') !== false) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                break;
            }
        }

        if (!$sheet) {
            $sheet = $spreadsheet->getActiveSheet();
        }

        return $sheet->toArray(null, true, false, false);
    }
}
