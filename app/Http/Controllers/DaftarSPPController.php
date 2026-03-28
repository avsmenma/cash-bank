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
     * Return server-rendered HTML grouped by month
     */
    public function dataGrouped(Request $request)
    {
        try {
            $tahun = $request->tahun ?? date('Y');
            $filterStatus = $request->status;

            $query = DB::connection('mysql_agenda_online')
                ->table('dokumens')
                ->select('*')
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

            $allData = $query->get();

            // Group by month
            $grouped = [];
            foreach ($allData as $row) {
                if ($row->tanggal_masuk) {
                    $bulan = (int) \Carbon\Carbon::parse($row->tanggal_masuk)->format('n');
                } else {
                    $bulan = 0;
                }
                $grouped[$bulan][] = $row;
            }

            ksort($grouped);
            if (isset($grouped[0])) {
                $noDateGroup = $grouped[0];
                unset($grouped[0]);
                $grouped[0] = $noDateGroup;
            }

            $bulanNames = [
                0 => 'TANPA TANGGAL',
                1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
                5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
                9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
            ];

            return view('cash_bank.dataSPP', compact('grouped', 'bulanNames', 'tahun'));
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
