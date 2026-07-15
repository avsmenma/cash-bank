@extends("layouts/index")
@section('content')

<div class="container-fluid mt-4">
    <section class="content-header cb-fullscreen-hide">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="font-weight:700;color:#0d3b6e;">
                        <i class="fas fa-landmark mr-2"></i>Rekening Koran
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.pembayaran.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Rekening Koran</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        {{-- FILTER BAR --}}
        <div class="card shadow mb-3 cb-fullscreen-hide" style="border-top:4px solid #0d3b6e;">
            <div class="card-body py-3">
                <form action="{{ route('bank-keluar.report') }}" method="GET" id="filterForm">
                    <div class="row align-items-end g-2">
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Tahun</label>
                            <select name="tahun" class="form-control form-control-sm" onchange="submitForm()" style="min-width:80px;">
                                @foreach($tahunList as $t)
                                    <option value="{{ $t }}" {{ (string) $tahun === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Bulan</label>
                            <select name="bulan" class="form-control form-control-sm" onchange="submitForm()" style="min-width:110px;">
                                <option value="">Semua Bulan</option>
                                @foreach($bulanList as $b)
                                    <option value="{{ $b }}" {{ (string) $bulan === (string) $b ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Sumber Dana</label>
                            <select name="sumber_dana[]" class="form-control form-control-sm" onchange="submitForm()" style="min-width:180px;">
                                <option value="">Semua Sumber Dana</option>
                                @foreach($sumberDanaList as $sd)
                                    <option value="{{ $sd->id_sumber_dana }}" {{ collect($sumberDanaIds ?? [])->contains($sd->id_sumber_dana) ? 'selected' : '' }}>
                                        {{ $sd->nama_sumber_dana }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Dari Tanggal</label>
                            <input type="date" name="tgl_dari" class="form-control form-control-sm"
                                   value="{{ request('tgl_dari') }}" onchange="submitForm()">
                        </div>
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Sampai Tanggal</label>
                            <input type="date" name="tgl_sampai" class="form-control form-control-sm"
                                   value="{{ request('tgl_sampai') }}" onchange="submitForm()">
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilter()">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                        <div class="col-auto ml-auto">
                            <a href="{{ route('bank-keluar.report_export_excel', request()->all()) }}"
                               class="btn btn-sm btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE (Tabulator, muat bertahap saat scroll) --}}
        <div class="card shadow cb-fullscreen-table" style="border:none;">
            <div class="d-flex align-items-center justify-content-end px-3 pt-3 pb-2 cb-fullscreen-hide">
                <div id="rkEntriesInfo" class="small text-secondary">Memuat data...</div>
            </div>
            <div class="card-body p-0">
                <div id="rkTable"></div>
                {{-- Footer TOTAL (nilai dari server, konsisten dgn versi lama) --}}
                <div class="rk-total-bar">
                    <span class="rk-total-label">TOTAL</span>
                    <span>Debet: <b id="rkTotDebet">{{ number_format($totalDebet ?? 0, 0, ',', '.') }}</b></span>
                    <span>Kredit: <b id="rkTotKredit">{{ number_format($totalKredit ?? 0, 0, ',', '.') }}</b></span>
                    <span>Saldo Akhir: <b id="rkTotSaldo">@if($finalSaldo !== null){{ $finalSaldo < 0 ? '(' . number_format(abs($finalSaldo), 0, ',', '.') . ')' : number_format($finalSaldo, 0, ',', '.') }}@else - @endif</b></span>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* REKENING KORAN — Tabulator ala spreadsheet */
#rkTable { border: 1px solid #d0dce8; font-size: 11.5px; font-family: 'Segoe UI', system-ui, sans-serif; }
#rkTable .tabulator-header { background: #0d3b6e; border-bottom: 2px solid #082948; }
#rkTable .tabulator-header .tabulator-col {
    background: #0d3b6e !important; color: #fff !important; font-weight: 600; font-size: 11.5px;
    border-color: #ffffff !important; border-right: 1px solid #ffffff !important;
}
#rkTable .tabulator-header .tabulator-col .tabulator-col-title { color: #fff; text-align: center; }
#rkTable .tabulator-header .tabulator-col-resize-handle { width: 6px; cursor: col-resize; background: none; }
#rkTable .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
    background: linear-gradient(to bottom, transparent 32%, rgba(255,255,255,.45) 32%, rgba(255,255,255,.45) 68%, transparent 68%)
        center / 2px 100% no-repeat;
}
#rkTable .tabulator-header .tabulator-col-resize-handle:hover { background: rgba(255,255,255,.3); }
/* Pesan tempat scrollbar sejak awal: tanpa ini scrollbar muncul SETELAH
   lebar kolom dihitung, sehingga isi tabel bergeser tidak sejajar header */
#rkTable .tabulator-tableholder { scrollbar-gutter: stable; }
#rkTable .tabulator-cell { border-right: 1px solid #c3d2e0; border-color: #c3d2e0; }
#rkTable .tabulator-row { border-bottom: 1px solid #c3d2e0; }
#rkTable .tabulator-row:hover .tabulator-cell { background: #f5f8fc; }

/* Active cell ala spreadsheet: sel yang diklik diberi bingkai + latar biru muda */
#rkTable .tabulator-cell.rk-active-cell {
    outline: 2px solid #1b6fd8; outline-offset: -2px;
    background: #e8f1fd !important;
}
#rkTable .tabulator-row:hover .tabulator-cell.rk-active-cell { background: #e8f1fd !important; }

/* Blok sel (drag ala Google Sheets) */
#rkTable .tabulator-cell.rk-range-cell { background: #dbeafe !important; }
#rkTable .tabulator-row:hover .tabulator-cell.rk-range-cell { background: #dbeafe !important; }
#rkTable.rk-noselect, #rkTable.rk-noselect * { user-select: none; }

/* Popup kecil hasil penjumlahan sel yang diblok */
.rk-sum-pop {
    position: fixed; z-index: 1080; display: none;
    background: #0d3b6e; color: #fff; font-size: 12px; line-height: 1;
    padding: 8px 12px; border-radius: 6px; box-shadow: 0 4px 14px rgba(0,0,0,.28);
    pointer-events: none; white-space: nowrap;
}
.rk-sum-pop b { color: #ffd166; }

#rkTable .rk-wrap { white-space: normal !important; overflow-wrap: anywhere; line-height: 1.35; }
#rkTable .rk-debet { color: #1a7a3d; font-weight: 600; }
#rkTable .rk-kredit { color: #c0392b; font-weight: 600; }
#rkTable .rk-saldo { color: #0d3b6e; font-weight: 700; }

.rk-total-bar {
    display: flex; gap: 26px; align-items: center; justify-content: flex-end;
    background: #1b4f72; color: #fff; font-size: 12.5px; padding: 9px 14px;
}
.rk-total-bar .rk-total-label { margin-right: auto; font-weight: 700; letter-spacing: .4px; }
.rk-total-bar #rkTotDebet { color: #a9dfbf; }
.rk-total-bar #rkTotKredit { color: #f1948a; }
.rk-total-bar #rkTotSaldo { color: #fad7a0; }

@media print { #rkTable .tabulator-tableholder { overflow: visible !important; max-height: none !important; } }
</style>

<script>
function submitForm()  { document.getElementById('filterForm').submit(); }
function resetFilter() {
    const tahun = document.querySelector('[name="tahun"]')?.value;
    window.location.href = '{{ route("bank-keluar.report") }}?tahun=' + (tahun || '');
}
</script>

@push('scripts')
<script>
(function () {
    var reportParams = @json(request()->query());
    var rkTotal = null;

    function init() {
        var el = document.getElementById('rkTable');
        if (!el || !window.Tabulator) return;

        function col(title, field, opts) {
            return Object.assign({ title: title, field: field, headerHozAlign: 'center', width: 90, widthGrow: 1, headerSort: false }, opts || {});
        }

        // Bila user sudah pernah menarik kolom, pakai lebar simpanannya apa
        // adanya (fitData); bila belum, kolom otomatis mengisi lebar wadah.
        var userSized = !!localStorage.getItem('tabulator-cb-rekening-koran-columns');

        var table = new Tabulator(el, {
            persistence: { columns: ['width'] },   // lebar kolom tarikan user tersimpan permanen (localStorage)
            persistenceID: 'cb-rekening-koran',
            layout: userSized ? 'fitData' : 'fitColumns',
            height: '62vh',
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            columnDefaults: { minWidth: 30, variableHeight: true },   // bebas dikecilkan; teks wrap, tinggi baris menyesuaikan
            placeholder: 'Tidak ada data untuk filter yang dipilih.',
            columns: [
                col('No', 'no', { width: 56, hozAlign: 'center' }),
                col('No Agenda', 'no_agenda', { width: 110 }),
                col('Tanggal', 'tanggal', { hozAlign: 'center', width: 100 }),
                col('No Bukti', 'no_sap', { width: 105 }),
                col('Sumber Dana', 'nama_sumber_dana', { width: 200, widthGrow: 2, cssClass: 'rk-wrap', variableHeight: true, formatter: 'html' }),
                col('Penerima / Dari', 'penerima', { width: 150, widthGrow: 2, cssClass: 'rk-wrap', variableHeight: true, formatter: 'html' }),
                col('Uraian', 'uraian', { width: 320, widthGrow: 4, cssClass: 'rk-wrap', variableHeight: true, formatter: 'html', tooltip: true }),
                col('Debet', 'debet', { hozAlign: 'right', width: 115, cssClass: 'rk-debet' }),
                col('Kredit', 'kredit', { hozAlign: 'right', width: 115, cssClass: 'rk-kredit' }),
                col('Saldo Akhir', 'saldo_akhir', { hozAlign: 'right', width: 120, cssClass: 'rk-saldo' })
            ],

            // Muat bertahap 100 baris saat scroll — endpoint lama (start/length) tetap dipakai
            progressiveLoad: 'scroll',
            paginationSize: 100,
            ajaxURL: @json(route('bank-keluar.report.data')),
            ajaxURLGenerator: function (url, config, params) {
                var size = params.size || 100;
                var usp = new URLSearchParams();
                Object.keys(reportParams).forEach(function (k) {
                    var v = reportParams[k];
                    if (Array.isArray(v)) { v.forEach(function (x) { usp.append(k + '[]', x); }); }
                    else if (v !== null && v !== undefined) { usp.append(k, v); }
                });
                usp.append('draw', params.page);
                usp.append('start', (params.page - 1) * size);
                usp.append('length', size);
                return url + '?' + usp.toString();
            },
            ajaxResponse: function (url, params, response) {
                if (response && response.totals) {
                    document.getElementById('rkTotDebet').textContent = response.totals.debet;
                    document.getElementById('rkTotKredit').textContent = response.totals.kredit;
                    document.getElementById('rkTotSaldo').textContent = response.totals.saldo;
                }
                rkTotal = parseInt((response && response.recordsFiltered) || 0, 10);
                var size = params.size || 100;
                return {
                    last_page: Math.max(1, Math.ceil(rkTotal / size)),
                    data: (response && response.data) || []
                };
            },

            rowFormatter: function (row) {
                var cls = row.getData().DT_RowClass;
                if (cls) row.getElement().classList.add(cls);
                // Virtual render Tabulator membuat ulang elemen baris saat scroll —
                // pasang kembali highlight active cell & blok sel bila baris ini pemiliknya.
                if (rkActive && row.getData().no === rkActive.no) {
                    var c = row.getCell(rkActive.field);
                    if (c) c.getElement().classList.add('rk-active-cell');
                }
                rkPaintRow(row);
            }
        });

        // ===== ACTIVE CELL + BLOK SEL (ala spreadsheet) =====
        // Klik sel untuk memilih; drag (atau Shift+klik) memblok banyak sel dan
        // memunculkan popup Jumlah; panah ⬅⬆⬇➡ memindah pilihan; Esc menghapus.
        var rkActive   = null;  // { no, field } — sel aktif (tahan terhadap re-render)
        var rkAnchor   = null;  // { r, c } — titik awal blok
        var rkRange    = null;  // { r1, c1, r2, c2 } — blok ternormalisasi
        var rkDragging = false;

        var rkPop = document.createElement('div');
        rkPop.className = 'rk-sum-pop';
        document.body.appendChild(rkPop);

        function rkVisCols() {
            return table.getColumns().filter(function (c) { return c.isVisible(); });
        }
        function rkCellPos(cell) {
            var p = cell.getRow().getPosition();
            var cols = rkVisCols();
            var ci = -1;
            for (var i = 0; i < cols.length; i++) {
                if (cols[i].getField() === cell.getField()) { ci = i; break; }
            }
            return (p === false || ci < 0) ? null : { r: p - 1, c: ci };
        }

        function rkClearActiveEl() {
            el.querySelectorAll('.tabulator-cell.rk-active-cell').forEach(function (n) {
                n.classList.remove('rk-active-cell');
            });
        }
        function rkSetActive(cell) {
            rkClearActiveEl();
            rkActive = { no: cell.getRow().getData().no, field: cell.getField() };
            cell.getElement().classList.add('rk-active-cell');
        }
        function rkGetActiveCell() {
            if (!rkActive) return null;
            var row = table.getRows().find(function (r) { return r.getData().no === rkActive.no; });
            return row ? row.getCell(rkActive.field) : null;
        }

        // --- blok sel ---
        function rkPaintRow(row) {
            if (!rkRange) return;
            var p = row.getPosition();
            if (p === false) return;
            var r = p - 1;
            if (r < rkRange.r1 || r > rkRange.r2) return;
            var cols = rkVisCols();
            for (var c = rkRange.c1; c <= rkRange.c2 && c < cols.length; c++) {
                var cell = row.getCell(cols[c]);
                var ce = cell && cell.getElement();
                if (ce && ce.classList) ce.classList.add('rk-range-cell');
            }
        }
        function rkApplyRange() {
            el.querySelectorAll('.tabulator-cell.rk-range-cell').forEach(function (n) {
                n.classList.remove('rk-range-cell');
            });
            if (!rkRange) return;
            table.getRows().slice(rkRange.r1, rkRange.r2 + 1).forEach(rkPaintRow);
        }
        function rkSetRange(a, b) {
            rkRange = {
                r1: Math.min(a.r, b.r), r2: Math.max(a.r, b.r),
                c1: Math.min(a.c, b.c), c2: Math.max(a.c, b.c)
            };
            rkApplyRange();
        }
        function rkClearRange() {
            rkRange = null;
            rkApplyRange();
            rkPop.style.display = 'none';
        }

        // Angka format Indonesia: "1.039.538.838", "(2.500)", "12,5". Tanggal,
        // kode agenda, dan teks lain ditolak agar tidak ikut terjumlah.
        function rkParseNum(v) {
            if (v === null || v === undefined) return null;
            var s = String(v).replace(/<[^>]*>/g, '').trim();
            if (!s || s === '-') return null;
            var neg = /^\(.*\)$/.test(s);
            s = s.replace(/[()\s]/g, '').replace(/^Rp/i, '');
            if (!/^-?\d{1,3}(\.\d{3})*(,\d+)?$/.test(s) && !/^-?\d+(,\d+)?$/.test(s)) return null;
            var n = parseFloat(s.replace(/\./g, '').replace(',', '.'));
            if (isNaN(n)) return null;
            return neg ? -n : n;
        }
        function rkShowPop(mx, my) {
            if (!rkRange || (rkRange.r1 === rkRange.r2 && rkRange.c1 === rkRange.c2)) {
                rkPop.style.display = 'none';
                return;
            }
            var rows = table.getRows().slice(rkRange.r1, rkRange.r2 + 1);
            var cols = rkVisCols().slice(rkRange.c1, rkRange.c2 + 1);
            var sum = 0, n = 0;
            rows.forEach(function (row) {
                var d = row.getData();
                cols.forEach(function (col) {
                    var v = rkParseNum(d[col.getField()]);
                    if (v !== null) { sum += v; n++; }
                });
            });
            if (!n) { rkPop.style.display = 'none'; return; }
            var fmt = function (x) { return x.toLocaleString('id-ID', { maximumFractionDigits: 2 }); };
            rkPop.innerHTML = 'Jumlah: <b>' + fmt(sum) + '</b>' +
                (n > 1 ? ' &nbsp;·&nbsp; Data angka: <b>' + n + '</b>' : '');
            rkPop.style.display = 'block';

            // Tempel dekat sel kanan-bawah blok; bila selnya di luar layar pakai posisi mouse.
            var x = mx || 0, y = my || 0;
            var lastRow = table.getRows()[rkRange.r2];
            var lastCell = lastRow && lastRow.getCell(rkVisCols()[rkRange.c2]);
            var ce = lastCell && lastCell.getElement();
            if (ce && ce.getBoundingClientRect) {
                var rc = ce.getBoundingClientRect();
                if (rc.width || rc.height) { x = rc.right + 8; y = rc.bottom + 8; }
            }
            var pr = rkPop.getBoundingClientRect();
            x = Math.min(Math.max(8, x), window.innerWidth  - pr.width  - 8);
            y = Math.min(Math.max(8, y), window.innerHeight - pr.height - 8);
            rkPop.style.left = x + 'px';
            rkPop.style.top  = y + 'px';
        }

        table.on('cellMouseDown', function (e, cell) {
            if (e.button !== 0) return;
            var p = rkCellPos(cell);
            if (!p) return;
            rkPop.style.display = 'none';
            if (e.shiftKey && rkAnchor) {          // Shift+klik: perluas blok dari anchor
                rkSetRange(rkAnchor, p);
                rkShowPop(e.clientX, e.clientY);
                e.preventDefault();
                return;
            }
            rkAnchor = p;
            rkDragging = true;
            el.classList.add('rk-noselect');
            rkSetActive(cell);
            rkSetRange(p, p);
            e.preventDefault();
        });
        table.on('cellMouseEnter', function (e, cell) {
            if (!rkDragging || !rkAnchor) return;
            var p = rkCellPos(cell);
            if (p) rkSetRange(rkAnchor, p);
        });
        document.addEventListener('mouseup', function (e) {
            if (!rkDragging) return;
            rkDragging = false;
            el.classList.remove('rk-noselect');
            if (rkRange && rkRange.r1 === rkRange.r2 && rkRange.c1 === rkRange.c2) {
                rkClearRange();                    // klik tunggal: cukup active cell
            } else {
                rkShowPop(e.clientX, e.clientY);
            }
        });

        table.on('cellClick', function (e, cell) { rkSetActive(cell); });

        document.addEventListener('keydown', function (e) {
            if (!rkActive) return;
            var tag = (document.activeElement && document.activeElement.tagName) || '';
            if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA') return;

            if (e.key === 'Escape') { rkClearActiveEl(); rkActive = null; rkClearRange(); return; }
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].indexOf(e.key) === -1) return;

            var cell = rkGetActiveCell();
            if (!cell) return;
            e.preventDefault();
            rkClearRange();

            var row = cell.getRow();
            var cols = rkVisCols();
            var ci = cols.findIndex(function (c) { return c.getField() === rkActive.field; });
            var target = null;
            if (e.key === 'ArrowLeft'  && ci > 0)               target = row.getCell(cols[ci - 1]);
            if (e.key === 'ArrowRight' && ci < cols.length - 1) target = row.getCell(cols[ci + 1]);
            if (e.key === 'ArrowUp')   { var pr = row.getPrevRow(); if (pr) target = pr.getCell(rkActive.field); }
            if (e.key === 'ArrowDown') { var nr = row.getNextRow(); if (nr) target = nr.getCell(rkActive.field); }
            if (target) {
                rkSetActive(target);
                target.getElement().scrollIntoView({ block: 'nearest', inline: 'nearest' });
            }
        });

        function updateInfo() {
            var info = document.getElementById('rkEntriesInfo');
            if (!info || rkTotal === null) return;
            var loaded = table.getDataCount();
            if (rkTotal === 0) info.textContent = 'Tidak ada data.';
            else if (loaded >= rkTotal) info.textContent = 'Semua ' + rkTotal.toLocaleString('id-ID') + ' data telah dimuat.';
            else info.textContent = loaded.toLocaleString('id-ID') + ' dari ' + rkTotal.toLocaleString('id-ID') + ' data dimuat — scroll ke bawah untuk memuat berikutnya.';
        }
        table.on('dataProcessed', updateInfo);
        table.on('renderComplete', updateInfo);

        // Hitung ulang lebar kolom SEKALI setelah data pertama masuk —
        // menyelaraskan header vs isi setelah scrollbar vertikal muncul.
        var rkRealigned = false;
        table.on('dataProcessed', function () {
            if (rkRealigned) return;
            rkRealigned = true;
            window.requestAnimationFrame(function () { table.redraw(true); });
        });
    }

    if (window.Tabulator) {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }
})();
</script>
@endpush

@endsection
