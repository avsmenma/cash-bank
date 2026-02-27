<?php

namespace App\Http\Controllers;
use App\Exports\ExcelPd;
use App\Models\Dropping;
use App\Models\Penerima;
use App\Exports\ExcelPvd;
use App\Models\BankMasuk;
use App\Models\BankKeluar;
use App\Models\Permintaan;
use Illuminate\Http\Request;
use App\Models\KategoriKriteria;
use Illuminate\Support\Facades\DB;
use App\Models\GabunganMasukKeluar;
use Maatwebsite\Excel\Facades\Excel;

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

        $result['penerima'][$kategori][$bulan] += $p->total / 1000;
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
    $bulanListFiltered = [];
    if (!empty($bulanAktif)) {
        foreach ($bulanList as $noBulan => $namaBulan) {
            if ($noBulan >= $bulanDari && $noBulan <= $bulanSampai && isset($bulanAktif[$noBulan])) {
                $bulanListFiltered[$noBulan] = $namaBulan;
            }
        }
    }

    // Jika request AJAX, return hanya content
    if ($request->ajax || $request->has('ajax')) {
        return view('cash_bank.dashbordPertama', compact('result', 'bulanListFiltered', 'tahun'));
    }

    // Jika bukan AJAX, return full page
    return view('cash_bank.dashboard', compact('result', 'bulanListFiltered', 'tahun', 'bulanDari', 'bulanSampai'));
}


    public function data2(Request $request)
    {
        $bulanList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
        ];
        
        $tahun = $request->tahunPvd ?? date('Y');
        $bulanDari = $request->bulan_dariPvd ?? 1;
        $bulanSampai = $request->bulan_sampaiPvd ?? 12;

        // ========== PERMINTAAN ==========
        $permintaan = Permintaan::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [$bulanDari, $bulanSampai])
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
            'permintaan' => [],
            'dropping' => [],
            'pembayaran' => []
        ];
        
        $bulanAktif = [];

        // ========== PROSES PERMINTAAN ==========
        foreach ($permintaan as $p) {
            $kategori = $p->kategori->nama_kriteria ?? '-';
            $subKriteria = $p->subKriteria->nama_sub_kriteria ?? '-';
            $itemKriteria = $p->itemSubKriteria->nama_item_sub_kriteria ?? '-';
            $bulan = $p->bulan;
            
            $key = $kategori . '|' . $subKriteria . '|' . $itemKriteria;
            
            if (!isset($result['permintaan'][$key])) {
                $result['permintaan'][$key] = [
                    'kategori' => $kategori,
                    'sub_kriteria' => $subKriteria,
                    'item_kriteria' => $itemKriteria,
                    'data' => []
                ];
                for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
                    $result['permintaan'][$key]['data'][$b] = 0;
                }
            }

            $total = $p->M1 + $p->M2 + $p->M3 + $p->M4;
            $result['permintaan'][$key]['data'][$bulan] += $total;
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
        $bulanListFiltered = [];
        if (!empty($bulanAktif)) {
            foreach ($bulanList as $noBulan => $namaBulan) {
                if ($noBulan >= $bulanDari && $noBulan <= $bulanSampai && isset($bulanAktif[$noBulan])) {
                    $bulanListFiltered[$noBulan] = $namaBulan;
                }
            }
        }

        // Jika request AJAX, return hanya content
        if ($request->ajax || $request->has('ajax')) {
            return view('cash_bank.dashbordKedua', compact('result', 'bulanListFiltered', 'tahun'));
        }


        return view('cash_bank.dashboard');
    }

    public function detailPvd(){
        // permintaan  
        $bulanList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
        ];
        
        $tahun = $request->tahunPvd ?? date('Y');
        // ========== PERMINTAAN ==========
        $permintaan = Permintaan::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->select('M1','M2','M3','M4')
            ->where('tahun', $tahun)
            ->where('bulan', $bulanList)
            ->get();

        // ========== DROPPING ==========
        $dropping = Dropping::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->select('M1','M2','M3','M4')
            ->where('tahun', $tahun)
            ->where('bulan', $bulanList)
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
    }

    public function export_excel(){
        return Excel::download(new ExcelPd, 'Rekapan-PD .xlsx');
    }
   public function export_excelPvd(Request $request)
    {
        return Excel::download(
            new ExcelPvd(
                $request->tahunPvd,
                $request->bulan_dariPvd,
                $request->bulan_sampaiPvd
            ),
            'Rekapan-PVD.xlsx'
        );
    }

    public function view_pdf(Request $request)
    {
     $bulanList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
        ];
        
        $tahun = $request->tahunPvd ?? date('Y');
        $bulanDari = $request->bulan_dariPvd ?? 1;
        $bulanSampai = $request->bulan_sampaiPvd ?? 12;

        // ========== PERMINTAAN ==========
        $permintaan = Permintaan::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [$bulanDari, $bulanSampai])
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
            'permintaan' => [],
            'dropping' => [],
            'pembayaran' => []
        ];
        
        $bulanAktif = [];

        // ========== PROSES PERMINTAAN ==========
        foreach ($permintaan as $p) {
            $kategori = $p->kategori->nama_kriteria ?? '-';
            $subKriteria = $p->subKriteria->nama_sub_kriteria ?? '-';
            $itemKriteria = $p->itemSubKriteria->nama_item_sub_kriteria ?? '-';
            $bulan = $p->bulan;
            
            $key = $kategori . '|' . $subKriteria . '|' . $itemKriteria;
            
            if (!isset($result['permintaan'][$key])) {
                $result['permintaan'][$key] = [
                    'kategori' => $kategori,
                    'sub_kriteria' => $subKriteria,
                    'item_kriteria' => $itemKriteria,
                    'data' => []
                ];
                for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
                    $result['permintaan'][$key]['data'][$b] = 0;
                }
            }

            $total = $p->M1 + $p->M2 + $p->M3 + $p->M4;
            $result['permintaan'][$key]['data'][$bulan] += $total;
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
        $bulanListFiltered = [];
        if (!empty($bulanAktif)) {
            foreach ($bulanList as $noBulan => $namaBulan) {
                if ($noBulan >= $bulanDari && $noBulan <= $bulanSampai && isset($bulanAktif[$noBulan])) {
                    $bulanListFiltered[$noBulan] = $namaBulan;
                }
            }
        }

        // Jika request AJAX, return hanya content
        if ($request->ajax || $request->has('ajax')) {
            return view('cash_bank.dashbordKedua', compact('result', 'bulanListFiltered', 'tahun'));
        }


        return view('cash_bank.dashboard');
    }

    public function modalKerja(Request $request)
    {
        $bulanList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
        ];

        $tahun     = $request->tahun      ?? date('Y');
        $bulanDari = $request->bulan_dari ?? 1;
        $bulanSampai = $request->bulan_sampai ?? date('m');

        if ($request->ajax || $request->has('ajax')) {
            return $this->modalKerjaData($request);
        }

        return view('cash_bank.modalKerja', compact('tahun', 'bulanList', 'bulanDari', 'bulanSampai'));
    }

    public function modalKerjaData(Request $request)
    {
        $bulanMap = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
        ];

        $tahun       = $request->tahun       ?? date('Y');
        $bulanDari   = (int)($request->bulan_dari   ?? 1);
        $bulanSampai = (int)($request->bulan_sampai ?? date('m'));

        // Template minggu per bulan
        $weekTemplate = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];

        // Bulan aktif sesuai filter
        $bulanAktif = [];
        for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
            $bulanAktif[$b] = $bulanMap[$b];
        }

        // ================================================================
        // PERMINTAAN (Rencana Mingguan) - M1, M2, M3, M4 = W1..W4
        // ================================================================
        $permintaanRows = \App\Models\Permintaan::with(['kategori','subKriteria','itemSubKriteria'])
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [$bulanDari, $bulanSampai])
            ->get();

        $permintaanData = []; // [bulan][kategori][sub][item] = weekTemplate

        foreach ($permintaanRows as $row) {
            $b   = (int)$row->bulan;
            $k   = $row->kategori->nama_kriteria          ?? '-';
            $s   = $row->subKriteria->nama_sub_kriteria   ?? '-';
            $i   = $row->itemSubKriteria->nama_item_sub_kriteria ?? '-';

            if (!isset($permintaanData[$b][$k][$s][$i])) {
                $permintaanData[$b][$k][$s][$i] = $weekTemplate;
            }
            $permintaanData[$b][$k][$s][$i]['w1'] += $row->M1 ?? 0;
            $permintaanData[$b][$k][$s][$i]['w2'] += $row->M2 ?? 0;
            $permintaanData[$b][$k][$s][$i]['w3'] += $row->M3 ?? 0;
            $permintaanData[$b][$k][$s][$i]['w4'] += $row->M4 ?? 0;
        }

        // ================================================================
        // DROPPING (Realisasi Mingguan) - M1, M2, M3, M4 = W1..W4
        // ================================================================
        $droppingRows = \App\Models\Dropping::with(['kategori','subKriteria','itemSubKriteria'])
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [$bulanDari, $bulanSampai])
            ->get();

        $droppingData = []; // [bulan][kategori][sub][item] = weekTemplate

        foreach ($droppingRows as $row) {
            $b   = (int)$row->bulan;
            $k   = $row->kategori->nama_kriteria          ?? '-';
            $s   = $row->subKriteria->nama_sub_kriteria   ?? '-';
            $i   = $row->itemSubKriteria->nama_item_sub_kriteria ?? '-';

            if (!isset($droppingData[$b][$k][$s][$i])) {
                $droppingData[$b][$k][$s][$i] = $weekTemplate;
            }
            $droppingData[$b][$k][$s][$i]['w1'] += $row->M1 ?? 0;
            $droppingData[$b][$k][$s][$i]['w2'] += $row->M2 ?? 0;
            $droppingData[$b][$k][$s][$i]['w3'] += $row->M3 ?? 0;
            $droppingData[$b][$k][$s][$i]['w4'] += $row->M4 ?? 0;
        }

        // ================================================================
        // PEMBAYARAN (BankKeluar) - breakdown by tanggal into weeks
        // ================================================================
        $pembayaranRows = \App\Models\BankKeluar::with(['kategori','subKriteria','itemSubKriteria'])
            ->whereYear('tanggal', $tahun)
            ->whereBetween(DB::raw('MONTH(tanggal)'), [$bulanDari, $bulanSampai])
            ->whereNotNull('kredit')
            ->where('kredit', '!=', '')
            ->where('kredit', '!=', '0')
            ->whereNotNull('id_kategori_kriteria')
            ->whereNotNull('id_sub_kriteria')
            ->whereNotNull('id_item_sub_kriteria')
            ->get();

        $pembayaranData = []; // [bulan][kategori][sub][item] = weekTemplate

        foreach ($pembayaranRows as $row) {
            $b   = (int)\Carbon\Carbon::parse($row->tanggal)->month;
            $day = (int)\Carbon\Carbon::parse($row->tanggal)->day;
            $k   = $row->kategori->nama_kriteria          ?? '-';
            $s   = $row->subKriteria->nama_sub_kriteria   ?? '-';
            $i   = $row->itemSubKriteria->nama_item_sub_kriteria ?? '-';
            $nilai = (float)($row->kredit ?? 0);

            if (!isset($pembayaranData[$b][$k][$s][$i])) {
                $pembayaranData[$b][$k][$s][$i] = $weekTemplate;
            }

            if      ($day <= 7)  { $pembayaranData[$b][$k][$s][$i]['w1'] += $nilai; }
            elseif  ($day <= 14) { $pembayaranData[$b][$k][$s][$i]['w2'] += $nilai; }
            elseif  ($day <= 21) { $pembayaranData[$b][$k][$s][$i]['w3'] += $nilai; }
            else                 { $pembayaranData[$b][$k][$s][$i]['w4'] += $nilai; }
        }

        // ================================================================
        // Bangun daftar baris terurut (superset dari semua section)
        // struktur: [kategori][sub][item] => ada/tidak
        // ================================================================
        $allKeys = [];
        foreach ([$permintaanData, $droppingData, $pembayaranData] as $section) {
            foreach ($section as $bulanRows) {
                foreach ($bulanRows as $k => $subs) {
                    foreach ($subs as $s => $items) {
                        foreach ($items as $i => $_) {
                            $allKeys[$k][$s][$i] = true;
                        }
                    }
                }
            }
        }

        return view('cash_bank.modalKerjaTable', compact(
            'permintaanData', 'droppingData', 'pembayaranData',
            'allKeys', 'bulanAktif', 'tahun', 'bulanMap',
            'bulanDari', 'bulanSampai'
        ));
    }

}