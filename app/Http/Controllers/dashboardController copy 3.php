<?php

namespace App\Http\Controllers;

use App\Models\Dropping;
use App\Models\Penerima;
use App\Models\BankMasuk;
use App\Models\BankKeluar;
use Illuminate\Http\Request;
use App\Models\KategoriKriteria;
use Illuminate\Support\Facades\DB;
use App\Models\GabunganMasukKeluar;

class dashboardController extends Controller
{
    public function index(Request $request)
    {
        $bulanList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
        ];
        
        $tahun = $request->tahun ?? date('Y');
        $bulanDari = $request->bulan_dari ?? 1;
        $bulanSampai = $request->bulan_sampai ?? 12;

        // ========== PENERIMA ==========
        $penerima = Penerima::with('kategori')
            ->select(
                'id_kategori_kriteria',
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(nilai + ppn - potppn) as total')
            )
            ->whereYear('tanggal', $tahun)
            ->whereBetween(DB::raw('MONTH(tanggal)'), [$bulanDari, $bulanSampai])
            ->groupBy('id_kategori_kriteria', DB::raw('MONTH(tanggal)'))
            ->get();

        // ========== DROPPING ==========
        $dropping = Dropping::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [$bulanDari, $bulanSampai])
            ->get();

        // ========== PEMBAYARAN (BANK KELUAR) ==========
        $pembayaran = BankKeluar::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->select(
                'id_kategori_kriteria',
                'id_sub_kriteria',
                'id_item_sub_kriteria',
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(CAST(kredit AS DECIMAL(15,2))) as total')
            )
            ->whereYear('tanggal', $tahun)
            ->whereBetween(DB::raw('MONTH(tanggal)'), [$bulanDari, $bulanSampai])
            ->whereNotNull('kredit')
            ->where('kredit', '!=', '')
            ->where('kredit', '!=', '0')
            ->whereNotNull('id_kategori_kriteria')
            ->whereNotNull('id_sub_kriteria')
            ->whereNotNull('id_item_sub_kriteria')
            ->groupBy('id_kategori_kriteria', 'id_sub_kriteria', 'id_item_sub_kriteria', DB::raw('MONTH(tanggal)'))
            ->get();

        // Struktur data hasil
        $result = [
            'penerima' => [],
            'dropping' => [],
            'pembayaran' => []
        ];
        
        $bulanAktif = [];

        // ========== PROSES PENERIMA ==========
        foreach ($penerima as $p) {
            $kategori = $p->kategori->nama_kriteria ?? '-';
            $bulan = $p->bulan;

            if (!isset($result['penerima'][$kategori])) {
                $result['penerima'][$kategori] = [];
                for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
                    $result['penerima'][$kategori][$b] = 0;
                }
            }

            $result['penerima'][$kategori][$bulan] += $p->total;
            $bulanAktif[$bulan] = true;
        }

        // ========== PROSES DROPPING ==========
        foreach ($dropping as $d) {
            $kategori = $d->kategori->nama_kriteria ?? '-';
            $subKriteria = $d->subKriteria->nama_sub_kriteria ?? '-';
            $itemKriteria = $d->itemSubKriteria->nama_item_sub_kriteria ?? '-';
            $bulan = $d->bulan;
            
            $key = $kategori . '|' . $subKriteria . '|' . $itemKriteria;
            
            if (!isset($result['dropping'][$key])) {
                $result['dropping'][$key] = [
                    'kategori' => $kategori,
                    'sub_kriteria' => $subKriteria,
                    'item_kriteria' => $itemKriteria,
                    'data' => []
                ];
                for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
                    $result['dropping'][$key]['data'][$b] = 0;
                }
            }

            $total = $d->M1 + $d->M2 + $d->M3 + $d->M4;
            $result['dropping'][$key]['data'][$bulan] += $total;
            $bulanAktif[$bulan] = true;
        }

        // ========== PROSES PEMBAYARAN ==========
        foreach ($pembayaran as $p) {
            $kategori = $p->kategori->nama_kriteria ?? '-';
            $subKriteria = $p->subKriteria->nama_sub_kriteria ?? '-';
            $itemKriteria = $p->itemSubKriteria->nama_item_sub_kriteria ?? '-';
            $bulan = $p->bulan;
            
            $key = $kategori . '|' . $subKriteria . '|' . $itemKriteria;
            
            if (!isset($result['pembayaran'][$key])) {
                $result['pembayaran'][$key] = [
                    'kategori' => $kategori,
                    'sub_kriteria' => $subKriteria,
                    'item_kriteria' => $itemKriteria,
                    'data' => []
                ];
                for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
                    $result['pembayaran'][$key]['data'][$b] = 0;
                }
            }

            $result['pembayaran'][$key]['data'][$bulan] += $p->total;
            $bulanAktif[$bulan] = true;
        }

        // Filter bulan
        if (empty($bulanAktif)) {
            $bulanListFiltered = array_filter($bulanList, function($namaBulan, $noBulan) use ($bulanDari, $bulanSampai) {
                return $noBulan >= $bulanDari && $noBulan <= $bulanSampai;
            }, ARRAY_FILTER_USE_BOTH);
        } else {
            $bulanListFiltered = array_filter($bulanList, function($namaBulan, $noBulan) use ($bulanAktif, $bulanDari, $bulanSampai) {
                return $noBulan >= $bulanDari 
                    && $noBulan <= $bulanSampai 
                    && isset($bulanAktif[$noBulan]);
            }, ARRAY_FILTER_USE_BOTH);
        }

        if (empty($bulanListFiltered)) {
            $bulanListFiltered = $bulanList;
        }

        if ($request->ajax()) {
            return view('cash_bank.dashbordPertama', compact('result', 'bulanListFiltered', 'tahun', 'bulanDari', 'bulanSampai'));
        }

        return view('cash_bank.dashboard', compact('result', 'bulanListFiltered', 'tahun', 'bulanDari', 'bulanSampai'));
    }
}