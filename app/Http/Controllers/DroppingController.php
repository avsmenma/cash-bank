<?php

namespace App\Http\Controllers;

use App\Models\Dropping;
use App\Models\SubKriteria;
use Illuminate\Http\Request;
use App\Models\ItemSubKriteria;
use App\Models\RencanaDropping;
use App\Models\KategoriKriteria;

use Illuminate\Support\Facades\DB;

class DroppingController extends Controller
{
    public function index()
    {
        $kategori = KategoriKriteria::where('tipe', 'keluar')->get();

        return view('cash_bank.pembayaran.dropping', compact('kategori'));
    }

    public function getSub($id)
    {
        return SubKriteria::where('id_kategori_kriteria', $id)->get();
    }

    public function getTable(Request $request)
    {
        $subKriteriaId = $request->sub;
        $tahun = $request->tahun;
        $bulan = $request->bulan;

        if (!$subKriteriaId || !$tahun || !$bulan) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        // Get items from sub kriteria
        $items = ItemSubKriteria::where('id_sub_kriteria', $subKriteriaId)
            ->get();

        // Custom sort order matching reference document
        $sortOrder = [
            'Gaji dan Tunjangan',
            'Cuti Tahunan',
            'Cuti Panjang',
            'THR',
            'Bonus',
            'PPh Pasal 21',
            'Iuran Dapenbun (Normal)',
            'Penghargaan Masa Kerja',
            'Iuran BPJS B. Perusahaan',
            'SHT (Cicilan)',
            'Lainnya',
        ];

        $items = $items->sort(function ($a, $b) use ($sortOrder) {
            $posA = array_search($a->nama_item_sub_kriteria, $sortOrder);
            $posB = array_search($b->nama_item_sub_kriteria, $sortOrder);

            // If both are in the sort order, sort by position
            if ($posA !== false && $posB !== false) {
                return $posA - $posB;
            }
            // If only A is in the sort order, A comes first
            if ($posA !== false)
                return -1;
            // If only B is in the sort order, B comes first
            if ($posB !== false)
                return 1;
            // Both not in sort order, sort alphabetically
            return strcmp($a->nama_item_sub_kriteria, $b->nama_item_sub_kriteria);
        })->values();

        if ($items->isEmpty()) {
            return view('cash_bank.pembayaran.createDropping', [
                'items' => [],
                'data' => [],
                'bulan' => $bulan,
                'tahun' => $tahun,
                'subKriteriaId' => $subKriteriaId,
                'message' => 'Tidak ada item untuk sub kriteria ini'
            ]);
        }

        // Get existing data for these items
        $existingData = Dropping::where('id_sub_kriteria', $subKriteriaId)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->whereIn('id_item_sub_kriteria', $items->pluck('id_item_sub_kriteria'))
            ->get()
            ->keyBy('id_item_sub_kriteria');

        // Format data for view
        $data = [];
        foreach ($items as $item) {
            $dropping = $existingData->get($item->id_item_sub_kriteria);
            $data[$item->id_item_sub_kriteria] = [
                'M1' => $dropping ? $dropping->M1 : 0,
                'M2' => $dropping ? $dropping->M2 : 0,
                'M3' => $dropping ? $dropping->M3 : 0,
                'M4' => $dropping ? $dropping->M4 : 0,
            ];
        }

        return view('cash_bank.pembayaran.createDropping', compact('items', 'data', 'bulan', 'tahun', 'subKriteriaId'));
    }

