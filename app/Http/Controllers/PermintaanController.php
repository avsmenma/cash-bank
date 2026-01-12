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
        return SubKriteria::where('id_kategori_kriteria', $id)->get();
    }

    public function getTable(Request $request)
    {
        $subKriteriaId = $request->sub;
        $tahun = $request->tahun;
        $bulan = $request->bulan;

        if(!$subKriteriaId || !$tahun || !$bulan){
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        // Get items from sub kriteria
        $items = ItemSubKriteria::where('id_sub_kriteria', $subKriteriaId)
            ->orderBy('nama_item_sub_kriteria')
            ->get();

        if($items->isEmpty()){
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
        foreach($items as $item){
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
            if(!$permintaan->exists){
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

            if($permintaan){
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

    public function cashFlow(Request $request)
{
    $tahun = $request->tahun ?? date('Y');
    
    \Log::info('CashFlow Request - Tahun: ' . $tahun);

    // Get all data permintaan for selected year
    $data = Permintaan::with(['kategori', 'subKriteria', 'itemSubKriteria'])
        ->where('tahun', $tahun)
        ->orderBy('id_kategori_kriteria')
        ->orderBy('id_sub_kriteria')
        ->orderBy('id_item_sub_kriteria')
        ->get();

    \Log::info('Total Data Found: ' . $data->count());
    
    // Debug: return JSON dulu untuk cek data
    // return response()->json([
    //     'tahun' => $tahun,
    //     'count' => $data->count(),
    //     'data' => $data
    // ]);

    // Group data by kategori -> sub -> item
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

    return view('cash_bank.pembayaran.cashFlowPermintaan', compact('result', 'tahun', 'totals', 'grandTotal'));
}
}