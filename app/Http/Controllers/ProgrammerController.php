<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\BankKeluar;
use App\Models\BankMasuk;
use App\Models\GabunganMasukKeluar;
use App\Models\Penerima;
use App\Models\Dropping;
use App\Models\Permintaan;
use App\Models\RencanaDropping;
use App\Models\RencanaPenerima;
use App\Models\SaldoAwal;
use App\Models\DaftarBank;
use App\Models\DaftarRekening;
use App\Models\Cashflow;
use App\Models\CashflowLock;
use App\Models\CashflowReference;
use App\Services\CashflowImportService;

class ProgrammerController extends Controller
{
    /**
     * Map of allowed table keys to their model classes and display names.
     */
    private function getTableMap(): array
    {
        return [
            'cashflows' => [
                'model' => Cashflow::class,
                'name' => 'Cash Flow Transaksi SAP',
                'icon' => 'fas fa-money-bill-wave',
                'color' => '#27ae60',
                'primary_key' => 'id',
            ],
            'cashflow_references' => [
                'model' => CashflowReference::class,
                'name' => 'Standarisasi Reff Key',
                'icon' => 'fas fa-tags',
                'color' => '#3498db',
                'primary_key' => 'id',
            ],
            'bank_keluar' => [
                'model' => BankKeluar::class,
                'name' => 'Bank Keluar',
                'icon' => 'fas fa-arrow-circle-up',
                'color' => '#e74c3c',
                'primary_key' => 'id_bank_keluar',
            ],
            'bank_masuk' => [
                'model' => BankMasuk::class,
                'name' => 'Bank Masuk',
                'icon' => 'fas fa-arrow-circle-down',
                'color' => '#27ae60',
                'primary_key' => 'id_bank_masuk',
            ],
            'gabungan' => [
                'model' => GabunganMasukKeluar::class,
                'name' => 'Gabungan Masuk Keluar',
                'icon' => 'fas fa-exchange-alt',
                'color' => '#2980b9',
                'primary_key' => 'id',
            ],
            'penerima' => [
                'model' => Penerima::class,
                'name' => 'Penerima',
                'icon' => 'fas fa-user-check',
                'color' => '#8e44ad',
                'primary_key' => 'id_penerima',
            ],
            'dropping' => [
                'model' => Dropping::class,
                'name' => 'Dropping',
                'icon' => 'fas fa-parachute-box',
                'color' => '#d35400',
                'primary_key' => 'id_dopping',
            ],
            'permintaan' => [
                'model' => Permintaan::class,
                'name' => 'Permintaan',
                'icon' => 'fas fa-hand-holding-usd',
                'color' => '#f39c12',
                'primary_key' => 'id_permintaan',
            ],
            'rencana_dropping' => [
                'model' => RencanaDropping::class,
                'name' => 'Rencana Dropping',
                'icon' => 'fas fa-calendar-alt',
                'color' => '#16a085',
                'primary_key' => 'id_rencana_dropping',
            ],
            'rencana_penerima' => [
                'model' => RencanaPenerima::class,
                'name' => 'Rencana Penerima',
                'icon' => 'fas fa-calendar-check',
                'color' => '#2c3e50',
                'primary_key' => 'id_rencana_penerima',
            ],
            'saldo_awal' => [
                'model' => SaldoAwal::class,
                'name' => 'Saldo Awal',
                'icon' => 'fas fa-wallet',
                'color' => '#1abc9c',
                'primary_key' => 'id',
            ],
            'daftar_bank' => [
                'model' => DaftarBank::class,
                'name' => 'Daftar Bank',
                'icon' => 'fas fa-university',
                'color' => '#34495e',
                'primary_key' => 'id',
            ],
            'daftar_rekening' => [
                'model' => DaftarRekening::class,
                'name' => 'Daftar Rekening',
                'icon' => 'fas fa-credit-card',
                'color' => '#7f8c8d',
                'primary_key' => 'id',
            ],
        ];
    }

