<?php

namespace App\Exports;

use App\Models\Penerima;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;

class ExportExcelCashFlowPenerima implements FromView
{
    protected $tahun;

    public function __construct($tahun = null)
    {
        $this->tahun = $tahun ?? date('Y');
    }

    public function view(): View
    {
        $data = Penerima::with('kategori')
            ->select(
                'id_kategori_kriteria',
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(nilai + ppn - potppn) as total')
            )
            ->whereYear('tanggal', $this->tahun)
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

            // TOTAL NILAI INC PPN
            $result[$kategori][$bulan] = $row->total;
        }

        // Ensure all categories have data structure
        foreach ($kategoriList as $cat) {
            if (!isset($result[$cat])) {
                $result[$cat] = array_fill(1, 12, 0);
            }
        }

        return view('cash_bank.exportExcel.excelCashFlowPenerima', [
            'result' => $result,
            'tahun' => $this->tahun
        ]);
    }
}
