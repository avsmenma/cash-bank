@php
    $bulanShort = [
        1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR', 5 => 'MEI', 6 => 'JUN',
        7 => 'JUL', 8 => 'AGS', 9 => 'SEP', 10 => 'OKT', 11 => 'NOV', 12 => 'DES'
    ];
@endphp

<style>
    /* ============================================================
       MODAL KERJA (SAP) — Tabulator Stylesheet
       ============================================================ */
    #mk-sap-table {
        border: 1px solid #8da0b3;
        border-radius: 8px;
        font-size: 11px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    #mk-sap-table .tabulator-header {
        background: #1B365D;
        border-bottom: 2px solid #0f2540;
    }
    #mk-sap-table .tabulator-header .tabulator-col,
    #mk-sap-table .tabulator-header .tabulator-col-group {
        background: #1B365D;
        color: #ffffff;
        font-weight: 600;
        font-size: 10.5px;
        border-right: 1px solid rgba(255, 255, 255, 0.25) !important;
    }
    #mk-sap-table .tabulator-header .tabulator-col-group-cols > .tabulator-col:last-child {
        border-right: 1px solid rgba(255, 255, 255, 0.4) !important;
    }
    #mk-sap-table .tabulator-header .tabulator-col .tabulator-col-title {
        text-align: center;
        white-space: normal;
        font-weight: 700;
    }

    /* Resize Handle */
    #mk-sap-table .tabulator-header .tabulator-col-resize-handle {
        width: 6px;
        cursor: col-resize;
    }
    #mk-sap-table .tabulator-header .tabulator-col-resize-handle:hover {
        background: rgba(255, 255, 255, 0.35);
    }

    /* Warna Header Grup Bulan */
    #mk-sap-table .tabulator-col-group.mk-sap-h-month {
        background: #20406b !important;
    }
    #mk-sap-table .tabulator-col.mk-sap-h-total,
    #mk-sap-table .tabulator-col-group.mk-sap-h-total {
        background: #0e294d !important;
        color: #fef08a !important;
    }

    /* Garis & Border Sel */
    #mk-sap-table .tabulator-cell {
        border-right: 1px solid #cbd5e1;
        border-color: #cbd5e1;
        padding: 6px 8px;
    }
    #mk-sap-table .tabulator-row {
        border-bottom: 1px solid #cbd5e1;
    }

    /* Tipe Baris */
    #mk-sap-table .tabulator-row.mk-r-kategori .tabulator-cell {
        background: #1F3864 !important;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 11.5px;
        letter-spacing: 0.2px;
    }
    #mk-sap-table .tabulator-row.mk-r-sub .tabulator-cell {
        background: #E8EEF5 !important;
        font-weight: 700;
        color: #1e293b;
    }
    #mk-sap-table .tabulator-row.mk-r-item .tabulator-cell {
        background: #ffffff;
        color: #334155;
    }
    #mk-sap-table .tabulator-row.mk-r-item:hover .tabulator-cell {
        background: #f1f5f9;
    }
    #mk-sap-table .tabulator-row.mk-r-subtotal .tabulator-cell {
        background: #D1E3F8 !important;
        font-weight: 700;
        color: #0f172a;
    }
    #mk-sap-table .tabulator-row.mk-r-kattotal .tabulator-cell {
        background: #2A4B7C !important;
        color: #ffffff !important;
        font-weight: 700;
    }
    #mk-sap-table .tabulator-row.mk-r-grandtotal .tabulator-cell {
        background: #0F2540 !important;
        color: #FFD700 !important;
        font-weight: 800;
        font-size: 12px;
        border-top: 2px solid #FFD700 !important;
    }

    .mk-c-bold { font-weight: 700; }
    .mk-c-total-month { background-color: rgba(30, 64, 175, 0.06); font-weight: 700; }
    .mk-c-grand-total { background-color: rgba(234, 179, 8, 0.12); font-weight: 700; }

    #mk-sap-table .mk-c-kode,
    #mk-sap-table .cf-kode {
        font-family: inherit;
        font-size: inherit;
        font-variant-numeric: tabular-nums;
    }
    #mk-sap-table .tabulator-row.mk-r-item .tabulator-cell.mk-c-kode,
    #mk-sap-table .tabulator-row.mk-r-sub .tabulator-cell.mk-c-kode {
        color: #000000 !important;
    }

    @media print {
        #mk-sap-table .tabulator-tableholder {
            overflow: visible !important;
            max-height: none !important;
        }
    }
</style>