    /**
     * Dashboard utama programmer — statistik jumlah data per tabel.
     */
    public function index()
    {
        $tableMap = $this->getTableMap();
        $stats = [];
        $keywordReferences = $this->getKeywordReferences();

        foreach ($tableMap as $key => $info) {
            try {
                $count = $info['model']::count();
            } catch (\Exception $e) {
                $count = 0;
            }
            $stats[$key] = [
                'name' => $info['name'],
                'icon' => $info['icon'],
                'color' => $info['color'],
                'count' => $count,
            ];
        }

        $currentYear = (int) date('Y');
        $availableYears = Cashflow::whereNotNull('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn($t) => (int) $t)
            ->all();

        if (empty($availableYears)) {
            $availableYears = [$currentYear];
        } elseif (!in_array($currentYear, $availableYears, true)) {
            array_unshift($availableYears, $currentYear);
        }

        $selectedLockYear = (int) request('lock_tahun', $availableYears[0]);
        $cashflowLocks = CashflowLock::getStatusForYear($selectedLockYear);

        return view('cash_bank.programmer', compact(
            'stats',
            'tableMap',
            'keywordReferences',
            'availableYears',
            'selectedLockYear',
            'cashflowLocks'
        ));
    }

    /**
     * AJAX endpoint: load data per tabel untuk DataTables.
     */
    public function getData(Request $request, $table)
    {
        $tableMap = $this->getTableMap();

        if (!isset($tableMap[$table])) {
            return response()->json(['error' => 'Tabel tidak ditemukan'], 404);
        }

        $info = $tableMap[$table];
        $model = new $info['model'];
        $query = $model->newQuery();

        // Search
        if ($request->has('search') && $request->search['value']) {
            $searchValue = $request->search['value'];
            $columns = \Schema::getColumnListing($model->getTable());
            $query->where(function ($q) use ($columns, $searchValue) {
                foreach ($columns as $col) {
                    $q->orWhere($col, 'LIKE', "%{$searchValue}%");
                }
            });
        }

        $totalCount = $info['model']::count();
        $filteredCount = $query->count();

        // Order
        if ($request->has('order') && count($request->order) > 0) {
            $columns = \Schema::getColumnListing($model->getTable());
            $orderCol = $request->order[0]['column'] ?? 0;
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            if (isset($columns[$orderCol])) {
                $query->orderBy($columns[$orderCol], $orderDir);
            }
        } else {
            $query->orderBy($info['primary_key'], 'desc');
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        $data = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $data,
            'columns' => \Schema::getColumnListing($model->getTable()),
            'primaryKey' => $info['primary_key'],
        ]);
    }

    /**
     * Hapus single record.
     */
    public function deleteRecord(Request $request, $table, $id)
    {
        $tableMap = $this->getTableMap();

        if (!isset($tableMap[$table])) {
            return response()->json(['error' => 'Tabel tidak ditemukan'], 404);
        }

        $info = $tableMap[$table];
        $model = $info['model']::where($info['primary_key'], $id)->first();

        if (!$model) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $model->delete();

        return response()->json([
            'success' => true,
            'message' => "1 data berhasil dihapus dari {$info['name']}",
            'newCount' => $info['model']::count(),
        ]);
    }