    public function saveData(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required|exists:item_sub_kriteria,id_item_sub_kriteria',
            'sub_kriteria' => 'required|exists:sub_kriteria,id_sub_kriteria',
            'kolom' => 'required|string|in:M1,M2,M3,M4',
            'nilai' => 'nullable|numeric',
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12'
        ]);

        try {
            $nilai = $validated['nilai'] ?? 0;
            $kolom = $validated['kolom'];

            // Get kategori from sub_kriteria
            $subKriteria = SubKriteria::find($validated['sub_kriteria']);

            // Find or create dropping record
            $dropping = Dropping::firstOrNew([
                'id_item_sub_kriteria' => $validated['item'],
                'id_sub_kriteria' => $validated['sub_kriteria'],
                'tahun' => $validated['tahun'],
                'bulan' => $validated['bulan']
            ]);

            // Set kategori if creating new
            if (!$dropping->exists) {
                $dropping->id_kategori_kriteria = $subKriteria->id_kategori_kriteria;
            }

            // Update the specific column (M1, M2, M3, or M4)
            $dropping->$kolom = $nilai;
            $dropping->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveBatch(Request $request)
    {
        try {
            $items = $request->input('items', []);

            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data untuk disimpan'
                ], 400);
            }

            DB::beginTransaction();

            foreach ($items as $item) {
                $kolom = $item['kolom'] ?? null;

                if (!$kolom || !in_array($kolom, ['M1', 'M2', 'M3', 'M4'])) {
                    continue;
                }

                $itemId = $item['item'] ?? null;
                $subKriteriaId = $item['sub_kriteria'] ?? null;
                $tahun = $item['tahun'] ?? null;
                $bulan = $item['bulan'] ?? null;
                $nilai = $item['nilai'] ?? 0;

                if (!$itemId || !$subKriteriaId || !$tahun || !$bulan) {
                    continue;
                }

                $dropping = Dropping::firstOrNew([
                    'id_item_sub_kriteria' => $itemId,
                    'id_sub_kriteria' => $subKriteriaId,
                    'tahun' => $tahun,
                    'bulan' => $bulan
                ]);

                if (!$dropping->exists) {
                    $subKriteria = SubKriteria::find($subKriteriaId);
                    if ($subKriteria) {
                        $dropping->id_kategori_kriteria = $subKriteria->id_kategori_kriteria;
                    }
                }

                $dropping->$kolom = $nilai;
                $dropping->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semua data berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteData(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required',
            'kolom' => 'required',
            'tahun' => 'required',
            'bulan' => 'required'
        ]);

        try {
            $dropping = Dropping::where('id_item_sub_kriteria', $validated['item'])
                ->where('tahun', $validated['tahun'])
                ->where('bulan', $validated['bulan'])
                ->first();

            if ($dropping) {
                $kolom = $validated['kolom'];
                $dropping->$kolom = 0;
                $dropping->save();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function rencana(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $sub = $request->sub;

        if (!$sub) {
            return response('Sub kriteria kosong', 400);
        }

        $items = ItemSubKriteria::where('id_sub_kriteria', $sub)->get();

        $data = RencanaDropping::where('tahun', $tahun)
            ->where('id_sub_kriteria', $sub)
            ->get()
            ->keyBy('id_item_sub_kriteria');

        return view('cash_bank.pembayaran.rencanaDropping', compact(
            'items',
            'data',
            'tahun'
        ));
    }
    public function saveRencana(Request $request)
    {
        try {
            // Cari data berdasarkan kategori dan tahun (bukan id_rencana)
            $rencana = RencanaDropping::firstOrNew([
                'id_kategori_kriteria' => $request->kategori,
                'id_sub_kriteria' => $request->subKriteria,
                'id_item_sub_kriteria' => $request->item,
                'tahun' => $request->tahun,
            ]);

            // Update hanya bulan yang diedit, bulan lain tetap dipertahankan
            $rencana->{$request->bulan} = $request->nilai;
            $rencana->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'id' => $rencana->id_rencana_dropping
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cashFlow(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

        \Log::info('CashFlow Request - Tahun: ' . $tahun);

        // Get all data p for selected year
        $data = Dropping::with(['kategori', 'subKriteria', 'itemSubKriteria'])
            ->where('tahun', $tahun)
            ->orderBy('id_kategori_kriteria')
            ->orderBy('id_sub_kriteria')
            ->orderBy('id_item_sub_kriteria')
            ->get();

        \Log::info('Total Data Found: ' . $data->count());


        $result = [];
        $totals = array_fill(1, 12, 0);
        $grandTotal = 0;

        foreach ($data as $row) {
            $kategoriName = $row->kategori->nama_kriteria ?? 'Tidak ada kategori';
            $subName = $row->subKriteria->nama_sub_kriteria ?? 'Tidak ada sub';
            $itemName = $row->itemSubKriteria->nama_item_sub_kriteria ?? 'Tidak ada item';

            if (!isset($result[$kategoriName])) {
                $result[$kategoriName] = [
                    'subs' => [],
                    'totals' => array_fill(1, 12, 0)
                ];
            }

            if (!isset($result[$kategoriName]['subs'][$subName])) {
                $result[$kategoriName]['subs'][$subName] = [
                    'items' => [],
                    'totals' => array_fill(1, 12, 0)
                ];
            }

            if (!isset($result[$kategoriName]['subs'][$subName]['items'][$itemName])) {
                $result[$kategoriName]['subs'][$subName]['items'][$itemName] = array_fill(1, 12, 0);
            }

            $bulan = $row->bulan;
            $totalBulan = $row->M1 + $row->M2 + $row->M3 + $row->M4;

            $result[$kategoriName]['subs'][$subName]['items'][$itemName][$bulan] += $totalBulan;
            $result[$kategoriName]['subs'][$subName]['totals'][$bulan] += $totalBulan;
            $result[$kategoriName]['totals'][$bulan] += $totalBulan;
            $totals[$bulan] += $totalBulan;
            $grandTotal += $totalBulan;
        }

        \Log::info('Result Count: ' . count($result));

        return view('cash_bank.pembayaran.cashFlowDropping', compact('result', 'tahun', 'totals', 'grandTotal'));
    }

    public function gabungan(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

        $bulanList = [
            'januari',
            'februari',
            'maret',
            'april',
            'mei',
            'juni',
            'juli',
            'agustus',
            'september',
            'oktober',
            'november',
            'desember'
        ];
        $mapBulan = [
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
        $idsRencana = DB::table('rencana_droppings')
            ->where('tahun', $tahun)
            ->pluck('id_item_sub_kriteria');

        $idsDropping = DB::table('droppings')
            ->where('tahun', $tahun)
            ->pluck('id_item_sub_kriteria');

        $itemIds = $idsRencana->merge($idsDropping)->unique();

        $items = ItemSubKriteria::with(['subKriteria.kategori'])
            ->whereIn('id_item_sub_kriteria', $itemIds)
            ->get();
        $data = [];

        foreach ($items as $item) {
            $kategori = $item->subKriteria->kategori->nama_kriteria;
            $sub = $item->subKriteria->nama_sub_kriteria;
            $itemNama = $item->nama_item_sub_kriteria;

            foreach ($bulanList as $bulan) {

                // ===== RENCANA (PER ITEM) =====
                $rencana = RencanaDropping::
                    where('id_item_sub_kriteria', $item->id_item_sub_kriteria)
                    ->where('tahun', $tahun)
                    ->sum($bulan);

                $bulanAngka = $mapBulan[$bulan];

                $realisasi = Dropping::
                    where('id_item_sub_kriteria', $item->id_item_sub_kriteria)
                    ->where('tahun', $tahun)
                    ->where('bulan', $bulanAngka)
                    ->sum(DB::raw('M1 + M2 + M3 + M4'));

                $selisih = $realisasi - $rencana;
                $persen = $rencana > 0 ? ($realisasi / $rencana) * 100 : 0;

                $data[$kategori][$sub][$itemNama][$bulan] = [
                    'rencana' => $rencana,
                    'realisasi' => $realisasi,
                    'selisih' => $selisih,
                    'persen' => $persen,
                ];
            }
        }

        return view(
            'cash_bank.pembayaran.cashFlowGabunganDropping',
            compact('data', 'bulanList')
        );
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'sub_kriteria' => 'required|exists:sub_kriteria,id_sub_kriteria',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer',
            'rows' => 'required|array',
        ]);

        try {
            $subKriteriaId = $request->sub_kriteria;
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            $rows = $request->rows;

            // Get items in sorted order (same order as displayed in view)
            $sortOrder = [
                'Gaji dan Tunjangan',
                'Cuti Tahunan',
                'Cuti Panjang',
                'THR',
                'Bonus',
                'PPh Pasal 21',
                'Iuran Dapenbun (Normal)',
                'Penghargaan Masa Kerja',
                'Iuran BPJS B. Perusahaan',
                'SHT (Cicilan)',
                'Lainnya',
            ];

            $items = ItemSubKriteria::where('id_sub_kriteria', $subKriteriaId)->get();
            $items = $items->sort(function ($a, $b) use ($sortOrder) {
                $posA = array_search($a->nama_item_sub_kriteria, $sortOrder);
                $posB = array_search($b->nama_item_sub_kriteria, $sortOrder);
                if ($posA !== false && $posB !== false)
                    return $posA - $posB;
                if ($posA !== false)
                    return -1;
                if ($posB !== false)
                    return 1;
                return strcmp($a->nama_item_sub_kriteria, $b->nama_item_sub_kriteria);
            })->values();

            $subKriteria = SubKriteria::find($subKriteriaId);
            $imported = 0;

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                if (!isset($items[$index]))
                    break;

                $item = $items[$index];
                $M1 = isset($row['M1']) ? (int) $row['M1'] : 0;
                $M2 = isset($row['M2']) ? (int) $row['M2'] : 0;
                $M3 = isset($row['M3']) ? (int) $row['M3'] : 0;
                $M4 = isset($row['M4']) ? (int) $row['M4'] : 0;

                // Skip jika semua 0
                if ($M1 === 0 && $M2 === 0 && $M3 === 0 && $M4 === 0)
                    continue;

                $dropping = Dropping::firstOrNew([
                    'id_item_sub_kriteria' => $item->id_item_sub_kriteria,
                    'id_sub_kriteria' => $subKriteriaId,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ]);

                if (!$dropping->exists) {
                    $dropping->id_kategori_kriteria = $subKriteria->id_kategori_kriteria;
                }

                $dropping->M1 = $M1;
                $dropping->M2 = $M2;
                $dropping->M3 = $M3;
                $dropping->M4 = $M4;
                $dropping->save();
                $imported++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import berhasil! ' . $imported . ' baris diimport.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

}

// namespace App\Http\Controllers;

// use App\Models\Dropping;
// use App\Models\dropping;
// use App\Models\SubKriteria;
// use Illuminate\Http\Request;
// use App\Models\ItemSubKriteria;
// use App\Models\KategoriKriteria;
// use Illuminate\Support\Facades\DB;

// class DroppingController extends Controller
// {
//     public function index()
//     {
//         $kategori = KategoriKriteria::where('tipe', 'keluar')->get();

//         return view('cash_bank.pembayaran.dropping', compact('kategori'));
//     }
//        public function getSub($id)
//     {
//         return SubKriteria::where('id_kategori_kriteria', $id)->get();
//     }

//     public function getTable(Request $request)
//     {
//         $subKriteriaId = $request->sub;
//         $tahun = $request->tahun;
//         $bulan = $request->bulan;

//         if(!$subKriteriaId || !$tahun || !$bulan){
//             return response()->json(['error' => 'Parameter tidak lengkap'], 400);
//         }

//         // Get items from sub kriteria
//         $items = ItemSubKriteria::where('id_sub_kriteria', $subKriteriaId)
//             ->orderBy('nama_item_sub_kriteria')
//             ->get();

//         if($items->isEmpty()){
//             return view('cash_bank.pembayaran.createDropping', [
//                 'items' => [],
//                 'data' => [],
//                 'bulan' => $bulan,
//                 'tahun' => $tahun,
//                 'subKriteriaId' => $subKriteriaId,
//                 'message' => 'Tidak ada item untuk sub kriteria ini'
//             ]);
//         }

//         // Get existing data for these items
//         $existingData = Dropping::where('id_sub_kriteria', $subKriteriaId)
//             ->where('tahun', $tahun)
//             ->where('bulan', $bulan)
//             ->whereIn('id_item_sub_kriteria', $items->pluck('id_item_sub_kriteria'))
//             ->get()
//             ->keyBy('id_item_sub_kriteria');

//         // Format data for view
//         $data = [];
//         foreach($items as $item){
//             $dropping = $existingData->get($item->id_item_sub_kriteria);
//             $data[$item->id_item_sub_kriteria] = [
//                 'M1' => $dropping ? $dropping->M1 : 0,
//                 'M2' => $dropping ? $dropping->M2 : 0,
//                 'M3' => $dropping ? $dropping->M3 : 0,
//                 'M4' => $dropping ? $dropping->M4 : 0,
//             ];
//         }

//         return view('cash_bank.pembayaran.createDropping', compact('items', 'data', 'bulan', 'tahun', 'subKriteriaId'));
//     }

//     public function saveData(Request $request)
//     {
//         $validated = $request->validate([
//             'item' => 'required|exists:item_sub_kriteria,id_item_sub_kriteria',
//             'sub_kriteria' => 'required|exists:sub_kriteria,id_sub_kriteria',
//             'kolom' => 'required|string|in:M1,M2,M3,M4',
//             'nilai' => 'nullable|numeric',
//             'tahun' => 'required|integer',
//             'bulan' => 'required|integer|between:1,12'
//         ]);

//         try {
//             $nilai = $validated['nilai'] ?? 0;
//             $kolom = $validated['kolom'];

//             // Get kategori from sub_kriteria
//             $subKriteria = SubKriteria::find($validated['sub_kriteria']);

//             // Find or create dropping record
//             $dropping = Dropping::firstOrNew([
//                 'id_item_sub_kriteria' => $validated['item'],
//                 'id_sub_kriteria' => $validated['sub_kriteria'],
//                 'tahun' => $validated['tahun'],
//                 'bulan' => $validated['bulan']
//             ]);

//             // Set kategori if creating new
//             if(!$dropping->exists){
//                 $dropping->id_kategori_kriteria = $subKriteria->id_kategori_kriteria;
//             }

//             // Update the specific column (M1, M2, M3, or M4)
//             $dropping->$kolom = $nilai;
//             $dropping->save();

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Data berhasil disimpan'
//             ]);

//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Gagal menyimpan data: ' . $e->getMessage()
//             ], 500);
//         }
//     }

//     public function deleteData(Request $request)
//     {
//         $validated = $request->validate([
//             'item' => 'required',
//             'kolom' => 'required',
//             'tahun' => 'required',
//             'bulan' => 'required'
//         ]);

//         try {
//             $dropping = Dropping::where('id_item_sub_kriteria', $validated['item'])
//                 ->where('tahun', $validated['tahun'])
//                 ->where('bulan', $validated['bulan'])
//                 ->first();

//             if($dropping){
//                 $kolom = $validated['kolom'];
//                 $dropping->$kolom = 0;
//                 $dropping->save();
//             }

//             return response()->json(['success' => true]);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     public function cashFlow(Request $request)
// {
//     $tahun = $request->tahun ?? date('Y');

//     \Log::info('CashFlow Request - Tahun: ' . $tahun);

//     // Get all data dropping for selected year
//     $data = Dropping::with(['kategori', 'subKriteria', 'itemSubKriteria'])
//         ->where('tahun', $tahun)
//         ->orderBy('id_kategori_kriteria')
//         ->orderBy('id_sub_kriteria')
//         ->orderBy('id_item_sub_kriteria')
//         ->get();

//     \Log::info('Total Data Found: ' . $data->count());

//     // Debug: return JSON dulu untuk cek data
//     // return response()->json([
//     //     'tahun' => $tahun,
//     //     'count' => $data->count(),
//     //     'data' => $data
//     // ]);

//     // Group data by kategori -> sub -> item
//     $result = [];
//     $totals = array_fill(1, 12, 0);
//     $grandTotal = 0;

//     foreach ($data as $row) {
//         $kategoriName = $row->kategori->nama_kriteria ?? 'Tidak ada kategori';
//         $subName = $row->subKriteria->nama_sub_kriteria ?? 'Tidak ada sub';
//         $itemName = $row->itemSubKriteria->nama_item_sub_kriteria ?? 'Tidak ada item';

//         if (!isset($result[$kategoriName])) {
//             $result[$kategoriName] = [
//                 'subs' => [],
//                 'totals' => array_fill(1, 12, 0)
//             ];
//         }

//         if (!isset($result[$kategoriName]['subs'][$subName])) {
//             $result[$kategoriName]['subs'][$subName] = [
//                 'items' => [],
//                 'totals' => array_fill(1, 12, 0)
//             ];
//         }

//         if (!isset($result[$kategoriName]['subs'][$subName]['items'][$itemName])) {
//             $result[$kategoriName]['subs'][$subName]['items'][$itemName] = array_fill(1, 12, 0);
//         }

//         $bulan = $row->bulan;
//         $totalBulan = $row->M1 + $row->M2 + $row->M3 + $row->M4;

//         $result[$kategoriName]['subs'][$subName]['items'][$itemName][$bulan] += $totalBulan;
//         $result[$kategoriName]['subs'][$subName]['totals'][$bulan] += $totalBulan;
//         $result[$kategoriName]['totals'][$bulan] += $totalBulan;
//         $totals[$bulan] += $totalBulan;
//         $grandTotal += $totalBulan;
//     }

//     \Log::info('Result Count: ' . count($result));

//     return view('cash_bank.pembayaran.cashFlowDropping', compact('result', 'tahun', 'totals', 'grandTotal'));
// }
// }