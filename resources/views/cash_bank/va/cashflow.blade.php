@extends("layouts/va_layout")
@section('content')
    @include('cash_bank.partials.gaya-arus-kas')

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

                        @include('cash_bank.partials.kop-laporan', [
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
                                <div id="tableCashFlow" class="cf-tabel" style="min-height: 250px;"></div>
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

                // Baris jenjang (bagian, sub-bagian, total, jumlah) tidak memakai
                // kolom Kode & Reference. Ketiga kolom kiri digabung jadi satu sel
                // supaya judulnya mulai dari tepi kiri tabel dan susunan laporan
                // terbaca rapi. Tabulator tidak punya colspan bawaan, jadi caranya:
                // sembunyikan dua sel pertama lalu alihkan lebarnya ke sel Uraian
                // — pola yang sama dipakai baris TOTAL di halaman Virtual Account.
                var MERGE_TYPES = ['section', 'subsection', 'subtotal', 'total', 'net', 'closing', 'spacer'];
                var MERGE_FIELDS = ['kode', 'reference', 'uraian'];

                function gabungSelJudul() {
                    var lebar = 0;
                    MERGE_FIELDS.forEach(function (f) {
                        var col = table.getColumn(f);
                        if (col) lebar += col.getWidth();
                    });
                    if (!lebar) return;

                    document.querySelectorAll('#tableCashFlow .tabulator-row').forEach(function (rowEl) {
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
