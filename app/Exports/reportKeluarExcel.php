<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class reportKeluarExcel implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $request;
    protected $rows;      // cache hasil query + filter
    protected $showDebet;
    protected $showSaldoAkhir;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /* ------------------------------------------------------------------ */
    /*  QUERY + FILTER (sama persis dengan controller reportKeluar)         */
    /* ------------------------------------------------------------------ */
    private function getData()
    {
        if ($this->rows !== null) return $this->rows;

        $tahun           = $this->request->tahun;
        $bulan           = $this->request->bulan;
        $tanggalDipilih  = $this->request->tanggal;
        $bankTujuanId    = $this->request->bank_tujuan;
        $sumberDanaIds   = $this->request->sumber_dana;
        $kategoriIds     = $this->request->kategori;
        $idJenisPembayaran = $this->request->id_jenis_pembayaran;
        $rekapanVA       = $this->request->rekapanVA;
        $tglDari         = $this->request->tgl_dari;
        $tglSampai       = $this->request->tgl_sampai;

        /* hitung filter aktif (sama dengan controller) */
        $activeFilters = [];
        if ($bankTujuanId)                                            $activeFilters[] = 'bank_tujuan';
        if ($sumberDanaIds && count((array)$sumberDanaIds) > 0)       $activeFilters[] = 'sumber_dana';
        if ($kategoriIds   && count((array)$kategoriIds)   > 0)       $activeFilters[] = 'kategori';
        if ($idJenisPembayaran)                                        $activeFilters[] = 'jenis_pembayaran';
        if ($rekapanVA)                                                $activeFilters[] = 'rekapan';

        $countActive = count($activeFilters);

        /* logika kolom yang ditampilkan */
        $this->showDebet      = ($countActive <= 1);
        $this->showSaldoAkhir = ($countActive <= 1);

        // override jika filter tunggal adalah jenis_pembayaran
        if ($countActive === 1 && $idJenisPembayaran) {
            $this->showDebet      = false;
            $this->showSaldoAkhir = false;
        }

        /* closure filter tanggal */
        $filterTanggal = function ($q) use ($tahun, $bulan, $tanggalDipilih, $tglDari, $tglSampai) {
            if ($tglDari && $tglSampai) {
                $q->whereBetween(DB::raw('DATE(tanggal)'), [$tglDari, $tglSampai]);
            } elseif ($tglDari) {
                $q->where(DB::raw('DATE(tanggal)'), '>=', $tglDari);
            } elseif ($tglSampai) {
                $q->where(DB::raw('DATE(tanggal)'), '<=', $tglSampai);
            } elseif (!empty($tanggalDipilih) && is_array($tanggalDipilih)) {
                $q->whereIn(DB::raw('DATE(tanggal)'), $tanggalDipilih);
            } elseif ($tahun && $bulan) {
                $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
            } elseif ($tahun) {
                $q->whereYear('tanggal', $tahun);
            }
        };

        /* closure filter saldo awal (tanpa kategori) */
        $applyFilterSaldoAwal = function ($q, $table = null) use ($filterTanggal, $bankTujuanId, $sumberDanaIds) {
            $prefix = $table ? $table . '.' : '';
            $filterTanggal($q);
            if ($bankTujuanId)  $q->where($prefix . 'id_bank_tujuan', $bankTujuanId);
            if ($sumberDanaIds && is_array($sumberDanaIds) && count($sumberDanaIds) > 0)
                $q->whereIn($prefix . 'id_sumber_dana', $sumberDanaIds);
        };

        /* closure filter lengkap */
        $applyFilter = function ($q, $table = null) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $kategoriIds, $idJenisPembayaran) {
            $prefix = $table ? $table . '.' : '';
            $filterTanggal($q);
            if ($bankTujuanId)  $q->where($prefix . 'id_bank_tujuan', $bankTujuanId);
            if ($sumberDanaIds && is_array($sumberDanaIds) && count($sumberDanaIds) > 0)
                $q->whereIn($prefix . 'id_sumber_dana', $sumberDanaIds);
            if ($kategoriIds && is_array($kategoriIds) && count($kategoriIds) > 0)
                $q->whereIn($prefix . 'id_kategori_kriteria', $kategoriIds);
            if ($idJenisPembayaran)
                $q->where($prefix . 'id_jenis_pembayaran', $idJenisPembayaran);
        };

        /* query utama */
        if ($this->showDebet) {
            $bankMasuk = DB::table('bank_masuk')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_masuk.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_masuk.id_bank_tujuan')
                ->select(
                    'bank_masuk.agenda_tahun',
                    'bank_masuk.no_sap',
                    'bank_tujuan.nama_tujuan',
                    'bank_masuk.penerima',
                    'bank_masuk.tanggal',
                    'bank_masuk.uraian',
                    'bank_masuk.debet',
                    DB::raw('0 as kredit'),
                    DB::raw("'MASUK' as jenis"),
                    DB::raw('bank_masuk.id_bank_masuk as urut_id')
                )
                ->where(function ($q) use ($applyFilterSaldoAwal) {
                    $applyFilterSaldoAwal($q, 'bank_masuk');
                });

            $bankKeluar = DB::table('bank_keluars')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_keluars.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_keluars.id_bank_tujuan')
                ->select(
                    'bank_keluars.agenda_tahun',
                    'bank_keluars.no_sap',
                    'bank_tujuan.nama_tujuan',
                    'bank_keluars.penerima',
                    'bank_keluars.tanggal',
                    'bank_keluars.uraian',
                    DB::raw('0 as debet'),
                    'bank_keluars.kredit',
                    DB::raw("'KELUAR' as jenis"),
                    DB::raw('bank_keluars.id_bank_keluar as urut_id')
                )
                ->where(function ($q) use ($applyFilterSaldoAwal) {
                    $applyFilterSaldoAwal($q, 'bank_keluars');
                });

            $data = $bankMasuk->unionAll($bankKeluar)
                ->orderBy('tanggal')
                ->orderBy('urut_id')
                ->get();
        } else {
            $data = DB::table('bank_keluars')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_keluars.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_keluars.id_bank_tujuan')
                ->select(
                    'bank_keluars.agenda_tahun',
                    'bank_keluars.no_sap',
                    'bank_tujuan.nama_tujuan',
                    'bank_keluars.penerima',
                    'bank_keluars.tanggal',
                    'bank_keluars.uraian',
                    DB::raw('0 as debet'),
                    'bank_keluars.kredit',
                    DB::raw("'KELUAR' as jenis"),
                    DB::raw('bank_keluars.id_bank_keluar as urut_id')
                )
                ->where(function ($q) use ($applyFilter) {
                    $applyFilter($q, 'bank_keluars');
                })
                ->orderBy('tanggal')
                ->orderBy('urut_id')
                ->get();
        }

        /* hitung saldo berjalan */
        $saldo = 0;
        foreach ($data as $row) {
            $saldo += ($row->debet ?? 0) - ($row->kredit ?? 0);
            $row->saldo_akhir = $this->showSaldoAkhir ? $saldo : null;
        }

        $this->rows = $data;
        return $this->rows;
    }

    /* ------------------------------------------------------------------ */
    /*  FROM COLLECTION                                                      */
    /* ------------------------------------------------------------------ */
    public function collection()
    {
        $data = $this->getData();
        $no = 1;
        $rows = [];

        foreach ($data as $row) {
            $isDebet = ($row->jenis ?? '') === 'MASUK';

            $r = [
                $no++,
                $row->agenda_tahun  ?? '-',
                $row->tanggal       ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-',
                $row->no_sap        ?? '-',
                $row->nama_tujuan   ?? '-',
                $row->penerima      ?? '-',
                $row->uraian        ?? '-',
            ];

            if ($this->showDebet) {
                $r[] = $row->debet  > 0 ? (float)$row->debet  : null;
                $r[] = $row->kredit > 0 ? (float)$row->kredit : null;
            } else {
                $r[] = $row->kredit > 0 ? (float)$row->kredit : null;
            }

            if ($this->showSaldoAkhir) {
                $saldoAkhir = $row->saldo_akhir ?? 0;
                $r[] = (float)$saldoAkhir;
            }

            $rows[] = $r;
        }

        /* baris TOTAL di footer */
        $totalDebet   = $data->sum('debet');
        $totalKredit  = $data->sum('kredit');
        $finalSaldo   = $data->last()->saldo_akhir ?? 0;

        $footer = ['', '', '', '', '', '', 'TOTAL'];
        if ($this->showDebet) {
            $footer[] = (float)$totalDebet;
            $footer[] = (float)$totalKredit;
        } else {
            $footer[] = (float)$totalKredit;
        }
        if ($this->showSaldoAkhir) {
            $footer[] = (float)$finalSaldo;
        }

        $rows[] = $footer;

        return collect($rows);
    }

    /* ------------------------------------------------------------------ */
    /*  HEADINGS                                                             */
    /* ------------------------------------------------------------------ */
    public function headings(): array
    {
        // Pastikan getData() dipanggil dulu agar showDebet & showSaldoAkhir terisi
        $this->getData();

        $h = ['No', 'No Agenda', 'Tanggal', 'No Bukti', 'Bank Tujuan', 'Penerima / Dari', 'Uraian'];

        if ($this->showDebet) {
            $h[] = 'Debet';
            $h[] = 'Kredit';
        } else {
            $h[] = 'Kredit';
        }

        if ($this->showSaldoAkhir) {
            $h[] = 'Saldo Akhir';
        }

        return $h;
    }

    /* ------------------------------------------------------------------ */
    /*  COLUMN WIDTHS                                                        */
    /* ------------------------------------------------------------------ */
    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 14,
            'D' => 18,
            'E' => 22,
            'F' => 28,
            'G' => 50,
            'H' => 18,
            'I' => 18,
            'J' => 18,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  STYLES (header row #1)                                               */
    /* ------------------------------------------------------------------ */
    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        // Header baris 1 – navy (#0d3b6e), teks putih, bold, center
        $lastCol = $this->showDebet
            ? ($this->showSaldoAkhir ? 'J' : 'I')
            : ($this->showSaldoAkhir ? 'I' : 'H');

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0D3B6E'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        return [];
    }

    /* ------------------------------------------------------------------ */
    /*  EVENTS – styling per baris (debet/kredit warna), footer, format      */
    /* ------------------------------------------------------------------ */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $data    = $this->getData();
                $lastCol = $this->showDebet
                    ? ($this->showSaldoAkhir ? 'J' : 'I')
                    : ($this->showSaldoAkhir ? 'I' : 'H');

                // Kolom angka (mulai H)
                $colDebet   = 'H';
                $colKredit  = $this->showDebet ? 'I' : 'H';
                $colSaldo   = $this->showSaldoAkhir ? ($this->showDebet ? 'J' : 'I') : null;

                $numFormat = '#,##0';
                $totalDataRows = count($data);
                $footerRow = $totalDataRows + 2; // +1 heading +1 footer

                /* ---- style tiap baris data ---- */
                foreach ($data as $i => $row) {
                    $excelRow = $i + 2; // baris 2 dst (baris 1 = heading)
                    $isDebet  = ($row->jenis ?? '') === 'MASUK';

                    // Warna baris: putih bersih (tidak pakai background berbeda agar bersih)
                    // Warna teks debet (hijau) dan kredit (merah) sesuai website
                    if ($this->showDebet) {
                        if ($isDebet) {
                            // debet row: kolom debet hijau
                            $sheet->getStyle("{$colDebet}{$excelRow}")->getFont()->getColor()->setARGB('FF1A7A3D');
                        } else {
                            // kredit row: kolom kredit merah
                            $sheet->getStyle("{$colKredit}{$excelRow}")->getFont()->getColor()->setARGB('FFC0392B');
                        }
                        // format number
                        $sheet->getStyle("{$colDebet}{$excelRow}")->getNumberFormat()->setFormatCode($numFormat);
                        $sheet->getStyle("{$colKredit}{$excelRow}")->getNumberFormat()->setFormatCode($numFormat);
                    } else {
                        $sheet->getStyle("{$colKredit}{$excelRow}")->getFont()->getColor()->setARGB('FFC0392B');
                        $sheet->getStyle("{$colKredit}{$excelRow}")->getNumberFormat()->setFormatCode($numFormat);
                    }

                    // Saldo akhir: navy
                    if ($colSaldo) {
                        $saldoAkhir = $row->saldo_akhir ?? 0;
                        $sheet->getStyle("{$colSaldo}{$excelRow}")->getFont()->getColor()->setARGB(
                            $saldoAkhir < 0 ? 'FFC0392B' : 'FF0D3B6E'
                        );
                        $sheet->getStyle("{$colSaldo}{$excelRow}")->getFont()->setBold(true);
                        $sheet->getStyle("{$colSaldo}{$excelRow}")->getNumberFormat()->setFormatCode($numFormat);
                    }

                    // border tipis
                    $sheet->getStyle("A{$excelRow}:{$lastCol}{$excelRow}")->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setARGB('FFD0DCE8');
                }

                /* ---- style baris TOTAL (footer) ---- */
                $sheet->getStyle("A{$footerRow}:{$lastCol}{$footerRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'size'  => 11,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF1B4F72'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);

                // Warna angka di footer
                if ($this->showDebet) {
                    $sheet->getStyle("{$colDebet}{$footerRow}")->getFont()->getColor()->setARGB('FFA9DFBF');
                    $sheet->getStyle("{$colKredit}{$footerRow}")->getFont()->getColor()->setARGB('FFF1948A');
                    $sheet->getStyle("{$colDebet}{$footerRow}")->getNumberFormat()->setFormatCode($numFormat);
                    $sheet->getStyle("{$colKredit}{$footerRow}")->getNumberFormat()->setFormatCode($numFormat);
                } else {
                    $sheet->getStyle("{$colKredit}{$footerRow}")->getFont()->getColor()->setARGB('FFF1948A');
                    $sheet->getStyle("{$colKredit}{$footerRow}")->getNumberFormat()->setFormatCode($numFormat);
                }
                if ($colSaldo) {
                    $sheet->getStyle("{$colSaldo}{$footerRow}")->getFont()->getColor()->setARGB('FFFAD7A0');
                    $sheet->getStyle("{$colSaldo}{$footerRow}")->getNumberFormat()->setFormatCode($numFormat);
                }

                // Label "TOTAL" rata kiri pada kolom G
                $sheet->getStyle("G{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Freeze pane (header tetap)
                $sheet->freezePane('A2');

                // Tinggi header
                $sheet->getRowDimension(1)->setRowHeight(22);
            },
        ];
    }
}
