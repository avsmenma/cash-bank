<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class ExportExcelDashboardBank implements FromView
{
    protected $tanggal;
    protected $nama;
    protected $jabatan;

    public function __construct($tanggal, $nama, $jabatan)
    {
        $this->tanggal = $tanggal;
        $this->nama    = $nama;
        $this->jabatan = $jabatan;
    }

    public function view(): View
    {
        // Ambil data sumber dana (sama seperti di controller bank())
        $sumberDanaList = DB::table('sumber_dana')
            ->select('sumber_dana.id_sumber_dana', 'sumber_dana.nama_sumber_dana')
            ->selectRaw('COALESCE((SELECT SUM(debet) FROM bank_masuk WHERE bank_masuk.id_sumber_dana = sumber_dana.id_sumber_dana), 0) as total_masuk')
            ->selectRaw('COALESCE((SELECT SUM(kredit) FROM bank_keluars WHERE bank_keluars.id_sumber_dana = sumber_dana.id_sumber_dana), 0) as total_keluar')
            ->orderBy('sumber_dana.id_sumber_dana')
            ->get()
            ->map(function ($sd) {
                $sd->saldo_va = (float) $sd->total_masuk - (float) $sd->total_keluar;
                return $sd;
            });

        $totalSaldoBank = $sumberDanaList->sum('saldo_va');

        return view('cash_bank.exportExcel.excelDashboardBank', [
            'sumberDanaList' => $sumberDanaList,
            'totalSaldoBank' => $totalSaldoBank,
            'tanggal'        => $this->tanggal,
            'nama'           => $this->nama,
            'jabatan'        => $this->jabatan,
        ]);
    }
}
