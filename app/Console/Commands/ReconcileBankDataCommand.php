<?php

namespace App\Console\Commands;

use App\Models\BankMasuk;
use App\Models\BankKeluar;
use App\Models\SumberDana;
use App\Models\BankTujuan;
use App\Models\KategoriKriteria;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use App\Models\JenisPembayaran;
use App\Services\AgendaTanggalBayarSync;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconcileBankDataCommand extends Command
{
    protected $signature = 'bank:reconcile
                            {--masuk= : Path to Bank Masuk CSV}
                            {--keluar= : Path to Bank Keluar CSV}
                            {--dry-run : Only simulate and show changes without modifying database}
                            {--force : Actually execute and save changes}';

    protected $description = 'Reconcile Bank Masuk & Bank Keluar database records against master CSV files';

    private $sumberDanaCache = [];
    private $bankTujuanCache = [];
    private $kategoriCache = [];
    private $subKriteriaCache = [];
    private $itemSubKriteriaCache = [];
    private $jenisPembayaranCache = [];

    public function handle()
    {
        ini_set('max_execution_time', '1800');
        ini_set('memory_limit', '2048M');

        $isDryRun = $this->option('dry-run') || !$this->option('force');

        if ($isDryRun) {
            $this->warn("==================================================================");
            $this->warn("MODE: DRY RUN (SIMULASI). Data TIDAK akan diubah di database.");
            $this->warn("Gunakan flag --force untuk benar-benar menerapkan perubahan.");
            $this->warn("==================================================================");
        } else {
            $this->info("==================================================================");
            $this->info("MODE: LIVE / FORCE. Perubahan AKAN diterapkan ke database.");
            $this->info("==================================================================");
        }

        $this->loadCaches();

        $masukFile = $this->option('masuk') ?: storage_path('app/bank_masuk_reconcile.csv');
        $keluarFile = $this->option('keluar') ?: storage_path('app/bank_keluar_reconcile.csv');

        // 1. Reconcile Bank Masuk
        if (file_exists($masukFile)) {
            $this->reconcileBankMasuk($masukFile, $isDryRun);
        } else {
            $this->error("File Bank Masuk CSV tidak ditemukan: {$masukFile}");
        }

        // 2. Reconcile Bank Keluar
        if (file_exists($keluarFile)) {
            $this->reconcileBankKeluar($keluarFile, $isDryRun);
        } else {
            $this->error("File Bank Keluar CSV tidak ditemukan: {$keluarFile}");
        }

        $this->info("\nProses rekonsiliasi selesai.");
        return Command::SUCCESS;
    }

    private function loadCaches()
    {
        $this->line("Memuat referensi cache...");

        foreach (SumberDana::all() as $sd) {
            $this->addReferenceCache($this->sumberDanaCache, $sd->nama_sumber_dana, $sd->id_sumber_dana);
        }
        foreach (BankTujuan::all() as $bt) {
            $this->addReferenceCache($this->bankTujuanCache, $bt->nama_tujuan, $bt->id_bank_tujuan);
        }
        foreach (KategoriKriteria::all() as $k) {
            $this->addReferenceCache($this->kategoriCache, $k->nama_kriteria, $k->id_kategori_kriteria);
        }
        foreach (SubKriteria::all() as $s) {
            $this->addReferenceCache($this->subKriteriaCache, $s->nama_sub_kriteria, $s->id_sub_kriteria);
        }
        foreach (ItemSubKriteria::all() as $i) {
            $this->addReferenceCache($this->itemSubKriteriaCache, $i->nama_item_sub_kriteria, $i->id_item_sub_kriteria);
        }
        foreach (JenisPembayaran::all() as $j) {
            $this->addReferenceCache($this->jenisPembayaranCache, $j->nama_jenis_pembayaran, $j->id_jenis_pembayaran);
        }

        $this->line("Cache selesai dimuat.");
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

    private function findInCache($cache, $search)
    {
        if (empty($search)) return null;

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
            if ($nameText === '' || $searchText === '') continue;

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

    private function cleanText($text)
    {
        if ($text === null || $text === '') return '';
        return trim(preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', (string) $text)));
    }

    private function parseTanggal($val)
    {
        if (empty($val)) return null;
        $val = str_replace(['-', '.'], '/', trim((string) $val));
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $val, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $val)) {
            return $val;
        }
        return null;
    }

    private function parseNilai($val)
    {
        if (empty($val)) return 0.0;
        $cleaned = trim((string) $val);
        $cleaned = str_replace(['Rp', 'rp', ' '], '', $cleaned);
        $cleaned = preg_replace('/[^\d.,]/', '', $cleaned);
        if (empty($cleaned)) return 0.0;
        $cleaned = str_replace('.', '', $cleaned);
        $cleaned = str_replace(',', '.', $cleaned);
        return round((float) $cleaned, 2);
    }

    // =========================================================================
    // BANK MASUK RECONCILIATION
    // =========================================================================
    private function reconcileBankMasuk(string $filePath, bool $isDryRun)
    {
        $this->info("\n>>> MEMULAI REKONSILIASI BANK MASUK <<<");
        $this->line("File: {$filePath}");

        // Load DB Rows
        $dbRows = BankMasuk::all()->keyBy('id_bank_masuk');
        $initialCount = $dbRows->count();
        $initialSum = (float) $dbRows->sum('debet');
        $this->line("Total baris Bank Masuk di DB saat ini: {$initialCount} (Sum Debet: " . number_format($initialSum, 2, ',', '.') . ")");

        // Delete junk rows (ID 2374: tanggal null, debet 0, uraian kosong)
        $junkIds = [];
        foreach ($dbRows as $id => $row) {
            if (empty($row->tanggal) && ((float) $row->debet === 0.0 || (float) $row->nilai_rupiah === 0.0) && empty(trim((string) $row->uraian))) {
                $junkIds[] = $id;
            }
        }

        if (!empty($junkIds)) {
            $this->warn("Ditemukan " . count($junkIds) . " baris sampah (junk row) di DB Bank Masuk: ID " . implode(', ', $junkIds));
            if (!$isDryRun) {
                BankMasuk::whereIn('id_bank_masuk', $junkIds)->delete();
                foreach ($junkIds as $jid) unset($dbRows[$jid]);
                $this->info("Berhasil menghapus baris sampah Bank Masuk.");
            }
        }

        // Read CSV
        $handle = fopen($filePath, 'r');
        $rawHeader = fgetcsv($handle);
        $csvRows = [];
        $lineNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNum++;
            if (empty(array_filter($row))) continue;

            $tanggal = $this->parseTanggal($row[1] ?? '');
            if (!$tanggal) continue;

            $agenda = $this->cleanText($row[0] ?? '');
            $noBukti = $this->cleanText($row[2] ?? '');
            $sumberDana = $this->cleanText($row[3] ?? '');
            $bankTujuan = $this->cleanText($row[4] ?? '');
            $kategori = $this->cleanText($row[5] ?? '');
            $penerima = $this->cleanText($row[6] ?? '');
            $uraian = $this->cleanText($row[7] ?? '');
            $debet = $this->parseNilai($row[8] ?? 0);

            $csvRows[] = [
                'line' => $lineNum,
                'agenda_tahun' => $agenda ?: null,
                'tanggal' => $tanggal,
                'no_bukti' => $noBukti ?: null,
                'sumber_dana' => $sumberDana,
                'bank_tujuan' => $bankTujuan,
                'kategori' => $kategori,
                'penerima' => $penerima ?: null,
                'uraian' => $uraian,
                'debet' => $debet,
            ];
        }
        fclose($handle);

        $csvCount = count($csvRows);
        $csvSum = array_sum(array_column($csvRows, 'debet'));
        $this->line("Total baris valid di CSV Bank Masuk: {$csvCount} (Sum Debet: " . number_format($csvSum, 2, ',', '.') . ")");

        // Multi-pass matching
        $dbAvailable = $dbRows->all();
        $dbByKey = [];
        foreach ($dbAvailable as $id => $row) {
            $key = $row->tanggal . '|' . sprintf('%.2f', (float) $row->debet);
            $dbByKey[$key][] = $row;
        }

        $matched = []; // csv_index => db_id

        // Pass 1: exact (tanggal, debet, uraian)
        foreach ($csvRows as $idx => $cm) {
            $key = $cm['tanggal'] . '|' . sprintf('%.2f', $cm['debet']);
            if (isset($dbByKey[$key])) {
                foreach ($dbByKey[$key] as $cand) {
                    if (isset($dbAvailable[$cand->id_bank_masuk]) && strtolower($this->cleanText($cand->uraian)) === strtolower($cm['uraian'])) {
                        $matched[$idx] = $cand->id_bank_masuk;
                        unset($dbAvailable[$cand->id_bank_masuk]);
                        break;
                    }
                }
            }
        }

        // Pass 2: (tanggal, debet)
        foreach ($csvRows as $idx => $cm) {
            if (isset($matched[$idx])) continue;
            $key = $cm['tanggal'] . '|' . sprintf('%.2f', $cm['debet']);
            if (isset($dbByKey[$key])) {
                foreach ($dbByKey[$key] as $cand) {
                    if (isset($dbAvailable[$cand->id_bank_masuk])) {
                        $matched[$idx] = $cand->id_bank_masuk;
                        unset($dbAvailable[$cand->id_bank_masuk]);
                        break;
                    }
                }
            }
        }

        // Pass 3: (debet, uraian) for date shift (e.g. ID 2554)
        foreach ($csvRows as $idx => $cm) {
            if (isset($matched[$idx])) continue;
            foreach ($dbAvailable as $dbId => $cand) {
                if (abs((float)$cand->debet - $cm['debet']) < 0.01 && strtolower($this->cleanText($cand->uraian)) === strtolower($cm['uraian'])) {
                    $matched[$idx] = $dbId;
                    unset($dbAvailable[$dbId]);
                    break;
                }
            }
        }

        $this->info("Bank Masuk Matched: " . count($matched) . " baris.");

        // Updates
        $updateList = [];
        foreach ($matched as $idx => $dbId) {
            $cm = $csvRows[$idx];
            $dm = $dbRows[$dbId];
            $diffs = [];

            if ($dm->tanggal !== $cm['tanggal']) {
                $diffs['tanggal'] = $cm['tanggal'];
            }
            if ($this->cleanText($dm->uraian) !== $cm['uraian'] && !empty($cm['uraian'])) {
                $diffs['uraian'] = $cm['uraian'];
            }
            if (!empty($cm['no_bukti']) && $this->cleanText($dm->no_bukti) !== $cm['no_bukti']) {
                $diffs['no_bukti'] = $cm['no_bukti'];
            }
            if (!empty($cm['agenda_tahun']) && $this->cleanText($dm->agenda_tahun) !== $cm['agenda_tahun']) {
                $diffs['agenda_tahun'] = $cm['agenda_tahun'];
            }

            // References
            $idSumber = $this->findInCache($this->sumberDanaCache, $cm['sumber_dana']);
            if ($idSumber && $dm->id_sumber_dana != $idSumber) {
                $diffs['id_sumber_dana'] = $idSumber;
            }
            $idTujuan = $this->findInCache($this->bankTujuanCache, $cm['bank_tujuan']);
            if ($idTujuan && $dm->id_bank_tujuan != $idTujuan) {
                $diffs['id_bank_tujuan'] = $idTujuan;
            }
            $idKat = $this->findInCache($this->kategoriCache, $cm['kategori']);
            if ($idKat && $dm->id_kategori_kriteria != $idKat) {
                $diffs['id_kategori_kriteria'] = $idKat;
            }

            if (!empty($diffs)) {
                $updateList[] = ['id' => $dbId, 'diffs' => $diffs];
            }
        }

        $this->info("Bank Masuk Memerlukan Update: " . count($updateList) . " baris.");
        foreach (array_slice($updateList, 0, 5) as $u) {
            $this->line("  [Update BM ID {$u['id']}]: " . json_encode($u['diffs']));
        }

        // Inserts (unmatched CSV rows)
        $insertList = [];
        foreach ($csvRows as $idx => $cm) {
            if (!isset($matched[$idx])) {
                $insertList[] = [
                    'agenda_tahun' => $cm['agenda_tahun'],
                    'no_bukti' => $cm['no_bukti'],
                    'tanggal' => $cm['tanggal'],
                    'id_sumber_dana' => $this->findInCache($this->sumberDanaCache, $cm['sumber_dana']),
                    'id_bank_tujuan' => $this->findInCache($this->bankTujuanCache, $cm['bank_tujuan']),
                    'id_kategori_kriteria' => $this->findInCache($this->kategoriCache, $cm['kategori']),
                    'id_jenis_pembayaran' => null,
                    'penerima' => $cm['penerima'],
                    'uraian' => $cm['uraian'],
                    'debet' => $cm['debet'],
                    'nilai_rupiah' => $cm['debet'],
                    'kredit' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        $this->info("Bank Masuk Missing di DB (akan di-insert): " . count($insertList) . " baris.");

        // Apply changes
        if (!$isDryRun) {
            DB::beginTransaction();
            try {
                foreach ($updateList as $u) {
                    $u['diffs']['updated_at'] = now();
                    BankMasuk::where('id_bank_masuk', $u['id'])->update($u['diffs']);
                }
                if (!empty($insertList)) {
                    foreach (array_chunk($insertList, 100) as $chunk) {
                        BankMasuk::insert($chunk);
                    }
                }
                DB::commit();
                $this->info("Berhasil menyimpan update dan insert untuk Bank Masuk!");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Gagal menyimpan Bank Masuk: " . $e->getMessage());
                throw $e;
            }
        }
    }

    // =========================================================================
    // BANK KELUAR RECONCILIATION
    // =========================================================================
    private function reconcileBankKeluar(string $filePath, bool $isDryRun)
    {
        $this->info("\n>>> MEMULAI REKONSILIASI BANK KELUAR <<<");
        $this->line("File: {$filePath}");

        // Load DB Rows
        $dbRows = BankKeluar::all()->keyBy('id_bank_keluar');
        $initialCount = $dbRows->count();
        $initialSum = (float) $dbRows->sum('kredit');
        $this->line("Total baris Bank Keluar di DB saat ini: {$initialCount} (Sum Kredit: " . number_format($initialSum, 2, ',', '.') . ")");

        // Read CSV
        $handle = fopen($filePath, 'r');
        $rawHeader = fgetcsv($handle);
        $header = array_map(fn($h) => str_replace(' ', '_', strtolower(trim($h))), $rawHeader);
        $headerCount = count($header);

        $csvRows = [];
        $lineNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNum++;
            if (empty(array_filter($row))) continue;

            $rowCount2 = count($row);
            if ($rowCount2 < $headerCount) {
                $row = array_pad($row, $headerCount, '');
            } elseif ($rowCount2 > $headerCount) {
                $row = array_slice($row, 0, $headerCount);
            }

            $data = array_combine($header, $row);
            $tanggal = $this->parseTanggal($data['tanggal'] ?? '');
            if (!$tanggal) continue;

            $agenda = $this->cleanText($data['agenda_tahun'] ?? $data['no_agenda'] ?? $data['nomor_agenda'] ?? $data['agenda'] ?? $data[''] ?? '');
            $noBukti = $this->cleanText($data['no._bukti'] ?? $data['no_bukti'] ?? $data['bukti'] ?? '');
            $sumberDana = $this->cleanText($data['sumber_dana'] ?? '');
            $bankTujuan = $this->cleanText($data['bank_tujuan'] ?? '');
            $kategoriRaw = $this->cleanText($data['kategori'] ?? $data['kriteria_cf'] ?? $data['kriteria'] ?? '');
            $kategori = preg_replace('/^\d+\.\s*/', '', $kategoriRaw);
            $penerima = $this->cleanText($data['penerima'] ?? $data['penerima/dari'] ?? '');
            $uraian = $this->cleanText($data['uraian'] ?? $data['edit_uraian'] ?? '');
            $kredit = $this->parseNilai($data['kredit'] ?? $data['debet'] ?? 0);

            $csvRows[] = [
                'line' => $lineNum,
                'agenda_tahun' => $agenda ?: null,
                'tanggal' => $tanggal,
                'no_bukti' => $noBukti ?: null,
                'sumber_dana' => $sumberDana,
                'bank_tujuan' => $bankTujuan,
                'kategori' => $kategori,
                'penerima' => $penerima ?: null,
                'uraian' => $uraian,
                'kredit' => $kredit,
            ];
        }
        fclose($handle);

        $csvCount = count($csvRows);
        $csvSum = array_sum(array_column($csvRows, 'kredit'));
        $this->line("Total baris valid di CSV Bank Keluar: {$csvCount} (Sum Kredit: " . number_format($csvSum, 2, ',', '.') . ")");

        // Multi-pass matching
        $dbAvailable = $dbRows->all();
        $dbByKey = [];
        foreach ($dbAvailable as $id => $row) {
            $key = $row->tanggal . '|' . sprintf('%.2f', (float) $row->kredit);
            $dbByKey[$key][] = $row;
        }

        $matched = []; // csv_index => db_id

        // Pass 1: exact (tanggal, kredit, uraian[:30])
        foreach ($csvRows as $idx => $ck) {
            $key = $ck['tanggal'] . '|' . sprintf('%.2f', $ck['kredit']);
            if (isset($dbByKey[$key])) {
                $subUraian = substr(strtolower($ck['uraian']), 0, 30);
                foreach ($dbByKey[$key] as $cand) {
                    if (isset($dbAvailable[$cand->id_bank_keluar])) {
                        $candSub = substr(strtolower($this->cleanText($cand->uraian)), 0, 30);
                        if ($candSub === $subUraian) {
                            $matched[$idx] = $cand->id_bank_keluar;
                            unset($dbAvailable[$cand->id_bank_keluar]);
                            break;
                        }
                    }
                }
            }
        }

        // Pass 2: (tanggal, kredit) with agenda match
        foreach ($csvRows as $idx => $ck) {
            if (isset($matched[$idx])) continue;
            $key = $ck['tanggal'] . '|' . sprintf('%.2f', $ck['kredit']);
            if (isset($dbByKey[$key])) {
                foreach ($dbByKey[$key] as $cand) {
                    if (isset($dbAvailable[$cand->id_bank_keluar]) && !empty($ck['agenda_tahun']) && $cand->agenda_tahun === $ck['agenda_tahun']) {
                        $matched[$idx] = $cand->id_bank_keluar;
                        unset($dbAvailable[$cand->id_bank_keluar]);
                        break;
                    }
                }
            }
        }

        // Pass 3: (tanggal, kredit) remaining
        foreach ($csvRows as $idx => $ck) {
            if (isset($matched[$idx])) continue;
            $key = $ck['tanggal'] . '|' . sprintf('%.2f', $ck['kredit']);
            if (isset($dbByKey[$key])) {
                foreach ($dbByKey[$key] as $cand) {
                    if (isset($dbAvailable[$cand->id_bank_keluar])) {
                        $matched[$idx] = $cand->id_bank_keluar;
                        unset($dbAvailable[$cand->id_bank_keluar]);
                        break;
                    }
                }
            }
        }

        // Pass 4: date shift match (e.g. ID 11411 Ground Checking 5056_2026 on 2026-07-16 -> 2026-07-15)
        foreach ($csvRows as $idx => $ck) {
            if (isset($matched[$idx])) continue;
            foreach ($dbAvailable as $dbId => $cand) {
                if (abs((float)$cand->kredit - $ck['kredit']) < 0.01 && !empty($ck['agenda_tahun']) && $cand->agenda_tahun === $ck['agenda_tahun']) {
                    $matched[$idx] = $dbId;
                    unset($dbAvailable[$dbId]);
                    break;
                }
            }
        }

        $this->info("Bank Keluar Matched: " . count($matched) . " baris.");

        // Updates
        $updateList = [];
        foreach ($matched as $idx => $dbId) {
            $ck = $csvRows[$idx];
            $dk = $dbRows[$dbId];
            $diffs = [];

            if ($dk->tanggal !== $ck['tanggal']) {
                $diffs['tanggal'] = $ck['tanggal'];
            }
            if ($this->cleanText($dk->uraian) !== $ck['uraian'] && !empty($ck['uraian'])) {
                $diffs['uraian'] = $ck['uraian'];
            }
            if (!empty($ck['agenda_tahun']) && $this->cleanText($dk->agenda_tahun) !== $ck['agenda_tahun']) {
                $diffs['agenda_tahun'] = $ck['agenda_tahun'];
            }
            if (!empty($ck['no_bukti']) && $this->cleanText($dk->no_bukti) !== $ck['no_bukti']) {
                $diffs['no_bukti'] = $ck['no_bukti'];
            }
            if (!empty($ck['penerima']) && $this->cleanText($dk->penerima) !== $ck['penerima']) {
                $diffs['penerima'] = $ck['penerima'];
            }

            // References
            $idSumber = $this->findInCache($this->sumberDanaCache, $ck['sumber_dana']);
            if ($idSumber && $dk->id_sumber_dana != $idSumber) {
                $diffs['id_sumber_dana'] = $idSumber;
            }
            $idTujuan = $this->findInCache($this->bankTujuanCache, $ck['bank_tujuan']);
            if ($idTujuan && $dk->id_bank_tujuan != $idTujuan) {
                $diffs['id_bank_tujuan'] = $idTujuan;
            }
            $idKat = $this->findInCache($this->kategoriCache, $ck['kategori']);
            if ($idKat && $dk->id_kategori_kriteria != $idKat) {
                $diffs['id_kategori_kriteria'] = $idKat;
            }

            if (!empty($diffs)) {
                $updateList[] = ['id' => $dbId, 'diffs' => $diffs];
            }
        }

        $this->info("Bank Keluar Memerlukan Update: " . count($updateList) . " baris.");
        foreach (array_slice($updateList, 0, 5) as $u) {
            $this->line("  [Update BK ID {$u['id']}]: " . json_encode($u['diffs']));
        }

        // Inserts (unmatched CSV rows)
        $insertList = [];
        $barisAgenda = [];
        foreach ($csvRows as $idx => $ck) {
            if (!isset($matched[$idx])) {
                $insertList[] = [
                    'agenda_tahun' => $ck['agenda_tahun'],
                    'no_bukti' => $ck['no_bukti'],
                    'dokumen_id' => null,
                    'tanggal' => $ck['tanggal'],
                    'id_sumber_dana' => $this->findInCache($this->sumberDanaCache, $ck['sumber_dana']),
                    'id_bank_tujuan' => $this->findInCache($this->bankTujuanCache, $ck['bank_tujuan']),
                    'id_kategori_kriteria' => $this->findInCache($this->kategoriCache, $ck['kategori']),
                    'id_sub_kriteria' => null,
                    'id_item_sub_kriteria' => null,
                    'id_jenis_pembayaran' => null,
                    'penerima' => $ck['penerima'],
                    'uraian' => $ck['uraian'],
                    'kredit' => $ck['kredit'],
                    'debet' => 0,
                    'nilai_rupiah' => $ck['kredit'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (!empty($ck['agenda_tahun']) && !empty($ck['tanggal'])) {
                    $barisAgenda[] = [
                        'agenda_tahun' => $ck['agenda_tahun'],
                        'tanggal' => $ck['tanggal'],
                    ];
                }
            }
        }

        $this->info("Bank Keluar Missing di DB (akan di-insert): " . count($insertList) . " baris.");

        // Apply changes
        if (!$isDryRun) {
            DB::beginTransaction();
            try {
                foreach ($updateList as $u) {
                    $u['diffs']['updated_at'] = now();
                    BankKeluar::where('id_bank_keluar', $u['id'])->update($u['diffs']);
                }
                if (!empty($insertList)) {
                    foreach (array_chunk($insertList, 100) as $chunk) {
                        BankKeluar::insert($chunk);
                    }
                }
                DB::commit();
                $this->info("Berhasil menyimpan update dan insert untuk Bank Keluar!");

                // Sync payment dates to Agenda Online
                if (!empty($barisAgenda)) {
                    $this->line("Menyinkronkan tanggal bayar ke Agenda Online...");
                    $terisi = (new AgendaTanggalBayarSync())->sinkronkan($barisAgenda);
                    $this->info("Berhasil sinkronkan {$terisi} dokumen ke Agenda Online.");
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Gagal menyimpan Bank Keluar: " . $e->getMessage());
                throw $e;
            }
        }
    }
}
