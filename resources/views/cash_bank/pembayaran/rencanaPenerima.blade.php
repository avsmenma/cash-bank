@if(isset($kategori) && $kategori->count() > 0)
<style>
    /* RENCANA PENERIMA — Tabulator ala spreadsheet, sel bisa diedit */
    #tabel-rencana-penerima { border: 1px solid #d0d5dd; border-radius: 8px; font-size: 12px; font-family: 'Segoe UI', system-ui, sans-serif; }
    #tabel-rencana-penerima .tabulator-header { background: #1e3a5f; border-bottom: 2px solid #0f2137; }
    #tabel-rencana-penerima .tabulator-header .tabulator-col {
        background: #1e3a5f !important; color: #f0f4f8 !important; font-weight: 600; font-size: 11px;
        border-color: #ffffff !important; border-right: 1px solid #ffffff !important;
    }
    #tabel-rencana-penerima .tabulator-header .tabulator-col .tabulator-col-title { color: #f0f4f8; text-align: center; }
    #tabel-rencana-penerima .tabulator-header .tabulator-col-resize-handle { width: 6px; cursor: col-resize; background: none; }
    #tabel-rencana-penerima .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
        background: linear-gradient(to bottom, transparent 32%, rgba(255,255,255,.45) 32%, rgba(255,255,255,.45) 68%, transparent 68%)
            center / 2px 100% no-repeat;
    }
    #tabel-rencana-penerima .tabulator-header .tabulator-col-resize-handle:hover { background: rgba(255,255,255,.3); }
    #tabel-rencana-penerima .tabulator-cell { border-right: 1px solid #b3bfcc; border-color: #b3bfcc; }
    #tabel-rencana-penerima .tabulator-row { border-bottom: 1px solid #b3bfcc; }
    #tabel-rencana-penerima .tabulator-row.pn-r-total .tabulator-cell { background: #1e3a5f; color: #fff; font-weight: 700; }

    /* Sel editable + highlight perubahan (kontrak lama dipertahankan) */
    #tabel-rencana-penerima .cell-rencana {
        display: block;
        min-width: 70px;
        cursor: text;
        outline: none;
        transition: background 0.15s;
    }
    #tabel-rencana-penerima .cell-rencana.cell-changed {
        background-color: #fff3cd !important;
        border: 1.5px solid #ffc107 !important;
    }
    #tabel-rencana-penerima .cell-rencana:focus { outline: 2px solid #007bff; }
    #tabel-rencana-penerima .rp-row-total, #tabel-rencana-penerima .rp-grand { font-weight: 700; }
</style>

