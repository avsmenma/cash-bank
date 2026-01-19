<?php

namespace App\Http\Controllers;

use App\Exports\sap;
use App\Models\BankKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UserSAPController extends Controller
{
   public function index(Request $request)
{
    $bankTujuanId = auth()->user()->id_bank_tujuan;
    $tahun = $request->tahun ?? date('Y');
    $tahunDipilih = $request->tahun ?? date('Y');

    $listTahun = DB::table('bank_keluars')
        ->selectRaw('YEAR(tanggal) as tahun')
        ->where('id_bank_tujuan', $bankTujuanId)
        ->groupBy('tahun')
        ->orderBy('tahun', 'desc')
        ->pluck('tahun');

    // ================= UNION TRANSAKSI =================
    $trx = DB::query()->fromSub(function ($q) use ($bankTujuanId, $tahun) {

        // -------- BANK MASUK (DEBET) --------
        $q->select(
            'bank_masuk.tanggal',
            'bank_masuk.agenda_tahun as agenda',
            'bank_masuk.id_bank_tujuan',
            'bank_masuk.penerima',
            'bank_masuk.uraian',
            DB::raw('bank_masuk.debet'),
            DB::raw('0 as kredit'),
            DB::raw('NULL as no_sap'),
            DB::raw('NULL as id_bank_keluar')
        )
        ->from('bank_masuk')
        ->where('bank_masuk.id_bank_tujuan', $bankTujuanId)
        ->whereYear('bank_masuk.tanggal', $tahun)

        ->unionAll(

            // -------- BANK KELUAR (KREDIT) --------
            DB::table('bank_keluars')
                ->select(
                    'bank_keluars.tanggal',
                    'bank_keluars.agenda_tahun as agenda',
                    'bank_keluars.id_bank_tujuan',
                    'bank_keluars.penerima',
                    'bank_keluars.uraian',
                    DB::raw('0 as debet'),
                    DB::raw('bank_keluars.kredit'),
                    'bank_keluars.no_sap',
                    'bank_keluars.id_bank_keluar'
                )
                ->where('bank_keluars.id_bank_tujuan', $bankTujuanId)
                ->whereYear('bank_keluars.tanggal', $tahun)
        );
    }, 'trx');

    // ================= JOIN BANK TUJUAN =================
    $rows = DB::table(DB::raw("({$trx->toSql()}) as trx"))
        ->mergeBindings($trx)
        ->leftJoin(
            'bank_tujuan',
            'bank_tujuan.id_bank_tujuan',
            '=',
            'trx.id_bank_tujuan'
        )
        ->select(
            'trx.*',
            'bank_tujuan.nama_tujuan'
        )
        ->orderBy('trx.tanggal')
        ->get();

    // ================= SALDO BERJALAN =================
    $saldo = 0;
    foreach ($rows as $row) {
        $saldo += ($row->debet ?? 0) - ($row->kredit ?? 0);
        $row->saldo_akhir = $saldo;
    }

    return view('cash_bank.user.usersVendor', [
        'data'  => $rows,
        'tahun'       => $listTahun,
        'tahunAktif'  => $tahunDipilih
    ]);
}




    public function edit(string $id)
    {
        $keluar = BankKeluar::findOrFail($id);
        return view('cash_bank.modal.editSAP', compact('keluar'));
    }

    /**
     * Update No SAP (TEXT / BUKAN NILAI)
     */
     public function update(Request $request, $id)
    {
        $request->validate([
            'no_sap' => 'required'
        ]);

        BankKeluar::where('id_bank_keluar', $id)
            ->update([
                'no_sap' => $request->no_sap
            ]);

         return response()->json([
            'status' => true,
            'message' => 'No SAP berhasil disimpan'
        ]);
    }

    public function export_excel(Request $request){
        return Excel::download(new sap($request), 'rekapan-bank-keluar.xlsx');
    }

    public function view_pdf()
    {
    $bankTujuanId = auth()->user()->id_bank_tujuan;
    $tahun = $request->tahun ?? date('Y');
    $tahunDipilih = $request->tahun ?? date('Y');

    $listTahun = DB::table('bank_keluars')
        ->selectRaw('YEAR(tanggal) as tahun')
        ->where('id_bank_tujuan', $bankTujuanId)
        ->groupBy('tahun')
        ->orderBy('tahun', 'desc')
        ->pluck('tahun');

    // ================= UNION TRANSAKSI =================
    $trx = DB::query()->fromSub(function ($q) use ($bankTujuanId, $tahun) {

        // -------- BANK MASUK (DEBET) --------
        $q->select(
            'bank_masuk.tanggal',
            'bank_masuk.agenda_tahun as agenda',
            'bank_masuk.id_bank_tujuan',
            'bank_masuk.penerima',
            'bank_masuk.uraian',
            DB::raw('bank_masuk.debet'),
            DB::raw('0 as kredit'),
            DB::raw('NULL as no_sap'),
            DB::raw('NULL as id_bank_keluar')
        )
        ->from('bank_masuk')
        ->where('bank_masuk.id_bank_tujuan', $bankTujuanId)
        ->whereYear('bank_masuk.tanggal', $tahun)

        ->unionAll(

            // -------- BANK KELUAR (KREDIT) --------
            DB::table('bank_keluars')
                ->select(
                    'bank_keluars.tanggal',
                    'bank_keluars.agenda_tahun as agenda',
                    'bank_keluars.id_bank_tujuan',
                    'bank_keluars.penerima',
                    'bank_keluars.uraian',
                    DB::raw('0 as debet'),
                    DB::raw('bank_keluars.kredit'),
                    'bank_keluars.no_sap',
                    'bank_keluars.id_bank_keluar'
                )
                ->where('bank_keluars.id_bank_tujuan', $bankTujuanId)
                ->whereYear('bank_keluars.tanggal', $tahun)
        );
    }, 'trx');

    // ================= JOIN BANK TUJUAN =================
    $rows = DB::table(DB::raw("({$trx->toSql()}) as trx"))
        ->mergeBindings($trx)
        ->leftJoin(
            'bank_tujuan',
            'bank_tujuan.id_bank_tujuan',
            '=',
            'trx.id_bank_tujuan'
        )
        ->select(
            'trx.*',
            'bank_tujuan.nama_tujuan'
        )
        ->orderBy('trx.tanggal')
        ->get();

    // ================= SALDO BERJALAN =================
    $saldo = 0;
    foreach ($rows as $row) {
        $saldo += ($row->debet ?? 0) - ($row->kredit ?? 0);
        $row->saldo_akhir = $saldo;
    }

    return view('cash_bank.exportPDF.userSap', [
        'data'  => $rows,
        'tahun'       => $listTahun,
        'tahunAktif'  => $tahunDipilih
    ]);
    }
}
