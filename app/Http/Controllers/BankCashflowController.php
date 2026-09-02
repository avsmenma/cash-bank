<?php

namespace App\Http\Controllers;

use App\Models\BankTujuan;
use App\Models\Cashflow;
use App\Models\CashflowReference;
use App\Support\CashflowReportBuilder;
use Illuminate\Http\Request;

/**
 * Laporan Arus Kas versi admin: susunannya sama dengan halaman unit/kebun,
 * tetapi kolom Realisasi berisi angka GLOBAL (gabungan seluruh unit) dan di
 * sebelah kanannya memanjang satu grup kolom per unit/kebun.
 */
class BankCashflowController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = (int) date('Y');

        $years = Cashflow::whereNotNull('tahun')
            ->whereBetween('tahun', [2000, 2100])
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn ($t) => (int) $t)
            ->all();

        if (empty($years)) {
            $years = range($currentYear, $currentYear - 4);
        } elseif (!in_array($currentYear, $years, true)) {
            array_unshift($years, $currentYear);
        }

        $selectedYear = (int) $request->get('tahun', $years[0]);
        $selectedBulan = (string) $request->get('bulan', '');
        $showEmpty = $request->boolean('semua');
        $showTahunLalu = $request->boolean('tahun_lalu');
        $prevYear = $selectedYear - 1;

        // Daftar unit sengaja diambil TANPA menyertakan filter periode supaya
        // susunan kolom tetap sama saat bulan/tahun diganti — kolom unit tidak
        // muncul-hilang dan posisi geser horizontal pengguna tidak melompat.
        $unitIds = Cashflow::whereNotNull('id_bank_tujuan')
            ->distinct()
            ->pluck('id_bank_tujuan')
            ->all();

        $units = BankTujuan::whereIn('id_bank_tujuan', $unitIds)
            ->orderBy('nama_tujuan')
            ->get(['id_bank_tujuan', 'nama_tujuan']);

        // Query tahun berjalan dan tahun lalu jika opsi tampilkan kolom tahun lalu aktif
        $yearsToQuery = $showTahunLalu ? [$selectedYear, $prevYear] : [$selectedYear, $prevYear];

        // Satu query untuk semua: dua tahun x seluruh unit x profit center x seluruh reference key.
        $aggregates = Cashflow::query()
            ->whereIn('tahun', [$selectedYear, $prevYear])
            ->when(is_numeric($selectedBulan), fn ($q) => $q->where('bulan', (int) $selectedBulan))
            ->groupBy('reference_key_1', 'tahun', 'id_bank_tujuan', 'profit_center')
            ->selectRaw('reference_key_1, tahun, id_bank_tujuan, profit_center, SUM(amount) AS total')
            ->get();

        $series = [
            CashflowReportBuilder::SERI_GLOBAL => [],
            CashflowReportBuilder::SERI_REGIONAL_OFFICE => [],
        ];
        foreach ($units as $unit) {
            $series['u' . $unit->id_bank_tujuan] = [];
        }

        foreach ($aggregates as $row) {
            $key = (string) $row->reference_key_1;
            $bucket = ((int) $row->tahun === $selectedYear) ? 'current' : 'previous';
            $nilai = (float) $row->total;
            $unitKey = 'u' . $row->id_bank_tujuan;

            $series[CashflowReportBuilder::SERI_GLOBAL][$key] ??= ['current' => 0.0, 'previous' => 0.0];
            $series[CashflowReportBuilder::SERI_GLOBAL][$key][$bucket] += $nilai;

            if ((string) $row->profit_center === '5R00000001') {
                $series[CashflowReportBuilder::SERI_REGIONAL_OFFICE][$key] ??= ['current' => 0.0, 'previous' => 0.0];
                $series[CashflowReportBuilder::SERI_REGIONAL_OFFICE][$key][$bucket] += $nilai;
            }

            if ($row->id_bank_tujuan && isset($series[$unitKey])) {
                $series[$unitKey][$key] ??= ['current' => 0.0, 'previous' => 0.0];
                $series[$unitKey][$key][$bucket] += $nilai;
            }
        }

        $references = CashflowReference::all()->keyBy('reference_key');
        $reportRows = CashflowReportBuilder::buildMulti($series, $references, $showEmpty);
        $summary = CashflowReportBuilder::summarize($reportRows);

        $unitColumns = $units->map(function ($unit) {
            // Hapus nomor VA dari nama unit, contoh: "81029155501 - RAREN" -> "RAREN"
            $cleanName = trim(preg_replace('/^(?:VA\s*)?\d+\s*[-–—]\s*/i', '', $unit->nama_tujuan)) ?: $unit->nama_tujuan;
            return [
                'key' => 'u' . $unit->id_bank_tujuan,
                'nama' => $cleanName,
            ];
        })->values();

        return view('cash_bank.cashflowBank', compact(
            'years',
            'currentYear',
            'selectedYear',
            'prevYear',
            'selectedBulan',
            'showEmpty',
            'showTahunLalu',
            'reportRows',
            'summary',
            'unitColumns'
        ));
    }

    /**
     * Mengambil rincian transaksi pembentuk angka arus kas (drilldown).
     */
    public function detail(Request $request)
    {
        $scope = (array) $request->get('scope', []);
        $series = (string) $request->get('series', 'global');
        $tahun = (int) $request->get('tahun', date('Y'));
        $bulan = $request->get('bulan');

        $query = Cashflow::query()
            ->where('tahun', $tahun)
            ->where('amount', '!=', 0);

        if ($bulan !== null && $bulan !== '' && is_numeric($bulan)) {
            $query->where('bulan', (int) $bulan);
        }

        // Filter seri/unit
        if ($series === CashflowReportBuilder::SERI_REGIONAL_OFFICE) {
            $query->where('profit_center', '5R00000001');
        } elseif (str_starts_with($series, 'u')) {
            $unitId = (int) substr($series, 1);
            $query->where('id_bank_tujuan', $unitId);
        }

        // Filter scope reference keys / prefixes
        if (!empty($scope)) {
            $query->where(function ($q) use ($scope) {
                foreach ($scope as $s) {
                    if (strlen($s) >= 8) {
                        $q->orWhere('reference_key_1', $s);
                    } else {
                        $q->orWhere('reference_key_1', 'LIKE', $s . '%');
                    }
                }
            });
        }

        $items = $query->with('bankTujuan')
            ->orderBy('posting_date')
            ->orderBy('document_number')
            ->get();

        $totalAmount = (float) $items->sum('amount');

        return response()->json([
            'success' => true,
            'total' => $totalAmount,
            'count' => $items->count(),
            'data' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'posting_date' => $item->posting_date ? \Carbon\Carbon::parse($item->posting_date)->format('d-m-Y') : '-',
                    'document_number' => $item->document_number ?? '-',
                    'unit' => $item->bankTujuan?->nama_tujuan ?? $item->nama_profit_center ?? $item->profit_center ?? '-',
                    'profit_center' => $item->profit_center ?? '-',
                    'account' => $item->account ?? '-',
                    'gl_account_desc' => $item->gl_account_desc ?? '-',
                    'offsetting_account' => $item->offsetting_account ?? '-',
                    'name_of_offsetting_account' => $item->name_of_offsetting_account ?? '-',
                    'text' => $item->text ?? '-',
                    'uraian' => $item->uraian ?? '-',
                    'reference_key_1' => $item->reference_key_1 ?? '-',
                    'amount' => (float) $item->amount,
                ];
            }),
        ]);
    }
}
