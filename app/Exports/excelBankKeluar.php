<?php

namespace App\Exports;

use App\Models\BankKeluar;
use App\Models\KategoriKriteria;
use App\Models\SumberDana;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class excelBankKeluar implements FromView, ShouldAutoSize, WithEvents
{
    // Filter export: tahun, bulan, kategori, sumber_dana (kosong = semua)
    private array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\View
    */
    public function view() : View
    {
        $query = BankKeluar::select(
            'id_bank_keluar',
            'agenda_tahun',
            'tanggal',
            'id_sumber_dana',
            'id_bank_tujuan',
            'id_kategori_kriteria',
            'id_sub_kriteria',
            'id_item_sub_kriteria',
            'penerima',
            'uraian',
            'id_jenis_pembayaran',
            'nilai_rupiah',
            'kredit',
            'keterangan'
        )
        ->with([
            'sumberDana:id_sumber_dana,nama_sumber_dana',
            'bankTujuan:id_bank_tujuan,nama_tujuan',
            'kategori:id_kategori_kriteria,nama_kriteria',
            'subKriteria:id_sub_kriteria,nama_sub_kriteria',
            'itemSubKriteria:id_item_sub_kriteria,nama_item_sub_kriteria',
            'jenisPembayaran:id_jenis_pembayaran,nama_jenis_pembayaran',
        ])
       ->orderBy('tanggal', 'asc')
       ->orderBy('id_bank_keluar');

        if (!empty($this->filters['tahun'])) {
            $query->whereYear('tanggal', $this->filters['tahun']);
        }
        if (!empty($this->filters['bulan'])) {
            $query->whereMonth('tanggal', $this->filters['bulan']);
        }
        if (!empty($this->filters['kategori'])) {
            $query->where('id_kategori_kriteria', $this->filters['kategori']);
        }
        if (!empty($this->filters['sumber_dana'])) {
            $query->where('id_sumber_dana', $this->filters['sumber_dana']);
        }

        $data = $query->get();

        return view('cash_bank.exportExcel.excelKeluar', [
            'data' => $data,
            'filterInfo' => $this->buildFilterInfo('Kriteria'),
        ]);
    }

    /**
     * Rangkai keterangan filter untuk kop laporan.
     */
    private function buildFilterInfo(string $labelKategori): string
    {
        $bulanNama = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $info = 'Periode: ' . (!empty($this->filters['bulan']) ? ($bulanNama[(int) $this->filters['bulan']] ?? '') . ' ' : '')
            . (!empty($this->filters['tahun']) ? $this->filters['tahun'] : 'Semua Tahun');
        if (!empty($this->filters['kategori'])) {
            $info .= " • {$labelKategori}: " . (optional(KategoriKriteria::find($this->filters['kategori']))->nama_kriteria ?? '-');
        }
        if (!empty($this->filters['sumber_dana'])) {
            $info .= ' • Sumber Dana: ' . (optional(SumberDana::find($this->filters['sumber_dana']))->nama_sumber_dana ?? '-');
        }
        return $info;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last = $sheet->getHighestRow();

                // Header (baris 4) beku saat scroll; kolom Kredit berformat ribuan
                $sheet->freezePane('A5');
                $sheet->getStyle("L5:L{$last}")->getNumberFormat()->setFormatCode('#,##0');

                // Uraian jangan autosize (bisa sangat panjang) — lebar tetap + wrap
                $sheet->getColumnDimension('J')->setAutoSize(false);
                $sheet->getColumnDimension('J')->setWidth(60);
                $sheet->getStyle("J5:J{$last}")->getAlignment()->setWrapText(true);
                $sheet->getRowDimension(1)->setRowHeight(22);
            },
        ];
    }
}
