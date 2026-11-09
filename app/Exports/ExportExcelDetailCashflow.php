<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportExcelDetailCashflow implements FromView, ShouldAutoSize, WithEvents
{
    protected $items;
    protected array $meta;

    public function __construct($items, array $meta = [])
    {
        $this->items = $items;
        $this->meta = $meta;
    }

    public function view(): View
    {
        return view('cash_bank.exportExcel.excelDetailCashflow', [
            'items' => $this->items,
            'meta'  => $this->meta,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Format kolom Nilai (Kolom J) sebagai format akuntansi ribuan
                $sheet->getStyle("J9:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0;(#,##0);"-"');

                // Lebar kolom khusus untuk teks/uraian agar rapi dan tidak terlalu melebar
                $sheet->getColumnDimension('I')->setAutoSize(false);
                $sheet->getColumnDimension('I')->setWidth(45);
                $sheet->getStyle("I9:I{$lastRow}")->getAlignment()->setWrapText(true);

                // Freeze pane di bawah header tabel (mulai baris data baris 9)
                $sheet->freezePane('A9');

                // Set tinggi baris header utama
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(8)->setRowHeight(26);
            },
        ];
    }
}
