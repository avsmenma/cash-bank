<?php

namespace App\Http\Controllers;

use App\Models\BankMasuk;
use App\Models\BankKeluar;
use Illuminate\Http\Request;
use App\Models\KategoriKriteria;
use Illuminate\Support\Facades\DB;
use App\Models\GabunganMasukKeluar;

class dashboardController extends Controller
{
    public function index()
    {
        // Ambil tahun saat ini atau dari request
        $tahun = request('tahun', date('Y'));

        // Nama bulan dalam bahasa Indonesia
        $bulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        
        // Inisialisasi array untuk 12 bulan dengan nilai 0
        $total_pengeluaran = array_fill(0, 12, 0);
        $total_pemasukkan = array_fill(0, 12, 0);

        // Ambil data pengeluaran per bulan untuk tahun tertentu
        $data_pengeluaran = BankKeluar::select(
            DB::raw("MONTH(tanggal) as bulan_angka"),
            DB::raw("SUM(kredit) as total")
        )
        ->whereYear('tanggal', $tahun)
        ->groupBy(DB::raw("MONTH(tanggal)"))
        ->orderBy(DB::raw("MONTH(tanggal)"))
        ->get();

        // Ambil data pemasukkan per bulan untuk tahun tertentu
        $data_pemasukkan = BankMasuk::select(
            DB::raw("MONTH(tanggal) as bulan_angka"),
            DB::raw("SUM(debet) as total")
        )
        ->whereYear('tanggal', $tahun)
        ->groupBy(DB::raw("MONTH(tanggal)"))
        ->orderBy(DB::raw("MONTH(tanggal)"))
        ->get();

        // Isi data pengeluaran yang ada ke array
        foreach ($data_pengeluaran as $data) {
            $total_pengeluaran[$data->bulan_angka - 1] = (int) $data->total;
        }

        // Isi data pemasukkan yang ada ke array
        foreach ($data_pemasukkan as $data) {
            $total_pemasukkan[$data->bulan_angka - 1] = (int) $data->total;
        }

        // Statistik untuk card
        $total_pengeluaran_card = BankKeluar::whereYear('tanggal', $tahun)
            ->sum('kredit');
        
        $total_pemasukkan_card = BankMasuk::whereYear('tanggal', $tahun)
            ->sum('debet');

        // grafik berdasarkan kategori kriteria
        $grafikKategori = BankKeluar::select(
        DB::raw("MONTH(tanggal) as bulan"),
        DB::raw("SUM(kredit) as total"),
            'id_kategori_kriteria'
        )
        ->whereYear('tanggal', $tahun)
        ->groupBy('bulan', 'id_kategori_kriteria')
        ->with('kategori')
        ->get();
        // Ambil nama kategori
        $kategoriList = KategoriKriteria::where('tipe','keluar')
        ->pluck('nama_kriteria', 'id_kategori_kriteria');

        // Siapkan array kosong untuk 12 bulan
        $kategori_total = [];
        foreach ($kategoriList as $id => $nama) {
            $kategori_total[$id] = array_fill(0, 12, 0);
        }

        // Isi data hasil query
        foreach ($grafikKategori as $item) {
            $index_bulan = $item->bulan - 1;
            $kategori_total[$item->id_kategori_kriteria][$index_bulan] = (int) $item->total;
        }

          /* ================= DATA AGENDA (TETAP) ================= */
        // $agendaBelumSiapBayar = DB::connection('mysql_agenda_online')
        // ->table('dokumens')
        // ->whereYear('tanggal_masuk', $tahun)
        // // ->where('status_pembayaran', 'belum_siap_dibayar')
        // ->where('status', '!=','sent_to_pembayaran')
        // ->where('current_handler', '!=','pembayaran')
        // ->where('status_pembayaran', null)
        // ->value(DB::raw('SUM(dokumens.nilai_rupiah)'));
        // $agendaBelumSiapBayar = DB::connection('mysql_agenda_online')
        // ->table('dokumens')
        // ->whereYear('tanggal_masuk', $tahun)
        // ->where('status', '!=', 'sent_to_pembayaran')
        // ->where('current_handler', '!=', 'pembayaran')
        // ->whereNull('status_pembayaran')
        // ->value(DB::raw('SUM(dokumens.nilai_rupiah)'));

        // $agendaSiapBayar = DB::connection('mysql_agenda_online')
        // ->table('dokumens')
        // ->whereYear('tanggal_masuk', $tahun)
        // ->where('current_handler', 'pembayaran')
        // ->where('status_pembayaran', 'siap_dibayar')
        // ->value(DB::raw('SUM(dokumens.nilai_rupiah)'));


        // Ubah array associative ke indexed array
        $kategori_nama = array_values($kategoriList->toArray());
        $kategori_total = array_values($kategori_total);
                return view('cash_bank.dashboard', compact(
                    'total_pengeluaran', 
                    'total_pemasukkan', 
                    'bulan',
                    'total_pengeluaran_card',
                    'total_pemasukkan_card',
                    'tahun', 'grafikKategori','kategori_nama',
                    'kategori_total',
                    
                ));
            }
}