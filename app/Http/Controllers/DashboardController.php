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
use App\Support\DashboardKriteriaHierarchy;
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
    // Dibagi 1000: nilai_inc_ppn tersimpan rupiah penuh, sedangkan Permintaan &
    // Dropping (M1-M4) tersimpan dalam ribuan — tanpa ini tabel PENERIMAAN dan
    // baris SELISIH mencampur satuan.
    $penerima = Penerima::with('kategori')
        ->select(
            'id_kategori_kriteria',
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(nilai_inc_ppn) / 1000 as total')
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
    // Dibagi 1000: kredit tersimpan rupiah penuh — samakan ke ribuan seperti
    // Permintaan/Dropping agar baris SELISIH konsisten satuannya.
    $pembayaran = BankKeluar::with(['kategori', 'subKriteria', 'itemSubKriteria'])
        ->select(
            'id_kategori_kriteria',
            'id_sub_kriteria',
            'id_item_sub_kriteria',
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(CAST(kredit AS DECIMAL(15,2))) / 1000 as total')
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

    $periodKeys = range((int) $bulanDari, (int) $bulanSampai);
    $result['permintaan'] = DashboardKriteriaHierarchy::ensureFlatRows($result['permintaan'], $periodKeys);
    $result['dropping'] = DashboardKriteriaHierarchy::ensureFlatRows($result['dropping'], $periodKeys);
    $result['pembayaran'] = DashboardKriteriaHierarchy::ensureFlatRows($result['pembayaran'], $periodKeys);

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

        $periodKeys = range((int) $bulanDari, (int) $bulanSampai);
        $result['permintaan'] = DashboardKriteriaHierarchy::ensureFlatRows($result['permintaan'], $periodKeys);
        $result['dropping'] = DashboardKriteriaHierarchy::ensureFlatRows($result['dropping'], $periodKeys);
        $result['pembayaran'] = DashboardKriteriaHierarchy::ensureFlatRows($result['pembayaran'], $periodKeys);

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

        $periodKeys = range((int) $bulanDari, (int) $bulanSampai);
        $result['permintaan'] = DashboardKriteriaHierarchy::ensureFlatRows($result['permintaan'], $periodKeys);
        $result['dropping'] = DashboardKriteriaHierarchy::ensureFlatRows($result['dropping'], $periodKeys);
        $result['pembayaran'] = DashboardKriteriaHierarchy::ensureFlatRows($result['pembayaran'], $periodKeys);

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

        $permintaanData = DashboardKriteriaHierarchy::normalizeByMonth($permintaanData);
        $droppingData = DashboardKriteriaHierarchy::normalizeByMonth($droppingData);
        $pembayaranData = DashboardKriteriaHierarchy::normalizeByMonth($pembayaranData);

        $penerimaanPengembalianDana = \App\Models\BankMasuk::query()
            ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_masuk.id_bank_tujuan')
            ->selectRaw('MONTH(bank_masuk.tanggal) as bulan')
            ->selectRaw('SUM(CAST(COALESCE(bank_masuk.debet, 0) AS DECIMAL(20,2))) / 1000 as total')
            ->whereYear('bank_masuk.tanggal', $tahun)
            ->whereBetween(DB::raw('MONTH(bank_masuk.tanggal)'), [$bulanDari, $bulanSampai])
            ->where('bank_masuk.debet', '>', 0)
            ->where(function ($query) {
                $query->whereNull('bank_tujuan.nama_tujuan')
                    ->orWhereRaw("bank_tujuan.nama_tujuan NOT REGEXP '[0-9]{6,}'");
            })
            ->whereRaw("LOWER(COALESCE(bank_masuk.uraian, '')) NOT REGEXP 'region[[:space:]]*v[[:space:]-]*dropping'")
            ->whereRaw("LOWER(COALESCE(bank_masuk.uraian, '')) NOT LIKE '%saldo awal%'")
            ->groupBy(DB::raw('MONTH(bank_masuk.tanggal)'))
            ->pluck('total', 'bulan')
            ->map(fn ($value) => (float) $value)
            ->toArray();

        $biayaAdminKeywords = [
            'biaya adm 14602',
            'pajak 14602',
            'biaya administrasi mcm',
            'telkomsel ad tagihan 99105',
            '020001160160812064651901 dr ca payment autodb',
        ];

        $biayaAdminBankLainnya = BankKeluar::query()
            ->selectRaw('MONTH(tanggal) as bulan')
            ->selectRaw('SUM(CAST(COALESCE(kredit, 0) AS DECIMAL(20,2))) / 1000 as total')
            ->whereYear('tanggal', $tahun)
            ->whereBetween(DB::raw('MONTH(tanggal)'), [$bulanDari, $bulanSampai])
            ->where('kredit', '>', 0)
            ->where(function ($query) use ($biayaAdminKeywords) {
                foreach ($biayaAdminKeywords as $keyword) {
                    $query->orWhereRaw("LOWER(COALESCE(uraian, '')) LIKE ?", ['%' . $keyword . '%']);
                }
            })
            ->groupBy(DB::raw('MONTH(tanggal)'))
            ->pluck('total', 'bulan')
            ->map(fn ($value) => (float) $value)
            ->toArray();

        // ================================================================
        // RINGKASAN MODAL KERJA (footer waterfall) — semua satuan ribuan.
        // Meniru sheet PMK Jan_W..Des_W baris 216-229. Dihitung oleh sistem
        // dari data transaksi (bukan input manual). Lihat docs/superpowers/
        // specs/2026-07-03-modal-kerja-footer-summary-design.md
        // ================================================================

        // Total Dropping & Pembayaran per bulan (dipakai baris 8 & 9).
        $sumByMonth = function (array $bySection, int $bulan): float {
            $total = 0.0;
            foreach (($bySection[$bulan] ?? []) as $subs) {
                foreach ($subs as $items) {
                    foreach ($items as $weeks) {
                        $total += (float) array_sum($weeks);
                    }
                }
            }
            return $total;
        };

        // Running balance dua rekening opex (Mandiri 408 & Mandiri TBS 200).
        // Rek Opex Operasional (Mandiri 408) : 146-00-9702740-8
        // Rek Overdraft "TBS" (Mandiri 200)  : 146-00-1201420-0
        $opex408 = $this->opexRekeningBalance($tahun, ['9702740']);
        $opex200 = $this->opexRekeningBalance($tahun, ['1201420']);

        $saldoAwalModalSendiri = [];       // baris 1
        $pembayaranModalSendiri = [];      // baris 2 (penggunaan modal sendiri)
        $saldoAkhirModalSendiri = [];      // baris 5
        $saldoAwalModalKerja = [];         // baris 6
        $saldoAwalBankOpex = [];           // baris 7
        $pembayaranModalKerja = [];        // baris 8
        $penerimaanModalKerja = [];        // baris 9
        $sisaModalKerja = [];              // baris 10
        $saldoAkhirBankOpex = [];          // baris 11
        $posisiRek408 = [];                // baris 12
        $posisiRek200 = [];                // baris 13
        $jumlahSaldoOpex = [];             // baris 14

        for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
            $msOpen  = $saldoAwalModalSendiri[$b] = 0.0;
            $msPay   = $pembayaranModalSendiri[$b] = 0.0;
            $msRecv  = (float) ($penerimaanPengembalianDana[$b] ?? 0);
            $msAdmin = (float) ($biayaAdminBankLainnya[$b] ?? 0);
            $msEnd   = $saldoAkhirModalSendiri[$b] = $msOpen - $msPay + $msRecv - $msAdmin;

            $mkOpen  = $saldoAwalModalKerja[$b] =
                ($opex408['start'][$b] ?? 0) + ($opex200['start'][$b] ?? 0);
            $saldoAwalBankOpex[$b] = $msOpen + $mkOpen;

            $mkPay   = $pembayaranModalKerja[$b] = $sumByMonth($pembayaranData, $b) - $msPay;
            $mkRecv  = $penerimaanModalKerja[$b] = $sumByMonth($droppingData, $b);
            $mkSisa  = $sisaModalKerja[$b] = $mkOpen - $mkPay + $mkRecv;
            $saldoAkhirBankOpex[$b] = $msEnd + $mkSisa;

            $p408 = $posisiRek408[$b] = (float) ($opex408['end'][$b] ?? 0);
            $p200 = $posisiRek200[$b] = (float) ($opex200['end'][$b] ?? 0);
            $jumlahSaldoOpex[$b] = $p408 + $p200;
        }

        $modalKerjaSummaryData = [
            'saldo_awal_modal_sendiri'          => $saldoAwalModalSendiri,
            'pembayaran_modal_sendiri_tahun_lalu' => $pembayaranModalSendiri,
            'penerimaan_pengembalian_dana'      => $penerimaanPengembalianDana,
            'biaya_admin_bank_lainnya'          => $biayaAdminBankLainnya,
            'saldo_akhir_modal_sendiri'         => $saldoAkhirModalSendiri,
            'saldo_awal_modal_kerja'            => $saldoAwalModalKerja,
            'saldo_awal_bank_opex'              => $saldoAwalBankOpex,
            'pembayaran_modal_kerja'            => $pembayaranModalKerja,
            'penerimaan_modal_kerja'            => $penerimaanModalKerja,
            'sisa_modal_kerja'                  => $sisaModalKerja,
            'saldo_akhir_bank_opex'             => $saldoAkhirBankOpex,
            'posisi_rek_408'                    => $posisiRek408,
            'posisi_rek_200'                    => $posisiRek200,
            'jumlah_saldo_opex'                 => $jumlahSaldoOpex,
        ];

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

        $allKeys = DashboardKriteriaHierarchy::ensureNestedRows($allKeys, true);

        return view('cash_bank.modalKerjaTable', compact(
            'permintaanData', 'droppingData', 'pembayaranData',
            'allKeys', 'bulanAktif', 'tahun', 'bulanMap',
            'bulanDari', 'bulanSampai', 'weekCuts', 'modalKerjaSummaryData'
        ));
    }

    /**
     * Running balance rekening opex (satuan ribuan) untuk baris ringkasan
     * Modal Kerja. Identik konsep dgn DashboardController::bank() untuk Rek 408.
     *
     * @param  array<int,string> $patterns  pola nama_sumber_dana (LIKE %..%)
     * @return array{start: array<int,float>, end: array<int,float>}
     *   start[b] = posisi awal bulan b (opening + gerakan bulan < b)
     *   end[b]   = posisi akhir bulan b (opening + gerakan bulan <= b)
     */
    private function opexRekeningBalance($tahun, array $patterns): array
    {
        $ids = \App\Models\SumberDana::query()
            ->where(function ($q) use ($patterns) {
                foreach ($patterns as $p) {
                    $q->orWhere('nama_sumber_dana', 'like', '%' . $p . '%');
                }
            })
            ->pluck('id_sumber_dana')
            ->all();

        $start = [];
        $end = [];
        if (empty($ids)) {
            foreach (range(1, 12) as $b) { $start[$b] = 0.0; $end[$b] = 0.0; }
            return ['start' => $start, 'end' => $end];
        }

        // Opening = entri "saldo awal" pada bank_masuk untuk rekening tsb.
        $opening = (float) BankMasuk::whereIn('id_sumber_dana', $ids)
            ->whereRaw("LOWER(COALESCE(uraian,'')) LIKE '%saldo awal%'")
            ->sum(DB::raw('CAST(COALESCE(debet,0) AS DECIMAL(20,2))'));

        // Gerakan (non saldo-awal) per bulan: masuk (debet) - keluar (kredit).
        $masuk = BankMasuk::whereIn('id_sumber_dana', $ids)
            ->whereYear('tanggal', $tahun)
            ->whereRaw("LOWER(COALESCE(uraian,'')) NOT LIKE '%saldo awal%'")
            ->selectRaw('MONTH(tanggal) as bulan')
            ->selectRaw('SUM(CAST(COALESCE(debet,0) AS DECIMAL(20,2))) as total')
            ->groupBy(DB::raw('MONTH(tanggal)'))
            ->pluck('total', 'bulan')
            ->map(fn ($v) => (float) $v)
            ->all();

        $keluar = BankKeluar::whereIn('id_sumber_dana', $ids)
            ->whereYear('tanggal', $tahun)
            ->selectRaw('MONTH(tanggal) as bulan')
            ->selectRaw('SUM(CAST(COALESCE(kredit,0) AS DECIMAL(20,2))) as total')
            ->groupBy(DB::raw('MONTH(tanggal)'))
            ->pluck('total', 'bulan')
            ->map(fn ($v) => (float) $v)
            ->all();

        $running = $opening;
        for ($b = 1; $b <= 12; $b++) {
            $start[$b] = $running / 1000;
            $running += ($masuk[$b] ?? 0) - ($keluar[$b] ?? 0);
            $end[$b] = $running / 1000;
        }

        return ['start' => $start, 'end' => $end];
    }

    private function inferModalKerjaPaymentPath($row): ?array
    {
        $kategori = trim((string)($row->kategori->nama_kriteria ?? ''));
        $sub = trim((string)($row->subKriteria->nama_sub_kriteria ?? ''));
        $item = trim((string)($row->itemSubKriteria->nama_item_sub_kriteria ?? ''));

        if ($kategori !== '' && $sub !== '' && $item !== '') {
            return DashboardKriteriaHierarchy::normalizePath($kategori, $sub, $item);
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

    /**
     * Data bersama dashboard Saldo Kas & Bank — dipakai halaman web DAN export
     * PDF agar isi keduanya selalu identik (saldo bank, Informasi Saldo, saldo VA).
     */
    private function buildBankDashboardData(): array
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

        return compact(
            'sumberDanaList',
            'totalSaldoBank',
            'bankVAList',
            'totalSaldoVA',
            'saldoRek408',
            'noRek408',
            'digitAkhirRek',
            'saldoRegion'
        );
    }

    public function bank(Request $request)
    {
        return view('cash_bank.dashboardBank', $this->buildBankDashboardData());
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

        return view('cash_bank.exportPDF.pdfDashboardBank', array_merge(
            $this->buildBankDashboardData(),
            compact('tanggal', 'nama', 'jabatan')
        ));
    }
}
