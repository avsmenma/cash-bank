<?php

namespace App\Http\Controllers;

use App\Models\saldoAwal;
use App\Models\daftarBank;
use Illuminate\Http\Request;
use App\Models\daftarRekening;

class saldoAwalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // return view('cash_bank.saldo.daftarRekening');
        $search = $request->keyword;
        $data = saldoAwal::with(['bank', 'rekening'])
        ->when($search, function($q) use ($search) {
            $q->whereHas('bank', function($namaBank) use ($search) {
                $namaBank->where('nama_bank', 'like', "%$search%");
            })
            ->orWhereHas('rekening', function($rek) use ($search) {
                $rek->where('nomor_rekening', 'like', "%$search%");
            })
            ->orWhere('saldo_awal', 'like', "%$search%");
        })
        ->latest()
        ->get();
        return view('cash_bank.saldo.daftarSaldoAwal', [
            'data' => $data,
            'daftarBank'        => daftarBank::all(),
            'daftarRekening'        => daftarRekening::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('modal.saldoAwal');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('STORE SALDO AWAL REQ', $request->all());

        $validated = $request->validate([
            'id_daftar_bank' => 'required|exists:daftarbanks,id_daftar_bank',
            'id_rekening'    => 'required|exists:daftarrekenings,id_rekening',
            'saldo_awal'     => 'required',
        ]);

        // bersihkan format rupiah (jika ada)
        $validated['saldo_awal'] = (int) str_replace(['.',','], ['', '.'], $validated['saldo_awal']);

        SaldoAwal::create($validated);

        return redirect()->back()->with('success', 'Saldo awal tersimpan');
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
