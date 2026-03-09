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
            <th>Penerima</th>
            <th><span class="th-filter-link" id="th-uraian-click">Uraian <i class="fas fa-search"></i></span></th>
            <th>Jenis Pembayaran</th>
            <th>Kredit</th>
            <th>Keterangan</th>
            <th>Aksi</th>
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
            var COL_URAIAN  = 11;

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
                    { data: 'penerima',            width: '150px' },
                    { data: 'uraian',              width: '250px' },
                    { data: 'jenis_pembayaran',    width: '120px' },
                    {
                        data: 'kredit',
                        width: '110px',
                        className: 'text-right',
                        render: function (data) {
                            if (data === null || data === undefined || data === '') return '0';
                            return data;
                        }
                    },
                    { data: 'keterangan',          width: '180px' },
                    { data: 'aksi',                width: '70px', orderable: false, searchable: false }
                ]
            });

            // Double-click pada baris → buka modal edit
            $('#example3 tbody').on('dblclick', 'tr', function() {
                var $editBtn = $(this).find('button[data-target="#editKeluar"]');
                if ($editBtn.length) $editBtn.click();
            });

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