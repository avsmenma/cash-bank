@push('styles')
    <style>
        #example3 {
            table-layout: fixed !important;
            width: 100% !important;
        }
        #example3 th,
        #example3 td {
            white-space: nowrap;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* Kolom yang boleh wrap teks ke bawah */
        #example3 td:nth-child(6),  /* Sumber Dana */
        #example3 th:nth-child(6),
        #example3 td:nth-child(7),  /* Bank Tujuan */
        #example3 th:nth-child(7),
        #example3 td:nth-child(11), /* Penerima */
        #example3 th:nth-child(11),
        #example3 td:nth-child(12), /* Uraian */
        #example3 th:nth-child(12),
        #example3 td:nth-child(15), /* Keterangan */
        #example3 th:nth-child(15) {
            white-space: normal !important;
            word-break: break-word;
        }
        /* Header navy */
        #example3 thead th,
        .dataTables_scrollHead thead th {
            background: #0d3b6e !important;
            color: #fff !important;
            font-size: 11.5px;
            font-weight: 600;
            padding: 9px 8px;
            border-color: #1a5276 !important;
            text-align: center;
        }
        /* Clickable header links */
        .th-filter-link {
            cursor: pointer;
            text-decoration: underline;
            text-decoration-style: dashed;
            text-underline-offset: 3px;
        }
        .th-filter-link:hover { color: #f9e79f !important; }
        .th-filter-link i { font-size: 9px; margin-left: 4px; opacity: 0.7; }

        /* Search popup — shared style */
        .col-search-popup {
            display: none;
            position: fixed;
            z-index: 9999;
            background: #fff;
            border: 2px solid #0d3b6e;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
            padding: 14px 16px;
            width: 320px;
        }
        .col-search-popup .popup-title {
            font-size: 13px; font-weight: 700; color: #0d3b6e; margin-bottom: 10px;
        }
        .col-search-popup .popup-title i { margin-right: 6px; }
        .col-search-popup .form-control-sm { font-size: 12px; height: 32px; }
        .col-search-popup .btn-cari {
            background: #0d3b6e; color: #fff; border: none; border-radius: 4px;
            font-size: 12px; font-weight: 600; padding: 6px 14px; cursor: pointer;
        }
        .col-search-popup .btn-cari:hover { background: #1a5276; }
        .col-search-popup .popup-footer {
            margin-top: 8px; display: flex; justify-content: space-between; align-items: center;
        }
        .col-search-popup .btn-reset-col {
            font-size: 11px; color: #999; cursor: pointer; text-decoration: underline;
            background: none; border: none; padding: 0;
        }
        .col-search-popup .btn-reset-col:hover { color: #c0392b; }
        .col-search-popup .btn-close-col {
            font-size: 11px; color: #999; cursor: pointer; background: none; border: none; padding: 0;
        }
        .col-search-popup .btn-close-col:hover { color: #333; }
        .th-filter-active { background: rgba(249,231,79,0.3) !important; }

        /* Active filter badges */
        .active-filters-bar {
            display: none;
            padding: 6px 0 10px;
        }
        .active-filters-bar .filter-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #e8f0f8; border: 1px solid #b8d4ea; border-radius: 4px;
            padding: 4px 10px; font-size: 11px; color: #0d3b6e; font-weight: 600;
            margin-right: 6px;
        }
        .active-filters-bar .filter-badge .remove-filter {
            cursor: pointer; color: #c0392b; font-weight: 800; font-size: 13px;
        }
        .active-filters-bar .filter-badge .remove-filter:hover { color: #e74c3c; }
        .active-filters-bar .btn-clear-all {
            font-size: 11px; color: #c0392b; cursor: pointer; text-decoration: underline;
            background: none; border: none; padding: 0; margin-left: 6px;
        }
        #example3 td.cb-spreadsheet-cell {
            cursor: cell;
            outline: none;
            position: relative;
        }
        #example3 td.cb-editable-cell::after {
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
        #example3 td.cb-editable-cell:hover::after,
        #example3 td.cb-active-cell::after {
            opacity: 1;
        }
        #example3 td.cb-active-cell {
            box-shadow: inset 0 0 0 2px #1f8ef1;
            background: #eef7ff !important;
        }
        #example3 td.cb-editing-cell {
            padding: 2px !important;
            overflow: visible;
        }
        #example3 td.cb-saving-cell {
            background: #fff8df !important;
        }
        #example3 td.cb-saved-cell {
            background: #e9f8ef !important;
        }
        #example3 td.cb-error-cell {
            background: #fdecec !important;
            box-shadow: inset 0 0 0 2px #dc3545;
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
        #example3 .select2-container--bootstrap4 .select2-selection {
            border: 1px solid #1f8ef1;
            border-radius: 0;
            min-height: 32px;
            font-size: 12px;
        }
        .cb-inline-dropdown.select2-dropdown {
            border-color: #1f8ef1;
            font-size: 12px;
            z-index: 99999;
        }
    </style>
