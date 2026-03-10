<?php

namespace App\Exports;

use App\Models\Penerima;
use App\Models\KategoriKriteria;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportExcelPenerima implements FromView
{
    protected $tahun;

    public function __construct($tahun = null)
    {
        $this->tahun = $tahun ?? date('Y');
    }

    public function view(): View
    {
        $query = Penerima::with('kategori')
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal')
            ->orderBy('id_kategori_kriteria')
            ->get();

        // Group by month -> kategori
        $grouped = [];
        foreach ($query as $row) {
            $bulan = (int) \Carbon\Carbon::parse($row->tanggal)->format('n');
            $kategoriName = $row->kategori->nama_kriteria ?? '-';
            $grouped[$bulan][$kategoriName][] = $row;
        }
        ksort($grouped);

        $bulanNames = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];

        $tahun = $this->tahun;

        return view('cash_bank.exportExcel.excelPenerima', compact('grouped', 'bulanNames', 'tahun'));
    }
}
