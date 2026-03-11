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
            $key = strtolower(trim($sd->nama_sumber_dana));
            $this->sumberDanaCache[$key] = $sd->id_sumber_dana;
        }

        // Bank Tujuan
        $bankTujuans = BankTujuan::all();
        foreach ($bankTujuans as $bt) {
            $key = strtolower(trim($bt->nama_tujuan));
            $this->bankTujuanCache[$key] = $bt->id_bank_tujuan;
        }

        // Kategori Kriteria
        $kategoris = KategoriKriteria::all();
        foreach ($kategoris as $k) {
            $key = strtolower(trim($k->nama_kriteria));
            $this->kategoriCache[$key] = $k->id_kategori_kriteria;
        }

        // Sub Kriteria
        $subs = SubKriteria::all();
        foreach ($subs as $s) {
            $key = strtolower(trim($s->nama_sub_kriteria));
            $this->subKriteriaCache[$key] = $s->id_sub_kriteria;
        }

        // Item Sub Kriteria
        $items = ItemSubKriteria::all();
        foreach ($items as $i) {
            $key = strtolower(trim($i->nama_item_sub_kriteria));
            $this->itemSubKriteriaCache[$key] = $i->id_item_sub_kriteria;
        }

        // Jenis Pembayaran
        $jenis = JenisPembayaran::all();
        foreach ($jenis as $j) {
            $key = strtolower(trim($j->nama_jenis_pembayaran));
            $this->jenisPembayaranCache[$key] = $j->id_jenis_pembayaran;
        }

        Log::info('Caches loaded: ' . count($this->sumberDanaCache) . ' sumber dana, ' .
            count($this->bankTujuanCache) . ' bank tujuan, ' .
            count($this->kategoriCache) . ' kategori');
    }

    /**
     * Cari ID dari cache dengan partial match
     */
    private function findInCache($cache, $search)
    {
        if (empty($search))
            return null;

        $searchLower = strtolower(trim($search));

        // Exact match first
        if (isset($cache[$searchLower])) {
            return $cache[$searchLower];
        }

        // Partial match (LIKE %search%)
        foreach ($cache as $key => $id) {
            if (strpos($key, $searchLower) !== false || strpos($searchLower, $key) !== false) {
                return $id;
            }
        }

        return null;
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
        $agendaTahun = $this->cleanText($data['agenda_tahun'] ?? $data['no_agenda'] ?? '');
        $tanggal = $this->parseTanggal($data['tanggal'] ?? '');
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

        // NOTE: Agenda Online update dinonaktifkan saat bulk import untuk mencegah timeout.
        // Setiap baris yang punya agenda_tahun mengandung '_2026' sebelumnya membuat query
        // ke database kedua (mysql_agenda_online) yang menyebabkan import 3000+ baris timeout.
        $dokumenId = null;

        /*
        if (!empty($agendaTahun) && strpos($agendaTahun, '_2026') !== false) {
            try {
                $dokumen = DB::connection('mysql_agenda_online')
                    ->table('dokumens')
                    ->where('nomor_agenda', $agendaTahun)
                    ->first();

                if ($dokumen) {
                    $dokumenId = $dokumen->id;

                    DB::connection('mysql_agenda_online')
                        ->table('dokumens')
                        ->where('id', $dokumen->id)
                        ->update([
                            'status_pembayaran' => 'sudah_dibayar',
                            'dibayar' => $kredit,
                            'tanggal_dibayar' => $tanggal,
                        ]);
                }
            } catch (\Exception $e) {
                Log::warning("Agenda Online update failed for {$agendaTahun}: " . $e->getMessage());
            }
        }
        */

        // =====================================
        // BUILD RECORD (using cache - no query!)
        // =====================================
        return [
            'agenda_tahun' => $agendaTahun ?: null,
            'dokumen_id' => $dokumenId,
            'tanggal' => $tanggal,
            'id_sumber_dana' => $this->findInCache($this->sumberDanaCache, $sumberDana),
            'id_bank_tujuan' => $this->findInCache($this->bankTujuanCache, $bankTujuan),
            'id_kategori_kriteria' => $this->findInCache($this->kategoriCache, $kategori),
            'id_sub_kriteria' => $this->findInCache($this->subKriteriaCache, $data['sub_kriteria'] ?? ''),
            'id_item_sub_kriteria' => $this->findInCache($this->itemSubKriteriaCache, $data['item_sub_kriteria'] ?? ''),
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
