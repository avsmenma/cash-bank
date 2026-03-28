<?php

namespace App\Http\Controllers;

use App\Models\daftarSPP;
use Illuminate\Http\Request;
use App\Models\DokumenAgenda;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class daftarSPPController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        return view('cash_bank.daftarSPP');
    }
    public function datatable(Request $request)
    {
        $filterStatus = $request->status;

        $query = DB::connection('mysql_agenda_online')
            ->table('dokumens')
            ->select('*')
            ->orderBY('tanggal_masuk');

        if ($filterStatus === 'belum') {
            $query->whereNotIn('status_pembayaran', [
                'siap_dibayar',
                'sudah_dibayar'
            ]);
        } 
        elseif ($filterStatus === 'siap') {
            $query->where('status_pembayaran', 'siap_dibayar');
        } 
        elseif ($filterStatus === 'sudah') {
            $query->where('status_pembayaran', 'sudah_dibayar');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Return server-rendered HTML with pagination
     */
    public function dataGrouped(Request $request)
    {
        try {
            $tahun = $request->tahun ?? date('Y');
            $filterStatus = $request->status;
            $perPage = (int) ($request->per_page ?? 10);
            $page = (int) ($request->page ?? 1);
            $search = $request->search;

            $query = DB::connection('mysql_agenda_online')
                ->table('dokumens')
                ->whereYear('tanggal_masuk', $tahun)
                ->orderBy('tanggal_masuk');

            if ($filterStatus === 'belum') {
                $query->whereNotIn('status_pembayaran', ['siap_dibayar', 'sudah_dibayar']);
            } elseif ($filterStatus === 'siap') {
                $query->where('status_pembayaran', 'siap_dibayar');
            } elseif ($filterStatus === 'sudah') {
                $query->where('status_pembayaran', 'sudah_dibayar');
            }

            if ($request->filled('bulan_dari') && $request->filled('bulan_sampai')) {
                $query->whereMonth('tanggal_masuk', '>=', $request->bulan_dari)
                      ->whereMonth('tanggal_masuk', '<=', $request->bulan_sampai);
            } elseif ($request->filled('bulan_dari')) {
                $query->whereMonth('tanggal_masuk', '>=', $request->bulan_dari);
            } elseif ($request->filled('bulan_sampai')) {
                $query->whereMonth('tanggal_masuk', '<=', $request->bulan_sampai);
            }

            // Search across multiple columns
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_agenda', 'like', "%{$search}%")
                      ->orWhere('nomor_spp', 'like', "%{$search}%")
                      ->orWhere('uraian_spp', 'like', "%{$search}%")
                      ->orWhere('dibayar_kepada', 'like', "%{$search}%")
                      ->orWhere('current_handler', 'like', "%{$search}%");
                });
            }

            // Get total count before pagination (clone to avoid modifying query)
            $totalRecords = (clone $query)->count();

            // Handle "show all"
            if ($perPage === -1) {
                $allData = $query->get();
                $totalPages = 1;
                $page = 1;
            } else {
                $totalPages = max(1, (int) ceil($totalRecords / $perPage));
                $page = min($page, $totalPages);
                $offset = ($page - 1) * $perPage;
                $allData = $query->skip($offset)->take($perPage)->get();
            }

            // Calculate global start index for numbering
            $startIndex = ($perPage === -1) ? 0 : ($page - 1) * $perPage;

            $bulanNames = [
                0 => 'TANPA TANGGAL',
                1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
                5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
                9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
            ];

            $pagination = [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'per_page' => $perPage,
                'total_records' => $totalRecords,
                'from' => $totalRecords > 0 ? $startIndex + 1 : 0,
                'to' => $totalRecords > 0 ? min($startIndex + ($perPage === -1 ? $totalRecords : $perPage), $totalRecords) : 0,
            ];

            return view('cash_bank.dataSPP', compact('allData', 'bulanNames', 'tahun', 'pagination', 'startIndex'));
        } catch (\Exception $e) {
            \Log::error('SPP dataGrouped error: ' . $e->getMessage());
            return response('<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
