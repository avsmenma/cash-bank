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
