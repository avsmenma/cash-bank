<?php

namespace App\Exports;

use App\Models\Dropping;
use App\Models\Penerima;
use App\Models\BankKeluar;
use App\Models\Permintaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExcelPd implements FromView
{
    protected $tahun;
    protected $bulanDari;
    protected $bulanSampai;

    public function __construct($tahun = null, $bulanDari = null, $bulanSampai = null)
    {
        $this->tahun = $tahun ?? date('Y');
        $this->bulanDari = $bulanDari ?? 1;
        $this->bulanSampai = $bulanSampai ?? 12;
    }

    /**
    * @return \Illuminate\Support\View
    */
    public function view() : View
    {
        $bulanList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
        ];
        
        $tahun = $this->tahun;
        $bulanDari = $this->bulanDari;
        $bulanSampai = $this->bulanSampai;

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
        for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
            if (isset($bulanList[$b])) {
                $bulanListFiltered[$b] = $bulanList[$b];
            }
        }
        if (empty($bulanListFiltered)) {
            $bulanListFiltered = $bulanList;
        }

        // Aggregate Permintaan, Dropping & Pembayaran
        $permAgg = [];
        foreach (($result['permintaan'] ?? []) as $item) {
            $kat = $item['kategori']; $sub = $item['sub_kriteria'];
            foreach ($bulanListFiltered as $b => $n) {
                $permAgg[$kat][$sub][$b] = ($permAgg[$kat][$sub][$b] ?? 0) + ($item['data'][$b] ?? 0);
            }
        }

        $dropAgg = [];
        foreach ($result['dropping'] as $item) {
            $kat = $item['kategori']; $sub = $item['sub_kriteria'];
            foreach ($bulanListFiltered as $b => $n) {
                $dropAgg[$kat][$sub][$b] = ($dropAgg[$kat][$sub][$b] ?? 0) + ($item['data'][$b] ?? 0);
            }
        }

        $payAgg = [];
        foreach ($result['pembayaran'] as $item) {
            $kat = $item['kategori']; $sub = $item['sub_kriteria'];
            foreach ($bulanListFiltered as $b => $n) {
                $payAgg[$kat][$sub][$b] = ($payAgg[$kat][$sub][$b] ?? 0) + ($item['data'][$b] ?? 0);
            }
        }

        $permAgg = \App\Support\DashboardKriteriaHierarchy::sortCategorySub($permAgg);
        $dropAgg = \App\Support\DashboardKriteriaHierarchy::sortCategorySub($dropAgg);
        $payAgg = \App\Support\DashboardKriteriaHierarchy::sortCategorySub($payAgg);

        $totalPenerima  = []; foreach ($bulanListFiltered as $b => $n) $totalPenerima[$b] = 0;
        foreach ($result['penerima'] as $kat => $bData) {
            foreach ($bulanListFiltered as $b => $n) $totalPenerima[$b] += ($bData[$b] ?? 0);
        }
        $totalPermAll   = []; foreach ($bulanListFiltered as $b => $n) $totalPermAll[$b] = 0;
        foreach ($permAgg as $kat => $subs) {
            foreach ($subs as $sub => $bData) {
                foreach ($bulanListFiltered as $b => $n) $totalPermAll[$b] += ($bData[$b] ?? 0);
            }
        }
        $totalDropAll   = []; foreach ($bulanListFiltered as $b => $n) $totalDropAll[$b] = 0;
        foreach ($dropAgg as $kat => $subs) {
            foreach ($subs as $sub => $bData) {
                foreach ($bulanListFiltered as $b => $n) $totalDropAll[$b] += ($bData[$b] ?? 0);
            }
        }
        $totalPayAll    = []; foreach ($bulanListFiltered as $b => $n) $totalPayAll[$b] = 0;
        foreach ($payAgg as $kat => $subs) {
            foreach ($subs as $sub => $bData) {
                foreach ($bulanListFiltered as $b => $n) $totalPayAll[$b] += ($bData[$b] ?? 0);
            }
        }

        $cpnKernel = []; foreach ($bulanListFiltered as $b => $n) $cpnKernel[$b] = 0;
        foreach (['Penjualan CPO (Minyak Sawit)', 'Penjualan Kernel (Inti Sawit)', 'Penjualan CPO', 'Penjualan Kernel'] as $cpk) {
            if (isset($result['penerima'][$cpk])) {
                foreach ($bulanListFiltered as $b => $n) {
                    $cpnKernel[$b] += ($result['penerima'][$cpk][$b] ?? 0);
                }
            }
        }

        $dropTBS = []; $payTBS = [];
        foreach ($bulanListFiltered as $b => $n) { $dropTBS[$b] = 0; $payTBS[$b] = 0; }
        foreach ($dropAgg as $kat => $subs) {
            foreach ($subs as $sub => $bData) {
                if (stripos($sub, 'TBS') !== false || $sub === 'Pembelian TBS') {
                    foreach ($bulanListFiltered as $b => $n) $dropTBS[$b] += ($bData[$b] ?? 0);
                }
            }
        }
        foreach ($payAgg as $kat => $subs) {
            foreach ($subs as $sub => $bData) {
                if (stripos($sub, 'TBS') !== false || $sub === 'Pembelian TBS') {
                    foreach ($bulanListFiltered as $b => $n) $payTBS[$b] += ($bData[$b] ?? 0);
                }
            }
        }

        $cfRows = [];
        $cfPush = function ($section, $type, $uraian, $vals = null, $total = null) use (&$cfRows, $bulanListFiltered) {
            $row = ['section' => $section, 'type' => $type, 'uraian' => $uraian, 'total' => $total];
            foreach ($bulanListFiltered as $b => $n) {
                $row['m' . $b] = $vals === null ? null : ($vals[$b] ?? 0);
            }
            $cfRows[] = $row;
        };

        $cfBagian = function ($agg, $sectionKey) use ($cfPush, $bulanListFiltered) {
            foreach ($agg as $kat => $subs) {
                $cfPush($sectionKey, 'kat', $kat);
                $subTotB = [];
                foreach ($bulanListFiltered as $b => $n) $subTotB[$b] = 0;
                $subAll = 0;
                foreach ($subs as $sub => $bData) {
                    $vals = [];
                    $rt = 0;
                    foreach ($bulanListFiltered as $b => $n) {
                        $v = $bData[$b] ?? 0;
                        $vals[$b] = $v;
                        $rt += $v;
                        $subTotB[$b] += $v;
                    }
                    $subAll += $rt;
                    $cfPush($sectionKey, 'item', '- ' . $sub, $vals, $rt);
                }
                $cfPush($sectionKey, 'subtotal', 'Jumlah ' . $kat, $subTotB, $subAll);
            }
        };

        // PENERIMAAN
        $cfPush('penerimaan', 'section', 'PENERIMAAN');
        foreach ($result['penerima'] as $kat => $bData) {
            $vals = [];
            $rowTotal = 0;
            foreach ($bulanListFiltered as $b => $n) { $v = $bData[$b] ?? 0; $vals[$b] = $v; $rowTotal += $v; }
            $cfPush('penerimaan', 'item', '- ' . $kat, $vals, $rowTotal);
        }
        $cfPush('penerimaan', 'total', 'TOTAL PENERIMAAN', $totalPenerima, array_sum($totalPenerima));

        // PERMINTAAN
        $cfPush('permintaan', 'section', 'PERMINTAAN');
        $cfBagian($permAgg, 'permintaan');
        $cfPush('permintaan', 'total', 'TOTAL PERMINTAAN', $totalPermAll, array_sum($totalPermAll));

        // DROPPING HO
        $cfPush('dropping', 'section', 'DROPPING HO');
        $cfBagian($dropAgg, 'dropping');
        $cfPush('dropping', 'total', 'TOTAL DROPPING HO', $totalDropAll, array_sum($totalDropAll));

        // SELISIH
        $selPD = [];
        foreach ($bulanListFiltered as $b => $n) $selPD[$b] = $totalPenerima[$b] - $totalDropAll[$b];
        $cfPush('semua-only', 'selisih', 'SELISIH PENERIMAAN TERHADAP DROPPING', $selPD, array_sum($selPD));

        // PEMBAYARAN
        $cfPush('pembayaran', 'section', 'PEMBAYARAN');
        $cfBagian($payAgg, 'pembayaran');
        $cfPush('pembayaran', 'total', 'TOTAL PEMBAYARAN', $totalPayAll, array_sum($totalPayAll));

        $selPP = [];
        foreach ($bulanListFiltered as $b => $n) $selPP[$b] = $totalPenerima[$b] - $totalPayAll[$b];
        $cfPush('semua-only', 'selisih', 'SELISIH PEMBAYARAN TERHADAP PENERIMAAN', $selPP, array_sum($selPP));

        $selDP = [];
        foreach ($bulanListFiltered as $b => $n) $selDP[$b] = $totalDropAll[$b] - $totalPayAll[$b];
        $cfPush('semua-only', 'selisih', 'SELISIH PEMBAYARAN TERHADAP DROPPING', $selDP, array_sum($selDP));

        $cfPush('semua-only', 'summary', 'PENERIMAAN PENJUALAN CPO & KERNEL', $cpnKernel, array_sum($cpnKernel));
        $cfPush('semua-only', 'summary', 'DROPPING TBS', $dropTBS, array_sum($dropTBS));
        $cfPush('semua-only', 'summary', 'PEMBAYARAN TBS', $payTBS, array_sum($payTBS));

        return view('cash_bank.exportExcel.excelPd', compact('cfRows', 'bulanListFiltered', 'tahun', 'bulanDari', 'bulanSampai'));
    }
}
