<?php

namespace App\Http\Controllers;

use App\Models\Dropping;
use App\Models\Penerima;
use App\Models\RencanaDropping;
use App\Models\RencanaPenerima;
use Illuminate\Support\Facades\DB;

class DashboardPembayaranController extends Controller
{
    public function index()
    {
        $tahun = request('tahun', date('Y'));

        $bulanList = [
            'januari'=>1,'februari'=>2,'maret'=>3,'april'=>4,'mei'=>5,'juni'=>6,
            'juli'=>7,'agustus'=>8,'september'=>9,'oktober'=>10,'november'=>11,'desember'=>12
        ];

        /* ===================== DETAIL PENERIMA (KATEGORI) ===================== */
        $dataPenerima = [];

        // Ambil semua kategori yang ada di penerima
        $kategoriPenerima = Penerima::select('id_kategori_kriteria')
            ->distinct()
            ->pluck('id_kategori_kriteria');

        foreach ($kategoriPenerima as $kategori) {
            foreach ($bulanList as $b => $no) {
                // Rencana penerima untuk kategori dan bulan tertentu
                $rencana = RencanaPenerima::where('tahun', $tahun)
                    ->where('id_kategori_kriteria', $kategori)
                    ->value($b) ?? 0;

                // Realisasi penerima untuk kategori dan bulan tertentu
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

        // Ambil semua kombinasi kategori, sub, dan item yang ada
        $items = Dropping::select('id_kategori_kriteria','id_sub_kriteria','id_item_sub_kriteria')
            ->distinct()
            ->get();

        foreach ($items as $row) {
            $kategori = $row->id_kategori_kriteria;
            $sub = $row->id_sub_kriteria;
            $item = $row->id_item_sub_kriteria;

            foreach ($bulanList as $b => $no) {
                // Rencana dropping untuk kombinasi kategori, sub, item, dan bulan tertentu
                $rencana = RencanaDropping::where('tahun', $tahun)
                    ->where('id_kategori_kriteria', $kategori)
                    ->where('id_sub_kriteria', $sub)
                    ->where('id_item_sub_kriteria', $item)
                    ->value($b) ?? 0;

                // Realisasi dropping untuk kombinasi kategori, sub, item, dan bulan tertentu
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
            // Total rencana penerima per bulan
            $tp = RencanaPenerima::where('tahun', $tahun)
                ->sum($b) ?? 0;

            // Total realisasi penerima per bulan
            $rp = Penerima::whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $no)
                ->sum(DB::raw('nilai + ppn - potppn')) ?? 0;

            // Total rencana dropping per bulan
            $td = RencanaDropping::where('tahun', $tahun)
                ->sum($b) ?? 0;

            // Total realisasi dropping per bulan
            $rd = Dropping::where('tahun', $tahun)
                ->where('bulan', $no)
                ->sum(DB::raw('M1 + M2 + M3 + M4')) ?? 0;

            $totalPenerima[$b] = ['rencana' => $tp, 'realisasi' => $rp];
            $totalDropping[$b] = ['rencana' => $td, 'realisasi' => $rd];
        }

        return view('cash_bank.dashboardPembayaran', [
            'bulanList'     => $bulanList,
            'penerima'      => $dataPenerima,
            'dropping'      => $dataDropping,
            'totalPenerima' => $totalPenerima,
            'totalDropping' => $totalDropping,
        ]);
    }
}