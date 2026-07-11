<style>
    /* ============================================================
       CASHFLOW PvD — Tabulator (tabel ala spreadsheet seperti project LM).
       Kolom bisa ditarik-lebarkan manual lewat garis antar kolom header.
       ============================================================ */
    #cashflow-table-pvd {
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        font-size: 11.5px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    #cashflow-table-pvd .tabulator-header {
        background: #1e3a5f;
        border-bottom: 2px solid #0f2137;
    }
    #cashflow-table-pvd .tabulator-header .tabulator-col,
    #cashflow-table-pvd .tabulator-header .tabulator-col-group {
        background: #1e3a5f !important;
        color: #f0f4f8 !important;
        font-weight: 600;
        font-size: 11px;
        /* garis header putih; border-right dipasang eksplisit karena tema
           tidak memberi garis pada elemen grup (batas antar bulan) */
        border-color: #ffffff !important;
        border-right: 1px solid #ffffff !important;
    }
    /* Kolom terakhir di dalam grup tidak perlu garis sendiri —
       garis batas sudah dipegang grup induknya (hindari garis dobel) */
    #cashflow-table-pvd .tabulator-header .tabulator-col-group-cols > .tabulator-col:last-child {
        border-right: none !important;
    }
    #cashflow-table-pvd .tabulator-header .tabulator-col .tabulator-col-title {
        color: #f0f4f8;
        text-align: center;
    }

    /* Penanda kolom bisa ditarik-lebarkan: takik putih HANYA di kolom
       terbawah (bukan di tiap tingkat grup, agar header tidak ramai) */
    #cashflow-table-pvd .tabulator-header .tabulator-col-resize-handle {
        width: 6px;
        cursor: col-resize;
        background: none;
    }
    #cashflow-table-pvd .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
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
    #cashflow-table-pvd .tabulator-header .tabulator-col-resize-handle:hover {
        background: rgba(255, 255, 255, 0.30);
    }

    /* Garis kolom & baris tegas agar mudah ditelusuri */
    #cashflow-table-pvd .tabulator-cell {
        border-right: 1px solid #b3bfcc;
        border-color: #b3bfcc;
    }
    #cashflow-table-pvd .tabulator-row {
        border-bottom: 1px solid #b3bfcc;
    }

    /* ---------- Jenis baris (kelas dari rowFormatter) ---------- */
    #cashflow-table-pvd .tabulator-row.pvd-r-kat .tabulator-cell {
        background: rgba(30, 58, 95, 0.15);
        color: #1e3a5f;
        font-weight: 800;
    }
    #cashflow-table-pvd .tabulator-row.pvd-r-sub .tabulator-cell {
        background: #f8fafc;
        color: #1a1a1a;
        font-weight: 600;
        font-style: italic;
    }
    #cashflow-table-pvd .tabulator-row.pvd-r-item .tabulator-cell {
        background: #ffffff;
        color: #344054;
    }
    #cashflow-table-pvd .tabulator-row.pvd-r-item:hover .tabulator-cell {
        background: #f8fafc;
    }
    #cashflow-table-pvd .tabulator-row.pvd-r-subtotal .tabulator-cell {
        background: #eef2f7;
        color: #1a1a1a;
        font-weight: 600;
        border-top: 1px solid #cbd5e1;
    }
    #cashflow-table-pvd .tabulator-row.pvd-r-kattotal .tabulator-cell,
    #cashflow-table-pvd .tabulator-row.pvd-r-grand .tabulator-cell {
        background: #1e3a5f;
        color: #ffffff;
        font-weight: 700;
    }

    @media print {
        #cashflow-table-pvd .tabulator-tableholder {
            overflow: visible !important;
            max-height: none !important;
        }
    }
</style>

