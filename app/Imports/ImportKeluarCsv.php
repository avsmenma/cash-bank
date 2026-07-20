<?php

namespace App\Imports;

use App\Models\BankKeluar;
use App\Models\BankTujuan;
use App\Models\SumberDana;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use App\Models\JenisPembayaran;
use App\Models\KategoriKriteria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportKeluarCsv
{
    // Cache untuk lookup - di-load 1x saja di awal
    private $sumberDanaCache = [];
    private $bankTujuanCache = [];
    private $kategoriCache = [];
    private $subKriteriaCache = [];
    private $itemSubKriteriaCache = [];
    private $jenisPembayaranCache = [];

    public function __construct()
    {
        $this->loadCaches();
    }

    /**
     * Load semua lookup tables ke memory (1x query per table)
     * Jauh lebih efisien daripada query per row
     */
    private function loadCaches()
    {
        Log::info('Loading lookup caches...');

        // Sumber Dana
        $sumberDanas = SumberDana::all();
        foreach ($sumberDanas as $sd) {
            $this->addReferenceCache($this->sumberDanaCache, $sd->nama_sumber_dana, $sd->id_sumber_dana);
        }

        // Bank Tujuan
        $bankTujuans = BankTujuan::all();
        foreach ($bankTujuans as $bt) {
            $this->addReferenceCache($this->bankTujuanCache, $bt->nama_tujuan, $bt->id_bank_tujuan);
        }

        // Kategori Kriteria
        $kategoris = KategoriKriteria::all();
        foreach ($kategoris as $k) {
            $this->addReferenceCache($this->kategoriCache, $k->nama_kriteria, $k->id_kategori_kriteria);
        }

        // Sub Kriteria
        $subs = SubKriteria::all();
        foreach ($subs as $s) {
            $this->addReferenceCache($this->subKriteriaCache, $s->nama_sub_kriteria, $s->id_sub_kriteria);
        }

        // Item Sub Kriteria
        $items = ItemSubKriteria::all();
        foreach ($items as $i) {
            $this->addReferenceCache($this->itemSubKriteriaCache, $i->nama_item_sub_kriteria, $i->id_item_sub_kriteria);
        }

        // Jenis Pembayaran
        $jenis = JenisPembayaran::all();
        foreach ($jenis as $j) {
            $this->addReferenceCache($this->jenisPembayaranCache, $j->nama_jenis_pembayaran, $j->id_jenis_pembayaran);
        }

        Log::info('Caches loaded: ' . count($this->sumberDanaCache) . ' sumber dana, ' .
            count($this->bankTujuanCache) . ' bank tujuan, ' .
            count($this->kategoriCache) . ' kategori');
    }

    private function addReferenceCache(array &$cache, ?string $name, $id): void
    {
        $cache[] = [
            'id' => $id,
            'name' => (string) $name,
            'normalized' => $this->normalizeReferenceText($name),
            'numbers' => $this->extractReferenceNumbers($name),
        ];
    }

    private function normalizeReferenceText(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = str_replace('&', ' dan ', $value);
        $value = preg_replace('/\bpt\b\.?/i', ' ', $value);
        $value = preg_replace('/\bcab\b\.?/i', ' cabang ', $value);
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value ?? '');
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

    /**
     * Cari ID dari cache dengan exact, nomor rekening, partial text, dan token overlap.
     */
    private function findInCache($cache, $search)
    {
        if (empty($search))
            return null;

        $searchText = $this->normalizeReferenceText($search);
        $searchNumbers = $this->extractReferenceNumbers($search);

        foreach ($cache as $entry) {
            if ($entry['normalized'] === $searchText) {
                return $entry['id'];
            }
        }

        if (!empty($searchNumbers)) {
            foreach ($cache as $entry) {
                if (array_intersect($searchNumbers, $entry['numbers'])) {
                    return $entry['id'];
                }
            }
        }

        foreach ($cache as $entry) {
            $nameText = $entry['normalized'];
            if ($nameText === '' || $searchText === '') {
                continue;
            }

            if (str_contains($nameText, $searchText) || str_contains($searchText, $nameText)) {
                return $entry['id'];
            }

            $searchTokens = array_filter(explode(' ', $searchText), fn($token) => strlen($token) >= 3);
            $nameTokens = array_filter(explode(' ', $nameText), fn($token) => strlen($token) >= 3);
            if (!empty($searchTokens) && count(array_intersect($searchTokens, $nameTokens)) >= min(2, count($searchTokens))) {
                return $entry['id'];
            }
        }

        return null;
    }

    private function inferKeluarReferenceIds(string $kategori, string $subKriteria, string $itemSubKriteria): array
    {
        $text = $this->normalizeReferenceText($kategori . ' ' . $subKriteria . ' ' . $itemSubKriteria);

        if ($text !== '' && (str_contains($text, 'pembelian tbs') || preg_match('/\btbs\b/', $text))) {
            return [
                'kategori' => $this->findInCache($this->kategoriCache, 'Payment Requirement for Exploitation Activity'),
                'sub' => $this->findInCache($this->subKriteriaCache, 'Purchase Volume'),
                'item' => $this->findInCache($this->itemSubKriteriaCache, 'TBS FFB'),
            ];
        }

        return [
            'kategori' => null,
            'sub' => null,
            'item' => null,
        ];
    }

    /**
     * Import dari file CSV
     */
    public function import($filePath)
    {
        Log::info('Starting CSV import from: ' . $filePath);

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Cannot open file: ' . $filePath);
        }

        // Baca header
        $firstLine = fgets($handle);
        $delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle);

        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            fclose($handle);
            throw new \Exception('Empty CSV file');
        }

        // Normalize header (lowercase, trim, replace spaces)
        $header = array_map(function ($h) {
            return str_replace(' ', '_', strtolower(trim($h)));
        }, $header);

        Log::info('CSV Headers: ' . implode(', ', $header));

        $rowCount = 0;
        $successCount = 0;
        $errorCount = 0;
        $skipCount  = 0;
        $batchData = [];
        $barisAgenda = [];
        $batchSize = 100;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowCount++;

                // Skip baris benar-benar kosong
                if (empty(array_filter($row))) continue;

                // Sesuaikan jumlah kolom dengan header
                $headerCount = count($header);
                $rowCount2 = count($row);

                // Google Sheets kadang membungkus seluruh baris dalam 1 pasang double-quote
                // ketika ada field yang mengandung koma. fgetcsv() akan menganggap
                // seluruh isi baris sebagai 1 field tunggal.
                // Deteksi dan re-parse jika kolom jauh lebih sedikit dari header.
                if ($rowCount2 < $headerCount) {
                    $reparsed = str_getcsv(implode($delimiter, $row), $delimiter);
                    if (count($reparsed) > $rowCount2) {
                        $row = $reparsed;
                        $rowCount2 = count($row);
                    }
                }

                // Jika kolom masih kurang dari header, padding dengan string kosong
                if ($rowCount2 < $headerCount) {
                    $row = array_pad($row, $headerCount, '');
                }
                if ($rowCount2 > $headerCount) {
                    $row = array_slice($row, 0, $headerCount);
                }

                try {
                    $data = array_combine($header, $row);
                } catch (\Throwable $e) {
                    Log::warning("Row {$rowCount} combine failed: " . $e->getMessage());
                    $errorCount++;
                    continue;
                }

                // Skip baris tanpa tanggal
                $tanggalVal = trim($data['tanggal'] ?? '');
                if (empty($tanggalVal)) continue;

                // Process row → build record
                try {
                    $record = $this->processRow($data);
                } catch (\Throwable $e) {
                    Log::warning("Row {$rowCount} processRow failed: " . $e->getMessage());
                    $errorCount++;
                    continue;
                }

                if ($record) {
                    $batchData[] = $record;
                    $successCount++;

                    // Kumpulkan pasangan agenda+tanggal saja (ringan); sinkron
                    // ke Agenda Online dijalankan sekali setelah commit.
                    if (!empty($record['agenda_tahun']) && !empty($record['tanggal'])) {
                        $barisAgenda[] = [
                            'agenda_tahun' => $record['agenda_tahun'],
                            'tanggal' => $record['tanggal'],
                        ];
                    }

                    if (count($batchData) >= $batchSize) {
                        BankKeluar::insert($batchData);
                        $batchData = [];
                        Log::info("Inserted batch at row {$rowCount}");
                    }
                } else {
                    $errorCount++;
                }

                if ($rowCount % 500 === 0) gc_collect_cycles();
            }

            if (!empty($batchData)) {
                BankKeluar::insert($batchData);
                Log::info("Inserted final batch");
            }

            DB::commit();
            fclose($handle);

            // Tanggal transaksi ikut mengisi tanggal_dibayar dokumen Agenda
            // Online. Dijalankan massal SETELAH commit — inilah pengganti blok
            // per-baris lama yang dimatikan karena bikin impor timeout.
            (new \App\Services\AgendaTanggalBayarSync())->sinkronkan($barisAgenda);

            Log::info("CSV Import: {$successCount} success, {$errorCount} errors out of {$rowCount} rows");

            return [
                'total'   => $rowCount,
                'success' => $successCount,
                'errors'  => $errorCount,
            ];


        } catch (\Exception $e) {
            DB::rollback();
            fclose($handle);
            Log::error('CSV Import failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process single row
     */
    private function processRow($data)
    {
        // Catatan: pada file ekspor Google Sheets "Bank Keluar", kolom nomor agenda
        // (mis. 0004_2026) berada di kolom PERTAMA yang TANPA judul, sehingga setelah
        // normalisasi header key-nya menjadi string kosong ''. Sertakan sebagai fallback.
        $agendaTahun = $this->cleanText(
            $data['agenda_tahun']
            ?? $data['no_agenda']
            ?? $data['nomor_agenda']
            ?? $data['agenda']
            ?? $data['']
            ?? ''
        );
        $tanggal = $this->parseTanggal($data['tanggal'] ?? '');
        // No Bukti manual dari pembukuan (header CSV "No. Bukti" → key no._bukti)
        $noBukti = $this->cleanText($data['no._bukti'] ?? $data['no_bukti'] ?? $data['bukti'] ?? '');
        $sumberDana = $this->cleanText($data['sumber_dana'] ?? '');
        $bankTujuan = $this->cleanText($data['bank_tujuan'] ?? '');
        // Kategori: support 'kategori', 'kriteria_cf', atau 'kriteria'; strip prefix angka "50. "
        $kategoriRaw = $this->cleanText($data['kategori'] ?? $data['kriteria_cf'] ?? $data['kriteria'] ?? '');
        $kategori = preg_replace('/^\d+\.\s*/', '', $kategoriRaw);
        $penerima = $this->cleanText($data['penerima'] ?? $data['penerima/dari'] ?? '');
        $uraian = $this->cleanText($data['uraian'] ?? $data['edit_uraian'] ?? '');
        $jenisPembayaran = $this->cleanText($data['jenis_pembayaran'] ?? '');

        // Parse nilai kredit: CSV bank keluar punya kolom 'kredit', fallback ke 'debet'
        $kredit = $this->parseNilai($data['kredit'] ?? $data['debet'] ?? 0);

        // Sengaja TIDAK menembak database kedua per baris — dulu itulah yang
        // membuat impor 3000+ baris timeout. Nomor agenda + tanggalnya dikumpulkan
        // oleh pemanggil, lalu disinkronkan sekali secara massal setelah commit
        // lewat App\Services\AgendaTanggalBayarSync.
        $dokumenId = null;

        // =====================================
        // BUILD RECORD (using cache - no query!)
        // =====================================
        $inferred = $this->inferKeluarReferenceIds(
            $kategori,
            $data['sub_kriteria'] ?? '',
            $data['item_sub_kriteria'] ?? ''
        );

        return [
            'agenda_tahun' => $agendaTahun ?: null,
            'no_bukti' => $noBukti ?: null,
            'dokumen_id' => $dokumenId,
            'tanggal' => $tanggal,
            'id_sumber_dana' => $this->findInCache($this->sumberDanaCache, $sumberDana),
            'id_bank_tujuan' => $this->findInCache($this->bankTujuanCache, $bankTujuan),
            'id_kategori_kriteria' => $this->findInCache($this->kategoriCache, $kategori) ?? $inferred['kategori'],
            'id_sub_kriteria' => $this->findInCache($this->subKriteriaCache, $data['sub_kriteria'] ?? '') ?? $inferred['sub'],
            'id_item_sub_kriteria' => $this->findInCache($this->itemSubKriteriaCache, $data['item_sub_kriteria'] ?? '') ?? $inferred['item'],
            'id_jenis_pembayaran' => $this->findInCache($this->jenisPembayaranCache, $jenisPembayaran),
            'penerima' => $penerima ?: null,
            'uraian' => $uraian ?: null,
            'kredit' => $kredit,
            'debet' => 0,
            'nilai_rupiah' => $kredit,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];
    }

    private function cleanText($text)
    {
        if ($text === null || $text === '')
            return '';
        $text = (string) $text;
        return trim(preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $text)));
    }

    private function parseTanggal($val)
    {
        if (empty($val))
            return null;

        // Format d/m/Y
        $val = str_replace(['-', '.'], '/', trim($val));

        if (preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $val)) {
            try {
                return \Carbon\Carbon::createFromFormat('d/m/Y', $val)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Format Y-m-d
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $val)) {
            return $val;
        }

        return null;
    }

    private function parseNilai($val)
    {
        if (empty($val)) return 0;

        // Bersihkan spasi dan karakter non-numerik kecuali . dan ,
        $cleaned = trim((string) $val);
        $cleaned = preg_replace('/[^\d.,]/', '', $cleaned);

        if (empty($cleaned)) return 0;

        // Format Indonesia: titik = pemisah ribuan, koma = desimal
        // Contoh: "3.500" = 3500, "3.035.315" = 3035315
        // JANGAN pakai is_numeric karena PHP anggap "3.500" = float 3.5 → (int)3 = SALAH
        $cleaned = str_replace('.', '', $cleaned);  // hapus pemisah ribuan (titik)
        $cleaned = str_replace(',', '.', $cleaned); // ubah koma desimal ke titik

        return (float) $cleaned;
    }
}
