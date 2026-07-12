@push('styles')
    <style>
        #example2 {
            table-layout: fixed !important;
            width: 100% !important;
            min-width: 2240px;
        }
        #example2 th,
        #example2 td {
            white-space: nowrap;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: clip;
        }
        /* Ukuran berbasis data: uraian rata-rata 69 karakter, 91% <= 120 karakter, outlier dibungkus ke bawah. */
        #example2 td:nth-child(6),  /* Sumber Dana */
        #example2 th:nth-child(6),
        #example2 td:nth-child(7),  /* Bank Tujuan */
        #example2 th:nth-child(7),
        #example2 td:nth-child(9),  /* Penerima */
        #example2 th:nth-child(9),
        #example2 td:nth-child(10), /* Uraian */
        #example2 th:nth-child(10),
        #example2 td:nth-child(13), /* Keterangan */
        #example2 th:nth-child(13) {
            white-space: normal !important;
            overflow-wrap: anywhere;
            word-break: normal;
            vertical-align: top;
            line-height: 1.35;
            overflow: visible !important;
            text-overflow: clip !important;
        }
        /* Header navy */
        #example2 thead th,
        .dataTables_scrollHead thead th {
            background: #0d3b6e !important;
            color: #fff !important;
            font-size: 11.5px;
            font-weight: 600;
            padding: 9px 8px;
            border-color: #1a5276 !important;
            text-align: center;
        }

        /* ===== Spreadsheet cell (active cell + inline edit) — pola tableKeluar ===== */
        #example2 td.cb-spreadsheet-cell {
            cursor: cell;
            outline: none;
            position: relative;
        }
        #example2 td.cb-editable-cell::after {
            content: '';
            position: absolute;
            right: 4px;
            bottom: 4px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-bottom: 5px solid rgba(13, 59, 110, .35);
            opacity: 0;
        }
        #example2 td.cb-editable-cell:hover::after,
        #example2 td.cb-active-cell::after {
            opacity: 1;
        }
        #example2 td.cb-active-cell {
            box-shadow: inset 0 0 0 2px #0d6efd, inset 0 0 0 9999px rgba(13, 110, 253, .08);
            background: #eef7ff !important;
            outline: 2px solid #0d6efd;
            outline-offset: -2px;
        }
        #example2 td.cb-editing-cell {
            padding: 2px !important;
            overflow: visible;
        }
        #example2 td.cb-saving-cell {
            background: #fff8df !important;
        }
        #example2 td.cb-saved-cell {
            background: #e9f8ef !important;
        }
        #example2 td.cb-error-cell {
            background: #fdecec !important;
            box-shadow: inset 0 0 0 2px #dc3545;
        }
        #example2 .select2-container--bootstrap4 .select2-selection {
            border: 1px solid #1f8ef1;
            border-radius: 0;
            min-height: 32px;
            font-size: 12px;
        }
        .cb-inline-editor {
            width: 100%;
            min-width: 100%;
            height: 100%;
            border: 1px solid #1f8ef1;
            border-radius: 0;
            padding: 5px 7px;
            font-size: 12px;
            background: #fff;
            color: #1f2d3d;
            outline: none;
        }
        textarea.cb-inline-editor {
            min-height: 58px;
            resize: vertical;
            white-space: normal;
        }
        textarea.cb-inline-editor-wide {
            width: 100%;
            max-width: 100%;
            min-height: 72px;
            line-height: 1.35;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .14);
            white-space: normal;
            overflow-wrap: anywhere;
        }
        .cb-inline-dropdown.select2-dropdown {
            border-color: #1f8ef1;
            font-size: 12px;
            z-index: 99999;
        }
    </style>
@endpush

<table id="example2" class="table table-bordered table-hover">
    <thead>
        <tr>
            <th><input type="checkbox" id="select_all_ids"></th>
            <th>No</th>
            <th>Agenda</th>
            <th>No Bukti</th>
            <th>Tanggal</th>
            <th>Sumber Dana</th>
            <th>Bank Tujuan</th>
            <th>Kriteria</th>
            <th>Penerima</th>
            <th>Uraian</th>
            <th>Jenis</th>
            <th>Debet</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

