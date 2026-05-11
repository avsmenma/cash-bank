<?php

namespace App\Http\Controllers;
use App\Exports\ExcelPd;
use App\Exports\ExportExcelDashboardBank;
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
            DB::raw('SUM(nilai_inc_ppn) as total')
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

    // ========== PERMINTAAN ==========
    $permintaan = Permintaan::with(['kategori', 'subKriteria', 'itemSubKriteria'])
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
        'permintaan' => [],
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

    public function export_excel(Request $request){
        return Excel::download(
            new ExcelPd(
                $request->tahun,
                $request->bulan_dari,
                $request->bulan_sampai
            ),
            'Rekapan-PD .xlsx'
        );
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
        // WEEK RANGES: custom cutoff per bulan dari request
        // Default: W1=1-7, W2=8-14, W3=15-21, W4=22-akhir
        // ================================================================
        $weekRangesRaw = [];
        if ($request->has('week_ranges')) {
            $decoded = json_decode($request->week_ranges, true);
            if (is_array($decoded)) $weekRangesRaw = $decoded;
        }
        $weekCuts = [];
        foreach ($bulanAktif as $bNo => $_bNama) {
            $raw = $weekRangesRaw[$bNo] ?? [];
            $w1e = max(1, min(28, (int)($raw['w1_end'] ?? 7)));
            $w2e = max($w1e+1, min(28, (int)($raw['w2_end'] ?? 14)));
            $w3e = max($w2e+1, min(28, (int)($raw['w3_end'] ?? 21)));
            $weekCuts[$bNo] = [
                'w1_start' => 1,    'w1_end' => $w1e,
                'w2_start' => $w1e+1, 'w2_end' => $w2e,
                'w3_start' => $w2e+1, 'w3_end' => $w3e,
                'w4_start' => $w3e+1, 'w4_end' => 31,
            ];
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
        // Sheet PMK memakai formula seperti =SUM(Bayar!F1304)/1000,
        // jadi nilai bank keluar dikonversi ke satuan ribuan rupiah.
        // ================================================================
        $pembayaranRows = \App\Models\BankKeluar::with(['kategori','subKriteria','itemSubKriteria'])
            ->whereYear('tanggal', $tahun)
            ->whereBetween(DB::raw('MONTH(tanggal)'), [$bulanDari, $bulanSampai])
            ->whereNotNull('kredit')
            ->where('kredit', '!=', '')
            ->where('kredit', '!=', '0')
            ->get();

        $pembayaranData = []; // [bulan][kategori][sub][item] = weekTemplate

        foreach ($pembayaranRows as $row) {
            $b   = (int)\Carbon\Carbon::parse($row->tanggal)->month;
            $day = (int)\Carbon\Carbon::parse($row->tanggal)->day;
            $inferred = $this->inferModalKerjaPaymentPath($row);
            if (!$inferred) {
                continue;
            }

            [$k, $s, $i] = $inferred;
            $nilai = ((float)($row->kredit ?? 0)) / 1000;

            if (!isset($pembayaranData[$b][$k][$s][$i])) {
                $pembayaranData[$b][$k][$s][$i] = $weekTemplate;
            }

            $cuts = $weekCuts[$b] ?? ['w1_end'=>7,'w2_end'=>14,'w3_end'=>21];
            if      ($day <= $cuts['w1_end']) { $pembayaranData[$b][$k][$s][$i]['w1'] += $nilai; }
            elseif  ($day <= $cuts['w2_end']) { $pembayaranData[$b][$k][$s][$i]['w2'] += $nilai; }
            elseif  ($day <= $cuts['w3_end']) { $pembayaranData[$b][$k][$s][$i]['w3'] += $nilai; }
            else                              { $pembayaranData[$b][$k][$s][$i]['w4'] += $nilai; }
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

        // ================================================================
        // Urutkan allKeys sesuai referensi gambar:
        // Sub kriteria dan item kriteria diurutkan by priority
        // Item yg tidak ada di daftar ditempatkan di akhir (priority 999)
        // ================================================================
        $subPriority = [
            'Karyawan Pimpinan'  => 1,
            'Karyawan Pelaksana' => 2,
        ];

        $itemPriority = [
            'Gaji dan Tunjangan'        => 1,
            'Lembur'                    => 2,
            'Premi'                     => 3,
            'Cuti Tahunan'              => 4,
            'Cuti Panjang'              => 5,
            'T H R'                     => 6,
            'THR'                       => 6,
            'Bonus'                     => 7,
            'PPh pasal 21'              => 8,
            'PPh Pasal 21'              => 8,
            'Iuran Dapenbun (Normal)'   => 9,
            'Iuran Dapenbun (Tambahan)' => 10,
            'Penghargaan Masa Kerja'    => 11,
            'Iuran BPJS B. Perusahaan'  => 12,
            'SHT (Cicilan)'             => 13,
            'Lainnya'                   => 14,
        ];

        foreach ($allKeys as $kat => &$subs) {
            // Sort sub_kriteria
            uksort($subs, function($a, $b) use ($subPriority) {
                $pA = $subPriority[$a] ?? 999;
                $pB = $subPriority[$b] ?? 999;
                if ($pA !== $pB) return $pA <=> $pB;
                return strcmp($a, $b);
            });

            // Sort item_kriteria di tiap sub
            foreach ($subs as $sub => &$items) {
                uksort($items, function($a, $b) use ($itemPriority) {
                    $pA = $itemPriority[$a] ?? 999;
                    $pB = $itemPriority[$b] ?? 999;
                    if ($pA !== $pB) return $pA <=> $pB;
                    return strcmp($a, $b);
                });
            }
            unset($items);
        }
        unset($subs);

        return view('cash_bank.modalKerjaTable', compact(
            'permintaanData', 'droppingData', 'pembayaranData',
            'allKeys', 'bulanAktif', 'tahun', 'bulanMap',
            'bulanDari', 'bulanSampai', 'weekCuts'
        ));
    }

    private function inferModalKerjaPaymentPath($row): ?array
    {
        $kategori = trim((string)($row->kategori->nama_kriteria ?? ''));
        $sub = trim((string)($row->subKriteria->nama_sub_kriteria ?? ''));
        $item = trim((string)($row->itemSubKriteria->nama_item_sub_kriteria ?? ''));

        if ($kategori !== '' && $sub !== '' && $item !== '') {
            return [$kategori, $sub, $item];
        }

        $text = $this->normalizeModalKerjaText(
            implode(' ', [
                $row->uraian ?? '',
                $row->penerima ?? '',
                $row->keterangan ?? '',
                $row->jenis_pembayaran ?? '',
            ])
        );

        if ($text === '') {
            return null;
        }

        $gajiKategori = 'Kebutuhan Gaji, Upah dan Tunjangan';
        $pimpinan = 'Karyawan Pimpinan';
        $pelaksana = 'Karyawan Pelaksana';

        if (str_contains($text, 'bpjs')) {
            $sub = (str_contains($text, 'pensiunan') || str_contains($text, 'gol iii') || str_contains($text, 'gol iv'))
                ? $pimpinan
                : $pelaksana;

            return [$gajiKategori, $sub, 'Iuran BPJS B. Perusahaan'];
        }

        if (str_contains($text, 'dapenbun')) {
            $sub = str_contains($text, 'pimpinan') ? $pimpinan : $pelaksana;
            $item = str_contains($text, 'tambahan')
                ? 'Iuran Dapenbun (Tambahan)'
                : 'Iuran Dapenbun (Normal)';

            return [$gajiKategori, $sub, $item];
        }

        if (str_contains($text, 'sht')) {
            return [$gajiKategori, $pelaksana, 'SHT (Cicilan)'];
        }

        if (str_contains($text, 'pph pasal 21') || str_contains($text, 'pph 21')) {
            $sub = str_contains($text, 'pimpinan') ? $pimpinan : $pelaksana;
            return [$gajiKategori, $sub, 'PPh pasal 21'];
        }

        if (str_contains($text, 'pakaian dinas')) {
            return [$gajiKategori, $pelaksana, 'Lainnya'];
        }

        if (str_contains($text, 'gaji') || str_contains($text, 'tunjangan') || str_contains($text, 'lembur') || str_contains($text, 'premi') || str_contains($text, 'cuti')) {
            $sub = (str_contains($text, 'pimpinan') || str_contains($text, 'gol iii') || str_contains($text, 'gol iv'))
                ? $pimpinan
                : $pelaksana;

            if (str_contains($text, 'lembur')) {
                return [$gajiKategori, $sub, 'Lembur'];
            }
            if (str_contains($text, 'premi')) {
                return [$gajiKategori, $sub, 'Premi'];
            }
            if (str_contains($text, 'cuti tahunan')) {
                return [$gajiKategori, $sub, 'Cuti Tahunan'];
            }
            if (str_contains($text, 'cuti panjang')) {
                return [$gajiKategori, $sub, 'Cuti Panjang'];
            }

            return [$gajiKategori, $sub, 'Gaji dan Tunjangan'];
        }

        $biayaKategori = 'Payment Requirement for Exploitation Activity';
        $biayaUsaha = 'Biaya Usaha dan Lainnya';

        $biayaMap = [
            'csr' => 'Biaya CSR',
            'keamanan' => 'Biaya Keamanan',
            'konsultan' => 'Biaya Konsultan (DAPN)',
            'media' => 'Biaya Media',
            'pelabuhan' => 'Biaya Pelabuhan',
            'pemasaran' => 'Biaya Pemasaran Lainnya',
            'pemeliharaan bangunan' => 'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi (DINP)',
            'perlengkapan kantor' => 'Biaya Pemeliharaan Perlengkapan Kantor',
            'pendidikan' => 'Biaya Pendidikan dan Pengembangan SDM',
            'pengangkutan' => 'Biaya Pengangkutan, Perjalanan & Penginapan',
            'pengendalian lingkungan' => 'Biaya Pengendalian Lingkungan (ISO 14000)',
            'pengiriman ke pelabuhan' => 'Biaya Pengiriman ke Pelabuhan (CPO)',
            'sumbangan' => 'Biaya Sumbangan dan Iuran',
            'telekomunikasi' => 'Biaya Telekomunikasi dan Ekspedisi',
            'atk' => 'Utilities (Air, Listrik, ATK, Brg Umum, Sewa Kantor)',
            'listrik' => 'Utilities (Air, Listrik, ATK, Brg Umum, Sewa Kantor)',
            'sewa kantor' => 'Utilities (Air, Listrik, ATK, Brg Umum, Sewa Kantor)',
        ];

        foreach ($biayaMap as $needle => $mappedItem) {
            if (str_contains($text, $needle)) {
                return [$biayaKategori, $biayaUsaha, $mappedItem];
            }
        }

        return null;
    }

    private function normalizeModalKerjaText(?string $value): string
    {
        $value = strtolower((string)$value);
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    public function bank(Request $request)
    {
        // Ambil semua sumber dana beserta saldo VA-nya
        // Saldo VA = SUM(bank_masuk.debet) - SUM(bank_keluars.kredit) per sumber_dana
        $sumberDanaList = DB::table('sumber_dana')
            ->select('sumber_dana.id_sumber_dana', 'sumber_dana.nama_sumber_dana')
            ->selectRaw('COALESCE((SELECT SUM(debet) FROM bank_masuk WHERE bank_masuk.id_sumber_dana = sumber_dana.id_sumber_dana), 0) as total_masuk')
            ->selectRaw('COALESCE((SELECT SUM(kredit) FROM bank_keluars WHERE bank_keluars.id_sumber_dana = sumber_dana.id_sumber_dana), 0) as total_keluar')
            ->orderBy('sumber_dana.id_sumber_dana')
            ->get()
            ->map(function ($sd) {
                $sd->saldo_va = (float) $sd->total_masuk - (float) $sd->total_keluar;
                return $sd;
            });

        $totalSaldoBank = $sumberDanaList->sum('saldo_va');

        // Ambil saldo per Bank Virtual Account (bank_tujuan)
        $bankVAList = DB::table('bank_tujuan')
            ->select('bank_tujuan.id_bank_tujuan', 'bank_tujuan.nama_tujuan')
            ->selectRaw('COALESCE((SELECT SUM(debet) FROM bank_masuk WHERE bank_masuk.id_bank_tujuan = bank_tujuan.id_bank_tujuan), 0) as total_masuk')
            ->selectRaw('COALESCE((SELECT SUM(kredit) FROM bank_keluars WHERE bank_keluars.id_bank_tujuan = bank_tujuan.id_bank_tujuan), 0) as total_keluar')
            ->orderBy('bank_tujuan.nama_tujuan')
            ->get()
            ->map(function ($va) {
                $va->saldo = (float) $va->total_masuk - (float) $va->total_keluar;
                return $va;
            });

        $totalSaldoVA = $bankVAList->sum('saldo');

        // Cari saldo Rek 408 (Bank Mandiri OPEX, no. rek mengandung '9702740-8')
        $saldoRek408 = 0;
        $noRek408 = '';
        $namaRek408 = '';
        foreach ($sumberDanaList as $sd) {
            if (preg_match('/9702740/', $sd->nama_sumber_dana)) {
                $saldoRek408 = $sd->saldo_va;
                // Ambil no rek dari nama_sumber_dana
                if (preg_match('/\*\s*([\d\-\/]+)\s*$/', $sd->nama_sumber_dana, $m)) {
                    $noRek408 = trim($m[1]);
                }
                $namaRek408 = $sd->nama_sumber_dana;
                break;
            }
        }

        // 3 digit terakhir dari no. rek (misal: 146-00-9702740-8 → 408)
        $digitAkhirRek = '';
        if ($noRek408) {
            $angkaSaja = preg_replace('/[^0-9]/', '', $noRek408);
            $digitAkhirRek = substr($angkaSaja, -3);
        }

        // Saldo Rek 408 yang digunakan region = Saldo Rek 408 - Total Saldo VA
        $saldoRegion = $saldoRek408 - $totalSaldoVA;

        return view('cash_bank.dashboardBank', compact(
            'sumberDanaList',
            'totalSaldoBank',
            'bankVAList',
            'totalSaldoVA',
            'saldoRek408',
            'noRek408',
            'digitAkhirRek',
            'saldoRegion'
        ));
    }
    public function bankExportExcel(Request $request)
    {
        $tanggal = $request->tanggal ?? 'Pontianak, ' . \Carbon\Carbon::now()->translatedFormat('d F Y');
        $nama    = $request->nama    ?? 'Herry Wahyudi';
        $jabatan = $request->jabatan ?? 'Kepala Bagian Akuntansi & Keuangan';

        return Excel::download(
            new ExportExcelDashboardBank($tanggal, $nama, $jabatan),
            'Saldo-Kas-Bank.xlsx'
        );
    }

    public function bankExportPdf(Request $request)
    {
        $tanggal = $request->tanggal ?? 'Pontianak, ' . \Carbon\Carbon::now()->translatedFormat('d F Y');
        $nama    = $request->nama    ?? 'Herry Wahyudi';
        $jabatan = $request->jabatan ?? 'Kepala Bagian Akuntansi & Keuangan';

        $sumberDanaList = DB::table('sumber_dana')
            ->select('sumber_dana.id_sumber_dana', 'sumber_dana.nama_sumber_dana')
            ->selectRaw('COALESCE((SELECT SUM(debet) FROM bank_masuk WHERE bank_masuk.id_sumber_dana = sumber_dana.id_sumber_dana), 0) as total_masuk')
            ->selectRaw('COALESCE((SELECT SUM(kredit) FROM bank_keluars WHERE bank_keluars.id_sumber_dana = sumber_dana.id_sumber_dana), 0) as total_keluar')
            ->orderBy('sumber_dana.id_sumber_dana')
            ->get()
            ->map(function ($sd) {
                $sd->saldo_va = (float) $sd->total_masuk - (float) $sd->total_keluar;
                return $sd;
            });

        $totalSaldoBank = $sumberDanaList->sum('saldo_va');

        return view('cash_bank.exportPDF.pdfDashboardBank', compact(
            'sumberDanaList',
            'totalSaldoBank',
            'tanggal',
            'nama',
            'jabatan'
        ));
    }
}
