<?php

namespace App\Exports;

use App\Models\Dropping;
use App\Models\Permintaan;
use App\Models\BankKeluar;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class ExcelPvd implements FromView
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

    public function view(): View
    {
        $tahun = $this->tahun;
        $bulanDari = $this->bulanDari;
        $bulanSampai = $this->bulanSampai;

        $bulanList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
        ];

        /* ================= PERMINTAAN ================= */
        $permintaan = Permintaan::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [$bulanDari, $bulanSampai])
            ->get();

        /* ================= DROPPING ================= */
        $dropping = Dropping::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [$bulanDari, $bulanSampai])
            ->get();

        /* ================= PEMBAYARAN ================= */
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
            ->where('kredit', '>', 0)
            ->groupBy(
                'id_kategori_kriteria',
                'id_sub_kriteria',
                'id_item_sub_kriteria',
                DB::raw('MONTH(tanggal)')
            )
            ->get();

        $result = [
            'permintaan' => [],
            'dropping' => [],
            'pembayaran' => []
        ];

        $bulanAktif = [];

        /* ================= PROSES PERMINTAAN ================= */
        foreach ($permintaan as $p) {
            $this->prosesData($result['permintaan'], $p, $bulanAktif, $bulanDari, $bulanSampai);
        }

        /* ================= PROSES DROPPING ================= */
        foreach ($dropping as $d) {
            $this->prosesData($result['dropping'], $d, $bulanAktif, $bulanDari, $bulanSampai);
        }

        /* ================= PROSES PEMBAYARAN ================= */
        foreach ($pembayaran as $p) {
            $this->prosesPembayaran($result['pembayaran'], $p, $bulanAktif, $bulanDari, $bulanSampai);
        }

        /* ================= FILTER BULAN ================= */
        $bulanListFiltered = [];
        for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
            if (isset($bulanList[$b])) {
                $bulanListFiltered[$b] = $bulanList[$b];
            }
        }
        if (empty($bulanListFiltered)) {
            $bulanListFiltered = $bulanList;
        }

        $organizedData = [];
        foreach (['dropping', 'permintaan', 'pembayaran'] as $sumber) {
            if (!isset($result[$sumber])) continue;
            foreach ($result[$sumber] as $item) {
                $kategori = $item['kategori'];
                $subKriteria = $item['sub_kriteria'];
                $itemKriteria = $item['item_kriteria'];

                if (!isset($organizedData[$kategori][$subKriteria][$itemKriteria])) {
                    $organizedData[$kategori][$subKriteria][$itemKriteria] = [
                        'permintaan' => [],
                        'dropping' => [],
                        'pembayaran' => []
                    ];
                }
                $organizedData[$kategori][$subKriteria][$itemKriteria][$sumber] = $item['data'];
            }
        }

        $organizedData = \App\Support\DashboardKriteriaHierarchy::sortNested($organizedData);

        $pvdRows = [];
        $pvdPush = function ($type, $no, $uraian, $perBulan = null, $tot = null, $pct = null) use (&$pvdRows, $bulanListFiltered) {
            $row = ['type' => $type, 'no' => $no, 'uraian' => $uraian];
            foreach ($bulanListFiltered as $noBulan => $nm) {
                $row['m' . $noBulan . '_p'] = $perBulan[$noBulan]['p'] ?? null;
                $row['m' . $noBulan . '_d'] = $perBulan[$noBulan]['d'] ?? null;
                $row['m' . $noBulan . '_b'] = $perBulan[$noBulan]['b'] ?? null;
            }
            $row['tot_p'] = $tot['p'] ?? null;
            $row['tot_d'] = $tot['d'] ?? null;
            $row['tot_b'] = $tot['b'] ?? null;
            $row['pct_p'] = $pct['p'] ?? null;
            $row['pct_d'] = $pct['d'] ?? null;
            $pvdRows[] = $row;
        };

        $grandTotal = [];
        foreach ($bulanListFiltered as $b => $n) $grandTotal[$b] = ['p' => 0, 'd' => 0, 'b' => 0];
        $grandAll = ['p' => 0, 'd' => 0, 'b' => 0];

        $rowNumber = 1;
        foreach ($organizedData as $kategori => $subKriterias) {
            $katTotal = [];
            foreach ($bulanListFiltered as $b => $n) $katTotal[$b] = ['p' => 0, 'd' => 0, 'b' => 0];
            $katAll = ['p' => 0, 'd' => 0, 'b' => 0];

            $pvdPush('kat', '', $kategori);

            foreach ($subKriterias as $subKriteria => $items) {
                $subTotal = [];
                foreach ($bulanListFiltered as $b => $n) $subTotal[$b] = ['p' => 0, 'd' => 0, 'b' => 0];
                $subAll = ['p' => 0, 'd' => 0, 'b' => 0];

                $pvdPush('sub', '', $subKriteria);

                foreach ($items as $itemKriteria => $sumberData) {
                    $itemPerBulan = [];
                    $totPerm = 0; $totDrop = 0; $totPay = 0;

                    foreach ($bulanListFiltered as $noBulan => $namaBulan) {
                        $p = $sumberData['permintaan'][$noBulan] ?? 0;
                        $d = $sumberData['dropping'][$noBulan] ?? 0;
                        $b = $sumberData['pembayaran'][$noBulan] ?? 0;

                        $totPerm += $p; $totDrop += $d; $totPay += $b;
                        $subTotal[$noBulan]['p'] += $p; $subTotal[$noBulan]['d'] += $d; $subTotal[$noBulan]['b'] += $b;
                        $katTotal[$noBulan]['p'] += $p; $katTotal[$noBulan]['d'] += $d; $katTotal[$noBulan]['b'] += $b;
                        $grandTotal[$noBulan]['p'] += $p; $grandTotal[$noBulan]['d'] += $d; $grandTotal[$noBulan]['b'] += $b;

                        $itemPerBulan[$noBulan] = ['p' => $p, 'd' => $d, 'b' => $b];
                    }

                    $subAll['p'] += $totPerm; $subAll['d'] += $totDrop; $subAll['b'] += $totPay;
                    $katAll['p'] += $totPerm; $katAll['d'] += $totDrop; $katAll['b'] += $totPay;
                    $grandAll['p'] += $totPerm; $grandAll['d'] += $totDrop; $grandAll['b'] += $totPay;

                    $pctP = $totPerm > 0 ? ($totPay / $totPerm) * 100 : 0;
                    $pctD = $totDrop > 0 ? ($totPay / $totDrop) * 100 : 0;

                    $pvdPush('item', $rowNumber++, '- ' . $itemKriteria, $itemPerBulan,
                        ['p' => $totPerm, 'd' => $totDrop, 'b' => $totPay],
                        ['p' => $pctP, 'd' => $pctD]
                    );
                }

                $subPctP = $subAll['p'] > 0 ? ($subAll['b'] / $subAll['p']) * 100 : 0;
                $subPctD = $subAll['d'] > 0 ? ($subAll['b'] / $subAll['d']) * 100 : 0;
                $pvdPush('subtotal', '', 'Jumlah ' . $subKriteria, $subTotal, $subAll, ['p' => $subPctP, 'd' => $subPctD]);
            }

            $katPctP = $katAll['p'] > 0 ? ($katAll['b'] / $katAll['p']) * 100 : 0;
            $katPctD = $katAll['d'] > 0 ? ($katAll['b'] / $katAll['d']) * 100 : 0;
            $pvdPush('kattotal', '', 'Total ' . $kategori, $katTotal, $katAll, ['p' => $katPctP, 'd' => $katPctD]);
        }

        $grandPctP = $grandAll['p'] > 0 ? ($grandAll['b'] / $grandAll['p']) * 100 : 0;
        $grandPctD = $grandAll['d'] > 0 ? ($grandAll['b'] / $grandAll['d']) * 100 : 0;
        $pvdPush('grand', '', 'GRAND TOTAL', $grandTotal, $grandAll, ['p' => $grandPctP, 'd' => $grandPctD]);

        return view('cash_bank.exportExcel.excelPvd', compact(
            'pvdRows',
            'bulanListFiltered',
            'tahun',
            'bulanDari',
            'bulanSampai'
        ));
    }

    /* ================= HELPER ================= */

    private function prosesData(&$target, $data, &$bulanAktif, $bulanDari, $bulanSampai)
    {
        $kategori = $data->kategori->nama_kriteria ?? '-';
        $sub = $data->subKriteria->nama_sub_kriteria ?? '-';
        $item = $data->itemSubKriteria->nama_item_sub_kriteria ?? '-';
        $bulan = $data->bulan;

        $key = "$kategori|$sub|$item";

        if (!isset($target[$key])) {
            $target[$key] = [
                'kategori' => $kategori,
                'sub_kriteria' => $sub,
                'item_kriteria' => $item,
                'data' => array_fill($bulanDari, $bulanSampai - $bulanDari + 1, 0)
            ];
        }

        $total = ($data->M1 ?? 0) + ($data->M2 ?? 0) + ($data->M3 ?? 0) + ($data->M4 ?? 0);
        $target[$key]['data'][$bulan] += $total;
        $bulanAktif[$bulan] = true;
    }

    private function prosesPembayaran(&$target, $data, &$bulanAktif, $bulanDari, $bulanSampai)
    {
        $kategori = $data->kategori->nama_kriteria ?? '-';
        $sub = $data->subKriteria->nama_sub_kriteria ?? '-';
        $item = $data->itemSubKriteria->nama_item_sub_kriteria ?? '-';
        $bulan = $data->bulan;

        $key = "$kategori|$sub|$item";

        if (!isset($target[$key])) {
            $target[$key] = [
                'kategori' => $kategori,
                'sub_kriteria' => $sub,
                'item_kriteria' => $item,
                'data' => array_fill($bulanDari, $bulanSampai - $bulanDari + 1, 0)
            ];
        }

        $target[$key]['data'][$bulan] += $data->total;
        $bulanAktif[$bulan] = true;
    }
}
