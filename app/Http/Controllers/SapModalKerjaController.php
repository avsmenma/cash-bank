<?php

namespace App\Http\Controllers;

use App\Models\BankTujuan;
use App\Models\Cashflow;
use App\Support\SapModalKerjaMapper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SapModalKerjaController extends Controller
{
    /**
     * Halaman Shell Utama Modal Kerja (SAP)
     */
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
            $years = range($currentYear, $currentYear - 3);
        } elseif (!in_array($currentYear, $years, true)) {
            array_unshift($years, $currentYear);
        }

        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $tahun = (int) $request->get('tahun', $years[0] ?? $currentYear);
        $bulanDari = (int) $request->get('bulan_dari', 1);
        $bulanSampai = (int) $request->get('bulan_sampai', (int) date('n'));
        $unitSelected = (string) $request->get('unit', 'all');

        $units = BankTujuan::whereNotNull('nama_tujuan')
            ->orderBy('nama_tujuan')
            ->get(['id_bank_tujuan', 'nama_tujuan']);

        return view('cash_bank.modalKerjaSap', compact(
            'years', 'bulanList', 'tahun', 'bulanDari', 'bulanSampai', 'unitSelected', 'units'
        ));
    }

    /**
     * Mengambil Data Tabel Mingguan Modal Kerja (SAP) via AJAX
     */
    public function data(Request $request)
    {
        $bulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $tahun = (int) ($request->tahun ?? date('Y'));
        $bulanDari = max(1, min(12, (int) ($request->bulan_dari ?? 1)));
        $bulanSampai = max($bulanDari, min(12, (int) ($request->bulan_sampai ?? date('n'))));
        $unit = (string) ($request->unit ?? 'all');

        // Daftar bulan yang terpilih pada filter
        $bulanAktif = [];
        for ($b = $bulanDari; $b <= $bulanSampai; $b++) {
            $bulanAktif[$b] = $bulanMap[$b];
        }

        // Query pengeluaran kas SAP
        $query = Cashflow::with('reference')
            ->where('tahun', $tahun)
            ->whereBetween('bulan', [$bulanDari, $bulanSampai])
            ->where(function ($q) {
                // Pengeluaran kas bertanda negatif atau berada di grup akun pengeluaran/investasi
                $q->where('amount', '<', 0)
                  ->orWhere('reference_key_1', 'like', 'A02%')
                  ->orWhere('reference_key_1', 'like', 'B02%');
            });

        // Filter unit
        if ($unit === 'ro') {
            $query->where('profit_center', '5R00000001');
        } elseif (is_numeric($unit) && (int) $unit > 0) {
            $query->where('id_bank_tujuan', (int) $unit);
        }

        $records = $query->get();

        // Inisialisasi struktur template minggu
        $initWeek = fn () => ['w1' => 0.0, 'w2' => 0.0, 'w3' => 0.0, 'w4' => 0.0, 'total' => 0.0];

        $matrix = [];     // [kategori][sub][item][bulan] = ['w1'=>0, ... , 'total'=>0]
        $subtotals = [];  // [kategori][sub][bulan] = ['w1'=>0, ... , 'total'=>0]
        $catTotals = [];  // [kategori][bulan] = ['w1'=>0, ... , 'total'=>0]
        $grandTotal = []; // [bulan] = ['w1'=>0, ... , 'total'=>0]

        // Default grand total untuk semua bulan aktif
        foreach ($bulanAktif as $bNum => $_bName) {
            $grandTotal[$bNum] = $initWeek();
        }

        foreach ($records as $row) {
            $b = (int) $row->bulan;
            if ($b < $bulanDari || $b > $bulanSampai) {
                continue;
            }

            // Tentukan W1..W4 dari tanggal posting
            $day = !empty($row->posting_date) ? (int) Carbon::parse($row->posting_date)->day : 1;
            if ($day <= 7) {
                $week = 'w1';
            } elseif ($day <= 14) {
                $week = 'w2';
            } elseif ($day <= 21) {
                $week = 'w3';
            } else {
                $week = 'w4';
            }

            $refKey = $row->reference_key_1;
            $uraian = $row->reference->uraian ?? ($row->uraian ?? ($row->text ?? ''));
            $parentKey = $row->reference->parent_key ?? null;
            $parentName = $row->reference->parent_name ?? null;

            [$kategori, $subKriteria, $itemKriteria] = SapModalKerjaMapper::map(
                $refKey, $uraian, $parentKey, $parentName
            );

            $nilai = abs((float) $row->amount) / 1000; // konversi ke satuan ribuan Rupiah

            // Inisialisasi sel item jika belum ada
            if (!isset($matrix[$kategori][$subKriteria][$itemKriteria])) {
                foreach ($bulanAktif as $bNum => $_bName) {
                    $matrix[$kategori][$subKriteria][$itemKriteria][$bNum] = $initWeek();
                }
            }

            // Inisialisasi subtotal sub-kriteria jika belum ada
            if (!isset($subtotals[$kategori][$subKriteria])) {
                foreach ($bulanAktif as $bNum => $_bName) {
                    $subtotals[$kategori][$subKriteria][$bNum] = $initWeek();
                }
            }

            // Inisialisasi subtotal kategori jika belum ada
            if (!isset($catTotals[$kategori])) {
                foreach ($bulanAktif as $bNum => $_bName) {
                    $catTotals[$kategori][$bNum] = $initWeek();
                }
            }

            // Akumulasi nilai
            $matrix[$kategori][$subKriteria][$itemKriteria][$b][$week] += $nilai;
            $matrix[$kategori][$subKriteria][$itemKriteria][$b]['total'] += $nilai;

            $subtotals[$kategori][$subKriteria][$b][$week] += $nilai;
            $subtotals[$kategori][$subKriteria][$b]['total'] += $nilai;

            $catTotals[$kategori][$b][$week] += $nilai;
            $catTotals[$kategori][$b]['total'] += $nilai;

            $grandTotal[$b][$week] += $nilai;
            $grandTotal[$b]['total'] += $nilai;
        }

        // Urutkan kategori agar susunan baku: Gaji -> Eksploitasi -> Investasi -> Lainnya
        $orderedCategories = [
            SapModalKerjaMapper::KAT_GAJI,
            SapModalKerjaMapper::KAT_OPS,
            SapModalKerjaMapper::KAT_INV,
            SapModalKerjaMapper::KAT_LAIN,
        ];

        $sortedMatrix = [];
        foreach ($orderedCategories as $kat) {
            if (isset($matrix[$kat])) {
                $sortedMatrix[$kat] = $matrix[$kat];
            }
        }
        foreach ($matrix as $kat => $val) {
            if (!isset($sortedMatrix[$kat])) {
                $sortedMatrix[$kat] = $val;
            }
        }

        return view('cash_bank.modalKerjaSapTable', compact(
            'sortedMatrix', 'subtotals', 'catTotals', 'grandTotal',
            'bulanAktif', 'tahun', 'unit'
        ));
    }
}
