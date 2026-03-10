<?php

namespace App\Exports;

use App\Models\Penerima;
use App\Models\KategoriKriteria;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportExcelPenerima implements FromView
{
    protected $tahun;
    protected $kategori;
    protected $bulanDari;
    protected $bulanSampai;

    public function __construct($tahun = null, $kategori = null, $bulanDari = null, $bulanSampai = null)
    {
        $this->tahun = $tahun ?? date('Y');
        $this->kategori = $kategori;
        $this->bulanDari = $bulanDari;
        $this->bulanSampai = $bulanSampai;
    }

    public function view(): View
    {
        $query = Penerima::with('kategori')
            ->whereYear('tanggal', $this->tahun)
            ->orderBy('tanggal')
            ->orderBy('id_kategori_kriteria');

        if ($this->kategori) {
            $query->where('id_kategori_kriteria', $this->kategori);
        }

        if ($this->bulanDari && $this->bulanSampai) {
            $query->whereMonth('tanggal', '>=', $this->bulanDari)
                  ->whereMonth('tanggal', '<=', $this->bulanSampai);
        } elseif ($this->bulanDari) {
            $query->whereMonth('tanggal', '>=', $this->bulanDari);
        } elseif ($this->bulanSampai) {
            $query->whereMonth('tanggal', '<=', $this->bulanSampai);
        }

        $allData = $query->get();

        // Group by month -> kategori
        $grouped = [];
        foreach ($allData as $row) {
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
