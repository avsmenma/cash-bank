<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RingkasanPembayaranController extends Controller
{
    /**
     * Halaman utama Ringkasan Pembayaran Hierarki
     */
    public function index(Request $request)
    {
        $tahun      = $request->tahun ?? date('Y');
        $dariBulan  = $request->dari_bulan ?? 1;
        $sampaiBulan = $request->sampai_bulan ?? date('n');

        $bulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',      6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',  9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // Build list of active months in filter range
        $bulanAktif = [];
        for ($i = $dariBulan; $i <= $sampaiBulan; $i++) {
            $bulanAktif[$i] = $bulanMap[$i];
        }

        // ======================================================
        // QUERY: Group bank_keluars by kategori → sub → item,
        //        pivot by month
        // ======================================================
        $rows = DB::table('bank_keluars')
            ->join('kategori_kriteria', 'bank_keluars.id_kategori_kriteria', '=', 'kategori_kriteria.id_kategori_kriteria')
            ->leftJoin('sub_kriteria', 'bank_keluars.id_sub_kriteria', '=', 'sub_kriteria.id_sub_kriteria')
            ->leftJoin('item_sub_kriteria', 'bank_keluars.id_item_sub_kriteria', '=', 'item_sub_kriteria.id_item_sub_kriteria')
            ->whereYear('bank_keluars.tanggal', $tahun)
            ->whereMonth('bank_keluars.tanggal', '>=', $dariBulan)
            ->whereMonth('bank_keluars.tanggal', '<=', $sampaiBulan)
            ->select([
                'kategori_kriteria.id_kategori_kriteria',
                'kategori_kriteria.nama_kriteria',
                'sub_kriteria.id_sub_kriteria',
                'sub_kriteria.nama_sub_kriteria',
                'item_sub_kriteria.id_item_sub_kriteria',
                'item_sub_kriteria.nama_item_sub_kriteria',
                DB::raw('MONTH(bank_keluars.tanggal) as bulan'),
                DB::raw('SUM(bank_keluars.kredit) as total_nilai'),
            ])
            ->groupBy(
                'kategori_kriteria.id_kategori_kriteria',
                'kategori_kriteria.nama_kriteria',
                'sub_kriteria.id_sub_kriteria',
                'sub_kriteria.nama_sub_kriteria',
                'item_sub_kriteria.id_item_sub_kriteria',
                'item_sub_kriteria.nama_item_sub_kriteria',
                DB::raw('MONTH(bank_keluars.tanggal)')
            )
            ->orderBy('kategori_kriteria.nama_kriteria')
            ->orderBy('sub_kriteria.nama_sub_kriteria')
            ->orderBy('item_sub_kriteria.nama_item_sub_kriteria')
            ->get();

        // ======================================================
        // BUILD HIERARCHY:  hierarki[kategori][sub][item][bulan] = nilai
        // ======================================================
        $hierarki = [];

        foreach ($rows as $row) {
            $katId   = $row->id_kategori_kriteria;
            $katNama = $row->nama_kriteria;
            $subId   = $row->id_sub_kriteria;
            $subNama = $row->nama_sub_kriteria ?? '-';
            $itemId  = $row->id_item_sub_kriteria;
            $itemNama = $row->nama_item_sub_kriteria ?? '-';
            $bulan   = $row->bulan;
            $nilai   = $row->total_nilai ?? 0;

            if (!isset($hierarki[$katId])) {
                $hierarki[$katId] = [
                    'nama'  => $katNama,
                    'bulan' => [],
                    'total' => 0,
                    'subs'  => [],
                ];
            }

            // Sub Kriteria
            $subKey = $subId ?? 'none';
            if (!isset($hierarki[$katId]['subs'][$subKey])) {
                $hierarki[$katId]['subs'][$subKey] = [
                    'id'    => $subId,
                    'nama'  => $subNama,
                    'bulan' => [],
                    'total' => 0,
                    'items' => [],
                ];
            }

            // Item Sub Kriteria
            $itemKey = $itemId ?? 'none';
            if (!isset($hierarki[$katId]['subs'][$subKey]['items'][$itemKey])) {
                $hierarki[$katId]['subs'][$subKey]['items'][$itemKey] = [
                    'id'    => $itemId,
                    'nama'  => $itemNama,
                    'bulan' => [],
                    'total' => 0,
                ];
            }

            // Item level
            $hierarki[$katId]['subs'][$subKey]['items'][$itemKey]['bulan'][$bulan] =
                ($hierarki[$katId]['subs'][$subKey]['items'][$itemKey]['bulan'][$bulan] ?? 0) + $nilai;
            $hierarki[$katId]['subs'][$subKey]['items'][$itemKey]['total'] += $nilai;

            // Sub level
            $hierarki[$katId]['subs'][$subKey]['bulan'][$bulan] =
                ($hierarki[$katId]['subs'][$subKey]['bulan'][$bulan] ?? 0) + $nilai;
            $hierarki[$katId]['subs'][$subKey]['total'] += $nilai;

            // Kategori level
            $hierarki[$katId]['bulan'][$bulan] =
                ($hierarki[$katId]['bulan'][$bulan] ?? 0) + $nilai;
            $hierarki[$katId]['total'] += $nilai;
        }

        // Grand total per bulan
        $grandTotal = [];
        $grandTotalAll = 0;
        foreach ($hierarki as $kat) {
            foreach ($bulanAktif as $bNum => $bName) {
                $grandTotal[$bNum] = ($grandTotal[$bNum] ?? 0) + ($kat['bulan'][$bNum] ?? 0);
            }
            $grandTotalAll += $kat['total'];
        }

        return view('cash_bank.ringkasanPembayaran', compact(
            'hierarki',
            'bulanAktif',
            'grandTotal',
            'grandTotalAll',
            'tahun',
            'dariBulan',
            'sampaiBulan',
            'bulanMap'
        ));
    }

    /**
     * AJAX endpoint: Detail transaksi untuk drawer
     */
    public function detail(Request $request)
    {
        $tahun       = $request->tahun ?? date('Y');
        $dariBulan   = $request->dari_bulan ?? 1;
        $sampaiBulan = $request->sampai_bulan ?? 12;
        $kategoriId  = $request->kategori_id;
        $subId       = $request->sub_kriteria_id;
        $itemId      = $request->item_sub_kriteria_id;

        $query = DB::table('bank_keluars')
            ->leftJoin('kategori_kriteria', 'bank_keluars.id_kategori_kriteria', '=', 'kategori_kriteria.id_kategori_kriteria')
            ->leftJoin('sub_kriteria', 'bank_keluars.id_sub_kriteria', '=', 'sub_kriteria.id_sub_kriteria')
            ->leftJoin('item_sub_kriteria', 'bank_keluars.id_item_sub_kriteria', '=', 'item_sub_kriteria.id_item_sub_kriteria')
            ->whereYear('bank_keluars.tanggal', $tahun)
            ->whereMonth('bank_keluars.tanggal', '>=', $dariBulan)
            ->whereMonth('bank_keluars.tanggal', '<=', $sampaiBulan);

        if ($kategoriId) {
            $query->where('bank_keluars.id_kategori_kriteria', $kategoriId);
        }
        if ($subId) {
            $query->where('bank_keluars.id_sub_kriteria', $subId);
        }
        if ($itemId) {
            $query->where('bank_keluars.id_item_sub_kriteria', $itemId);
        }

        $transaksi = $query->select([
                'bank_keluars.tanggal',
                'bank_keluars.uraian',
                'bank_keluars.penerima as dibayarkan_kepada',
                'bank_keluars.kredit as nilai',
                'bank_keluars.no_agenda',
                'bank_keluars.keterangan',
                'kategori_kriteria.nama_kriteria',
                'sub_kriteria.nama_sub_kriteria',
                'item_sub_kriteria.nama_item_sub_kriteria',
            ])
            ->orderBy('bank_keluars.tanggal')
            ->get();

        return response()->json($transaksi);
    }
}
