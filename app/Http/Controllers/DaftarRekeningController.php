<?php

namespace App\Http\Controllers;

use App\Models\daftarBank;
use Illuminate\Http\Request;
use App\Models\daftarRekening;

class daftarRekeningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // return view('cash_bank.saldo.daftarRekening');
        $search = $request->keyword;
        $data = daftarRekening::with('bank')
            ->when($search, function($q) use ($search) {
                $q->whereHas('bank', function($sub) use ($search) {
                    $sub->where('nama_bank', 'like', "%$search%");
                })->orWhere('nomor_rekening', 'like', "%$search%");
            })
            ->latest()
            ->get();
        return view('cash_bank.saldo.daftarRekening', [
            'data' => $data,
            'daftarBank'        => daftarBank::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('modal.tambahRekening');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'id_daftar_bank' => 'required|exists:daftarbanks,id_daftar_bank',
        'nomor_rekening' => 'required|numeric',
    ]);

        daftarRekening::create($validated);

        return redirect()->route('daftarRekening.index')
                        ->with('success', 'Data berhasil ditambahkan!');
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

    // Method untuk AJAX dropdown
    public function getBank($id)
    {
        return daftarBank::where('id_daftar_bank', $id)->get();
    }

    public function getRekeningByBank($id)
    {
        return daftarRekening::where('id_daftar_bank', $id)->get();
    }
}
