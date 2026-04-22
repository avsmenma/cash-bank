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
            'kategoriKriteria' => KategoriKriteria::where('tipe', 'Penerima')->get(),
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

        if ($request->bulan_dari && $request->bulan_sampai) {
            $query->whereMonth('tanggal', '>=', $request->bulan_dari)
                  ->whereMonth('tanggal', '<=', $request->bulan_sampai);
        } elseif ($request->bulan_dari) {
            $query->whereMonth('tanggal', '>=', $request->bulan_dari);
        } elseif ($request->bulan_sampai) {
            $query->whereMonth('tanggal', '<=', $request->bulan_sampai);
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
            ->addColumn('bulan_num', function ($row) {
                return (int) \Carbon\Carbon::parse($row->tanggal)->format('n');
            })
            ->rawColumns(['checkbox', 'aksi'])
            ->with([
                'groupColumn' => 'nama_kriteria'
            ])
            ->make(true);
    }

    /**
     * Return server-rendered HTML grouped by month → kategori
     */
    public function dataGrouped(Request $request)
    {
        try {
            $tahun = $request->tahun ?? date('Y');

            $query = Penerima::with('kategori')
                ->where(function ($q) use ($tahun) {
                    $q->whereYear('tanggal', $tahun)
                      ->orWhereNull('tanggal')
                      ->orWhere('tanggal', '0000-00-00');
                })
                ->orderByRaw('(tanggal IS NULL OR tanggal = ?) ASC, tanggal ASC', ['0000-00-00'])
                ->orderBy('id_kategori_kriteria');

            if ($request->filled('kategori')) {
                $query->where('id_kategori_kriteria', $request->kategori);
            }

            if ($request->filled('bulan_dari') && $request->filled('bulan_sampai')) {
                $query->where(function ($q) use ($request) {
                    $q->where(function ($q2) use ($request) {
                        $q2->whereMonth('tanggal', '>=', $request->bulan_dari)
                           ->whereMonth('tanggal', '<=', $request->bulan_sampai);
                    })->orWhereNull('tanggal')->orWhere('tanggal', '0000-00-00');
                });
            } elseif ($request->filled('bulan_dari')) {
                $query->where(function ($q) use ($request) {
                    $q->whereMonth('tanggal', '>=', $request->bulan_dari)
                      ->orWhereNull('tanggal')->orWhere('tanggal', '0000-00-00');
                });
            } elseif ($request->filled('bulan_sampai')) {
                $query->where(function ($q) use ($request) {
                    $q->whereMonth('tanggal', '<=', $request->bulan_sampai)
                      ->orWhereNull('tanggal')->orWhere('tanggal', '0000-00-00');
                });
            }

            $allData = $query->get();

            // Group by month -> kategori (0 = no date)
            $grouped = [];
            foreach ($allData as $row) {
                if ($row->tanggal && $row->tanggal !== '0000-00-00') {
                    $bulan = (int) \Carbon\Carbon::parse($row->tanggal)->format('n');
                } else {
                    $bulan = 0; // special key for rows without date
                }
                $kategoriName = $row->kategori->nama_kriteria ?? '-';
                $grouped[$bulan][$kategoriName][] = $row;
            }

            // Sort by month (0 = no date will come first, move to end)
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

            return view('cash_bank.pembayaran.dataPenerima', compact('grouped', 'bulanNames', 'tahun'));
        } catch (\Exception $e) {
            \Log::error('dataGrouped error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response('<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>', 500);
        }
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date',
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
            'tanggal' => !empty($validated['tanggal']) ? $validated['tanggal'] : null,
            'volume' => $validated['volume'],
            'harga' => $validated['harga'],
            'nilai' => $validated['nilai'] ?? 0,
            'ppn' => $validated['ppn'] ?? 0,
            'potppn' => $validated['potppn'] ?? 0,
            'nilai_inc_ppn' => $validated['nilai_inc_ppn'] ?? 0,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => 'Data berhasil disimpan']);
        }

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

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui']);
        }

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

            // Query menggunakan YEAR() agar bisa filter berdasarkan tahun saja
            $data = RencanaPenerima::where('tahun', \$tahun)
                ->get()
                ->keyBy('id_kategori_kriteria');

            return view('cash_bank.pembayaran.rencanaPenerima', compact('kategori', 'data', 'tahun'));

        } catch (\Exception $e) {
            \Log::error('Error in rencana(): ' . $e->getMessage());
            return response('<div class="alert alert-danger">Terjadi kesalahan: ' . $e->getMessage() . '</div>', 500);
        }
    }

    public function save(Request $request)
    {
        try {
            $tahun    = $request->kategori_tahun ?? $request->tahun;
            $kategori = $request->kategori;
            $bulan    = $request->bulan;
            $nilai    = $request->nilai;

            // Cari data berdasarkan kategori dan tahun
            $rencana = RencanaPenerima::where('id_kategori_kriteria', $kategori)
                ->where('tahun', $tahun)
                ->first();

            if (!$rencana) {
                $rencana = new RencanaPenerima();
                $rencana->id_kategori_kriteria = $kategori;
                $rencana->tahun = (int) $tahun; // simpan sebagai integer (kolom smallint)
            }

            // Update hanya bulan yang diedit
            $rencana->{$bulan} = $nilai;
            $rencana->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'id'      => $rencana->id_rencana_penerima
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in save(): ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan semua baris tabel Permintaan sekaligus (batch save).
     * Dipanggil ketika user klik tombol "Simpan" di tab Permintaan.
     */
    public function saveBatch(Request $request)
    {
        try {
            $tahun     = $request->tahun;
            $tahunDate = $tahun . '-01-01'; // Kolom tahun di DB bertipe DATE
            $rows      = $request->rows;   // array: [{kategori, bulan, nilai}, ...]

            if (empty($rows) || !is_array($rows)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data yang dikirim'], 422);
            }

            $bulanValid = ['januari','februari','maret','april','mei','juni',
                           'juli','agustus','september','oktober','november','desember'];

            \DB::beginTransaction();

            foreach ($rows as $row) {
                $kategoriId = $row['kategori'] ?? null;
                $bulan      = $row['bulan']    ?? null;
                $nilai      = $row['nilai']    ?? 0;

                if (!$kategoriId || !in_array($bulan, $bulanValid)) continue;

                $rencana = RencanaPenerima::where('id_kategori_kriteria', $kategoriId)
                    ->where('tahun', $tahun)
                    ->first();

                if (!$rencana) {
                    $rencana = new RencanaPenerima();
                    $rencana->id_kategori_kriteria = $kategoriId;
                    $rencana->tahun = (int) $tahun; // kolom smallint — simpan integer
                }

                $rencana->{$bulan} = is_numeric($nilai) ? $nilai : 0;
                $rencana->save();
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data permintaan berhasil disimpan',
                'count'   => count($rows),
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error in saveBatch(): ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gabungan(Request $request)
    {
        try {
            $tahun = $request->tahun ?? date('Y');
            $bulanDari = $request->bulan_dari ?? 1;
            $bulanSampai = $request->bulan_sampai ?? 12;

            $bulanList = [
                'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
                'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
                'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
            ];

            $bulanListFiltered = array_filter($bulanList, function ($noBulan) use ($bulanDari, $bulanSampai) {
                return $noBulan >= $bulanDari && $noBulan <= $bulanSampai;
            });

            $kategori = KategoriKriteria::where('tipe', 'Penerima')->get();
            $data = [];
            $bulanAktif = [];

            foreach ($kategori as $k) {
                foreach ($bulanListFiltered as $namaBulan => $noBulan) {

                    // RENCANA — gunakan whereYear() agar kompatibel dengan kolom DATE
                    $rencana = DB::table('rencana_penerimas')
                        ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                        ->where('tahun', \$tahun)
                        ->sum($namaBulan);

                    // REALISASI
                    $nilaiRow = DB::table('penerimas')
                        ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                        ->whereYear('tanggal', $tahun)
                        ->whereMonth('tanggal', $noBulan)
                        ->selectRaw('COALESCE(SUM(nilai),0) as nilai, COALESCE(SUM(ppn),0) as ppn, COALESCE(SUM(potppn),0) as potppn')
                        ->first();

                    $realisasi = ($nilaiRow->nilai ?? 0) + ($nilaiRow->ppn ?? 0) - ($nilaiRow->potppn ?? 0);

                    if ($rencana > 0 || $realisasi > 0) {
                        $bulanAktif[$namaBulan] = true;
                    }

                    $selisih = $realisasi - $rencana;
                    $persen = $rencana > 0 ? ($realisasi / $rencana) * 100 : 0;

                    $data[$k->nama_kriteria][$namaBulan] = [
                        'rencana'   => $rencana,
                        'realisasi' => $realisasi,
                        'selisih'   => $selisih,
                        'persen'    => $persen
                    ];
                }
            }

            $bulanListFiltered = array_filter($bulanListFiltered, function ($namaBulan) use ($bulanAktif) {
                return isset($bulanAktif[$namaBulan]);
            }, ARRAY_FILTER_USE_KEY);

            return view('cash_bank.pembayaran.cashFlowGabunganPenerima', compact('data', 'bulanListFiltered', 'tahun', 'bulanDari', 'bulanSampai'));

        } catch (\Exception $e) {
            \Log::error('Error in gabungan(): ' . $e->getMessage());
            return response('<div class="alert alert-danger">Gagal memuat data: ' . $e->getMessage() . '</div>', 500);
        }
    }

    public function export_excel(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $kategori = $request->kategori;
        $bulanDari = $request->bulan_dari;
        $bulanSampai = $request->bulan_sampai;
        return Excel::download(new ExportExcelPenerima($tahun, $kategori, $bulanDari, $bulanSampai), 'penerima-' . $tahun . '.xlsx');
    }

    public function export_pdf(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

        $query = Penerima::with('kategori')
            ->where(function ($q) use ($tahun) {
                $q->whereYear('tanggal', $tahun)
                  ->orWhereNull('tanggal')
                  ->orWhere('tanggal', '0000-00-00');
            })
            ->orderByRaw('(tanggal IS NULL OR tanggal = ?) ASC, tanggal ASC', ['0000-00-00'])
            ->orderBy('id_kategori_kriteria');

        if ($request->filled('kategori')) {
            $query->where('id_kategori_kriteria', $request->kategori);
        }

        if ($request->filled('bulan_dari') && $request->filled('bulan_sampai')) {
            $query->where(function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->whereMonth('tanggal', '>=', $request->bulan_dari)
                       ->whereMonth('tanggal', '<=', $request->bulan_sampai);
                })->orWhereNull('tanggal')->orWhere('tanggal', '0000-00-00');
            });
        } elseif ($request->filled('bulan_dari')) {
            $query->where(function ($q) use ($request) {
                $q->whereMonth('tanggal', '>=', $request->bulan_dari)
                  ->orWhereNull('tanggal')->orWhere('tanggal', '0000-00-00');
            });
        } elseif ($request->filled('bulan_sampai')) {
            $query->where(function ($q) use ($request) {
                $q->whereMonth('tanggal', '<=', $request->bulan_sampai)
                  ->orWhereNull('tanggal')->orWhere('tanggal', '0000-00-00');
            });
        }

        $allData = $query->get();

        $grouped = [];
        foreach ($allData as $row) {
            if ($row->tanggal && $row->tanggal !== '0000-00-00') {
                $bulan = (int) \Carbon\Carbon::parse($row->tanggal)->format('n');
            } else {
                $bulan = 0;
            }
            $kategoriName = $row->kategori->nama_kriteria ?? '-';
            $grouped[$bulan][$kategoriName][] = $row;
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

        $pdf = Pdf::loadView('cash_bank.exportPDF.pdfPenerima', compact('grouped', 'bulanNames', 'tahun'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('penerima-' . $tahun . '.pdf');
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
        $data = RencanaPenerima::where('tahun', \$tahun)
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

        $kategori = KategoriKriteria::where('tipe', 'Penerima')->get();
        $data = [];
        $bulanAktif = [];

        foreach ($kategori as $k) {
            foreach ($bulanListFiltered as $namaBulan => $noBulan) {
                $rencana = DB::table('rencana_penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->where('tahun', \$tahun)
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
