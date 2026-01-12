<?php

namespace App\Http\Controllers;

use App\Models\daftarSPP;
use Illuminate\Http\Request;
use App\Models\DokumenAgenda;
use Illuminate\Support\Facades\DB;

class daftarSPPController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        // ambil keyword
       $search = $request->query('search');

        // ambil filter status (opsional)
        $filterStatus = $request->status; // siap_bayar, belum_siap, sudah_dibayar

        // query dasar
        // $query = DokumenAgenda::select('*',
        //     DB::raw("CONCAT(nomor_agenda,'_',tahun) as agenda_tahun")
        // );
        $query = DB::connection('mysql_agenda_online')
        ->table('dokumens')
        ->select('*')
        ->orderBy('tanggal_masuk', 'asc');

        // search
        if ($search) {
            $query
                ->orWhere('nomor_spp', 'like', "%{$search}%")
                ->orWhere('dibayar_kepada', 'like', "%{$search}%")
                ->orWhere('nilai_rupiah', 'like', "%{$search}%")
                ->orWhere('tanggal_masuk', 'like', "%{$search}%")
                ->orWhere('tanggal_spk', 'like', "%{$search}%")
                ->orWhere('uraian_spp', 'like', "%{$search}%");
        }

        // filter status
        if ($filterStatus == "belum") {
            $query
            ->whereNotIn('status_pembayaran', [
                'siap_dibayar',
                'sudah_dibayar'
            ])->orderBy('tanggal_masuk', 'asc');
        } elseif ($filterStatus == "siap") {
            $query->where('status_pembayaran', 'siap_dibayar')
            ->orderBy('tanggal_masuk','asc');
        } elseif ($filterStatus == "sudah") {
            $query->where('status_pembayaran', 'sudah_dibayar')
            ->orderBy('tanggal_masuk','asc');
        }

        $data = $query->get();
        return view('cash_bank.daftarSPP', compact('data'));
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
