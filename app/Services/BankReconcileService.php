<?php

namespace App\Services;

use App\Models\BankMasuk;
use App\Models\BankKeluar;
use App\Models\SumberDana;
use App\Models\BankTujuan;
use App\Models\KategoriKriteria;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use App\Models\JenisPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankReconcileService
{
    private array $sumberDanaCache = [];
    private array $bankTujuanCache = [];
    private array $kategoriCache = [];
    private array $subKriteriaCache = [];
    private array $itemSubKriteriaCache = [];
    private array $jenisPembayaranCache = [];
    private array $findInCacheMemo = [];

    public function __construct()
    {
        $this->loadCaches();
    }

    public function loadCaches(): void
    {
        $this->sumberDanaCache = [];
        $this->bankTujuanCache = [];
        $this->kategoriCache = [];
        $this->subKriteriaCache = [];
        $this->itemSubKriteriaCache = [];
        $this->jenisPembayaranCache = [];
        $this->findInCacheMemo = [];

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
        return trim((string) preg_replace('/\s+/', ' ', $value));
    }

    private function extractReferenceNumbers(?string $value): array
    {
        preg_match_all('/\d+/', (string) $value, $matches);
        return $matches[0] ?? [];
    }

    private function cleanText($val): string
    {
        if ($val === null) return '';
        $val = trim((string) $val);
        $val = preg_replace('/\s+/', ' ', $val);
        return $val;
    }

    private function findInCache(array $cache, ?string $target)
    {
        if (empty($target)) return null;

        $memoKey = md5(serialize(array_column($cache, 'id')) . '|' . $target);
        if (array_key_exists($memoKey, $this->findInCacheMemo)) {
            return $this->findInCacheMemo[$memoKey];
        }

        $targetClean = $this->cleanText($target);
        $targetNormalized = $this->normalizeReferenceText($target);
        $targetNumbers = $this->extractReferenceNumbers($target);

        // 1. Exact match on raw name
        foreach ($cache as $item) {
            if (strcasecmp($this->cleanText($item['name']), $targetClean) === 0) {
                return $this->findInCacheMemo[$memoKey] = $item['id'];
            }
        }

        // 2. Exact match on normalized
        if ($targetNormalized !== '') {
            foreach ($cache as $item) {
                if ($item['normalized'] !== '' && $item['normalized'] === $targetNormalized) {
                    return $this->findInCacheMemo[$memoKey] = $item['id'];
                }
            }
        }

        // 3. Substring match
        if ($targetNormalized !== '') {
            foreach ($cache as $item) {
                if ($item['normalized'] !== '') {
                    if (str_contains($item['normalized'], $targetNormalized) || str_contains($targetNormalized, $item['normalized'])) {
                        return $this->findInCacheMemo[$memoKey] = $item['id'];
                    }
                }
            }
        }

        // 4. Similarity match
        $bestId = null;
        $bestSim = 0;
        foreach ($cache as $item) {
            if ($item['normalized'] === '' || $targetNormalized === '') continue;
            similar_text($targetNormalized, $item['normalized'], $perc);
            if ($perc > 75 && $perc > $bestSim) {
                $bestSim = $perc;
                $bestId = $item['id'];
            }
        }

        return $this->findInCacheMemo[$memoKey] = $bestId;
    }

    private function parseTanggal($val): ?string
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

    private function parseNilai($val): float
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
    public function reconcileBankMasuk(string $filePath, bool $isDryRun, bool $isFixSelisih = false, ?callable $onProgress = null): array
    {
        if ($onProgress) $onProgress(5, "Membaca data Bank Masuk dari database...");

        // Load DB Rows
        $dbRows = BankMasuk::all()->keyBy('id_bank_masuk');
        $initialCount = $dbRows->count();
        $initialSum = (float) $dbRows->sum('debet');

        // Delete junk rows
        $junkIds = [];
        foreach ($dbRows as $id => $row) {
            if (empty($row->tanggal) && ((float) $row->debet === 0.0 || (float) $row->nilai_rupiah === 0.0) && empty(trim((string) $row->uraian))) {
                $junkIds[] = $id;
            }
        }

        if (!empty($junkIds) && !$isDryRun) {
            BankMasuk::whereIn('id_bank_masuk', $junkIds)->delete();
            foreach ($junkIds as $jid) unset($dbRows[$jid]);
        }

        if ($onProgress) $onProgress(15, "Membaca file CSV Bank Masuk...");

        if (!file_exists($filePath)) {
            throw new \RuntimeException("File Bank Masuk CSV tidak ditemukan: {$filePath}");
        }

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

        if ($onProgress) $onProgress(30, "Mencocokkan {$csvCount} baris Bank Masuk dengan data database...");

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

        // Pass 3: (debet, uraian)
        $dbByDebetUraian = [];
        foreach ($dbAvailable as $dbId => $cand) {
            $k = sprintf('%.2f', (float)$cand->debet) . '|' . strtolower($this->cleanText($cand->uraian));
            $dbByDebetUraian[$k][$dbId] = $cand;
        }

        foreach ($csvRows as $idx => $cm) {
            if (isset($matched[$idx])) continue;
            $k = sprintf('%.2f', $cm['debet']) . '|' . strtolower($this->cleanText($cm['uraian']));
            if (isset($dbByDebetUraian[$k])) {
                foreach ($dbByDebetUraian[$k] as $dbId => $cand) {
                    if (isset($dbAvailable[$dbId])) {
                        $matched[$idx] = $dbId;
                        unset($dbAvailable[$dbId]);
                        unset($dbByDebetUraian[$k][$dbId]);
                        break;
                    }
                }
            }
        }

        // Pass 4: Fix Selisih (jika aktif) -> Cocokkan baris meskipun debet berbeda di database!
        if ($isFixSelisih) {
            if ($onProgress) $onProgress(45, "Menjalankan Pass Koreksi Selisih Bank Masuk...");

            // Pass 4a: Match by unique no_bukti
            $dbByNoBukti = [];
            foreach ($dbAvailable as $dbId => $cand) {
                $nb = $this->cleanText($cand->no_bukti);
                if (strlen($nb) >= 3) {
                    $dbByNoBukti[$nb][$dbId] = $cand;
                }
            }

            foreach ($csvRows as $idx => $cm) {
                if (isset($matched[$idx])) continue;
                $nb = $this->cleanText($cm['no_bukti']);
                if (strlen($nb) >= 3 && isset($dbByNoBukti[$nb])) {
                    if (count($dbByNoBukti[$nb]) === 1) {
                        $dbId = array_key_first($dbByNoBukti[$nb]);
                        if (isset($dbAvailable[$dbId])) {
                            $matched[$idx] = $dbId;
                            unset($dbAvailable[$dbId]);
                            unset($dbByNoBukti[$nb][$dbId]);
                        }
                    }
                }
            }

            // Pass 4b: Match by unique agenda_tahun
            $dbByAgenda = [];
            foreach ($dbAvailable as $dbId => $cand) {
                $ag = $this->cleanText($cand->agenda_tahun);
                if (strlen($ag) >= 2) {
                    $dbByAgenda[$ag][$dbId] = $cand;
                }
            }

            foreach ($csvRows as $idx => $cm) {
                if (isset($matched[$idx])) continue;
                $ag = $this->cleanText($cm['agenda_tahun']);
                if (strlen($ag) >= 2 && isset($dbByAgenda[$ag])) {
                    if (count($dbByAgenda[$ag]) === 1) {
                        $dbId = array_key_first($dbByAgenda[$ag]);
                        if (isset($dbAvailable[$dbId])) {
                            $matched[$idx] = $dbId;
                            unset($dbAvailable[$dbId]);
                            unset($dbByAgenda[$ag][$dbId]);
                        }
                    }
                }
            }

            // Pass 4c: Match by (tanggal + exact uraian) regardless of debet
            $dbByTglUraian = [];
            foreach ($dbAvailable as $dbId => $cand) {
                $k = $cand->tanggal . '|' . strtolower($this->cleanText($cand->uraian));
                $dbByTglUraian[$k][$dbId] = $cand;
            }

            foreach ($csvRows as $idx => $cm) {
                if (isset($matched[$idx])) continue;
                $k = $cm['tanggal'] . '|' . strtolower($this->cleanText($cm['uraian']));
                if (isset($dbByTglUraian[$k])) {
                    foreach ($dbByTglUraian[$k] as $dbId => $cand) {
                        if (isset($dbAvailable[$dbId])) {
                            $matched[$idx] = $dbId;
                            unset($dbAvailable[$dbId]);
                            unset($dbByTglUraian[$k][$dbId]);
                            break;
                        }
                    }
                }
            }
        }

        if ($onProgress) $onProgress(60, "Mempersiapkan pembaruan data Bank Masuk...");

        // Updates
        $updateList = [];
        $fixedSelisihCount = 0;
        $fixedSelisihDetails = [];

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

            // Fix Selisih nominal
            if ($isFixSelisih && abs((float)$dm->debet - (float)$cm['debet']) > 0.009) {
                $oldNominal = (float) $dm->debet;
                $newNominal = (float) $cm['debet'];
                $diffs['debet'] = $newNominal;
                $diffs['nilai_rupiah'] = $newNominal;
                $fixedSelisihCount++;
                $fixedSelisihDetails[] = [
                    'id' => $dbId,
                    'no_bukti' => $cm['no_bukti'] ?: $dm->no_bukti,
                    'uraian' => substr($cm['uraian'] ?: $dm->uraian, 0, 40),
                    'old_nominal' => $oldNominal,
                    'new_nominal' => $newNominal,
                ];
            }

            if (!empty($diffs)) {
                $updateList[] = ['id' => $dbId, 'diffs' => $diffs];
            }
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

        // Purge orphan phantom records on Fix Selisih
        $deletedPhantomCount = 0;
        $deletedPhantomDetails = [];
        if ($isFixSelisih && !empty($dbAvailable)) {
            $csvSumberDanaIds = [];
            foreach ($csvRows as $cm) {
                $sid = $this->findInCache($this->sumberDanaCache, $cm['sumber_dana']);
                if ($sid) $csvSumberDanaIds[$sid] = true;
            }

            $phantomIds = [];
            foreach ($dbAvailable as $dbId => $cand) {
                if ($cand->id_sumber_dana && isset($csvSumberDanaIds[$cand->id_sumber_dana])) {
                    if (empty($cand->agenda_tahun) && empty($cand->no_bukti)) {
                        $phantomIds[] = $dbId;
                        $deletedPhantomDetails[] = [
                            'id' => $dbId,
                            'tanggal' => $cand->tanggal,
                            'uraian' => substr((string) $cand->uraian, 0, 40),
                            'nominal' => (float) $cand->debet,
                            'action' => 'deleted_phantom',
                        ];
                    }
                }
            }

            if (!empty($phantomIds)) {
                $deletedPhantomCount = count($phantomIds);
            }
        }

        if ($onProgress) $onProgress(80, "Menyimpan perubahan Bank Masuk ke database...");

        // Apply changes
        if (!$isDryRun) {
            DB::beginTransaction();
            try {
                if (!empty($phantomIds)) {
                    BankMasuk::whereIn('id_bank_masuk', $phantomIds)->delete();
                }
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
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[BankReconcileService] Gagal simpan Bank Masuk: " . $e->getMessage());
                throw $e;
            }
        }

        if ($onProgress) $onProgress(100, "Bank Masuk selesai diproses.");

        return [
            'total_csv' => $csvCount,
            'sum_csv' => $csvSum,
            'matched' => count($matched),
            'updated' => count($updateList),
            'inserted' => count($insertList),
            'fixed_selisih' => $fixedSelisihCount + $deletedPhantomCount,
            'fixed_details' => array_merge($fixedSelisihDetails, $deletedPhantomDetails),
        ];
    }

    // =========================================================================
    // BANK KELUAR RECONCILIATION
    // =========================================================================
    public function reconcileBankKeluar(string $filePath, bool $isDryRun, bool $isFixSelisih = false, ?callable $onProgress = null): array
    {
        if ($onProgress) $onProgress(5, "Membaca data Bank Keluar dari database...");

        // Load DB Rows
        $dbRows = BankKeluar::all()->keyBy('id_bank_keluar');
        $initialCount = $dbRows->count();
        $initialSum = (float) $dbRows->sum('kredit');

        if ($onProgress) $onProgress(15, "Membaca file CSV Bank Keluar...");

        if (!file_exists($filePath)) {
            throw new \RuntimeException("File Bank Keluar CSV tidak ditemukan: {$filePath}");
        }

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

        if ($onProgress) $onProgress(30, "Mencocokkan {$csvCount} baris Bank Keluar dengan database...");

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

        // Pass 4: date shift match with same agenda and kredit
        $dbByAgenda = [];
        foreach ($dbAvailable as $dbId => $cand) {
            if (!empty($cand->agenda_tahun)) {
                $dbByAgenda[$cand->agenda_tahun][$dbId] = $cand;
            }
        }

        foreach ($csvRows as $idx => $ck) {
            if (isset($matched[$idx])) continue;
            if (!empty($ck['agenda_tahun']) && isset($dbByAgenda[$ck['agenda_tahun']])) {
                foreach ($dbByAgenda[$ck['agenda_tahun']] as $dbId => $cand) {
                    if (isset($dbAvailable[$dbId]) && abs((float)$cand->kredit - $ck['kredit']) < 0.01) {
                        $matched[$idx] = $dbId;
                        unset($dbAvailable[$dbId]);
                        unset($dbByAgenda[$ck['agenda_tahun']][$dbId]);
                        break;
                    }
                }
            }
        }

        // Pass 5: Fix Selisih (jika aktif) -> Cocokkan baris meskipun kredit berbeda di database!
        if ($isFixSelisih) {
            if ($onProgress) $onProgress(45, "Menjalankan Pass Koreksi Selisih Bank Keluar...");

            // Pass 5a: Match by agenda_tahun regardless of kredit!
            foreach ($csvRows as $idx => $ck) {
                if (isset($matched[$idx])) continue;
                $ag = $this->cleanText($ck['agenda_tahun']);
                if (strlen($ag) >= 2 && isset($dbByAgenda[$ag])) {
                    // Pick candidate
                    $bestCandId = null;
                    foreach ($dbByAgenda[$ag] as $dbId => $cand) {
                        if (isset($dbAvailable[$dbId])) {
                            // If no_bukti matches or exact match
                            if (!empty($ck['no_bukti']) && $cand->no_bukti === $ck['no_bukti']) {
                                $bestCandId = $dbId;
                                break;
                            }
                            if ($bestCandId === null) {
                                $bestCandId = $dbId;
                            }
                        }
                    }

                    if ($bestCandId !== null) {
                        $matched[$idx] = $bestCandId;
                        unset($dbAvailable[$bestCandId]);
                        unset($dbByAgenda[$ag][$bestCandId]);
                    }
                }
            }

            // Pass 5b: Match by unique no_bukti regardless of kredit!
            $dbByNoBukti = [];
            foreach ($dbAvailable as $dbId => $cand) {
                $nb = $this->cleanText($cand->no_bukti);
                if (strlen($nb) >= 3) {
                    $dbByNoBukti[$nb][$dbId] = $cand;
                }
            }

            foreach ($csvRows as $idx => $ck) {
                if (isset($matched[$idx])) continue;
                $nb = $this->cleanText($ck['no_bukti']);
                if (strlen($nb) >= 3 && isset($dbByNoBukti[$nb])) {
                    if (count($dbByNoBukti[$nb]) === 1) {
                        $dbId = array_key_first($dbByNoBukti[$nb]);
                        if (isset($dbAvailable[$dbId])) {
                            $matched[$idx] = $dbId;
                            unset($dbAvailable[$dbId]);
                            unset($dbByNoBukti[$nb][$dbId]);
                        }
                    }
                }
            }

            // Pass 5c: Match by (tanggal + exact uraian) regardless of kredit!
            $dbByTglUraian = [];
            foreach ($dbAvailable as $dbId => $cand) {
                $k = $cand->tanggal . '|' . strtolower($this->cleanText($cand->uraian));
                $dbByTglUraian[$k][$dbId] = $cand;
            }

            foreach ($csvRows as $idx => $ck) {
                if (isset($matched[$idx])) continue;
                $k = $ck['tanggal'] . '|' . strtolower($this->cleanText($ck['uraian']));
                if (isset($dbByTglUraian[$k])) {
                    foreach ($dbByTglUraian[$k] as $dbId => $cand) {
                        if (isset($dbAvailable[$dbId])) {
                            $matched[$idx] = $dbId;
                            unset($dbAvailable[$dbId]);
                            unset($dbByTglUraian[$k][$dbId]);
                            break;
                        }
                    }
                }
            }
        }

        if ($onProgress) $onProgress(60, "Mempersiapkan pembaruan data Bank Keluar...");

        // Updates
        $updateList = [];
        $fixedSelisihCount = 0;
        $fixedSelisihDetails = [];

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

            // Fix Selisih nominal
            if ($isFixSelisih && abs((float)$dk->kredit - (float)$ck['kredit']) > 0.009) {
                $oldNominal = (float) $dk->kredit;
                $newNominal = (float) $ck['kredit'];
                $diffs['kredit'] = $newNominal;
                $diffs['nilai_rupiah'] = $newNominal;
                $fixedSelisihCount++;
                $fixedSelisihDetails[] = [
                    'id' => $dbId,
                    'agenda_tahun' => $ck['agenda_tahun'] ?: $dk->agenda_tahun,
                    'no_bukti' => $ck['no_bukti'] ?: $dk->no_bukti,
                    'uraian' => substr($ck['uraian'] ?: $dk->uraian, 0, 40),
                    'old_nominal' => $oldNominal,
                    'new_nominal' => $newNominal,
                ];
            }

            if (!empty($diffs)) {
                $updateList[] = ['id' => $dbId, 'diffs' => $diffs];
            }
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

        // Purge orphan phantom records on Fix Selisih
        $deletedPhantomCount = 0;
        $deletedPhantomDetails = [];
        if ($isFixSelisih && !empty($dbAvailable)) {
            $csvSumberDanaIds = [];
            foreach ($csvRows as $ck) {
                $sid = $this->findInCache($this->sumberDanaCache, $ck['sumber_dana']);
                if ($sid) $csvSumberDanaIds[$sid] = true;
            }

            $phantomIds = [];
            foreach ($dbAvailable as $dbId => $cand) {
                if ($cand->id_sumber_dana && isset($csvSumberDanaIds[$cand->id_sumber_dana])) {
                    if (empty($cand->agenda_tahun) && empty($cand->no_bukti) && empty($cand->dokumen_id)) {
                        $phantomIds[] = $dbId;
                        $deletedPhantomDetails[] = [
                            'id' => $dbId,
                            'tanggal' => $cand->tanggal,
                            'uraian' => substr((string) $cand->uraian, 0, 40),
                            'nominal' => (float) $cand->kredit,
                            'action' => 'deleted_phantom',
                        ];
                    }
                }
            }

            if (!empty($phantomIds)) {
                $deletedPhantomCount = count($phantomIds);
            }
        }

        if ($onProgress) $onProgress(80, "Menyimpan perubahan Bank Keluar ke database...");

        $agendaBayarCount = 0;
        // Apply changes
        if (!$isDryRun) {
            DB::beginTransaction();
            try {
                if (!empty($phantomIds)) {
                    BankKeluar::whereIn('id_bank_keluar', $phantomIds)->delete();
                }
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

                // Sync payment dates to Agenda Online
                if (!empty($barisAgenda)) {
                    if ($onProgress) $onProgress(90, "Menyinkronkan tanggal bayar ke Agenda Online...");
                    $agendaBayarCount = (new AgendaTanggalBayarSync())->sinkronkan($barisAgenda);
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[BankReconcileService] Gagal simpan Bank Keluar: " . $e->getMessage());
                throw $e;
            }
        }

        if ($onProgress) $onProgress(100, "Bank Keluar selesai diproses.");

        return [
            'total_csv' => $csvCount,
            'sum_csv' => $csvSum,
            'matched' => count($matched),
            'updated' => count($updateList),
            'inserted' => count($insertList),
            'fixed_selisih' => $fixedSelisihCount + $deletedPhantomCount,
            'fixed_details' => array_merge($fixedSelisihDetails, $deletedPhantomDetails),
            'agenda_bayar_synced' => $agendaBayarCount,
        ];
    }
}
