<?php

namespace App\Exports;

use App\Models\Penerima;
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
        $data = Penerima::select(
            'id_penerima',
            'kontrak',
            'id_kategori_kriteria',
            'pembeli',
            'no_reg',
            'tanggal',
            'volume',
            'harga',
            'nilai',
            'ppn',
            'potppn'
        )
            ->with(['kategori:id_kategori_kriteria,nama_kriteria'])
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id_penerima')
            ->get();

        return view('cash_bank.exportExcel.excelPenerima', compact('data'));
    }
}
