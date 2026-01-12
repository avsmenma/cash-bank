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
        ])->orderBy('tanggal','asc');

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
                return '<input type="checkbox" class="checkbox_ids" name="ids[]" value="'.$row->id_bank_masuk.'">';
            })
            ->addColumn('aksi', function ($row) {
                return '
                <button class="btn btn-warning btn-sm" 
                    data-toggle="modal"
                        data-target="#edit"
                        data-id="'.$row->id_bank_masuk.'"
                        data-agenda="'.$row->agenda_tahun.'"
                        data-penerima="'.$row->penerima.'"
                        data-uraian="'.$row->uraian.'"
                        data-tanggal="'.$row->tanggal.'"
                        data-bank="'.$row->id_bank_tujuan.'"
                        data-sumber="'.$row->id_sumber_dana.'"
                        data-kategori="'.$row->id_kategori_kriteria.'"
                        data-jenis="'.$row->id_jenis_pembayaran.'"
                        data-keterangan="'.$row->keterangan.'"
                        data-debet="'.$row->debet.'">Edit</button>
                ';
            })
            ->rawColumns(['checkbox','aksi'])
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

        return back()->with('success','Data berhasil disimpan');
    }

public function report(Request $request)
{
    /* ================= REQUEST ================= */
    $tahun               = $request->tahun;
    $bulan               = $request->bulan;
    $bankTujuanId        = $request->bankTujuan;          
    $sumberDanaIds       = $request->sumber_dana ?? [];   
    $kategoriIds         = $request->kategori ?? [];      
    $jenisPembayaranId  = $request->jenis_pembayaran; 

    /* ================= QUERY DATA ================= */
    $data = BankMasuk::with(['sumberDana','bankTujuan','kategori','jenisPembayaran'])
        ->when($tahun, fn ($q) => $q->whereYear('tanggal', $tahun))
        ->when($bulan, fn ($q) => $q->whereMonth('tanggal', $bulan))
        ->when($bankTujuanId, fn ($q) => $q->where('id_bank_tujuan', $bankTujuanId))
        ->when($jenisPembayaranId, fn ($q) => $q->where('id_jenis_pembayaran', $jenisPembayaranId))
        ->when(count($sumberDanaIds), fn ($q) => $q->whereIn('id_sumber_dana', $sumberDanaIds))
        ->when(count($kategoriIds), fn ($q) => $q->whereIn('id_kategori_kriteria', $kategoriIds))
        ->orderBy('tanggal')
        ->get();

    /* ================= DROPDOWN TERHUBUNG ================= */

    // Bank Tujuan
    $bankTujuanList = DB::table('bank_tujuan')
        ->whereExists(function ($q) use ($tahun,$bulan,$sumberDanaIds,$kategoriIds,$jenisPembayaranId) {
            $q->select(DB::raw(1))
              ->from('bank_masuk')
              ->whereColumn('bank_masuk.id_bank_tujuan','bank_tujuan.id_bank_tujuan')
              ->when($tahun, fn($x)=>$x->whereYear('tanggal',$tahun))
              ->when($bulan, fn($x)=>$x->whereMonth('tanggal',$bulan))
              ->when(count($sumberDanaIds), fn($x)=>$x->whereIn('id_sumber_dana',$sumberDanaIds))
              ->when(count($kategoriIds), fn($x)=>$x->whereIn('id_kategori_kriteria',$kategoriIds))
              ->when($jenisPembayaranId, fn($x)=>$x->where('id_jenis_pembayaran', $jenisPembayaranId));
        })
        ->orderBy('nama_tujuan')
        ->get();

    // Sumber Dana
    $sumberDanaList = DB::table('sumber_dana')
        ->whereExists(function ($q) use ($tahun,$bulan,$bankTujuanId,$kategoriIds,$jenisPembayaranId) {
            $q->select(DB::raw(1))
              ->from('bank_masuk')
              ->whereColumn('bank_masuk.id_sumber_dana','sumber_dana.id_sumber_dana')
              ->when($tahun, fn($x)=>$x->whereYear('tanggal',$tahun))
              ->when($bulan, fn($x)=>$x->whereMonth('tanggal',$bulan))
              ->when($bankTujuanId, fn($x)=>$x->where('id_bank_tujuan',$bankTujuanId))
              ->when($jenisPembayaranId, fn($x)=>$x->where('id_jenis_pembayaran', $jenisPembayaranId))
              ->when(count($kategoriIds), fn($x)=>$x->whereIn('id_kategori_kriteria',$kategoriIds));
        })
        ->orderBy('nama_sumber_dana')
        ->get();

    // Kategori
    $kategoriList = DB::table('kategori_kriteria')
        ->whereExists(function ($q) use ($tahun,$bulan,$bankTujuanId,$sumberDanaIds,$jenisPembayaranId) {
            $q->select(DB::raw(1))
              ->from('bank_masuk')
              ->whereColumn('bank_masuk.id_kategori_kriteria','kategori_kriteria.id_kategori_kriteria')
              ->when($tahun, fn($x)=>$x->whereYear('tanggal',$tahun))
              ->when($bulan, fn($x)=>$x->whereMonth('tanggal',$bulan))
              ->when($bankTujuanId, fn($x)=>$x->where('id_bank_tujuan',$bankTujuanId))
              ->when($jenisPembayaranId, fn($x)=>$x->where('id_jenis_pembayaran', $jenisPembayaranId))
              ->when(count($sumberDanaIds), fn($x)=>$x->whereIn('id_sumber_dana',$sumberDanaIds));

        })
        ->orderBy('nama_kriteria')
        ->get();

    // Jenis Pembayaran
    $jenisPembayaranList = DB::table('jenis_pembayarans')
        ->whereExists(function ($q) use ($tahun,$bulan,$bankTujuanId,$sumberDanaIds,$kategoriIds) {
            $q->select(DB::raw(1))
              ->from('bank_masuk')
              ->whereColumn('bank_masuk.id_jenis_pembayaran','jenis_pembayarans.id_jenis_pembayaran')
              ->when($tahun, fn($x)=>$x->whereYear('tanggal',$tahun))
              ->when($bulan, fn($x)=>$x->whereMonth('tanggal',$bulan))
              ->when($bankTujuanId, fn($x)=>$x->where('id_bank_tujuan',$bankTujuanId))
              ->when(count($sumberDanaIds), fn($x)=>$x->whereIn('id_sumber_dana',$sumberDanaIds))
              ->when(count($kategoriIds), fn($x)=>$x->whereIn('id_kategori_kriteria',$kategoriIds));
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
        $request->validate([
            'fileExcel' => 'required|mimes:xlsx,xls'
        ]);
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        // $file = $request->file('fileExcel')->store('public/import');

        // Excel::import(new importSheet, $file);

         Excel::import(
            new importSheet,
            $request->file('fileExcel')
        );

        return redirect()
            ->route('bank-masuk.index')
            ->with('success', 'Data berhasil diimport');
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


    public function export_excel(){
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
            'kategoriKriteria' => KategoriKriteria::where('tipe','Masuk')->get(),
            'jenisPembayaran' => JenisPembayaran::all(),
        ]);
    }

    public function reportMasukPdf(Request $request)
    {
       /* ================= REQUEST ================= */
    $tahun               = $request->tahun;
    $bulan               = $request->bulan;
    $bankTujuanId        = $request->bankTujuan;          
    $sumberDanaIds       = $request->sumber_dana ?? [];   
    $kategoriIds         = $request->kategori ?? [];      
    $jenisPembayaranId  = $request->jenis_pembayaran; 

    /* ================= QUERY DATA ================= */
    $data = BankMasuk::with(['sumberDana','bankTujuan','kategori','jenisPembayaran'])
        ->when($tahun, fn ($q) => $q->whereYear('tanggal', $tahun))
        ->when($bulan, fn ($q) => $q->whereMonth('tanggal', $bulan))
        ->when($bankTujuanId, fn ($q) => $q->where('id_bank_tujuan', $bankTujuanId))
        ->when($jenisPembayaranId, fn ($q) => $q->where('id_jenis_pembayaran', $jenisPembayaranId))
        ->when(count($sumberDanaIds), fn ($q) => $q->whereIn('id_sumber_dana', $sumberDanaIds))
        ->when(count($kategoriIds), fn ($q) => $q->whereIn('id_kategori_kriteria', $kategoriIds))
        ->orderBy('tanggal')
        ->get();

    /* ================= DROPDOWN TERHUBUNG ================= */

    // Bank Tujuan
    $bankTujuanList = DB::table('bank_tujuan')
        ->whereExists(function ($q) use ($tahun,$bulan,$sumberDanaIds,$kategoriIds,$jenisPembayaranId) {
            $q->select(DB::raw(1))
              ->from('bank_masuk')
              ->whereColumn('bank_masuk.id_bank_tujuan','bank_tujuan.id_bank_tujuan')
              ->when($tahun, fn($x)=>$x->whereYear('tanggal',$tahun))
              ->when($bulan, fn($x)=>$x->whereMonth('tanggal',$bulan))
              ->when(count($sumberDanaIds), fn($x)=>$x->whereIn('id_sumber_dana',$sumberDanaIds))
              ->when(count($kategoriIds), fn($x)=>$x->whereIn('id_kategori_kriteria',$kategoriIds))
              ->when($jenisPembayaranId, fn($x)=>$x->where('id_jenis_pembayaran', $jenisPembayaranId));
        })
        ->orderBy('nama_tujuan')
        ->get();

    // Sumber Dana
    $sumberDanaList = DB::table('sumber_dana')
        ->whereExists(function ($q) use ($tahun,$bulan,$bankTujuanId,$kategoriIds,$jenisPembayaranId) {
            $q->select(DB::raw(1))
              ->from('bank_masuk')
              ->whereColumn('bank_masuk.id_sumber_dana','sumber_dana.id_sumber_dana')
              ->when($tahun, fn($x)=>$x->whereYear('tanggal',$tahun))
              ->when($bulan, fn($x)=>$x->whereMonth('tanggal',$bulan))
              ->when($bankTujuanId, fn($x)=>$x->where('id_bank_tujuan',$bankTujuanId))
              ->when($jenisPembayaranId, fn($x)=>$x->where('id_jenis_pembayaran', $jenisPembayaranId))
              ->when(count($kategoriIds), fn($x)=>$x->whereIn('id_kategori_kriteria',$kategoriIds));
        })
        ->orderBy('nama_sumber_dana')
        ->get();

    // Kategori
    $kategoriList = DB::table('kategori_kriteria')
        ->whereExists(function ($q) use ($tahun,$bulan,$bankTujuanId,$sumberDanaIds,$jenisPembayaranId) {
            $q->select(DB::raw(1))
              ->from('bank_masuk')
              ->whereColumn('bank_masuk.id_kategori_kriteria','kategori_kriteria.id_kategori_kriteria')
              ->when($tahun, fn($x)=>$x->whereYear('tanggal',$tahun))
              ->when($bulan, fn($x)=>$x->whereMonth('tanggal',$bulan))
              ->when($bankTujuanId, fn($x)=>$x->where('id_bank_tujuan',$bankTujuanId))
              ->when($jenisPembayaranId, fn($x)=>$x->where('id_jenis_pembayaran', $jenisPembayaranId))
              ->when(count($sumberDanaIds), fn($x)=>$x->whereIn('id_sumber_dana',$sumberDanaIds));

        })
        ->orderBy('nama_kriteria')
        ->get();

    // Jenis Pembayaran
    $jenisPembayaranList = DB::table('jenis_pembayarans')
        ->whereExists(function ($q) use ($tahun,$bulan,$bankTujuanId,$sumberDanaIds,$kategoriIds) {
            $q->select(DB::raw(1))
              ->from('bank_masuk')
              ->whereColumn('bank_masuk.id_jenis_pembayaran','jenis_pembayarans.id_jenis_pembayaran')
              ->when($tahun, fn($x)=>$x->whereYear('tanggal',$tahun))
              ->when($bulan, fn($x)=>$x->whereMonth('tanggal',$bulan))
              ->when($bankTujuanId, fn($x)=>$x->where('id_bank_tujuan',$bankTujuanId))
              ->when(count($sumberDanaIds), fn($x)=>$x->whereIn('id_sumber_dana',$sumberDanaIds))
              ->when(count($kategoriIds), fn($x)=>$x->whereIn('id_kategori_kriteria',$kategoriIds));
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