@php
    $bulanList = ['januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];
    $totalPerBulan = array_fill_keys($bulanList, 0);
    $grandTotal = 0;

    $rpRows = [];
    foreach ($kategori as $k) {
        $row0 = $data[$k->id_kategori_kriteria] ?? null;
        $row = [
            'type' => 'item',
            'uraian' => $k->nama_kriteria,
            'id_rencana' => $row0->id_rencana_penerima ?? '',
            'id_kategori' => $k->id_kategori_kriteria,
        ];
        $total = 0;
        foreach ($bulanList as $b) {
            $nilai = $row0->$b ?? 0;
            $row[$b] = $nilai;
            $total += $nilai;
            $totalPerBulan[$b] += $nilai;
            $grandTotal += $nilai;
        }
        $row['total'] = $total;
        $rpRows[] = $row;
    }
    $rowT = ['type' => 'total', 'uraian' => 'Total'];
    foreach ($bulanList as $b) $rowT[$b] = $totalPerBulan[$b];
    $rowT['total'] = $grandTotal;
    $rpRows[] = $rowT;
@endphp

<div id="tabel-rencana-penerima"></div>

<script>
(function () {
    var rpRows = @json($rpRows);
    var rpBulan = @json($bulanList);
    var rpTahun = @json($tahun);

    function fmt(v) {
        v = Number(v) || 0;
        var s = Math.round(Math.abs(v)).toLocaleString('id-ID');
        return v < 0 ? '(' + s + ')' : s;
    }
    function unformat(str) {
        return parseInt(String(str || '0').replace(/\./g, '').replace(/,/g, '')) || 0;
    }

    // Sel bulan: span contenteditable ber-class .cell-rencana + data attr
    // (kontrak lama — tombol Simpan halaman induk membaca elemen ini)
    function editFmt(cell) {
        var d = cell.getRow().getData();
        var b = cell.getField();
        var v = fmt(cell.getValue());
        if (d.type === 'total') {
            return '<span class="rp-col-total" data-bulan="' + b + '">' + v + '</span>';
        }
        return '<span class="cell-rencana" contenteditable="true"'
            + ' data-id="' + d.id_rencana + '"'
            + ' data-kategori="' + d.id_kategori + '"'
            + ' data-bulan="' + b + '"'
            + ' data-tahun="' + rpTahun + '"'
            + ' data-original="' + v + '">' + v + '</span>';
    }

    function totalFmt(cell) {
        var cls = cell.getRow().getData().type === 'total' ? 'rp-grand' : 'rp-row-total';
        return '<span class="' + cls + '">' + fmt(cell.getValue()) + '</span>';
    }

    function init() {
        var el = document.getElementById('tabel-rencana-penerima');
        if (!el || !window.Tabulator) return;

        var cols = [{ title: 'Kategori', field: 'uraian', frozen: true, minWidth: 220, widthGrow: 2, headerHozAlign: 'center' }];
        rpBulan.forEach(function (b) {
            cols.push({
                title: b.charAt(0).toUpperCase() + b.slice(1),
                field: b, hozAlign: 'right', minWidth: 95, widthGrow: 1,
                formatter: editFmt, headerHozAlign: 'center', variableHeight: true
            });
        });
        cols.push({ title: 'Total', field: 'total', hozAlign: 'right', minWidth: 110, widthGrow: 1, formatter: totalFmt, headerHozAlign: 'center' });

        new Tabulator(el, {
            data: rpRows,
            columns: cols,
            layout: 'fitColumns',
            height: '65vh',
            renderVertical: 'basic',        // semua baris di DOM (sel editable)
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            columnDefaults: { headerSort: false },
            rowFormatter: function (row) {
                row.getElement().classList.toggle('pn-r-total', row.getData().type === 'total');
            },
            placeholder: 'Tidak ada data'
        });

        // Highlight sel berubah + hitung ulang total baris/kolom secara langsung
        if (!el._rpBound) {
            el._rpBound = true;
            el.addEventListener('input', function (e) {
                var cellEl = e.target;
                if (!cellEl.classList || !cellEl.classList.contains('cell-rencana')) return;

                cellEl.classList.toggle('cell-changed', cellEl.innerText.trim() !== cellEl.getAttribute('data-original'));

                // Total baris
                var rowEl = cellEl.closest('.tabulator-row');
                if (rowEl) {
                    var rowSum = 0;
                    rowEl.querySelectorAll('.cell-rencana').forEach(function (c) { rowSum += unformat(c.innerText); });
                    var rowTotEl = rowEl.querySelector('.rp-row-total');
                    if (rowTotEl) rowTotEl.textContent = fmt(rowSum);
                }

                // Total kolom bulan + grand total
                var b = cellEl.getAttribute('data-bulan');
                var colSum = 0;
                el.querySelectorAll('.cell-rencana[data-bulan="' + b + '"]').forEach(function (c) { colSum += unformat(c.innerText); });
                var colTotEl = el.querySelector('.rp-col-total[data-bulan="' + b + '"]');
                if (colTotEl) colTotEl.textContent = fmt(colSum);

                var grand = 0;
                el.querySelectorAll('.rp-col-total').forEach(function (c) { grand += unformat(c.textContent); });
                var grandEl = el.querySelector('.rp-grand');
                if (grandEl) grandEl.textContent = fmt(grand);
            });
        }
    }

    if (window.Tabulator) { init(); } else { document.addEventListener('DOMContentLoaded', init); }
})();
</script>
@else
    <div class="alert alert-info m-3">Tidak ada kategori penerima.</div>
@endif
