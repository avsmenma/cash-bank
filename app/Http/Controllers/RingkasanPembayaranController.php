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
                        'ids'   => $uItem['ids'],
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
        $itemIds     = array_values(array_filter((array) $request->input('item_sub_kriteria_ids', [])));

        $bulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',      6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',  9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $query = DB::table('droppings')
            ->leftJoin('kategori_kriteria', 'droppings.id_kategori_kriteria', '=', 'kategori_kriteria.id_kategori_kriteria')
            ->leftJoin('sub_kriteria', 'droppings.id_sub_kriteria', '=', 'sub_kriteria.id_sub_kriteria')
            ->leftJoin('item_sub_kriteria', 'droppings.id_item_sub_kriteria', '=', 'item_sub_kriteria.id_item_sub_kriteria')
            ->where('droppings.tahun', $tahun)
            ->whereBetween('droppings.bulan', [$dariBulan, $sampaiBulan]);

        if ($kategoriId) {
            $query->where('droppings.id_kategori_kriteria', $kategoriId);
        }
        if ($subId) {
            $query->where('droppings.id_sub_kriteria', $subId);
        }
        if (!empty($itemIds)) {
            $query->whereIn('droppings.id_item_sub_kriteria', $itemIds);
        } elseif ($itemId) {
            $query->where('droppings.id_item_sub_kriteria', $itemId);
        }

        $droppingsData = $query->get();
        
        $transaksiArr = [];
        $totalNilai = 0;

        foreach ($droppingsData as $d) {
            $bulanName = $bulanMap[$d->bulan] ?? '';
            
            for ($i = 1; $i <= 4; $i++) {
                $m = "M$i";
                $nilai = $d->$m ?? 0;
                
                if ($nilai > 0) {
                    $tanggal = $d->tahun . '-' . str_pad($d->bulan, 2, '0', STR_PAD_LEFT) . '-01';
                    $transaksiArr[] = (object) [
                        'tanggal' => $tanggal,
                        'uraian' => 'Realisasi ' . $bulanName . ' - Minggu ke ' . $i,
                        'penerima' => '-',
                        'kredit' => $nilai,
                        'keterangan' => '',
                        'jenis_pembayaran_str' => '-',
                        'nama_kriteria' => $d->nama_kriteria,
                        'nama_sub_kriteria' => $d->nama_sub_kriteria,
                        'nama_item_sub_kriteria' => $d->nama_item_sub_kriteria,
                        'kebun' => '-',
                        'nama_jenis_pembayaran' => '-',
                        'sort_date' => $d->tahun . str_pad($d->bulan, 2, '0', STR_PAD_LEFT) . $i
                    ];
                    $totalNilai += $nilai;
                }
            }
        }

        usort($transaksiArr, function($a, $b) {
            return strcmp($a->sort_date, $b->sort_date);
        });

        $transaksi = collect($transaksiArr);

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
        if (!empty($itemIds)) {
            $item = DB::table('item_sub_kriteria')->where('id_item_sub_kriteria', $itemIds[0])->first();
            $label = $item->nama_item_sub_kriteria ?? '';
        } elseif ($itemId) {
            $item = DB::table('item_sub_kriteria')->where('id_item_sub_kriteria', $itemId)->first();
            $label = $item->nama_item_sub_kriteria ?? '';
        }
        if ($label === '') {
            $label = 'Grand Total Pembayaran';
        }

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
