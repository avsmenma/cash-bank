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
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn ($t) => (int) $t)
            ->all();

        if (empty($years)) {
            $years = range($currentYear, $currentYear - 4);
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

        // Satu query untuk semua: dua tahun x seluruh unit x seluruh reference key.
        $aggregates = Cashflow::query()
            ->whereIn('tahun', [$selectedYear, $prevYear])
            ->when(is_numeric($selectedBulan), fn ($q) => $q->where('bulan', (int) $selectedBulan))
            ->groupBy('reference_key_1', 'tahun', 'id_bank_tujuan')
            ->selectRaw('reference_key_1, tahun, id_bank_tujuan, SUM(amount) AS total')
            ->get();

        $series = [CashflowReportBuilder::SERI_GLOBAL => []];
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

            if (isset($series[$unitKey])) {
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
}
