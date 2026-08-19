<?php

namespace App\Services;

use App\Models\BankTujuan;
use App\Models\Cashflow;
use App\Models\CashflowReference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use XMLReader;
use ZipArchive;

class CashflowImportService
{
    /**
     * Profit Center code to BankTujuan mapping.
     */
    public static function getProfitCenterMap(): array
    {
        return [
            '5R00000001' => 'PPPBB',
            '5D01000001' => 'UGKB',
            '5E01000001' => 'GUNME',
            '5F01000001' => 'PAGUN',
            '5E02000001' => 'GUMAS',
            '5E04000001' => 'RIMBA',
            '5F04000001' => 'PARBA',
            '5E06000001' => 'SINTANG',
            '5E07000001' => 'NGABANG',
            '5F07000001' => 'PANGA',
            '5E08000001' => 'PARINDU',
            '5F08000001' => 'PAPAR',
            '5E09000001' => 'BAYAN',
            '5F09000001' => 'PAKEM',
            '5D03000001' => 'UGKST',
            '5E11000001' => 'DASAL',
            '5F20000001' => 'TAMBA',
            '5E14000001' => 'PAMUKAN',
            '5F14000001' => 'PAPAM',
            '5E13000001' => 'BALIN',
            '5E15000001' => 'PELAIHARI',
            '5F15000001' => 'PALAI',
            '5E12000001' => 'KUMAI',
            '5F11000001' => 'PRYBB',
            '5D02000001' => 'UGKT',
            '5E16000001' => 'TABARA',
            '5E17000001' => 'TAJATI',
            '5E18000001' => 'PANDAWA',
            '5F22000001' => 'PALPI',
            '5F21000001' => 'PASAM',
            '5E19000001' => 'LONGKALI',
            '5E03000001' => 'DEKAN',
            '5E20000001' => 'RAREN',
            '1D01000001' => 'UGKB',
        ];
    }

