@extends("layouts/va_layout")
@section('content')
    @include('cash_bank.partials.gaya-arus-kas')

    @push('styles')
        <style>
            /* Sel angka yang punya isi bisa diklik untuk melihat rinciannya */
            .cf-tabel .tabulator-cell.cf-klik {
                cursor: pointer;
            }

            .cf-tabel .tabulator-cell.cf-klik:hover {
                outline: 2px solid var(--cf-navy);
                outline-offset: -2px;
                background: #E8F1FD !important;
            }

            .cf-rincian-kop {
                background: #F3F6FA;
                border: 1px solid var(--cf-garis);
                border-radius: 4px;
                padding: 8px 12px;
                font-size: 12px;
            }

            .cf-rincian-kop .cf-rincian-nilai {
                font-size: 17px;
                font-weight: 700;
                font-variant-numeric: tabular-nums;
            }

            #tabelRincian {
                font-size: 12px;
            }

            #tabelRincian .tabulator-header,
            #tabelRincian .tabulator-header .tabulator-col {
                background-color: var(--cf-navy) !important;
                color: #fff !important;
            }

            #tabelRincian .tabulator-header .tabulator-col .tabulator-col-title {
                color: #fff;
                font-weight: 600;
                text-align: center;
                white-space: normal;
            }

            /* Sembunyikan seluruh panah sorting pada modal rincian */
            #tabelRincian .tabulator-col .tabulator-arrow,
            #tabelRincian .tabulator-col-sorter {
                display: none !important;
            }

            /* Agar data teks panjang membungkus ke bawah (wrap) dan tampil penuh tanpa terpotong */
            #tabelRincian .tabulator-cell {
                white-space: normal !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                line-height: 1.4 !important;
                padding: 8px 6px !important;
                display: flex;
                align-items: center;
            }

            #tabelRincian .tabulator-row {
                min-height: 38px;
                height: auto !important;
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

    {{-- Modal rincian: transaksi penyusun angka yang diklik --}}
    <div class="modal fade" id="modalRincian" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #1E3A5F; color: #fff;">
                    <h5 class="modal-title">
                        <i class="fas fa-search-dollar mr-1"></i> Rincian Angka
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="cf-rincian-kop mb-2">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div class="mr-3">
                                <div class="font-weight-bold" id="rincianUraian">-</div>
                                <div class="text-muted" id="rincianKeterangan">-</div>
                            </div>
                            <div class="text-right">
                                <div class="text-muted" style="font-size: 10px; letter-spacing: .08em;">JUMLAH</div>
                                <div class="cf-rincian-nilai" id="rincianTotal">-</div>
                                <div class="text-muted" id="rincianJumlahBaris">-</div>
                            </div>
                        </div>
                    </div>

                    <div id="rincianPesan" class="alert alert-warning py-2 px-3 d-none" style="font-size: 12px;"></div>
                    <div id="tabelRincian"></div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

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
                    // Warna & tebal huruf tiap baris ditentukan jenjangnya (type),
                    // sekaligus menandai sel angka yang bisa diklik untuk rincian.
                    rowFormatter: function (row) {
                        var data = row.getData();
                        row.getElement().classList.add('cf-' + data.type);

                        row.getCells().forEach(function (cell) {
                            var field = cell.getField();
                            if (field !== 'current' && field !== 'previous') return;
                            if (bisaDirinci(data, field)) {
                                cell.getElement().classList.add('cf-klik');
                                cell.getElement().setAttribute('title', 'Klik untuk melihat rincian');
                            }
                        });
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
                            formatter: formatAngka,
                            cellClick: function (e, cell) { bukaRincian(cell); }
                        },
                        {
                            title: "Realisasi " + prevYear,
                            field: "previous",
                            minWidth: 170,
                            widthGrow: 1,
                            hozAlign: "right",
                            headerHozAlign: "center",
                            formatter: formatAngka,
                            cellClick: function (e, cell) { bukaRincian(cell); }
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

                // ============ RINCIAN ANGKA (drill-down ala pivot) ============
                var URL_RINCIAN = @json(route('va.cashflow.rincian'));
                var bulanAktif = @json((string) $selectedBulan);
                var namaBulan = @json($namaBulan);
                var tabelRincian = null;
                var siapRincian = null; // Promise: tabel rincian selesai dibangun

                // Sebuah angka bisa dirinci bila baris membawa cakupan reference key
                // dan nilainya tidak nol — mengklik nol tidak akan menghasilkan apa pun.
                function bisaDirinci(data, field) {
                    if (!data.scope || !data.scope.length) return false;
                    return Number(field === 'current' ? data.current : data.previous) !== 0;
                }

                function formatTeksBungkus(cell) {
                    var val = cell.getValue();
                    if (!val) return '<span class="cf-nol">-</span>';
                    return '<div style="white-space: normal; word-break: break-word; line-height: 1.4;">' + escapeHtml(val) + '</div>';
                }

                // Tabulator baru boleh menerima setData/redraw SETELAH selesai
                // dibangun; memanggilnya lebih awal melempar error internal
                // (verticalFillMode null). Karena itu pembangunannya dibungkus
                // Promise dan semua pemakaian menunggu promise tersebut.
                function siapkanTabelRincian() {
                    if (siapRincian) return siapRincian;

                    siapRincian = new Promise(function (resolve) {
                        tabelRincian = new Tabulator("#tabelRincian", {
                            data: [],
                            layout: 'fitColumns',
                            height: '52vh',
                            columnHeaderVertAlign: 'middle',
                            columnDefaults: { headerSort: false, minWidth: 60, variableHeight: true },
                            placeholder: "<div class='py-4 text-muted'>Tidak ada transaksi pada cakupan ini.</div>",
                            columns: [
                                {
                                    title: "Tanggal", field: "tanggal", width: 95, hozAlign: "center",
                                    headerHozAlign: "center", headerSort: false,
                                    formatter: function (cell) {
                                        var v = cell.getValue();
                                        if (!v) return '<span class="cf-nol">-</span>';
                                        var p = String(v).split('-');
                                        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : escapeHtml(v);
                                    }
                                },
                                {
                                    title: "No. Dokumen", field: "dokumen", width: 115, hozAlign: "center",
                                    headerHozAlign: "center", headerSort: false,
                                    formatter: function (cell) {
                                        return '<span class="cf-kode" style="white-space: normal; word-break: break-all;">' + escapeHtml(cell.getValue()) + '</span>';
                                    }
                                },
                                {
                                    title: "Reference", field: "reference", width: 100, hozAlign: "center",
                                    headerHozAlign: "center", headerSort: false,
                                    formatter: function (cell) {
                                        return '<span class="cf-kode">' + escapeHtml(cell.getValue()) + '</span>';
                                    }
                                },
                                {
                                    title: "Uraian", field: "uraian", minWidth: 160, widthGrow: 2,
                                    headerHozAlign: "center", headerSort: false,
                                    formatter: formatTeksBungkus
                                },
                                {
                                    title: "Keterangan", field: "keterangan", minWidth: 180, widthGrow: 3,
                                    headerHozAlign: "center", headerSort: false,
                                    formatter: formatTeksBungkus
                                },
                                {
                                    title: "Akun Lawan", field: "lawan", minWidth: 160, widthGrow: 2,
                                    headerHozAlign: "center", headerSort: false,
                                    formatter: formatTeksBungkus
                                },
                                {
                                    title: "Nominal", field: "amount", width: 140, hozAlign: "right",
                                    headerHozAlign: "center", headerSort: false,
                                    formatter: function (cell) {
                                        return '<span class="cf-angka">' + tulisAngka(cell.getValue()) + '</span>';
                                    }
                                }
                            ]
                        });

                        tabelRincian.on("tableBuilt", function () { resolve(tabelRincian); });
                    });

                    return siapRincian;
                }

                function bukaRincian(cell) {
                    var data = cell.getRow().getData();
                    var field = cell.getField();
                    if (!bisaDirinci(data, field)) return;

                    var tahun = (field === 'current') ? selectedYear : prevYear;
                    var nilai = (field === 'current') ? data.current : data.previous;

                    var jejak = [];
                    if (data.kode) jejak.push('Kode ' + data.kode);
                    if (data.reference && data.reference !== '-') jejak.push('Reference ' + data.reference);
                    jejak.push('Realisasi ' + tahun);
                    if (bulanAktif && namaBulan[bulanAktif]) jejak.push(namaBulan[bulanAktif] + ' ' + tahun);

                    $('#rincianUraian').text(data.uraian);
                    $('#rincianKeterangan').text(jejak.join(' · '));
                    $('#rincianTotal').html(tulisAngka(nilai));
                    $('#rincianJumlahBaris').text('memuat…');
                    $('#rincianPesan').addClass('d-none').text('');

                    $('#modalRincian').modal('show');

                    var params = new URLSearchParams();
                    params.set('tahun', tahun);
                    if (bulanAktif) params.set('bulan', bulanAktif);
                    data.scope.forEach(function (s) { params.append('scope[]', s); });

                    siapkanTabelRincian().then(function (tabel) {
                        tabel.setData([]);

                        $.getJSON(URL_RINCIAN + '?' + params.toString())
                            .done(function (res) {
                                tabel.setData(res.rows || []);

                                $('#rincianJumlahBaris').text(
                                    (res.jumlah || 0).toLocaleString('id-ID') + ' transaksi'
                                );

                                // Total dari server dihitung atas SELURUH transaksi yang
                                // cocok, jadi harus sama dengan angka yang diklik. Selisih
                                // apa pun berarti ada yang salah dan wajib diberitahukan.
                                if (Math.abs(Number(res.total) - Number(nilai)) > 0.5) {
                                    $('#rincianPesan')
                                        .removeClass('d-none')
                                        .html('<b>Perhatian:</b> jumlah rincian (' + tulisAngka(res.total)
                                            + ') tidak sama dengan angka pada tabel (' + tulisAngka(nilai) + ').');
                                } else if (res.terpotong) {
                                    $('#rincianPesan')
                                        .removeClass('d-none')
                                        .text('Menampilkan ' + res.batas + ' transaksi pertama dari '
                                            + res.jumlah + '. Jumlah di atas tetap dihitung dari seluruh transaksi.');
                                }
                            })
                            .fail(function () {
                                $('#rincianJumlahBaris').text('-');
                                $('#rincianPesan').removeClass('d-none')
                                    .text('Gagal memuat rincian. Coba ulangi beberapa saat lagi.');
                            });
                    });
                }

                // Tabulator menghitung lebar kolom saat modal masih tersembunyi
                // (lebar 0). Hitung ulang setelah modal benar-benar tampil.
                $('#modalRincian').on('shown.bs.modal', function () {
                    if (siapRincian) siapRincian.then(function (tabel) { tabel.redraw(true); });
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
