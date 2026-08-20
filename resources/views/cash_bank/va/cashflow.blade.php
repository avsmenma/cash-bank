@extends("layouts/va_layout")
@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('plugins/tabulator/tabulator_semanticui.min.css') }}">
        <style>
            /* Tampilan kompak DISETEL LEWAT UKURAN HURUF, bukan `zoom`.
               Catatan penting: `zoom: 0.8` merusak layout fitColumns Tabulator —
               ia membaca lebar wadah hasil getBoundingClientRect() yang sudah
               terkena zoom (mis. 1263px) lalu menetapkan lebar kolom dalam satuan
               CSS px pada wadah yang sebenarnya 1582px, sehingga tabel selalu
               kurang selebar wadah x (1 - zoom) dan menyisakan ruang kosong. */
            section.content {
                font-size: 13px;
            }

            /* ===== Palet mengikuti berkas baku "Laporan Arus Kas" PTPN ===== */
            :root {
                --cf-navy: #1E3A5F;
                --cf-section: #A9D18E;
                --cf-subsection: #E2F0D9;
                --cf-group: #F3F8EF;
                --cf-subtotal: #C5E0B4;
                --cf-total: #548135;
                --cf-garis: #C9D4DF;
            }

            /* ── Kartu ringkasan ── */
            .cf-kartu {
                border: 1px solid var(--cf-garis);
                border-left: 5px solid var(--cf-navy);
                border-radius: 4px;
                background: #fff;
                padding: 12px 14px;
                height: 100%;
            }

            .cf-kartu.masuk { border-left-color: #1E7E34; }
            .cf-kartu.keluar { border-left-color: #C82333; }
            .cf-kartu.bersih { border-left-color: var(--cf-total); }

            .cf-kartu .cf-kartu-label {
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .08em;
                text-transform: uppercase;
                color: #6B7280;
            }

            .cf-kartu .cf-kartu-nilai {
                font-size: 17px;
                font-weight: 700;
                line-height: 1.25;
                color: var(--cf-navy);
            }

            .cf-kartu.masuk .cf-kartu-nilai { color: #1E7E34; }
            .cf-kartu.keluar .cf-kartu-nilai { color: #C82333; }

            .cf-kartu .cf-kartu-banding {
                font-size: 10px;
                color: #6B7280;
                border-top: 1px dashed var(--cf-garis);
                margin-top: 6px;
                padding-top: 5px;
            }

            /* ── Tabulator: kepala tabel tema Navy standar Cash Bank ── */
            #tableCashFlow {
                font-size: 12.5px;
            }

            #tableCashFlow .tabulator-header,
            #tableCashFlow .tabulator-header .tabulator-col {
                background-color: var(--cf-navy) !important;
                color: #fff !important;
                border-color: rgba(255, 255, 255, .25) !important;
            }

            #tableCashFlow .tabulator-header .tabulator-col .tabulator-col-title {
                color: #fff;
                font-weight: 600;
                white-space: normal;
                text-align: center;
            }

            #tableCashFlow .tabulator-header .tabulator-col {
                border-right: 2px solid rgba(255, 255, 255, .9) !important;
            }

            #tableCashFlow .tabulator-cell {
                white-space: normal;
                overflow-wrap: break-word;
                border-right: 1px solid var(--cf-garis) !important;
                padding-top: 5px;
                padding-bottom: 5px;
            }

            #tableCashFlow .tabulator-row {
                border-bottom: 1px solid var(--cf-garis) !important;
            }

            /* ── Pewarnaan baris menurut jenjang laporan ── */
            #tableCashFlow .tabulator-row.cf-section .tabulator-cell {
                background: var(--cf-section) !important;
                font-weight: 700;
                color: #21351A;
                letter-spacing: .03em;
            }

            #tableCashFlow .tabulator-row.cf-subsection .tabulator-cell {
                background: var(--cf-subsection) !important;
                font-weight: 700;
                color: #33502A;
            }

            #tableCashFlow .tabulator-row.cf-group .tabulator-cell {
                background: var(--cf-group) !important;
                font-weight: 600;
                color: #2B3A4A;
            }

            #tableCashFlow .tabulator-row.cf-detail .tabulator-cell {
                background: #fff !important;
            }

            #tableCashFlow .tabulator-row.cf-plain .tabulator-cell {
                background: #FBFCFD !important;
            }

            #tableCashFlow .tabulator-row.cf-subtotal .tabulator-cell {
                background: var(--cf-subtotal) !important;
                font-weight: 700;
                color: #22331A;
            }

            #tableCashFlow .tabulator-row.cf-total .tabulator-cell {
                background: var(--cf-total) !important;
                color: #fff !important;
                font-weight: 700;
                letter-spacing: .03em;
            }

            #tableCashFlow .tabulator-row.cf-net .tabulator-cell {
                background: #375623 !important;
                color: #fff !important;
                font-weight: 700;
            }

            #tableCashFlow .tabulator-row.cf-closing .tabulator-cell {
                background: var(--cf-navy) !important;
                color: #fff !important;
                font-weight: 700;
                border-top: 2px solid #fff !important;
            }

            /* Baris antar-bagian: pemisah tipis tanpa isi */
            #tableCashFlow .tabulator-row.cf-spacer .tabulator-cell {
                background: #EEF2F6 !important;
                padding-top: 2px !important;
                padding-bottom: 2px !important;
                border-right-color: #EEF2F6 !important;
            }

            /* Hover hanya untuk baris data agar blok warna tidak "berkedip" */
            #tableCashFlow .tabulator-row.cf-detail:hover .tabulator-cell,
            #tableCashFlow .tabulator-row.cf-plain:hover .tabulator-cell {
                background: #EAF2FB !important;
            }

            /* ── Angka: gaya SAP, tanda minus di belakang seperti berkas Excel ── */
            .cf-angka { font-variant-numeric: tabular-nums; }
            .cf-negatif { color: #C82333; }
            .cf-nol { color: #B7C0CA; }

            #tableCashFlow .tabulator-row.cf-total .cf-negatif,
            #tableCashFlow .tabulator-row.cf-net .cf-negatif,
            #tableCashFlow .tabulator-row.cf-closing .cf-negatif { color: #FFD5D5; }

            #tableCashFlow .tabulator-row.cf-total .cf-nol,
            #tableCashFlow .tabulator-row.cf-net .cf-nol,
            #tableCashFlow .tabulator-row.cf-closing .cf-nol { color: rgba(255, 255, 255, .6); }

            /* Indentasi uraian mengikuti kedalaman jenjang */
            .cf-indent { padding-left: 20px; display: inline-block; }
            .cf-kode { font-family: "Consolas", "Courier New", monospace; font-size: 10px; }

            @media print {
                .cf-no-print { display: none !important; }
            }
        </style>
    @endpush

    {{-- Judul halaman & breadcrumb sengaja ditiadakan: identitas laporan sudah
         tertulis pada kop tabel, dan menghapusnya menaikkan posisi tabel. --}}
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="invoice p-3 mb-3">

                        {{-- Kop laporan, meniru berkas baku Laporan Arus Kas --}}
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

                        @include('cash_bank.va.partials.kop-laporan', [
                            'judul' => 'Laporan Arus Kas',
                            'unit' => $va->nama_tujuan,
                            'periode' => 'Periode ' . $labelPeriode . ' · pembanding Tahun ' . $prevYear,
                        ])

                        {{-- Kartu ringkasan --}}
                        <div class="row mb-3">
                            <div class="col-md-4 mb-2">
                                <div class="cf-kartu masuk">
                                    <div class="cf-kartu-label">Total Penerimaan</div>
                                    <div class="cf-kartu-nilai" id="kartuPenerimaan">-</div>
                                    <div class="cf-kartu-banding">{{ $prevYear }}: <span id="kartuPenerimaanLalu">-</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="cf-kartu keluar">
                                    <div class="cf-kartu-label">Total Pengeluaran</div>
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

                        {{-- Penyaring: bulan, tahun, kelengkapan akun --}}
                        <div class="row mb-3 cf-no-print">
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

                        {{-- Wadah tabel Tabulator --}}
                        <div class="row">
                            <div class="col-12">
                                <div id="tableCashFlow" style="min-height: 250px;"></div>
                            </div>
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
                var selectedYear = {{ $selectedYear }};
                var prevYear = {{ $prevYear }};
                var tableData = @json($reportRows);
                var summary = @json($summary);

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

                // Uraian baris detail diberi indentasi supaya jenjang terbaca.
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

                // Kartu ringkasan memakai format angka yang sama dengan tabel.
                $('#kartuPenerimaan').html(tulisAngka(summary.penerimaan));
                $('#kartuPenerimaanLalu').html(tulisAngka(summary.penerimaan_lalu));
                $('#kartuPengeluaran').html(tulisAngka(summary.pengeluaran));
                $('#kartuPengeluaranLalu').html(tulisAngka(summary.pengeluaran_lalu));
                $('#kartuBersih').html(tulisAngka(summary.bersih));
                $('#kartuBersihLalu').html(tulisAngka(summary.bersih_lalu));

                // Buang lebar kolom simpanan versi lama. Persistence lebar sengaja
                // TIDAK dipakai di halaman ini: Tabulator menyalin lebar tersimpan
                // ke definisi setiap kolom, dan pada layout fitColumns kolom yang
                // punya width dianggap tetap — akibatnya tabel berhenti memenuhi
                // lebar layar begitu lebar pernah tersimpan sekali saja.
                try {
                    localStorage.removeItem('tabulator-cb-va-cashflow-columns');
                } catch (e) { /* localStorage diblokir browser: abaikan */ }

                var table = new Tabulator("#tableCashFlow", {
                    data: tableData,
                    // Selalu penuh selebar wadah; sisa ruang dibagi menurut widthGrow.
                    layout: 'fitColumns',
                    columnHeaderVertAlign: 'middle',
                    movableColumns: false,
                    columnDefaults: { headerSort: false, resizable: true, minWidth: 30, variableHeight: true },
                    renderVertical: 'basic',
                    placeholder: "<div class='py-4 text-muted'><i class='fas fa-info-circle mr-1'></i> Belum ada data Cash Flow untuk periode ini.</div>",
                    // Warna & tebal huruf tiap baris ditentukan jenjangnya (type).
                    rowFormatter: function (row) {
                        row.getElement().classList.add('cf-' + row.getData().type);
                    },
                    // Dua kolom kode dibiarkan sempit & tetap; sisa lebar layar
                    // dibagi ke Uraian (porsi terbesar) dan dua kolom nilai.
                    columns: [
                        {
                            title: "Kode",
                            field: "kode",
                            width: 90,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: formatKode
                        },
                        {
                            title: "Reference",
                            field: "reference",
                            width: 110,
                            hozAlign: "center",
                            headerHozAlign: "center",
                            formatter: formatKode
                        },
                        {
                            title: "Uraian",
                            field: "uraian",
                            minWidth: 320,
                            widthGrow: 4,
                            hozAlign: "left",
                            headerHozAlign: "center",
                            formatter: formatUraian
                        },
                        {
                            title: "Realisasi " + selectedYear,
                            field: "current",
                            minWidth: 170,
                            widthGrow: 1,
                            hozAlign: "right",
                            headerHozAlign: "center",
                            formatter: formatAngka
                        },
                        {
                            title: "Realisasi " + prevYear,
                            field: "previous",
                            minWidth: 170,
                            widthGrow: 1,
                            hozAlign: "right",
                            headerHozAlign: "center",
                            formatter: formatAngka
                        }
                    ]
                });

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
