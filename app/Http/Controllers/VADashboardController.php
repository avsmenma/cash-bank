<?php

namespace App\Http\Controllers;

use App\Models\BankTujuan;
use App\Models\BankMasuk;
use App\Models\BankKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VADashboardController extends Controller
{
    /**
     * Show the Detail Transaksi VA page for the logged-in VA user.
     */
    public function index()
    {
        $user = Auth::user();
        $id = $user->id_bank_tujuan;

        $va = BankTujuan::findOrFail($id);
        $transactions = $this->buildLedger($id);

        // Daftar tahun untuk dropdown filter
        $years = $transactions->pluck('tanggal')
            ->filter()
            ->map(fn ($t) => substr((string) $t, 0, 4))
            ->unique()
            ->sortDesc()
            ->values();

        return view('cash_bank.va.dashboard', compact('va', 'transactions', 'years'));
    }

    /**
     * Show the Cash Flow page for the logged-in VA user.
     */
    public function cashflow(Request $request)
    {
        $user = Auth::user();
        $id = $user->id_bank_tujuan;

        $va = BankTujuan::findOrFail($id);

        $currentYear = (int) date('Y');
        $selectedYear = (int) ($request->get('tahun', $currentYear));
        $selectedBulan = $request->get('bulan', '');
        $prevYear = $selectedYear - 1;

        $years = \App\Models\Cashflow::where('id_bank_tujuan', $id)
            ->whereNotNull('tahun')
            ->pluck('tahun')
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        if (empty($years)) {
            $years = range($currentYear + 1, 2023);
        }

        // Query transaksi cashflow khusus untuk unit/kebun ini
        $query = \App\Models\Cashflow::where('id_bank_tujuan', $id);

        if (!empty($selectedBulan) && is_numeric($selectedBulan)) {
            $query->where('bulan', (int) $selectedBulan);
        }

        // Ambil transaksi untuk tahun pilihan dan tahun lalu
        $transactions = (clone $query)->whereIn('tahun', [$selectedYear, $prevYear])->get();

        // Dictionary standarisasi referensi
        $references = \App\Models\CashflowReference::all()->keyBy('reference_key');

        // Kelompokkan per Reference Key (reference_key_1)
        $groupedData = [];

        foreach ($transactions as $tx) {
            $key = $tx->reference_key_1 ?: ($tx->reference_key_3 ? $tx->reference_key_3 . '000' : 'A0209029');

            if (!isset($groupedData[$key])) {
                $refObj = $references->get($key);
                $uraian = $refObj ? $refObj->uraian : ($tx->uraian ?: $tx->name_of_offsetting_account ?: $key);

                $groupedData[$key] = [
                    'reference_key' => $key,
                    'uraian' => $uraian,
                    'realisasi_tahun' => 0.0,
                    'realisasi_tahun_lalu' => 0.0,
                ];
            }

            $amt = (float) $tx->amount;
            if ($tx->tahun == $selectedYear) {
                $groupedData[$key]['realisasi_tahun'] += $amt;
            } elseif ($tx->tahun == $prevYear) {
                $groupedData[$key]['realisasi_tahun_lalu'] += $amt;
            }
        }

        // Urutkan berdasarkan Reference Key secara abjad/standar
        ksort($groupedData);
        $cashflowData = array_values($groupedData);

        return view('cash_bank.va.cashflow', compact(
            'va',
            'years',
            'currentYear',
            'selectedYear',
            'selectedBulan',
            'cashflowData'
        ));
    }

    /**
     * Gabungkan Bank Masuk & Bank Keluar milik satu VA menjadi buku pembantu
     * (urut tanggal, saldo berjalan kumulatif).
     */
    private function buildLedger(int $id)
    {
        // Get Bank Masuk transactions for this VA
        $masuk = BankMasuk::where('id_bank_tujuan', $id)
            ->select('tanggal', 'penerima', 'uraian', 'debet', 'kredit', 'created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'created_at' => $item->created_at,
                    'penerima' => $item->penerima,
                    'uraian' => $item->uraian,
                    'debet' => (float) $item->debet,
                    'kredit' => 0,
                    'tipe' => 'masuk',
                ];
            });

        // Get Bank Keluar transactions for this VA
        $keluar = BankKeluar::where('id_bank_tujuan', $id)
            ->select('tanggal', 'penerima', 'uraian', 'debet', 'kredit', 'created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'created_at' => $item->created_at,
                    'penerima' => $item->penerima,
                    'uraian' => $item->uraian,
                    'debet' => 0,
                    'kredit' => (float) $item->kredit,
                    'tipe' => 'keluar',
                ];
            });

        // Merge and sort by date ascending
        $transactions = $masuk->toBase()->merge($keluar->toBase())
            ->sortBy(['tanggal', 'created_at'])
            ->values();

        // Compute running total (saldo akhir)
        $saldo = 0;
        return $transactions->map(function ($item) use (&$saldo) {
            $saldo = $saldo + $item['debet'] - $item['kredit'];
            $item['saldo'] = $saldo;
            return $item;
        });
    }

    /**
     * Export buku pembantu VA ke Excel (styling navy, ikut filter bulan/tahun).
     */
    public function exportExcel(Request $request)
    {
        $id = Auth::user()->id_bank_tujuan;
        return $this->streamLedgerExcel(BankTujuan::findOrFail($id), $this->buildLedger($id), $request);
    }

    /**
     * Export detail satu VA berdasarkan id (dipakai admin dari Daftar VA → Detail).
     */
    public function exportExcelById(Request $request, $id)
    {
        $this->authorizeVaAccess($id);
        return $this->streamLedgerExcel(BankTujuan::findOrFail($id), $this->buildLedger($id), $request);
    }

    /**
     * Cegah IDOR: user role "va" hanya boleh mengakses VA miliknya sendiri.
     * Role internal (admin/programmer/dll) yang memakai panel Daftar VA tetap
     * bebas mengakses semua VA seperti sebelumnya.
     */
    private function authorizeVaAccess($id): void
    {
        $user = Auth::user();
        if ($user && $user->role === 'va' && (string) $user->id_bank_tujuan !== (string) $id) {
            abort(403, 'Anda tidak berhak mengakses data VA ini.');
        }
    }

    /**
     * Bangun & kirim file Excel buku pembantu VA. Dipakai VA sendiri (exportExcel)
     * maupun admin (exportExcelById).
     */
    private function streamLedgerExcel($va, $transactions, Request $request)
    {
        // Filter bulan/tahun — saldo tetap kumulatif sejak awal (dihitung sebelum filter)
        $bulan = $request->input('bulan'); // '01'..'12'
        $tahun = $request->input('tahun'); // '2026'
        if ($bulan || $tahun) {
            $transactions = $transactions->filter(function ($t) use ($bulan, $tahun) {
                $tgl = (string) $t['tanggal'];
                if ($tahun && substr($tgl, 0, 4) !== $tahun) return false;
                if ($bulan && substr($tgl, 5, 2) !== $bulan) return false;
                return true;
            })->values();
        }

        $bulanNama = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        if ($bulan && $tahun) {
            $periode = 'Periode: ' . ($bulanNama[$bulan] ?? $bulan) . ' ' . $tahun;
        } elseif ($bulan) {
            $periode = 'Periode: ' . ($bulanNama[$bulan] ?? $bulan) . ' (semua tahun)';
        } elseif ($tahun) {
            $periode = 'Periode: Tahun ' . $tahun;
        } else {
            $periode = 'Periode: Semua';
        }

        $navy = '1E3A5F';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail VA');

        // ── Kop laporan ──
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'DETAIL TRANSAKSI VIRTUAL ACCOUNT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB($navy);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', $va->nama_tujuan . ' — Buku Pembantu (Gabungan Bank Masuk & Bank Keluar)');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', $periode . ' • ' . number_format($transactions->count(), 0, ',', '.')
            . ' transaksi • diexport ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('6B7280');

        // ── Header tabel (baris 5) ──
        $headers = ['No', 'Tanggal', 'Bank Tujuan', 'Penerima/Dari', 'Uraian', 'Debet (Rp)', 'Kredit (Rp)', 'Saldo Akhir (Rp)'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:H5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $navy]],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(22);

        // ── Data (mulai baris 6) ──
        $row = 6;
        foreach ($transactions as $i => $trx) {
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $trx['tanggal']
                ? \Carbon\Carbon::parse($trx['tanggal'])->format('d/m/Y') : '-');
            $sheet->setCellValue("C{$row}", $va->nama_tujuan);
            $sheet->setCellValue("D{$row}", $trx['penerima'] ?: '-');
            $sheet->setCellValue("E{$row}", $trx['uraian'] ?: '-');
            $sheet->setCellValue("F{$row}", $trx['debet']);
            $sheet->setCellValue("G{$row}", $trx['kredit']);
            $sheet->setCellValue("H{$row}", $trx['saldo']);

            if ($i % 2 === 1) {
                $sheet->getStyle("A{$row}:H{$row}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F5F8FB');
            }
            $row++;
        }
        $lastData = $row - 1;

        if ($transactions->count()) {
            // Border + perataan blok data
            $sheet->getStyle("A6:H{$lastData}")->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()->setRGB('B3BFCC');
            $sheet->getStyle("A6:B{$lastData}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E6:E{$lastData}")->getAlignment()->setWrapText(true);

            // Angka: 0 tampil sebagai '-' agar mudah dibaca
            $sheet->getStyle("F6:H{$lastData}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;"-"');
            $sheet->getStyle("F6:F{$lastData}")->getFont()->getColor()->setRGB('1E7E34'); // debet hijau
            $sheet->getStyle("G6:G{$lastData}")->getFont()->getColor()->setRGB('C82333'); // kredit merah
            $sheet->getStyle("H6:H{$lastData}")->getFont()->setBold(true);

            // ── Baris TOTAL ──
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", 'TOTAL');
            $sheet->setCellValue("F{$row}", $transactions->sum('debet'));
            $sheet->setCellValue("G{$row}", $transactions->sum('kredit'));
            $sheet->setCellValue("H{$row}", $transactions->last()['saldo']);
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $navy]],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;"-"');
        } else {
            $sheet->mergeCells("A6:H6");
            $sheet->setCellValue('A6', 'Tidak ada transaksi pada periode ini.');
            $sheet->getStyle('A6')->getFont()->setItalic(true);
        }

        // Lebar kolom & freeze header
        foreach (['A' => 5, 'B' => 13, 'C' => 26, 'D' => 24, 'E' => 60, 'F' => 15, 'G' => 15, 'H' => 17] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('A6');

        $slug = preg_replace('/[^A-Za-z0-9]+/', '_', $va->nama_tujuan);
        $suffix = ($tahun ? "_{$tahun}" : '') . ($bulan ? "_{$bulan}" : '');
        $filename = "Detail_VA_{$slug}{$suffix}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
