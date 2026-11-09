<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ExportExcelDashboardBank implements WithEvents, ShouldAutoSize
{
    protected $tanggal;
    protected $nama;
    protected $jabatan;
    protected $sumberDanaList;
    protected $totalSaldoBank;
    protected $labelFilter;
    protected $bankVAList;
    protected $totalSaldoVA;
    protected $totalSaldoSap;

    public function __construct(
        $tanggal,
        $nama,
        $jabatan,
        $sumberDanaList = null,
        $totalSaldoBank = null,
        $labelFilter = null,
        $bankVAList = null,
        $totalSaldoVA = null,
        $totalSaldoSap = null
    ) {
        $this->tanggal = $tanggal;
        $this->nama    = $nama;
        $this->jabatan = $jabatan;
        $this->labelFilter = $labelFilter;
        $this->bankVAList = $bankVAList;
        $this->totalSaldoVA = $totalSaldoVA;
        $this->totalSaldoSap = $totalSaldoSap;

        if ($sumberDanaList !== null) {
            $this->sumberDanaList = $sumberDanaList;
            $this->totalSaldoBank = $totalSaldoBank ?? $sumberDanaList->sum('saldo_va');
        } else {
            $this->sumberDanaList = DB::table('sumber_dana')
                ->select('sumber_dana.id_sumber_dana', 'sumber_dana.nama_sumber_dana')
                ->selectRaw('COALESCE((SELECT SUM(debet) FROM bank_masuk WHERE bank_masuk.id_sumber_dana = sumber_dana.id_sumber_dana), 0) as total_masuk')
                ->selectRaw('COALESCE((SELECT SUM(kredit) FROM bank_keluars WHERE bank_keluars.id_sumber_dana = sumber_dana.id_sumber_dana), 0) as total_keluar')
                ->orderBy('sumber_dana.id_sumber_dana')
                ->get()
                ->map(function ($sd) {
                    $sd->saldo_va = (float) $sd->total_masuk - (float) $sd->total_keluar;
                    return $sd;
                });

            $this->totalSaldoBank = $this->sumberDanaList->sum('saldo_va');
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ===================== WIDTHS =====================
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(52);
                $sheet->getColumnDimension('C')->setWidth(22);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(22);

                // ===================== ROW 1: HEADER UTAMA =====================
                $sheet->mergeCells('A1:B1');
                $sheet->setCellValue('A1', 'Saldo Kas & Bank');
                $tanggalHeader = (!empty($this->labelFilter) && $this->labelFilter !== 'Semua Waktu (Seluruh Data)')
                    ? $this->labelFilter
                    : 'Tanggal';
                $sheet->setCellValue('C1', $tanggalHeader);
                $sheet->setCellValue('D1', 'Nilai (Rp)');

                $headerStyle = [
                    'font'      => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDC3C7']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '888888']]],
                ];
                $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

                // ===================== ROW 2: SUB HEADER =====================
                $sheet->setCellValue('A2', 'No.');
                $sheet->setCellValue('B2', 'Uraian');
                $sheet->setCellValue('C2', 'No. Rek');
                $sheet->setCellValue('D2', 'Nilai (Rp)');

                $subHeaderStyle = [
                    'font'      => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ECF0F1']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBBBBB']]],
                ];
                $sheet->getStyle('A2:D2')->applyFromArray($subHeaderStyle);

                // ===================== ROW 3: A. SALDO =====================
                $sheet->mergeCells('B3:C3');
                $sheet->setCellValue('A3', 'A.');
                $sheet->setCellValue('B3', 'SALDO');

                $yellowStyle = [
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9E400']],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBBBBB']]],
                ];
                $sheet->getStyle('A3:D3')->applyFromArray($yellowStyle);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ===================== ROW 4: I. Saldo Kas =====================
                $sheet->setCellValue('A4', 'I.');
                $sheet->setCellValue('B4', 'Saldo Kas');
                $sheet->setCellValue('D4', '-');

                $normalStyle = [
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                ];
                $sheet->getStyle('A4:D4')->applyFromArray($normalStyle);
                $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // ===================== ROW 5: II. Saldo Bank =====================
                $sheet->mergeCells('B5:D5');
                $sheet->setCellValue('A5', 'II.');
                $sheet->setCellValue('B5', 'Saldo Bank :');
                $sheet->getStyle('A5:D5')->applyFromArray($normalStyle);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B5')->getFont()->setBold(true);

                // ===================== SUMBER DANA ROWS =====================
                $row = 6;
                foreach ($this->sumberDanaList as $sd) {
                    // Parse nama & nomor rek
                    $noRek     = '';
                    $namaBersih = $sd->nama_sumber_dana;
                    if (preg_match('/\*\s*([\d\-\/]+)\s*$/', $sd->nama_sumber_dana, $m)) {
                        $noRek     = trim($m[1]);
                        $namaBersih = trim(preg_replace('/\s*\*\s*[\d\-\/]+\s*$/', '', $sd->nama_sumber_dana));
                    }

                    // Ganti nama rekening untuk ekspor
                    if (stripos($namaBersih, 'OPEX') !== false) {
                        $namaBersih = 'PT. Bank Mandiri Operasional';
                    } elseif (stripos($namaBersih, 'Cadangan MTN') !== false || stripos($namaBersih, 'MTN') !== false) {
                        $namaBersih = 'PT. Bank Mandiri TBS';
                    }

                    // Format nilai
                    $saldo = $sd->saldo_va;
                    if ($saldo < 0) {
                        $nilaiStr = '(' . number_format(abs($saldo), 0, ',', '.') . ')';
                    } elseif ($saldo > 0) {
                        $nilaiStr = number_format($saldo, 0, ',', '.');
                    } else {
                        $nilaiStr = '-';
                    }

                    $sheet->setCellValue('A' . $row, '');
                    $sheet->setCellValue('B' . $row, '- ' . $namaBersih);
                    $sheet->setCellValue('C' . $row, $noRek);
                    $sheet->setCellValue('D' . $row, $nilaiStr);

                    $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($normalStyle);
                    $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    if ($saldo != 0) {
                        $sheet->getStyle('D' . $row)->getFont()->setBold(true);
                    }

                    $row++;
                }

                // ===================== TOTAL SALDO BANK =====================
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $sheet->setCellValue('A' . $row, 'Saldo Bank');

                $totalVal = $this->totalSaldoBank;
                if ($totalVal < 0) {
                    $totalStr = '(' . number_format(abs($totalVal), 0, ',', '.') . ')';
                } else {
                    $totalStr = number_format($totalVal, 0, ',', '.');
                }
                $sheet->setCellValue('D' . $row, $totalStr);

                $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($yellowStyle);
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('D' . $row)->getFont()->setBold(true);

                // ===================== FOOTER TANDA TANGAN =====================
                $row += 2; // 2 baris kosong

                // Baris tanggal di kolom C-D
                $sheet->mergeCells('C' . $row . ':D' . $row);
                $sheet->setCellValue('C' . $row, $this->tanggal);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row += 4; // 4 baris ruang tanda tangan

                // Nama penandatangan
                $sheet->mergeCells('C' . $row . ':D' . $row);
                $sheet->setCellValue('C' . $row, $this->nama);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row)->getFont()->setBold(true)->setUnderline(true);

                $row++;

                // Jabatan
                $sheet->mergeCells('C' . $row . ':D' . $row);
                $sheet->setCellValue('C' . $row, $this->jabatan);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ===================== TABEL SALDO VA & SALDO SAP =====================
                if (!empty($this->bankVAList) && count($this->bankVAList) > 0) {
                    $row += 3; // 3 baris kosong pemisah antar tabel

                    // Judul Tabel VA & SAP
                    $sheet->mergeCells('A' . $row . ':E' . $row);
                    $sheet->setCellValue('A' . $row, 'Saldo Bank Virtual Account (VA) & Saldo SAP');
                    $vaHeaderStyle = [
                        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A5276']],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0E3A56']]],
                    ];
                    $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($vaHeaderStyle);
                    $sheet->getRowDimension($row)->setRowHeight(24);

                    $row++;
                    // Sub Header Kolom
                    $sheet->setCellValue('A' . $row, 'No.');
                    $sheet->setCellValue('B' . $row, 'Nama Bank / VA');
                    $sheet->setCellValue('C' . $row, 'Saldo Akhir (Rp)');
                    $sheet->setCellValue('D' . $row, 'SALDO SAP');
                    $sheet->setCellValue('E' . $row, 'SELISIH');

                    $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($subHeaderStyle);
                    $sheet->getRowDimension($row)->setRowHeight(20);

                    // Baris Data VA & SAP
                    $vaIndex = 1;
                    foreach ($this->bankVAList as $va) {
                        $row++;
                        $saldoVaVal   = (float) ($va->saldo ?? 0);
                        $sapVal       = (float) ($va->sap_nilai ?? 0);
                        $selisihVal   = (float) ($va->selisih ?? ($saldoVaVal - $sapVal));

                        $saldoVaStr   = $saldoVaVal < 0 ? '(' . number_format(abs($saldoVaVal), 0, ',', '.') . ')' : ($saldoVaVal > 0 ? number_format($saldoVaVal, 0, ',', '.') : '-');
                        $sapStr       = $sapVal == 0 ? '-' : number_format($sapVal, 0, ',', '.');
                        $selisihStr   = $selisihVal < 0 ? '(' . number_format(abs($selisihVal), 0, ',', '.') . ')' : ($selisihVal > 0 ? number_format($selisihVal, 0, ',', '.') : '-');

                        $sheet->setCellValue('A' . $row, $vaIndex . '.');
                        $sheet->setCellValue('B' . $row, $va->nama_tujuan ?? '');
                        $sheet->setCellValue('C' . $row, $saldoVaStr);
                        $sheet->setCellValue('D' . $row, $sapStr);
                        $sheet->setCellValue('E' . $row, $selisihStr);

                        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($normalStyle);
                        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                        if ($saldoVaVal != 0) {
                            $sheet->getStyle('C' . $row)->getFont()->setBold(true);
                        }
                        if ($sapVal != 0) {
                            $sheet->getStyle('D' . $row)->getFont()->setBold(true);
                        }
                        if ($selisihVal != 0) {
                            $sheet->getStyle('E' . $row)->getFont()->setBold(true);
                        }

                        $vaIndex++;
                    }

                    // Baris Total VA
                    $row++;
                    $sheet->mergeCells('A' . $row . ':B' . $row);
                    $sheet->setCellValue('A' . $row, 'Total Saldo VA');

                    $totVaVal      = (float) ($this->totalSaldoVA ?? 0);
                    $totSapVal     = (float) ($this->totalSaldoSap ?? 0);
                    $totSelisihVal = $totVaVal - $totSapVal;

                    $totVaStr      = $totVaVal < 0 ? '(' . number_format(abs($totVaVal), 0, ',', '.') . ')' : number_format($totVaVal, 0, ',', '.');
                    $totSapStr     = $totSapVal == 0 ? '-' : number_format($totSapVal, 0, ',', '.');
                    $totSelisihStr = $totSelisihVal < 0 ? '(' . number_format(abs($totSelisihVal), 0, ',', '.') . ')' : ($totSelisihVal > 0 ? number_format($totSelisihVal, 0, ',', '.') : '-');

                    $sheet->setCellValue('C' . $row, $totVaStr);
                    $sheet->setCellValue('D' . $row, $totSapStr);
                    $sheet->setCellValue('E' . $row, $totSelisihStr);

                    $vaTotalStyle = [
                        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A5276']],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0E3A56']]],
                    ];
                    $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($vaTotalStyle);
                    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                // Row heights
                $sheet->getDefaultRowDimension()->setRowHeight(16);
            },
        ];
    }
}
