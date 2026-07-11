<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriKriteria;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use App\Models\Permintaan;
use Illuminate\Support\Facades\DB;


class PermintaanController extends Controller
{
    public function index()
    {
        $kategori = KategoriKriteria::where('tipe', 'keluar')->get();

        return view('cash_bank.pembayaran.permintaan', compact('kategori'));
    }
    public function getSub($id)
    {
        return SubKriteria::where('id_kategori_kriteria', $id)
            ->orderByRaw("CASE nama_sub_kriteria
                WHEN 'Karyawan Pimpinan' THEN 1
                WHEN 'Karyawan Pelaksana' THEN 2
                WHEN 'Gaji Honor' THEN 3
                WHEN 'Purchase Volume' THEN 4
                WHEN 'Biaya Usaha dan Lainnya' THEN 5
                WHEN 'Pajak' THEN 6
                WHEN 'Operasional Produksi' THEN 7
                ELSE 999
            END")
            ->orderBy('id_sub_kriteria')
            ->get();
    }

    /**
     * Item sub kriteria dengan urutan tampil yang baku (dipakai tabel & export).
     */
    private function sortedItems($subKriteriaId)
    {
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

        return $items->sort(function ($a, $b) use ($sortOrder) {
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
    }

    public function getTable(Request $request)
    {
        $subKriteriaId = $request->sub;
        $tahun = $request->tahun;
        $bulan = $request->bulan;

        if (!$subKriteriaId || !$tahun || !$bulan) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        $items = $this->sortedItems($subKriteriaId);

        if ($items->isEmpty()) {
            return view('cash_bank.pembayaran.createPermintaan', [
                'items' => [],
                'data' => [],
                'bulan' => $bulan,
                'tahun' => $tahun,
                'subKriteriaId' => $subKriteriaId,
                'message' => 'Tidak ada item untuk sub kriteria ini'
            ]);
        }

        // Get existing data for these items
        $existingData = Permintaan::where('id_sub_kriteria', $subKriteriaId)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->whereIn('id_item_sub_kriteria', $items->pluck('id_item_sub_kriteria'))
            ->get()
            ->keyBy('id_item_sub_kriteria');

        // Format data for view
        $data = [];
        foreach ($items as $item) {
            $permintaan = $existingData->get($item->id_item_sub_kriteria);
            $data[$item->id_item_sub_kriteria] = [
                'M1' => $permintaan ? $permintaan->M1 : 0,
                'M2' => $permintaan ? $permintaan->M2 : 0,
                'M3' => $permintaan ? $permintaan->M3 : 0,
                'M4' => $permintaan ? $permintaan->M4 : 0,
            ];
        }

        return view('cash_bank.pembayaran.createPermintaan', compact('items', 'data', 'bulan', 'tahun', 'subKriteriaId'));
    }

    /**
     * Susun data permintaan (item + nilai M1-M4) untuk kebutuhan export.
     */
    private function buildExportData($subKriteriaId, $tahun, $bulan)
    {
        $sub = SubKriteria::findOrFail($subKriteriaId);
        $kategori = KategoriKriteria::find($sub->id_kategori_kriteria);

        $items = $this->sortedItems($subKriteriaId);

        $existingData = Permintaan::where('id_sub_kriteria', $subKriteriaId)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->whereIn('id_item_sub_kriteria', $items->pluck('id_item_sub_kriteria'))
            ->get()
            ->keyBy('id_item_sub_kriteria');

        $data = [];
        foreach ($items as $item) {
            $p = $existingData->get($item->id_item_sub_kriteria);
            $data[$item->id_item_sub_kriteria] = [
                'M1' => $p ? $p->M1 : 0,
                'M2' => $p ? $p->M2 : 0,
                'M3' => $p ? $p->M3 : 0,
                'M4' => $p ? $p->M4 : 0,
            ];
        }

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return [
            'items' => $items,
            'data' => $data,
            'namaKategori' => $kategori->nama_kriteria ?? '-',
            'namaSub' => $sub->nama_sub_kriteria,
            'namaBulan' => $bulanNames[(int) $bulan] ?? '-',
            'tahun' => $tahun,
        ];
    }

    public function export_excel(Request $request)
    {
        $request->validate([
            'sub' => 'required|exists:sub_kriteria,id_sub_kriteria',
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
        ]);

        $payload = $this->buildExportData($request->sub, $request->tahun, $request->bulan);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ExportExcelPermintaan($payload),
            'permintaan-' . $request->tahun . '-' . $request->bulan . '.xlsx'
        );
    }

    public function export_pdf(Request $request)
    {
        $request->validate([
            'sub' => 'required|exists:sub_kriteria,id_sub_kriteria',
            'tahun' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
        ]);

        $payload = $this->buildExportData($request->sub, $request->tahun, $request->bulan);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('cash_bank.exportPDF.pdfPermintaan', $payload);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('permintaan-' . $request->tahun . '-' . $request->bulan . '.pdf');
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

            // Find or create permintaan record
            $permintaan = Permintaan::firstOrNew([
                'id_item_sub_kriteria' => $validated['item'],
                'id_sub_kriteria' => $validated['sub_kriteria'],
                'tahun' => $validated['tahun'],
                'bulan' => $validated['bulan']
            ]);

            // Set kategori if creating new
            if (!$permintaan->exists) {
                $permintaan->id_kategori_kriteria = $subKriteria->id_kategori_kriteria;
            }

            // Update the specific column (M1, M2, M3, or M4)
            $permintaan->$kolom = $nilai;
            $permintaan->save();

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

    public function deleteData(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required',
            'kolom' => 'required',
            'tahun' => 'required',
            'bulan' => 'required'
        ]);

        try {
            $permintaan = Permintaan::where('id_item_sub_kriteria', $validated['item'])
                ->where('tahun', $validated['tahun'])
                ->where('bulan', $validated['bulan'])
                ->first();

            if ($permintaan) {
                $kolom = $validated['kolom'];
                $permintaan->$kolom = 0;
                $permintaan->save();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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

                $permintaan = Permintaan::firstOrNew([
                    'id_item_sub_kriteria' => $itemId,
                    'id_sub_kriteria' => $subKriteriaId,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ]);

                if (!$permintaan->exists) {
                    $subKriteria = SubKriteria::find($subKriteriaId);
                    if ($subKriteria) {
                        $permintaan->id_kategori_kriteria = $subKriteria->id_kategori_kriteria;
                    }
                }

                $permintaan->$kolom = $nilai;
                $permintaan->save();
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

    public function cashFlow(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

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
        // QUERY PERMINTAAN DATA — sum M1+M2+M3+M4 per item per bulan
        // ======================================================
        $data = Permintaan::where('tahun', $tahun)->get();

        $txIndex = [];
        foreach ($data as $row) {
            $k = $row->id_kategori_kriteria;
            $s = $row->id_sub_kriteria;
            $i = $row->id_item_sub_kriteria;
            $b = $row->bulan;
            $val = ($row->M1 ?? 0) + ($row->M2 ?? 0) + ($row->M3 ?? 0) + ($row->M4 ?? 0);
            $txIndex[$k][$s][$i][$b] = ($txIndex[$k][$s][$i][$b] ?? 0) + $val;
        }

        // ======================================================
        // BUILD COMPLETE HIERARCHY
        // ======================================================
        $result = [];
        $totals = array_fill(1, 12, 0);
        $grandTotal = 0;

        foreach ($allKategori as $kat) {
            $katId = $kat->id_kategori_kriteria;
            $kategoriName = $kat->nama_kriteria;

            $result[$kategoriName] = [
                'subs'   => [],
                'totals' => array_fill(1, 12, 0),
            ];

            $subs = $allSub->where('id_kategori_kriteria', $katId);
            foreach ($subs as $sub) {
                $subId = $sub->id_sub_kriteria;
                $subName = trim($sub->nama_sub_kriteria);

                $result[$kategoriName]['subs'][$subName] = [
                    'items'  => [],
                    'totals' => array_fill(1, 12, 0),
                ];

                // Deduplicate items by name
                $items = $allItem->where('id_sub_kriteria', $subId);
                $uniqueItems = [];
                foreach ($items as $item) {
                    $itemName = trim($item->nama_item_sub_kriteria);
                    if (!isset($uniqueItems[$itemName])) {
                        $uniqueItems[$itemName] = [$item->id_item_sub_kriteria];
                    } else {
                        $uniqueItems[$itemName][] = $item->id_item_sub_kriteria;
                    }
                }

                foreach ($uniqueItems as $itemName => $itemIds) {
                    if (!isset($result[$kategoriName]['subs'][$subName]['items'][$itemName])) {
                        $result[$kategoriName]['subs'][$subName]['items'][$itemName] = array_fill(1, 12, 0);
                    }

                    for ($m = 1; $m <= 12; $m++) {
                        $val = 0;
                        foreach ($itemIds as $iid) {
                            $val += $txIndex[$katId][$subId][$iid][$m] ?? 0;
                        }
                        $result[$kategoriName]['subs'][$subName]['items'][$itemName][$m] += $val;
                        $result[$kategoriName]['subs'][$subName]['totals'][$m] += $val;
                        $result[$kategoriName]['totals'][$m] += $val;
                        $totals[$m] += $val;
                        $grandTotal += $val;
                    }
                }
            }
        }

        return view('cash_bank.pembayaran.cashFlowPermintaan', compact('result', 'tahun', 'totals', 'grandTotal'));
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

                $permintaan = Permintaan::firstOrNew([
                    'id_item_sub_kriteria' => $item->id_item_sub_kriteria,
                    'id_sub_kriteria' => $subKriteriaId,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ]);

                if (!$permintaan->exists) {
                    $permintaan->id_kategori_kriteria = $subKriteria->id_kategori_kriteria;
                }

                $permintaan->M1 = $M1;
                $permintaan->M2 = $M2;
                $permintaan->M3 = $M3;
                $permintaan->M4 = $M4;
                $permintaan->save();
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
