@push('styles')
    <style>
        /* ===== BANK MASUK — Tabulator ala spreadsheet ===== */
        #example2 {
            border: 1px solid #d0dce8;
            font-size: 12px;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        #example2 .tabulator-header { background: #0d3b6e; border-bottom: 2px solid #082948; }
        #example2 .tabulator-header .tabulator-col {
            background: #0d3b6e !important; color: #fff !important; font-weight: 600; font-size: 11.5px;
            border-right: 1px solid #ffffff !important;
        }
        #example2 .tabulator-header .tabulator-col .tabulator-col-title {
            color: #fff; text-align: center; white-space: normal;
        }
        /* Pegangan tarik-lebar kolom (garis putih di batas header) */
        #example2 .tabulator-header .tabulator-col .tabulator-col-resize-handle { width: 6px; cursor: col-resize; }
        #example2 .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
            background: linear-gradient(to bottom, transparent 32%, rgba(255,255,255,.45) 32%, rgba(255,255,255,.45) 68%, transparent 68%)
                center / 2px 100% no-repeat;
        }
        #example2 .tabulator-header .tabulator-col-resize-handle:hover { background: rgba(255,255,255,.35); }
        /* Sediakan tempat scrollbar sejak awal agar header & isi tetap sejajar */
        #example2 .tabulator-tableholder { scrollbar-gutter: stable; }
        #example2 .tabulator-cell { border-right: 1px solid #c3d2e0; border-color: #c3d2e0; padding: 6px 8px; }
        #example2 .tabulator-row { border-bottom: 1px solid #c3d2e0; }
        #example2 .tabulator-row.tabulator-row-even { background: #fbfdff; }
        #example2 .tabulator-row:hover .tabulator-cell { background: #f0f5fb; }

        /* Sel aktif (klik) */
        #example2 .tabulator-cell.bm-active-cell {
            outline: 2px solid #1b6fd8; outline-offset: -2px; background: #e8f1fd !important;
        }
        #example2 .tabulator-row:hover .tabulator-cell.bm-active-cell { background: #e8f1fd !important; }
        /* Blok sel (drag / Shift+klik) */
        #example2 .tabulator-cell.bm-range-cell { background: #dbeafe !important; }
        #example2 .tabulator-row:hover .tabulator-cell.bm-range-cell { background: #dbeafe !important; }
        #example2.bm-noselect, #example2.bm-noselect * { user-select: none; }

        /* Sel sedang disimpan / berhasil / gagal (inline edit) */
        #example2 .tabulator-cell.cb-saving-cell { background: #fff8df !important; }
        #example2 .tabulator-cell.cb-saved-cell  { background: #e9f8ef !important; }
        #example2 .tabulator-cell.cb-error-cell  { background: #fdecec !important; box-shadow: inset 0 0 0 2px #dc3545; }

        /* Kolom teks panjang: bungkus ke bawah, tinggi baris menyesuaikan */
        #example2 .bm-wrap { white-space: normal !important; overflow-wrap: anywhere; line-height: 1.35; }
        #example2 .bm-debet { color: #0d3b6e; font-weight: 600; }
        #example2 .tabulator-cell.bm-editable { cursor: cell; }

        /* Popup kecil hasil penjumlahan / info copy */
        .bm-sum-pop {
            position: fixed; z-index: 1080; display: none;
            background: #0d3b6e; color: #fff; font-size: 12px; line-height: 1;
            padding: 8px 12px; border-radius: 6px; box-shadow: 0 4px 14px rgba(0,0,0,.28);
            pointer-events: none; white-space: nowrap;
        }
        .bm-sum-pop b { color: #ffd166; }

        #bmInfo { padding: 2px 2px 8px; }
    </style>
@endpush

<div id="bmInfo" class="small text-secondary">Memuat data...</div>
<div id="example2"></div>

@push('scripts')
    <script>
        (function () {
            'use strict';

            // ── Opsi referensi (id → nama) untuk formatter tampilan & dropdown editor ──
            var refValues = {
                sumberDana: @json($sumberDana->pluck('nama_sumber_dana', 'id_sumber_dana')),
                bankTujuan: @json($bankTujuan->pluck('nama_tujuan', 'id_bank_tujuan')),
                kategori:   @json($kategoriKriteria->pluck('nama_kriteria', 'id_kategori_kriteria')),
                jenis:      @json($jenisPembayaran->pluck('nama_jenis_pembayaran', 'id_jenis_pembayaran'))
            };
            // Nilai untuk editor list: tambah opsi "-" (kosongkan) di paling atas
            function withDash(map) {
                var out = { '-': '-' };
                Object.keys(map).forEach(function (k) { out[k] = map[k]; });
                return out;
            }
            var editorValues = {
                sumberDana: withDash(refValues.sumberDana),
                bankTujuan: withDash(refValues.bankTujuan),
                kategori:   withDash(refValues.kategori),
                jenis:      withDash(refValues.jenis)
            };

            // Kolom referensi yang boleh diedit massal (baris tercentang sekaligus)
            var BULK_FIELDS = ['id_sumber_dana', 'id_bank_tujuan', 'id_kategori_kriteria', 'id_jenis_pembayaran'];

            var bmSelected = {};   // { idString: true } — pilihan tahan re-render virtual DOM
            var bmTotal = null;

            var el = document.getElementById('example2');
            if (!el || !window.Tabulator) { return; }

            // ── Formatter ──
            function fmtRef(mapKey, fallbackField) {
                return function (cell) {
                    var v = cell.getValue();
                    if (v === null || v === undefined || v === '' || v === '-') return '-';
                    var name = refValues[mapKey][String(v)];
                    if (name) return name;
                    var fb = cell.getRow().getData()[fallbackField];
                    return fb || '-';
                };
            }
            function fmtTanggal(cell) {
                var v = cell.getValue();
                if (!v) return '-';
                return (typeof moment !== 'undefined') ? moment(v, 'YYYY-MM-DD').format('DD MMMM YYYY') : v;
            }
            function fmtRupiah(cell) {
                var v = cell.getValue();
                var raw = String(v === null || v === undefined ? '' : v).replace(/[^\d]/g, '');
                return raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '0';
            }
            function fmtText(cell) {
                var v = cell.getValue();
                return (v === null || v === undefined || v === '') ? '-' : v;
            }
            function fmtCheckbox(cell) {
                var id = cell.getRow().getData().id_bank_masuk;
                return '<input type="checkbox" class="checkbox_ids" name="ids[]" value="' + id + '"' +
                    (bmSelected[String(id)] ? ' checked' : '') + '>';
            }
            function fmtSelectAll() {
                return '<input type="checkbox" id="select_all_ids">';
            }

            // Nilai mentah untuk editor (bukan tampilan)
            var table = new Tabulator(el, {
                index: 'id_bank_masuk',
                height: '62vh',
                layout: localStorage.getItem('tabulator-cb-bank-masuk-columns') ? 'fitData' : 'fitColumns',
                persistence: { columns: ['width'] },          // lebar kolom tarikan user tersimpan (localStorage)
                persistenceID: 'cb-bank-masuk',
                movableColumns: false,
                editTriggerEvent: 'dblclick',                 // dobel-klik untuk mulai edit (klik tunggal = pilih sel)
                columnHeaderVertAlign: 'middle',
                placeholder: 'Belum ada data Bank Masuk.',
                columnDefaults: { resizable: true, headerSort: false, minWidth: 40, variableHeight: true },
                columns: [
                    { title: '', titleFormatter: fmtSelectAll, field: '_cb', width: 40, hozAlign: 'center',
                      headerSort: false, resizable: false, formatter: fmtCheckbox },
                    { title: 'No', field: 'DT_RowIndex', width: 52, hozAlign: 'center' },
                    { title: 'Agenda', field: 'agenda_tahun', width: 100, formatter: fmtText,
                      editor: 'input', cssClass: 'bm-editable' },
                    { title: 'No Bukti', field: 'no_bukti', width: 80, formatter: fmtText,
                      editor: 'input', cssClass: 'bm-editable' },
                    { title: 'Tanggal', field: 'tanggal_raw', width: 120, hozAlign: 'center', formatter: fmtTanggal,
                      editor: 'input', editorParams: { elementAttributes: { type: 'date' } }, cssClass: 'bm-editable' },
                    { title: 'Sumber Dana', field: 'id_sumber_dana', width: 220, widthGrow: 2,
                      formatter: fmtRef('sumberDana', 'sumber_dana'), cssClass: 'bm-wrap bm-editable',
                      editor: 'list', editorParams: { values: editorValues.sumberDana, autocomplete: true, listOnEmpty: true } },
                    { title: 'Bank Tujuan', field: 'id_bank_tujuan', width: 170, widthGrow: 1,
                      formatter: fmtRef('bankTujuan', 'bank_tujuan'), cssClass: 'bm-wrap bm-editable',
                      editor: 'list', editorParams: { values: editorValues.bankTujuan, autocomplete: true, listOnEmpty: true } },
                    { title: 'Kriteria', field: 'id_kategori_kriteria', width: 160, widthGrow: 1,
                      formatter: fmtRef('kategori', 'kategori_kriteria'), cssClass: 'bm-wrap bm-editable',
                      editor: 'list', editorParams: { values: editorValues.kategori, autocomplete: true, listOnEmpty: true } },
                    { title: 'Penerima', field: 'penerima', width: 180, widthGrow: 1, formatter: fmtText,
                      cssClass: 'bm-wrap bm-editable', editor: 'input' },
                    { title: 'Uraian', field: 'uraian', width: 320, widthGrow: 3, formatter: fmtText,
                      cssClass: 'bm-wrap bm-editable', editor: 'textarea', tooltip: true },
                    { title: 'Jenis', field: 'id_jenis_pembayaran', width: 110, formatter: fmtRef('jenis', 'jenis_pembayaran'),
                      cssClass: 'bm-editable', editor: 'list',
                      editorParams: { values: editorValues.jenis, autocomplete: true, listOnEmpty: true } },
                    { title: 'Debet', field: 'debet_raw', width: 130, hozAlign: 'right', formatter: fmtRupiah,
                      cssClass: 'bm-debet bm-editable', editor: 'number' },
                    { title: 'Keterangan', field: 'keterangan', width: 220, widthGrow: 1, formatter: fmtText,
                      cssClass: 'bm-wrap bm-editable', editor: 'textarea' }
                ],

                rowFormatter: function (row) {
                    // Virtual DOM membuat ulang baris saat scroll — pasang kembali
                    // highlight sel aktif & blok, serta status centang.
                    var d = row.getData();
                    var cb = row.getElement().querySelector('input.checkbox_ids');
                    if (cb) cb.checked = !!bmSelected[String(d.id_bank_masuk)];
                    if (bmActive && String(d.id_bank_masuk) === String(bmActive.id)) {
                        var c = row.getCell(bmActive.field);
                        if (c) c.getElement().classList.add('bm-active-cell');
                    }
                    bmPaintRow(row);
                }
            });

            // ============ PILIHAN BARIS (checkbox) — untuk hapus massal ============
            function bmSelectedIds() { return Object.keys(bmSelected); }
            function syncHeaderCheckbox() {
                var total = table.getDataCount();
                var sel = bmSelectedIds().length;
                var h = document.getElementById('select_all_ids');
                if (h) h.checked = total > 0 && sel >= total;
            }
            function refreshCheckboxUI() {
                el.querySelectorAll('input.checkbox_ids').forEach(function (cb) {
                    cb.checked = !!bmSelected[cb.value];
                });
                syncHeaderCheckbox();
            }

            $(el).on('change', 'input.checkbox_ids', function () {
                if (this.checked) bmSelected[this.value] = true; else delete bmSelected[this.value];
                syncHeaderCheckbox();
            });
            $(el).on('click', '#select_all_ids', function (e) {
                e.stopPropagation();
                var check = this.checked;
                bmSelected = {};
                if (check) { table.getData().forEach(function (d) { bmSelected[String(d.id_bank_masuk)] = true; }); }
                refreshCheckboxUI();
            });
            // Jangan mulai blok sel saat mengklik area checkbox
            $(el).on('mousedown', 'input.checkbox_ids, #select_all_ids', function (e) { e.stopPropagation(); });

            // Ekspor ke halaman (dipakai bankMasuk.blade.php: hapus, refresh, select-all)
            window.bmSelectedIds = bmSelectedIds;
            window.bmClearSelection = function () { bmSelected = {}; refreshCheckboxUI(); };

            // ============ INLINE EDIT (simpan per sel; bulk utk kolom referensi) ============
            function csrf() { return $('meta[name="csrf-token"]').attr('content'); }
            function bmRowById(id) {
                return table.getRows().find(function (r) { return String(r.getData().id_bank_masuk) === String(id); }) || null;
            }
            function bmShowInfo(title, iconCls, msg) {
                if ($('#modalInfo').length) {
                    $('#modalInfoTitle').text(title);
                    $('#modalInfoIcon').attr('class', iconCls);
                    $('#modalInfoMsg').text(msg);
                    $('#modalInfo').modal('show');
                } else { alert(msg); }
            }
            function payloadFor(field, v) {
                var p = { _token: csrf(), _method: 'PUT' };
                if (field === 'debet_raw')        p.debet = (v === null || v === '' ? 0 : v);
                else if (field === 'tanggal_raw') p.tanggal = v || '';
                else                              p[field] = (v === null || v === undefined ? '' : v);
                return p;
            }

            table.on('cellEdited', function (cell) {
                var field = cell.getField();
                var d = cell.getRow().getData();
                var id = String(d.id_bank_masuk);
                var value = cell.getValue();

                var targets = [id];
                if (BULK_FIELDS.indexOf(field) !== -1) {
                    var sel = bmSelectedIds();
                    if (sel.indexOf(id) !== -1 && sel.length > 1) targets = sel;
                }

                var calls = [], touched = [];
                targets.forEach(function (tid) {
                    var trow = bmRowById(tid);
                    if (trow) {
                        if (tid !== id) {           // baris lain (bulk): samakan nilainya
                            var upd = {}; upd[field] = value; trow.update(upd);
                        }
                        var tcell = trow.getCell(field);
                        if (tcell) {
                            var ce = tcell.getElement();
                            ce.classList.remove('cb-error-cell', 'cb-saved-cell');
                            ce.classList.add('cb-saving-cell');
                            touched.push(tcell);
                        }
                    }
                    calls.push($.ajax({ url: '/bank-masuk/' + tid, type: 'POST', data: payloadFor(field, value) }));
                });

                $.when.apply($, calls).done(function () {
                    touched.forEach(function (tc) {
                        var ce = tc.getElement();
                        ce.classList.remove('cb-saving-cell', 'cb-error-cell');
                        ce.classList.add('cb-saved-cell');
                    });
                    setTimeout(function () {
                        touched.forEach(function (tc) { tc.getElement().classList.remove('cb-saved-cell'); });
                    }, 900);
                }).fail(function (xhr) {
                    touched.forEach(function (tc) {
                        var ce = tc.getElement();
                        ce.classList.remove('cb-saving-cell');
                        ce.classList.add('cb-error-cell');
                    });
                    var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) ||
                        'Gagal menyimpan perubahan sel.';
                    bmShowInfo('Gagal', 'fas fa-times-circle text-danger mr-2', msg);
                });
            });

            // ============ SEL AKTIF + BLOK SEL (ala spreadsheet) + COPY + SUM ============
            var bmActive = null;   // { id, field }
            var bmAnchor = null;   // { r, c }
            var bmRange  = null;   // { r1, c1, r2, c2 }
            var bmDrag   = false;

            var pop = document.createElement('div');
            pop.className = 'bm-sum-pop';
            document.body.appendChild(pop);

            function bmVisCols() {
                return table.getColumns().filter(function (c) { return c.isVisible() && c.getField() !== '_cb'; });
            }
            function bmCellPos(cell) {
                var p = cell.getRow().getPosition();
                var cols = bmVisCols(), ci = -1;
                for (var i = 0; i < cols.length; i++) { if (cols[i].getField() === cell.getField()) { ci = i; break; } }
                return (p === false || ci < 0) ? null : { r: p - 1, c: ci };
            }
            function bmClearActiveEl() {
                el.querySelectorAll('.tabulator-cell.bm-active-cell').forEach(function (n) { n.classList.remove('bm-active-cell'); });
            }
            function bmSetActive(cell) {
                bmClearActiveEl();
                bmActive = { id: cell.getRow().getData().id_bank_masuk, field: cell.getField() };
                cell.getElement().classList.add('bm-active-cell');
            }
            function bmGetActiveCell() {
                if (!bmActive) return null;
                var row = table.getRows().find(function (r) { return String(r.getData().id_bank_masuk) === String(bmActive.id); });
                return row ? row.getCell(bmActive.field) : null;
            }
            function bmPaintRow(row) {
                if (!bmRange) return;
                var p = row.getPosition();
                if (p === false) return;
                var r = p - 1;
                if (r < bmRange.r1 || r > bmRange.r2) return;
                var cols = bmVisCols();
                for (var c = bmRange.c1; c <= bmRange.c2 && c < cols.length; c++) {
                    var cell = row.getCell(cols[c]);
                    var ce = cell && cell.getElement();
                    if (ce && ce.classList) ce.classList.add('bm-range-cell');
                }
            }
            function bmApplyRange() {
                el.querySelectorAll('.tabulator-cell.bm-range-cell').forEach(function (n) { n.classList.remove('bm-range-cell'); });
                if (!bmRange) return;
                table.getRows().slice(bmRange.r1, bmRange.r2 + 1).forEach(bmPaintRow);
            }
            function bmSetRange(a, b) {
                bmRange = {
                    r1: Math.min(a.r, b.r), r2: Math.max(a.r, b.r),
                    c1: Math.min(a.c, b.c), c2: Math.max(a.c, b.c)
                };
                bmApplyRange();
            }
            function bmClearRange() { bmRange = null; bmApplyRange(); pop.style.display = 'none'; }

            // Angka format Indonesia: "1.500.000", "(2.500)". Teks/tanggal ditolak.
            function bmParseNum(v) {
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
            function bmPosXY(mx, my) {
                var x = mx || 0, y = my || 0;
                var lastRow = table.getRows()[bmRange.r2];
                var lastCell = lastRow && lastRow.getCell(bmVisCols()[bmRange.c2]);
                var ce = lastCell && lastCell.getElement();
                if (ce && ce.getBoundingClientRect) {
                    var rc = ce.getBoundingClientRect();
                    if (rc.width || rc.height) { x = rc.right + 8; y = rc.bottom + 8; }
                }
                var pr = pop.getBoundingClientRect();
                x = Math.min(Math.max(8, x), window.innerWidth - pr.width - 8);
                y = Math.min(Math.max(8, y), window.innerHeight - pr.height - 8);
                pop.style.left = x + 'px';
                pop.style.top = y + 'px';
            }
            function bmShowSum(mx, my) {
                if (!bmRange || (bmRange.r1 === bmRange.r2 && bmRange.c1 === bmRange.c2)) { pop.style.display = 'none'; return; }
                var rows = table.getRows().slice(bmRange.r1, bmRange.r2 + 1);
                var cols = bmVisCols().slice(bmRange.c1, bmRange.c2 + 1);
                var sum = 0, n = 0;
                rows.forEach(function (row) {
                    cols.forEach(function (col) {
                        var cell = row.getCell(col);
                        var txt = cell ? cell.getElement().innerText : '';
                        var val = bmParseNum(txt);
                        if (val !== null) { sum += val; n++; }
                    });
                });
                if (!n) { pop.style.display = 'none'; return; }
                var fmt = function (x) { return x.toLocaleString('id-ID', { maximumFractionDigits: 2 }); };
                pop.innerHTML = 'Jumlah: <b>' + fmt(sum) + '</b>' + (n > 1 ? ' &nbsp;·&nbsp; Data angka: <b>' + n + '</b>' : '');
                pop.style.display = 'block';
                bmPosXY(mx, my);
            }

            table.on('cellMouseDown', function (e, cell) {
                if (e.button !== 0) return;
                if (cell.getField() === '_cb') return;
                var tag = (e.target && e.target.tagName) || '';
                if (/INPUT|TEXTAREA|SELECT/.test(tag)) return;   // sedang mengetik di editor
                var p = bmCellPos(cell);
                if (!p) return;
                pop.style.display = 'none';
                if (e.shiftKey && bmAnchor) {          // Shift+klik: perluas blok dari anchor
                    bmSetRange(bmAnchor, p);
                    bmShowSum(e.clientX, e.clientY);
                    e.preventDefault();
                    return;
                }
                bmAnchor = p;
                bmDrag = true;
                el.classList.add('bm-noselect');
                bmSetActive(cell);
                bmSetRange(p, p);
                e.preventDefault();
            });
            table.on('cellMouseEnter', function (e, cell) {
                if (!bmDrag || !bmAnchor) return;
                if (cell.getField() === '_cb') return;
                var p = bmCellPos(cell);
                if (p) bmSetRange(bmAnchor, p);
            });
            document.addEventListener('mouseup', function (e) {
                if (!bmDrag) return;
                bmDrag = false;
                el.classList.remove('bm-noselect');
                if (bmRange && bmRange.r1 === bmRange.r2 && bmRange.c1 === bmRange.c2) bmClearRange();
                else bmShowSum(e.clientX, e.clientY);
            });
            table.on('cellClick', function (e, cell) {
                if (cell.getField() === '_cb') return;
                var tag = (e.target && e.target.tagName) || '';
                if (/INPUT|TEXTAREA|SELECT/.test(tag)) return;
                bmSetActive(cell);
            });

            // Navigasi panah + Esc + Ctrl/Cmd+C copy
            document.addEventListener('keydown', function (e) {
                // Abaikan tombol yang datang DARI dalam editor (mis. Enter untuk
                // simpan). Cek e.target — bukan activeElement — karena saat Enter
                // menyimpan, editor sudah menutup & fokus balik ke sel sehingga
                // activeElement keliru dan Enter itu akan membuka-edit lagi.
                function isEditorEl(n) {
                    var t = (n && n.tagName) || '';
                    return t === 'INPUT' || t === 'SELECT' || t === 'TEXTAREA';
                }
                if (isEditorEl(e.target) || isEditorEl(document.activeElement)) return;

                if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'C')) {
                    if (!bmRange && !bmActive) return;
                    e.preventDefault();
                    bmCopy(e);
                    return;
                }
                if (!bmActive) return;
                if (e.key === 'Escape') { bmClearActiveEl(); bmActive = null; bmClearRange(); return; }
                // Enter di sel aktif = masuk mode edit (selain dobel-klik)
                if (e.key === 'Enter' || e.key === 'F2') {
                    var editCell = bmGetActiveCell();
                    if (editCell) { e.preventDefault(); editCell.edit(true); }
                    return;
                }
                if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].indexOf(e.key) === -1) return;

                var cell = bmGetActiveCell();
                if (!cell) return;
                e.preventDefault();
                bmClearRange();

                var row = cell.getRow();
                var cols = bmVisCols();
                var ci = cols.findIndex(function (c) { return c.getField() === bmActive.field; });
                var target = null;
                if (e.key === 'ArrowLeft'  && ci > 0)               target = row.getCell(cols[ci - 1]);
                if (e.key === 'ArrowRight' && ci < cols.length - 1) target = row.getCell(cols[ci + 1]);
                if (e.key === 'ArrowUp')   { var pr = row.getPrevRow(); if (pr) target = pr.getCell(bmActive.field); }
                if (e.key === 'ArrowDown') { var nr = row.getNextRow(); if (nr) target = nr.getCell(bmActive.field); }
                if (target) {
                    bmSetActive(target);
                    target.getElement().scrollIntoView({ block: 'nearest', inline: 'nearest' });
                }
            });

            // ── Copy blok/sel aktif ke clipboard sebagai TSV (tempel rapi ke Excel) ──
            function bmCopy(e) {
                var vcols = bmVisCols(), rows, cols;
                if (bmRange) {
                    rows = table.getRows().slice(bmRange.r1, bmRange.r2 + 1);
                    cols = vcols.slice(bmRange.c1, bmRange.c2 + 1);
                } else {
                    var c = bmGetActiveCell();
                    if (!c) return;
                    rows = [c.getRow()];
                    var ci = vcols.findIndex(function (x) { return x.getField() === bmActive.field; });
                    cols = ci >= 0 ? [vcols[ci]] : [];
                }
                if (!cols.length) return;
                var tsv = rows.map(function (row) {
                    return cols.map(function (col) {
                        var cell = row.getCell(col);
                        var t = cell ? (cell.getElement().innerText || '') : '';
                        return t.replace(/\r?\n/g, ' ').replace(/\t/g, ' ').trim();
                    }).join('\t');
                }).join('\n');

                var flash = function () { bmFlashCopied(e); };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(tsv).then(flash).catch(function () { bmFallbackCopy(tsv); flash(); });
                } else { bmFallbackCopy(tsv); flash(); }
            }
            function bmFallbackCopy(text) {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); } catch (err) {}
                document.body.removeChild(ta);
            }
            function bmFlashCopied(e) {
                pop.innerHTML = '<b>✓</b> Disalin';
                pop.style.display = 'block';
                if (bmRange) { bmPosXY(e && e.clientX, e && e.clientY); }
                else {
                    var c = bmGetActiveCell();
                    var rc = c && c.getElement().getBoundingClientRect();
                    var x = rc ? rc.right + 8 : (e ? e.clientX : 20);
                    var y = rc ? rc.bottom + 8 : (e ? e.clientY : 20);
                    var pr = pop.getBoundingClientRect();
                    pop.style.left = Math.min(Math.max(8, x), window.innerWidth - pr.width - 8) + 'px';
                    pop.style.top = Math.min(Math.max(8, y), window.innerHeight - pr.height - 8) + 'px';
                }
                setTimeout(function () {
                    // kembalikan tampilan jumlah bila masih ada blok, jika tidak sembunyikan
                    if (bmRange && !(bmRange.r1 === bmRange.r2 && bmRange.c1 === bmRange.c2)) bmShowSum();
                    else pop.style.display = 'none';
                }, 900);
            }

            // ============ PEMUATAN DATA (SEKALI MUAT PENUH — virtual DOM anti-lag) ============
            var BM_URL = @json(route('bank-masuk.data'));
            function bmFetchUrl() {
                var usp = new URLSearchParams();
                usp.append('draw', 1);
                usp.append('start', 0);
                usp.append('length', 1000000);
                return BM_URL + '?' + usp.toString();
            }
            function updateInfo() {
                var info = document.getElementById('bmInfo');
                if (!info || bmTotal === null) return;
                var loaded = table.getDataCount();
                if (bmTotal === 0) info.textContent = 'Tidak ada data.';
                else info.textContent = 'Menampilkan ' + loaded.toLocaleString('id-ID') + ' data — geser kolom untuk melebarkan, klik/seret sel untuk memilih (Ctrl+C menyalin).';
            }
            function loadData() {
                var info = document.getElementById('bmInfo');
                if (info) info.textContent = 'Memuat data...';
                return fetch(bmFetchUrl(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(function (json) {
                        var rows = (json && json.data) || [];
                        bmTotal = parseInt((json && (json.recordsTotal || json.recordsFiltered)) || rows.length, 10);
                        return table.setData(rows);
                    })
                    .then(function () { updateInfo(); refreshCheckboxUI(); })
                    .catch(function () {
                        if (info) info.textContent = 'Gagal memuat data — tekan Refresh untuk mencoba lagi.';
                    });
            }
            window.bmReload = function () {
                bmClearRange();
                bmActive = null;
                return loadData();
            };

            // Selaraskan lebar kolom sekali setelah data pertama masuk (setelah scrollbar muncul)
            var realigned = false;
            table.on('dataProcessed', function () {
                if (realigned) return;
                realigned = true;
                window.requestAnimationFrame(function () { table.redraw(true); });
            });

            table.on('tableBuilt', function () { loadData(); });
        })();
    </script>
@endpush
