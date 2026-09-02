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

        $bulanList = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        // Helper untuk parse input tanggal format d-m-Y, d/m/Y, atau Y-m-d
        $parseDateInput = function ($val) {
            if (empty($val)) return null;
            $val = trim($val);
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $val, $m)) {
                return $m[3] . '-' . $m[2] . '-' . $m[1];
            }
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $val, $m)) {
                return $m[3] . '-' . $m[2] . '-' . $m[1];
            }
            try {
                return \Carbon\Carbon::parse($val)->toDateString();
            } catch (\Exception $e) {
                return $val;
            }
        };

        $tglDari = $parseDateInput($request->get('tgl_dari'));
        $tglSampai = $parseDateInput($request->get('tgl_sampai'));

        $defaultYear = !empty($tglSampai) ? (int) \Carbon\Carbon::parse($tglSampai)->year : $years[0];
        $selectedYear = (int) $request->get('tahun', $defaultYear);
        $selectedBulan = (string) $request->get('bulan', '');
        $showEmpty = $request->boolean('semua');
        $showTahunLalu = $request->boolean('tahun_lalu');
        $prevYear = $selectedYear - 1;

        // Format label periode untuk tampilan kop laporan & banner filter
        $formatTanggalIndo = function ($dateStr) use ($bulanList) {
            if (empty($dateStr)) return '';
            try {
                $c = \Carbon\Carbon::parse($dateStr);
                $d = $c->format('d');
                $m = $bulanList[str_pad($c->format('n'), 2, '0', STR_PAD_LEFT)] ?? $c->format('m');
                $y = $c->format('Y');
                return $d . ' ' . $m . ' ' . $y;
            } catch (\Exception $e) {
                return $dateStr;
            }
        };

        if (!empty($tglDari) && !empty($tglSampai)) {
            $labelPeriode = $formatTanggalIndo($tglDari) . ' s/d ' . $formatTanggalIndo($tglSampai);
        } elseif (!empty($tglSampai)) {
            $labelPeriode = 'Sampai dengan ' . $formatTanggalIndo($tglSampai);
        } elseif (!empty($tglDari)) {
            $labelPeriode = 'Mulai ' . $formatTanggalIndo($tglDari);
        } elseif ($selectedBulan !== '') {
            $labelPeriode = ($bulanList[str_pad($selectedBulan, 2, '0', STR_PAD_LEFT)] ?? $selectedBulan) . ' ' . $selectedYear;
        } else {
            $labelPeriode = 'Tahun ' . $selectedYear;
        }

        // Daftar unit diambil tanpa filter periode agar struktur kolom tabel konsisten
        $unitIds = Cashflow::whereNotNull('id_bank_tujuan')
            ->distinct()
            ->pluck('id_bank_tujuan')
            ->all();

        $units = BankTujuan::whereIn('id_bank_tujuan', $unitIds)
            ->orderBy('nama_tujuan')
            ->get(['id_bank_tujuan', 'nama_tujuan']);

        // Query agregasi arus kas dengan filter tanggal / periode
        $query = Cashflow::query();

        if (!empty($tglDari) && !empty($tglSampai)) {
            $prevTglDari = \Carbon\Carbon::parse($tglDari)->subYear()->toDateString();
            $prevTglSampai = \Carbon\Carbon::parse($tglSampai)->subYear()->toDateString();

            $query->where(function ($q) use ($tglDari, $tglSampai, $prevTglDari, $prevTglSampai) {
                $q->whereBetween('posting_date', [$tglDari, $tglSampai])
                  ->orWhereBetween('posting_date', [$prevTglDari, $prevTglSampai]);
            });
        } elseif (!empty($tglSampai)) {
            $prevTglSampai = \Carbon\Carbon::parse($tglSampai)->subYear()->toDateString();

            $query->where(function ($q) use ($selectedYear, $prevYear, $tglSampai, $prevTglSampai) {
                $q->where(function ($q1) use ($selectedYear, $tglSampai) {
                    $q1->where('tahun', $selectedYear)->where('posting_date', '<=', $tglSampai);
                })->orWhere(function ($q2) use ($prevYear, $prevTglSampai) {
                    $q2->where('tahun', $prevYear)->where('posting_date', '<=', $prevTglSampai);
                });
            });
        } elseif (!empty($tglDari)) {
            $prevTglDari = \Carbon\Carbon::parse($tglDari)->subYear()->toDateString();

            $query->where(function ($q) use ($selectedYear, $prevYear, $tglDari, $prevTglDari) {
                $q->where(function ($q1) use ($selectedYear, $tglDari) {
                    $q1->where('tahun', $selectedYear)->where('posting_date', '>=', $tglDari);
                })->orWhere(function ($q2) use ($prevYear, $prevTglDari) {
                    $q2->where('tahun', $prevYear)->where('posting_date', '>=', $prevTglDari);
                });
            });
        } else {
            $query->whereIn('tahun', [$selectedYear, $prevYear])
                  ->when(is_numeric($selectedBulan), fn ($q) => $q->where('bulan', (int) $selectedBulan));
        }

        $aggregates = $query
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
            'bulanList',
            'currentYear',
            'selectedYear',
            'prevYear',
            'selectedBulan',
            'tglDari',
            'tglSampai',
            'labelPeriode',
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

        $parseDateInput = function ($val) {
            if (empty($val)) return null;
            $val = trim($val);
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $val, $m)) {
                return $m[3] . '-' . $m[2] . '-' . $m[1];
            }
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $val, $m)) {
                return $m[3] . '-' . $m[2] . '-' . $m[1];
            }
            try {
                return \Carbon\Carbon::parse($val)->toDateString();
            } catch (\Exception $e) {
                return $val;
            }
        };

        $tglDari = $parseDateInput($request->get('tgl_dari'));
        $tglSampai = $parseDateInput($request->get('tgl_sampai'));
        $baseYear = (int) ($request->get('selected_year') ?: (!empty($tglSampai) ? \Carbon\Carbon::parse($tglSampai)->year : $tahun));

        $query = Cashflow::query()
            ->where('amount', '!=', 0);

        if (!empty($tglDari) && !empty($tglSampai)) {
            $diff = $baseYear - $tahun;
            $tglDariEff = ($diff != 0) ? \Carbon\Carbon::parse($tglDari)->subYears($diff)->toDateString() : $tglDari;
            $tglSampaiEff = ($diff != 0) ? \Carbon\Carbon::parse($tglSampai)->subYears($diff)->toDateString() : $tglSampai;
            $query->whereBetween('posting_date', [$tglDariEff, $tglSampaiEff]);
        } elseif (!empty($tglSampai)) {
            $diff = $baseYear - $tahun;
            $tglSampaiEff = ($diff != 0) ? \Carbon\Carbon::parse($tglSampai)->subYears($diff)->toDateString() : $tglSampai;
            $query->where('tahun', $tahun)->where('posting_date', '<=', $tglSampaiEff);
        } elseif (!empty($tglDari)) {
            $diff = $baseYear - $tahun;
            $tglDariEff = ($diff != 0) ? \Carbon\Carbon::parse($tglDari)->subYears($diff)->toDateString() : $tglDari;
            $query->where('tahun', $tahun)->where('posting_date', '>=', $tglDariEff);
        } else {
            $query->where('tahun', $tahun);
            if ($bulan !== null && $bulan !== '' && is_numeric($bulan)) {
                $query->where('bulan', (int) $bulan);
            }
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
