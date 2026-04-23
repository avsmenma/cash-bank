<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RingkasanPembayaranController extends Controller
{
    /**
     * Halaman utama Ringkasan Pembayaran Hierarki
     * Menampilkan SEMUA kategori, sub, dan item dari tabel master,
     * lalu overlay data transaksi dari bank_keluars.
     */
    public function index(Request $request)
    {
        $tahun       = $request->tahun ?? date('Y');
        $dariBulan   = $request->dari_bulan ?? 1;
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
        // LOAD ALL MASTER DATA
        // ======================================================
        $allKategori = DB::table('kategori_kriteria')
            ->where('tipe', 'Keluar')
            ->orderBy('id_kategori_kriteria')
            ->get();

        $allSub = DB::table('sub_kriteria')
            ->whereIn('id_kategori_kriteria', $allKategori->pluck('id_kategori_kriteria'))
            ->orderBy('id_sub_kriteria')
            ->get();

        $allItem = DB::table('item_sub_kriteria')
            ->whereIn('id_sub_kriteria', $allSub->pluck('id_sub_kriteria'))
            ->orderBy('id_item_sub_kriteria')
            ->get();

        // ======================================================
        // QUERY TRANSACTION SUMS
        // ======================================================
        $rows = DB::table('droppings')
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [$dariBulan, $sampaiBulan])
            ->whereNotNull('id_kategori_kriteria')
            ->select([
                'id_kategori_kriteria',
                'id_sub_kriteria',
                'id_item_sub_kriteria',
                'bulan',
                DB::raw('SUM(COALESCE(M1, 0) + COALESCE(M2, 0) + COALESCE(M3, 0) + COALESCE(M4, 0)) as total_nilai'),
            ])
            ->groupBy(
                'id_kategori_kriteria',
                'id_sub_kriteria',
                'id_item_sub_kriteria',
                'bulan'
            )
            ->get();

        // Index transaction data for fast lookup: [katId][subId][itemId][bulan] = nilai
        $txIndex = [];
        foreach ($rows as $row) {
            $k = $row->id_kategori_kriteria;
            $s = $row->id_sub_kriteria ?? 'none';
            $i = $row->id_item_sub_kriteria ?? 'none';
            $b = $row->bulan;
            $txIndex[$k][$s][$i][$b] = ($txIndex[$k][$s][$i][$b] ?? 0) + $row->total_nilai;
        }

        // ======================================================
        // BUILD COMPLETE HIERARCHY from master data + overlay tx
        // ======================================================
        $hierarki = [];

        foreach ($allKategori as $kat) {
            $katId = $kat->id_kategori_kriteria;
            $hierarki[$katId] = [
                'nama'  => $kat->nama_kriteria,
                'bulan' => [],
                'total' => 0,
                'subs'  => [],
            ];

            $subs = $allSub->where('id_kategori_kriteria', $katId);
            foreach ($subs as $sub) {
                $subId = $sub->id_sub_kriteria;
                $hierarki[$katId]['subs'][$subId] = [
                    'id'    => $subId,
                    'nama'  => trim($sub->nama_sub_kriteria),
                    'bulan' => [],
                    'total' => 0,
                    'items' => [],
                ];

                $items = $allItem->where('id_sub_kriteria', $subId);

                // Deduplicate items by nama (same name = same logical item)
                $uniqueItems = [];
                foreach ($items as $item) {
                    $itemName = trim($item->nama_item_sub_kriteria);
                    if (!isset($uniqueItems[$itemName])) {
                        $uniqueItems[$itemName] = [
                            'id'   => $item->id_item_sub_kriteria,
                            'nama' => $itemName,
                            'ids'  => [$item->id_item_sub_kriteria], // all IDs for tx lookup
                        ];
                    } else {
                        $uniqueItems[$itemName]['ids'][] = $item->id_item_sub_kriteria;
                    }
                }

                foreach ($uniqueItems as $itemName => $uItem) {
                    $itemBulan = [];
                    $itemTotal = 0;

                    foreach ($bulanAktif as $bNum => $bName) {
                        $val = 0;
                        // Sum values across all duplicate IDs
                        foreach ($uItem['ids'] as $iid) {
                            $val += $txIndex[$katId][$subId][$iid][$bNum] ?? 0;
                        }
                        $itemBulan[$bNum] = $val;
                        $itemTotal += $val;
                    }

                    $hierarki[$katId]['subs'][$subId]['items'][$uItem['id']] = [
                        'id'    => $uItem['id'],
                        'nama'  => $uItem['nama'],
                        'bulan' => $itemBulan,
                        'total' => $itemTotal,
                    ];

                    // Accumulate to sub level
                    foreach ($bulanAktif as $bNum => $bName) {
                        $hierarki[$katId]['subs'][$subId]['bulan'][$bNum] =
                            ($hierarki[$katId]['subs'][$subId]['bulan'][$bNum] ?? 0) + $itemBulan[$bNum];
                    }
                    $hierarki[$katId]['subs'][$subId]['total'] += $itemTotal;
                }

                // Accumulate to kategori level
                foreach ($bulanAktif as $bNum => $bName) {
                    $hierarki[$katId]['bulan'][$bNum] =
                        ($hierarki[$katId]['bulan'][$bNum] ?? 0) + ($hierarki[$katId]['subs'][$subId]['bulan'][$bNum] ?? 0);
                }
                $hierarki[$katId]['total'] += $hierarki[$katId]['subs'][$subId]['total'];
            }
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
     * Halaman detail transaksi — navigated from hierarchy table
     */
    public function detail(Request $request)
    {
        $tahun       = $request->tahun ?? date('Y');
        $dariBulan   = $request->dari_bulan ?? 1;
        $sampaiBulan = $request->sampai_bulan ?? 12;
        $kategoriId  = $request->kategori_id;
        $subId       = $request->sub_kriteria_id;
        $itemId      = $request->item_sub_kriteria_id;

        $bulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',      6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',  9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $query = DB::table('bank_keluars')
            ->leftJoin('kategori_kriteria', 'bank_keluars.id_kategori_kriteria', '=', 'kategori_kriteria.id_kategori_kriteria')
            ->leftJoin('sub_kriteria', 'bank_keluars.id_sub_kriteria', '=', 'sub_kriteria.id_sub_kriteria')
            ->leftJoin('item_sub_kriteria', 'bank_keluars.id_item_sub_kriteria', '=', 'item_sub_kriteria.id_item_sub_kriteria')
            ->leftJoin('bank_tujuan', 'bank_keluars.id_bank_tujuan', '=', 'bank_tujuan.id_bank_tujuan')
            ->leftJoin('jenis_pembayarans', 'bank_keluars.id_jenis_pembayaran', '=', 'jenis_pembayarans.id_jenis_pembayaran')
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
                'bank_keluars.agenda_tahun',
                'bank_keluars.uraian',
                'bank_keluars.penerima',
                'bank_keluars.kredit',
                'bank_keluars.keterangan',
                'bank_keluars.jenis_pembayaran as jenis_pembayaran_str',
                'kategori_kriteria.nama_kriteria',
                'sub_kriteria.nama_sub_kriteria',
                'item_sub_kriteria.nama_item_sub_kriteria',
                'bank_tujuan.nama_tujuan as kebun',
                'jenis_pembayarans.nama_jenis_pembayaran',
            ])
            ->orderBy('bank_keluars.tanggal')
            ->get();

        // Build label & breadcrumb
        $label = '';
        $breadcrumb = '';
        if ($kategoriId) {
            $kat = DB::table('kategori_kriteria')->where('id_kategori_kriteria', $kategoriId)->first();
            $breadcrumb = $kat->nama_kriteria ?? '';
            $label = $breadcrumb;
        }
        if ($subId) {
            $sub = DB::table('sub_kriteria')->where('id_sub_kriteria', $subId)->first();
            $label = $sub->nama_sub_kriteria ?? '';
        }
        if ($itemId) {
            $item = DB::table('item_sub_kriteria')->where('id_item_sub_kriteria', $itemId)->first();
            $label = $item->nama_item_sub_kriteria ?? '';
        }

        $totalNilai = $transaksi->sum('kredit');
        $periodeLabel = ($bulanMap[$dariBulan] ?? '') . ' — ' . ($bulanMap[$sampaiBulan] ?? '') . ' ' . $tahun;

        return view('cash_bank.ringkasanDetail', compact(
            'transaksi',
            'label',
            'breadcrumb',
            'totalNilai',
            'periodeLabel',
            'tahun',
            'dariBulan',
            'sampaiBulan',
            'kategoriId',
            'subId',
            'itemId'
        ));
    }
}