@php
    // ================================================================
    // Susun data kategori -> sub kriteria -> item (logika lama utuh),
    // lalu rakit menjadi baris DATA untuk Tabulator.
    // ================================================================
    $organizedData = [];

    foreach (['dropping', 'permintaan', 'pembayaran'] as $sumber) {
        if (!isset($result[$sumber])) continue;
        foreach ($result[$sumber] as $item) {
            $kategori = $item['kategori'];
            $subKriteria = $item['sub_kriteria'];
            $itemKriteria = $item['item_kriteria'];

            if (!isset($organizedData[$kategori][$subKriteria][$itemKriteria])) {
                $organizedData[$kategori][$subKriteria][$itemKriteria] = [
                    'permintaan' => [],
                    'dropping' => [],
                    'pembayaran' => []
                ];
            }
            $organizedData[$kategori][$subKriteria][$itemKriteria][$sumber] = $item['data'];
        }
    }

    $organizedData = \App\Support\DashboardKriteriaHierarchy::sortNested($organizedData);

    $pvdRows = [];
    $pvdPush = function ($type, $no, $uraian, $perBulan = null, $tot = null, $pct = null) use (&$pvdRows, $bulanListFiltered) {
        // $perBulan: [$noBulan => ['p'=>..,'d'=>..,'b'=>..]]
        $row = ['type' => $type, 'no' => $no, 'uraian' => $uraian];
        foreach ($bulanListFiltered as $noBulan => $nm) {
            $row['m' . $noBulan . '_p'] = $perBulan[$noBulan]['p'] ?? null;
            $row['m' . $noBulan . '_d'] = $perBulan[$noBulan]['d'] ?? null;
            $row['m' . $noBulan . '_b'] = $perBulan[$noBulan]['b'] ?? null;
        }
        $row['tot_p'] = $tot['p'] ?? null;
        $row['tot_d'] = $tot['d'] ?? null;
        $row['tot_b'] = $tot['b'] ?? null;
        $row['pct_p'] = $pct['p'] ?? null;
        $row['pct_d'] = $pct['d'] ?? null;
        $pvdRows[] = $row;
    };

    $grandTotal = [];
    foreach ($bulanListFiltered as $b => $n) $grandTotal[$b] = ['p' => 0, 'd' => 0, 'b' => 0];
    $grandAll = ['p' => 0, 'd' => 0, 'b' => 0];

    $rowNumber = 1;
    foreach ($organizedData as $kategori => $subKriterias) {
        $katTotal = [];
        foreach ($bulanListFiltered as $b => $n) $katTotal[$b] = ['p' => 0, 'd' => 0, 'b' => 0];
        $katAll = ['p' => 0, 'd' => 0, 'b' => 0];

        $pvdPush('kat', $rowNumber++, $kategori);

        foreach ($subKriterias as $subKriteria => $items) {
            $subTotal = [];
            foreach ($bulanListFiltered as $b => $n) $subTotal[$b] = ['p' => 0, 'd' => 0, 'b' => 0];
            $subAll = ['p' => 0, 'd' => 0, 'b' => 0];

            $pvdPush('sub', null, $subKriteria);

            foreach ($items as $itemKriteria => $data) {
                $perBulan = [];
                $itemAll = ['p' => 0, 'd' => 0, 'b' => 0];
                foreach ($bulanListFiltered as $noBulan => $nm) {
                    $p = $data['permintaan'][$noBulan] ?? 0;
                    $d = $data['dropping'][$noBulan] ?? 0;
                    $bb = $data['pembayaran'][$noBulan] ?? 0;
                    $perBulan[$noBulan] = ['p' => $p, 'd' => $d, 'b' => $bb];

                    $itemAll['p'] += $p; $itemAll['d'] += $d; $itemAll['b'] += $bb;
                    $subTotal[$noBulan]['p'] += $p; $subTotal[$noBulan]['d'] += $d; $subTotal[$noBulan]['b'] += $bb;
                    $katTotal[$noBulan]['p'] += $p; $katTotal[$noBulan]['d'] += $d; $katTotal[$noBulan]['b'] += $bb;
                    $grandTotal[$noBulan]['p'] += $p; $grandTotal[$noBulan]['d'] += $d; $grandTotal[$noBulan]['b'] += $bb;
                }
                $subAll['p'] += $itemAll['p']; $subAll['d'] += $itemAll['d']; $subAll['b'] += $itemAll['b'];
                $katAll['p'] += $itemAll['p']; $katAll['d'] += $itemAll['d']; $katAll['b'] += $itemAll['b'];

                $pvdPush('item', null, $itemKriteria === '' ? '' : '- ' . $itemKriteria, $perBulan, $itemAll, [
                    'p' => $itemAll['p'] > 0 ? ($itemAll['b'] / $itemAll['p'] * 100) : 0,
                    'd' => $itemAll['d'] > 0 ? ($itemAll['b'] / $itemAll['d'] * 100) : 0,
                ]);
            }

            $pvdPush('subtotal', null, 'Sub Total ' . $subKriteria, $subTotal, $subAll, [
                'p' => $subAll['p'] > 0 ? ($subAll['b'] / $subAll['p'] * 100) : 0,
                'd' => $subAll['d'] > 0 ? ($subAll['b'] / $subAll['d'] * 100) : 0,
            ]);
        }

        $grandAll['p'] += $katAll['p']; $grandAll['d'] += $katAll['d']; $grandAll['b'] += $katAll['b'];

        $pvdPush('kattotal', null, 'Total ' . $kategori, $katTotal, $katAll, [
            'p' => $katAll['p'] > 0 ? ($katAll['b'] / $katAll['p'] * 100) : 0,
            'd' => $katAll['d'] > 0 ? ($katAll['b'] / $katAll['d'] * 100) : 0,
        ]);
    }

    $pvdPush('grand', null, 'TOTAL KESELURUHAN', $grandTotal, $grandAll, [
        'p' => $grandAll['p'] > 0 ? ($grandAll['b'] / $grandAll['p'] * 100) : 0,
        'd' => $grandAll['d'] > 0 ? ($grandAll['b'] / $grandAll['d'] * 100) : 0,
    ]);

    $pvdMonths = [];
    foreach ($bulanListFiltered as $noBulan => $namaBulan) {
        $pvdMonths[] = ['no' => $noBulan, 'title' => ucfirst($namaBulan) . ' ' . $tahun];
    }
