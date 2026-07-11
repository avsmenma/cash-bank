@extends('layouts/index')
@section('content')
    <div class="container-fuild">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Penerima</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active"><a href="{{route('dashboard.index')}}">Dashboard
                                    Pembayaran</a></li>
                            <li class="breadcrumb-item"><a href="{{route('dashboard.pembayaran.index')}}">Dashboard Versi
                                    2</a></li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header p-2 cb-fullscreen-tabs">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#activity"
                                data-toggle="tab">Realisasi</a></li>
                        <li class="nav-item"><a class="nav-link" id="rencana-tab" href="#rencana"
                                data-toggle="tab">Rencana</a></li>
                        <li class="nav-item"> <a class="nav-link" id="cashflow-tab" data-toggle="tab"
                                href="#timeline">Rekap</a></li>
                        <li class="nav-item"> <a class="nav-link" id="cashflowGabungan-tab" data-toggle="tab"
                                href="#gabungan">Evaluasi</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane cb-fullscreen-table" id="activity">
                            <div class="row no-print">
                                <div class="col-12">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                        {{-- Tombol Aksi --}}
                                        <a href="#" rel="noopener" class="btn btn-danger btn-sm" id="deleteAllSelectedRecord">
                                            <i class="fas fa-trash"></i> Delete All
                                        </a>
                                        <a href="javascript:void(0)" class="btn btn-success btn-sm" data-toggle="modal" data-target="#ModalCreatePenerima">
                                            <i class="fas fa-plus"></i> Tambah Data
                                        </a>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-file-export"></i> Export
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('penerima.export_pdf') }}" id="btnDownloadPdf">
                                                    <i class="fas fa-file-pdf text-danger"></i> Export PDF
                                                </a>
                                                <a class="dropdown-item" href="{{ route('penerima.export_excel') }}" id="btnDownloadExcel">
                                                    <i class="fas fa-file-excel text-success"></i> Export Excel
                                                </a>
                                            </div>
                                        </div>
                                        <a href="javascript:void(0)" class="btn btn-info btn-sm" data-toggle="modal" data-target="#ModalImportPenerima">
                                            <i class="fas fa-file-import"></i> Import Data
                                        </a>

                                        {{-- Separator --}}
                                        <div style="width:1px;height:28px;background:#ccc;margin:0 4px;"></div>

                                        {{-- Filter Tahun --}}
                                        <select class="filter-select2 form-control form-control-sm" id="filterTahunRealisasi" style="width:90px;">
                                            @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                                <option value="{{ $t }}" {{ $t == date('Y') ? 'selected' : '' }}>{{ $t }}</option>
                                            @endfor
                                        </select>

                                        {{-- Filter Dari Bulan --}}
                                        <select class="form-control form-control-sm" id="filterBulanDariRealisasi" style="width:115px;">
                                            <option value="">Dari Bulan</option>
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>

                                        {{-- Filter Sampai Bulan --}}
                                        <select class="form-control form-control-sm" id="filterBulanSampaiRealisasi" style="width:115px;">
                                            <option value="">Sampai Bulan</option>
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>

                                        {{-- Tombol Terapkan --}}
                                        <button class="btn btn-primary btn-sm" id="terapkanFilterRealisasi">
                                            <i class="fas fa-filter"></i> Terapkan
                                        </button>

                                        {{-- Tombol Reset --}}
                                        <button class="btn btn-secondary btn-sm" id="resetFilterRealisasi">
                                            <i class="fas fa-times"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>



                            {{-- ================================================================
                                 TAB KATEGORI: pengganti dropdown kategori.
                                 Tab Semua menampilkan seluruh kategori; tab lain memfilter tabel
                                 berdasarkan kategori terpilih. Filter yang tersisa hanya bulan & tahun.
                                 ================================================================ --}}
                            <style>
                                .pn-tabbar {
                                    display: flex;
                                    flex-wrap: wrap;
                                    gap: 6px;
                                    margin-bottom: 12px;
                                }
                                .pn-tabbar .pn-tab {
                                    border: 1px solid #d0d5dd;
                                    background: #fff;
                                    color: #344054;
                                    padding: 6px 16px;
                                    border-radius: 6px;
                                    font-size: 12.5px;
                                    font-weight: 600;
                                    cursor: pointer;
                                    transition: all .15s ease;
                                }
                                .pn-tabbar .pn-tab:hover { background: #f0f4f8; }
                                .pn-tabbar .pn-tab.active {
                                    background: #1e3a5f;
                                    border-color: #1e3a5f;
                                    color: #fff;
                                }
                            </style>
                            <div class="pn-tabbar" id="penerimaKategoriTabs">
                                <button type="button" class="pn-tab active" data-kategori="">Semua</button>
                                @foreach($kategoriKriteria as $k)
                                    <button type="button" class="pn-tab" data-kategori="{{ $k->id_kategori_kriteria }}">{{ $k->nama_kriteria }}</button>
                                @endforeach
                            </div>

                            <div id="realisasi-content">
                                <div class="text-center p-5">
                                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                                    <p class="mt-2 text-muted">Memuat data...</p>
                                </div>
                            </div>

                        </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane cb-fullscreen-table" id="timeline">
                        <div class="row no-print mb-3">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('penerima.export_pdf_cashFlow') }}" id="btnDownloadPdfCashFlow" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download
                                        PDF</a>
                                    <a href="{{ route('penerima.export_excel_cashFlow') }}" id="btnDownloadExcelCashFlow" class="btn btn-outline-danger"><i class="fas fa-file-excel"></i> Download
                                        Excel</a>
                                    <div class="col-md-3">
                                        <select class="select2" id="tahunCashflow">
                                            @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                                <option value="{{ $t }}" {{ request('tahun', date('Y')) == $t ? 'selected' : '' }}> {{ $t }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="cashflow-content" class="p-3 text-center">
                            <span class="text-muted">Memuat data...</span>
                        </div>
                    </div>
                    {{-- TAB RENCANA --}}
                    <div class="tab-pane cb-fullscreen-table" id="rencana">
                        <div class="row no-print mb-3">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('penerima.export_pdf_rencana') }}" id="btnDownloadPdfRencana" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download
                                        PDF</a>
                                    <a href="{{ route('penerima.export_excel_rencana') }}" id="btnDownloadExcelRencana" class="btn btn-outline-danger"><i class="fas fa-file-excel"></i> Download
                                        Excel</a>
                                    <div class="col-md-3">
                                        <select class="select2" id="tahunRencana">
                                            @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                                <option value="{{ $t }}" {{ request('tahun', date('Y')) == $t ? 'selected' : '' }}> {{ $t }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    {{-- Tombol Simpan --}}
                                    <button type="button" class="btn btn-success" id="btnSimpanRencana">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- RENCANA CONTENT - ID UNIK --}}
                        <div id="rencana-content" class="p-3">
                            <div class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Memuat data rencana...
                            </div>
                        </div>
                    </div>
                    {{-- TAB GABUNGAN --}}
                    <div class="tab-pane cb-fullscreen-table" id="gabungan">
                        <div class="row no-print mb-3">
                            <div class="col-12">
                                <div class="d-flex gap-2 align-items-center">
                                    <a href="{{ route('penerima.export_pdf_gabungan') }}" id="btnDownloadPdfGabungan" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a>
                                    <a href="{{ route('penerima.export_excel_gabungan') }}" id="btnDownloadExcelGabungan" class="btn btn-outline-danger"><i class="fas fa-file-excel"></i> Download Excel</a>
                                    <div class="col-md-2">
                                        <label>Tahun:</label>
                                        <select class="select2" id="tahunGabungan">
                                            @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                                <option value="{{ $t }}" {{ $t == date('Y') ? 'selected' : '' }}>{{ $t }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label>Dari Bulan:</label>
                                        <select class="form-control" id="bulanDari">
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label>Sampai Bulan:</label>
                                        <select class="form-control" id="bulanSampai">
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12" selected>Desember</option>
                                        </select>
                                    </div>

                                    <div class="col-md-1">
                                        <label>&nbsp;</label>
                                        <button class="btn btn-primary btn-block" id="filterGabungan">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- GABUNGAN CONTENT - ID UNIK --}}
                        <div id="gabungan-content" class="p-3">
                            <div class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Memuat CashFlow data gabungan rencana & realisasi...
                            </div>
                        </div>
                    </div><!-- /.tab-pane gabungan -->
                </div><!-- /.tab-content -->
            </div><!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>

    @include('cash_bank.modal.modalPenerima.createPenerima')
    @include('cash_bank.modal.modalPenerima.editPenerima')
    @include('cash_bank.modal.modalPenerima.importPenerima')

    @push('scripts')
        <script>
            // Move modals to body level to prevent z-index/overflow issues with AdminLTE content-wrapper
            $(document).ready(function() {
                $('#ModalCreatePenerima, #editPenerima, #ModalImportPenerima').appendTo('body');
            });
        </script>
        <script>
            $(document).ready(function () {
                console.log('=== SCRIPT LOADED ===');

                // INIT SELECT2 (global - untuk modal dan tab lain)
                if ($.fn.select2) {
                    $('.select2').select2({
                        theme: 'bootstrap4',
                        width: '100%'
                    });
                    $('.filter-select2').select2({
                        theme: 'bootstrap4',
                        width: 'resolve'
                    });
                }

                // ===== REALISASI / DROPPING TAB =====
                // Kategori dipilih lewat tab bar (bukan dropdown lagi)
                let kategoriRealisasiAktif = '';

                function loadRealisasi() {
                    let tahun      = $('#filterTahunRealisasi').val();
                    let kategori   = kategoriRealisasiAktif;
                    let bulanDari  = $('#filterBulanDariRealisasi').val();
                    let bulanSampai = $('#filterBulanSampaiRealisasi').val();

                    // Update download links
                    let baseUrlExcel = "{{ route('penerima.export_excel') }}";
                    let baseUrlPdf   = "{{ route('penerima.export_pdf') }}";
                    let params = '?tahun=' + tahun;
                    if (kategori) params += '&kategori=' + kategori;
                    if (bulanDari) params += '&bulan_dari=' + bulanDari;
                    if (bulanSampai) params += '&bulan_sampai=' + bulanSampai;
                    $('#btnDownloadExcel').attr('href', baseUrlExcel + params);
                    $('#btnDownloadPdf').attr('href', baseUrlPdf + params);

                    $('#realisasi-content').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2 text-muted">Memuat data...</p></div>');

                    $.ajax({
                        url: "{{ route('penerima.data-grouped') }}",
                        method: 'GET',
                        data: {
                            tahun: tahun,
                            kategori: kategori,
                            bulan_dari: bulanDari,
                            bulan_sampai: bulanSampai
                        },
                        success: function (response) {
                            $('#realisasi-content').html(response);
                        },
                        error: function (xhr) {
                            console.error('Realisasi error:', xhr);
                            $('#realisasi-content').html('<div class="alert alert-danger">Gagal memuat data realisasi</div>');
                        }
                    });
                }

                // Load on page load
                loadRealisasi();

                // Klik tab kategori: set kategori aktif lalu muat ulang tabel
                $('#penerimaKategoriTabs').on('click', '.pn-tab', function () {
                    kategoriRealisasiAktif = $(this).data('kategori') !== undefined
                        ? String($(this).data('kategori'))
                        : '';
                    $('#penerimaKategoriTabs .pn-tab').removeClass('active');
                    $(this).addClass('active');
                    loadRealisasi();
                });

                // Terapkan filter
                $('#terapkanFilterRealisasi').on('click', function () {
                    loadRealisasi();
                });

                // Reset Filter (kategori kembali ke tab Semua)
                $('#resetFilterRealisasi').on('click', function () {
                    $('#filterTahunRealisasi').val({{ date('Y') }}).trigger('change');
                    $('#filterBulanDariRealisasi').val('');
                    $('#filterBulanSampaiRealisasi').val('');
                    kategoriRealisasiAktif = '';
                    $('#penerimaKategoriTabs .pn-tab').removeClass('active');
                    $('#penerimaKategoriTabs .pn-tab[data-kategori=""]').addClass('active');
                    loadRealisasi();
                });

                // ===== TAB CASHFLOW =====
                $('#cashflow-tab').on('shown.bs.tab', function () {
                    loadCashflow();
                });

                $('#tahunCashflow').on('change', function () {
                    loadCashflow();
                });

                function loadCashflow() {
                    const tahun = $('#tahunCashflow').val();
                    let baseUrlExcel = "{{ route('penerima.export_excel_cashFlow') }}";
                    let baseUrlPdf = "{{ route('penerima.export_pdf_cashFlow') }}";
                    $('#btnDownloadExcelCashFlow').attr('href', baseUrlExcel + '?tahun=' + tahun);
                    $('#btnDownloadPdfCashFlow').attr('href', baseUrlPdf + '?tahun=' + tahun);
                    $('#cashflow-content').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
                    $.ajax({
                        url: "{{ route('penerima.cashflow') }}",
                        method: 'GET',
                        data: { tahun: tahun },
                        success: function (response) {
                            $('#cashflow-content').html(response);
                        },
                        error: function (xhr) {
                            $('#cashflow-content').html('<div class="alert alert-danger">Gagal memuat data cashflow</div>');
                        }
                    });
                }

                // ===== TAB RENCANA =====
                $('#rencana-tab').on('shown.bs.tab', function () {
                    loadRencana();
                });

                $('#tahunRencana').on('change', function () {
                    loadRencana();
                });

                function loadRencana() {
                    const tahun = $('#tahunRencana').val();
                    let baseUrlExcel = "{{ route('penerima.export_excel_rencana') }}";
                    let baseUrlPdf = "{{ route('penerima.export_pdf_rencana') }}";
                    $('#btnDownloadExcelRencana').attr('href', baseUrlExcel + '?tahun=' + tahun);
                    $('#btnDownloadPdfRencana').attr('href', baseUrlPdf + '?tahun=' + tahun);
                    $('#rencana-content').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
                    $.ajax({
                        url: "{{ route('penerima.rencana') }}",
                        method: 'GET',
                        data: { tahun: tahun },
                        success: function (response) {
                            $('#rencana-content').html(response);
                        },
                        error: function (xhr) {
                            $('#rencana-content').html('<div class="alert alert-danger">Gagal memuat data rencana: ' + xhr.statusText + '</div>');
                        }
                    });
                }

                // ===== TAB GABUNGAN =====
                $('#cashflowGabungan-tab').on('shown.bs.tab', function () {
                    loadGabungan();
                });
                $('#filterGabungan').click(function () { loadGabungan(); });
                $('#tahunGabungan, #bulanDari, #bulanSampai').keypress(function (e) {
                    if (e.which == 13) loadGabungan();
                });

                function loadGabungan() {
                    var tahun = $('#tahunGabungan').val();
                    var bulanDari = $('#bulanDari').val();
                    var bulanSampai = $('#bulanSampai').val();
                    if (parseInt(bulanDari) > parseInt(bulanSampai)) {
                        alert('Bulan dari tidak boleh lebih besar dari bulan sampai');
                        return;
                    }

                    // Link export ikut filter terpilih (tanpa ini export selalu pakai default)
                    var paramsGabungan = '?tahun=' + tahun + '&bulan_dari=' + bulanDari + '&bulan_sampai=' + bulanSampai;
                    $('#btnDownloadPdfGabungan').attr('href', '{{ route("penerima.export_pdf_gabungan") }}' + paramsGabungan);
                    $('#btnDownloadExcelGabungan').attr('href', '{{ route("penerima.export_excel_gabungan") }}' + paramsGabungan);

                    $('#gabungan-content').html('<div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
                    $.ajax({
                        url: '{{ route("penerima.gabungan") }}',
                        type: 'GET',
                        data: { tahun: tahun, bulan_dari: bulanDari, bulan_sampai: bulanSampai },
                        success: function (response) { $('#gabungan-content').html(response); },
                        error: function () { $('#gabungan-content').html('<div class="alert alert-danger">Gagal memuat data</div>'); }
                    });
                }

                // ===== TOMBOL SIMPAN RENCANA (menggantikan auto-save) =====
                $('#btnSimpanRencana').on('click', function () {
                    var tahun = $('#tahunRencana').val();
                    var rows  = [];

                    // Kumpulkan semua cell dalam tabel
                    $('#rencana-content .cell-rencana').each(function () {
                        var $cell = $(this);
                        var rawText = $cell.text().replace(/\./g, '').trim();
                        var nilai   = parseInt(rawText) || 0;
                        rows.push({
                            kategori : $cell.data('kategori'),
                            bulan    : $cell.data('bulan'),
                            nilai    : nilai
                        });
                    });

                    if (rows.length === 0) {
                        alert('Tidak ada data untuk disimpan.');
                        return;
                    }

                    var $btn = $(this);
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                    $.ajax({
                        url    : "{{ route('penerima.rencana.saveBatch') }}",
                        method : 'POST',
                        data   : {
                            _token : "{{ csrf_token() }}",
                            tahun  : tahun,
                            rows   : rows
                        },
                        success: function (response) {
                            if (response.success) {
                                // Hapus semua highlight setelah simpan berhasil
                                $('#rencana-content .cell-rencana').each(function () {
                                    $(this).removeClass('cell-changed');
                                    $(this).attr('data-original', $(this).text().trim());
                                });
                                // Tampilkan notif sukses sementara
                                $btn.removeClass('btn-success').addClass('btn-outline-success')
                                    .html('<i class="fas fa-check"></i> Tersimpan');
                                setTimeout(function () {
                                    $btn.prop('disabled', false)
                                        .removeClass('btn-outline-success').addClass('btn-success')
                                        .html('<i class="fas fa-save"></i> Simpan');
                                }, 2000);
                            } else {
                                alert('Gagal menyimpan: ' + (response.message || 'Unknown error'));
                                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                            }
                        },
                        error: function (xhr) {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Server error';
                            alert('Gagal menyimpan data: ' + msg);
                            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                        }
                    });
                });

                // ===== SELECT ALL & DELETE ALL =====
                $(document).on('click', '#select_all_ids', function () {
                    $('.checkbox_ids').prop('checked', $(this).prop('checked'));
                });

                $(document).on('click', '#deleteAllSelectedRecord', function (e) {
                    e.preventDefault();
                    let all_ids = [];
                    $('.checkbox_ids:checked').each(function () {
                        all_ids.push($(this).val());
                    });
                    if (all_ids.length === 0) {
                        alert('Pilih data terlebih dahulu!');
                        return;
                    }
                    if (!confirm('Yakin ingin menghapus ' + all_ids.length + ' data?')) return;
                    $.ajax({
                        url: "{{ route('penerima.delete') }}",
                        type: "DELETE",
                        data: { ids: all_ids, _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            alert(res.success);
                            loadRealisasi();
                        },
                        error: function () { alert('Gagal menghapus data'); }
                    });
                });

                // ===== MODAL CREATE =====
                $('#ModalCreatePenerima').on('shown.bs.modal', function () {
                    $('#importExcel')[0].reset();
                    if (!$('#create_reservationdate').data('datetimepicker')) {
                        $('#create_reservationdate').datetimepicker({ format: 'YYYY-MM-DD' });
                    }
                    $(this).find('.select2').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#ModalCreatePenerima'),
                        width: '100%'
                    });
                });

                // Handle create form submission via AJAX
                $(document).on('submit', '#importExcel', function (e) {
                    e.preventDefault();
                    let $form = $(this);
                    let $btn = $form.find('button[type="submit"]');
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                    let formData = {
                        _token: $form.find('input[name="_token"]').val(),
                        id_kategori_kriteria: $('#create_kategori').val(),
                        kontrak: $('#create_kontrak').val(),
                        pembeli: $('#create_pembeli').val(),
                        tanggal: $form.find('input[name="tanggal"]').val(),
                        no_reg: $('#create_no_reg').val(),
                        volume: unformatRibuan($('#create_volume').val()),
                        harga: unformatRibuan($('#create_harga').val()),
                        nilai: unformatRibuan($('#create_nilai').val()),
                        ppn: unformatRibuan($('#create_ppn').val()),
                        potppn: unformatRibuan($('#create_potppn').val()),
                        nilai_inc_ppn: unformatRibuan($('#create_nilai_inc_ppn').val()),
                    };

                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            $('#ModalCreatePenerima').modal('hide');
                            loadRealisasi();
                            alert('Data berhasil disimpan!');
                        },
                        error: function (xhr) {
                            let msg = 'Gagal menyimpan data.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                msg = Object.values(errors).flat().join('\n');
                            }
                            alert(msg);
                        },
                        complete: function () {
                            $btn.prop('disabled', false).html('Save changes');
                        }
                    });
                });

                // ===== FORMAT ANGKA HELPERS =====
                function formatRibuan(val) {
                    if (val === null || val === undefined || val === '') return '0';
                    let num = parseFloat(String(val).replace(/\./g, '').replace(/,/g, '.'));
                    if (isNaN(num)) return '0';
                    // Bulatkan dulu, lalu format dengan titik ribuan
                    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }
                function unformatRibuan(str) {
                    if (!str || str === '') return 0;
                    return str.replace(/\./g, '');
                }

                // Auto-format .edit-angka fields on input
                $(document).on('input', '.edit-angka', function () {
                    let raw = $(this).val().replace(/\./g, '');
                    if (raw === '' || raw === '-') return;
                    // Hapus karakter non-digit
                    raw = raw.replace(/[^\d]/g, '');
                    $(this).val(formatRibuan(raw));
                });

                // ===== MODAL EDIT =====
                $(document).on('click', '[data-target="#editPenerima"]', function (e) {
                    e.preventDefault();
                    let button = $(this);
                    let id = button.data('id');
                    $('#formEditPenerima').attr('action', '/penerima/' + id);
                    $('#edit_pembeli').val(button.data('pembeli'));
                    $('#edit_no_reg').val(button.data('no_reg'));
                    $('#edit_kontrak').val(button.data('kontrak'));

                    // Format angka dengan titik ribuan
                    $('#edit_volume').val(formatRibuan(button.data('volume')));
                    $('#edit_harga').val(formatRibuan(button.data('harga')));
                    $('#edit_nilai').val(formatRibuan(button.data('nilai')));
                    $('#edit_ppn').val(formatRibuan(button.data('ppn')));
                    $('#edit_potppn').val(formatRibuan(button.data('potppn')));
                    $('#edit_nilai_inc_ppn').val(formatRibuan(button.data('nilai_inc_ppn') || 0));

                    if (!$('#edit_reservationdate').data('datetimepicker')) {
                        $('#edit_reservationdate').datetimepicker({ format: 'YYYY-MM-DD' });
                    }
                    $('#edit_reservationdate').datetimepicker('date', button.data('tanggal'));

                    $('#editPenerima').find('.select2').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#editPenerima'),
                        width: '100%'
                    });
                    $('#edit_kategori').val(button.data('kategori')).trigger('change');

                    $('#editPenerima').modal('show');
                });

                // Handle edit form submission via AJAX
                $(document).on('submit', '#formEditPenerima', function (e) {
                    e.preventDefault();
                    let $form = $(this);
                    let actionUrl = $form.attr('action');
                    let $btn = $form.find('button[type="submit"]');
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                    // Build data with unformatted numbers
                    let formData = {
                        _token: $form.find('input[name="_token"]').val(),
                        _method: 'PUT',
                        id_kategori_kriteria: $('#edit_kategori').val(),
                        kontrak: $('#edit_kontrak').val(),
                        pembeli: $('#edit_pembeli').val(),
                        tanggal: $form.find('input[name="tanggal"]').val(),
                        no_reg: $('#edit_no_reg').val(),
                        volume: unformatRibuan($('#edit_volume').val()),
                        harga: unformatRibuan($('#edit_harga').val()),
                        nilai: unformatRibuan($('#edit_nilai').val()),
                        ppn: unformatRibuan($('#edit_ppn').val()),
                        potppn: unformatRibuan($('#edit_potppn').val()),
                        nilai_inc_ppn: unformatRibuan($('#edit_nilai_inc_ppn').val()),
                    };

                    $.ajax({
                        url: actionUrl,
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            $('#editPenerima').modal('hide');
                            loadRealisasi();
                            alert('Data berhasil diperbarui!');
                        },
                        error: function (xhr) {
                            let msg = 'Gagal menyimpan data.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                msg = Object.values(errors).flat().join('\n');
                            }
                            alert(msg);
                        },
                        complete: function () {
                            $btn.prop('disabled', false).html('Save changes');
                        }
                    });
                });

                // ===== MODAL IMPORT =====
                $('#ModalImportPenerima').on('shown.bs.modal', function () {
                    $(this).find('.select2').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('#ModalImportPenerima'),
                        width: '100%'
                    });
                });

                function parseAngkaID(str) {
                    if (!str || str.trim() === '') return 0;
                    let cleaned = str.trim().replace(/\./g, '').replace(/,/g, '.');
                    let val = parseFloat(cleaned);
                    return isNaN(val) ? 0 : val;
                }

                function parseTanggalID(str) {
                    if (!str || str.trim() === '') return '';
                    let bulanMap = {
                        'Jan':'01','Feb':'02','Mar':'03','Apr':'04',
                        'Mei':'05','May':'05','Jun':'06','Jul':'07',
                        'Agu':'08','Aug':'08','Sep':'09','Okt':'10',
                        'Oct':'10','Nov':'11','Des':'12','Dec':'12'
                    };
                    let parts = str.trim().split(/\s+/);
                    if (parts.length === 3) {
                        let day = parts[0].padStart(2, '0');
                        let month = bulanMap[parts[1]] || '01';
                        let year = parts[2];
                        return year + '-' + month + '-' + day;
                    }
                    return str.trim();
                }

                function parseImportData() {
                    let text = $('#import_data_text').val().trim();
                    if (!text) return [];
                    let lines = text.split('\n');
                    let rows = [];
                    for (let i = 0; i < lines.length; i++) {
                        let line = lines[i].trim();
                        if (!line) continue;
                        let cols = line.split('\t');
                        if (cols.length < 3) continue;
                        while (cols.length < 10) cols.push('');
                        rows.push({
                            kontrak: cols[0].trim(), pembeli: cols[1].trim(),
                            tanggal: parseTanggalID(cols[2]), no_reg: cols[3].trim(),
                            volume: parseAngkaID(cols[4]), harga: parseAngkaID(cols[5]),
                            nilai: parseAngkaID(cols[6]), ppn: parseAngkaID(cols[7]),
                            potppn: parseAngkaID(cols[8]), nilai_inc_ppn: parseAngkaID(cols[9]),
                        });
                    }
                    return rows;
                }

                $('#btnPreviewImport').on('click', function () {
                    let rows = parseImportData();
                    if (rows.length === 0) {
                        alert('Tidak ada data yang bisa di-parse.');
                        return;
                    }
                    let tbody = $('#import_preview_table tbody');
                    tbody.empty();
                    let fmt = (n) => n.toLocaleString('id-ID');
                    rows.forEach(function (r, i) {
                        tbody.append(`<tr><td>${i+1}</td><td>${r.kontrak}</td><td>${r.pembeli}</td><td>${r.tanggal}</td><td>${r.no_reg}</td><td class="text-right">${fmt(r.volume)}</td><td class="text-right">${fmt(r.harga)}</td><td class="text-right">${fmt(r.nilai)}</td><td class="text-right">${fmt(r.ppn)}</td><td class="text-right">${fmt(r.potppn)}</td><td class="text-right">${fmt(r.nilai_inc_ppn)}</td></tr>`);
                    });
                    $('#import_row_count').text(rows.length);
                    $('#import_preview_area').show();
                });

                $('#btnSubmitImport').on('click', function () {
                    let kategori = $('#import_kategori').val();
                    if (!kategori) { alert('Pilih kategori terlebih dahulu!'); return; }
                    let rows = parseImportData();
                    if (rows.length === 0) { alert('Tidak ada data yang bisa diimport.'); return; }
                    if (!confirm('Yakin ingin mengimport ' + rows.length + ' baris data?')) return;
                    let $btn = $(this);
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengimport...');
                    $.ajax({
                        url: "{{ route('penerima.import') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id_kategori_kriteria: kategori,
                            rows: rows
                        },
                        success: function (response) {
                            alert('Berhasil mengimport ' + response.count + ' data!');
                            $('#ModalImportPenerima').modal('hide');
                            $('#import_data_text').val('');
                            $('#import_preview_area').hide();
                            loadRealisasi();
                        },
                        error: function (xhr) {
                            alert('Gagal mengimport data: ' + (xhr.responseJSON?.message || xhr.statusText));
                        },
                        complete: function () {
                            $btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Import Data');
                        }
                    });
                });
            });
        </script>
    @endpush

@endsection
