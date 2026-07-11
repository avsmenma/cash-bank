<style>
    /* ============================================================
       DASHBOARD PD & PvD — Tabulator (tabel ala spreadsheet).
       Kolom bisa ditarik-lebarkan manual lewat garis antar kolom header.
       ============================================================ */
    #dp-table {
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        font-size: 12px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    #dp-table .tabulator-header {
        background: #1e3a5f;
        border-bottom: 2px solid #0f2137;
    }
    #dp-table .tabulator-header .tabulator-col,
    #dp-table .tabulator-header .tabulator-col-group {
        background: #1e3a5f !important;
        color: #f0f4f8 !important;
        font-weight: 600;
        font-size: 11px;
        border-color: #ffffff !important;
        border-right: 1px solid #ffffff !important;
    }
    /* Kolom terakhir di dalam grup: garis batas dipegang grup induk */
    #dp-table .tabulator-header .tabulator-col-group-cols > .tabulator-col:last-child {
        border-right: none !important;
    }
    #dp-table .tabulator-header .tabulator-col .tabulator-col-title {
        color: #f0f4f8;
        text-align: center;
    }

    /* Penanda kolom bisa ditarik-lebarkan (takik hanya di kolom terbawah) */
    #dp-table .tabulator-header .tabulator-col-resize-handle {
        width: 6px;
        cursor: col-resize;
        background: none;
    }
    #dp-table .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
        background: linear-gradient(
            to bottom,
            transparent 32%,
            rgba(255, 255, 255, 0.45) 32%,
            rgba(255, 255, 255, 0.45) 68%,
            transparent 68%
        );
        background-size: 2px 100%;
        background-position: center;
        background-repeat: no-repeat;
    }
    #dp-table .tabulator-header .tabulator-col-resize-handle:hover {
        background: rgba(255, 255, 255, 0.30);
    }

    /* Garis kolom & baris tegas */
    #dp-table .tabulator-cell {
        border-right: 1px solid #b3bfcc;
        border-color: #b3bfcc;
    }
    #dp-table .tabulator-row {
        border-bottom: 1px solid #b3bfcc;
    }

    /* ---------- Jenis baris ---------- */
    #dp-table .tabulator-row.dp-r-section .tabulator-cell {
        background: rgba(30, 58, 95, 0.07);
        color: #1e3a5f;
        font-weight: 800;
        letter-spacing: .2px;
    }
    #dp-table .tabulator-row.dp-r-kat .tabulator-cell {
        background: #d7dde3;
        color: #172033;
        font-weight: 800;
    }
    #dp-table .tabulator-row.dp-r-sub .tabulator-cell {
        background: #eef4fb;
        color: #17324b;
        font-weight: 700;
    }
    #dp-table .tabulator-row.dp-r-item .tabulator-cell {
        background: #ffffff;
        color: #344054;
    }
    #dp-table .tabulator-row.dp-r-item:hover .tabulator-cell {
        background: #f8fbff;
    }
    #dp-table .tabulator-row.dp-r-total .tabulator-cell {
        background: #1e3a5f;
        color: #ffffff;
        font-weight: 700;
    }
    #dp-table .tabulator-row.dp-r-footer .tabulator-cell {
        background: #fef3c7;
        color: #1a1a1a;
        font-weight: 700;
    }
    #dp-table .tabulator-row.dp-r-footer .tabulator-cell:first-child {
        border-left: 3px solid #f59e0b;
    }

    @media print {
        #dp-table .tabulator-tableholder {
            overflow: visible !important;
            max-height: none !important;
        }
    }
</style>

