<?php

namespace App\Exports;

use App\Models\RencanaPenerima;
use App\Models\KategoriKriteria;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportExcelRencanaPenerima implements FromView
{
    protected $tahun;

    public function __construct($tahun = null)
    {
        $this->tahun = $tahun ?? date('Y');
    }

    public function view(): View
    {
        $kategori = KategoriKriteria::where('tipe', 'Penerima')->get();
        $data = RencanaPenerima::where('tahun', $this->tahun)
            ->get()
            ->keyBy('id_kategori_kriteria');

        return view('cash_bank.exportExcel.excelRencanaPenerima', [
            'kategori' => $kategori,
            'data' => $data,
            'tahun' => $this->tahun
        ]);
    }
}
