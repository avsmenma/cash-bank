<?php

namespace App\Http\Controllers;


use App\Models\Penerima;
use Illuminate\Http\Request;
use App\Models\RencanaPenerima;
use App\Models\KategoriKriteria;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportExcelPenerima;
use App\Exports\ExportExcelRencanaPenerima;
use App\Exports\ExportExcelCashFlowPenerima;
use App\Exports\ExportExcelGabunganPenerima;
use Barryvdh\DomPDF\Facade\Pdf;

class penerimaController extends Controller
{
    public function index()
    {
        $result = [];
        return view('cash_bank.pembayaran.penerima', [
            'kategoriKriteria' => KategoriKriteria::where('tipe', 'penerima')->get(),
            'result' => $result
        ]);
    }
    public function datatable(Request $request)
    {
        $filterStatus = $request->status;

        $query = Penerima::with('kategori')
            ->orderBy('id_kategori_kriteria')
            ->orderBy('tanggal');

        if ($request->tahun) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->bulan) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        if ($request->kategori) {
            $query->where('id_kategori_kriteria', $request->kategori);
        }

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn(
                'kategori_kriteria',
                fn($r) =>
                $r->kategori->nama_kriteria ?? '-'
            )

            ->addColumn(
                'nilai_inc_ppn',
                fn($r) =>
                $r->nilai_inc_ppn ?? 0
            )
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="checkbox_ids" name="ids[]" value="' . $row->id_penerima . '">';
            })
            ->addColumn('aksi', function ($row) {
                $route = route('penerima.destroy', $row->id_penerima);
                $csrf = csrf_token();

                return '
                    <button class="btn btn-warning btn-sm" 
                        data-toggle="modal"
                        data-target="#editPenerima"
                        data-id="' . $row->id_penerima . '"
                        data-pembeli="' . $row->pembeli . '"
                        data-kontrak="' . $row->kontrak . '"
                        data-no_reg="' . $row->no_reg . '"
                        data-harga="' . $row->harga . '"
                        data-tanggal="' . $row->tanggal . '"
                        data-volume="' . $row->volume . '"
                        data-nilai="' . $row->nilai . '"
                        data-kategori="' . $row->id_kategori_kriteria . '"
                        data-ppn="' . $row->ppn . '"
                        data-potppn="' . $row->potppn . '"
                        data-nilai_inc_ppn="' . ($row->nilai_inc_ppn ?? 0) . '"
                        >Edit</button>

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
            ->editColumn('tanggal', function ($row) {
                return \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y');
            })
            ->rawColumns(['checkbox', 'aksi'])
            ->with([
                'groupColumn' => 'nama_kriteria'
            ])
            ->make(true);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'harga' => 'required|numeric',
            'volume' => 'required|numeric',
            'pembeli' => 'required|string',
            'kontrak' => 'nullable|string',
            'no_reg' => 'nullable|string',
            'id_kategori_kriteria' => 'required|exists:kategori_kriteria,id_kategori_kriteria',
            'nilai' => 'nullable|numeric',
            'ppn' => 'nullable|numeric',
            'potppn' => 'nullable|numeric',
            'nilai_inc_ppn' => 'nullable|numeric',
        ]);

        Penerima::create([
            'kontrak' => $validated['kontrak'] ?? null,
            'id_kategori_kriteria' => $validated['id_kategori_kriteria'],
            'pembeli' => $validated['pembeli'],
            'no_reg' => $validated['no_reg'] ?? null,
            'tanggal' => $validated['tanggal'],
            'volume' => $validated['volume'],
            'harga' => $validated['harga'],
            'nilai' => $validated['nilai'] ?? 0,
            'ppn' => $validated['ppn'] ?? 0,
            'potppn' => $validated['potppn'] ?? 0,
            'nilai_inc_ppn' => $validated['nilai_inc_ppn'] ?? 0,
        ]);

        return back()->with('success', 'Data berhasil disimpan');
    }

    public function importData(Request $request)
    {
        $request->validate([
            'id_kategori_kriteria' => 'required|exists:kategori_kriteria,id_kategori_kriteria',
            'rows' => 'required|array|min:1',
        ]);

        $kategori = $request->id_kategori_kriteria;
        $rows = $request->rows;
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                Penerima::create([
                    'id_kategori_kriteria' => $kategori,
                    'kontrak' => $row['kontrak'] ?? null,
                    'pembeli' => $row['pembeli'] ?? null,
                    'tanggal' => $row['tanggal'] ?? null,
                    'no_reg' => $row['no_reg'] ?? null,
                    'volume' => $row['volume'] ?? 0,
                    'harga' => $row['harga'] ?? 0,
                    'nilai' => $row['nilai'] ?? 0,
                    'ppn' => $row['ppn'] ?? 0,
                    'potppn' => $row['potppn'] ?? 0,
                    'nilai_inc_ppn' => $row['nilai_inc_ppn'] ?? 0,
                ]);
                $count++;
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimport $count data",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimport data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $penerima = Penerima::findOrFail($id);
        return view('cash_bank.modal.modalPenerima.editPenerima', compact('penerima'));
    }
    public function update(Request $request, string $id)
    {

        $penerima = Penerima::findOrFail($id);
        $penerima->update([
            'id_kategori_kriteria' => $request->id_kategori_kriteria,
            'kontrak' => $request->kontrak,
            'pembeli' => $request->pembeli,
            'tanggal' => $request->tanggal,
            'no_reg' => $request->no_reg,
            'volume' => $request->volume ?? 0,
            'harga' => $request->harga ?? 0,
            'nilai' => $request->nilai ?? 0,
            'ppn' => $request->ppn ?? 0,
            'potppn' => $request->potppn ?? 0,
            'nilai_inc_ppn' => $request->nilai_inc_ppn ?? 0,
        ]);

        return redirect()->route('penerima.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $data = Penerima::findOrFail($id);
        $data->delete();

        return redirect()->route('penerima.index')->with('success', 'Data berhasil dihapus');
    }

    public function deleteAll(Request $request)
    {
        $ids = $request->ids;
        Penerima::whereIn('id_penerima', $ids)->delete();

        return response()->json([
            'success' => 'Data berhasil dihapus!'
        ]);
    }

    public function cashFlow(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

        $data = Penerima::with('kategori')
            ->select(
                'id_kategori_kriteria',
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(nilai + ppn - potppn) as total')
            )
            ->whereYear('tanggal', $tahun)
            ->groupBy(
                'id_kategori_kriteria',
                DB::raw('MONTH(tanggal)')
            )
            ->orderBy('id_kategori_kriteria')
            ->orderBy(DB::raw('MONTH(tanggal)'))
            ->get();

        $result = [];

        foreach ($data as $row) {
            $kategori = $row->kategori->nama_kriteria ?? '-';
            $bulan = (int) $row->bulan;

            if (!isset($result[$kategori])) {
                $result[$kategori] = array_fill(1, 12, 0);
            }

            // TOTAL NILAI INC PPN
            $result[$kategori][$bulan] = $row->total;
        }

        return view(
            'cash_bank.pembayaran.cashFlowPenerima',
            compact('result', 'tahun')
        );
    }

    public function rencana(Request $request)
    {
        try {
            $tahun = $request->tahun ?? date('Y');

            $kategori = KategoriKriteria::where('tipe', 'Penerima')->get();

            $data = RencanaPenerima::where('tahun', $tahun)
                ->get()
                ->keyBy('id_kategori_kriteria');

            // Debugging
            \Log::info('Rencana Data:', [
                'tahun' => $tahun,
                'kategori_count' => $kategori->count(),
                'data_count' => $data->count()
            ]);

            return view('cash_bank.pembayaran.rencanaPenerima', compact('kategori', 'data', 'tahun'));

        } catch (\Exception $e) {
            \Log::error('Error in rencana(): ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function save(Request $request)
    {
        try {
            // Cari data berdasarkan kategori dan tahun (bukan id_rencana)
            $rencana = RencanaPenerima::firstOrNew([
                'id_kategori_kriteria' => $request->kategori,
                'tahun' => $request->tahun,
            ]);

            // Update hanya bulan yang diedit, bulan lain tetap dipertahankan
            $rencana->{$request->bulan} = $request->nilai;
            $rencana->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'id' => $rencana->id_rencana_penerima
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gabungan(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $bulanDari = $request->bulan_dari ?? 1; // filter bulan dari
        $bulanSampai = $request->bulan_sampai ?? 12; // filter bulan sampai

        $bulanList = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        // Filter bulan sesuai range yang dipilih
        $bulanListFiltered = array_filter($bulanList, function ($noBulan) use ($bulanDari, $bulanSampai) {
            return $noBulan >= $bulanDari && $noBulan <= $bulanSampai;
        });

        $kategori = KategoriKriteria::where('tipe', 'penerima')->get();
        $data = [];
        $bulanAktif = []; // untuk tracking bulan yang punya data

        foreach ($kategori as $k) {
            foreach ($bulanListFiltered as $namaBulan => $noBulan) {

                // RENCANA
                $rencana = DB::table('rencana_penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->where('tahun', $tahun)
                    ->sum($namaBulan);

                // REALISASI (DARI TABEL penerimas)
                $nilai = DB::table('penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $noBulan)
                    ->sum('nilai');
                $ppn = DB::table('penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $noBulan)
                    ->sum('ppn');
                $potppn = DB::table('penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $noBulan)
                    ->sum('potppn');
                $realisasi = $nilai + $ppn - $potppn;

                // Tandai bulan yang punya data
                if ($rencana > 0 || $realisasi > 0) {
                    $bulanAktif[$namaBulan] = true;
                }

                $selisih = $realisasi - $rencana;
                $persen = $rencana > 0 ? ($realisasi / $rencana) * 100 : 0;

                $data[$k->nama_kriteria][$namaBulan] = [
                    'rencana' => $rencana,
                    'realisasi' => $realisasi,
                    'selisih' => $selisih,
                    'persen' => $persen
                ];
            }
        }

        // Filter hanya bulan yang punya data
        $bulanListFiltered = array_filter($bulanListFiltered, function ($namaBulan) use ($bulanAktif) {
            return isset($bulanAktif[$namaBulan]);
        }, ARRAY_FILTER_USE_KEY);

        return view('cash_bank.pembayaran.cashFlowGabunganPenerima', compact('data', 'bulanListFiltered', 'tahun', 'bulanDari', 'bulanSampai'));
    }

    public function export_excel(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        return Excel::download(new ExportExcelPenerima($tahun), 'penerima-' . $tahun . '.xlsx');
    }

    public function export_excel_rencana(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        return Excel::download(new ExportExcelRencanaPenerima($tahun), 'rencana-penerima-' . $tahun . '.xlsx');
    }

    public function export_pdf_rencana(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

        $kategori = KategoriKriteria::where('tipe', 'Penerima')->get();
        $data = RencanaPenerima::where('tahun', $tahun)
            ->get()
            ->keyBy('id_kategori_kriteria');

        $pdf = Pdf::loadView('cash_bank.exportPDF.pdfRencanaPenerima', [
            'kategori' => $kategori,
            'data' => $data,
            'tahun' => $tahun
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('rencana-penerima-' . $tahun . '.pdf');
    }

    public function export_excel_cashFlow(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        return Excel::download(new ExportExcelCashFlowPenerima($tahun), 'cashflow-realisasi-' . $tahun . '.xlsx');
    }

    public function export_pdf_cashFlow(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

        $data = Penerima::with('kategori')
            ->select(
                'id_kategori_kriteria',
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(nilai + ppn - potppn) as total')
            )
            ->whereYear('tanggal', $tahun)
            ->groupBy(
                'id_kategori_kriteria',
                DB::raw('MONTH(tanggal)')
            )
            ->orderBy('id_kategori_kriteria')
            ->orderBy(DB::raw('MONTH(tanggal)'))
            ->get();

        $result = [];
        $kategoriList = [];

        foreach ($data as $row) {
            $kategori = $row->kategori->nama_kriteria ?? '-';
            $bulan = (int) $row->bulan;

            if (!in_array($kategori, $kategoriList)) {
                $kategoriList[] = $kategori;
            }

            if (!isset($result[$kategori])) {
                $result[$kategori] = array_fill(1, 12, 0);
            }

            $result[$kategori][$bulan] = $row->total;
        }

        $pdf = Pdf::loadView('cash_bank.exportPDF.pdfCashFlowPenerima', [
            'result' => $result,
            'tahun' => $tahun
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('cashflow-realisasi-' . $tahun . '.pdf');
    }

    public function export_excel_gabungan(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $bulanDari = $request->bulan_dari ?? 1;
        $bulanSampai = $request->bulan_sampai ?? 12;

        return Excel::download(new ExportExcelGabunganPenerima($tahun, $bulanDari, $bulanSampai), 'cashflow-gabungan-' . $tahun . '.xlsx');
    }

    public function export_pdf_gabungan(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $bulanDari = $request->bulan_dari ?? 1;
        $bulanSampai = $request->bulan_sampai ?? 12;

        $bulanList = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        $bulanListFiltered = array_filter($bulanList, function ($noBulan) use ($bulanDari, $bulanSampai) {
            return $noBulan >= $bulanDari && $noBulan <= $bulanSampai;
        });

        $kategori = KategoriKriteria::where('tipe', 'penerima')->get();
        $data = [];
        $bulanAktif = [];

        foreach ($kategori as $k) {
            foreach ($bulanListFiltered as $namaBulan => $noBulan) {
                $rencana = DB::table('rencana_penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->where('tahun', $tahun)
                    ->sum($namaBulan);

                $nilai = DB::table('penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $noBulan)
                    ->sum('nilai');
                $ppn = DB::table('penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $noBulan)
                    ->sum('ppn');
                $potppn = DB::table('penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $noBulan)
                    ->sum('potppn');
                $realisasi = $nilai + $ppn - $potppn;

                if ($rencana > 0 || $realisasi > 0) {
                    $bulanAktif[$namaBulan] = true;
                }

                $selisih = $realisasi - $rencana;
                $persen = $rencana > 0 ? ($realisasi / $rencana) * 100 : 0;

                $data[$k->nama_kriteria][$namaBulan] = [
                    'rencana' => $rencana,
                    'realisasi' => $realisasi,
                    'selisih' => $selisih,
                    'persen' => $persen
                ];
            }
        }

        $bulanListFiltered = array_filter($bulanListFiltered, function ($namaBulan) use ($bulanAktif) {
            return isset($bulanAktif[$namaBulan]);
        }, ARRAY_FILTER_USE_KEY);

        $pdf = Pdf::loadView('cash_bank.exportPDF.pdfGabunganPenerima', [
            'data' => $data,
            'bulanListFiltered' => $bulanListFiltered,
            'tahun' => $tahun,
            'bulanDari' => $bulanDari,
            'bulanSampai' => $bulanSampai
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('cashflow-gabungan-' . $tahun . '.pdf');
    }
}