@if(empty($bulanAktif))
    <div class="alert alert-info m-3">
        <i class="fas fa-info-circle mr-1"></i> Tidak ada data untuk periode bulan yang dipilih.
    </div>
@else

@php
    $tableRows = [];
    $pushRow = function ($type, $no, $kode, $uraian, $cells = []) use (&$tableRows) {
        $tableRows[] = array_merge(['type' => $type, 'no' => $no, 'kode' => $kode, 'uraian' => $uraian], $cells);
    };

    $formatCells = function ($bulanDataMap) use ($bulanAktif) {
        $cells = [];
        $periodeTotal = 0.0;
        foreach ($bulanAktif as $bNo => $_bName) {
            $dataBulan = $bulanDataMap[$bNo] ?? ['w1' => 0.0, 'w2' => 0.0, 'w3' => 0.0, 'w4' => 0.0, 'total' => 0.0];
            $cells["m{$bNo}_w1"] = (float)($dataBulan['w1'] ?? 0);
            $cells["m{$bNo}_w2"] = (float)($dataBulan['w2'] ?? 0);
            $cells["m{$bNo}_w3"] = (float)($dataBulan['w3'] ?? 0);
            $cells["m{$bNo}_w4"] = (float)($dataBulan['w4'] ?? 0);
            $cells["m{$bNo}_total"] = (float)($dataBulan['total'] ?? 0);
            $periodeTotal += (float)($dataBulan['total'] ?? 0);
        }
        $cells['periode_total'] = $periodeTotal;
        return $cells;
    };

    $catNumber = 1;
    foreach ($sortedMatrix as $kategori => $subGroups) {
        // Baris Kategori Header
        $catData = $catTotals[$kategori] ?? [];
        $catCode = match ($kategori) {
            \App\Support\SapModalKerjaMapper::KAT_GAJI, \App\Support\SapModalKerjaMapper::KAT_OPS => 'A02',
            \App\Support\SapModalKerjaMapper::KAT_INV => 'B02',
            \App\Support\SapModalKerjaMapper::KAT_FIN => 'A0206',
            default => '-'
        };
        $pushRow('kategori', $catNumber++, $catCode, $kategori, $formatCells($catData));

        foreach ($subGroups as $sub => $items) {
            // Baris Sub-Kategori
            $subCode = \App\Support\SapModalKerjaMapper::getSubParentCode($sub) ?? '-';
            $pushRow('sub', '', $subCode, $sub);

            // Baris Item-Item Detail
            foreach ($items as $item => $itemData) {
                $itemLabel = $item === '' ? $sub : ('- ' . $item);
                $foundCodes = isset($itemCodes[$kategori][$sub][$item]) ? array_keys($itemCodes[$kategori][$sub][$item]) : [];
                sort($foundCodes);
                $itemCode = !empty($foundCodes)
                    ? implode(', ', $foundCodes)
                    : (\App\Support\SapModalKerjaMapper::getStandardCode($kategori, $sub, $item) ?? '-');

                $pushRow('item', '', $itemCode, $itemLabel, $formatCells($itemData));
            }

            // Sub Total Sub-Kategori
            $subData = $subtotals[$kategori][$sub] ?? [];
            $pushRow('subtotal', '', '-', 'Sub Total ' . $sub, $formatCells($subData));
        }

        // Total Kategori
        $pushRow('kattotal', '', '-', 'Total ' . $kategori, $formatCells($catData));
    }

    // GRAND TOTAL
    $pushRow('grandtotal', '', '-', 'TOTAL REALISASI PENGELUARAN KAS (SAP)', $formatCells($grandTotal));

    // ================================================================
    // DEFINISI KOLOM TABULATOR
    // ================================================================
    $columns = [
        [
            'title' => 'No.',
            'field' => 'no',
            'frozen' => true,
            'width' => 46,
            'hozAlign' => 'center',
            'headerHozAlign' => 'center'
        ],
        [
            'title' => 'Kode SAP',
            'field' => 'kode',
            'frozen' => true,
            'width' => 105,
            'hozAlign' => 'center',
            'headerHozAlign' => 'center',
            'cssClass' => 'mk-c-kode'
        ],
        [
            'title' => 'Akun Pengeluaran Kas (SAP) - Modal Kerja',
            'field' => 'uraian',
            'frozen' => true,
            'width' => 330,
            'minWidth' => 240,
            'hozAlign' => 'left',
            'headerHozAlign' => 'left'
        ]
    ];

    foreach ($bulanAktif as $bNo => $bNama) {
        $monthSubCols = [
            [
                'title' => $bNama . '-W1 (1-7)',
                'field' => "m{$bNo}_w1",
                'width' => 105,
                'hozAlign' => 'right',
                'headerHozAlign' => 'center'
            ],
            [
                'title' => $bNama . '-W2 (8-14)',
                'field' => "m{$bNo}_w2",
                'width' => 105,
                'hozAlign' => 'right',
                'headerHozAlign' => 'center'
            ],
            [
                'title' => $bNama . '-W3 (15-21)',
                'field' => "m{$bNo}_w3",
                'width' => 105,
                'hozAlign' => 'right',
                'headerHozAlign' => 'center'
            ],
            [
                'title' => $bNama . '-W4 (22-31)',
                'field' => "m{$bNo}_w4",
                'width' => 105,
                'hozAlign' => 'right',
                'headerHozAlign' => 'center'
            ],
            [
                'title' => 'Total ' . $bNama,
                'field' => "m{$bNo}_total",
                'width' => 125,
                'hozAlign' => 'right',
                'headerHozAlign' => 'center',
                'cssClass' => 'mk-c-total-month'
            ],
        ];

        $columns[] = [
            'title' => strtoupper($bNama) . ' ' . $tahun . ' (Rp Ribuan)',
            'cssClass' => 'mk-sap-h-month',
            'columns' => $monthSubCols
        ];
    }

    // Jika lebih dari 1 bulan, tampilkan kolom akumulasi periode
    if (count($bulanAktif) > 1) {
        $columns[] = [
            'title' => 'TOTAL PERIODE',
            'cssClass' => 'mk-sap-h-total',
            'columns' => [
                [
                    'title' => 'Grand Total (Rp \'000)',
                    'field' => 'periode_total',
                    'width' => 140,
                    'hozAlign' => 'right',
                    'headerHozAlign' => 'center',
                    'cssClass' => 'mk-c-grand-total'
                ]
            ]
        ];
    }