@php
    // ================================================================
    // Susun baris tabel sebagai DATA untuk Tabulator.
    // Bulan diacu lewat indeks (nama bulan jadi judul kolom).
    // ================================================================
    $dpMonths = array_values($bulanListFiltered);
    $dpRows = [];

    $dpPush = function ($type, $uraian, $vals = null, $tot = null) use (&$dpRows, $dpMonths) {
        $row = ['type' => $type, 'uraian' => $uraian];
        foreach ($dpMonths as $i => $b) {
            $row['m' . $i . '_r'] = $vals[$b]['r'] ?? null;
            $row['m' . $i . '_re'] = $vals[$b]['re'] ?? null;
            $row['m' . $i . '_s'] = $vals[$b]['s'] ?? null;
            $row['m' . $i . '_p'] = $vals[$b]['p'] ?? null;
        }
        $row['tot_r'] = $tot['r'] ?? null;
        $row['tot_re'] = $tot['re'] ?? null;
        $row['tot_s'] = $tot['s'] ?? null;
        $row['tot_p'] = $tot['p'] ?? null;
        $dpRows[] = $row;
    };

    // Baris nilai per bulan + total baris dari array [bulan => rencana/realisasi/selisih/persen]
    $dpBuild = function ($bulanData) use ($dpMonths) {
        $vals = [];
        $tr = 0;
        $tre = 0;
        foreach ($dpMonths as $b) {
            $r = $bulanData[$b]['rencana'] ?? 0;
            $re = $bulanData[$b]['realisasi'] ?? 0;
            $vals[$b] = [
                'r' => $r,
                're' => $re,
                's' => $bulanData[$b]['selisih'] ?? 0,
                'p' => $bulanData[$b]['persen'] ?? 0,
            ];
            $tr += $r;
            $tre += $re;
        }
        return [$vals, [
            'r' => $tr,
            're' => $tre,
            's' => $tre - $tr,
            'p' => $tr > 0 ? ($tre / $tr) * 100 : 0,
        ]];
    };

    // ---------- PENERIMA ----------
    $dpPush('section', 'PENERIMA');
    foreach ($dataPenerima as $kategori => $bulanData) {
        [$vals, $tot] = $dpBuild($bulanData);
        $dpPush('item', $kategori, $vals, $tot);
    }
    [$vals, $tot] = $dpBuild($totalPenerima);
    $dpPush('total', 'Total Penerima', $vals, $tot);

    // ---------- DROPPING ----------
    $dpPush('section', 'DROPPING');
    foreach ($dataDropping as $kategori => $subs) {
        $dpPush('kat', $kategori);
        foreach ($subs as $sub => $items) {
            $dpPush('sub', $sub);
            foreach ($items as $item => $bulanData) {
                [$vals, $tot] = $dpBuild($bulanData);
                $dpPush('item', $item === '' ? '' : '- ' . $item, $vals, $tot);
            }
        }
    }
    [$vals, $tot] = $dpBuild($totalDropping);
    $dpPush('total', 'Total Dropping', $vals, $tot);

    // ---------- SELISIH PENERIMA - DROPPING ----------
    $selVals = [];
    $selTr = 0;
    $selTre = 0;
    foreach ($dpMonths as $b) {
        $r = ($totalPenerima[$b]['rencana'] ?? 0) - ($totalDropping[$b]['rencana'] ?? 0);
        $re = ($totalPenerima[$b]['realisasi'] ?? 0) - ($totalDropping[$b]['realisasi'] ?? 0);
        $selVals[$b] = [
            'r' => $r,
            're' => $re,
            's' => $re - $r,
            'p' => $r != 0 ? ($re / $r) * 100 : 0,
        ];
        $selTr += $r;
        $selTre += $re;
    }
    $dpPush('footer', 'Selisih Penerima - Dropping', $selVals, [
        'r' => $selTr,
        're' => $selTre,
        's' => $selTre - $selTr,
        'p' => $selTr != 0 ? ($selTre / $selTr) * 100 : 0,
    ]);

    $dpMonthTitles = [];
    foreach ($dpMonths as $i => $b) $dpMonthTitles[] = ['i' => $i, 'title' => ucfirst($b)];
@endphp

<div class="table-responsive">
    <div id="dp-table"></div>
</div>

<script>
(function () {
    var dpRows = @json($dpRows);
    var dpMonths = @json($dpMonthTitles);

    // Format ala formatMinus: negatif -> (x); kolom % -> 2 desimal + '%'
    function dpFmt(cell) {
        var v = cell.getValue();
        if (v === null || v === undefined || v === '') return '';
        v = Number(v);
        var isPct = cell.getField().slice(-2) === '_p';
        var s = Math.abs(v).toLocaleString('id-ID', {
            minimumFractionDigits: isPct ? 2 : 0,
            maximumFractionDigits: isPct ? 2 : 0
        });
        if (isPct) s += '%';
        return v < 0 ? '(' + s + ')' : s;
    }

    function numCol(title, field, minW) {
        return {
            title: title,
            field: field,
            hozAlign: 'right',
            minWidth: minW || 100,
            widthGrow: 1,
            formatter: dpFmt,
            headerHozAlign: 'center'
        };
    }

    function monthGroup(title, prefix) {
        return {
            title: title,
            columns: [
                numCol('Rencana', prefix + '_r'),
                numCol('Realisasi', prefix + '_re'),
                numCol('Selisih', prefix + '_s'),
                numCol('%', prefix + '_p', 85)
            ]
        };
    }

    function buildColumns() {
        var cols = [
            { title: 'URAIAN', field: 'uraian', frozen: true, minWidth: 250, widthGrow: 2, headerHozAlign: 'center' }
        ];
        dpMonths.forEach(function (m) {
            cols.push(monthGroup(m.title, 'm' + m.i));
        });
        cols.push(monthGroup('TOTAL', 'tot'));
        return cols;
    }

    function dpRowFormatter(row) {
        var t = row.getData().type;
        var el = row.getElement();
        ['section', 'kat', 'sub', 'item', 'total', 'footer'].forEach(function (k) {
            el.classList.toggle('dp-r-' + k, t === k);
        });
    }

    function initDp() {
        var el = document.getElementById('dp-table');
        if (!el || !window.Tabulator) return;

        new Tabulator(el, {
            persistence: { columns: ['width'] },   // lebar kolom tarikan user tersimpan permanen (localStorage)
            persistenceID: 'cb-dashboard-pembayaran',
            data: dpRows,
            columns: buildColumns(),
            layout: 'fitColumns',
            height: '68vh',
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            columnDefaults: { headerSort: false },
            rowFormatter: dpRowFormatter,
            placeholder: 'Tidak ada data'
        });
    }

    if (window.Tabulator) {
        initDp();
    } else {
        document.addEventListener('DOMContentLoaded', initDp);
    }
})();
</script>
