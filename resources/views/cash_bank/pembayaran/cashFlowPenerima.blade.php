<style>
    /* REKAP PENERIMAAN — Tabulator ala spreadsheet */
    #pn-rekap-table { border: 1px solid #d0d5dd; border-radius: 8px; font-size: 12px; font-family: 'Segoe UI', system-ui, sans-serif; }
    #pn-rekap-table .tabulator-header { background: #1e3a5f; border-bottom: 2px solid #0f2137; }
    #pn-rekap-table .tabulator-header .tabulator-col,
    #pn-rekap-table .tabulator-header .tabulator-col-group {
        background: #1e3a5f !important; color: #f0f4f8 !important; font-weight: 600; font-size: 11px;
        border-color: #ffffff !important; border-right: 1px solid #ffffff !important;
    }
    #pn-rekap-table .tabulator-header .tabulator-col-group-cols > .tabulator-col:last-child { border-right: none !important; }
    #pn-rekap-table .tabulator-header .tabulator-col .tabulator-col-title { color: #f0f4f8; text-align: center; }
    #pn-rekap-table .tabulator-header .tabulator-col-resize-handle { width: 6px; cursor: col-resize; background: none; }
    #pn-rekap-table .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
        background: linear-gradient(to bottom, transparent 32%, rgba(255,255,255,.45) 32%, rgba(255,255,255,.45) 68%, transparent 68%)
            center / 2px 100% no-repeat;
    }
    #pn-rekap-table .tabulator-header .tabulator-col-resize-handle:hover { background: rgba(255,255,255,.3); }
    #pn-rekap-table .tabulator-cell { border-right: 1px solid #b3bfcc; border-color: #b3bfcc; }
    #pn-rekap-table .tabulator-row { border-bottom: 1px solid #b3bfcc; }
    #pn-rekap-table .tabulator-row.pn-r-total .tabulator-cell { background: #1e3a5f; color: #fff; font-weight: 700; }
    @media print { #pn-rekap-table .tabulator-tableholder { overflow: visible !important; max-height: none !important; } }
</style>

@php
    $bulanNamaRekap = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

    $rkRows = [];
    $totalBulan = array_fill(1, 12, 0);
    foreach ($result as $kategori => $bulan) {
        $row = ['type' => 'item', 'uraian' => $kategori];
        $sum = 0;
        for ($i = 1; $i <= 12; $i++) {
            $v = $bulan[$i] ?? 0;
            $row['m' . $i] = $v;
            $totalBulan[$i] += $v;
            $sum += $v;
        }
        $row['total'] = $sum;
        $rkRows[] = $row;
    }
    $rowTotal = ['type' => 'total', 'uraian' => 'Total'];
    for ($i = 1; $i <= 12; $i++) $rowTotal['m' . $i] = $totalBulan[$i];
    $rowTotal['total'] = array_sum($totalBulan);
    $rkRows[] = $rowTotal;
@endphp

<div id="pn-rekap-table"></div>

<script>
(function () {
    var rkRows = @json($rkRows);
    var rkMonths = @json($bulanNamaRekap);

    function rkFmt(cell) {
        var v = cell.getValue();
        if (v === null || v === undefined) return '';
        v = Number(v);
        var s = Math.round(Math.abs(v)).toLocaleString('id-ID');
        return v < 0 ? '(' + s + ')' : s;
    }

    function init() {
        var el = document.getElementById('pn-rekap-table');
        if (!el || !window.Tabulator) return;
        var cols = [{ title: 'PENERIMAAN', field: 'uraian', frozen: true, width: 220, widthGrow: 2, headerHozAlign: 'center' }];
        Object.keys(rkMonths).forEach(function (i) {
            cols.push({ title: rkMonths[i], field: 'm' + i, hozAlign: 'right', width: 100, widthGrow: 1, formatter: rkFmt, headerHozAlign: 'center' });
        });
        cols.push({ title: 'Total', field: 'total', hozAlign: 'right', width: 120, widthGrow: 1, formatter: rkFmt, headerHozAlign: 'center' });

        // Bila user sudah pernah menarik kolom, pakai lebar simpanannya apa
        // adanya (fitData); bila belum, kolom otomatis mengisi lebar wadah.
        var userSized = !!localStorage.getItem('tabulator-cb-penerima-rekap-columns');

        new Tabulator(el, {
            persistence: { columns: ['width'] },   // lebar kolom tarikan user tersimpan permanen (localStorage)
            persistenceID: 'cb-penerima-rekap',
            data: rkRows,
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
