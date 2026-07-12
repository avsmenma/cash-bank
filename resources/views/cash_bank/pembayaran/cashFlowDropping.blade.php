{{-- CSS & script inline karena partial ini dimuat via AJAX --}}
<style>
    /* CASHFLOW DROPPING — Tabulator ala spreadsheet */
    #cashflow-table { border: 1px solid #d0d5dd; border-radius: 8px; font-size: 12px; font-family: 'Segoe UI', system-ui, sans-serif; }
    #cashflow-table .tabulator-header { background: #1e3a5f; border-bottom: 2px solid #0f2137; }
    #cashflow-table .tabulator-header .tabulator-col {
        background: #1e3a5f !important; color: #f0f4f8 !important; font-weight: 600; font-size: 11px;
        border-color: #ffffff !important; border-right: 1px solid #ffffff !important;
    }
    #cashflow-table .tabulator-header .tabulator-col .tabulator-col-title { color: #f0f4f8; text-align: center; }
    #cashflow-table .tabulator-header .tabulator-col-resize-handle { width: 6px; cursor: col-resize; background: none; }
    #cashflow-table .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
        background: linear-gradient(to bottom, transparent 32%, rgba(255,255,255,.45) 32%, rgba(255,255,255,.45) 68%, transparent 68%)
            center / 2px 100% no-repeat;
    }
    #cashflow-table .tabulator-header .tabulator-col-resize-handle:hover { background: rgba(255,255,255,.3); }
    #cashflow-table .tabulator-cell { border-right: 1px solid #b3bfcc; border-color: #b3bfcc; }
    #cashflow-table .tabulator-row { border-bottom: 1px solid #b3bfcc; }

    #cashflow-table .tabulator-row.cf-r-kategori .tabulator-cell { background: rgba(30,58,95,.15); color: #1e3a5f; font-weight: 800; }
    #cashflow-table .tabulator-row.cf-r-sub .tabulator-cell { background: #f8fafc; font-weight: 700; }
    #cashflow-table .tabulator-row.cf-r-item .tabulator-cell { background: #fff; }
    #cashflow-table .tabulator-row.cf-r-item:hover .tabulator-cell { background: #f8fbff; }
    #cashflow-table .tabulator-row.cf-r-subtotal .tabulator-cell,
    #cashflow-table .tabulator-row.cf-r-total .tabulator-cell,
    #cashflow-table .tabulator-row.cf-r-grand .tabulator-cell { background: #1e3a5f; color: #fff; font-weight: 700; }
    @media print { #cashflow-table .tabulator-tableholder { overflow: visible !important; max-height: none !important; } }
</style>

@php
    // Rakit baris data Tabulator (logika hitung lama utuh)
    $cfRows = [];
    $no = 1;
    foreach ($result as $kategoriName => $kategoriData) {
        $cfRows[] = ['type' => 'kategori', 'no' => $no++, 'uraian' => $kategoriName];

        foreach ($kategoriData['subs'] as $subName => $subData) {
            $cfRows[] = ['type' => 'sub', 'uraian' => $subName];

            foreach ($subData['items'] as $itemName => $itemData) {
                $row = ['type' => 'item', 'uraian' => '- ' . $itemName];
                $t = 0;
                for ($m = 1; $m <= 12; $m++) { $v = $itemData[$m] ?? 0; $row['m' . $m] = $v; $t += $v; }
                $row['total'] = $t;
                $cfRows[] = $row;
            }

            $row = ['type' => 'subtotal', 'uraian' => 'Sub Total ' . $subName];
            $t = 0;
            for ($m = 1; $m <= 12; $m++) { $v = $subData['totals'][$m] ?? 0; $row['m' . $m] = $v; $t += $v; }
            $row['total'] = $t;
            $cfRows[] = $row;
        }

        $row = ['type' => 'total', 'uraian' => $kategoriName];
        $t = 0;
        for ($m = 1; $m <= 12; $m++) { $v = $kategoriData['totals'][$m] ?? 0; $row['m' . $m] = $v; $t += $v; }
        $row['total'] = $t;
        $cfRows[] = $row;
    }
    $row = ['type' => 'grand', 'uraian' => 'TOTAL Keseluruhan'];
    for ($m = 1; $m <= 12; $m++) $row['m' . $m] = $totals[$m] ?? 0;
    $row['total'] = $grandTotal ?? 0;
    $cfRows[] = $row;
@endphp

<div class="card-body">
    <div class="mb-3"><h5>Cash Flow Dropping - Tahun {{ $tahun }}</h5></div>
    <div id="cashflow-table"></div>
</div>

<script>
(function () {
    var cfRows = @json($cfRows);
    var cfBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    var cfUraianTitle = @json('Payments for ' . $tahun . ' transactions - Accounts');

    function cfFmt(cell) {
        var v = cell.getValue();
        if (v === null || v === undefined) return '';
        v = Number(v);
        var s = Math.round(Math.abs(v)).toLocaleString('id-ID');
        return v < 0 ? '(' + s + ')' : s;
    }

    function init() {
        var el = document.getElementById('cashflow-table');
        if (!el || !window.Tabulator) return;

        var cols = [
            { title: 'No', field: 'no', frozen: true, width: 48, hozAlign: 'center', headerHozAlign: 'center' },
            { title: cfUraianTitle, field: 'uraian', frozen: true, width: 260, widthGrow: 3, headerHozAlign: 'center' }
        ];
        cfBulan.forEach(function (nm, i) {
            cols.push({ title: nm, field: 'm' + (i + 1), hozAlign: 'right', width: 92, widthGrow: 1, formatter: cfFmt, headerHozAlign: 'center' });
        });
        cols.push({ title: 'Total', field: 'total', hozAlign: 'right', width: 115, widthGrow: 1, formatter: cfFmt, headerHozAlign: 'center' });

        // Bila user sudah pernah menarik kolom, pakai lebar simpanannya apa
        // adanya (fitData); bila belum, kolom otomatis mengisi lebar wadah.
        var userSized = !!localStorage.getItem('tabulator-cb-cashflow-dropping-columns');

        new Tabulator(el, {
            persistence: { columns: ['width'] },   // lebar kolom tarikan user tersimpan permanen (localStorage)
            persistenceID: 'cb-cashflow-dropping',
            data: cfRows,
            columns: cols,
            layout: userSized ? 'fitData' : 'fitColumns',
            height: '65vh',
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            columnDefaults: { headerSort: false, minWidth: 30, variableHeight: true },   // bebas dikecilkan; teks wrap, tinggi baris menyesuaikan
            rowFormatter: function (row) {
                var t = row.getData().type;
                var e2 = row.getElement();
                ['kategori', 'sub', 'item', 'subtotal', 'total', 'grand'].forEach(function (k) {
                    e2.classList.toggle('cf-r-' + k, t === k);
                });
            },
            placeholder: 'Tidak ada data untuk tahun {{ $tahun }}'
        });
    }

    if (window.Tabulator) { init(); } else { document.addEventListener('DOMContentLoaded', init); }
})();
</script>
