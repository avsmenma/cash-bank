@extends('layouts.index')
@section('content')
    @include('cash_bank.partials.gaya-arus-kas')

    @push('styles')
        <style>
            /* Tabel admin sangat lebar (satu grup kolom per unit), jadi kolom kiri
               dibekukan agar Kode/Reference/Uraian + Realisasi Global tetap terlihat
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
        </style>
    @endpush

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="invoice p-3 mb-3">

                        @php
                            $namaBulan = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                            ];
                            $labelPeriode = $selectedBulan !== ''
                                ? ($namaBulan[str_pad($selectedBulan, 2, '0', STR_PAD_LEFT)] ?? $selectedBulan) . ' ' . $selectedYear
                                : 'Tahun ' . $selectedYear;
                        @endphp

                        @include('cash_bank.partials.kop-laporan', [
                            'judul' => 'Laporan Arus Kas',
                            'unit' => 'Seluruh Unit & Kebun — ' . $unitColumns->count() . ' unit',
                            'periode' => 'Periode ' . $labelPeriode . ' · pembanding Tahun ' . $prevYear,
                        ])

                        {{-- Kartu ringkasan memakai angka global (gabungan seluruh unit) --}}
                        <div class="row mb-3">
                            <div class="col-md-4 mb-2">
                                <div class="cf-kartu masuk">
                                    <div class="cf-kartu-label">Total Penerimaan (Global)</div>
                                    <div class="cf-kartu-nilai" id="kartuPenerimaan">-</div>
                                    <div class="cf-kartu-banding">{{ $prevYear }}: <span id="kartuPenerimaanLalu">-</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="cf-kartu keluar">
                                    <div class="cf-kartu-label">Total Pengeluaran (Global)</div>
                                    <div class="cf-kartu-nilai" id="kartuPengeluaran">-</div>
                                    <div class="cf-kartu-banding">{{ $prevYear }}: <span id="kartuPengeluaranLalu">-</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="cf-kartu bersih">
                                    <div class="cf-kartu-label">Kenaikan (Penurunan) Arus Kas Bersih</div>
                                    <div class="cf-kartu-nilai" id="kartuBersih">-</div>
                                    <div class="cf-kartu-banding">{{ $prevYear }}: <span id="kartuBersihLalu">-</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2 cf-no-print">
                            <div class="col-12 d-flex align-items-center flex-wrap">
                                <label for="filterBulan" class="mr-2 mb-0 font-weight-bold">Bulan</label>
                                <select id="filterBulan" class="form-control form-control-sm mr-3" style="width: 140px;">
                                    <option value="">Semua</option>
                                    @foreach ($namaBulan as $val => $nama)
                                        <option value="{{ $val }}" {{ $selectedBulan == $val ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>

                                <label for="filterTahun" class="mr-2 mb-0 font-weight-bold">Tahun</label>
                                <select id="filterTahun" class="form-control form-control-sm mr-3" style="width: 120px;">
                                    @foreach ($years as $y)
                                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>

                                <div class="custom-control custom-checkbox mr-3">
                                    <input type="checkbox" class="custom-control-input" id="filterSemua" {{ $showEmpty ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="filterSemua">Tampilkan semua akun</label>
                                </div>

                                <button type="button" id="btnCetak" class="btn btn-sm btn-outline-secondary ml-auto">
                                    <i class="fas fa-print mr-1"></i> Cetak
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="cf-petunjuk-geser mb-1">
                                    <i class="fas fa-arrows-alt-h mr-1"></i>
                                    Geser tabel ke kanan untuk melihat rincian tiap unit/kebun.
                                    Kolom Kode, Reference, Uraian, dan Realisasi Global tetap terlihat.
                                </div>
                                <div id="tableCashFlowBank" class="cf-tabel"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            $(document).ready(function () {
                var selectedYear = {{ $selectedYear }};
                var prevYear = {{ $prevYear }};
                var tableData = @json($reportRows);
                var summary = @json($summary);
                var unitColumns = @json($unitColumns);

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
                    return '<span class="cf-angka">' + tulisAngka(cell.getValue()) + '</span>';
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

                // ── Susunan kolom ──
                // Kiri (dibekukan): Kode | Reference | Uraian | Realisasi Global
                // Kanan (digeser) : satu grup per unit, masing-masing 2 sub-kolom tahun
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
                        title: "Realisasi Global",
                        headerHozAlign: "center",
                        frozen: true,
                        columns: [
                            {
                                title: String(selectedYear), field: "values.global.current", width: 155,
                                hozAlign: "right", headerHozAlign: "center", formatter: formatAngka
                            },
                            {
                                title: String(prevYear), field: "values.global.previous", width: 155,
                                hozAlign: "right", headerHozAlign: "center", formatter: formatAngka
                            }
                        ]
                    }
                ];

                unitColumns.forEach(function (unit) {
                    kolom.push({
                        title: unit.nama,
                        headerHozAlign: "center",
                        columns: [
                            {
                                title: "Realisasi " + selectedYear, field: "values." + unit.key + ".current",
                                width: 140, hozAlign: "right", headerHozAlign: "center", formatter: formatAngka
                            },
                            {
                                title: "Realisasi " + prevYear, field: "values." + unit.key + ".previous",
                                width: 140, hozAlign: "right", headerHozAlign: "center", formatter: formatAngka
                            }
                        ]
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

                    window.location.href = url.toString();
                }

                $('#filterTahun, #filterBulan, #filterSemua').on('change', applyFilter);
                $('#btnCetak').on('click', function () { window.print(); });
            });
        </script>
    @endpush
@endsection
