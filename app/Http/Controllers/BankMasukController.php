<?php
// namespace App\Http\Controllers;

namespace App\Http\Controllers;
use App\Models\BankMasuk;
use App\Models\BankTujuan;
use App\Models\SumberDana;
use App\Imports\importMasuk;
use App\Imports\importSheet;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\JenisPembayaran;
use App\Models\KategoriKriteria;
use App\Exports\reportMasukExcel;
use App\Imports\importExcelMasukk;
use Illuminate\Support\Facades\DB;
use App\Models\GabunganMasukKeluar;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportExcelBankMasuk;
use App\Imports\importExcelMasukImport;

class BankMasukController extends Controller
{
    public function index(Request $request)
    {
        return view('cash_bank.bankMasuk', [
            'sumberDana' => SumberDana::all(),
            'bankTujuan' => BankTujuan::all(),
            'kategoriKriteria' => KategoriKriteria::where('tipe', 'Masuk')->get(),
            'jenisPembayaran' => JenisPembayaran::all(),
        ]);
    }
    public function datatable(Request $request)
    {
        $query = BankMasuk::with([
            'sumberDana:id_sumber_dana,nama_sumber_dana',
            'bankTujuan:id_bank_tujuan,nama_tujuan',
            'kategori:id_kategori_kriteria,nama_kriteria',
            'jenisPembayaran:id_jenis_pembayaran,nama_jenis_pembayaran',
        ])->orderBy('tanggal', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('jenis_pembayaran', function ($row) {
                return $row->jenisPembayaran
                    ? $row->jenisPembayaran->nama_jenis_pembayaran
                    : '-';
            })
            ->addColumn('kategori_kriteria', function ($row) {
                return $row->kategori
                    ? $row->kategori->nama_kriteria
                    : '-';
            })
            ->addColumn('bank_tujuan', function ($row) {
                return $row->bankTujuan
                    ? $row->bankTujuan->nama_tujuan
                    : '-';
            })
            ->addColumn('sumber_dana', function ($row) {
                return $row->sumberDana
                    ? $row->sumberDana->nama_sumber_dana
                    : '-';
            })
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="checkbox_ids" name="ids[]" value="' . $row->id_bank_masuk . '">';
            })
            ->editColumn('debet', function ($row) {
                return number_format((float) $row->debet, 0, ',', '.');
            })
            ->editColumn('tanggal', function ($row) {
                return \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y');
            })
            ->addColumn('aksi', function ($row) {
                return '
                <button class="btn btn-warning btn-sm" 
                    data-toggle="modal"
                        data-target="#edit"
                        data-id="' . $row->id_bank_masuk . '"
                        data-agenda="' . $row->agenda_tahun . '"
                        data-penerima="' . $row->penerima . '"
                        data-uraian="' . $row->uraian . '"
                        data-tanggal="' . $row->tanggal . '"
                        data-bank="' . $row->id_bank_tujuan . '"
                        data-sumber="' . $row->id_sumber_dana . '"
                        data-kategori="' . $row->id_kategori_kriteria . '"
                        data-jenis="' . $row->id_jenis_pembayaran . '"
                        data-keterangan="' . $row->keterangan . '"
                        data-debet="' . $row->debet . '">Edit</button>
                ';
            })
            ->rawColumns(['checkbox', 'aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->method(), $request->all());

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'debet' => 'required|numeric',
        ]);
        // dd($request->tanggal);

        BankMasuk::create([
            'agenda_tahun' => $request->agenda_tahun,
            'id_sumber_dana' => $request->id_sumber_dana,
            'id_bank_tujuan' => $request->id_bank_tujuan,
            'id_kategori_kriteria' => $request->id_kategori_kriteria,
            'id_jenis_pembayaran' => $request->id_jenis_pembayaran,
            'uraian' => $request->uraian,
            'penerima' => $request->penerima,
            'tanggal' => $request->tanggal,
            'debet' => str_replace('.', '', $request->debet) ?? 0,
            'kredit' => 0,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Data berhasil disimpan');
    }

    public function report(Request $request)
    {
        /* ================= REQUEST ================= */
        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $bankTujuanId = $request->bankTujuan;
        $sumberDanaIds = $request->sumber_dana ?? [];
        $kategoriIds = $request->kategori ?? [];
        $jenisPembayaranId = $request->jenis_pembayaran;

        /* ================= QUERY DATA ================= */
        $data = BankMasuk::with(['sumberDana', 'bankTujuan', 'kategori', 'jenisPembayaran'])
            ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
            ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
            ->when($bankTujuanId, fn($q) => $q->where('id_bank_tujuan', $bankTujuanId))
            ->when($jenisPembayaranId, fn($q) => $q->where('id_jenis_pembayaran', $jenisPembayaranId))
            ->when(count($sumberDanaIds), fn($q) => $q->whereIn('id_sumber_dana', $sumberDanaIds))
            ->when(count($kategoriIds), fn($q) => $q->whereIn('id_kategori_kriteria', $kategoriIds))
            ->orderBy('tanggal')
            ->get();

        /* ================= DROPDOWN TERHUBUNG ================= */

        // Bank Tujuan
        $bankTujuanList = DB::table('bank_tujuan')
            ->whereExists(function ($q) use ($tahun, $bulan, $sumberDanaIds, $kategoriIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_bank_tujuan', 'bank_tujuan.id_bank_tujuan')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId));
            })
            ->orderBy('nama_tujuan')
            ->get();

        // Sumber Dana
        $sumberDanaList = DB::table('sumber_dana')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $kategoriIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_sumber_dana', 'sumber_dana.id_sumber_dana')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds));
            })
            ->orderBy('nama_sumber_dana')
            ->get();

