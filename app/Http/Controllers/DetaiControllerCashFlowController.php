<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permintaan;
use App\Models\Dropping;
use App\Models\BankKeluar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DetaiControllerCashFlowController extends Controller
{
    /**
     * Menampilkan halaman detail cashflow berdasarkan minggu tertentu
     */
    public function detail(Request $request)
    {
        try {
            $bulan = $request->get('bulan');
            $tahun = $request->get('tahun');
            $week = $request->get('week', 1); // Default minggu 1
            $idKategori = $request->get('id_kategori');
            $idSubKriteria = $request->get('id_sub_kriteria');
            $idItem = $request->get('id_item');
            $namaItem = $request->get('nama_item', '');

            // Validasi input
            if (!$bulan || !$tahun) {
                return redirect()->back()->with('error', 'Parameter bulan dan tahun diperlukan');
            }

            $namaBulanList = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            
            $namaBulan = $namaBulanList[$bulan] ?? '';

            // Hitung jumlah hari dalam bulan
            $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

            // Data untuk semua minggu dalam bulan
            $dataPerMinggu = [];
            
            for ($minggu = 1; $minggu <= 4; $minggu++) {
                $startDay = (($minggu - 1) * 7) + 1;
                $endDay = min($minggu * 7, $jumlahHari);
                
                // Query Permintaan untuk minggu ini
                $queryPermintaan = Permintaan::where('bulan', $bulan)
                    ->where('tahun', $tahun);
                
                if ($idKategori) $queryPermintaan->where('id_kategori_kriteria', $idKategori);
                if ($idSubKriteria) $queryPermintaan->where('id_sub_kriteria', $idSubKriteria);
                if ($idItem) $queryPermintaan->where('id_item_sub_kriteria', $idItem);
                
                $permintaanData = $queryPermintaan->get();
                $mColumn = 'M' . $minggu;
                $totalPermintaan = $permintaanData->sum($mColumn);
                
                // Query Dropping untuk minggu ini
                $queryDropping = Dropping::where('bulan', $bulan)
                    ->where('tahun', $tahun);
                
                if ($idKategori) $queryDropping->where('id_kategori_kriteria', $idKategori);
                if ($idSubKriteria) $queryDropping->where('id_sub_kriteria', $idSubKriteria);
                if ($idItem) $queryDropping->where('id_item_sub_kriteria', $idItem);
                
                $droppingData = $queryDropping->get();
                $totalDropping = $droppingData->sum($mColumn);
                
                // Query Pembayaran untuk minggu ini
                $startDate = Carbon::create($tahun, $bulan, $startDay)->startOfDay();
                $endDate = Carbon::create($tahun, $bulan, $endDay)->endOfDay();
                
                $queryPembayaran = BankKeluar::whereBetween('tanggal', [$startDate, $endDate]);
                
                if ($idKategori) $queryPembayaran->where('id_kategori_kriteria', $idKategori);
                if ($idSubKriteria) $queryPembayaran->where('id_sub_kriteria', $idSubKriteria);
                if ($idItem) $queryPembayaran->where('id_item_sub_kriteria', $idItem);
                
                $pembayaranData = $queryPembayaran->orderBy('tanggal', 'asc')->get();
                $totalPembayaran = $pembayaranData->sum('nilai_rupiah');
                
                // Simpan data per minggu
                $dataPerMinggu[$minggu] = [
                    'minggu' => $minggu,
                    'periode' => $startDay . ' - ' . $endDay . ' ' . $namaBulan . ' ' . $tahun,
                    'start_day' => $startDay,
                    'end_day' => $endDay,
                    'permintaan' => [
                        'data' => $permintaanData,
                        'total' => $totalPermintaan
                    ],
                    'dropping' => [
                        'data' => $droppingData,
                        'total' => $totalDropping
                    ],
                    'pembayaran' => [
                        'data' => $pembayaranData,
                        'total' => $totalPembayaran
                    ]
                ];
            }

            // Data untuk semua bulan (summary)
            $totalPermintaanBulan = 0;
            $totalDroppingBulan = 0;
            $totalPembayaranBulan = 0;
            
            foreach ($dataPerMinggu as $data) {
                $totalPermintaanBulan += $data['permintaan']['total'];
                $totalDroppingBulan += $data['dropping']['total'];
                $totalPembayaranBulan += $data['pembayaran']['total'];
            }

            return view('detailPd_detailPvD.detailPvd', compact(
                'bulan', 
                'tahun', 
                'namaBulan', 
                'namaItem',
                'dataPerMinggu',
                'totalPermintaanBulan',
                'totalDroppingBulan',
                'totalPembayaranBulan',
                'idKategori', 
                'idSubKriteria', 
                'idItem'
            ));
            
        } catch (\Exception $e) {
            Log::error('Cashflow Detail Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}