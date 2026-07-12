<?php

namespace App\Exports;

use App\Models\BankKeluar;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class excelBankKeluar implements FromView
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

        return view('cash_bank.exportExcel.excelKeluar', compact('data'));
    }
}
