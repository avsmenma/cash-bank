<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;

class ExcelPembayaran implements FromView
{
    /**
    * @return \Illuminate\Support\View
    */
    public function view() : View
    {
          // ===== BULAN =====
        $bulanList = [
            'januari', 'februari', 'maret', 'april',
            'mei', 'juni', 'juli', 'agustus',
            'september', 'oktober', 'november', 'desember'
        ];

        // Mapping bulan ke angka
        $bulanMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12
        ];

        // ===== TEMPLATE =====
        $templateBulan = [];
        foreach ($bulanList as $b) {
            $templateBulan[$b] = [
                'rencana' => 0,
                'realisasi' => 0,
                'selisih' => 0,
                'persen' => 0
            ];
        }

        $dataPenerima  = [];
        $dataDropping  = [];
        $totalPenerima = $templateBulan;
        $totalDropping = $templateBulan;

        /*
        |================================================
        | PENERIMA - RENCANA
        |================================================
        */
        $rencanaPenerima = RencanaPenerima::with('kategori')
            ->where('tahun', $tahun)
            ->get();

        foreach ($rencanaPenerima as $row) {
            $namaKategori = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';

            if (!isset($dataPenerima[$namaKategori])) {
                $dataPenerima[$namaKategori] = $templateBulan;
            }

            // Loop setiap bulan
            foreach ($bulanList as $bulan) {
                $nilaiRencana = $row->$bulan ?? 0;
                $dataPenerima[$namaKategori][$bulan]['rencana'] += $nilaiRencana;
                $totalPenerima[$bulan]['rencana'] += $nilaiRencana;
            }
        }

        /*
        |================================================
        | PENERIMA - REALISASI
        |================================================
        */
        $realisasiPenerima = Penerima::with('kategori')
            ->whereYear('tanggal', $tahun)
            ->get();

        foreach ($realisasiPenerima as $row) {
            $bulanAngka = Carbon::parse($row->tanggal)->month;
            $bulan = array_search($bulanAngka, $bulanMap);
            
            if (!$bulan) continue;

            $namaKategori = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';

            if (!isset($dataPenerima[$namaKategori])) {
                $dataPenerima[$namaKategori] = $templateBulan;
            }

            // Hitung realisasi (nilai + ppn - potppn)
            $nilaiRealisasi = ($row->nilai ?? 0) + ($row->ppn ?? 0) - ($row->potppn ?? 0);
            
            $dataPenerima[$namaKategori][$bulan]['realisasi'] += $nilaiRealisasi;
            $totalPenerima[$bulan]['realisasi'] += $nilaiRealisasi;
        }

        // Hitung selisih dan persen untuk PENERIMA
        foreach ($dataPenerima as $k => $bulanData) {
            foreach ($bulanData as $b => $v) {
                $dataPenerima[$k][$b]['selisih'] = $v['realisasi'] - $v['rencana'];
                $dataPenerima[$k][$b]['persen']  = $v['rencana'] > 0 ? ($v['realisasi'] / $v['rencana']) * 100 : 0;
            }
        }

        foreach ($totalPenerima as $b => $v) {
            $totalPenerima[$b]['selisih'] = $v['realisasi'] - $v['rencana'];
            $totalPenerima[$b]['persen']  = $v['rencana'] > 0 ? ($v['realisasi'] / $v['rencana']) * 100 : 0;
        }

        /*
        |================================================
        | DROPPING - RENCANA
        |================================================
        */
        $rencanaDropping = RencanaDropping::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->get();

        foreach ($rencanaDropping as $row) {
            $k = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';
            $s = $row->subKriteria->nama_sub ?? 'Tidak Ada Sub';
            $i = $row->itemSubKriteria->nama_item ?? 'Tidak Ada Item';

            if (!isset($dataDropping[$k])) {
                $dataDropping[$k] = [];
            }
            if (!isset($dataDropping[$k][$s])) {
                $dataDropping[$k][$s] = [];
            }
            if (!isset($dataDropping[$k][$s][$i])) {
                $dataDropping[$k][$s][$i] = $templateBulan;
            }

            // Loop setiap bulan
            foreach ($bulanList as $bulan) {
                $nilaiRencana = $row->$bulan ?? 0;
                $dataDropping[$k][$s][$i][$bulan]['rencana'] += $nilaiRencana;
                $totalDropping[$bulan]['rencana'] += $nilaiRencana;
            }
        }

        /*
        |================================================
        | DROPPING - REALISASI
        |================================================
        */
        $realisasiDropping = Dropping::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->get();

        foreach ($realisasiDropping as $row) {
            $bulanAngka = $row->bulan;
            $bulan = array_search($bulanAngka, $bulanMap);
            
            if (!$bulan) continue;

            $k = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';
            $s = $row->subKriteria->nama_sub ?? 'Tidak Ada Sub';
            $i = $row->itemSubKriteria->nama_item ?? 'Tidak Ada Item';

            if (!isset($dataDropping[$k])) {
                $dataDropping[$k] = [];
            }
            if (!isset($dataDropping[$k][$s])) {
                $dataDropping[$k][$s] = [];
            }
            if (!isset($dataDropping[$k][$s][$i])) {
                $dataDropping[$k][$s][$i] = $templateBulan;
            }

            // Hitung realisasi (M1 + M2 + M3 + M4)
            $nilaiRealisasi = ($row->M1 ?? 0) + ($row->M2 ?? 0) + ($row->M3 ?? 0) + ($row->M4 ?? 0);
            
            $dataDropping[$k][$s][$i][$bulan]['realisasi'] += $nilaiRealisasi;
            $totalDropping[$bulan]['realisasi'] += $nilaiRealisasi;
        }

        // Hitung selisih dan persen untuk DROPPING
        foreach ($dataDropping as $k => $subs) {
            foreach ($subs as $s => $items) {
                foreach ($items as $i => $bulanData) {
                    foreach ($bulanData as $b => $v) {
                        $dataDropping[$k][$s][$i][$b]['selisih'] = $v['realisasi'] - $v['rencana'];
                        $dataDropping[$k][$s][$i][$b]['persen']  = $v['rencana'] > 0 ? ($v['realisasi'] / $v['rencana']) * 100 : 0;
                    }
                }
            }
        }

        foreach ($totalDropping as $b => $v) {
            $totalDropping[$b]['selisih'] = $v['realisasi'] - $v['rencana'];
            $totalDropping[$b]['persen']  = $v['rencana'] > 0 ? ($v['realisasi'] / $v['rencana']) * 100 : 0;
        }

                return compact(
            'bulanList',
            'dataPenerima',
            'dataDropping',
            'totalPenerima',
            'totalDropping'
        );
    }
}
