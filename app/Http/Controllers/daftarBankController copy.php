<?php

namespace App\Http\Controllers;

use App\Models\BankTujuan;
use App\Models\daftarBank;
use Illuminate\Http\Request;

class daftarBankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $search = $request->keyword;
        // $data = BankTujuan::when($search, function($query, $search) {
        //     return $query->where('nama_tujuan', 'like', "%{$search}%");
        // })->get();
        return view('cash_bank.saldo.daftarBank');
    }
    public function datatable(Request $request)
    {
        $filterStatus = $request->status;

        $data = BankTujuan::all();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                return 
                '
                <button class="btn btn-warning btn-sm">Edit</button>
                <button class="btn btn-warning btn-sm">Edit</button>
                ';
            })
            ->rawColumns(['checkbox','aksi'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        return view('modal.tambahBank');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'nama_tujuan' => 'required',
    ]);

        BankTujuan::create($validated);
        return redirect()->route('daftarBank.index')->with('success', 'Data berhasil ditambahkan!');
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
        $data = BankTujuan::findOrFail($id);
        return view('modal.editBankTujuan', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_tujuan' => 'required'
        ]);

        BankTujuan::where('id_bank_tujuan', $id)->update([
            'nama_tujuan' => $request->nama_tujuan
        ]);

        return redirect()->route('daftarBank.index')
            ->with('success', 'Data berhasil diperbarui');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = BankTujuan::findOrFail($id);
        $data->delete();

        return redirect()->route('daftarBank.index')->with('success', 'Data berhasil dihapus');
    }
}