@push('scripts')
    <script>
        $(document).ready(function () {
            // Kolom referensi yang boleh diubah massal (baris tercentang)
            var BULK_EDIT_FIELDS = [
                'id_sumber_dana',
                'id_bank_tujuan',
                'id_kategori_kriteria',
                'id_jenis_pembayaran'
            ];
            var selectedInlineIds = {};
            var inlineOptions = {
                bankTujuan: @json($bankTujuan->map(fn($row) => ['value' => (string) $row->id_bank_tujuan, 'label' => $row->nama_tujuan])->values()),
                sumberDana: @json($sumberDana->map(fn($row) => ['value' => (string) $row->id_sumber_dana, 'label' => $row->nama_sumber_dana])->values()),
                kategori: @json($kategoriKriteria->map(fn($row) => ['value' => (string) $row->id_kategori_kriteria, 'label' => $row->nama_kriteria])->values()),
                jenisPembayaran: @json($jenisPembayaran->map(fn($row) => ['value' => (string) $row->id_jenis_pembayaran, 'label' => $row->nama_jenis_pembayaran])->values())
            };
            // Opsi "-" di semua dropdown = kosongkan nilai (dokumen tanpa data itu)
            inlineOptions.kategori.unshift({ value: '-', label: '-' });
            inlineOptions.sumberDana.unshift({ value: '-', label: '-' });
            inlineOptions.bankTujuan.unshift({ value: '-', label: '-' });
            inlineOptions.jenisPembayaran.unshift({ value: '-', label: '-' });

            // Peta kolom yang bisa diedit inline (indeks kolom DataTable)
            var editableColumns = {
                2:  { field: 'agenda_tahun', type: 'text' },
                4:  { field: 'tanggal', type: 'date' },
                5:  { field: 'id_sumber_dana', type: 'select', source: 'sumberDana' },
                6:  { field: 'id_bank_tujuan', type: 'select', source: 'bankTujuan' },
                7:  { field: 'id_kategori_kriteria', type: 'select', source: 'kategori' },
                8:  { field: 'penerima', type: 'text' },
                9:  { field: 'uraian', type: 'textarea' },
                10: { field: 'id_jenis_pembayaran', type: 'select', source: 'jenisPembayaran' },
                11: { field: 'debet', type: 'currency' },
                12: { field: 'keterangan', type: 'textarea' }
            };
            var COL_AKSI = 13;

            // Tanpa pagination: data dimuat bertahap per 100 baris oleh
            // CbInfiniteTable (append saat scroll mendekati dasar tabel).
            var table = $('#example2').DataTable({
                processing: true,
                serverSide: false,
                paging: false,
                ordering: false,
                autoWidth: false,
                rowId: function (row) {
                    return row && row.id_bank_masuk ? 'bank-masuk-' + row.id_bank_masuk : null;
                },
                scrollX: true,
                scrollY: '60vh',
                scrollCollapse: true,
                dom: 'frti',
                columns: [
                    { data: 'checkbox',          width: '35px' },
                    { data: 'DT_RowIndex',       width: '45px',  orderable: false, searchable: false, title: 'No' },
                    { data: 'agenda_tahun',      width: '100px' },
                    { data: 'DT_RowIndex',       width: '72px',  orderable: false, searchable: false, title: 'No Bukti' },
                    { data: 'tanggal',           width: '110px' },
                    { data: 'sumber_dana',       width: '250px' },
                    { data: 'bank_tujuan',       width: '180px' },
                    { data: 'kategori_kriteria', width: '170px' },
                    { data: 'penerima',          width: '180px' },
                    { data: 'uraian',            width: '560px' },
                    { data: 'jenis_pembayaran',  width: '100px' },
                    { data: 'debet',             width: '130px', className: 'text-right' },
                    { data: 'keterangan',        width: '240px' },
                    { data: 'aksi',              width: '70px', orderable: false, searchable: false }
                ],
                columnDefs: [
                    {
                        targets: '_all',
                        createdCell: function (td, cellData, rowData, row, col) {
                            if (col === 0 || col === COL_AKSI) return;
                            $(td)
                                .addClass('cb-spreadsheet-cell')
                                .attr('tabindex', '0')
                                .attr('data-col-index', col);
                            if (editableColumns[col]) {
                                $(td)
                                    .addClass('cb-editable-cell')
                                    .attr('data-field', editableColumns[col].field);
                            }
                        }
                    }
                ]
            });

            CbInfiniteTable.init('#example2', {
                url: "{{ route('bank-masuk.data') }}",
                chunkSize: 100
            });

            var activeCell = null;
            var shiftSelectAnchorCell = null;

            function cellColumn($cell) {
                return parseInt($cell.attr('data-col-index'), 10);
            }

            function setActiveCell($cell, options) {
                options = options || {};
                if (!$cell || !$cell.length || $cell.hasClass('cb-editing-cell')) return;
                $('#example2 tbody td.cb-active-cell').removeClass('cb-active-cell');
                activeCell = $cell;
                activeCell.addClass('cb-active-cell');
                // preventScroll saat skipScroll: aktivasi otomatis tidak boleh
                // membuat halaman melompat ke posisi sel
                try {
                    activeCell[0].focus({ preventScroll: !!options.skipScroll });
                } catch (err) {
                    activeCell.focus();
                }
                if (!options.skipScroll && activeCell[0] && activeCell[0].scrollIntoView) {
                    activeCell[0].scrollIntoView({ block: 'nearest', inline: 'nearest' });
                }
            }

            function activateCellFromPointer(e, $cell) {
                if ($(e.target).is('input, select, textarea, button, a, label')) return;
                shiftSelectAnchorCell = $cell;
                setActiveCell($cell, { skipScroll: true });
            }

            function selectRowsBetweenCells($fromCell, $toCell) {
                var $rows = $('#example2 tbody tr');
                var start = $rows.index($fromCell.closest('tr'));
                var end = $rows.index($toCell.closest('tr'));

                if (start < 0 || end < 0) return;
                if (start > end) {
                    var tmp = start;
                    start = end;
                    end = tmp;
                }

                $rows.slice(start, end + 1).each(function () {
                    var rowData = table.row(this).data();
                    if (!rowData || !rowData.id_bank_masuk) return;
                    selectedInlineIds[String(rowData.id_bank_masuk)] = true;
                    $(this).find('.checkbox_ids').prop('checked', true);
                });
                syncSelectAllCheckbox();
            }

            function findCellByRowId(rowId, colIndex) {
                var found = $();
                table.rows({ page: 'current' }).every(function () {
                    var data = this.data();
                    if (String(data.id_bank_masuk) === String(rowId)) {
                        found = $(this.node()).children('td').eq(colIndex);
                    }
                });
                return found;
            }

            function ensureActiveCell() {
                if (activeCell && activeCell.length && !$.contains(document, activeCell[0])) {
                    activeCell = null;
                }
                // Saat halaman baru dibuka: sel pertama langsung aktif tanpa
                // harus diklik dulu (keyboard navigasi langsung jalan)
                if (!activeCell || !activeCell.length) {
                    var $pertama = $('#example2 tbody td.cb-spreadsheet-cell').first();
                    if ($pertama.length) {
                        shiftSelectAnchorCell = $pertama;
                        setActiveCell($pertama, { skipScroll: true });
                    }
                }
            }

            table.on('draw', function () {
                reapplyCheckedRows();
                ensureActiveCell();
            });

            $(document).on('change', '.checkbox_ids', function () {
                var id = String($(this).val());
                if (this.checked) {
                    selectedInlineIds[id] = true;
                } else {
                    delete selectedInlineIds[id];
                }
                syncSelectAllCheckbox();
            });

            $(document).on('click', '#select_all_ids', function () {
                var checked = this.checked;
                setTimeout(function () {
                    $('.checkbox_ids').each(function () {
                        var id = String($(this).val());
                        if (checked) {
                            selectedInlineIds[id] = true;
                        } else {
                            delete selectedInlineIds[id];
                        }
                    });
                    reapplyCheckedRows();
                }, 0);
            });

            $('#example2 tbody').on('pointerdown', 'td.cb-spreadsheet-cell', function (e) {
                if (e.pointerType === 'mouse' && e.button !== 0) return;
                activateCellFromPointer(e, $(this));
                e.stopPropagation();
            });

            $('#example2 tbody').on('click', 'td.cb-spreadsheet-cell', function (e) {
                activateCellFromPointer(e, $(this));
            });

            $('#example2 tbody').on('dblclick', 'td.cb-editable-cell', function () {
                beginInlineEdit($(this));
            });

            $('#example2 tbody').on('keydown', 'td.cb-spreadsheet-cell', function (e) {
                var $cell = $(this);
                if ($cell.hasClass('cb-editing-cell')) return;

                if (e.key === 'Enter') {
                    e.preventDefault();
                    beginInlineEdit($cell);
                    return;
                }

                var col = cellColumn($cell);
                var $row = $cell.closest('tr');
                var $target = $();

                if (e.shiftKey && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                    e.preventDefault();
                    if (!shiftSelectAnchorCell || !shiftSelectAnchorCell.length || !$.contains(document, shiftSelectAnchorCell[0])) {
                        shiftSelectAnchorCell = $cell;
                    }
                    $target = e.key === 'ArrowDown'
                        ? $row.next('tr').children('td').eq(col)
                        : $row.prev('tr').children('td').eq(col);

                    if ($target.length && $target.hasClass('cb-spreadsheet-cell')) {
                        selectRowsBetweenCells(shiftSelectAnchorCell, $target);
                        setActiveCell($target);
                    }
                    return;
                }

                if (e.key === 'ArrowRight' || e.key === 'Tab') {
                    e.preventDefault();
                    $target = $row.children('td').eq(col + 1);
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    $target = $row.children('td').eq(col - 1);
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    $target = $row.next('tr').children('td').eq(col);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    $target = $row.prev('tr').children('td').eq(col);
                }

                if ($target.length && $target.hasClass('cb-spreadsheet-cell')) {
                    shiftSelectAnchorCell = $target;
                    setActiveCell($target);
                }
            });

            function getCellRawValue(rowData, meta) {
                var value = rowData[meta.field];
                if (meta.field === 'tanggal') value = rowData.tanggal_raw || '';
                if (meta.field === 'debet') value = rowData.debet_raw || rowData.debet || 0;
                if (value === null || value === undefined || value === '') {
                    // Kolom referensi kosong tampil sebagai opsi "-" di dropdown
                    return String(meta.field).indexOf('id_') === 0 ? '-' : '';
                }
                return value;
            }

            function escapeHtml(value) {
                return $('<div>').text(value === null || value === undefined ? '' : value).html();
            }

            function formatRupiah(value) {
                var raw = String(value || '').replace(/\D/g, '');
                return raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '0';
            }

            function currencyToNumber(value) {
                return String(value || '').replace(/\D/g, '') || '0';
            }

            function optionLabel(options, value) {
                var match = (options || []).find(function (opt) {
                    return String(opt.value) === String(value);
                });
                return match ? match.label : '-';
            }

            function keepOptionInView($option) {
                var option = $option.get(0);
                var list = $('.select2-container--open .select2-results__options').get(0);
                if (!option || !list) return;

                var optionTop = option.offsetTop;
                var optionBottom = optionTop + option.offsetHeight;
                var listTop = list.scrollTop;
                var listBottom = listTop + list.clientHeight;

                if (optionTop < listTop) {
                    list.scrollTop = optionTop;
                } else if (optionBottom > listBottom) {
                    list.scrollTop = optionBottom - list.clientHeight;
                }
            }

            function highlightInlineOption($options, index) {
                var $option = $options.eq(index);
                $options.removeClass('select2-results__option--highlighted');
                $option.addClass('select2-results__option--highlighted');
                keepOptionInView($option);
            }

            function bindInlineDropdownKeyboard($select, chooseHighlighted, cancelEdit) {
                $(document).off('keydown.cbInlineSelect2Masuk').on('keydown.cbInlineSelect2Masuk', function (e) {
                    if (!$('.select2-container--open').length) return;

                    var $options = $('.select2-container--open .select2-results__option[role="option"]').not('[aria-disabled="true"]');
                    if (!$options.length) return;

                    var currentIndex = $options.index($('.select2-container--open .select2-results__option--highlighted'));

                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        var nextIndex = currentIndex;
                        if (e.key === 'ArrowDown') {
                            nextIndex = currentIndex < 0 ? 0 : Math.min(currentIndex + 1, $options.length - 1);
                        } else {
                            nextIndex = currentIndex < 0 ? $options.length - 1 : Math.max(currentIndex - 1, 0);
                        }
                        highlightInlineOption($options, nextIndex);
                        return false;
                    }

                    if (e.key === 'Enter') {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        var $highlighted = $('.select2-container--open .select2-results__option--highlighted').first();
                        if (!$highlighted.length) $highlighted = $options.first();
                        var selectedData = $highlighted.data('data');
                        var selectedValue = selectedData && selectedData.id !== undefined
                            ? selectedData.id
                            : null;

                        if (selectedValue === null) {
                            var selectedText = $.trim($highlighted.text());
                            $select.find('option').each(function () {
                                if ($.trim($(this).text()) === selectedText) {
                                    selectedValue = $(this).val();
                                    return false;
                                }
                            });
                        }

                        if (selectedValue !== null) {
                            $select.val(String(selectedValue)).trigger('change.select2');
                        }
                        chooseHighlighted();
                        return false;
                    }

                    if (e.key === 'Escape') {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        cancelEdit();
                        return false;
                    }
                });
            }

            function buildPayload(rowData, field, value) {
                var payload = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'PUT',
                    agenda_tahun: rowData.agenda_tahun || '',
                    tanggal: rowData.tanggal_raw || '',
                    id_bank_tujuan: rowData.id_bank_tujuan || '',
                    id_sumber_dana: rowData.id_sumber_dana || '',
                    id_kategori_kriteria: rowData.id_kategori_kriteria || '-',
                    id_jenis_pembayaran: rowData.id_jenis_pembayaran || '',
                    penerima: rowData.penerima || '',
                    uraian: rowData.uraian || '',
                    debet: rowData.debet_raw || currencyToNumber(rowData.debet),
                    keterangan: rowData.keterangan || ''
                };

                if (field === 'tanggal') payload.tanggal = value || '';
                else if (field === 'debet') payload.debet = currencyToNumber(value);
                else payload[field] = value || '';

                if (field === 'id_kategori_kriteria') {
                    payload.id_kategori_kriteria = value || '-';
                }

                return payload;
            }

            function buildFieldPayload(field, value) {
                var payload = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'PUT'
                };

                if (field === 'debet') payload.debet = currencyToNumber(value);
                else payload[field] = value || '';

                if (field === 'id_kategori_kriteria') {
                    payload.id_kategori_kriteria = value || '-';
                }

                return payload;
            }

            function syncSelectAllCheckbox() {
                var $boxes = $('.checkbox_ids');
                var total = $boxes.length;
                var checked = $boxes.filter(':checked').length;
                $('#select_all_ids').prop('checked', total > 0 && checked === total);
            }

            function rememberCheckedRowsFromDom() {
                $('.checkbox_ids').each(function () {
                    var id = String($(this).val());
                    if (this.checked) {
                        selectedInlineIds[id] = true;
                    } else {
                        delete selectedInlineIds[id];
                    }
                });
                syncSelectAllCheckbox();
            }

            function reapplyCheckedRows() {
                $('.checkbox_ids').each(function () {
                    this.checked = !!selectedInlineIds[String($(this).val())];
                });
                syncSelectAllCheckbox();
            }

            function selectedRowIds() {
                rememberCheckedRowsFromDom();
                return Object.keys(selectedInlineIds);
            }

            function targetIdsForInlineSave(rowData, field) {
                var selectedIds = selectedRowIds();
                var currentId = String(rowData.id_bank_masuk);
                var canBulk = BULK_EDIT_FIELDS.indexOf(field) !== -1 && selectedIds.indexOf(currentId) !== -1;

                return canBulk && selectedIds.length > 1 ? selectedIds : [currentId];
            }

            function rowAndCellById(rowId, colIndex) {
                var result = null;
                table.rows({ page: 'current' }).every(function () {
                    var data = this.data();
                    if (String(data.id_bank_masuk) === String(rowId)) {
                        result = {
                            rowData: data,
                            cell: $(this.node()).children('td').eq(colIndex)
                        };
                    }
                });
                return result;
            }

            function formatTanggalDisplay(value) {
                if (!value) return '-';
                if (typeof moment !== 'undefined') {
                    return moment(value, 'YYYY-MM-DD').format('DD MMMM YYYY');
                }

                var parts = String(value).split('-');
                return parts.length === 3 ? parts[2] + '-' + parts[1] + '-' + parts[0] : value;
            }

            function inlineDisplayValue(meta, value, payload, $cell) {
                if (meta.field === 'tanggal') return formatTanggalDisplay(payload.tanggal);
                if (meta.field === 'debet') return formatRupiah(payload.debet);
                if (meta.type === 'select') {
                    var selectedText = $cell.data('selected-label');
                    return selectedText || '-';
                }
                return value || '-';
            }

            function applyInlineSaveToRow($cell, meta, rowData, value, payload) {
                var display = inlineDisplayValue(meta, value, payload, $cell);

                rowData[meta.field] = payload[meta.field];
                if (Object.prototype.hasOwnProperty.call(payload, 'tanggal')) rowData.tanggal_raw = payload.tanggal;
                if (Object.prototype.hasOwnProperty.call(payload, 'debet')) rowData.debet_raw = payload.debet;

                if (meta.field === 'tanggal') rowData.tanggal = display;
                if (meta.field === 'debet') rowData.debet = display;
                if (meta.field === 'id_sumber_dana') rowData.sumber_dana = display;
                if (meta.field === 'id_bank_tujuan') rowData.bank_tujuan = display;
                if (meta.field === 'id_kategori_kriteria') rowData.kategori_kriteria = display;
                if (meta.field === 'id_jenis_pembayaran') rowData.jenis_pembayaran = display;
                if (meta.field === 'penerima') rowData.penerima = value;
                if (meta.field === 'uraian') rowData.uraian = value;
                if (meta.field === 'keterangan') rowData.keterangan = value;

                table.row($cell.closest('tr')).data(rowData);
                reapplyCheckedRows();

                var $newCell = findCellByRowId(rowData.id_bank_masuk, cellColumn($cell));
                setActiveCell($newCell.length ? $newCell : $cell);
                return $newCell.length ? $newCell : $cell;
            }

            function saveInlineCell($cell, meta, rowData, value) {
                var col = cellColumn($cell);
                var targetIds = targetIdsForInlineSave(rowData, meta.field);
                var selectedLabel = $cell.data('selected-label');
                var ajaxCalls = [];
                var visibleTargets = [];

                targetIds.forEach(function (id) {
                    var target = rowAndCellById(id, col);
                    var payload = targetIds.length > 1
                        ? buildFieldPayload(meta.field, value)
                        : buildPayload(rowData, meta.field, value);

                    if (target && target.cell.length) {
                        target.cell
                            .data('selected-label', selectedLabel)
                            .removeClass('cb-error-cell cb-saved-cell')
                            .addClass('cb-saving-cell');
                        visibleTargets.push({
                            id: id,
                            payload: payload,
                            rowData: target.rowData,
                            cell: target.cell
                        });
                    }

                    ajaxCalls.push($.ajax({
                        url: '/bank-masuk/' + id,
                        type: 'POST',
                        data: payload
                    }));
                });

                $.when.apply($, ajaxCalls).done(function () {
                    var updatedCells = [];
                    visibleTargets.forEach(function (target) {
                        var $updatedTargetCell = applyInlineSaveToRow(target.cell, meta, target.rowData, value, target.payload);
                        $updatedTargetCell.removeClass('cb-saving-cell cb-error-cell').addClass('cb-saved-cell');
                        updatedCells.push($updatedTargetCell);
                    });

                    var $updatedCell = findCellByRowId(rowData.id_bank_masuk, col);
                    if ($updatedCell.length) setActiveCell($updatedCell);

                    setTimeout(function () {
                        updatedCells.forEach(function ($updatedTargetCell) {
                            $updatedTargetCell.removeClass('cb-saved-cell');
                        });
                    }, 900);
                }).fail(function (xhr) {
                    visibleTargets.forEach(function (target) {
                        target.cell.removeClass('cb-saving-cell').addClass('cb-error-cell');
                    });
                    var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error))
                        ? (xhr.responseJSON.message || xhr.responseJSON.error)
                        : 'Gagal menyimpan perubahan sel.';
                    if ($('#modalInfo').length) {
                        $('#modalInfoTitle').text('Gagal');
                        $('#modalInfoIcon').attr('class', 'fas fa-times-circle text-danger mr-2');
                        $('#modalInfoMsg').text(msg);
                        $('#modalInfo').modal('show');
                    } else {
                        alert(msg);
                    }
                });
            }

            function beginInlineEdit($cell) {
                var col = cellColumn($cell);
                var meta = editableColumns[col];
                if (!meta || $cell.hasClass('cb-editing-cell')) return;

                var rowData = table.row($cell.closest('tr')).data();
                if (!rowData || !rowData.id_bank_masuk) return;

                var originalHtml = $cell.html();
                var currentValue = getCellRawValue(rowData, meta);
                $cell.addClass('cb-editing-cell').removeClass('cb-saved-cell cb-error-cell').empty();

                function finishEdit($editor, shouldSave) {
                    if (!$cell.hasClass('cb-editing-cell')) return;
                    var nextValue = $editor.val();
                    if ($editor.is('select')) {
                        $cell.data('selected-label', $.trim($editor.find('option:selected').text()));
                    }
                    if ($editor.hasClass('select2-hidden-accessible')) {
                        $(document).off('keydown.cbInlineSelect2Masuk');
                        $editor.off('select2:select select2:close');
                        $editor.select2('destroy');
                    }
                    $cell.removeClass('cb-editing-cell').html(originalHtml);
                    setActiveCell($cell);
                    if (shouldSave && String(nextValue) !== String(currentValue)) {
                        saveInlineCell($cell, meta, rowData, nextValue);
                    }
                }

                if (meta.type === 'select') {
                    var selectionMade = false;
                    var $select = $('<select class="cb-inline-editor cb-inline-select"></select>');
                    (inlineOptions[meta.source] || []).forEach(function (opt) {
                        $select.append('<option value="' + escapeHtml(opt.value) + '">' + escapeHtml(opt.label) + '</option>');
                    });
                    $cell.append($select);
                    $select.val(String(currentValue));

                    if ($.fn.select2) {
                        $select.select2({
                            theme: 'bootstrap4',
                            width: '100%',
                            dropdownParent: $('body'),
                            minimumResultsForSearch: Infinity,
                            dropdownCssClass: 'cb-inline-dropdown'
                        });
                        setTimeout(function () {
                            $select.select2('open');
                            bindInlineDropdownKeyboard(
                                $select,
                                function () {
                                    selectionMade = true;
                                    finishEdit($select, true);
                                },
                                function () {
                                    finishEdit($select, false);
                                }
                            );
                            var $options = $('.select2-container--open .select2-results__option[role="option"]').not('[aria-disabled="true"]');
                            var selectedIndex = Math.max(0, $options.index($('.select2-container--open .select2-results__option[aria-selected="true"]').first()));
                            highlightInlineOption($options, selectedIndex);
                        }, 0);
                    } else {
                        $select.focus();
                    }

                    $select.on('select2:select', function () {
                        selectionMade = true;
                        finishEdit($select, true);
                    });
                    $select.on('select2:close', function () {
                        if (!selectionMade) finishEdit($select, false);
                    });
                    $select.on('change', function () {
                        if (!$.fn.select2) finishEdit($select, true);
                    });
                    $select.on('keydown', function (e) {
                        if (e.key === 'Enter') { e.preventDefault(); finishEdit($select, true); }
                        if (e.key === 'Escape') { e.preventDefault(); finishEdit($select, false); }
                    });
                    return;
                }

                var $editor;
                if (meta.type === 'textarea') {
                    $editor = $('<textarea class="cb-inline-editor"></textarea>').val(currentValue);
                } else if (meta.field === 'penerima') {
                    $editor = $('<textarea class="cb-inline-editor cb-inline-editor-wide"></textarea>').val(currentValue);
                } else {
                    var inputType = meta.type === 'date' ? 'date' : 'text';
                    $editor = $('<input class="cb-inline-editor">').attr('type', inputType);
                    $editor.val(meta.type === 'currency' ? formatRupiah(currentValue) : currentValue);
                    if (meta.type === 'currency') {
                        $editor.on('input', function () {
                            this.value = formatRupiah(this.value);
                        });
                    }
                }

                $cell.append($editor);
                $editor.focus().select();
                $editor.on('keydown', function (e) {
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        finishEdit($editor, false);
                        return;
                    }

                    if (e.key === 'Enter') {
                        if ($editor.is('textarea') && e.shiftKey) return;
                        e.preventDefault();
                        finishEdit($editor, true);
                    }
                });
                $editor.on('blur', function () {
                    finishEdit($editor, true);
                });
            }

            ensureActiveCell();
        });
    </script>
@endpush
@include('cash_bank.modal.edit')
