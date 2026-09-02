@extends('layouts.index')
@section('content')
    @include('cash_bank.partials.gaya-arus-kas')

    @push('styles')
        <style>
            /* Tabel admin sangat lebar (satu grup kolom per unit), jadi kolom kiri
               dibekukan agar Kode/Reference/Uraian + Realisasi Reg5 & Regional Office tetap terlihat
               saat digeser ke kanan. */
            .cf-tabel .tabulator-col.tabulator-frozen,
            .cf-tabel .tabulator-cell.tabulator-frozen {
                z-index: 11;
            }

            /* Batas tegas antara blok beku dan blok unit yang digeser */
            .cf-tabel .tabulator-cell.tabulator-frozen.tabulator-frozen-left:last-of-type,
            .cf-tabel .tabulator-col.tabulator-frozen.tabulator-frozen-left:last-of-type {
                border-right: 3px solid var(--cf-navy) !important;
            }

            /* Judul grup unit: dibuat mencolok supaya batas antar unit terbaca */
            .cf-tabel .tabulator-col.tabulator-col-group > .tabulator-col-content .tabulator-col-title {
                font-size: 11px;
                letter-spacing: .02em;
            }

            .cf-petunjuk-geser {
                font-size: 11px;
                color: #6B7280;
                font-style: italic;
            }

            /* Sel angka yang dapat diklik (drilldown) khusus admin */
            .cf-tabel .cf-clickable-angka {
                cursor: pointer;
                padding: 1px 4px;
                border-radius: 4px;
                transition: all 0.15s ease-in-out;
            }
            .cf-tabel .cf-clickable-angka:hover {
                background-color: rgba(13, 59, 110, 0.15);
                color: #0d3b6e !important;
                text-decoration: underline;
                font-weight: 700;
            }
        </style>
    @endpush

    <section class="content">
        {{-- Flatpickr untuk format tanggal Tanggal-Bulan-Tahun (dd-mm-yyyy) --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="invoice p-3 mb-3">

                        @include('cash_bank.partials.kop-laporan', [
                            'judul' => 'Laporan Arus Kas',
                            'unit' => 'Seluruh Unit & Kebun — ' . $unitColumns->count() . ' unit',
                            'periode' => 'Periode ' . $labelPeriode . ($showTahunLalu ? ' · pembanding Tahun ' . $prevYear : ''),
                        ])

                        {{-- Kartu ringkasan memakai angka global (gabungan seluruh unit) --}}
                        <div class="row mb-3">
                            <div class="col-md-4 mb-2">
                                <div class="cf-kartu masuk">
                                    <div class="cf-kartu-label">Total Penerimaan (Global)</div>
                                    <div class="cf-kartu-nilai" id="kartuPenerimaan">-</div>
                                    @if($showTahunLalu)
                                        <div class="cf-kartu-banding">{{ $prevYear }}: <span id="kartuPenerimaanLalu">-</span></div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="cf-kartu keluar">
                                    <div class="cf-kartu-label">Total Pengeluaran (Global)</div>
                                    <div class="cf-kartu-nilai" id="kartuPengeluaran">-</div>
                                    @if($showTahunLalu)
                                        <div class="cf-kartu-banding">{{ $prevYear }}: <span id="kartuPengeluaranLalu">-</span></div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="cf-kartu bersih">
                                    <div class="cf-kartu-label">Kenaikan (Penurunan) Arus Kas Bersih</div>
                                    <div class="cf-kartu-nilai" id="kartuBersih">-</div>
                                    @if($showTahunLalu)
                                        <div class="cf-kartu-banding">{{ $prevYear }}: <span id="kartuBersihLalu">-</span></div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- FILTER BAR --}}
                        <div class="card shadow-sm mb-3 cf-no-print" style="border-top: 4px solid #0d3b6e; border-radius: 8px;">
                            <div class="card-body py-2 px-3">
                                <form action="{{ route('bank-cashflow.index') }}" method="GET" id="formFilterCashflow">
                                    <div class="d-flex flex-wrap align-items-end" style="gap: 10px;">
                                        
                                        {{-- Tahun --}}
                                        <div style="min-width: 110px;">
                                            <label class="mb-1 small font-weight-bold text-secondary">
                                                <i class="fas fa-calendar mr-1"></i>Tahun
                                            </label>
                                            <select name="tahun" id="filterTahun" class="form-control form-control-sm">
                                                @foreach ($years as $y)
                                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Bulan --}}
                                        <div style="min-width: 130px;">
                                            <label class="mb-1 small font-weight-bold text-secondary">
                                                <i class="fas fa-calendar-alt mr-1"></i>Bulan
                                            </label>
                                            <select name="bulan" id="filterBulan" class="form-control form-control-sm">
                                                <option value="">Semua Bulan</option>
                                                @foreach ($bulanList as $val => $nama)
                                                    <option value="{{ (int)$val }}" {{ (string)$selectedBulan === (string)(int)$val ? 'selected' : '' }}>{{ $nama }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Dari Tanggal --}}
                                        <div style="min-width: 145px;">
                                            <label class="mb-1 small font-weight-bold text-secondary">
                                                <i class="fas fa-calendar-day mr-1"></i>Dari Tanggal
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="tgl_dari" id="filterTglDari" class="form-control form-control-sm bg-white"
                                                       placeholder="dd-mm-yyyy"
                                                       value="{{ !empty($tglDari) ? \Carbon\Carbon::parse($tglDari)->format('d-m-Y') : '' }}"
                                                       autocomplete="off">
                                                <div class="input-group-append">
                                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-secondary"></i></span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Sampai Dengan --}}
                                        <div style="min-width: 145px;">
                                            <label class="mb-1 small font-weight-bold text-secondary">
                                                <i class="fas fa-calendar-check mr-1 text-primary"></i>Sampai Dengan (s/d)
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="tgl_sampai" id="filterTglSampai" class="form-control form-control-sm bg-white"
                                                       placeholder="dd-mm-yyyy"
                                                       value="{{ !empty($tglSampai) ? \Carbon\Carbon::parse($tglSampai)->format('d-m-Y') : '' }}"
                                                       autocomplete="off">
                                                <div class="input-group-append">
                                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-check text-primary"></i></span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Checkboxes --}}
                                        <div class="d-flex align-items-center mb-1" style="gap: 15px;">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="semua" value="1" class="custom-control-input" id="filterSemua" {{ $showEmpty ? 'checked' : '' }}>
                                                <label class="custom-control-label small font-weight-bold text-secondary" for="filterSemua">Tampilkan semua akun</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="tahun_lalu" value="1" class="custom-control-input" id="filterTahunLalu" {{ $showTahunLalu ? 'checked' : '' }}>
                                                <label class="custom-control-label small font-weight-bold text-secondary" for="filterTahunLalu">Tampilkan Kolom Thn Lalu</label>
                                            </div>
                                        </div>

                                        {{-- Action buttons --}}
                                        <div class="d-flex align-items-center" style="gap: 6px;">
                                            <button type="submit" class="btn btn-primary btn-sm px-3 font-weight-bold">
                                                <i class="fas fa-filter mr-1"></i> Filter
                                            </button>
                                            <a href="{{ route('bank-cashflow.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset filter">
                                                <i class="fas fa-redo mr-1"></i> Reset
                                            </a>
                                        </div>

                                        {{-- Tombol Cetak di kanan --}}
                                        <div class="ml-auto">
                                            <button type="button" id="btnCetak" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-print mr-1"></i> Cetak
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            @if(!empty($tglDari) || !empty($tglSampai) || !empty($selectedBulan) || $showEmpty || $showTahunLalu)
                            <div class="card-footer py-1 px-3 bg-light d-flex align-items-center justify-content-between" style="font-size: 12px;">
                                <div>
                                    <i class="fas fa-info-circle text-primary mr-1"></i>
                                    Menampilkan laporan arus kas: <strong class="text-dark">{{ $labelPeriode }}</strong>
                                </div>
                                <a href="{{ route('bank-cashflow.index') }}" class="text-danger font-weight-bold" style="text-decoration: none;">
                                    <i class="fas fa-times-circle mr-1"></i>Hapus Filter
                                </a>
                            </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="cf-petunjuk-geser mb-1">
                                    <i class="fas fa-arrows-alt-h mr-1"></i>
                                    Geser tabel ke kanan untuk melihat rincian tiap unit/kebun.
                                    Kolom Kode, Reference, Uraian, Realisasi Reg5, dan Regional Office tetap terlihat.
                                </div>
                                <div id="tableCashFlowBank" class="cf-tabel"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL DETAIL TRANSAKSI ARUS KAS (DRILLDOWN) KHUSUS ADMIN --}}
    <div class="modal fade" id="modalDetailCashflow" tabindex="-1" role="dialog" aria-labelledby="modalDetailCashflowLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 95%;">
            <div class="modal-content shadow-lg border-0" style="border-radius: 10px; overflow: hidden;">
                <div class="modal-header text-white py-2 px-3 align-items-center" style="background: #0d3b6e !important;">
                    <h5 class="modal-title font-weight-bold mb-0" id="modalDetailCashflowLabel" style="font-size: 15px;">
                        <i class="fas fa-file-invoice-dollar mr-2 text-warning"></i> Rincian Transaksi Arus Kas
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9; outline: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    {{-- Header Badges & Summary --}}
                    <div class="card mb-3 shadow-none border" style="background: #f8fafc; border-radius: 8px;">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 10px;">
                                <div>
                                    <div class="small text-muted font-weight-bold">Akun / Uraian:</div>
                                    <h6 class="mb-0 font-weight-bold text-dark" id="mdlUraian">-</h6>
                                </div>
                                <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                                    <span class="badge badge-secondary px-2 py-1" id="mdlKodeRef">Kode: -</span>
                                    <span class="badge badge-info px-2 py-1" id="mdlUnit">Unit: -</span>
                                    <span class="badge badge-primary px-2 py-1" id="mdlPeriode">Periode: -</span>
                                    <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 13px;" id="mdlTotalNilai">Total: Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Loading state --}}
                    <div id="mdlLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <div class="mt-2 text-muted font-weight-bold small">Memuat data transaksi...</div>
                    </div>

                    {{-- Table wrapper --}}
                    <div id="mdlTableWrapper" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-sm w-100" id="tblDetailCashflow" style="font-size: 12px;">
                                <thead class="bg-light text-dark">
                                    <tr class="text-center">
                                        <th style="width: 35px;">No.</th>
                                        <th style="min-width: 85px;">Tgl Posting</th>
                                        <th style="min-width: 90px;">No. Dokumen</th>
                                        <th style="min-width: 110px;">Unit / Profit Ctr</th>
                                        <th style="min-width: 130px;">G/L Account</th>
                                        <th style="min-width: 140px;">Offsetting Account</th>
                                        <th style="min-width: 180px;">Keterangan / Text</th>
                                        <th class="text-right" style="min-width: 120px;">Nilai (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="font-weight-bold text-dark" style="background: #e2e8f0;">
                                        <td colspan="7" class="text-right">TOTAL TRANSAKSI :</td>
                                        <td class="text-right font-weight-bold" id="tblTotalFooter">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <span class="small text-muted font-weight-bold" id="mdlRowCount">0 transaksi ditemukan</span>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                var selectedYear = {{ $selectedYear }};
                var prevYear = {{ $prevYear }};
                var showTahunLalu = {{ $showTahunLalu ? 'true' : 'false' }};
                var tableData = @json($reportRows);
                var summary = @json($summary);
                var unitColumns = @json($unitColumns);
                var selectedBulan = @json($selectedBulan);
                var tglDariVal = @json(!empty($tglDari) ? \Carbon\Carbon::parse($tglDari)->format('d-m-Y') : '');
                var tglSampaiVal = @json(!empty($tglSampai) ? \Carbon\Carbon::parse($tglSampai)->format('d-m-Y') : '');
                var isAdmin = {{ auth()->check() && auth()->user()->role === 'admin' ? 'true' : 'false' }};

                // Format baku laporan: pemisah ribuan titik, minus di BELAKANG angka,
                // nilai nol ditulis sebagai tanda hubung agar tabel tidak penuh "0".
                function tulisAngka(val) {
                    var num = Number(val);
                    if (!isFinite(num) || num === 0) return '<span class="cf-nol">-</span>';
                    var teks = Math.abs(num).toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    return num < 0
                        ? '<span class="cf-negatif">' + teks + '-</span>'
                        : teks;
                }

                function formatAngka(cell) {
                    var val = cell.getValue();
                    var formatted = tulisAngka(val);
                    var num = Number(val);
                    if (isAdmin && isFinite(num) && num !== 0) {
                        return '<span class="cf-angka cf-clickable-angka" title="Klik untuk melihat rincian data transaksi">' + formatted + '</span>';
                    }
                    return '<span class="cf-angka">' + formatted + '</span>';
                }

                function escapeHtml(text) {
                    return $('<div>').text(text === null || text === undefined ? '' : text).html();
                }

                function formatUraian(cell) {
                    var data = cell.getRow().getData();
                    var teks = escapeHtml(cell.getValue());
                    if (data.type === 'spacer') return '';
                    if (data.type === 'detail') return '<span class="cf-indent">' + teks + '</span>';
                    return teks;
                }

                function formatKode(cell) {
                    var data = cell.getRow().getData();
                    if (data.type === 'spacer') return '';
                    var nilai = cell.getValue();
                    return nilai ? '<span class="cf-kode">' + escapeHtml(nilai) + '</span>' : '';
                }

                $('#kartuPenerimaan').html(tulisAngka(summary.penerimaan));
                $('#kartuPenerimaanLalu').html(tulisAngka(summary.penerimaan_lalu));
                $('#kartuPengeluaran').html(tulisAngka(summary.pengeluaran));
                $('#kartuPengeluaranLalu').html(tulisAngka(summary.pengeluaran_lalu));
                $('#kartuBersih').html(tulisAngka(summary.bersih));
                $('#kartuBersihLalu').html(tulisAngka(summary.bersih_lalu));

                // ── Handler Klik Sel Angka untuk Drilldown Detail Transaksi ──
                function handleCellClick(e, cell) {
                    if (!isAdmin) return;
                    var val = cell.getValue();
                    var num = Number(val);
                    if (!isFinite(num) || num === 0) return;

                    var rowData = cell.getRow().getData();
                    if (!rowData || rowData.type === 'spacer') return;

                    var field = cell.getField();
                    if (!field || !field.startsWith('values.')) return;

                    var parts = field.split('.');
                    var seriesKey = parts[1]; // 'global', 'regional_office', 'u1', etc.
                    var bucket = parts[2]; // 'current' or 'previous'
                    var year = (bucket === 'current') ? selectedYear : prevYear;

                    var seriesTitle = "Realisasi Reg5 (Global)";
                    if (seriesKey === 'regional_office') {
                        seriesTitle = "Regional Office";
                    } else if (seriesKey.startsWith('u')) {
                        var uFound = unitColumns.find(function (u) { return u.key === seriesKey; });
                        seriesTitle = uFound ? uFound.nama : ("Unit " + seriesKey);
                    }

                    var scope = rowData.scope || [];
                    if (!scope.length && rowData.reference && rowData.reference !== '-') {
                        scope = [rowData.reference];
                    }

                    openDetailModal({
                        uraian: rowData.uraian,
                        kode: rowData.kode || '-',
                        reference: rowData.reference || '-',
                        seriesKey: seriesKey,
                        seriesTitle: seriesTitle,
                        year: year,
                        selectedYear: selectedYear,
                        month: selectedBulan,
                        tglDari: tglDariVal,
                        tglSampai: tglSampaiVal,
                        scope: scope,
                        expectedAmount: num
                    });
                }

                // ── Susunan kolom ──
                // Kiri (dibekukan): Kode | Reference | Uraian | Realisasi Reg5 | Regional Office
                // Kanan (digeser) : satu grup per unit
                var globalSubColumns = [
                    {
                        title: "Realisasi " + selectedYear, field: "values.global.current", width: 155,
                        hozAlign: "right", headerHozAlign: "center", formatter: formatAngka,
                        cellClick: handleCellClick
                    }
                ];

                if (showTahunLalu) {
                    globalSubColumns.push({
                        title: "Realisasi " + prevYear, field: "values.global.previous", width: 155,
                        hozAlign: "right", headerHozAlign: "center", formatter: formatAngka,
                        cellClick: handleCellClick
                    });
                }

                var regionalOfficeSubColumns = [
                    {
                        title: "Realisasi " + selectedYear, field: "values.regional_office.current", width: 155,
                        hozAlign: "right", headerHozAlign: "center", formatter: formatAngka,
                        cellClick: handleCellClick
                    }
                ];

                if (showTahunLalu) {
                    regionalOfficeSubColumns.push({
                        title: "Realisasi " + prevYear, field: "values.regional_office.previous", width: 155,
                        hozAlign: "right", headerHozAlign: "center", formatter: formatAngka,
                        cellClick: handleCellClick
                    });
                }

                var kolom = [
                    {
                        title: "Kode", field: "kode", width: 90, frozen: true,
                        hozAlign: "center", headerHozAlign: "center", formatter: formatKode
                    },
                    {
                        title: "Reference", field: "reference", width: 110, frozen: true,
                        hozAlign: "center", headerHozAlign: "center", formatter: formatKode
                    },
                    {
                        title: "Uraian", field: "uraian", width: 360, frozen: true,
                        hozAlign: "left", headerHozAlign: "center", formatter: formatUraian
                    },
                    {
                        title: "Realisasi Reg5",
                        headerHozAlign: "center",
                        frozen: true,
                        columns: globalSubColumns
                    },
                    {
                        title: "Regional Office",
                        headerHozAlign: "center",
                        frozen: true,
                        columns: regionalOfficeSubColumns
                    }
                ];

                unitColumns.forEach(function (unit) {
                    var unitSubColumns = [
                        {
                            title: "Realisasi " + selectedYear, field: "values." + unit.key + ".current",
                            width: 140, hozAlign: "right", headerHozAlign: "center", formatter: formatAngka,
                            cellClick: handleCellClick
                        }
                    ];
                    if (showTahunLalu) {
                        unitSubColumns.push({
                            title: "Realisasi " + prevYear, field: "values." + unit.key + ".previous",
                            width: 140, hozAlign: "right", headerHozAlign: "center", formatter: formatAngka,
                            cellClick: handleCellClick
                        });
                    }
                    kolom.push({
                        title: unit.nama,
                        headerHozAlign: "center",
                        columns: unitSubColumns
                    });
                });

                var table = new Tabulator("#tableCashFlowBank", {
                    data: tableData,
                    // fitData (bukan fitColumns): kolom mempertahankan lebarnya sehingga
                    // tabel melebihi layar dan bisa digeser ke kanan.
                    layout: 'fitData',
                    height: '68vh',
                    columnHeaderVertAlign: 'middle',
                    movableColumns: false,
                    columnDefaults: { headerSort: false, resizable: true, minWidth: 30, variableHeight: true },
                    placeholder: "<div class='py-4 text-muted'><i class='fas fa-info-circle mr-1'></i> Belum ada data Cash Flow untuk periode ini.</div>",
                    rowFormatter: function (row) {
                        row.getElement().classList.add('cf-' + row.getData().type);
                    },
                    columns: kolom
                });

                // Baris jenjang tidak memakai kolom Kode & Reference, jadi ketiga kolom
                // kiri digabung (ala colspan). Karena kolomnya dibekukan, sel gabungan
                // juga perlu dipaksa menempel di tepi kiri: Tabulator memberi tiap sel
                // beku offset `left` sendiri, dan offset milik Uraian akan menyisakan
                // celah sebesar lebar dua kolom yang disembunyikan.
                var MERGE_TYPES = ['section', 'subsection', 'subtotal', 'total', 'net', 'closing', 'spacer'];
                var MERGE_FIELDS = ['kode', 'reference', 'uraian'];

                function gabungSelJudul() {
                    var lebar = 0;
                    MERGE_FIELDS.forEach(function (f) {
                        var col = table.getColumn(f);
                        if (col) lebar += col.getWidth();
                    });
                    if (!lebar) return;

                    document.querySelectorAll('#tableCashFlowBank .tabulator-row').forEach(function (rowEl) {
                        var perluGabung = MERGE_TYPES.some(function (t) {
                            return rowEl.classList.contains('cf-' + t);
                        });
                        if (!perluGabung) return;

                        var selUraian = null;
                        rowEl.querySelectorAll('.tabulator-cell').forEach(function (sel) {
                            var f = sel.getAttribute('tabulator-field');
                            if (MERGE_FIELDS.indexOf(f) === -1) return;
                            if (f === 'uraian') selUraian = sel;
                            else sel.style.display = 'none';
                        });

                        if (selUraian) {
                            selUraian.style.width = lebar + 'px';
                            selUraian.style.maxWidth = lebar + 'px';
                            selUraian.style.left = '0px';
                        }
                    });
                }

                table.on('renderComplete', gabungSelJudul);
                table.on('columnResized', function () { setTimeout(gabungSelJudul, 0); });

                // Perubahan penyaring -> muat ulang halaman dengan parameter query
                function applyFilter() {
                    var url = new URL(window.location.href);
                    url.searchParams.set('tahun', $('#filterTahun').val());

                    var bulan = $('#filterBulan').val();
                    if (bulan) {
                        url.searchParams.set('bulan', bulan);
                    } else {
                        url.searchParams.delete('bulan');
                    }

                    if ($('#filterSemua').is(':checked')) {
                        url.searchParams.set('semua', 1);
                    } else {
                        url.searchParams.delete('semua');
                    }

                    if ($('#filterTahunLalu').is(':checked')) {
                        url.searchParams.set('tahun_lalu', 1);
                    } else {
                        url.searchParams.delete('tahun_lalu');
                    }

                    window.location.href = url.toString();
                }

                // ── Inisialisasi Flatpickr Tanggal (dd-mm-yyyy) ──
                var fpDari = flatpickr("#filterTglDari", {
                    dateFormat: "d-m-Y",
                    allowInput: true,
                    locale: "id"
                });

                var fpSampai = flatpickr("#filterTglSampai", {
                    dateFormat: "d-m-Y",
                    allowInput: true,
                    locale: "id"
                });

                function syncDates() {
                    var thn = $('#filterTahun').val();
                    var bln = $('#filterBulan').val();
                    if (thn && bln) {
                        var y = parseInt(thn);
                        var m = parseInt(bln);
                        var lastDay = new Date(y, m, 0).getDate();
                        var padM = String(m).padStart(2, '0');
                        var padLastDay = String(lastDay).padStart(2, '0');
                        fpDari.setDate('01-' + padM + '-' + y, true);
                        fpSampai.setDate(padLastDay + '-' + padM + '-' + y, true);
                    } else if (thn && !bln) {
                        fpDari.setDate('01-01-' + thn, true);
                        fpSampai.setDate('31-12-' + thn, true);
                    }
                }

                $('#filterTahun, #filterBulan').on('change', syncDates);
                $('#btnCetak').on('click', function () { window.print(); });

                // ── Inisialisasi & Pengambilan Data Modal Detail Transaksi Arus Kas ──
                var dtDetail = null;

                function formatRupiah(num) {
                    var n = Number(num);
                    if (!isFinite(n)) return 'Rp 0';
                    var abs = Math.abs(n).toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    return (n < 0 ? 'Rp -' : 'Rp ') + abs;
                }

                function openDetailModal(params) {
                    $('#mdlUraian').text(params.uraian || '-');
                    var kodeRef = '';
                    if (params.kode && params.kode !== '-') kodeRef += 'Kode: ' + params.kode;
                    if (params.reference && params.reference !== '-') {
                        kodeRef += (kodeRef ? ' | ' : '') + 'Ref: ' + params.reference;
                    }
                    if (kodeRef) {
                        $('#mdlKodeRef').text(kodeRef).show();
                    } else {
                        $('#mdlKodeRef').hide();
                    }

                    $('#mdlUnit').text('Unit: ' + params.seriesTitle);

                    var periodeStr = '';
                    if (params.tglDari && params.tglSampai) {
                        periodeStr = params.tglDari + ' s/d ' + params.tglSampai;
                    } else if (params.tglSampai) {
                        periodeStr = 's/d ' + params.tglSampai;
                    } else if (params.tglDari) {
                        periodeStr = 'Mulai ' + params.tglDari;
                    } else if (params.month) {
                        var mText = $('#filterBulan option[value="' + params.month + '"]').text() || params.month;
                        periodeStr = mText + ' ' + params.year;
                    } else {
                        periodeStr = 'Tahun ' + params.year;
                    }
                    $('#mdlPeriode').text('Periode: ' + periodeStr);
                    $('#mdlTotalNilai').text('Total: ' + formatRupiah(params.expectedAmount));

                    $('#mdlLoading').show();
                    $('#mdlTableWrapper').hide();
                    $('#mdlRowCount').text('Memuat data transaksi...');
                    $('#modalDetailCashflow').modal('show');

                    if (dtDetail) {
                        dtDetail.destroy();
                        dtDetail = null;
                    }
                    $('#tblDetailCashflow tbody').empty();

                    $.ajax({
                        url: "{{ route('bank-cashflow.detail') }}",
                        type: "GET",
                        data: {
                            series: params.seriesKey,
                            tahun: params.year,
                            selected_year: params.selectedYear,
                            bulan: params.month,
                            tgl_dari: params.tglDari,
                            tgl_sampai: params.tglSampai,
                            scope: params.scope
                        },
                        dataType: "json",
                        success: function (res) {
                            $('#mdlLoading').hide();
                            $('#mdlTableWrapper').show();

                            if (res.success && res.data && res.data.length > 0) {
                                var rowsHtml = '';
                                var totalSum = 0;
                                res.data.forEach(function (item, idx) {
                                    totalSum += item.amount;
                                    var valRupiah = Number(item.amount).toLocaleString('id-ID', { maximumFractionDigits: 0 });
                                    var amountHtml = item.amount < 0
                                        ? '<span class="text-danger font-weight-bold">(' + valRupiah + ')</span>'
                                        : '<span class="text-dark">' + valRupiah + '</span>';

                                    rowsHtml += '<tr>' +
                                        '<td class="text-center">' + (idx + 1) + '</td>' +
                                        '<td class="text-center">' + escapeHtml(item.posting_date) + '</td>' +
                                        '<td class="text-center font-weight-bold">' + escapeHtml(item.document_number) + '</td>' +
                                        '<td>' + escapeHtml(item.unit) + '</td>' +
                                        '<td>' + escapeHtml(item.account) + '<br><small class="text-muted">' + escapeHtml(item.gl_account_desc) + '</small></td>' +
                                        '<td>' + escapeHtml(item.offsetting_account) + '<br><small class="text-muted">' + escapeHtml(item.name_of_offsetting_account) + '</small></td>' +
                                        '<td>' + escapeHtml(item.text !== '-' ? item.text : item.uraian) + '</td>' +
                                        '<td class="text-right font-weight-bold">' + amountHtml + '</td>' +
                                        '</tr>';
                                });

                                $('#tblDetailCashflow tbody').html(rowsHtml);
                                $('#tblTotalFooter').html(formatRupiah(totalSum));
                                $('#mdlRowCount').text(res.data.length + ' transaksi ditemukan');

                                dtDetail = $('#tblDetailCashflow').DataTable({
                                    paging: true,
                                    pageLength: 15,
                                    lengthMenu: [10, 15, 25, 50, 100],
                                    searching: true,
                                    ordering: true,
                                    order: [[1, 'asc']],
                                    language: {
                                        search: "Cari:",
                                        lengthMenu: "Tampilkan _MENU_ data",
                                        zeroRecords: "Tidak ada transaksi yang cocok",
                                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
                                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 transaksi",
                                        paginate: {
                                            first: "Awal",
                                            last: "Akhir",
                                            next: "›",
                                            previous: "‹"
                                        }
                                    }
                                });
                            } else {
                                $('#tblDetailCashflow tbody').html('<tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada transaksi ditemukan untuk rincian ini.</td></tr>');
                                $('#tblTotalFooter').text('Rp 0');
                                $('#mdlRowCount').text('0 transaksi ditemukan');
                            }
                        },
                        error: function (xhr) {
                            $('#mdlLoading').hide();
                            $('#mdlTableWrapper').show();
                            $('#tblDetailCashflow tbody').html('<tr><td colspan="8" class="text-center py-4 text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat data transaksi. Silakan coba lagi.</td></tr>');
                            $('#mdlRowCount').text('Gagal memuat');
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
