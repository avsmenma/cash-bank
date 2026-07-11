@if(isset($grouped) && count($grouped) > 0)
<style>
    /* REALISASI PENERIMA — Tabulator ala spreadsheet (skema hijau lama) */
    #pn-real-table { border: 1px solid #d0d5dd; border-radius: 8px; font-size: 12px; font-family: 'Segoe UI', system-ui, sans-serif; }
    #pn-real-table .tabulator-header { background: #2d7a4a; border-bottom: 2px solid #1a5632; }
    #pn-real-table .tabulator-header .tabulator-col {
        background: #2d7a4a !important; color: #fff !important; font-weight: 700; font-size: 11px;
        border-color: #ffffff !important; border-right: 1px solid #ffffff !important;
    }
    #pn-real-table .tabulator-header .tabulator-col .tabulator-col-title { color: #fff; text-align: center; }
    #pn-real-table .tabulator-header .tabulator-col-resize-handle { width: 7px; cursor: col-resize; background: none; }
    #pn-real-table .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
        background: linear-gradient(to bottom, transparent 32%, rgba(255,255,255,.45) 32%, rgba(255,255,255,.45) 68%, transparent 68%)
            center / 2px 100% no-repeat;
    }
    #pn-real-table .tabulator-header .tabulator-col-resize-handle:hover { background: rgba(255,255,255,.3); }
    #pn-real-table .tabulator-cell { border-right: 1px solid #b3bfcc; border-color: #b3bfcc; }
    #pn-real-table .tabulator-row { border-bottom: 1px solid #b3bfcc; }

    #pn-real-table .tabulator-row.pn-r-monthtitle .tabulator-cell { background: #1a5632 !important; color: #fff !important; font-weight: 700; }
    #pn-real-table .tabulator-row.pn-r-kategori .tabulator-cell { background: #e8f5e9 !important; color: #1a5632 !important; font-weight: 700; }
    #pn-real-table .tabulator-row.pn-r-item .tabulator-cell { background: #fff; }
    #pn-real-table .tabulator-row.pn-r-item:hover .tabulator-cell { background: #e3f0e8; }
    #pn-real-table .tabulator-row.pn-r-subtotal .tabulator-cell { background: #fff3cd !important; color: #333 !important; font-weight: 700; border-top: 2px solid #c9a825; }
    #pn-real-table .tabulator-row.pn-r-monthtotal .tabulator-cell { background: #1a5632 !important; color: #fff !important; font-weight: 700; }
    #pn-real-table .pn-wrap { white-space: normal !important; word-break: break-word; }
    @media print { #pn-real-table .tabulator-tableholder { overflow: visible !important; max-height: none !important; } }
</style>

@php
    $judul = $judulPenerimaan ?? 'PENERIMAAN ATAS PENJUALAN CPO, KERNEL, SIR 20, TBS, KSO & LAINNYA';
    $nf = fn($v) => number_format($v ?? 0, 0, ',', '.');

    $csrf = csrf_field();
    $method = method_field('DELETE');

    $pnRows = [];
    foreach ($grouped as $bulanNum => $kategoriGroup) {
        $pnRows[] = [
            'type' => 'monthtitle',
            'sel' => '<input type="checkbox" class="select-all-month" data-month="' . $bulanNum . '">',
            'penerimaan' => $judul . ' — ' . ($bulanNames[$bulanNum] ?? '') . ' ' . $tahun,
        ];

        $monthTot = ['volume' => 0, 'nilai' => 0, 'ppn' => 0, 'potppn' => 0, 'inc' => 0];

        foreach ($kategoriGroup as $kategoriName => $rows) {
            $pnRows[] = ['type' => 'kategori', 'penerimaan' => strtoupper($kategoriName)];

            $catNo = 1;
            $sub = ['volume' => 0, 'nilai' => 0, 'ppn' => 0, 'potppn' => 0, 'inc' => 0];

            foreach ($rows as $row) {
                $inc = $row->nilai_inc_ppn ?? 0;
                $sub['volume'] += $row->volume; $sub['nilai'] += $row->nilai; $sub['ppn'] += $row->ppn;
                $sub['potppn'] += $row->potppn; $sub['inc'] += $inc;

                $aksi = '<button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editPenerima"'
                    . ' data-id="' . $row->id_penerima . '"'
                    . ' data-pembeli="' . e($row->pembeli) . '"'
                    . ' data-kontrak="' . e($row->kontrak) . '"'
                    . ' data-no_reg="' . e($row->no_reg) . '"'
                    . ' data-harga="' . $row->harga . '"'
                    . ' data-tanggal="' . $row->tanggal . '"'
                    . ' data-volume="' . $row->volume . '"'
                    . ' data-nilai="' . $row->nilai . '"'
                    . ' data-kategori="' . $row->id_kategori_kriteria . '"'
                    . ' data-ppn="' . $row->ppn . '"'
                    . ' data-potppn="' . $row->potppn . '"'
                    . ' data-nilai_inc_ppn="' . ($row->nilai_inc_ppn ?? 0) . '"'
                    . '><i class="fas fa-edit"></i></button> '
                    . '<form action="' . route('penerima.destroy', $row->id_penerima) . '" method="POST" style="display:inline;">'
                    . $csrf . $method
                    . '<button type="submit" class="btn btn-xs btn-danger" onclick="return confirm(\'Yakin ingin menghapus?\')">'
                    . '<i class="fas fa-trash"></i></button></form>';

                $pnRows[] = [
                    'type' => 'item',
                    'sel' => '<input type="checkbox" class="checkbox_ids" name="ids[]" data-bulan="' . $bulanNum . '" value="' . $row->id_penerima . '">',
                    'no' => $catNo++,
                    'penerimaan' => $kategoriName,
                    'kontrak' => $row->kontrak,
                    'pembeli' => $row->pembeli,
                    'tanggal' => ($row->tanggal && $row->tanggal !== '0000-00-00') ? \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d M Y') : '-',
                    'no_reg' => $row->no_reg,
                    'volume' => $nf($row->volume),
                    'harga' => $nf($row->harga),
                    'nilai' => $nf($row->nilai),
                    'ppn' => $nf($row->ppn),
                    'potppn' => $nf($row->potppn),
                    'inc' => $nf($inc),
                    'aksi' => $aksi,
                ];
            }

            foreach ($sub as $k2 => $v2) $monthTot[$k2] += $v2;

            $pnRows[] = [
                'type' => 'subtotal',
                'penerimaan' => 'JUMLAH ' . strtoupper($kategoriName),
                'volume' => $nf($sub['volume']), 'nilai' => $nf($sub['nilai']), 'ppn' => $nf($sub['ppn']),
                'potppn' => $nf($sub['potppn']), 'inc' => $nf($sub['inc']),
            ];
        }

        $pnRows[] = [
            'type' => 'monthtotal',
            'penerimaan' => 'TOTAL ' . ($bulanNames[$bulanNum] ?? ''),
            'volume' => $nf($monthTot['volume']), 'nilai' => $nf($monthTot['nilai']), 'ppn' => $nf($monthTot['ppn']),
            'potppn' => $nf($monthTot['potppn']), 'inc' => $nf($monthTot['inc']),
        ];
    }
@endphp

<div id="pn-real-table"></div>

<script>
(function () {
    var pnRows = @json($pnRows);

    function init() {
        var el = document.getElementById('pn-real-table');
        if (!el || !window.Tabulator) return;

        function col(title, field, opts) {
            return Object.assign({ title: title, field: field, headerHozAlign: 'center', minWidth: 90, widthGrow: 1 }, opts || {});
        }

        new Tabulator(el, {
            data: pnRows,
            columns: [
                col('', 'sel', { formatter: 'html', width: 40, hozAlign: 'center', frozen: true, resizable: false }),
                col('No', 'no', { width: 46, hozAlign: 'center', frozen: true }),
                col('Penerimaan', 'penerimaan', { minWidth: 170, widthGrow: 2, frozen: true }),
                col('Kontrak', 'kontrak', { minWidth: 180, widthGrow: 2, cssClass: 'pn-wrap', variableHeight: true }),
                col('Pembeli', 'pembeli', { minWidth: 90 }),
                col('Tgl. Diterima', 'tanggal', { hozAlign: 'center', minWidth: 105 }),
                col('No. Rekg. Penerima', 'no_reg', { minWidth: 130 }),
                col('Volume (Kg)', 'volume', { hozAlign: 'right' }),
                col('Harga (Rp)', 'harga', { hozAlign: 'right' }),
                col('Nilai', 'nilai', { hozAlign: 'right', minWidth: 110 }),
                col('PPN', 'ppn', { hozAlign: 'right' }),
                col('Pot PPh', 'potppn', { hozAlign: 'right' }),
                col('Nilai Inc. PPN', 'inc', { hozAlign: 'right', minWidth: 115 }),
                col('Aksi', 'aksi', { formatter: 'html', width: 92, hozAlign: 'center', resizable: false })
            ],
            layout: 'fitColumns',
            height: '65vh',
            renderVertical: 'basic',    // semua baris di DOM: checkbox hapus-terpilih tetap terbaca
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            columnDefaults: { headerSort: false },
            rowFormatter: function (row) {
                var t = row.getData().type;
                var e2 = row.getElement();
                ['monthtitle', 'kategori', 'item', 'subtotal', 'monthtotal'].forEach(function (k) {
                    e2.classList.toggle('pn-r-' + k, t === k);
                });
            },
            placeholder: 'Tidak ada data'
        });

        // Centang semua baris dalam satu bulan
        if (!el._pnBound) {
            el._pnBound = true;
            el.addEventListener('change', function (e) {
                if (!e.target.classList || !e.target.classList.contains('select-all-month')) return;
                var m = e.target.getAttribute('data-month');
                el.querySelectorAll('.checkbox_ids[data-bulan="' + m + '"]').forEach(function (c) {
                    c.checked = e.target.checked;
                });
            });
        }
    }

    if (window.Tabulator) { init(); } else { document.addEventListener('DOMContentLoaded', init); }
})();
</script>
@else
    <div class="penerima-empty-state" style="text-align:center; padding:40px 20px; color:#888;">
        <i class="fas fa-inbox" style="font-size:40px; margin-bottom:10px; display:block;"></i>
        <p>Tidak ada data penerima untuk periode ini.</p>
    </div>
@endif
