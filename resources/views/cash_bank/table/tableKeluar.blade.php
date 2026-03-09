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
        /* Clickable header for Uraian */
        .th-uraian-link {
            cursor: pointer;
            text-decoration: underline;
            text-decoration-style: dashed;
            text-underline-offset: 3px;
        }
        .th-uraian-link:hover {
            color: #f9e79f !important;
        }
        .th-uraian-link i {
            font-size: 9px;
            margin-left: 4px;
            opacity: 0.7;
        }
        /* Search popup */
        .uraian-search-popup {
            display: none;
            position: fixed;
            z-index: 9999;
            background: #fff;
            border: 2px solid #0d3b6e;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
            padding: 16px;
            width: 340px;
        }
        .uraian-search-popup .popup-title {
            font-size: 13px;
            font-weight: 700;
            color: #0d3b6e;
            margin-bottom: 10px;
        }
        .uraian-search-popup .popup-title i {
            margin-right: 6px;
        }
        .uraian-search-popup .input-group input {
            font-size: 13px;
            border: 1px solid #ced4da;
            border-radius: 4px 0 0 4px;
            height: 34px;
        }
        .uraian-search-popup .input-group input:focus {
            border-color: #0d3b6e;
            box-shadow: 0 0 0 2px rgba(13,59,110,.15);
        }
        .uraian-search-popup .btn-cari {
            background: #0d3b6e;
            color: #fff;
            border: none;
            border-radius: 0 4px 4px 0;
            font-size: 12px;
            font-weight: 600;
            padding: 0 14px;
        }
        .uraian-search-popup .btn-cari:hover {
            background: #1a5276;
        }
        .uraian-search-popup .popup-footer {
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .uraian-search-popup .btn-reset-uraian {
            font-size: 11px;
            color: #999;
            cursor: pointer;
            text-decoration: underline;
            background: none;
            border: none;
            padding: 0;
        }
        .uraian-search-popup .btn-reset-uraian:hover {
            color: #c0392b;
        }
        .uraian-search-popup .btn-close-popup {
            font-size: 11px;
            color: #999;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }
        .uraian-search-popup .btn-close-popup:hover {
            color: #333;
        }
        .uraian-active-filter {
            background: rgba(249,231,79,0.3) !important;
        }
    </style>
@endpush

<table id="example3" class="table table-bordered table-hover">
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
            <th>Sub Kriteria</th>
            <th>Item Sub Kriteria</th>
            <th>Penerima</th>
            <th><span class="th-uraian-link" id="th-uraian-click">Uraian <i class="fas fa-search"></i></span></th>
            <th>Jenis Pembayaran</th>
            <th>Kredit</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

{{-- Popup Search Uraian --}}
<div class="uraian-search-popup" id="uraian-search-popup">
    <div class="popup-title"><i class="fas fa-search"></i>Cari Uraian</div>
    <div class="input-group">
        <input type="text" class="form-control" id="uraian-search-input" placeholder="Ketik kata kunci, misal: TBS" autocomplete="off">
        <div class="input-group-append">
            <button class="btn-cari" id="btn-cari-uraian" type="button"><i class="fas fa-search mr-1"></i>Cari</button>
        </div>
    </div>
    <div class="popup-footer">
        <button class="btn-reset-uraian" id="btn-reset-uraian"><i class="fas fa-times mr-1"></i>Reset Filter</button>
        <button class="btn-close-popup" id="btn-close-uraian-popup">Tutup</button>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            var table = $('#example3').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                autoWidth: false,
                scrollX: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
                ajax: "{{ route('bank-keluar.data') }}",
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
                if ($editBtn.length) {
                    $editBtn.click();
                }
            });

            // =============================================
            // URAIAN COLUMN SEARCH POPUP
            // =============================================
            var $popup = $('#uraian-search-popup');
            var uraianColIndex = 11; // uraian column index (0-based)

            // Open popup when clicking Uraian header
            $(document).on('click', '#th-uraian-click, .dataTables_scrollHead th:nth-child(12)', function(e) {
                e.stopPropagation();
                var offset = $(this).offset();
                var headerWidth = $(this).outerWidth();

                // Position popup below the header
                $popup.css({
                    top: offset.top + $(this).outerHeight() + 4,
                    left: Math.min(offset.left, $(window).width() - 360)
                }).fadeIn(150);

                $('#uraian-search-input').focus();
            });

            // Search on click button
            $('#btn-cari-uraian').on('click', function() {
                doUraianSearch();
            });

            // Search on Enter key
            $('#uraian-search-input').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    doUraianSearch();
                }
            });

            function doUraianSearch() {
                var keyword = $('#uraian-search-input').val().trim();
                table.column(uraianColIndex).search(keyword).draw();

                if (keyword) {
                    // Highlight the header to show filter is active
                    $('.dataTables_scrollHead th:nth-child(12)').addClass('uraian-active-filter');
                } else {
                    $('.dataTables_scrollHead th:nth-child(12)').removeClass('uraian-active-filter');
                }

                $popup.fadeOut(100);
            }

            // Reset filter
            $('#btn-reset-uraian').on('click', function() {
                $('#uraian-search-input').val('');
                table.column(uraianColIndex).search('').draw();
                $('.dataTables_scrollHead th:nth-child(12)').removeClass('uraian-active-filter');
                $popup.fadeOut(100);
            });

            // Close popup
            $('#btn-close-uraian-popup').on('click', function() {
                $popup.fadeOut(100);
            });

            // Close popup when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#uraian-search-popup, #th-uraian-click').length) {
                    $popup.fadeOut(100);
                }
            });

            // Prevent popup from closing when clicking inside it
            $popup.on('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
@endpush
@include('cash_bank.modal.editKeluar')