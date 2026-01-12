<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Dropping;
use App\Models\Penerima;
use App\Models\SubKriteria;
use Illuminate\Http\Request;
use App\Models\ItemSubKriteria;
use App\Models\RencanaDropping;
use App\Models\RencanaPenerima;
use App\Exports\ExcelPembayaran;
use App\Models\KategoriKriteria;
use Maatwebsite\Excel\Facades\Excel;

class DashboardPembayaranController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tahun dari request atau default tahun sekarang
        $tahun = $request->tahun ?? date('Y');
        
        $kategoriList = KategoriKriteria::whereIn('tipe', ['keluar', 'Penerima'])
            ->pluck('nama_kriteria', 'id_kategori_kriteria');
        
        // Load data langsung untuk tampilan awal
        $data = $this->getData($tahun);
        $data2 = $this->getData2($tahun);
        
        return view('cash_bank.dashboardPembayaran', array_merge(
            compact('kategoriList', 'tahun'),
            $data,
            $data2
        ));
    }

    public function data(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $data = $this->getData($tahun);
        
        return view('cash_bank.pembayaran.dashboardPembayaran', $data);
    }

    public function data2(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $data = $this->getData2($tahun);
        
        return view('cash_bank.pembayaran.dashboardPembayaran2', $data);
    }

    private function getData($tahun)
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
         $rencanaDropping = Permintaan::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->get();

        foreach ($rencanaDropping as $row) {
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
            $nilaiRencana = ($row->M1 ?? 0) + ($row->M2 ?? 0) + ($row->M3 ?? 0) + ($row->M4 ?? 0);
            
            $dataDropping[$k][$s][$i][$bulan]['rencana'] += $nilaiRencana;
            $totalDropping[$bulan]['rencana'] += $nilaiRencana;
        }

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

    private function getData2($tahun)
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
        $dataPembayaran = [];
        $totalPenerima = $templateBulan;
        $totalDropping = $templateBulan;
        $totalPembayaran = $templateBulan;

        // ===== PROSES DATA PENERIMA (sama seperti getData) =====
        $rencanaPenerima = RencanaPenerima::with('kategori')
            ->where('tahun', $tahun)
            ->get();

        foreach ($rencanaPenerima as $row) {
            $namaKategori = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';
            if (!isset($dataPenerima[$namaKategori])) {
                $dataPenerima[$namaKategori] = $templateBulan;
            }
            foreach ($bulanList as $bulan) {
                $nilaiRencana = $row->$bulan ?? 0;
                $dataPenerima[$namaKategori][$bulan]['rencana'] += $nilaiRencana;
                $totalPenerima[$bulan]['rencana'] += $nilaiRencana;
            }
        }

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
            $nilaiRealisasi = ($row->nilai ?? 0) + ($row->ppn ?? 0) - ($row->potppn ?? 0);
            $dataPenerima[$namaKategori][$bulan]['realisasi'] += $nilaiRealisasi;
            $totalPenerima[$bulan]['realisasi'] += $nilaiRealisasi;
        }

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

        // ===== PROSES DATA DROPPING (sama seperti getData) =====
        $rencanaDropping = RencanaDropping::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->get();

        foreach ($rencanaDropping as $row) {
            $k = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';
            $s = $row->subKriteria->nama_sub_kriteria ?? 'Tidak Ada Sub';
            $i = $row->itemSubKriteria->nama_item_sub_kriteria ?? 'Tidak Ada Item';

            if (!isset($dataDropping[$k])) $dataDropping[$k] = [];
            if (!isset($dataDropping[$k][$s])) $dataDropping[$k][$s] = [];
            if (!isset($dataDropping[$k][$s][$i])) $dataDropping[$k][$s][$i] = $templateBulan;

            foreach ($bulanList as $bulan) {
                $nilaiRencana = $row->$bulan ?? 0;
                $dataDropping[$k][$s][$i][$bulan]['rencana'] += $nilaiRencana;
                $totalDropping[$bulan]['rencana'] += $nilaiRencana;
            }
        }

        $realisasiDropping = Dropping::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->get();

        foreach ($realisasiDropping as $row) {
            $bulanAngka = $row->bulan;
            $bulan = array_search($bulanAngka, $bulanMap);
            if (!$bulan) continue;

            $k = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';
            $s = $row->subKriteria->nama_sub_kriteria ?? 'Tidak Ada Sub';
            $i = $row->itemSubKriteria->nama_item_sub_kriteria ?? 'Tidak Ada Item';

            if (!isset($dataDropping[$k])) $dataDropping[$k] = [];
            if (!isset($dataDropping[$k][$s])) $dataDropping[$k][$s] = [];
            if (!isset($dataDropping[$k][$s][$i])) $dataDropping[$k][$s][$i] = $templateBulan;

            $nilaiRealisasi = ($row->M1 ?? 0) + ($row->M2 ?? 0) + ($row->M3 ?? 0) + ($row->M4 ?? 0);
            $dataDropping[$k][$s][$i][$bulan]['realisasi'] += $nilaiRealisasi;
            $totalDropping[$bulan]['realisasi'] += $nilaiRealisasi;
        }

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

        /*
        |================================================
        | PEMBAYARAN (dari BankKeluar)
        |================================================
        */
        $pembayaran = \App\Models\BankKeluar::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->whereYear('tanggal', $tahun)
            ->get();

        foreach ($pembayaran as $row) {
            $bulanAngka = Carbon::parse($row->tanggal)->month;
            $bulan = array_search($bulanAngka, $bulanMap);
            if (!$bulan) continue;

            $k = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';
            $s = $row->subKriteria->nama_sub_kriteria ?? 'Tidak Ada Sub';
            $i = $row->itemSubKriteria->nama_item_sub_kriteria ?? 'Tidak Ada Item';

            if (!isset($dataPembayaran[$k])) $dataPembayaran[$k] = [];
            if (!isset($dataPembayaran[$k][$s])) $dataPembayaran[$k][$s] = [];
            if (!isset($dataPembayaran[$k][$s][$i])) $dataPembayaran[$k][$s][$i] = $templateBulan;

            // Hitung realisasi pembayaran
            $nilaiPembayaran = $row->nilai_rupiah ?? 0;
            $dataPembayaran[$k][$s][$i][$bulan]['realisasi'] += $nilaiPembayaran;
            $totalPembayaran[$bulan]['realisasi'] += $nilaiPembayaran;
        }

        // Hitung selisih untuk PEMBAYARAN (tidak ada rencana, jadi selisih = realisasi)
        foreach ($dataPembayaran as $k => $subs) {
            foreach ($subs as $s => $items) {
                foreach ($items as $i => $bulanData) {
                    foreach ($bulanData as $b => $v) {
                        $dataPembayaran[$k][$s][$i][$b]['selisih'] = $v['realisasi'];
                        $dataPembayaran[$k][$s][$i][$b]['persen']  = 0;
                    }
                }
            }
        }

        foreach ($totalPembayaran as $b => $v) {
            $totalPembayaran[$b]['selisih'] = $v['realisasi'];
            $totalPembayaran[$b]['persen']  = 0;
        }

        return compact(
            'bulanList',
            'dataPenerima',
            'dataDropping',
            'dataPembayaran',
            'totalPenerima',
            'totalDropping',
            'totalPembayaran'
        );
    }
    

  
    public function getSubKriteria(Request $request)
    {
        $subKriteria = SubKriteria::where('id_kategori_kriteria', $request->kategori_id)
            ->pluck('nama_sub_kriteria', 'id');

        return response()->json($subKriteria);
    }

    public function getItem(Request $request)
    {
        $items = ItemSubKriteria::where('id_kategori_kriteria', $request->kategori_id)
            ->where('id_sub_kriteria', $request->sub_id)
            ->pluck('nama_item_sub_kriteria', 'id');

        return response()->json($items);
    }

    
    public function exportPdf(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $data = $this->getData($tahun);
        
        // TODO: Implementasi export PDF menggunakan library seperti DomPDF atau mPDF
        // $pdf = PDF::loadView('cash_bank.pembayaran.pdf', $data);
        // return $pdf->download('dashboard-pembayaran-'.$tahun.'.pdf');
        
        return response()->json(['message' => 'Export PDF belum diimplementasikan']);
    }

    public function export_excel(Request $request){
        return Excel::download(new ExcelPembayaran, 'Rekapan-PD .xlsx');
    }

    // public function exportExcel(Request $request)
    // {
    //     $tahun = $request->tahun ?? date('Y');
    //     $data = $this->getData($tahun);
        
    //     // TODO: Implementasi export Excel menggunakan Laravel Excel
    //     // return Excel::download(new DashboardPembayaranExport($tahun), 'dashboard-pembayaran-'.$tahun.'.xlsx');
        
    //     return response()->json(['message' => 'Export Excel belum diimplementasikan']);
    // }
}