@endpush

{{-- Active filter indicators --}}
<div class="active-filters-bar" id="active-filters-bar"></div>

<table id="example3" class="table table-bordered table-hover">
    <thead>
        <tr>
            <th><input type="checkbox" id="select_all_ids"></th>
            <th>No</th>
            <th>Agenda</th>
            <th>No Bukti</th>
            <th><span class="th-filter-link" id="th-tanggal-click">Tanggal <i class="fas fa-calendar-alt"></i></span></th>
            <th><span class="th-filter-link" id="th-sumber-click">Sumber Dana <i class="fas fa-filter"></i></span></th>
            <th>Bank Tujuan</th>
            <th>Kriteria</th>
            <th>Sub Kriteria</th>
            <th>Item Sub Kriteria</th>
            <th>Jenis Pembayaran</th>
            <th>Penerima</th>
            <th><span class="th-filter-link" id="th-uraian-click">Uraian <i class="fas fa-search"></i></span></th>
            <th>Kredit</th>
            <th>Keterangan</th>
        </tr>
    </thead>
</table>

{{-- ============ POPUP: Tanggal ============ --}}
<div class="col-search-popup" id="popup-tanggal">
    <div class="popup-title"><i class="fas fa-calendar-alt"></i>Filter Tanggal</div>
    <div class="mb-2">
        <label style="font-size:11px; color:#666; font-weight:600;">Dari Tanggal</label>
        <input type="date" class="form-control form-control-sm" id="filter-tgl-dari">
    </div>
    <div class="mb-2">
        <label style="font-size:11px; color:#666; font-weight:600;">Sampai Tanggal</label>
        <input type="date" class="form-control form-control-sm" id="filter-tgl-sampai">
    </div>
    <button class="btn-cari" id="btn-cari-tanggal" style="width:100%;"><i class="fas fa-filter mr-1"></i>Terapkan</button>
    <div class="popup-footer">
        <button class="btn-reset-col" id="btn-reset-tanggal"><i class="fas fa-times mr-1"></i>Reset</button>
        <button class="btn-close-col" id="btn-close-tanggal">Tutup</button>
    </div>
</div>

{{-- ============ POPUP: Sumber Dana ============ --}}
<div class="col-search-popup" id="popup-sumber">
    <div class="popup-title"><i class="fas fa-filter"></i>Filter Sumber Dana</div>
    <select class="form-control form-control-sm" id="filter-sumber-dana">
        <option value="">-- Semua Sumber Dana --</option>
        @foreach($sumberDana as $sd)
            <option value="{{ $sd->nama_sumber_dana }}">{{ $sd->nama_sumber_dana }}</option>
        @endforeach
    </select>
    <div style="margin-top:10px;">
        <button class="btn-cari" id="btn-cari-sumber" style="width:100%;"><i class="fas fa-filter mr-1"></i>Terapkan</button>
    </div>
    <div class="popup-footer">
        <button class="btn-reset-col" id="btn-reset-sumber"><i class="fas fa-times mr-1"></i>Reset</button>
        <button class="btn-close-col" id="btn-close-sumber">Tutup</button>
    </div>
</div>