@endphp

<div id="mk-sap-table"></div>

<script>
(function () {
    var rawRows = @json($tableRows);
    var rawCols = @json($columns);

    // Formatter angka ribuan Rupiah
    function formatNumberSap(cell) {
        var val = cell.getValue();
        if (val === null || val === undefined || val === '' || val === 0) {
            return '-';
        }
        var num = parseFloat(val);
        if (isNaN(num) || num === 0) {
            return '-';
        }
        return Math.round(num).toLocaleString('id-ID');
    }

    // Formatter kode SAP (identik dengan Laporan Arus Kas / cashflow)
    function formatKodeSap(cell) {
        var val = cell.getValue();
        if (!val || val === '-') return '-';
        return '<span class="cf-kode">' + val + '</span>';
    }

    // Pasang formatter pada seluruh kolom nilai
    function decorateCols(cols) {
        cols.forEach(function (c) {
            if (c.columns) {
                decorateCols(c.columns);
                return;
            }
            if (c.field === 'kode') {
                c.formatter = formatKodeSap;
            } else if (c.field && c.field !== 'no' && c.field !== 'uraian') {
                c.formatter = formatNumberSap;
            }
        });
    }
    decorateCols(rawCols);

    var rowTypes = ['kategori', 'sub', 'item', 'subtotal', 'kattotal', 'grandtotal'];

    function rowStyleFormatter(row) {
        var t = row.getData().type;
        var el = row.getElement();
        rowTypes.forEach(function (k) {
            el.classList.toggle('mk-r-' + k, t === k);
        });
    }

    function initTable() {
        var el = document.getElementById('mk-sap-table');
        if (!el || !window.Tabulator) return;

        var userSized = !!localStorage.getItem('tabulator-cb-modal-kerja-sap-columns');

        var table = new Tabulator(el, {
            persistence: { columns: ['width'] },
            persistenceID: 'cb-modal-kerja-sap',
            data: rawRows,
            columns: rawCols,
            layout: userSized ? 'fitData' : 'fitColumns',
            height: 'calc(100vh - 200px)',
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            columnDefaults: {
                headerSort: false,
                minWidth: 40,
                variableHeight: true
            },
            rowFormatter: rowStyleFormatter,
            placeholder: 'Tidak ada data pengeluaran kas SAP pada periode ini.'
        });

        table.on('tableBuilt', function () {
            var holder = el.querySelector('.tabulator-tableholder');
            if (holder) holder.classList.add('drag-scroll');
        });
    }

    if (window.Tabulator) {
        initTable();
    } else {
        document.addEventListener('DOMContentLoaded', initTable);
    }
})();
</script>

@endif