        // Kategori
        $kategoriList = DB::table('kategori_kriteria')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $sumberDanaIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds));

            })
            ->orderBy('nama_kriteria')
            ->get();

        // Jenis Pembayaran
        $jenisPembayaranList = DB::table('jenis_pembayarans')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $sumberDanaIds, $kategoriIds) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_jenis_pembayaran', 'jenis_pembayarans.id_jenis_pembayaran')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds));
            })
            ->orderBy('nama_jenis_pembayaran')
            ->get();

        $tahunList = BankMasuk::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->pluck('tahun');

        return view('cash_bank.reportMasuk', compact(
            'data',
            'tahunList',
            'bankTujuanList',
            'sumberDanaList',
            'kategoriList',
            'jenisPembayaranList'
        ));
    }


    public function importExcel(Request $request)
    {
        $request->validate(['fileExcel' => 'required|file']);
        $ext = strtolower($request->file('fileExcel')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return back()->withErrors(['fileExcel' => 'File harus berformat xlsx, xls, atau csv.']);
        }
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        Excel::import(
            new importSheet,
            $request->file('fileExcel')
        );

        return redirect()
            ->route('bank-masuk.index')
            ->with('success', 'Data berhasil diimport');
    }

    /**
     * PREVIEW: Parse file tanpa simpan, return JSON untuk preview modal
     */
    public function previewImport(Request $request)
    {
        $request->validate(['fileExcel' => 'required|file']);
        $ext = strtolower($request->file('fileExcel')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return response()->json(['message' => 'File harus berformat xlsx, xls, atau csv.'], 422);
        }

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $file = $request->file('fileExcel');

        // Simpan file sementara
        $tempPath = $file->store('import_temp', 'local');
        session(['masuk_import_temp' => $tempPath]);

        // Baca data dari file sebagai array dengan heading row
        $headingImport = new class implements \Maatwebsite\Excel\Concerns\WithHeadingRow {
            public function headingRow(): int { return 1; }
        };
        $rows = Excel::toArray($headingImport, $file);
        $sheetRows = $rows[0] ?? [];


        // Lookup referensi dari DB (cache semuanya)
        $sumberDanaMap  = \App\Models\SumberDana::pluck('id_sumber_dana', 'nama_sumber_dana')->toArray();
        $bankTujuanMap  = \App\Models\BankTujuan::pluck('id_bank_tujuan', 'nama_tujuan')->toArray();
        $kategoriMap    = \App\Models\KategoriKriteria::pluck('id_kategori_kriteria', 'nama_kriteria')->toArray();
        $jenisPembMap   = \App\Models\JenisPembayaran::pluck('id_jenis_pembayaran', 'nama_jenis_pembayaran')->toArray();

        $preview  = [];
        $warnings = 0;

        foreach ($sheetRows as $i => $row) {
            // Ambil nilai kolom utama dulu
            $tanggalRaw    = $row['tanggal'] ?? $row[1] ?? null;
            $sumberRaw     = trim((string)($row['sumber_dana'] ?? $row[2] ?? ''));
            $bankRaw       = trim((string)($row['bank_tujuan'] ?? $row[3] ?? ''));
            $kategoriRaw   = trim((string)($row['kategori'] ?? $row[4] ?? ''));
            $penerimaRaw   = trim((string)($row['penerima'] ?? $row[5] ?? ''));
            $uraianRaw     = trim((string)($row['uraian'] ?? $row[6] ?? ''));
            $debetRaw      = $row['debet'] ?? $row[7] ?? null;
            $jenisRaw      = trim((string)($row['jenis_pembayaran'] ?? $row[8] ?? ''));

            // Hitung nilai debet numerik untuk filter
            $debetNum = 0;
            if ($debetRaw !== null && $debetRaw !== '') {
                $debetNum = (float) str_replace(['.', ','], ['', '.'], (string)$debetRaw);
            }

            // Skip baris kosong: wajib punya tanggal DAN (debet > 0 ATAU sumber_dana tidak kosong)
            $tanggalStr = trim((string)$tanggalRaw);
            if (empty($tanggalStr) || ($debetNum <= 0 && empty($sumberRaw))) continue;

            // Resolve referensi — HANYA jika nilai tidak kosong (str_contains(x,'') selalu true!)
            $sumberFound = null;
            if (!empty($sumberRaw)) {
                foreach ($sumberDanaMap as $nama => $id) {
                    if (str_contains(strtolower($nama), strtolower($sumberRaw)) || str_contains(strtolower($sumberRaw), strtolower($nama))) {
                        $sumberFound = $nama; break;
                    }
                }
            }

            $bankFound = null;
            if (!empty($bankRaw)) {
                foreach ($bankTujuanMap as $nama => $id) {
                    if (str_contains(strtolower($nama), strtolower($bankRaw)) || str_contains(strtolower($bankRaw), strtolower($nama))) {
                        $bankFound = $nama; break;
                    }
                }
            }

            $kategoriFound = null;
            if (!empty($kategoriRaw)) {
                foreach ($kategoriMap as $nama => $id) {
                    if (str_contains(strtolower($nama), strtolower($kategoriRaw)) || str_contains(strtolower($kategoriRaw), strtolower($nama))) {
                        $kategoriFound = $nama; break;
                    }
                }
            }

            $jenisFound = null;
            if (!empty($jenisRaw)) {
                foreach ($jenisPembMap as $nama => $id) {
                    if (str_contains(strtolower($nama), strtolower($jenisRaw)) || str_contains(strtolower($jenisRaw), strtolower($nama))) {
                        $jenisFound = $nama; break;
                    }
                }
            }


            // Format tanggal
            $tanggalFormatted = null;
            if ($tanggalRaw) {
                try {
                    if (is_numeric($tanggalRaw)) {
                        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$tanggalRaw);
                        $tanggalFormatted = \Carbon\Carbon::instance($dt)->format('d/m/Y');
                    } else {
                        $tanggalFormatted = \Carbon\Carbon::parse(str_replace(['-','.'], '/', $tanggalRaw))->format('d/m/Y');
                    }
                } catch (\Exception $e) {
                    $tanggalFormatted = (string)$tanggalRaw;
                }
            }


            $hasWarning = ($sumberRaw && !$sumberFound)
                       || ($bankRaw && !$bankFound)
                       || ($kategoriRaw && !$kategoriFound)
                       || ($jenisRaw && !$jenisFound);

            if ($hasWarning) $warnings++;

            $preview[] = [
                'no'       => $i + 1,
                'tanggal'  => $tanggalFormatted,
                'sumber'   => $sumberFound ?? ($sumberRaw ?: '-'),
                'bank'     => $bankFound   ?? ($bankRaw   ?: '-'),
                'kategori' => $kategoriFound ?? ($kategoriRaw ?: '-'),
                'jenis'    => $jenisFound  ?? ($jenisRaw ?: '-'),
                'penerima' => $penerimaRaw ?: '-',
                'uraian'   => $uraianRaw   ?: '-',
                'debet'    => number_format($debetNum, 0, ',', '.'),
                'warning'  => $hasWarning,
                // warning detail
                'warn_sumber'   => $sumberRaw && !$sumberFound,
                'warn_bank'     => $bankRaw && !$bankFound,
                'warn_kategori' => $kategoriRaw && !$kategoriFound,
                'warn_jenis'    => $jenisRaw && !$jenisFound,
            ];
        }

        return response()->json([
            'rows'     => $preview,
            'total'    => count($preview),
            'warnings' => $warnings,
        ]);
    }

    /**
     * CONFIRM: Eksekusi import dari file temp yang tersimpan di session
     */
    public function confirmImport(Request $request)
    {
        $tempPath = session('masuk_import_temp');

        if (!$tempPath || !\Illuminate\Support\Facades\Storage::disk('local')->exists($tempPath)) {
            return response()->json(['error' => 'File sementara tidak ditemukan. Silakan upload ulang.'], 422);
        }

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($tempPath);

        Excel::import(new importMasuk, $fullPath);

        // Hapus file temp & session
        \Illuminate\Support\Facades\Storage::disk('local')->delete($tempPath);
        session()->forget('masuk_import_temp');

        return response()->json(['success' => 'Data berhasil diimport ke database.', 'total' => 0]);
    }


    public function edit(string $id)
    {
        $masuk = BankMasuk::findOrFail($id);
        return view('cash_bank.modal.edit', compact('masuk'));
    }
    public function update(Request $request, string $id)
    {

        $masuk = BankMasuk::findOrFail($id);
        $masuk->update($request->all());

        return redirect()->route('bank-masuk.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $data = BankMasuk::findOrFail($id);
        $data->delete();

        return redirect()->route('bank-masuk.index')->with('success', 'Data berhasil dihapus');
    }

    public function deleteAll(Request $request)
    {
        $ids = $request->ids;

        BankMasuk::whereIn('id_bank_masuk', $ids)->delete();

        return response()->json([
            'success' => 'Data Bank Masuk Berhasil Dihapus!'
        ]);
    }


    public function export_excel()
    {
        return Excel::download(new ExportExcelBankMasuk, 'bankMasuk.xlsx');
    }

    public function report_export_excel(Request $request)
    {
        return Excel::download(
            new reportMasukExcel($request),
            'report-bank-masuk-' . date('Y-m-d') . '.xlsx'
        );
    }

    public function view_pdf()
    {
        $data = BankMasuk::select(
            'id_bank_masuk',
            'agenda_tahun',
            'tanggal',
            'id_sumber_dana',
            'id_bank_tujuan',
            'id_kategori_kriteria',
            'penerima',
            'uraian',
            'id_jenis_pembayaran',
            'nilai_rupiah',
            'debet',
            'keterangan'
        )
            ->with([
                'sumberDana:id_sumber_dana,nama_sumber_dana',
                'bankTujuan:id_bank_tujuan,nama_tujuan',
                'kategori:id_kategori_kriteria,nama_kriteria',
                'jenisPembayaran:id_jenis_pembayaran,nama_jenis_pembayaran',
            ])
            ->orderBy('tanggal', 'asc')
            ->orderBy('id_bank_masuk')
            ->get();

        return view('cash_bank.exportPDF.masukPdf', [
            'data' => $data,
            'sumberDana' => SumberDana::all(),
            'bankTujuan' => BankTujuan::all(),
            'kategoriKriteria' => KategoriKriteria::where('tipe', 'Masuk')->get(),
            'jenisPembayaran' => JenisPembayaran::all(),
        ]);
    }

    public function reportMasukPdf(Request $request)
    {
        /* ================= REQUEST ================= */
        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $bankTujuanId = $request->bankTujuan;
        $sumberDanaIds = $request->sumber_dana ?? [];
        $kategoriIds = $request->kategori ?? [];
        $jenisPembayaranId = $request->jenis_pembayaran;

        /* ================= QUERY DATA ================= */
        $data = BankMasuk::with(['sumberDana', 'bankTujuan', 'kategori', 'jenisPembayaran'])
            ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
            ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
            ->when($bankTujuanId, fn($q) => $q->where('id_bank_tujuan', $bankTujuanId))
            ->when($jenisPembayaranId, fn($q) => $q->where('id_jenis_pembayaran', $jenisPembayaranId))
            ->when(count($sumberDanaIds), fn($q) => $q->whereIn('id_sumber_dana', $sumberDanaIds))
            ->when(count($kategoriIds), fn($q) => $q->whereIn('id_kategori_kriteria', $kategoriIds))
            ->orderBy('tanggal')
            ->get();

        /* ================= DROPDOWN TERHUBUNG ================= */

        // Bank Tujuan
        $bankTujuanList = DB::table('bank_tujuan')
            ->whereExists(function ($q) use ($tahun, $bulan, $sumberDanaIds, $kategoriIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_bank_tujuan', 'bank_tujuan.id_bank_tujuan')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId));
            })
            ->orderBy('nama_tujuan')
            ->get();

        // Sumber Dana
        $sumberDanaList = DB::table('sumber_dana')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $kategoriIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_sumber_dana', 'sumber_dana.id_sumber_dana')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds));
            })
            ->orderBy('nama_sumber_dana')
            ->get();

        // Kategori
        $kategoriList = DB::table('kategori_kriteria')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $sumberDanaIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds));

            })
            ->orderBy('nama_kriteria')
            ->get();

        // Jenis Pembayaran
        $jenisPembayaranList = DB::table('jenis_pembayarans')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $sumberDanaIds, $kategoriIds) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_jenis_pembayaran', 'jenis_pembayarans.id_jenis_pembayaran')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds));
            })
            ->orderBy('nama_jenis_pembayaran')
            ->get();

        $tahunList = BankMasuk::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->pluck('tahun');

        return view('cash_bank.exportPDF.reportMasuk', compact(
            'data',
            'tahunList',
            'bankTujuanList',
            'sumberDanaList',
            'kategoriList',
            'jenisPembayaranList'
        ));
    }
}