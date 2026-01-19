<?php

namespace App\Http\Controllers;

use App\Models\BankTujuan;
use App\Models\daftarBank;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class daftarBankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('cash_bank.saldo.daftarBank');
    }
    public function datatable(Request $request)
    {
        $filterStatus = $request->status;

        $query = BankTujuan::all();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                $route = route('daftarBank.destroy', $row->id_bank_tujuan);
                $csrf = csrf_token();

                return '
                    <button type="button"
                        class="btn btn-warning btn-sm"
                        data-toggle="modal"
                        data-target="#editBankTujuan"
                        data-id="'.$row->id_bank_tujuan.'"
                        data-nama="'.$row->nama_tujuan.'">
                        Edit
                    </button>

                    <form action="'.$route.'" method="POST" style="display:inline;">
                        <input type="hidden" name="_token" value="'.$csrf.'">
                        <input type="hidden" name="_method" value="DELETE">

                        <button type="submit"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm(\'Yakin ingin menghapus?\')">
                            <i class="bi bi-trash"></i>Hapus
                        </button>
                    </form>
                ';
            })
            ->rawColumns(['aksi'])
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
