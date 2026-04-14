<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
use App\Models\Dokumen;

class ProgrammerController extends Controller
{
    /**
     * Map of allowed table keys to their model classes and display names.
     */
    private function getTableMap(): array
    {
        return [
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
            'dokumen' => [
                'model' => Dokumen::class,
                'name' => 'Dokumen',
                'icon' => 'fas fa-file-alt',
                'color' => '#c0392b',
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

        foreach ($tableMap as $key => $info) {
            $stats[$key] = [
                'name' => $info['name'],
                'icon' => $info['icon'],
                'color' => $info['color'],
                'count' => $info['model']::count(),
            ];
        }

        return view('cash_bank.programmer', compact('stats', 'tableMap'));
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
}
