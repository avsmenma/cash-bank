<style>
    /* EVALUASI (RENCANA vs REALISASI) — Tabulator ala spreadsheet */
    #pn-eval-table { border: 1px solid #d0d5dd; border-radius: 8px; font-size: 12px; font-family: 'Segoe UI', system-ui, sans-serif; }
    #pn-eval-table .tabulator-header { background: #1e3a5f; border-bottom: 2px solid #0f2137; }
    #pn-eval-table .tabulator-header .tabulator-col,
    #pn-eval-table .tabulator-header .tabulator-col-group {
        background: #1e3a5f !important; color: #f0f4f8 !important; font-weight: 600; font-size: 11px;
        border-color: #ffffff !important; border-right: 1px solid #ffffff !important;
    }
    #pn-eval-table .tabulator-header .tabulator-col-group-cols > .tabulator-col:last-child { border-right: none !important; }
    #pn-eval-table .tabulator-header .tabulator-col .tabulator-col-title { color: #f0f4f8; text-align: center; }
    #pn-eval-table .tabulator-header .tabulator-col-resize-handle { width: 6px; cursor: col-resize; background: none; }
    #pn-eval-table .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
        background: linear-gradient(to bottom, transparent 32%, rgba(255,255,255,.45) 32%, rgba(255,255,255,.45) 68%, transparent 68%)
            center / 2px 100% no-repeat;
    }
    #pn-eval-table .tabulator-header .tabulator-col-resize-handle:hover { background: rgba(255,255,255,.3); }
    #pn-eval-table .tabulator-cell { border-right: 1px solid #b3bfcc; border-color: #b3bfcc; }
    #pn-eval-table .tabulator-row { border-bottom: 1px solid #b3bfcc; }
    #pn-eval-table .tabulator-row.pn-r-total .tabulator-cell { background: #1e3a5f; color: #fff; font-weight: 700; }
    @media print { #pn-eval-table .tabulator-tableholder { overflow: visible !important; max-height: none !important; } }
</style>

@php
    // Rakit baris data (logika lama utuh: total tahunan per kategori + footer Total)
    $evMonths = array_keys($bulanListFiltered); // nama bulan
    $evRows = [];
    $footer = [];
    foreach ($evMonths as $b) $footer[$b] = ['rencana' => 0, 'realisasi' => 0];

    foreach ($data as $kategori => $bulan) {
        $row = ['type' => 'item', 'uraian' => $kategori];
        $tr = 0; $tre = 0;
        foreach ($evMonths as $i => $b) {
            $v = $bulan[$b] ?? ['rencana' => 0, 'realisasi' => 0, 'selisih' => 0, 'persen' => 0];
            $row['m' . $i . '_r'] = $v['rencana'];
            $row['m' . $i . '_re'] = $v['realisasi'];
            $row['m' . $i . '_s'] = $v['selisih'];
            $row['m' . $i . '_p'] = $v['persen'];
            $footer[$b]['rencana'] += $v['rencana'];
            $footer[$b]['realisasi'] += $v['realisasi'];
            $tr += $v['rencana'];
            $tre += $v['realisasi'];
        }
        $row['tot_r'] = $tr;
        $row['tot_re'] = $tre;
        $row['tot_s'] = $tre - $tr;
        $row['tot_p'] = $tr > 0 ? ($tre / $tr) * 100 : 0;
        $evRows[] = $row;
    }

    $rowT = ['type' => 'total', 'uraian' => 'Total'];
    $gr = 0; $gre = 0;
    foreach ($evMonths as $i => $b) {
        $r = $footer[$b]['rencana'];
        $re = $footer[$b]['realisasi'];
        $rowT['m' . $i . '_r'] = $r;
        $rowT['m' . $i . '_re'] = $re;
        $rowT['m' . $i . '_s'] = $re - $r;
        $rowT['m' . $i . '_p'] = $r > 0 ? ($re / $r) * 100 : 0;
        $gr += $r;
        $gre += $re;
    }
    $rowT['tot_r'] = $gr;
    $rowT['tot_re'] = $gre;
    $rowT['tot_s'] = $gre - $gr;
    $rowT['tot_p'] = $gr > 0 ? ($gre / $gr) * 100 : 0;
    $evRows[] = $rowT;

    $evMonthTitles = [];
    foreach ($evMonths as $i => $b) $evMonthTitles[] = ['i' => $i, 'title' => ucfirst($b)];
@endphp

<div id="pn-eval-table"></div>

<script>
(function () {
    var evRows = @json($evRows);
    var evMonths = @json($evMonthTitles);
    var evTotalTitle = @json('Total ' . $tahun);

    function evFmt(cell) {
        var v = cell.getValue();
        if (v === null || v === undefined) return '';
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
        return { title: title, field: field, hozAlign: 'right', width: minW || 100, widthGrow: 1, formatter: evFmt, headerHozAlign: 'center' };
    }

    function group(title, prefix) {
        return { title: title, columns: [
            numCol('Rencana', prefix + '_r'),
            numCol('Realisasi', prefix + '_re'),
            numCol('Selisih', prefix + '_s'),
            numCol('%Tase', prefix + '_p', 85)
        ]};
    }

    function init() {
        var el = document.getElementById('pn-eval-table');
        if (!el || !window.Tabulator) return;
        var cols = [{ title: 'Kategori', field: 'uraian', frozen: true, width: 220, widthGrow: 2, headerHozAlign: 'center' }];
        evMonths.forEach(function (m) { cols.push(group(m.title, 'm' + m.i)); });
        cols.push(group(evTotalTitle, 'tot'));

        // Bila user sudah pernah menarik kolom, pakai lebar simpanannya apa
        // adanya (fitData); bila belum, kolom otomatis mengisi lebar wadah.
        var userSized = !!localStorage.getItem('tabulator-cb-penerima-evaluasi-columns');

        new Tabulator(el, {
            persistence: { columns: ['width'] },   // lebar kolom tarikan user tersimpan permanen (localStorage)
            persistenceID: 'cb-penerima-evaluasi',
            data: evRows,
            columns: cols,
            layout: userSized ? 'fitData' : 'fitColumns',
            height: '65vh',
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            columnDefaults: { headerSort: false, minWidth: 30, variableHeight: true },   // bebas dikecilkan; teks wrap, tinggi baris menyesuaikan
            rowFormatter: function (row) {
                row.getElement().classList.toggle('pn-r-total', row.getData().type === 'total');
            },
            placeholder: 'Tidak ada data'
        });
    }

    if (window.Tabulator) { init(); } else { document.addEventListener('DOMContentLoaded', init); }
})();
</script>