    /**
     * Helper to parse sharedStrings from XLSX zip archive.
     */
    private function getSharedStrings(ZipArchive $zip): array
    {
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $xml = new XMLReader();
            $xml->xml($ssXml);
            while ($xml->read()) {
                if ($xml->nodeType === XMLReader::ELEMENT && $xml->name === 'si') {
                    $siXml = $xml->readOuterXml();
                    $siEl = simplexml_load_string($siXml);
                    if (isset($siEl->t)) {
                        $sharedStrings[] = (string) $siEl->t;
                    } else {
                        $text = '';
                        if (isset($siEl->r)) {
                            foreach ($siEl->r as $r) {
                                $text .= (string) $r->t;
                            }
                        }
                        $sharedStrings[] = $text;
                    }
                }
            }
            $xml->close();
        }
        return $sharedStrings;
    }

    /**
     * Import Standarisasi Reference Keys dari Excel.
     */
    public function importStandarisasiReffkey(string $filePath): int
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File Standarisasi Reffkey tidak ditemukan: {$filePath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception("Gagal membuka file zip: {$filePath}");
        }

        $sharedStrings = $this->getSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new \Exception("Lembar kerja sheet1.xml tidak ditemukan pada file {$filePath}");
        }

        $xml = new XMLReader();
        $xml->xml($sheetXml);

        $records = [];
        $now = now();

        while ($xml->read()) {
            if ($xml->nodeType === XMLReader::ELEMENT && $xml->name === 'row') {
                $rNum = (int) $xml->getAttribute('r');
                if ($rNum < 9) {
                    continue; // Header ada di baris 1-8
                }

                $rowXml = $xml->readOuterXml();
                $rowEl = simplexml_load_string($rowXml);

                $cols = [];
                foreach ($rowEl->c as $c) {
                    $ref = (string) $c['r'];
                    $colLetter = preg_replace('/[0-9]/', '', $ref);
                    $t = (string) $c['t'];
                    $val = (string) $c->v;
                    if ($t === 's' && isset($sharedStrings[(int) $val])) {
                        $val = $sharedStrings[(int) $val];
                    }
                    $cols[$colLetter] = $val;
                }

                $k3 = trim($cols['A'] ?? '');
                $k3Name = trim($cols['B'] ?? '');
                $k1 = trim($cols['C'] ?? '');
                $desc = trim($cols['D'] ?? '');
                $nature = trim($cols['E'] ?? '');

                if (empty($k1) || str_starts_with($k1, 'ARUS KAS') || $k1 === 'Penerimaan' || $k1 === 'Pengeluaran') {
                    continue;
                }

                if (empty($desc)) {
                    $desc = !empty($k3Name) && $k3Name !== '#N/A' ? $k3Name : $k1;
                }

                $records[$k1] = [
                    'reference_key' => $k1,
                    'parent_key' => $k3 ?: null,
                    'parent_name' => $k3Name && $k3Name !== '#N/A' ? $k3Name : null,
                    'uraian' => $desc,
                    'nature' => $nature ?: null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $xml->close();
        $zip->close();

        // Tambahkan default mapping untuk kode-kode induk (...000)
        $defaults000 = [
            'A0101000' => ['parent_key' => 'A0101', 'parent_name' => 'Penerimaan kas dari pelanggan', 'uraian' => 'Penerimaan kas dari pelanggan'],
            'A0102000' => ['parent_key' => 'A0102', 'parent_name' => 'Penerimaan kas lainnya', 'uraian' => 'Penerimaan kas lainnya'],
            'A0104000' => ['parent_key' => 'A0104', 'parent_name' => 'Penerimaan bunga', 'uraian' => 'Penerimaan bunga'],
            'A0105000' => ['parent_key' => 'A0105', 'parent_name' => 'Penerimaan dana dari Bank untuk petani rakyat', 'uraian' => 'Penerimaan dana dari Bank untuk petani rakyat'],
            'A0106000' => ['parent_key' => 'A0106', 'parent_name' => 'Pengembalian dari petani rakyat', 'uraian' => 'Pengembalian dari petani rakyat'],
            'A0201000' => ['parent_key' => 'A0201', 'parent_name' => 'Pembayaran kas kepada pemasok', 'uraian' => 'Pembayaran kas kepada pemasok'],
            'A0202000' => ['parent_key' => 'A0202', 'parent_name' => 'Pembayaran kas kepada karyawan', 'uraian' => 'Pembayaran kas kepada karyawan'],
            'A0209000' => ['parent_key' => 'A0209', 'parent_name' => 'Pembayaran kas lainnya', 'uraian' => 'Pembayaran kas lainnya'],
            'B0103024' => ['parent_key' => 'B0103', 'parent_name' => 'Penerimaan dari penjualan aset tetap', 'uraian' => 'Penerimaan dari penjualan aset tetap'],
            'B0203000' => ['parent_key' => 'B0203', 'parent_name' => 'Penambahan aset tetap', 'uraian' => 'Penambahan aset tetap'],
            'B0205000' => ['parent_key' => 'B0205', 'parent_name' => 'Penambahan Pembibitan', 'uraian' => 'Penambahan Pembibitan'],
            'E0101000' => ['parent_key' => 'E0101', 'parent_name' => 'Bank Clearing', 'uraian' => 'Bank Clearing'],
        ];

        foreach ($defaults000 as $k => $d) {
            if (!isset($records[$k])) {
                $records[$k] = [
                    'reference_key' => $k,
                    'parent_key' => $d['parent_key'],
                    'parent_name' => $d['parent_name'],
                    'uraian' => $d['uraian'],
                    'nature' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk(array_values($records), 200) as $chunk) {
            CashflowReference::upsert($chunk, ['reference_key'], ['parent_key', 'parent_name', 'uraian', 'nature', 'updated_at']);
        }

        return count($records);
    }

    /**
     * Import Data Transaksi Cashflow dari Excel SAP (Cashflow.xlsx).
     */
    public function importCashflowTransactions(string $filePath): int
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File Cashflow.xlsx tidak ditemukan: {$filePath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception("Gagal membuka file zip: {$filePath}");
        }

        $sharedStrings = $this->getSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            $zip->close();
            throw new \Exception("Lembar kerja sheet1.xml tidak ditemukan pada file {$filePath}");
        }

        // 1. Load BankTujuan lookup
        $btMap = [];
        $bankTujuans = BankTujuan::all();
        $prctrKeywordMap = self::getProfitCenterMap();

        foreach ($bankTujuans as $bt) {
            $name = strtoupper($bt->nama_tujuan);
            foreach ($prctrKeywordMap as $code => $alias) {
                if (str_contains($name, $alias)) {
                    $btMap[$code] = $bt->id_bank_tujuan;
                }
            }
        }

        $defaultBtId = $bankTujuans->first()->id_bank_tujuan ?? 1;

        // 2. Load References dictionary
        $refMap = CashflowReference::pluck('uraian', 'reference_key')->toArray();

        // 3. Fast streaming parse sheet1.xml
        $xml = new XMLReader();
        $xml->xml($sheetXml);

        $rowsToInsert = [];
        $totalInserted = 0;
        $now = now();

        while ($xml->read()) {
            if ($xml->nodeType === XMLReader::ELEMENT && $xml->name === 'row') {
                $rNum = (int) $xml->getAttribute('r');
                if ($rNum <= 1) {
                    continue; // Skip header
                }

                $rowXml = $xml->readOuterXml();
                $rowEl = simplexml_load_string($rowXml);

                $cols = [];
                foreach ($rowEl->c as $c) {
                    $ref = (string) $c['r'];
                    $colLetter = preg_replace('/[0-9]/', '', $ref);
                    $t = (string) $c['t'];
                    $val = (string) $c->v;
                    if ($t === 's' && isset($sharedStrings[(int) $val])) {
                        $val = $sharedStrings[(int) $val];
                    }
                    $cols[$colLetter] = $val;
                }

                $docNum = trim($cols['A'] ?? '');
                if (empty($docNum)) {
                    continue;
                }

                $postingDateRaw = $cols['B'] ?? null;
                $postingDate = null;
                $bulan = null;
                $tahun = null;

                if (is_numeric($postingDateRaw)) {
                    $valNum = (float) $postingDateRaw;
                    if ($valNum > 25569) {
                        $unix = ($valNum - 25569) * 86400;
                        $postingDate = gmdate('Y-m-d', (int) $unix);
                        $bulan = (int) gmdate('m', (int) $unix);
                        $tahun = (int) gmdate('Y', (int) $unix);
                    }
                } elseif (!empty($postingDateRaw)) {
                    try {
                        $dt = new \DateTime($postingDateRaw);
                        $postingDate = $dt->format('Y-m-d');
                        $bulan = (int) $dt->format('m');
                        $tahun = (int) $dt->format('Y');
                    } catch (\Exception $e) {}
                }

                // Fallback tahun/bulan dari Kolom V (Year/Month e.g. "2026/01")
                $yearMonthRaw = trim($cols['V'] ?? '');
                if (empty($tahun) && !empty($yearMonthRaw) && str_contains($yearMonthRaw, '/')) {
                    $parts = explode('/', $yearMonthRaw);
                    $tahun = (int) $parts[0];
                    $bulan = (int) $parts[1];
                }

                $period = (int) ($cols['C'] ?? 0) ?: ($bulan ?: 1);
                $account = trim($cols['D'] ?? '');
                $prctr = trim($cols['I'] ?? '');
                $prctrName = trim($cols['J'] ?? '');
                $offsettingAcc = trim($cols['K'] ?? '');
                $offsettingAccName = trim($cols['L'] ?? '');
                $postingKey = trim($cols['N'] ?? '');
                $amountRaw = (float) ($cols['O'] ?? 0);
                $text = trim($cols['Q'] ?? '');
                $glDesc = trim($cols['S'] ?? '');
                $refKeyRaw = trim($cols['W'] ?? '');
                $costCenter = trim($cols['AC'] ?? '');
                $refKey3 = trim($cols['AE'] ?? '');
                $refKey1 = trim($cols['AL'] ?? '');

                if (empty($refKey1)) {
                    $refKey1 = !empty($refKey3) ? $refKey3 . '000' : 'A0209029';
                }

                $uraian = $refMap[$refKey1] ?? ($refMap[$refKey3] ?? ($offsettingAccName ?: $text));
                $idBankTujuan = $btMap[$prctr] ?? $defaultBtId;

                $rowsToInsert[] = [
                    'id_bank_tujuan' => $idBankTujuan,
                    'profit_center' => $prctr ?: null,
                    'nama_profit_center' => $prctrName ?: null,
                    'document_number' => $docNum,
                    'posting_date' => $postingDate,
                    'posting_period' => $period,
                    'bulan' => $bulan ?: 1,
                    'tahun' => $tahun ?: (int) date('Y'),
                    'account' => $account ?: null,
                    'offsetting_account' => $offsettingAcc ?: null,
                    'name_of_offsetting_account' => $offsettingAccName ?: null,
                    'posting_key' => $postingKey ?: null,
                    'amount' => $amountRaw,
                    'text' => $text ?: null,
                    'gl_account_desc' => $glDesc ?: null,
                    'reference_key' => $refKeyRaw ?: null,
                    'reference_key_1' => $refKey1,
                    'reference_key_3' => $refKey3 ?: null,
                    'cost_center' => $costCenter ?: null,
                    'uraian' => $uraian,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($rowsToInsert) >= 1000) {
                    Cashflow::insert($rowsToInsert);
                    $totalInserted += count($rowsToInsert);
                    $rowsToInsert = [];
                }
            }
        }

        if (!empty($rowsToInsert)) {
            Cashflow::insert($rowsToInsert);
            $totalInserted += count($rowsToInsert);
        }

        $xml->close();
        $zip->close();

        return $totalInserted;
    }
}
