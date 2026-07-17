@push('styles')
    <style>
        /* ===== BANK KELUAR — Tabulator ala spreadsheet ===== */
        #example3 {
            border: 1px solid #d0dce8;
            font-size: 12px;
            /* Font persis seperti aplikasi (Source Sans Pro), disetel eksplisit
               agar tema Tabulator tidak menggeser. */
            font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI",
                Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        #example3 .tabulator-cell,
        #example3 .tabulator-header .tabulator-col,
        #example3 .tabulator-header .tabulator-col-title { font-family: inherit; }
        #example3 .tabulator-header { background: #0d3b6e; border-bottom: 2px solid #082948; }
        #example3 .tabulator-header .tabulator-col {
            background: #0d3b6e !important; color: #fff !important; font-weight: 600; font-size: 11.5px;
            border-right: 1px solid #ffffff !important;
        }
        #example3 .tabulator-header .tabulator-col .tabulator-col-title {
            color: #fff; text-align: center; white-space: normal;
        }
        #example3 .tabulator-header .tabulator-col .tabulator-col-resize-handle { width: 6px; cursor: col-resize; }
        #example3 .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
            background: linear-gradient(to bottom, transparent 32%, rgba(255,255,255,.45) 32%, rgba(255,255,255,.45) 68%, transparent 68%)
                center / 2px 100% no-repeat;
        }
        #example3 .tabulator-header .tabulator-col-resize-handle:hover { background: rgba(255,255,255,.35); }
        #example3 .tabulator-tableholder { scrollbar-gutter: stable; }
        #example3 .tabulator-cell { border-right: 1px solid #c3d2e0; border-color: #c3d2e0; padding: 6px 8px; }
        #example3 .tabulator-row { border-bottom: 1px solid #c3d2e0; }
        #example3 .tabulator-row.tabulator-row-even { background: #fbfdff; }
        #example3 .tabulator-row:hover .tabulator-cell { background: #f0f5fb; }

        #example3 .tabulator-cell.bk-active-cell {
            outline: 2px solid #1b6fd8; outline-offset: -2px; background: #e8f1fd !important;
        }
        #example3 .tabulator-row:hover .tabulator-cell.bk-active-cell { background: #e8f1fd !important; }
        #example3 .tabulator-cell.bk-range-cell { background: #dbeafe !important; }
        #example3 .tabulator-row:hover .tabulator-cell.bk-range-cell { background: #dbeafe !important; }
        #example3.bk-noselect, #example3.bk-noselect * { user-select: none; }

        #example3 .tabulator-cell.cb-saving-cell { background: #fff8df !important; }
        #example3 .tabulator-cell.cb-saved-cell  { background: #e9f8ef !important; }
        #example3 .tabulator-cell.cb-error-cell  { background: #fdecec !important; box-shadow: inset 0 0 0 2px #dc3545; }

        #example3 .bk-wrap { white-space: normal !important; overflow-wrap: anywhere; line-height: 1.35; }
        #example3 .bk-kredit { color: #0d3b6e; font-weight: 600; }
        #example3 .tabulator-cell.bk-editable { cursor: cell; }

        .bk-sum-pop {
            position: fixed; z-index: 1080; display: none;
            background: #0d3b6e; color: #fff; font-size: 12px; line-height: 1;
            padding: 8px 12px; border-radius: 6px; box-shadow: 0 4px 14px rgba(0,0,0,.28);
            pointer-events: none; white-space: nowrap;
        }
        .bk-sum-pop b { color: #ffd166; }
        #bkInfo { padding: 2px 2px 8px; }

        /* Dropdown editor list: item terpilih (biru muda) vs terarah/hover (solid) */
        .tabulator-edit-list { border: 1px solid #1b6fd8 !important; border-radius: 4px;
            box-shadow: 0 6px 22px rgba(13,59,110,.20); font-size: 12px; }
        .tabulator-edit-list .tabulator-edit-list-item { padding: 7px 12px; color: #1f2d3d; }
        .tabulator-edit-list .tabulator-edit-list-item.active { background: #e8f1fd; color: #0d3b6e; font-weight: 600; }
        .tabulator-edit-list .tabulator-edit-list-item.focused,
        .tabulator-edit-list .tabulator-edit-list-item.hover,
        .tabulator-edit-list .tabulator-edit-list-item:hover {
            background: #0d6efd !important; color: #fff !important; outline: none !important;
        }

        /* ===== Filter per-kolom (popup + badge) ===== */
        .th-filter-link { cursor: pointer; text-decoration: underline; text-decoration-style: dashed; text-underline-offset: 3px; color: #fff; }
        .th-filter-link:hover { color: #f9e79f !important; }
        .th-filter-link i { font-size: 9px; margin-left: 4px; opacity: 0.75; }
        .th-filter-link.th-filter-active { color: #ffe08a !important; }
        .col-search-popup {
            display: none; position: fixed; z-index: 9999; background: #fff;
            border: 2px solid #0d3b6e; border-radius: 8px; box-shadow: 0 8px 32px rgba(0,0,0,0.25);
            padding: 14px 16px; width: 320px;
        }
        .col-search-popup .popup-title { font-size: 13px; font-weight: 700; color: #0d3b6e; margin-bottom: 10px; }
        .col-search-popup .popup-title i { margin-right: 6px; }
        .col-search-popup .form-control-sm { font-size: 12px; height: 32px; }
        .col-search-popup .btn-cari {
            background: #0d3b6e; color: #fff; border: none; border-radius: 4px;
            font-size: 12px; font-weight: 600; padding: 6px 14px; cursor: pointer;
        }
        .col-search-popup .btn-cari:hover { background: #1a5276; }
        .col-search-popup .popup-footer { margin-top: 8px; display: flex; justify-content: space-between; align-items: center; }
        .col-search-popup .btn-reset-col { font-size: 11px; color: #999; cursor: pointer; text-decoration: underline; background: none; border: none; padding: 0; }
        .col-search-popup .btn-reset-col:hover { color: #c0392b; }
        .col-search-popup .btn-close-col { font-size: 11px; color: #999; cursor: pointer; background: none; border: none; padding: 0; }
        .col-search-popup .btn-close-col:hover { color: #333; }
        .active-filters-bar { display: none; padding: 6px 0 10px; }
        .active-filters-bar .filter-badge {
            display: inline-flex; align-items: center; gap: 6px; background: #e8f0f8; border: 1px solid #b8d4ea;
            border-radius: 4px; padding: 4px 10px; font-size: 11px; color: #0d3b6e; font-weight: 600; margin-right: 6px;
        }
        .active-filters-bar .filter-badge .remove-filter { cursor: pointer; color: #c0392b; font-weight: 800; font-size: 13px; }
        .active-filters-bar .btn-clear-all { font-size: 11px; color: #c0392b; cursor: pointer; text-decoration: underline; background: none; border: none; padding: 0; margin-left: 6px; }
    </style>
@endpush

<div class="active-filters-bar" id="active-filters-bar"></div>
<div id="bkInfo" class="small text-secondary">Memuat data...</div>
<div id="example3"></div>

{{-- Popup filter generik (dipakai semua kolom) --}}
<div class="col-search-popup" id="popup-generic-filter">
    <div class="popup-title" id="generic-filter-title"><i class="fas fa-filter"></i>Filter</div>
    <div id="generic-filter-body"></div>
    <button class="btn-cari" id="btn-apply-generic-filter" style="width:100%; margin-top:10px;">
        <i class="fas fa-filter mr-1"></i>Terapkan
    </button>
    <div class="popup-footer">
        <button class="btn-reset-col" id="btn-reset-generic-filter"><i class="fas fa-times mr-1"></i>Reset</button>
        <button class="btn-close-col" id="btn-close-generic-filter">Tutup</button>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            'use strict';

            var el = document.getElementById('example3');
            if (!el || !window.Tabulator) { return; }
            el.setAttribute('tabindex', '-1');   // bisa difokuskan → event 'paste' terarah ke tabel

            // ── Opsi referensi statis (id → nama) ──
            var refValues = {
                sumberDana: @json($sumberDana->pluck('nama_sumber_dana', 'id_sumber_dana')),
                bankTujuan: @json($bankTujuan->pluck('nama_tujuan', 'id_bank_tujuan')),
                kategori:   @json($kategoriKriteria->pluck('nama_kriteria', 'id_kategori_kriteria')),
                jenis:      @json($jenisPembayaran->pluck('nama_jenis_pembayaran', 'id_jenis_pembayaran'))
            };
            // ARRAY terurut, opsi "-" di paling ATAS (object dgn key id numerik
            // akan diurutkan numerik oleh JS → "-" terlempar ke bawah).
            function withDash(map) {
                var arr = [{ label: '-', value: '-' }];
                Object.keys(map).forEach(function (k) { arr.push({ label: map[k], value: k }); });
                return arr;
            }
            var editorValues = {
                sumberDana: withDash(refValues.sumberDana),
                bankTujuan: withDash(refValues.bankTujuan),
                kategori:   withDash(refValues.kategori),
                jenis:      withDash(refValues.jenis)
            };

            // ── Peta dependensi Sub Kriteria (per Kriteria) & Item (per Sub) ──
            var subKriteriaAll = @json($subKriteria->map(fn($r) => ['id' => (string) $r->id_sub_kriteria, 'kat' => (string) $r->id_kategori_kriteria, 'nama' => trim($r->nama_sub_kriteria)])->values());
            var itemSubAll = @json($itemSubKriteria->map(fn($r) => ['id' => (string) $r->id_item_sub_kriteria, 'sub' => (string) $r->id_sub_kriteria, 'nama' => trim($r->nama_item_sub_kriteria)])->values());

            // Peta bertingkat sebagai ARRAY terurut ("-" paling atas) untuk editor list,
            // plus lookup id→nama (object) untuk formatter.
            var subsByKategori = {}, subNameById = { '-': '-' };
            subKriteriaAll.forEach(function (s) {
                subNameById[s.id] = s.nama;
                if (!subsByKategori[s.kat]) subsByKategori[s.kat] = [{ label: '-', value: '-' }];
                subsByKategori[s.kat].push({ label: s.nama, value: s.id });
            });
            var itemsBySub = {}, itemNameById = { '-': '-' }, _seenItem = {};
            itemSubAll.forEach(function (it) {
                itemNameById[it.id] = it.nama;
                if (!itemsBySub[it.sub]) itemsBySub[it.sub] = [{ label: '-', value: '-' }];
                var key = it.sub + '|' + it.nama.toLowerCase();
                if (!_seenItem[key]) { _seenItem[key] = true; itemsBySub[it.sub].push({ label: it.nama, value: it.id }); }   // dedupe nama per sub
            });

            // Opsi untuk filter select (pakai nama)
            var filterOptions = {
                sumber: @json($sumberDana->map(fn($r) => ['value' => $r->nama_sumber_dana, 'label' => $r->nama_sumber_dana])->values()),
                bank:   @json($bankTujuan->map(fn($r) => ['value' => $r->nama_tujuan, 'label' => $r->nama_tujuan])->values()),
                kategori: @json($kategoriKriteria->map(fn($r) => ['value' => $r->nama_kriteria, 'label' => $r->nama_kriteria])->values()),
                sub:    @json($subKriteria->map(fn($r) => ['value' => $r->nama_sub_kriteria, 'label' => $r->nama_sub_kriteria])->values()),
                item:   @json($itemSubKriteria->map(fn($r) => ['value' => $r->nama_item_sub_kriteria, 'label' => $r->nama_item_sub_kriteria])->unique('value')->values()),
                jenis:  @json($jenisPembayaran->map(fn($r) => ['value' => $r->nama_jenis_pembayaran, 'label' => $r->nama_jenis_pembayaran])->values())
            };

            var BULK_FIELDS = ['id_sumber_dana', 'id_bank_tujuan', 'id_kategori_kriteria',
                'id_sub_kriteria', 'id_item_sub_kriteria', 'id_jenis_pembayaran'];

            var bkSelected = {};
            var bkTotal = null;

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
            function fmtSub(cell) {
                var v = cell.getValue();
                if (v === null || v === undefined || v === '' || v === '-') return '-';
                return subNameById[String(v)] || cell.getRow().getData().sub_kriteria || '-';
            }
            function fmtItem(cell) {
                var v = cell.getValue();
                if (v === null || v === undefined || v === '' || v === '-') return '-';
                return itemNameById[String(v)] || cell.getRow().getData().item_sub_kriteria || '-';
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
                var id = cell.getRow().getData().id_bank_keluar;
                return '<input type="checkbox" class="checkbox_ids" name="ids[]" value="' + id + '"' +
                    (bkSelected[String(id)] ? ' checked' : '') + '>';
            }
            function fmtSelectAll() { return '<input type="checkbox" id="select_all_ids">'; }

            // Judul kolom dengan pemicu filter
            function titleFilter(label, key, icon) {
                return function () {
                    return '<span class="th-filter-link" data-filter-key="' + key + '">' +
                        label + ' <i class="' + (icon || 'fas fa-filter') + '"></i></span>';
                };
            }

            // Editor tanggal kustom: tampil & ketik dd-mm-yyyy, SIMPAN hanya saat
            // Enter / keluar-fokus, dan WAJIB tahun 4 digit. Nilai disimpan tetap
            // yyyy-mm-dd (cocok formatter & backend).
            function cbDateToRaw(str) {
                var s = String(str == null ? '' : str).trim();
                if (!s) return null;
                var m = s.match(/^(\d{1,2})\D+(\d{1,2})\D+(\d{4})$/);
                if (!m) {
                    var g = s.replace(/\D/g, '');
                    if (g.length === 8) m = [null, g.slice(0, 2), g.slice(2, 4), g.slice(4, 8)];
                    else return null;
                }
                var d = parseInt(m[1], 10), mo = parseInt(m[2], 10), y = parseInt(m[3], 10);
                if (!d || !mo || !y || d < 1 || d > 31 || mo < 1 || mo > 12 || y < 1000) return null;
                var dt = new Date(y, mo - 1, d);
                if (dt.getFullYear() !== y || dt.getMonth() !== mo - 1 || dt.getDate() !== d) return null;
                var p = function (n) { return (n < 10 ? '0' : '') + n; };
                return y + '-' + p(mo) + '-' + p(d);
            }
            function cbRawToDMY(raw) {
                var m = String(raw || '').match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
                if (!m) return raw ? String(raw) : '';
                var p = function (n) { return n.length < 2 ? '0' + n : n; };
                return p(m[3]) + '-' + p(m[2]) + '-' + m[1];
            }
            function cbDateEditor(cell, onRendered, success, cancel) {
                var input = document.createElement('input');
                input.type = 'text';
                input.setAttribute('inputmode', 'numeric');
                input.placeholder = 'dd-mm-yyyy';
                input.value = cbRawToDMY(cell.getValue());
                input.style.cssText = 'width:100%;box-sizing:border-box;padding:4px 6px;border:1px solid #1b6fd8;font:inherit;text-align:center;outline:none;';
                var done = false;
                function commit(fromEnter) {
                    if (done) return;
                    var val = input.value.trim();
                    if (val === '') { done = true; cancel(); return; }          // kosong → batal (nilai lama)
                    var raw = cbDateToRaw(val);
                    if (raw === null) {
                        if (fromEnter) { input.style.borderColor = '#dc3545'; input.style.background = '#fff5f5'; return; }
                        done = true; cancel(); return;                          // keluar-fokus tapi invalid → batal
                    }
                    done = true; success(raw);
                }
                onRendered(function () { input.focus(); try { input.select(); } catch (e) {} });
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') { e.preventDefault(); e.stopPropagation(); commit(true); }
                    else if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); if (!done) { done = true; cancel(); } }
                });
                input.addEventListener('blur', function () { commit(false); });
                return input;
            }

            // Editor textarea: Enter=simpan, Shift+Enter=baris baru
            function textareaEnterSave(cell, onRendered, success, cancel) {
                var val = cell.getValue();
                var ta = document.createElement('textarea');
                ta.value = (val === null || val === undefined) ? '' : val;
                ta.style.cssText = 'width:100%;box-sizing:border-box;min-height:60px;padding:4px 6px;' +
                    'border:1px solid #1b6fd8;font:inherit;line-height:1.35;resize:vertical;outline:none;';
                var finished = false;
                function done()  { if (finished) return; finished = true; success(ta.value); }
                function abort() { if (finished) return; finished = true; cancel(); }
                onRendered(function () {
                    ta.focus();
                    ta.style.height = Math.max(60, ta.scrollHeight) + 'px';
                    try { ta.setSelectionRange(ta.value.length, ta.value.length); } catch (e) {}
                });
                ta.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); e.stopPropagation(); done(); }
                    else if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); abort(); }
                });
                ta.addEventListener('blur', function () { done(); });
                return ta;
            }

            var table = new Tabulator(el, {
                index: 'id_bank_keluar',
                height: '62vh',
                layout: localStorage.getItem('tabulator-cb-bank-keluar-columns') ? 'fitData' : 'fitColumns',
                persistence: { columns: ['width'] },
                persistenceID: 'cb-bank-keluar',
                movableColumns: false,
                editTriggerEvent: 'dblclick',
                columnHeaderVertAlign: 'middle',
                placeholder: 'Belum ada data Bank Keluar.',
                columnDefaults: { resizable: true, headerSort: false, minWidth: 40, variableHeight: true },
                columns: [
                    { title: '', titleFormatter: fmtSelectAll, field: '_cb', width: 40, hozAlign: 'center', resizable: false, formatter: fmtCheckbox },
                    { title: 'No', field: 'DT_RowIndex', width: 52, hozAlign: 'center' },
                    { titleFormatter: titleFilter('Agenda', 'agenda', 'fas fa-search'), field: 'agenda_tahun', width: 105, formatter: fmtText, editor: 'input', cssClass: 'bk-editable' },
                    { title: 'No Bukti', field: 'no_bukti', width: 80, formatter: fmtText, editor: 'input', cssClass: 'bk-editable' },
                    { titleFormatter: titleFilter('Tanggal', 'tanggal', 'fas fa-calendar-alt'), field: 'tanggal_raw', width: 120, hozAlign: 'center', formatter: fmtTanggal, editor: cbDateEditor, cssClass: 'bk-editable' },
                    { titleFormatter: titleFilter('Sumber Dana', 'sumber'), field: 'id_sumber_dana', width: 220, widthGrow: 2, formatter: fmtRef('sumberDana', 'sumber_dana'), cssClass: 'bk-wrap bk-editable', editor: 'list', editorParams: { values: editorValues.sumberDana, autocomplete: true, listOnEmpty: true } },
                    { titleFormatter: titleFilter('Bank Tujuan', 'bank'), field: 'id_bank_tujuan', width: 180, widthGrow: 1, formatter: fmtRef('bankTujuan', 'bank_tujuan'), cssClass: 'bk-wrap bk-editable', editor: 'list', editorParams: { values: editorValues.bankTujuan, autocomplete: true, listOnEmpty: true } },
                    { titleFormatter: titleFilter('Kriteria', 'kategori'), field: 'id_kategori_kriteria', width: 200, widthGrow: 1, formatter: fmtRef('kategori', 'kategori_kriteria'), cssClass: 'bk-wrap bk-editable', editor: 'list', editorParams: { values: editorValues.kategori, autocomplete: true, listOnEmpty: true } },
                    { titleFormatter: titleFilter('Sub Kriteria', 'sub'), field: 'id_sub_kriteria', width: 200, widthGrow: 1, formatter: fmtSub, cssClass: 'bk-wrap bk-editable', editor: 'list',
                      editorParams: function (cell) {
                          var kat = cell.getRow().getData().id_kategori_kriteria;
                          return { values: subsByKategori[String(kat)] || [{ label: '-', value: '-' }], autocomplete: true, listOnEmpty: true };
                      } },
                    { titleFormatter: titleFilter('Item Sub Kriteria', 'item'), field: 'id_item_sub_kriteria', width: 240, widthGrow: 1, formatter: fmtItem, cssClass: 'bk-wrap bk-editable', editor: 'list',
                      editorParams: function (cell) {
                          var sub = cell.getRow().getData().id_sub_kriteria;
                          return { values: itemsBySub[String(sub)] || [{ label: '-', value: '-' }], autocomplete: true, listOnEmpty: true };
                      } },
                    { titleFormatter: titleFilter('Jenis Pembayaran', 'jenis'), field: 'id_jenis_pembayaran', width: 135, formatter: fmtRef('jenis', 'jenis_pembayaran'), cssClass: 'bk-editable', editor: 'list', editorParams: { values: editorValues.jenis, autocomplete: true, listOnEmpty: true } },
                    { titleFormatter: titleFilter('Penerima', 'penerima', 'fas fa-search'), field: 'penerima', width: 200, widthGrow: 1, formatter: fmtText, cssClass: 'bk-wrap bk-editable', editor: 'input' },
                    { titleFormatter: titleFilter('Uraian', 'uraian', 'fas fa-search'), field: 'uraian', width: 320, widthGrow: 3, formatter: fmtText, cssClass: 'bk-wrap bk-editable', editor: textareaEnterSave, tooltip: true },
                    { titleFormatter: titleFilter('Kredit', 'kredit', 'fas fa-search'), field: 'kredit_raw', width: 130, hozAlign: 'right', formatter: fmtRupiah, cssClass: 'bk-kredit bk-editable', editor: 'number' },
                    { titleFormatter: titleFilter('Keterangan', 'keterangan', 'fas fa-search'), field: 'keterangan', width: 220, widthGrow: 1, formatter: fmtText, cssClass: 'bk-wrap bk-editable', editor: textareaEnterSave }
                ],
                rowFormatter: function (row) {
                    var d = row.getData();
                    var cb = row.getElement().querySelector('input.checkbox_ids');
                    if (cb) cb.checked = !!bkSelected[String(d.id_bank_keluar)];
                    if (bkActive && String(d.id_bank_keluar) === String(bkActive.id)) {
                        var c = row.getCell(bkActive.field);
                        if (c) c.getElement().classList.add('bk-active-cell');
                    }
                    bkPaintRow(row);
                }
            });

            // ============ PILIHAN BARIS (checkbox) ============
            function bkSelectedIds() { return Object.keys(bkSelected); }
            function syncHeaderCheckbox() {
                var total = table.getDataCount();
                var h = document.getElementById('select_all_ids');
                if (h) h.checked = total > 0 && bkSelectedIds().length >= total;
            }
            function refreshCheckboxUI() {
                el.querySelectorAll('input.checkbox_ids').forEach(function (cb) { cb.checked = !!bkSelected[cb.value]; });
                syncHeaderCheckbox();
            }
            $(el).on('change', 'input.checkbox_ids', function () {
                if (this.checked) bkSelected[this.value] = true; else delete bkSelected[this.value];
                syncHeaderCheckbox();
            });
            $(el).on('click', '#select_all_ids', function (e) {
                e.stopPropagation();
                var check = this.checked;
                bkSelected = {};
                if (check) { table.getData().forEach(function (d) { bkSelected[String(d.id_bank_keluar)] = true; }); }
                refreshCheckboxUI();
            });
            $(el).on('mousedown', 'input.checkbox_ids, #select_all_ids', function (e) { e.stopPropagation(); });
            window.bkSelectedIds = bkSelectedIds;
            window.bkClearSelection = function () { bkSelected = {}; refreshCheckboxUI(); };

            // ============ INLINE EDIT ============
            function csrf() { return $('meta[name="csrf-token"]').attr('content'); }
            function bkRowById(id) { return table.getRows().find(function (r) { return String(r.getData().id_bank_keluar) === String(id); }) || null; }
            function bkShowInfo(title, iconCls, msg) {
                if ($('#modalInfo').length) {
                    $('#modalInfoTitle').text(title); $('#modalInfoIcon').attr('class', iconCls);
                    $('#modalInfoMsg').text(msg); $('#modalInfo').modal('show');
                } else { alert(msg); }
            }
            function payloadFor(field, v) {
                var p = { _token: csrf(), _method: 'PUT' };
                if (field === 'kredit_raw')       p.kredit = (v === null || v === '' ? 0 : v);
                else if (field === 'tanggal_raw') p.tanggal = v || '';
                else                              p[field] = (v === null || v === undefined ? '' : v);
                return p;
            }
            // Field tambahan yang harus ikut dikirim (cascade) saat kolom tertentu berubah
            function cascadeFor(field, value) {
                var extra = {};
                if (field === 'id_kategori_kriteria') { extra.id_sub_kriteria = '-'; extra.id_item_sub_kriteria = '-'; }
                else if (field === 'id_sub_kriteria') { extra.id_item_sub_kriteria = '-'; }
                else if (field === 'id_jenis_pembayaran' && refValues.jenis[String(value)] === 'MPN') {
                    extra.penerima = 'Modul Penerimaan Negara (MPN)';
                }
                return extra;
            }
            // Terapkan cascade ke data baris (agar tampilan langsung ikut berubah)
            function applyCascadeToRow(row, field, value) {
                if (field === 'id_kategori_kriteria') row.update({ id_sub_kriteria: '-', id_item_sub_kriteria: '-', sub_kriteria: '-', item_sub_kriteria: '-' });
                else if (field === 'id_sub_kriteria') row.update({ id_item_sub_kriteria: '-', item_sub_kriteria: '-' });
                else if (field === 'id_jenis_pembayaran' && refValues.jenis[String(value)] === 'MPN') row.update({ penerima: 'Modul Penerimaan Negara (MPN)' });
            }

            table.on('cellEdited', function (cell) {
                var editedRow = cell.getRow();
                window.requestAnimationFrame(function () { editedRow.normalizeHeight(); });

                var field = cell.getField();
                var d = editedRow.getData();
                var id = String(d.id_bank_keluar);
                var value = cell.getValue();

                var targets = [id];
                if (BULK_FIELDS.indexOf(field) !== -1) {
                    var sel = bkSelectedIds();
                    if (sel.indexOf(id) !== -1 && sel.length > 1) targets = sel;
                }

                var extra = cascadeFor(field, value);
                var calls = [], touched = [];
                targets.forEach(function (tid) {
                    var trow = bkRowById(tid);
                    if (trow) {
                        if (tid !== id) { var upd = {}; upd[field] = value; trow.update(upd); }
                        applyCascadeToRow(trow, field, value);
                        var tcell = trow.getCell(field);
                        if (tcell) {
                            var ce = tcell.getElement();
                            ce.classList.remove('cb-error-cell', 'cb-saved-cell');
                            ce.classList.add('cb-saving-cell');
                            touched.push(tcell);
                        }
                    }
                    calls.push($.ajax({ url: '/bank-keluar/' + tid, type: 'POST', data: $.extend(payloadFor(field, value), extra) }));
                });

                $.when.apply($, calls).done(function () {
                    touched.forEach(function (tc) { var ce = tc.getElement(); ce.classList.remove('cb-saving-cell', 'cb-error-cell'); ce.classList.add('cb-saved-cell'); });
                    setTimeout(function () { touched.forEach(function (tc) { tc.getElement().classList.remove('cb-saved-cell'); }); }, 900);
                }).fail(function (xhr) {
                    touched.forEach(function (tc) { var ce = tc.getElement(); ce.classList.remove('cb-saving-cell'); ce.classList.add('cb-error-cell'); });
                    var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Gagal menyimpan perubahan sel.';
                    bkShowInfo('Gagal', 'fas fa-times-circle text-danger mr-2', msg);
                });
            });

            // ============ SEL AKTIF + BLOK + COPY + SUM ============
            var bkActive = null, bkAnchor = null, bkRange = null, bkDrag = false;
            var pop = document.createElement('div');
            pop.className = 'bk-sum-pop';
            document.body.appendChild(pop);

            function bkVisCols() { return table.getColumns().filter(function (c) { return c.isVisible() && c.getField() !== '_cb'; }); }
            function bkCellPos(cell) {
                var p = cell.getRow().getPosition(), cols = bkVisCols(), ci = -1;
                for (var i = 0; i < cols.length; i++) { if (cols[i].getField() === cell.getField()) { ci = i; break; } }
                return (p === false || ci < 0) ? null : { r: p - 1, c: ci };
            }
            function bkClearActiveEl() { el.querySelectorAll('.tabulator-cell.bk-active-cell').forEach(function (n) { n.classList.remove('bk-active-cell'); }); }
            function bkSetActive(cell) { bkClearActiveEl(); bkActive = { id: cell.getRow().getData().id_bank_keluar, field: cell.getField() }; cell.getElement().classList.add('bk-active-cell'); }
            function bkGetActiveCell() {
                if (!bkActive) return null;
                var row = table.getRows().find(function (r) { return String(r.getData().id_bank_keluar) === String(bkActive.id); });
                return row ? row.getCell(bkActive.field) : null;
            }
            function bkPaintRow(row) {
                if (!bkRange) return;
                var p = row.getPosition(); if (p === false) return;
                var r = p - 1; if (r < bkRange.r1 || r > bkRange.r2) return;
                var cols = bkVisCols();
                for (var c = bkRange.c1; c <= bkRange.c2 && c < cols.length; c++) {
                    var cell = row.getCell(cols[c]); var ce = cell && cell.getElement();
                    if (ce && ce.classList) ce.classList.add('bk-range-cell');
                }
            }
            function bkApplyRange() {
                el.querySelectorAll('.tabulator-cell.bk-range-cell').forEach(function (n) { n.classList.remove('bk-range-cell'); });
                if (!bkRange) return;
                table.getRows().slice(bkRange.r1, bkRange.r2 + 1).forEach(bkPaintRow);
            }
            function bkSetRange(a, b) { bkRange = { r1: Math.min(a.r, b.r), r2: Math.max(a.r, b.r), c1: Math.min(a.c, b.c), c2: Math.max(a.c, b.c) }; bkApplyRange(); }
            function bkClearRange() { bkRange = null; bkApplyRange(); pop.style.display = 'none'; }
            function bkParseNum(v) {
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
            function bkPosXY(mx, my) {
                var x = mx || 0, y = my || 0;
                var lastRow = table.getRows()[bkRange.r2];
                var lastCell = lastRow && lastRow.getCell(bkVisCols()[bkRange.c2]);
                var ce = lastCell && lastCell.getElement();
                if (ce && ce.getBoundingClientRect) { var rc = ce.getBoundingClientRect(); if (rc.width || rc.height) { x = rc.right + 8; y = rc.bottom + 8; } }
                var pr = pop.getBoundingClientRect();
                x = Math.min(Math.max(8, x), window.innerWidth - pr.width - 8);
                y = Math.min(Math.max(8, y), window.innerHeight - pr.height - 8);
                pop.style.left = x + 'px'; pop.style.top = y + 'px';
            }
            function bkShowSum(mx, my) {
                if (!bkRange || (bkRange.r1 === bkRange.r2 && bkRange.c1 === bkRange.c2)) { pop.style.display = 'none'; return; }
                var rows = table.getRows().slice(bkRange.r1, bkRange.r2 + 1);
                var cols = bkVisCols().slice(bkRange.c1, bkRange.c2 + 1);
                var sum = 0, n = 0;
                rows.forEach(function (row) {
                    cols.forEach(function (col) {
                        var cell = row.getCell(col);
                        var val = bkParseNum(cell ? cell.getElement().innerText : '');
                        if (val !== null) { sum += val; n++; }
                    });
                });
                if (!n) { pop.style.display = 'none'; return; }
                var fmt = function (x) { return x.toLocaleString('id-ID', { maximumFractionDigits: 2 }); };
                pop.innerHTML = 'Jumlah: <b>' + fmt(sum) + '</b>' + (n > 1 ? ' &nbsp;·&nbsp; Data angka: <b>' + n + '</b>' : '');
                pop.style.display = 'block';
                bkPosXY(mx, my);
            }

            table.on('cellMouseDown', function (e, cell) {
                if (e.button !== 0) return;
                if (cell.getField() === '_cb') return;
                var tag = (e.target && e.target.tagName) || '';
                if (/INPUT|TEXTAREA|SELECT/.test(tag)) return;
                var p = bkCellPos(cell); if (!p) return;
                try { el.focus({ preventScroll: true }); } catch (err) { try { el.focus(); } catch (e2) {} }
                pop.style.display = 'none';
                if (e.shiftKey && bkAnchor) { bkSetRange(bkAnchor, p); bkShowSum(e.clientX, e.clientY); e.preventDefault(); return; }
                bkAnchor = p; bkDrag = true; el.classList.add('bk-noselect'); bkSetActive(cell); bkSetRange(p, p); e.preventDefault();
            });
            table.on('cellMouseEnter', function (e, cell) {
                if (!bkDrag || !bkAnchor) return;
                if (cell.getField() === '_cb') return;
                var p = bkCellPos(cell); if (p) bkSetRange(bkAnchor, p);
            });
            document.addEventListener('mouseup', function (e) {
                if (!bkDrag) return;
                bkDrag = false; el.classList.remove('bk-noselect');
                if (bkRange && bkRange.r1 === bkRange.r2 && bkRange.c1 === bkRange.c2) bkClearRange();
                else bkShowSum(e.clientX, e.clientY);
            });
            table.on('cellClick', function (e, cell) {
                if (cell.getField() === '_cb') return;
                var tag = (e.target && e.target.tagName) || '';
                if (/INPUT|TEXTAREA|SELECT/.test(tag)) return;
                bkSetActive(cell);
            });

            document.addEventListener('keydown', function (e) {
                function isEditorEl(n) { var t = (n && n.tagName) || ''; return t === 'INPUT' || t === 'SELECT' || t === 'TEXTAREA'; }
                if (isEditorEl(e.target) || isEditorEl(document.activeElement)) return;

                if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'C')) {
                    if (!bkRange && !bkActive) return;
                    e.preventDefault(); bkCopy(e); return;
                }
                // Delete / Backspace → kosongkan sel aktif / seluruh blok (tanpa mode edit).
                if (e.key === 'Delete' || e.key === 'Backspace') {
                    if (!bkActive && !bkRange) return;
                    if (bkClearSelection()) e.preventDefault();
                    return;
                }
                if (!bkActive) return;
                if (e.key === 'Escape') { bkClearActiveEl(); bkActive = null; bkClearRange(); return; }
                if (e.key === 'Enter' || e.key === 'F2') {
                    var editCell = bkGetActiveCell();
                    if (editCell) { e.preventDefault(); editCell.edit(true); }
                    return;
                }
                if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].indexOf(e.key) === -1) return;

                var cell = bkGetActiveCell(); if (!cell) return;
                e.preventDefault();
                var cols = bkVisCols(), rows = table.getRows(), pos = bkCellPos(cell);
                if (!pos) return;

                if (e.shiftKey) {
                    if (!bkRange || !bkAnchor) bkAnchor = pos;
                    var nr = pos.r, nc = pos.c;
                    if (e.key === 'ArrowUp') nr = Math.max(0, pos.r - 1);
                    else if (e.key === 'ArrowDown') nr = Math.min(rows.length - 1, pos.r + 1);
                    else if (e.key === 'ArrowLeft') nc = Math.max(0, pos.c - 1);
                    else if (e.key === 'ArrowRight') nc = Math.min(cols.length - 1, pos.c + 1);
                    if (nr === pos.r && nc === pos.c) return;
                    var trow = rows[nr]; if (!trow) return;
                    bkActive = { id: trow.getData().id_bank_keluar, field: cols[nc].getField() };
                    bkClearActiveEl(); bkSetRange(bkAnchor, { r: nr, c: nc });
                    var applyActive = function () {
                        var ac = bkGetActiveCell();
                        if (ac && ac.getElement()) ac.getElement().classList.add('bk-active-cell');
                        bkApplyRange(); bkShowSum();
                    };
                    var pr1 = table.scrollToRow(trow, 'nearest', false);
                    if (pr1 && pr1.then) pr1.then(applyActive).catch(applyActive); else window.requestAnimationFrame(applyActive);
                    return;
                }

                bkClearRange();
                var row = cell.getRow();
                var ci = cols.findIndex(function (c) { return c.getField() === bkActive.field; });
                var target = null;
                if (e.key === 'ArrowLeft'  && ci > 0)               target = row.getCell(cols[ci - 1]);
                if (e.key === 'ArrowRight' && ci < cols.length - 1) target = row.getCell(cols[ci + 1]);
                if (e.key === 'ArrowUp')   { var pv = row.getPrevRow(); if (pv) target = pv.getCell(bkActive.field); }
                if (e.key === 'ArrowDown') { var nx = row.getNextRow(); if (nx) target = nx.getCell(bkActive.field); }
                if (target) { bkSetActive(target); if (target.getElement()) target.getElement().scrollIntoView({ block: 'nearest', inline: 'nearest' }); }
            });

            function bkCopy(e) {
                var vcols = bkVisCols(), rows, cols;
                if (bkRange) { rows = table.getRows().slice(bkRange.r1, bkRange.r2 + 1); cols = vcols.slice(bkRange.c1, bkRange.c2 + 1); }
                else {
                    var c = bkGetActiveCell(); if (!c) return;
                    rows = [c.getRow()];
                    var ci = vcols.findIndex(function (x) { return x.getField() === bkActive.field; });
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
                var flash = function () { bkFlashCopied(e); };
                if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(tsv).then(flash).catch(function () { bkFallbackCopy(tsv); flash(); });
                else { bkFallbackCopy(tsv); flash(); }
            }
            function bkFallbackCopy(text) {
                var ta = document.createElement('textarea'); ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
                document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); } catch (err) {} document.body.removeChild(ta);
            }
            function bkFlashCopied(e) {
                pop.innerHTML = '<b>✓</b> Disalin'; pop.style.display = 'block';
                if (bkRange) bkPosXY(e && e.clientX, e && e.clientY);
                else {
                    var c = bkGetActiveCell(); var rc = c && c.getElement().getBoundingClientRect();
                    var x = rc ? rc.right + 8 : (e ? e.clientX : 20), y = rc ? rc.bottom + 8 : (e ? e.clientY : 20);
                    var pr = pop.getBoundingClientRect();
                    pop.style.left = Math.min(Math.max(8, x), window.innerWidth - pr.width - 8) + 'px';
                    pop.style.top = Math.min(Math.max(8, y), window.innerHeight - pr.height - 8) + 'px';
                }
                setTimeout(function () {
                    if (bkRange && !(bkRange.r1 === bkRange.r2 && bkRange.c1 === bkRange.c2)) bkShowSum();
                    else pop.style.display = 'none';
                }, 900);
            }

            // ============ TEMPEL (PASTE) & KOSONGKAN (DELETE/BACKSPACE) ala spreadsheet ============
            // Peta balik nama→id kolom referensi (dropdown), agar teks (nama) yang ditempel
            // dikonversi kembali ke id saat disimpan.
            var FIELD_REF = {
                id_sumber_dana: 'sumberDana',
                id_bank_tujuan: 'bankTujuan',
                id_kategori_kriteria: 'kategori',
                id_jenis_pembayaran: 'jenis'
            };
            var refReverse = {};
            Object.keys(refValues).forEach(function (mapKey) {
                var rev = {}, map = refValues[mapKey];
                Object.keys(map).forEach(function (id) { rev[String(map[id]).trim().toLowerCase()] = id; });
                refReverse[mapKey] = rev;
            });

            var ID_MONTHS = {
                januari: 1, februari: 2, maret: 3, april: 4, mei: 5, juni: 6, juli: 7, agustus: 8,
                september: 9, oktober: 10, november: 11, desember: 12,
                january: 1, february: 2, march: 3, may: 5, june: 6, july: 7, august: 8, october: 10, december: 12
            };
            function bkPad2(n) { return (n < 10 ? '0' : '') + n; }
            function bkValidYMD(y, mo, d) {
                if (!y || !mo || !d || d < 1 || d > 31 || mo < 1 || mo > 12 || y < 1000) return null;
                var dt = new Date(y, mo - 1, d);
                if (dt.getFullYear() !== y || dt.getMonth() !== mo - 1 || dt.getDate() !== d) return null;
                return y + '-' + bkPad2(mo) + '-' + bkPad2(d);
            }
            function bkParseAnyDate(text) {
                var s = String(text == null ? '' : text).trim();
                if (!s) return null;
                var iso = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
                if (iso) return bkValidYMD(+iso[1], +iso[2], +iso[3]);
                var dmy = cbDateToRaw(s);                                    // dd-mm-yyyy / ddmmyyyy
                if (dmy) return dmy;
                var nm = s.match(/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/);      // "16 Juli 2026"
                if (nm) { var mo = ID_MONTHS[nm[2].toLowerCase()]; if (mo) return bkValidYMD(+nm[3], mo, +nm[1]); }
                return null;
            }
            function bkEditable(col) { try { var d = col.getDefinition(); return !!(d && d.editor); } catch (e) { return false; } }

            // Teks clipboard → nilai tersimpan sesuai jenis kolom.
            function bkConvert(field, text) {
                var t = String(text == null ? '' : text).trim();
                if (field === 'tanggal_raw') {
                    if (t === '' || t === '-') return { skip: true };
                    var raw = bkParseAnyDate(t);
                    return raw ? { value: raw } : { skip: true };
                }
                if (field === 'kredit_raw') {
                    var digits = t.replace(/\D/g, '');
                    return { value: digits === '' ? '' : (parseInt(digits, 10) || 0) };
                }
                if (FIELD_REF[field]) {
                    if (t === '' || t === '-') return { value: '' };
                    var id = refReverse[FIELD_REF[field]][t.toLowerCase()];
                    return (id !== undefined) ? { value: id } : { skip: true };
                }
                // Kolom id_* lain (Sub Kriteria / Item Sub Kriteria) bersifat hierarkis &
                // dinamis (nilainya tergantung induk). JANGAN tempel teks mentah ke sini —
                // itu memicu "Server Error". Dilewati sampai konversi bertingkat disiapkan.
                if (field.indexOf('id_') === 0) return { skip: true };
                return { value: (t === '-' ? '' : t) };   // kolom teks biasa
            }
            function bkParseClip(text) {
                var t = String(text).replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                var lines = t.split('\n');
                while (lines.length && lines[lines.length - 1] === '') lines.pop();
                return lines.map(function (l) { return l.split('\t'); });
            }

            // Terapkan target lewat pencarian baris SEGAR (by id) agar aman dari re-render
            // di tengah proses (mis. cellEdited yang memanggil row.update untuk MPN).
            function bkApplyTargets(targets) {
                targets.forEach(function (t) {
                    var row = table.getRow(t.id);
                    if (!row) return;
                    var cell = row.getCell(t.field);
                    if (!cell) return;
                    if (t.clear) { cell.setValue(''); return; }
                    var conv = bkConvert(t.field, t.text);
                    if (conv.skip) return;
                    cell.setValue(conv.value);
                });
            }

            function bkPaste(text) {
                var matrix = bkParseClip(text);
                if (!matrix.length) return;
                var cols = bkVisCols(), rows = table.getRows();
                var block = bkRange && !(bkRange.r1 === bkRange.r2 && bkRange.c1 === bkRange.c2);
                var single = (matrix.length === 1 && matrix[0].length === 1);
                var targets = [];
                function add(row, col, text) {
                    if (!row || !col || !bkEditable(col)) return;
                    targets.push({ id: row.getData().id_bank_keluar, field: col.getField(), text: text });
                }
                if (single && block) {
                    var val = matrix[0][0];
                    for (var r = bkRange.r1; r <= bkRange.r2; r++) {
                        for (var c = bkRange.c1; c <= bkRange.c2; c++) add(rows[r], cols[c], val);
                    }
                } else {
                    var r0, c0;
                    if (block) { r0 = bkRange.r1; c0 = bkRange.c1; }
                    else { var ac = bkGetActiveCell(); var p = ac && bkCellPos(ac); if (!p) return; r0 = p.r; c0 = p.c; }
                    for (var i = 0; i < matrix.length; i++) {
                        for (var j = 0; j < matrix[i].length; j++) add(rows[r0 + i], cols[c0 + j], matrix[i][j]);
                    }
                }
                bkApplyTargets(targets);
            }

            function bkClearSelection() {
                var cols = bkVisCols(), rows = table.getRows();
                var block = bkRange && !(bkRange.r1 === bkRange.r2 && bkRange.c1 === bkRange.c2);
                var targets = [];
                function add(row, col) {
                    if (!row || !col || !bkEditable(col)) return;
                    targets.push({ id: row.getData().id_bank_keluar, field: col.getField(), clear: true });
                }
                if (block) {
                    for (var r = bkRange.r1; r <= bkRange.r2; r++) {
                        for (var c = bkRange.c1; c <= bkRange.c2; c++) add(rows[r], cols[c]);
                    }
                } else {
                    var ac = bkGetActiveCell();
                    if (ac && bkEditable(ac.getColumn())) add(ac.getRow(), ac.getColumn());
                }
                if (!targets.length) return false;
                bkApplyTargets(targets);
                return true;
            }

            document.addEventListener('paste', function (e) {
                if (/INPUT|TEXTAREA|SELECT/.test((e.target && e.target.tagName) || '') ||
                    /INPUT|TEXTAREA|SELECT/.test((document.activeElement && document.activeElement.tagName) || '')) return;
                if (!bkActive && !bkRange) return;
                var cd = e.clipboardData || window.clipboardData;
                if (!cd) return;
                var text = cd.getData('text/plain') || cd.getData('text') || '';
                if (!text) return;
                e.preventDefault();
                bkPaste(text);
            });

            // ============ PEMUATAN DATA (SEKALI MUAT PENUH) ============
            var BK_URL = @json(route('bank-keluar.data'));
            function bkFetchUrl() {
                var usp = new URLSearchParams();
                usp.append('draw', 1); usp.append('start', 0); usp.append('length', 1000000);
                return BK_URL + '?' + usp.toString();
            }
            function updateInfo() {
                var info = document.getElementById('bkInfo'); if (!info || bkTotal === null) return;
                var shown = table.getDataCount(true);
                if (bkTotal === 0) { info.textContent = 'Tidak ada data.'; return; }
                var filtered = Object.keys(activeFilters).length > 0;
                info.textContent = filtered
                    ? ('Menampilkan ' + shown.toLocaleString('id-ID') + ' dari ' + bkTotal.toLocaleString('id-ID') + ' data (terfilter).')
                    : ('Menampilkan ' + bkTotal.toLocaleString('id-ID') + ' data.');
            }
            function loadData() {
                var info = document.getElementById('bkInfo'); if (info) info.textContent = 'Memuat data...';
                return fetch(bkFetchUrl(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(function (json) {
                        var rows = (json && json.data) || [];
                        bkTotal = parseInt((json && (json.recordsTotal || json.recordsFiltered)) || rows.length, 10);
                        return table.setData(rows);
                    })
                    .then(function () { updateInfo(); refreshCheckboxUI(); })
                    .catch(function () { if (info) info.textContent = 'Gagal memuat data — tekan Refresh untuk mencoba lagi.'; });
            }
            window.bkReload = function () { bkClearRange(); bkActive = null; return loadData(); };

            var realigned = false;
            table.on('dataProcessed', function () {
                if (realigned) return; realigned = true;
                window.requestAnimationFrame(function () { table.redraw(true); });
            });
            table.on('tableBuilt', function () { loadData(); });

            // ============ FILTER PER-KOLOM (client-side Tabulator) ============
            var columnFilters = {
                agenda:    { label: 'Agenda', field: 'agenda_tahun', type: 'text', icon: 'fas fa-search' },
                tanggal:   { label: 'Tanggal', field: 'tanggal_raw', type: 'date', icon: 'fas fa-calendar-alt' },
                sumber:    { label: 'Sumber Dana', field: 'sumber_dana', type: 'select', icon: 'fas fa-filter', options: filterOptions.sumber },
                bank:      { label: 'Bank Tujuan', field: 'bank_tujuan', type: 'select', icon: 'fas fa-filter', options: filterOptions.bank },
                kategori:  { label: 'Kriteria', field: 'kategori_kriteria', type: 'select', icon: 'fas fa-filter', options: filterOptions.kategori },
                sub:       { label: 'Sub Kriteria', field: 'sub_kriteria', type: 'select', icon: 'fas fa-filter', options: filterOptions.sub },
                item:      { label: 'Item Sub Kriteria', field: 'item_sub_kriteria', type: 'select', icon: 'fas fa-filter', options: filterOptions.item },
                jenis:     { label: 'Jenis Pembayaran', field: 'jenis_pembayaran', type: 'select', icon: 'fas fa-filter', options: filterOptions.jenis },
                penerima:  { label: 'Penerima', field: 'penerima', type: 'text', icon: 'fas fa-search' },
                uraian:    { label: 'Uraian', field: 'uraian', type: 'text', icon: 'fas fa-search' },
                kredit:    { label: 'Kredit', field: 'kredit_raw', type: 'kredit', icon: 'fas fa-search' },
                keterangan:{ label: 'Keterangan', field: 'keterangan', type: 'text', icon: 'fas fa-search' }
            };
            var activeFilters = {};      // key -> { label, value } atau { label, dari, sampai }
            var currentFilterKey = null;

            function escapeHtml(v) { return $('<div>').text(v === null || v === undefined ? '' : v).html(); }

            function bkApplyClientFilters() {
                var filters = [];
                Object.keys(activeFilters).forEach(function (key) {
                    var f = activeFilters[key], cfg = columnFilters[key];
                    if (!f || !cfg) return;
                    if (cfg.type === 'date') {
                        if (f.dari)   filters.push({ field: 'tanggal_raw', type: '>=', value: f.dari });
                        if (f.sampai) filters.push({ field: 'tanggal_raw', type: '<=', value: f.sampai });
                    } else if (cfg.type === 'kredit') {
                        filters.push({ field: 'kredit_raw', type: 'like', value: String(f.value).replace(/[^\d]/g, '') });
                    } else {
                        filters.push({ field: cfg.field, type: 'like', value: f.value });
                    }
                });
                table.setFilter(filters);
                updateInfo();
            }

            function updateFilterBar() {
                var $bar = $('#active-filters-bar'), html = '', has = false;
                Object.keys(activeFilters).forEach(function (key) {
                    var f = activeFilters[key], cfg = columnFilters[key];
                    if (!f || !cfg) return;
                    has = true;
                    html += '<span class="filter-badge"><i class="' + cfg.icon + '"></i> ' + escapeHtml(cfg.label) + ': ' +
                        escapeHtml(f.label) + ' <span class="remove-filter" data-filter="' + key + '">✕</span></span>';
                });
                if (has) { html += '<button class="btn-clear-all" id="btn-clear-all-filters"><i class="fas fa-times mr-1"></i>Hapus Semua Filter</button>'; $bar.html(html).slideDown(120); }
                else $bar.slideUp(120);
                $('#example3 .th-filter-link').each(function () {
                    var k = $(this).data('filter-key');
                    $(this).toggleClass('th-filter-active', !!activeFilters[k]);
                });
            }

            function openPopup($popup, x, y) {
                $('.col-search-popup').hide();
                $popup.css({ top: y + 4, left: Math.min(x, $(window).width() - 340) }).fadeIn(120);
            }
            function closeAllPopups() { $('.col-search-popup').fadeOut(90); }

            function buildFilterControl(key) {
                var cfg = columnFilters[key], cur = activeFilters[key] || {};
                if (cfg.type === 'date') {
                    return '<div class="mb-2"><label style="font-size:11px;color:#666;font-weight:600;">Dari Tanggal</label>' +
                        '<input type="date" class="form-control form-control-sm" id="gen-date-from" value="' + escapeHtml(cur.dari || '') + '"></div>' +
                        '<div class="mb-2"><label style="font-size:11px;color:#666;font-weight:600;">Sampai Tanggal</label>' +
                        '<input type="date" class="form-control form-control-sm" id="gen-date-to" value="' + escapeHtml(cur.sampai || '') + '"></div>';
                }
                if (cfg.type === 'select') {
                    var html = '<select class="form-control form-control-sm" id="gen-filter-input"><option value="">-- Semua ' + escapeHtml(cfg.label) + ' --</option>';
                    (cfg.options || []).forEach(function (opt) {
                        html += '<option value="' + escapeHtml(opt.value) + '"' + (String(cur.value) === String(opt.value) ? ' selected' : '') + '>' + escapeHtml(opt.label) + '</option>';
                    });
                    return html + '</select>';
                }
                return '<div class="input-group"><input type="text" class="form-control form-control-sm" id="gen-filter-input" ' +
                    'placeholder="Ketik kata kunci" autocomplete="off" value="' + escapeHtml(cur.value || '') + '" style="border-radius:4px 0 0 4px;">' +
                    '<div class="input-group-append"><button class="btn-cari" id="gen-filter-icon" type="button" style="border-radius:0 4px 4px 0;"><i class="fas fa-search"></i></button></div></div>';
            }

            function openFilter(key, x, y) {
                var cfg = columnFilters[key]; if (!cfg) return;
                currentFilterKey = key;
                $('#generic-filter-title').html('<i class="' + cfg.icon + '"></i>' + (cfg.type === 'select' || cfg.type === 'date' ? 'Filter ' : 'Cari ') + escapeHtml(cfg.label));
                $('#generic-filter-body').html(buildFilterControl(key));
                openPopup($('#popup-generic-filter'), x, y);
                setTimeout(function () { $('#gen-filter-input, #gen-date-from').first().focus(); }, 60);
            }

            function applyFilter() {
                var key = currentFilterKey, cfg = columnFilters[key]; if (!cfg) return;
                if (cfg.type === 'date') {
                    var dari = $('#gen-date-from').val(), sampai = $('#gen-date-to').val();
                    if (dari || sampai) {
                        var label = dari && sampai ? dari + ' s/d ' + sampai : (dari ? 'dari ' + dari : 's/d ' + sampai);
                        activeFilters[key] = { label: label, dari: dari, sampai: sampai };
                    } else delete activeFilters[key];
                } else {
                    var value = ($('#gen-filter-input').val() || '').trim();
                    if (value) activeFilters[key] = { label: value, value: value };
                    else delete activeFilters[key];
                }
                bkApplyClientFilters(); updateFilterBar(); closeAllPopups();
            }
            function clearFilter(key) { delete activeFilters[key]; bkApplyClientFilters(); updateFilterBar(); }

            $(document).on('click', '#example3 .th-filter-link', function (e) {
                e.stopPropagation();
                var rc = this.getBoundingClientRect();
                openFilter($(this).data('filter-key'), rc.left, rc.bottom);
            });
            $('.col-search-popup').on('click', function (e) { e.stopPropagation(); });
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.col-search-popup, .th-filter-link').length) closeAllPopups();
            });
            $('#btn-apply-generic-filter').on('click', applyFilter);
            $('#generic-filter-body').on('click', '#gen-filter-icon', applyFilter);
            $('#generic-filter-body').on('keydown', '#gen-filter-input', function (e) { if (e.key === 'Enter') { e.preventDefault(); applyFilter(); } });
            $('#btn-reset-generic-filter').on('click', function () { if (currentFilterKey) clearFilter(currentFilterKey); closeAllPopups(); });
            $('#btn-close-generic-filter').on('click', closeAllPopups);
            $(document).on('click', '.remove-filter', function () { clearFilter($(this).data('filter')); });
            $(document).on('click', '#btn-clear-all-filters', function () { activeFilters = {}; bkApplyClientFilters(); updateFilterBar(); });
        })();
    </script>
@endpush
@include('cash_bank.modal.editKeluar')
