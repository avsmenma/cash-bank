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
        foreach ($bulanList as $no => $nama) {
            if ($no >= $bulanDari && $no <= $bulanSampai && isset($bulanAktif[$no])) {
                $bulanListFiltered[$no] = $nama;
            }
        }

        return view('cash_bank.dashbordKedua', compact(
            'result',
            'bulanListFiltered',
            'tahun'
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
