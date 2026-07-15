<?php

namespace App\Http\Controllers;

use App\Models\BankTujuan;
use App\Models\BankMasuk;
use App\Models\BankKeluar;
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
            ->addColumn('saldo_akhir', function ($row) {
                $totalMasuk = BankMasuk::where('id_bank_tujuan', $row->id_bank_tujuan)->sum('debet');
                $totalKeluar = BankKeluar::where('id_bank_tujuan', $row->id_bank_tujuan)->sum('kredit');
                $saldo = (float) $totalMasuk - (float) $totalKeluar;
                return 'Rp ' . number_format($saldo, 0, ',', '.');
            })
            ->addColumn('sap', function ($row) {
                $value = e($this->formatSapValue($row->sap));

                return '
                    <input type="text"
                        class="form-control form-control-sm sap-inline-input"
                        value="' . $value . '"
                        data-id="' . $row->id_bank_tujuan . '"
                        data-original="' . $value . '"
                        placeholder="Input SAP">
                ';
            })
            ->addColumn('aksi', function ($row) {
                $route = route('daftarBank.destroy', $row->id_bank_tujuan);
                $detailRoute = route('daftarBank.detail', $row->id_bank_tujuan);
                $csrf = csrf_token();

                return '
                    <a href="' . $detailRoute . '" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>

                    <button type="button"
                        class="btn btn-warning btn-sm"
                        data-toggle="modal"
                        data-target="#editBankTujuan"
                        data-id="' . $row->id_bank_tujuan . '"
                        data-nama="' . $row->nama_tujuan . '">
                        Edit
                    </button>

                    <form action="' . $route . '" method="POST" style="display:inline;">
                        <input type="hidden" name="_token" value="' . $csrf . '">
                        <input type="hidden" name="_method" value="DELETE">

                        <button type="submit"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm(\'Yakin ingin menghapus?\')">
                            <i class="bi bi-trash"></i>Hapus
                        </button>
                    </form>
                ';
            })
            ->rawColumns(['sap', 'aksi'])
            ->make(true);
    }

    public function updateSap(Request $request, $id)
    {
        $validated = $request->validate([
            'sap' => 'nullable|string|max:255',
        ]);

        $bank = BankTujuan::findOrFail($id);
        $bank->sap = $this->formatSapValue($validated['sap'] ?? null);
        $bank->save();

        return response()->json([
            'success' => true,
            'sap' => $bank->sap,
            'message' => 'SAP berhasil disimpan.',
        ]);
    }

    private function formatSapValue($value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        $digits = ltrim($digits, '0');
        $digits = $digits === '' ? '0' : $digits;

        return preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $digits);
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

    /**
     * Show detail ledger for a specific Virtual Account.
     */
    public function showDetail($id)
    {
        $va = BankTujuan::findOrFail($id);

        // Get Bank Masuk transactions for this VA
        $masuk = BankMasuk::where('id_bank_tujuan', $id)
            ->select('tanggal', 'penerima', 'uraian', 'debet', 'kredit', 'created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'created_at' => $item->created_at,
                    'penerima' => $item->penerima,
                    'uraian' => $item->uraian,
                    'debet' => (float) $item->debet,
                    'kredit' => 0,
                    'tipe' => 'masuk',
                ];
            });

        // Get Bank Keluar transactions for this VA
        $keluar = BankKeluar::where('id_bank_tujuan', $id)
            ->select('tanggal', 'penerima', 'uraian', 'debet', 'kredit', 'created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'created_at' => $item->created_at,
                    'penerima' => $item->penerima,
                    'uraian' => $item->uraian,
                    'debet' => 0,
                    'kredit' => (float) $item->kredit,
                    'tipe' => 'keluar',
                ];
            });

        // Merge and sort by date ascending, then by created_at for same-date entries
        $transactions = $masuk->toBase()->merge($keluar->toBase())
            ->sortBy(['tanggal', 'created_at'])
            ->values();

        // Compute running total (saldo akhir)
        $saldo = 0;
        $transactions = $transactions->map(function ($item) use (&$saldo) {
            $saldo = $saldo + $item['debet'] - $item['kredit'];
            $item['saldo'] = $saldo;
            return $item;
        });

        // Daftar tahun untuk dropdown filter (samakan dengan halaman VA Dashboard)
        $years = $transactions->pluck('tanggal')
            ->filter()
            ->map(fn ($t) => substr((string) $t, 0, 4))
            ->unique()
            ->sortDesc()
            ->values();

        return view('cash_bank.saldo.detailVA', compact('va', 'transactions', 'years'));
    }
}