    /**
     * Hapus banyak record sekaligus.
     */
    public function bulkDelete(Request $request, $table)
    {
        $tableMap = $this->getTableMap();

        if (!isset($tableMap[$table])) {
            return response()->json(['error' => 'Tabel tidak ditemukan'], 404);
        }

        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['error' => 'Tidak ada data yang dipilih'], 400);
        }

        $info = $tableMap[$table];
        $deleted = $info['model']::whereIn($info['primary_key'], $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} data berhasil dihapus dari {$info['name']}",
            'newCount' => $info['model']::count(),
        ]);
    }

    /**
     * Hapus semua data di satu tabel (TRUNCATE).
     */
    public function truncateTable(Request $request, $table)
    {
        $tableMap = $this->getTableMap();

        if (!isset($tableMap[$table])) {
            return response()->json(['error' => 'Tabel tidak ditemukan'], 404);
        }

        $info = $tableMap[$table];
        $modelInstance = new $info['model'];
        $tableName = $modelInstance->getTable();

        // Disable foreign key checks temporarily for truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table($tableName)->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return response()->json([
            'success' => true,
            'message' => "Semua data di tabel {$info['name']} berhasil dihapus",
            'newCount' => 0,
        ]);
    }

    public function keywordPreview(Request $request)
    {
        $payload = $this->validateKeywordPayload($request);
        $target = $payload['target'];

        $matchedQuery = $this->buildKeywordMatchQuery($payload['keywords'], $payload['match_mode']);
        $changedQuery = $this->buildNeedsKeywordUpdateQuery(clone $matchedQuery, $target);

        $samples = (clone $matchedQuery)
            ->leftJoin('kategori_kriteria as kk', 'kk.id_kategori_kriteria', '=', 'bank_keluars.id_kategori_kriteria')
            ->leftJoin('sub_kriteria as sk', 'sk.id_sub_kriteria', '=', 'bank_keluars.id_sub_kriteria')
            ->leftJoin('item_sub_kriteria as isk', 'isk.id_item_sub_kriteria', '=', 'bank_keluars.id_item_sub_kriteria')
            ->leftJoin('jenis_pembayarans as jp', 'jp.id_jenis_pembayaran', '=', 'bank_keluars.id_jenis_pembayaran')
            ->select(
                'bank_keluars.id_bank_keluar',
                'bank_keluars.agenda_tahun',
                'bank_keluars.uraian',
                'kk.nama_kriteria',
                'sk.nama_sub_kriteria',
                'isk.nama_item_sub_kriteria',
                'jp.nama_jenis_pembayaran'
            )
            ->orderBy('bank_keluars.id_bank_keluar')
            ->limit(10)
            ->get();

        return response()->json([
            'matched' => (clone $matchedQuery)->count(),
            'will_update' => $changedQuery->count(),
            'samples' => $samples,
        ]);
    }

    public function keywordUpdate(Request $request)
    {
        $payload = $this->validateKeywordPayload($request);
        $target = $payload['target'];

        $matchedQuery = $this->buildKeywordMatchQuery($payload['keywords'], $payload['match_mode']);
        $ids = $this->buildNeedsKeywordUpdateQuery(clone $matchedQuery, $target)
            ->pluck('id_bank_keluar')
            ->all();

        if (empty($ids)) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada data yang perlu diubah.',
                'matched' => (clone $matchedQuery)->count(),
                'updated' => 0,
                'backup_table' => null,
            ]);
        }

        $backupTable = 'bank_keluars_keyword_backup_' . now()->format('Ymd_His') . '_' . bin2hex(random_bytes(3));
        DB::statement("CREATE TABLE {$backupTable} LIKE bank_keluars");

        $columns = Schema::getColumnListing('bank_keluars');
        foreach (array_chunk($ids, 500) as $chunk) {
            DB::table($backupTable)->insertUsing(
                $columns,
                DB::table('bank_keluars')->select($columns)->whereIn('id_bank_keluar', $chunk)
            );
        }

        $updated = 0;
        DB::beginTransaction();
        try {
            foreach (array_chunk($ids, 500) as $chunk) {
                $updated += DB::table('bank_keluars')
                    ->whereIn('id_bank_keluar', $chunk)
                    ->update([
                        'id_kategori_kriteria' => $target['id_kategori_kriteria'],
                        'id_sub_kriteria' => $target['id_sub_kriteria'],
                        'id_item_sub_kriteria' => $target['id_item_sub_kriteria'],
                        'id_jenis_pembayaran' => $target['id_jenis_pembayaran'],
                        'updated_at' => now(),
                    ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} data Bank Keluar berhasil diubah.",
            'matched' => (clone $matchedQuery)->count(),
            'updated' => $updated,
            'backup_table' => $backupTable,
        ]);
    }

    private function validateKeywordPayload(Request $request): array
    {
        $validated = $request->validate([
            'keywords' => 'required|string',
            'match_mode' => 'required|in:all,any',
            'id_kategori_kriteria' => 'required|exists:kategori_kriteria,id_kategori_kriteria',
            'id_sub_kriteria' => 'required|exists:sub_kriteria,id_sub_kriteria',
            'id_item_sub_kriteria' => 'required|exists:item_sub_kriteria,id_item_sub_kriteria',
            'id_jenis_pembayaran' => 'required|exists:jenis_pembayarans,id_jenis_pembayaran',
        ]);

        $keywords = $this->parseKeywordInput($validated['keywords']);
        if (empty($keywords)) {
            abort(422, 'Masukkan minimal satu kata kunci.');
        }

        $subExists = DB::table('sub_kriteria')
            ->where('id_sub_kriteria', $validated['id_sub_kriteria'])
            ->where('id_kategori_kriteria', $validated['id_kategori_kriteria'])
            ->exists();

        $itemExists = DB::table('item_sub_kriteria')
            ->where('id_item_sub_kriteria', $validated['id_item_sub_kriteria'])
            ->where('id_sub_kriteria', $validated['id_sub_kriteria'])
            ->exists();

        if (!$subExists || !$itemExists) {
            abort(422, 'Kombinasi kategori, sub kategori, dan item sub kategori tidak valid.');
        }

        return [
            'keywords' => $keywords,
            'match_mode' => $validated['match_mode'],
            'target' => [
                'id_kategori_kriteria' => (int) $validated['id_kategori_kriteria'],
                'id_sub_kriteria' => (int) $validated['id_sub_kriteria'],
                'id_item_sub_kriteria' => (int) $validated['id_item_sub_kriteria'],
                'id_jenis_pembayaran' => (int) $validated['id_jenis_pembayaran'],
            ],
        ];
    }

    private function parseKeywordInput(string $input): array
    {
        return collect(preg_split('/[\r\n,;]+/', $input))
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->unique(fn($value) => mb_strtolower($value))
            ->values()
            ->all();
    }

    private function buildKeywordMatchQuery(array $keywords, string $matchMode)
    {
        $query = DB::table('bank_keluars');

        if ($matchMode === 'all') {
            foreach ($keywords as $keyword) {
                $query->whereRaw('LOWER(bank_keluars.uraian) LIKE ?', ['%' . mb_strtolower($keyword) . '%']);
            }

            return $query;
        }

        return $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhereRaw('LOWER(bank_keluars.uraian) LIKE ?', ['%' . mb_strtolower($keyword) . '%']);
            }
        });
    }

    private function buildNeedsKeywordUpdateQuery($query, array $target)
    {
        return $query->where(function ($q) use ($target) {
            $q->whereRaw('COALESCE(id_kategori_kriteria, 0) <> ?', [$target['id_kategori_kriteria']])
                ->orWhereRaw('COALESCE(id_sub_kriteria, 0) <> ?', [$target['id_sub_kriteria']])
                ->orWhereRaw('COALESCE(id_item_sub_kriteria, 0) <> ?', [$target['id_item_sub_kriteria']])
                ->orWhereRaw('COALESCE(id_jenis_pembayaran, 0) <> ?', [$target['id_jenis_pembayaran']]);
        });
    }

    private function getKeywordReferences(): array
    {
        return [
            'kategori' => DB::table('kategori_kriteria')
                ->where('tipe', 'Keluar')
                ->orderBy('nama_kriteria')
                ->get(['id_kategori_kriteria', 'nama_kriteria']),
            'subByKategori' => DB::table('sub_kriteria')
                ->orderBy('nama_sub_kriteria')
                ->get(['id_sub_kriteria', 'id_kategori_kriteria', 'nama_sub_kriteria'])
                ->groupBy('id_kategori_kriteria'),
            'itemBySub' => DB::table('item_sub_kriteria')
                ->orderBy('nama_item_sub_kriteria')
                ->get(['id_item_sub_kriteria', 'id_sub_kriteria', 'nama_item_sub_kriteria'])
                ->groupBy('id_sub_kriteria'),
            'jenis' => DB::table('jenis_pembayarans')
                ->orderBy('nama_jenis_pembayaran')
                ->get(['id_jenis_pembayaran', 'nama_jenis_pembayaran']),
        ];
    }

    /**
     * AJAX endpoint: Ambil status lock bulan untuk tahun tertentu.
     */
    public function getCashflowLocks(Request $request)
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $data = CashflowLock::getStatusForYear($tahun);

        return response()->json([
            'success' => true,
            'tahun' => $tahun,
            'months' => $data,
        ]);
    }

    /**
     * AJAX endpoint: Toggle status lock satu bulan.
     */
    public function toggleCashflowLock(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|min:1|max:12',
            'is_locked' => 'required|boolean',
        ]);

        $tahun = (int) $validated['tahun'];
        $bulan = (int) $validated['bulan'];
        $isLocked = (bool) $validated['is_locked'];

        CashflowLock::setLock(
            $tahun,
            $bulan,
            $isLocked,
            $request->user()?->username
        );

        $data = CashflowLock::getStatusForYear($tahun);
        $monthNames = CashflowLock::getMonthNames();
        $monthName = $monthNames[$bulan] ?? "Bulan {$bulan}";
        $statusText = $isLocked ? 'dikunci (Locked)' : 'dibuka (Unlocked)';

        return response()->json([
            'success' => true,
            'message' => "Bulan {$monthName} {$tahun} berhasil {$statusText}.",
            'tahun' => $tahun,
            'bulan' => $bulan,
            'is_locked' => $isLocked,
            'months' => $data,
        ]);
    }

    /**
     * AJAX endpoint: Batch update lock bulan (Kunci Semua, Buka Semua, Kunci Jan-X, Custom).
     */
    public function batchUpdateCashflowLocks(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer',
            'action' => 'required|in:lock_all,unlock_all,lock_until,custom',
            'locked_months' => 'nullable|array',
            'locked_months.*' => 'integer|min:1|max:12',
            'until_month' => 'nullable|integer|min:1|max:12',
        ]);

        $tahun = (int) $validated['tahun'];
        $action = $validated['action'];
        $lockedMonths = [];

        if ($action === 'lock_all') {
            $lockedMonths = range(1, 12);
        } elseif ($action === 'unlock_all') {
            $lockedMonths = [];
        } elseif ($action === 'lock_until') {
            $until = (int) ($validated['until_month'] ?? 7);
            $lockedMonths = range(1, $until);
        } elseif ($action === 'custom') {
            $lockedMonths = array_map('intval', $validated['locked_months'] ?? []);
        }

        CashflowLock::batchSetLocks($tahun, $lockedMonths, $request->user()?->username);
        $data = CashflowLock::getStatusForYear($tahun);

        $monthNames = CashflowLock::getMonthNames();
        $msg = "Pengaturan Lock Bulan tahun {$tahun} berhasil diperbarui (" . count($lockedMonths) . " bulan terkunci).";

        return response()->json([
            'success' => true,
            'message' => $msg,
            'tahun' => $tahun,
            'locked_count' => count($lockedMonths),
            'unlocked_count' => 12 - count($lockedMonths),
            'months' => $data,
        ]);
    }

    /**
     * Import raw Cashflow.xlsx and Standarisasi Reffkey.
     */
    public function importCashflow(Request $request, CashflowImportService $service)
    {
        ini_set('max_execution_time', '600');
        ini_set('memory_limit', '1024M');

        try {
            $refCount = 0;
            $cfResult = null;

            // 1. Process Standarisasi Reffkey if file uploaded or exists in docs
            $refFile = null;
            if ($request->hasFile('file_standarisasi')) {
                $refFile = $request->file('file_standarisasi')->getPathname();
            } else {
                $defaultRef = base_path('../docs/Standarisasi Reffkey (1).xlsx');
                if (file_exists($defaultRef)) {
                    $refFile = $defaultRef;
                } elseif (file_exists(base_path('docs/Standarisasi Reffkey (1).xlsx'))) {
                    $refFile = base_path('docs/Standarisasi Reffkey (1).xlsx');
                }
            }

            if ($refFile && file_exists($refFile)) {
                $refCount = $service->importStandarisasiReffkey($refFile);
            }

            // 2. Process Cashflow.xlsx if file uploaded or exists in docs
            $cfFile = null;
            if ($request->hasFile('file_cashflow')) {
                $cfFile = $request->file('file_cashflow')->getPathname();
            } else {
                $defaultCf = base_path('../docs/Cashflow.xlsx');
                if (file_exists($defaultCf)) {
                    $cfFile = $defaultCf;
                } elseif (file_exists(base_path('docs/Cashflow.xlsx'))) {
                    $cfFile = base_path('docs/Cashflow.xlsx');
                }
            }

            if ($cfFile && file_exists($cfFile)) {
                $truncateUnlocked = $request->boolean('truncate_first', true);
                $cfResult = $service->importCashflowTransactions($cfFile, $truncateUnlocked);
            }

            $cfCount = $cfResult ? ($cfResult['inserted'] ?? 0) : 0;
            $skippedCount = $cfResult ? ($cfResult['skipped_locked'] ?? 0) : 0;
            $replacedPeriods = $cfResult ? ($cfResult['replaced_periods'] ?? []) : [];

            $monthNames = CashflowLock::getMonthNames();
            $replacedDesc = [];
            foreach ($replacedPeriods as $pKey) {
                $parts = explode('_', $pKey);
                if (count($parts) === 2) {
                    $y = $parts[0];
                    $m = (int) $parts[1];
                    $replacedDesc[] = ($monthNames[$m] ?? "Bln {$m}") . " {$y}";
                }
            }

            $msg = "Import berhasil! {$refCount} kode referensi standarisasi dan {$cfCount} baris transaksi cashflow berhasil diproses.";
            if (!empty($replacedDesc)) {
                $msg .= " Periode yang di-replace: " . implode(', ', $replacedDesc) . ".";
            }
            if ($skippedCount > 0) {
                $msg .= " Sebanyak {$skippedCount} baris data pada bulan yang TERKUNCI dilewati (data lama tetap aman terjaga).";
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            \Log::error('Import Cashflow Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Gagal import cashflow: ' . $e->getMessage());
        }
    }
}
