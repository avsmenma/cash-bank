<?php

namespace App\Http\Controllers;

use App\Models\Dropping;
use App\Models\Penerima;
use App\Models\RencanaDropping;
use App\Models\RencanaPenerima;
use App\Models\KategoriKriteria;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardPembayaranController extends Controller
{
    /**
     * Tampilkan halaman utama dashboard
     */
    public function index()
    {
        $tahun = request('tahun', date('Y'));

        // Ambil daftar kategori untuk filter
        $kategoriList = KategoriKriteria::pluck('nama_kriteria', 'id_kategori_kriteria');

        $bulanList = [
            'januari'=>1,'februari'=>2,'maret'=>3,'april'=>4,'mei'=>5,'juni'=>6,
            'juli'=>7,'agustus'=>8,'september'=>9,'oktober'=>10,'november'=>11,'desember'=>12
        ];

        // Load data awal
        $data = $this->getData($tahun);

        return view('cash_bank.dashboardPembayaran', array_merge([
            'bulanList' => $bulanList,
            'kategoriList' => $kategoriList,
        ], $data));
    }

    /**
     * Get data untuk AJAX request
     */
    public function getData($tahun = null, $kategoriFilter = null, $subFilter = null, $itemFilter = null)
    {
        $tahun = $tahun ?? request('tahun', date('Y'));
        $kategoriFilter = $kategoriFilter ?? request('kategori');
        $subFilter = $subFilter ?? request('sub');
        $itemFilter = $itemFilter ?? request('item');

        $bulanList = [
            'januari'=>1,'februari'=>2,'maret'=>3,'april'=>4,'mei'=>5,'juni'=>6,
            'juli'=>7,'agustus'=>8,'september'=>9,'oktober'=>10,'november'=>11,'desember'=>12
        ];

        /* ===================== DETAIL PENERIMA (KATEGORI) ===================== */
        $dataPenerima = [];

        $queryPenerima = Penerima::select('id_kategori_kriteria')->distinct();
        
        if ($kategoriFilter) {
            $queryPenerima->where('id_kategori_kriteria', $kategoriFilter);
        }
        
        $kategoriPenerima = $queryPenerima->pluck('id_kategori_kriteria');

        foreach ($kategoriPenerima as $kategori) {
            foreach ($bulanList as $b => $no) {
                $rencana = RencanaPenerima::where('tahun', $tahun)
                    ->where('id_kategori_kriteria', $kategori)
                    ->value($b) ?? 0;

                $nilai = Penerima::where('id_kategori_kriteria', $kategori)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $no)
                    ->sum('nilai') ?? 0;

                $ppn = Penerima::where('id_kategori_kriteria', $kategori)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $no)
                    ->sum('ppn') ?? 0;

                $pot = Penerima::where('id_kategori_kriteria', $kategori)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $no)
                    ->sum('potppn') ?? 0;

                $realisasi = $nilai + $ppn - $pot;
                $selisih = $realisasi - $rencana;
                $persen  = $rencana > 0 ? ($realisasi / $rencana) * 100 : 0;

                $dataPenerima[$kategori][$b] = compact('rencana','realisasi','selisih','persen');
            }
        }

        /* ===================== DETAIL DROPPING (KATEGORI > SUB > ITEM) ===================== */
        $dataDropping = [];

        $queryDropping = Dropping::select('id_kategori_kriteria','id_sub_kriteria','id_item_sub_kriteria')
            ->distinct();

        if ($kategoriFilter) {
            $queryDropping->where('id_kategori_kriteria', $kategoriFilter);
        }
        if ($subFilter) {
            $queryDropping->where('id_sub_kriteria', $subFilter);
        }
        if ($itemFilter) {
            $queryDropping->where('id_item_sub_kriteria', $itemFilter);
        }

        $items = $queryDropping->get();

        foreach ($items as $row) {
            $kategori = $row->id_kategori_kriteria;
            $sub = $row->id_sub_kriteria;
            $item = $row->id_item_sub_kriteria;

            foreach ($bulanList as $b => $no) {
                $rencana = RencanaDropping::where('tahun', $tahun)
                    ->where('id_kategori_kriteria', $kategori)
                    ->where('id_sub_kriteria', $sub)
                    ->where('id_item_sub_kriteria', $item)
                    ->value($b) ?? 0;

                $realisasi = Dropping::where('id_kategori_kriteria', $kategori)
                    ->where('id_sub_kriteria', $sub)
                    ->where('id_item_sub_kriteria', $item)
                    ->where('tahun', $tahun)
                    ->where('bulan', $no)
                    ->sum(DB::raw('M1 + M2 + M3 + M4')) ?? 0;

                $selisih = $realisasi - $rencana;
                $persen  = $rencana > 0 ? ($realisasi / $rencana) * 100 : 0;

                $dataDropping[$kategori][$sub][$item][$b] = compact('rencana','realisasi','selisih','persen');
            }
        }

        /* ===================== TOTAL BULANAN ===================== */
        $totalPenerima = [];
        $totalDropping = [];

        foreach ($bulanList as $b => $no) {
            $queryTotalPenerima = RencanaPenerima::where('tahun', $tahun);
            if ($kategoriFilter) {
                $queryTotalPenerima->where('id_kategori_kriteria', $kategoriFilter);
            }
            $tp = $queryTotalPenerima->sum($b) ?? 0;

            $queryRealisasiPenerima = Penerima::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $no);
            if ($kategoriFilter) {
                $queryRealisasiPenerima->where('id_kategori_kriteria', $kategoriFilter);
            }
            $rp = $queryRealisasiPenerima->sum(DB::raw('nilai + ppn - potppn')) ?? 0;

            $queryTotalDropping = RencanaDropping::where('tahun', $tahun);
            if ($kategoriFilter) {
                $queryTotalDropping->where('id_kategori_kriteria', $kategoriFilter);
            }
            if ($subFilter) {
                $queryTotalDropping->where('id_sub_kriteria', $subFilter);
            }
            if ($itemFilter) {
                $queryTotalDropping->where('id_item_sub_kriteria', $itemFilter);
            }
            $td = $queryTotalDropping->sum($b) ?? 0;

            $queryRealisasiDropping = Dropping::where('tahun', $tahun)
                ->where('bulan', $no);
            if ($kategoriFilter) {
                $queryRealisasiDropping->where('id_kategori_kriteria', $kategoriFilter);
            }
            if ($subFilter) {
                $queryRealisasiDropping->where('id_sub_kriteria', $subFilter);
            }
            if ($itemFilter) {
                $queryRealisasiDropping->where('id_item_sub_kriteria', $itemFilter);
            }
            $rd = $queryRealisasiDropping->sum(DB::raw('M1 + M2 + M3 + M4')) ?? 0;

            $totalPenerima[$b] = ['rencana' => $tp, 'realisasi' => $rp];
            $totalDropping[$b] = ['rencana' => $td, 'realisasi' => $rd];
        }

        return [
            'bulanList' => $bulanList,
            'penerima' => $dataPenerima,
            'dropping' => $dataDropping,
            'totalPenerima' => $totalPenerima,
            'totalDropping' => $totalDropping,
        ];
    }

    /**
     * AJAX: Load tabel data
     */
    public function loadData(Request $request)
    {
        $data = $this->getData(
            $request->tahun,
            $request->kategori,
            $request->sub,
            $request->item
        );

        return view('cash_bank.pembayaran.dashboardPembayaran', $data);
    }

    /**
     * AJAX: Get sub kriteria berdasarkan kategori
     */
    public function getSubKriteria(Request $request)
    {
        $subs = SubKriteria::where('id_kategori_kriteria', $request->kategori_id)
            ->pluck('nama_sub_kriteria', 'id_sub_kriteria');

        return response()->json($subs);
    }

    /**
     * AJAX: Get item berdasarkan kategori dan sub
     */
    public function getItem(Request $request)
    {
        $items = ItemSubKriteria::where('id_kategori_kriteria', $request->kategori_id)
            ->where('id_sub_kriteria', $request->sub_id)
            ->pluck('nama_item_sub_kriteria', 'id_item_sub_kriteria');

        return response()->json($items);
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getData(
            $request->tahun,
            $request->kategori,
            $request->sub,
            $request->item
        );

        $pdf = \PDF::loadView('cash_bank.pembayaran.pdf', $data);
        return $pdf->download('dashboard-pembayaran-'.$request->tahun.'.pdf');
    }

    /**
     * Export Excel
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getData(
            $request->tahun,
            $request->kategori,
            $request->sub,
            $request->item
        );

        return \Excel::download(
            new \App\Exports\DashboardPembayaranExport($data), 
            'dashboard-pembayaran-'.$request->tahun.'.xlsx'
        );
    }
}