@endphp

<div class="row">
<div class="col-12">
    <div id="cashflow-table-pvd"></div>
</div>
</div>

<script>
(function () {
    var pvdRows = @json($pvdRows);
    var pvdMonths = @json($pvdMonths);
    var pvdTitleUraian = @json('Payments for ' . $tahun . ' transactions - Accounts');

    // Format angka: item 0/negatif -> '-', persen 2 desimal + '%'
    function pvdFmt(cell) {
        var v = cell.getValue();
        if (v === null || v === undefined || v === '') return '';
        v = Number(v);
        var field = cell.getField();
        if (field.indexOf('pct_') === 0) {
            return v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
        }
        if (cell.getRow().getData().type === 'item') {
            return v > 0 ? Math.round(v).toLocaleString('id-ID') : '-';
        }
        return Math.round(v).toLocaleString('id-ID');
    }

    function numCol(title, field, minW) {
        return {
            title: title,
            field: field,
            hozAlign: 'right',
            minWidth: minW || 95,
            widthGrow: 1,
            formatter: pvdFmt,
            headerHozAlign: 'center'
        };
    }

    function buildColumns() {
        var cols = [
            { title: 'No.', field: 'no', frozen: true, width: 52, hozAlign: 'center', headerHozAlign: 'center' },
            { title: pvdTitleUraian, field: 'uraian', frozen: true, minWidth: 280, widthGrow: 2, headerHozAlign: 'center' }
        ];

        // Bulan -> (Permintaan RD / Dropping HO / Pembayaran) -> nomor minggu
        pvdMonths.forEach(function (m) {
            cols.push({
                title: m.title,
                columns: [
                    { title: 'Permintaan RD', columns: [numCol('1', 'm' + m.no + '_p')] },
                    { title: 'Dropping HO', columns: [numCol('2', 'm' + m.no + '_d')] },
                    { title: 'Pembayaran', columns: [numCol('3', 'm' + m.no + '_b')] }
                ]
            });
        });

        cols.push({
            title: 'Total',
            columns: [
                { title: 'Permintaan RD', columns: [numCol('1', 'tot_p', 105)] },
                { title: 'Dropping HO', columns: [numCol('2', 'tot_d', 105)] },
                { title: 'Pembayaran', columns: [numCol('3', 'tot_b', 105)] }
            ]
        });

        cols.push({
            title: '%Tase Pembayaran Thdp',
            columns: [
                { title: 'Permintaan', columns: [numCol('1', 'pct_p', 90)] },
                { title: 'Dropping', columns: [numCol('2', 'pct_d', 90)] }
            ]
        });

        return cols;
    }

    // Kelas per jenis baris untuk pewarnaan (pola rowFormatter project LM)
    function pvdRowFormatter(row) {
        var t = row.getData().type;
        var el = row.getElement();
        ['kat', 'sub', 'item', 'subtotal', 'kattotal', 'grand'].forEach(function (k) {
            el.classList.toggle('pvd-r-' + k, t === k);
        });
    }

    function initPvd() {
        var el = document.getElementById('cashflow-table-pvd');
        if (!el || !window.Tabulator) return;

        new Tabulator(el, {
            persistence: { columns: ['width'] },   // lebar kolom tarikan user tersimpan permanen (localStorage)
            persistenceID: 'cb-cashflow-pvd',
            data: pvdRows,
            columns: buildColumns(),
            // fitColumns: kolom mengisi penuh lebar wadah; tetap bisa ditarik
            // manual dan otomatis scroll horizontal bila kolom banyak (minWidth).
            layout: 'fitColumns',
            height: '68vh',
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            columnDefaults: { headerSort: false },
            rowFormatter: pvdRowFormatter,
            placeholder: 'Tidak ada data'
        });
    }

    // Saat render penuh halaman, script ini jalan sebelum Tabulator dimuat;
    // saat dimuat via AJAX, Tabulator sudah tersedia dan langsung jalan.
    if (window.Tabulator) {
        initPvd();
    } else {
        document.addEventListener('DOMContentLoaded', initPvd);
    }
})();
</script>