{{-- ============ POPUP: Uraian ============ --}}
<div class="col-search-popup" id="popup-uraian">
    <div class="popup-title"><i class="fas fa-search"></i>Cari Uraian</div>
    <div class="input-group">
        <input type="text" class="form-control form-control-sm" id="uraian-search-input"
               placeholder="Ketik kata kunci, misal: TBS" autocomplete="off" style="border-radius:4px 0 0 4px;">
        <div class="input-group-append">
            <button class="btn-cari" id="btn-cari-uraian" type="button" style="border-radius:0 4px 4px 0;">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
    <div class="popup-footer">
        <button class="btn-reset-col" id="btn-reset-uraian"><i class="fas fa-times mr-1"></i>Reset</button>
        <button class="btn-close-col" id="btn-close-uraian">Tutup</button>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            // Column indexes (0-based in DataTable columns array)
            var COL_TANGGAL = 4;
            var COL_SUMBER  = 5;
            var COL_URAIAN  = 12;
            var inlineOptions = {
                bankTujuan: @json($bankTujuan->map(fn($row) => ['value' => (string) $row->id_bank_tujuan, 'label' => $row->nama_tujuan])->values()),
                sumberDana: @json($sumberDana->map(fn($row) => ['value' => (string) $row->id_sumber_dana, 'label' => $row->nama_sumber_dana])->values()),
                kategori: @json($kategoriKriteria->map(fn($row) => ['value' => (string) $row->id_kategori_kriteria, 'label' => $row->nama_kriteria])->values()),
                jenisPembayaran: @json($jenisPembayaran->map(fn($row) => ['value' => (string) $row->id_jenis_pembayaran, 'label' => $row->nama_jenis_pembayaran])->values())
            };
            inlineOptions.kategori.unshift({ value: '-', label: '-' });
            var editableColumns = {
                2:  { field: 'agenda_tahun', type: 'text' },
                4:  { field: 'tanggal', type: 'date' },
                5:  { field: 'id_sumber_dana', type: 'select', source: 'sumberDana' },
                6:  { field: 'id_bank_tujuan', type: 'select', source: 'bankTujuan' },
                7:  { field: 'id_kategori_kriteria', type: 'select', source: 'kategori' },
                8:  { field: 'id_sub_kriteria', type: 'select', source: 'subKriteria' },
                9:  { field: 'id_item_sub_kriteria', type: 'select', source: 'itemSubKriteria' },
                10: { field: 'id_jenis_pembayaran', type: 'select', source: 'jenisPembayaran' },
                11: { field: 'penerima', type: 'text' },
                12: { field: 'uraian', type: 'textarea' },
                13: { field: 'kredit', type: 'currency' },
                14: { field: 'keterangan', type: 'textarea' }
            };

            var table = $('#example3').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                autoWidth: false,
                scrollX: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
                ajax: {
                    url: "{{ route('bank-keluar.data') }}",
                    data: function(d) {
                        d.filter_tgl_dari   = $('#filter-tgl-dari').val() || '';
                        d.filter_tgl_sampai = $('#filter-tgl-sampai').val() || '';
                    }
                },
                columns: [
                    { data: 'checkbox',            width: '35px' },
                    { data: 'DT_RowIndex',         width: '45px',  orderable: false, searchable: false, title: 'No' },
                    { data: 'agenda_tahun',        width: '110px' },
                    { data: 'DT_RowIndex',         width: '70px',  orderable: false, searchable: false, title: 'No Bukti' },
                    { data: 'tanggal',             width: '90px' },
                    { data: 'sumber_dana',         width: '180px' },
                    { data: 'bank_tujuan',         width: '160px' },
                    { data: 'kategori_kriteria',   width: '130px' },
                    { data: 'sub_kriteria',        width: '130px' },
                    { data: 'item_sub_kriteria',   width: '130px' },
                    { data: 'jenis_pembayaran',    width: '120px' },
                    { data: 'penerima',            width: '150px' },
                    { data: 'uraian',              width: '250px' },
                    {
                        data: 'kredit',
                        width: '110px',
                        className: 'text-right',
                        render: function (data) {
                            if (data === null || data === undefined || data === '') return '0';
                            return data;
                        }
                    },
                    { data: 'keterangan',          width: '180px' }
                ],
                columnDefs: [
                    {
                        targets: '_all',
                        createdCell: function (td, cellData, rowData, row, col) {
                            if (col === 0) return;
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

            var activeCell = null;
            var restoreActive = null;

            function cellColumn($cell) {
                return parseInt($cell.attr('data-col-index'), 10);
            }

            function setActiveCell($cell) {
                if (!$cell || !$cell.length || $cell.hasClass('cb-editing-cell')) return;
                $('#example3 tbody td.cb-active-cell').removeClass('cb-active-cell');
                activeCell = $cell;
                activeCell.addClass('cb-active-cell').focus();
                if (activeCell[0] && activeCell[0].scrollIntoView) {
                    activeCell[0].scrollIntoView({ block: 'nearest', inline: 'nearest' });
                }
            }

            function findCellByRowId(rowId, colIndex) {
                var found = $();
                table.rows({ page: 'current' }).every(function () {
                    var data = this.data();
                    if (String(data.id_bank_keluar) === String(rowId)) {
                        found = $(this.node()).children('td').eq(colIndex);
                    }
                });
                return found;
            }

            function ensureActiveCell() {
                if (restoreActive) {
                    var $restored = findCellByRowId(restoreActive.id, restoreActive.col);
                    restoreActive = null;
                    if ($restored.length) {
                        setActiveCell($restored);
                        return;
                    }
                }
                if (!activeCell || !activeCell.length || !$.contains(document, activeCell[0])) {
                    setActiveCell($('#example3 tbody td.cb-editable-cell:visible').first());
                }
            }

            table.on('draw', ensureActiveCell);

            $('#example3 tbody').on('click', 'td.cb-spreadsheet-cell', function (e) {
                if ($(e.target).is('input, select, textarea, button, a')) return;
                setActiveCell($(this));
            });

            $('#example3 tbody').on('dblclick', 'td.cb-editable-cell', function () {
                beginInlineEdit($(this));
            });

            $('#example3 tbody').on('keydown', 'td.cb-spreadsheet-cell', function (e) {
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

                if ($target.length && $target.hasClass('cb-spreadsheet-cell')) setActiveCell($target);
            });

            function getCellRawValue(rowData, meta) {
                var value = rowData[meta.field];
                if (meta.field === 'tanggal') value = rowData.tanggal_raw || '';
                if (meta.field === 'kredit') value = rowData.kredit_raw || rowData.kredit || 0;
                if (value === null || value === undefined || value === '') {
                    return (meta.field === 'id_kategori_kriteria' || meta.field === 'id_sub_kriteria' || meta.field === 'id_item_sub_kriteria')
                        ? '-'
                        : '';
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

            function resolvedOptions(options) {
                var deferred = $.Deferred();
                deferred.resolve(options);
                return deferred.promise();
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
                $(document).off('keydown.cbInlineSelect2').on('keydown.cbInlineSelect2', function (e) {
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

            function getSelectOptions(meta, rowData) {
                if (meta.source === 'subKriteria') {
                    var kategori = rowData.id_kategori_kriteria || '-';
                    if (kategori === '-') return resolvedOptions([{ value: '-', label: '-' }]);
                    return $.get('/get-sub-kriteria/' + kategori).then(function (rows) {
                        var options = [{ value: '-', label: '-' }];
                        (rows || []).forEach(function (row) {
                            options.push({ value: String(row.id_sub_kriteria), label: row.nama_sub_kriteria });
                        });
                        return options;
                    });
                }

                if (meta.source === 'itemSubKriteria') {
                    var sub = rowData.id_sub_kriteria || '-';
                    if (sub === '-') return resolvedOptions([{ value: '-', label: '-' }]);
                    return $.get('/get-item-sub-kriteria/' + sub).then(function (rows) {
                        var options = [{ value: '-', label: '-' }];
                        (rows || []).forEach(function (row) {
                            options.push({ value: String(row.id_item_sub_kriteria), label: row.nama_item_sub_kriteria });
                        });
                        return options;
                    });
                }

                return resolvedOptions(inlineOptions[meta.source] || []);
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
                    id_sub_kriteria: rowData.id_sub_kriteria || '-',
                    id_item_sub_kriteria: rowData.id_item_sub_kriteria || '-',
                    id_jenis_pembayaran: rowData.id_jenis_pembayaran || '',
                    penerima: rowData.penerima || '',
                    uraian: rowData.uraian || '',
                    kredit: rowData.kredit_raw || currencyToNumber(rowData.kredit),
                    keterangan: rowData.keterangan || ''
                };

                if (field === 'tanggal') payload.tanggal = value || '';
                else if (field === 'kredit') payload.kredit = currencyToNumber(value);
                else payload[field] = value || '';

                if (field === 'id_kategori_kriteria') {
                    payload.id_kategori_kriteria = value || '-';
                    payload.id_sub_kriteria = '-';
                    payload.id_item_sub_kriteria = '-';
                }
                if (field === 'id_sub_kriteria') {
                    payload.id_sub_kriteria = value || '-';
                    payload.id_item_sub_kriteria = '-';
                }
                if (field === 'id_item_sub_kriteria') {
                    payload.id_item_sub_kriteria = value || '-';
                }
                if (field === 'id_jenis_pembayaran' && optionLabel(inlineOptions.jenisPembayaran, value) === 'MPN') {
                    payload.penerima = 'Modul Penerimaan Negara (MPN)';
                }

                return payload;
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
                if (meta.field === 'kredit') return formatRupiah(payload.kredit);
                if (meta.type === 'select') {
                    var selectedText = $cell.data('selected-label');
                    return selectedText || '-';
                }
                return value || '-';
            }

            function applyInlineSaveToRow($cell, meta, rowData, value, payload) {
                var display = inlineDisplayValue(meta, value, payload, $cell);

                rowData[meta.field] = payload[meta.field];
                rowData.tanggal_raw = payload.tanggal;
                rowData.kredit_raw = payload.kredit;

                if (meta.field === 'tanggal') rowData.tanggal = display;
                if (meta.field === 'kredit') rowData.kredit = display;
                if (meta.field === 'id_sumber_dana') rowData.sumber_dana = display;
                if (meta.field === 'id_bank_tujuan') rowData.bank_tujuan = display;
                if (meta.field === 'id_kategori_kriteria') {
                    rowData.kategori_kriteria = display;
                    rowData.id_sub_kriteria = null;
                    rowData.sub_kriteria = '-';
                    rowData.id_item_sub_kriteria = null;
                    rowData.item_sub_kriteria = '-';
                    $cell.closest('tr').children('td').eq(8).text('-');
                    $cell.closest('tr').children('td').eq(9).text('-');
                }
                if (meta.field === 'id_sub_kriteria') {
                    rowData.sub_kriteria = display;
                    rowData.id_item_sub_kriteria = null;
                    rowData.item_sub_kriteria = '-';
                    $cell.closest('tr').children('td').eq(9).text('-');
                }
                if (meta.field === 'id_item_sub_kriteria') rowData.item_sub_kriteria = display;
                if (meta.field === 'id_jenis_pembayaran') {
                    rowData.jenis_pembayaran = display;
                    if (payload.penerima !== rowData.penerima) {
                        rowData.penerima = payload.penerima;
                        $cell.closest('tr').children('td').eq(11).text(payload.penerima || '-');
                    }
                }
                if (meta.field === 'penerima') rowData.penerima = value;
                if (meta.field === 'uraian') rowData.uraian = value;
                if (meta.field === 'keterangan') rowData.keterangan = value;

                table.row($cell.closest('tr')).data(rowData);
                setActiveCell($cell.closest('tr').children('td').eq(cellColumn($cell)));
            }

            function saveInlineCell($cell, meta, rowData, value) {
                var col = cellColumn($cell);
                var payload = buildPayload(rowData, meta.field, value);
                $cell.removeClass('cb-error-cell cb-saved-cell').addClass('cb-saving-cell');

                $.ajax({
                    url: '/bank-keluar/' + rowData.id_bank_keluar,
                    type: 'POST',
                    data: payload,
                    success: function () {
                        applyInlineSaveToRow($cell, meta, rowData, value, payload);
                        var $updatedCell = findCellByRowId(rowData.id_bank_keluar, col);
                        $updatedCell.removeClass('cb-saving-cell cb-error-cell').addClass('cb-saved-cell');
                        setActiveCell($updatedCell);
                        setTimeout(function () {
                            $updatedCell.removeClass('cb-saved-cell');
                        }, 900);
                    },
                    error: function (xhr) {
                        $cell.removeClass('cb-saving-cell').addClass('cb-error-cell');
                        var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error))
                            ? (xhr.responseJSON.message || xhr.responseJSON.error)
                            : 'Gagal menyimpan perubahan sel.';
                        $('#modalInfoTitle').text('Gagal');
                        $('#modalInfoIcon').attr('class', 'fas fa-times-circle text-danger mr-2');
                        $('#modalInfoMsg').text(msg);
                        $('#modalInfo').modal('show');
                    }
                });
            }

            function beginInlineEdit($cell) {
                var col = cellColumn($cell);
                var meta = editableColumns[col];
                if (!meta || $cell.hasClass('cb-editing-cell')) return;

                var rowData = table.row($cell.closest('tr')).data();
                if (!rowData || !rowData.id_bank_keluar) return;

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
                        $(document).off('keydown.cbInlineSelect2');
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
                    var $select = $('<select class="cb-inline-editor cb-inline-select"></select>').prop('disabled', true);
                    $select.append('<option value="">Memuat...</option>');
                    $cell.append($select);
                    getSelectOptions(meta, rowData).done(function (options) {
                        $select.empty();
                        (options || []).forEach(function (opt) {
                            $select.append('<option value="' + escapeHtml(opt.value) + '">' + escapeHtml(opt.label) + '</option>');
                        });
                        $select.val(String(currentValue)).prop('disabled', false);

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
                    });
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

            // =============================================
            // POPUP HELPERS
            // =============================================
            function openPopup($popup, $trigger) {
                // close all popups first
                $('.col-search-popup').fadeOut(50);
                var offset = $trigger.offset();
                $popup.css({
                    top: offset.top + $trigger.outerHeight() + 4,
                    left: Math.min(offset.left, $(window).width() - 340)
                }).fadeIn(150);
            }

            function closeAllPopups() {
                $('.col-search-popup').fadeOut(100);
            }

            // Close popups when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.col-search-popup, .th-filter-link').length) {
                    closeAllPopups();
                }
            });
            $('.col-search-popup').on('click', function(e) { e.stopPropagation(); });

            // Track active filters
            var activeFilters = {};

            function updateFilterBar() {
                var $bar = $('#active-filters-bar');
                var html = '';
                var hasFilter = false;

                if (activeFilters.tanggal) {
                    hasFilter = true;
                    html += '<span class="filter-badge"><i class="fas fa-calendar-alt"></i> Tanggal: ' +
                            activeFilters.tanggal + ' <span class="remove-filter" data-filter="tanggal">✕</span></span>';
                }
                if (activeFilters.sumber) {
                    hasFilter = true;
                    html += '<span class="filter-badge"><i class="fas fa-university"></i> Sumber: ' +
                            activeFilters.sumber + ' <span class="remove-filter" data-filter="sumber">✕</span></span>';
                }
                if (activeFilters.uraian) {
                    hasFilter = true;
                    html += '<span class="filter-badge"><i class="fas fa-search"></i> Uraian: "' +
                            activeFilters.uraian + '" <span class="remove-filter" data-filter="uraian">✕</span></span>';
                }

                if (hasFilter) {
                    html += '<button class="btn-clear-all" id="btn-clear-all-filters"><i class="fas fa-times mr-1"></i>Hapus Semua Filter</button>';
                    $bar.html(html).slideDown(150);
                } else {
                    $bar.slideUp(150);
                }
            }

            // Remove individual filter badge
            $(document).on('click', '.remove-filter', function() {
                var filter = $(this).data('filter');
                if (filter === 'tanggal') {
                    $('#filter-tgl-dari').val('');
                    $('#filter-tgl-sampai').val('');
                    delete activeFilters.tanggal;
                    table.draw();
                } else if (filter === 'sumber') {
                    $('#filter-sumber-dana').val('');
                    delete activeFilters.sumber;
                    table.column(COL_SUMBER).search('').draw();
                } else if (filter === 'uraian') {
                    $('#uraian-search-input').val('');
                    delete activeFilters.uraian;
                    table.column(COL_URAIAN).search('').draw();
                }
                updateFilterBar();
            });

            // Clear all filters
            $(document).on('click', '#btn-clear-all-filters', function() {
                $('#filter-tgl-dari').val('');
                $('#filter-tgl-sampai').val('');
                $('#filter-sumber-dana').val('');
                $('#uraian-search-input').val('');
                activeFilters = {};
                table.column(COL_SUMBER).search('');
                table.column(COL_URAIAN).search('');
                table.draw();
                updateFilterBar();
            });

            // =============================================
            // TANGGAL FILTER
            // =============================================
            $(document).on('click', '#th-tanggal-click', function(e) {
                e.stopPropagation();
                openPopup($('#popup-tanggal'), $(this));
            });

            $('#btn-cari-tanggal').on('click', function() {
                var dari = $('#filter-tgl-dari').val();
                var sampai = $('#filter-tgl-sampai').val();
                if (dari || sampai) {
                    var label = '';
                    if (dari && sampai) label = dari + ' s/d ' + sampai;
                    else if (dari) label = 'dari ' + dari;
                    else label = 's/d ' + sampai;
                    activeFilters.tanggal = label;
                } else {
                    delete activeFilters.tanggal;
                }
                table.draw();
                updateFilterBar();
                closeAllPopups();
            });

            $('#btn-reset-tanggal').on('click', function() {
                $('#filter-tgl-dari').val('');
                $('#filter-tgl-sampai').val('');
                delete activeFilters.tanggal;
                table.draw();
                updateFilterBar();
                closeAllPopups();
            });

            $('#btn-close-tanggal').on('click', closeAllPopups);

            // =============================================
            // SUMBER DANA FILTER
            // =============================================
            $(document).on('click', '#th-sumber-click', function(e) {
                e.stopPropagation();
                openPopup($('#popup-sumber'), $(this));
            });

            $('#btn-cari-sumber').on('click', function() {
                var val = $('#filter-sumber-dana').val();
                table.column(COL_SUMBER).search(val).draw();
                if (val) {
                    activeFilters.sumber = val;
                } else {
                    delete activeFilters.sumber;
                }
                updateFilterBar();
                closeAllPopups();
            });

            // Also apply on change
            $('#filter-sumber-dana').on('change', function() {
                var val = $(this).val();
                table.column(COL_SUMBER).search(val).draw();
                if (val) {
                    activeFilters.sumber = val;
                } else {
                    delete activeFilters.sumber;
                }
                updateFilterBar();
            });

            $('#btn-reset-sumber').on('click', function() {
                $('#filter-sumber-dana').val('');
                delete activeFilters.sumber;
                table.column(COL_SUMBER).search('').draw();
                updateFilterBar();
                closeAllPopups();
            });

            $('#btn-close-sumber').on('click', closeAllPopups);

            // =============================================
            // URAIAN FILTER
            // =============================================
            $(document).on('click', '#th-uraian-click', function(e) {
                e.stopPropagation();
                openPopup($('#popup-uraian'), $(this));
                $('#uraian-search-input').focus();
            });

            $('#btn-cari-uraian').on('click', doUraianSearch);
            $('#uraian-search-input').on('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); doUraianSearch(); }
            });

            function doUraianSearch() {
                var keyword = $('#uraian-search-input').val().trim();
                table.column(COL_URAIAN).search(keyword).draw();
                if (keyword) {
                    activeFilters.uraian = keyword;
                } else {
                    delete activeFilters.uraian;
                }
                updateFilterBar();
                closeAllPopups();
            }

            $('#btn-reset-uraian').on('click', function() {
                $('#uraian-search-input').val('');
                delete activeFilters.uraian;
                table.column(COL_URAIAN).search('').draw();
                updateFilterBar();
                closeAllPopups();
            });

            $('#btn-close-uraian').on('click', closeAllPopups);
        });
    </script>
@endpush
@include('cash_bank.modal.editKeluar')
