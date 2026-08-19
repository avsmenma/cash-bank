@extends("layouts/va_layout")
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('plugins/tabulator/tabulator_semanticui.min.css') }}">
        <style>
            .content-header,
            section.content {
                zoom: 0.85;
            }

            /* Tabulator — styling konsisten dengan tema Navy standard Cash Bank */
            #tableCashFlow .tabulator-header,
            #tableCashFlow .tabulator-header .tabulator-col {
                background-color: #1E3A5F !important;
                color: #fff !important;
                border-color: rgba(255, 255, 255, 0.25) !important;
            }

            #tableCashFlow .tabulator-header .tabulator-col .tabulator-col-title {
                color: #fff;
                font-weight: 600;
                white-space: normal;
                text-align: center;
            }

            #tableCashFlow .tabulator-header .tabulator-col {
                border-right: 2px solid rgba(255, 255, 255, 0.9) !important;
            }

            #tableCashFlow .tabulator-row.tabulator-row-even {
                background-color: #F5F8FB;
            }

            #tableCashFlow .tabulator-cell {
                white-space: normal;
                overflow-wrap: break-word;
                border-right: 1px solid #C9D4DF !important;
            }

            #tableCashFlow .tabulator-row {
                border-bottom: 1px solid #C9D4DF !important;
            }

            #tableCashFlow .tabulator-calcs-holder,
            #tableCashFlow .tabulator-row.tabulator-calcs-bottom,
            #tableCashFlow .tabulator-row.tabulator-calcs-bottom .tabulator-cell {
                background-color: #1E3A5F !important;
                color: #fff !important;
                font-weight: 700;
                border-color: rgba(255, 255, 255, 0.25) !important;
                border-right: 2px solid rgba(255, 255, 255, 0.9) !important;
            }
        </style>
    @endpush

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold">Cash Flow</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('va.dashboard') }}">Virtual Account</a></li>
                        <li class="breadcrumb-item active">Cash Flow</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-money-bill-wave mr-1"></i> Cash Flow — {{ $va->nama_tujuan }}
                    </h3>
                </div>

                <div class="card-body">
                    <!-- Filter Dropdown: Hanya Bulan dan Tahun -->
                    <div class="row mb-3">
                        <div class="col-12 d-flex align-items-center flex-wrap">
                            <label for="filterBulan" class="mr-2 mb-0 font-weight-bold">Bulan</label>
                            <select id="filterBulan" class="form-control form-control-sm mr-3" style="width: 140px;">
                                <option value="">Semua</option>
                                @foreach (['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $val => $nama)
                                    <option value="{{ $val }}" {{ $selectedBulan == $val ? 'selected' : '' }}>{{ $nama }}</option>
                                @endforeach
                            </select>

                            <label for="filterTahun" class="mr-2 mb-0 font-weight-bold">Tahun</label>
                            <select id="filterTahun" class="form-control form-control-sm" style="width: 120px;">
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Wadah Tabel Tabulator -->
                    <div class="row">
                        <div class="col-12">
                            <div id="tableCashFlow" style="min-height: 250px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="{{ asset('plugins/tabulator/tabulator.min.js') }}"></script>
        <script>
            $(document).ready(function () {
                var selectedYear = parseInt($('#filterTahun').val()) || new Date().getFullYear();
                var prevYear = selectedYear - 1;
                var tableData = @json($cashflowData);

                function formatRupiah(cell) {
                    var val = cell.getValue();
                    if (val === null || val === undefined || val === '') return '-';
                    var num = Number(val);
                    if (isNaN(num) || num === 0) return '-';
                    var formatted = Math.abs(num).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                    return num < 0 ? '(' + formatted + ')' : formatted;
                }

                // Inisialisasi Tabel Tabulator
                var table = new Tabulator("#tableCashFlow", {
                    data: tableData,
                    placeholder: "<div class='py-4 text-muted'><i class='fas fa-info-circle mr-1'></i> Belum ada data Cash Flow untuk periode ini.</div>",
                    layout: "fitColumns",
                    responsiveLayout: true,
                    columns: [
                        {
                            title: "Reference Key",
                            field: "reference_key",
                            width: 180,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            headerSort: true
                        },
                        {
                            title: "Uraian",
                            field: "uraian",
                            minWidth: 320,
                            hozAlign: "left",
                            headerHozAlign: "center",
                            headerSort: true
                        },
                        {
                            title: "Realisasi " + selectedYear,
                            field: "realisasi_tahun",
                            hozAlign: "right",
                            headerHozAlign: "center",
                            formatter: formatRupiah,
                            headerSort: true
                        },
                        {
                            title: "Realisasi " + prevYear,
                            field: "realisasi_tahun_lalu",
                            hozAlign: "right",
                            headerHozAlign: "center",
                            formatter: formatRupiah,
                            headerSort: true
                        }
                    ]
                });

                // Perubahan filter Bulan / Tahun -> reload halaman dengan parameter query
                function applyFilter() {
                    var yr = $('#filterTahun').val();
                    var bln = $('#filterBulan').val();
                    var url = new URL(window.location.href);
                    url.searchParams.set('tahun', yr);
                    if (bln) {
                        url.searchParams.set('bulan', bln);
                    } else {
                        url.searchParams.delete('bulan');
                    }
                    window.location.href = url.toString();
                }

                $('#filterTahun, #filterBulan').on('change', function () {
                    applyFilter();
                });
            });
        </script>
    @endpush
@endsection
