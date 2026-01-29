<?php

namespace App\Exports;

use App\Models\KategoriKriteria;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;

class ExportExcelGabunganPenerima implements FromView
{
    protected $tahun;
    protected $bulanDari;
    protected $bulanSampai;

    public function __construct($tahun = null, $bulanDari = 1, $bulanSampai = 12)
    {
        $this->tahun = $tahun ?? date('Y');
        $this->bulanDari = $bulanDari;
        $this->bulanSampai = $bulanSampai;
    }

    public function view(): View
    {
        $bulanList = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        // Filter bulan sesuai range yang dipilih
        $bulanListFiltered = array_filter($bulanList, function ($noBulan) {
            return $noBulan >= $this->bulanDari && $noBulan <= $this->bulanSampai;
        });

        $kategori = KategoriKriteria::where('tipe', 'penerima')->get();
        $data = [];
        $bulanAktif = []; // untuk tracking bulan yang punya data

        foreach ($kategori as $k) {
            foreach ($bulanListFiltered as $namaBulan => $noBulan) {

                // RENCANA
                $rencana = DB::table('rencana_penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->where('tahun', $this->tahun)
                    ->sum($namaBulan);

                // REALISASI (DARI TABEL penerimas)
                $nilai = DB::table('penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->whereYear('tanggal', $this->tahun)
                    ->whereMonth('tanggal', $noBulan)
                    ->sum('nilai');
                $ppn = DB::table('penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->whereYear('tanggal', $this->tahun)
                    ->whereMonth('tanggal', $noBulan)
                    ->sum('ppn');
                $potppn = DB::table('penerimas')
                    ->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                    ->whereYear('tanggal', $this->tahun)
                    ->whereMonth('tanggal', $noBulan)
                    ->sum('potppn');
                $realisasi = $nilai + $ppn - $potppn;

                // Tandai bulan yang punya data
                if ($rencana > 0 || $realisasi > 0) {
                    $bulanAktif[$namaBulan] = true;
                }

                $selisih = $realisasi - $rencana;
                $persen = $rencana > 0 ? ($realisasi / $rencana) * 100 : 0;

                $data[$k->nama_kriteria][$namaBulan] = [
                    'rencana' => $rencana,
                    'realisasi' => $realisasi,
                    'selisih' => $selisih,
                    'persen' => $persen
                ];
            }
        }

        // Filter hanya bulan yang punya data
        $bulanListFiltered = array_filter($bulanListFiltered, function ($namaBulan) use ($bulanAktif) {
            return isset($bulanAktif[$namaBulan]);
        }, ARRAY_FILTER_USE_KEY);

        return view('cash_bank.exportExcel.excelGabunganPenerima', [
            'data' => $data,
            'bulanListFiltered' => $bulanListFiltered,
            'tahun' => $this->tahun,
            'bulanDari' => $this->bulanDari,
            'bulanSampai' => $this->bulanSampai
        ]);
    }
}
