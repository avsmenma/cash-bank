<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Dropping;
use App\Models\Penerima;
use App\Models\Permintaan;
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
        $bulanDari = $request->bulan_dari ?? 1;
        $bulanSampai = $request->bulan_sampai ?? 12;
        
        $kategoriList = KategoriKriteria::whereIn('tipe', ['keluar', 'Penerima'])
            ->pluck('nama_kriteria', 'id_kategori_kriteria');
        
        // Load data langsung untuk tampilan awal
        $data = $this->getData($tahun, $bulanDari, $bulanSampai);
        
        return view('cash_bank.dashboardPembayaran', array_merge(
            compact('kategoriList', 'tahun', 'bulanDari', 'bulanSampai'),
            $data,
        ));
    }

    public function getData($tahun, $bulanDari = 1, $bulanSampai = 12)
    {
        // ===== BULAN =====
        $bulanList = [
            'januari', 'februari', 'maret', 'april',
            'mei', 'juni', 'juli', 'agustus',
            'september', 'oktober', 'november', 'desember'
        ];

        // Mapping bulan ke angka
        $bulanMap = [
            1 => 'januari', 2 => 'februari', 3 => 'maret', 4 => 'april',
            5 => 'mei', 6 => 'juni', 7 => 'juli', 8 => 'agustus',
            9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'desember'
        ];

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
        
        // ✅ TRACKING BULAN YANG ADA TRANSAKSI
        $bulanAktif = [];

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

            foreach ($bulanList as $index => $bulan) {
                $bulanAngka = $index + 1; // 1 = januari, 2 = februari, dst
                
                // ✅ FILTER BERDASARKAN RANGE BULAN
                if ($bulanAngka < $bulanDari || $bulanAngka > $bulanSampai) {
                    continue;
                }
                
                $nilaiRencana = $row->$bulan ?? 0;
                if ($nilaiRencana > 0) {
                    $dataPenerima[$namaKategori][$bulan]['rencana'] += $nilaiRencana;
                    $totalPenerima[$bulan]['rencana'] += $nilaiRencana;
                    $bulanAktif[$bulan] = true;
                }
            }
        }

        /*
        |================================================
        | PENERIMA - REALISASI
        |================================================
        */
        $realisasiPenerima = Penerima::with('kategori')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', '>=', $bulanDari)  // ✅ FILTER BULAN
            ->whereMonth('tanggal', '<=', $bulanSampai) // ✅ FILTER BULAN
            ->get();

        foreach ($realisasiPenerima as $row) {
            $bulanAngka = Carbon::parse($row->tanggal)->month;
            $bulan = $bulanMap[$bulanAngka] ?? null;
            
            if (!$bulan) continue;

            $namaKategori = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';

            if (!isset($dataPenerima[$namaKategori])) {
                $dataPenerima[$namaKategori] = $templateBulan;
            }

            $nilaiRealisasi = ($row->nilai ?? 0) + ($row->ppn ?? 0) - ($row->potppn ?? 0);
            
            if ($nilaiRealisasi > 0) {
                $dataPenerima[$namaKategori][$bulan]['realisasi'] += $nilaiRealisasi;
                $totalPenerima[$bulan]['realisasi'] += $nilaiRealisasi;
                $bulanAktif[$bulan] = true;
            }
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
            ->whereBetween('bulan', [$bulanDari, $bulanSampai]) // ✅ FILTER BULAN
            ->get();

        foreach ($rencanaDropping as $row) {
            $bulanAngka = $row->bulan;
            $bulan = $bulanMap[$bulanAngka] ?? null;
            
            if (!$bulan) continue;

            $k = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';
            $s = $row->subKriteria->nama_sub_kriteria ?? 'Tidak Ada Sub';
            $i = $row->itemSubKriteria->nama_item_sub_kriteria ?? 'Tidak Ada Item';

            if (!isset($dataDropping[$k])) {
                $dataDropping[$k] = [];
            }
            if (!isset($dataDropping[$k][$s])) {
                $dataDropping[$k][$s] = [];
            }
            if (!isset($dataDropping[$k][$s][$i])) {
                $dataDropping[$k][$s][$i] = $templateBulan;
            }

            $nilaiRencana = ($row->M1 ?? 0) + ($row->M2 ?? 0) + ($row->M3 ?? 0) + ($row->M4 ?? 0);

            if ($nilaiRencana > 0) {
                $dataDropping[$k][$s][$i][$bulan]['rencana'] += $nilaiRencana;
                $totalDropping[$bulan]['rencana'] += $nilaiRencana;
                $bulanAktif[$bulan] = true;
            }
        }

        /*
        |================================================
        | DROPPING - REALISASI
        |================================================
        */
        $realisasiDropping = Dropping::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [$bulanDari, $bulanSampai]) // ✅ FILTER BULAN
            ->get();

        foreach ($realisasiDropping as $row) {
            $bulanAngka = $row->bulan;
            $bulan = $bulanMap[$bulanAngka] ?? null;
            
            if (!$bulan) continue;

            $k = $row->kategori->nama_kriteria ?? 'Tidak Dikategorikan';
            $s = $row->subKriteria->nama_sub_kriteria ?? 'Tidak Ada Sub';
            $i = $row->itemSubKriteria->nama_item_sub_kriteria ?? 'Tidak Ada Item';

            if (!isset($dataDropping[$k])) {
                $dataDropping[$k] = [];
            }
            if (!isset($dataDropping[$k][$s])) {
                $dataDropping[$k][$s] = [];
            }
            if (!isset($dataDropping[$k][$s][$i])) {
                $dataDropping[$k][$s][$i] = $templateBulan;
            }

            $nilaiRealisasi = ($row->M1 ?? 0) + ($row->M2 ?? 0) + ($row->M3 ?? 0) + ($row->M4 ?? 0);
            
            if ($nilaiRealisasi > 0) {
                $dataDropping[$k][$s][$i][$bulan]['realisasi'] += $nilaiRealisasi;
                $totalDropping[$bulan]['realisasi'] += $nilaiRealisasi;
                $bulanAktif[$bulan] = true;
            }
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

        // ✅ FILTER BULAN: Hanya bulan yang ada transaksi DAN dalam range
        $bulanListFiltered = [];
        foreach ($bulanList as $index => $bulan) {
            $bulanAngka = $index + 1;
            if ($bulanAngka >= $bulanDari && $bulanAngka <= $bulanSampai && isset($bulanAktif[$bulan])) {
                $bulanListFiltered[] = $bulan;
            }
        }

        return compact(
            'bulanListFiltered',
            'dataPenerima',
            'dataDropping',
            'totalPenerima',
            'totalDropping'
        );
    }
    
    
    public function exportPdf(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $data = $this->getData($tahun);
        
        
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