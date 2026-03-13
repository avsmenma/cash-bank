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
            ->where(function ($q) {
                $q->whereYear('tanggal', $this->tahun)
                  ->orWhereNull('tanggal')
                  ->orWhere('tanggal', '0000-00-00');
            })
            ->orderByRaw('(tanggal IS NULL OR tanggal = "0000-00-00") ASC, tanggal ASC')
            ->orderBy('id_kategori_kriteria');

        if ($this->kategori) {
            $query->where('id_kategori_kriteria', $this->kategori);
        }

        if ($this->bulanDari && $this->bulanSampai) {
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereMonth('tanggal', '>=', $this->bulanDari)
                       ->whereMonth('tanggal', '<=', $this->bulanSampai);
                })->orWhereNull('tanggal')->orWhere('tanggal', '0000-00-00');
            });
        } elseif ($this->bulanDari) {
            $query->where(function ($q) {
                $q->whereMonth('tanggal', '>=', $this->bulanDari)
                  ->orWhereNull('tanggal')->orWhere('tanggal', '0000-00-00');
            });
        } elseif ($this->bulanSampai) {
            $query->where(function ($q) {
                $q->whereMonth('tanggal', '<=', $this->bulanSampai)
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

        $tahun = $this->tahun;

        return view('cash_bank.exportExcel.excelPenerima', compact('grouped', 'bulanNames', 'tahun'));
    }
}
