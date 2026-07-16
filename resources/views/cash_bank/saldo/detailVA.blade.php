@extends("layouts/index")
@section('content')
    @push('styles')
        <style>
            /* Tampilan kompak: setara zoom browser 80%, agar seluruh tabel
               terlihat dalam satu layar pada web size 100% (samakan dgn VA Dashboard) */
            .content-header,
            section.content {
                zoom: 0.8;
            }

            /* Tabulator — header & baris total navy, zebra, teks wrap */
            #tableDetailVA .tabulator-header,
            #tableDetailVA .tabulator-header .tabulator-col {
                background-color: #1E3A5F !important;
                color: #fff !important;
                border-color: rgba(255, 255, 255, 0.25) !important;
            }

            #tableDetailVA .tabulator-header .tabulator-col .tabulator-col-title {
                color: #fff;
                font-weight: 600;
                white-space: normal;
                text-align: center;
            }

            #tableDetailVA .tabulator-header .tabulator-col {
                border-right: 2px solid rgba(255, 255, 255, 0.9) !important;
            }

            #tableDetailVA .tabulator-row.tabulator-row-even {
                background-color: #F5F8FB;
            }

            #tableDetailVA .tabulator-cell {
                white-space: normal;
                overflow-wrap: break-word;
                border-right: 1px solid #C9D4DF !important;
            }

            #tableDetailVA .tabulator-row {
                border-bottom: 1px solid #C9D4DF !important;
            }

            #tableDetailVA .tabulator-calcs-holder,
            #tableDetailVA .tabulator-row.tabulator-calcs-bottom,
            #tableDetailVA .tabulator-row.tabulator-calcs-bottom .tabulator-cell {
                background-color: #1E3A5F !important;
                color: #fff !important;
                font-weight: 700;
                border-color: rgba(255, 255, 255, 0.25) !important;
            }

            #tableDetailVA .tabulator-row.tabulator-calcs-bottom .tabulator-cell {
                border-right: 2px solid rgba(255, 255, 255, 0.9) !important;
            }

            .text-debet { color: #28a745; font-weight: 600; }
            .text-kredit { color: #dc3545; font-weight: 600; }
            .text-saldo { font-weight: 700; }

            .btn-download-excel {
                color: #dc3545;
                background-color: #fff;
                border: 2px solid #dc3545;
                font-weight: 600;
                padding: 5px 14px;
                font-size: 14px;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
            }
            .btn-download-excel:hover { background-color: #dc3545; color: #fff; }
            .btn-download-excel i { margin-right: 5px; }
        </style>
    @endpush

    <section class="content-header cb-fullscreen-hide">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detail Transaksi VA</h1>
                </div>

                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('daftarBank.index') }}">Daftar VA</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="invoice p-3 mb-3 cb-fullscreen-table">

                        <!-- Header -->
                        <div class="row mb-3 cb-fullscreen-hide">
                            <div class="col-12 d-flex align-items-start justify-content-between flex-wrap">
                                <div>
                                    <h4>
                                        <i class="fas fa-university"></i>
                                        {{ $va->nama_tujuan }}
                                    </h4>
                                    <p class="text-muted mb-0">Buku Pembantu (Ledger) — Gabungan Bank Masuk & Bank Keluar</p>
                                </div>
                                <a href="{{ route('daftarBank.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>

                        <hr class="cb-fullscreen-hide">

                        <div class="row mb-2">
                            <div class="col-12 d-flex align-items-center flex-wrap">
                                <label for="filterBulan" class="mr-2 mb-0 font-weight-bold">Bulan</label>
                                <select id="filterBulan" class="form-control form-control-sm mr-3" style="width: 140px;">
                                    <option value="">Semua</option>
                                    @foreach (['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $val => $nama)
                                        <option value="{{ $val }}">{{ $nama }}</option>
                                    @endforeach
                                </select>

                                <label for="filterTahun" class="mr-2 mb-0 font-weight-bold">Tahun</label>
                                <select id="filterTahun" class="form-control form-control-sm" style="width: 110px;">
                                    <option value="">Semua</option>
                                    @foreach ($years as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>

                                <button type="button" class="btn-download-excel ml-3" id="btnDownloadExcel">
                                    <i class="fas fa-file-excel"></i> Download Excel
                                </button>

                                <div class="ml-auto">
                                    <input type="text" id="searchVA" class="form-control form-control-sm"
                                        placeholder="Cari transaksi..." style="min-width: 220px;">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div id="tableDetailVA"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal konfirmasi export Excel --}}
    <div class="modal fade" id="modalExportVA" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #1E3A5F; color: #fff;">
                    <h5 class="modal-title">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel — Detail Transaksi VA
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Pilih periode transaksi yang ingin diexport:</p>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="exportBulan">Bulan</label>
                            <select id="exportBulan" class="form-control">
                                <option value="">Semua</option>
                                @foreach (['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $val => $nama)
                                    <option value="{{ $val }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-6">
                            <label for="exportTahun">Tahun</label>
                            <select id="exportTahun" class="form-control">
                                <option value="">Semua</option>
                                @foreach ($years as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info py-2 mb-0" id="exportCountInfo">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="exportCountText">-</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn text-white" style="background-color: #1E3A5F;" id="btnConfirmExportVA">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                var vaName = @json($va->nama_tujuan);
                var vaRows = @json($transactions->values());
                vaRows.forEach(function (r, i) {
                    r.no = i + 1;
                    r.bank = vaName;
                });

                var BULAN_NAMA = {
                    '01': 'Januari', '02': 'Februari', '03': 'Maret', '04': 'April',
                    '05': 'Mei', '06': 'Juni', '07': 'Juli', '08': 'Agustus',
                    '09': 'September', '10': 'Oktober', '11': 'November', '12': 'Desember'
                };

                function fmtTanggal(t) {
                    var s = String(t || '');
                    if (s.length < 10) return '-';
                    return s.substr(8, 2) + ' ' + (BULAN_NAMA[s.substr(5, 2)] || '') + ' ' + s.substr(0, 4);
                }
                function fmtRupiah(v) { return Math.round(v || 0).toLocaleString('id-ID'); }
                function moneyCalcFormatter(cell) { return fmtRupiah(cell.getValue()); }

                var userSized = !!localStorage.getItem('tabulator-cb-va-detail-columns');

                var table = new Tabulator('#tableDetailVA', {
                    persistence: { columns: ['width'] },
                    persistenceID: 'cb-va-detail',
                    data: vaRows,
                    layout: userSized ? 'fitData' : 'fitColumns',
                    columnHeaderVertAlign: 'middle',
                    movableColumns: false,
                    columnDefaults: { headerSort: false, minWidth: 30, variableHeight: true },
                    renderVertical: 'basic',
                    // Batasi tinggi tabel → muncul scrollbar vertikal saat baris banyak
                    // (mis. page size 100 tanpa filter). Header & baris TOTAL tetap terlihat.
                    maxHeight: '75vh',
                    pagination: true,
                    paginationSize: 10,
                    paginationSizeSelector: [10, 25, 50, 100],
                    paginationCounter: 'rows',
                    placeholder: 'Belum ada transaksi untuk VA ini.',
                    columns: [
                        // No mengikuti urutan baris yang sedang tampil (ikut filter),
                        // selalu mulai dari 1 — hindari kesan "loncat"/terpotong.
                        { title: 'No', field: 'no', width: 55, hozAlign: 'center', formatter: 'rownum' },
                        {
                            title: 'Tanggal', field: 'tanggal', width: 135, hozAlign: 'center',
                            formatter: function (cell) { return fmtTanggal(cell.getValue()); }
                        },
                        { title: 'Bank Tujuan', field: 'bank', width: 190 },
                        {
                            title: 'Penerima/Dari', field: 'penerima', width: 150,
                            formatter: function (cell) { return cell.getValue() || '-'; }
                        },
                        {
                            title: 'Uraian', field: 'uraian', widthGrow: 3,
                            formatter: function (cell) { return cell.getValue() || '-'; }
                        },
                        {
                            title: 'Debet', field: 'debet', width: 120, hozAlign: 'right',
                            formatter: function (cell) {
                                var v = cell.getValue() || 0;
                                return v > 0 ? '<span class="text-debet">' + fmtRupiah(v) + '</span>' : '-';
                            },
                            bottomCalc: 'sum', bottomCalcFormatter: moneyCalcFormatter,
                            bottomCalcParams: { precision: 0 }
                        },
                        {
                            title: 'Kredit', field: 'kredit', width: 120, hozAlign: 'right',
                            formatter: function (cell) {
                                var v = cell.getValue() || 0;
                                return v > 0 ? '<span class="text-kredit">' + fmtRupiah(v) + '</span>' : '-';
                            },
                            bottomCalc: 'sum', bottomCalcFormatter: moneyCalcFormatter,
                            bottomCalcParams: { precision: 0 }
                        },
                        {
                            title: 'Saldo Akhir', field: 'saldo', width: 135, hozAlign: 'right',
                            formatter: function (cell) {
                                return '<span class="text-saldo">' + fmtRupiah(cell.getValue()) + '</span>';
                            },
                            bottomCalc: function (values) { return values.length ? values[values.length - 1] : 0; },
                            bottomCalcFormatter: moneyCalcFormatter
                        }
                    ]
                });

                // Baris TOTAL: gabungkan sel No..Uraian jadi satu sel berlabel TOTAL di tengah
                var MERGE_FIELDS = ['no', 'tanggal', 'bank', 'penerima', 'uraian'];
                function mergeTotalRow() {
                    var rowEl = document.querySelector('#tableDetailVA .tabulator-row.tabulator-calcs-bottom');
                    if (!rowEl) return;
                    var width = 0;
                    MERGE_FIELDS.forEach(function (f) { var col = table.getColumn(f); if (col) width += col.getWidth(); });
                    var firstCell = null;
                    rowEl.querySelectorAll('.tabulator-cell').forEach(function (c) {
                        var f = c.getAttribute('tabulator-field');
                        if (MERGE_FIELDS.indexOf(f) === -1) return;
                        if (f === 'no') firstCell = c; else c.style.display = 'none';
                    });
                    if (firstCell) {
                        firstCell.style.width = width + 'px';
                        firstCell.style.maxWidth = width + 'px';
                        firstCell.style.textAlign = 'center';
                        firstCell.textContent = 'TOTAL';
                    }
                }
                table.on('renderComplete', mergeTotalRow);
                table.on('columnResized', function () { setTimeout(mergeTotalRow, 0); });

                function applyFilters() {
                    table.setFilter(function (data) {
                        var bulan = $('#filterBulan').val();
                        var tahun = $('#filterTahun').val();
                        var q = ($('#searchVA').val() || '').toLowerCase();
                        var t = String(data.tanggal || '');
                        if (bulan && t.substr(5, 2) !== bulan) return false;
                        if (tahun && t.substr(0, 4) !== tahun) return false;
                        if (q) {
                            var hay = [
                                fmtTanggal(data.tanggal), data.penerima || '', data.uraian || '', vaName,
                                fmtRupiah(data.debet), fmtRupiah(data.kredit), fmtRupiah(data.saldo)
                            ].join(' ').toLowerCase();
                            if (hay.indexOf(q) === -1) return false;
                        }
                        return true;
                    });
                }
                $('#filterBulan, #filterTahun').on('change', applyFilters);
                $('#searchVA').on('keyup', applyFilters);

                function updateExportCount() {
                    var bulan = $('#exportBulan').val();
                    var tahun = $('#exportTahun').val();
                    var count = 0;
                    vaRows.forEach(function (r) {
                        var t = String(r.tanggal || '');
                        if (bulan && t.substr(5, 2) !== bulan) return;
                        if (tahun && t.substr(0, 4) !== tahun) return;
                        count++;
                    });
                    var label = [];
                    if (bulan) label.push($('#exportBulan option:selected').text());
                    if (tahun) label.push(tahun);
                    var periode = label.length ? label.join(' ') : 'semua periode';
                    $('#exportCountText').text(count.toLocaleString('id-ID') + ' transaksi (' + periode + ') akan diexport.');
                    $('#btnConfirmExportVA').prop('disabled', count === 0);
                    $('#exportCountInfo').toggleClass('alert-info', count > 0).toggleClass('alert-warning', count === 0);
                    if (count === 0) $('#exportCountText').text('Tidak ada transaksi pada periode ini.');
                }

                $(document).on('click', '#btnDownloadExcel', function () {
                    $('#exportBulan').val($('#filterBulan').val());
                    $('#exportTahun').val($('#filterTahun').val());
                    updateExportCount();
                    $('#modalExportVA').modal('show');
                });
                $('#exportBulan, #exportTahun').on('change', updateExportCount);

                $('#btnConfirmExportVA').on('click', function () {
                    var params = new URLSearchParams();
                    var bulan = $('#exportBulan').val();
                    var tahun = $('#exportTahun').val();
                    if (bulan) params.set('bulan', bulan);
                    if (tahun) params.set('tahun', tahun);
                    var qs = params.toString();
                    window.location.href = '{{ route('daftarBank.exportDetail', $va->id_bank_tujuan) }}' + (qs ? '?' + qs : '');
                    $('#modalExportVA').modal('hide');
                });
            });
        </script>
    @endpush

@endsection
