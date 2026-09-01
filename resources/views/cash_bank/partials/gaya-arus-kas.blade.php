{{--
    Gaya bersama Laporan Arus Kas (halaman unit/kebun & halaman admin).
    Semua aturan dikunci pada kelas `.cf-tabel`, jadi wadah Tabulator cukup
    diberi class="cf-tabel" — bukan lagi berdasarkan id tabel.
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('plugins/tabulator/tabulator_semanticui.min.css') }}">
    <style>
        /* Tampilan kompak DISETEL LEWAT UKURAN HURUF, bukan `zoom`.
           Catatan penting: `zoom: 0.8` merusak layout kolom Tabulator — ia
           membaca lebar wadah hasil getBoundingClientRect() yang sudah terkena
           zoom lalu menetapkan lebar kolom dalam satuan CSS px pada wadah yang
           sebenarnya lebih lebar, sehingga tabel tidak pernah memenuhi layar. */
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
            --cf-garis: #B0BEC5;
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
        .cf-tabel {
            font-size: 12.5px;
            border: 1.5px solid var(--cf-garis) !important;
        }

        .cf-tabel .tabulator-header,
        .cf-tabel .tabulator-header .tabulator-col {
            background-color: var(--cf-navy) !important;
            color: #fff !important;
            border-color: rgba(255, 255, 255, .35) !important;
        }

        .cf-tabel .tabulator-header .tabulator-col .tabulator-col-title {
            color: #fff;
            font-weight: 600;
            white-space: normal;
            text-align: center;
        }

        .cf-tabel .tabulator-header .tabulator-col {
            border-right: 2px solid rgba(255, 255, 255, .9) !important;
        }

        .cf-tabel .tabulator-cell {
            white-space: normal;
            overflow-wrap: break-word;
            border-right: 1.5px solid var(--cf-garis) !important;
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .cf-tabel .tabulator-row {
            border-bottom: 1.5px solid var(--cf-garis) !important;
        }

        /* ── Pewarnaan baris menurut jenjang laporan ── */
        .cf-tabel .tabulator-row.cf-section .tabulator-cell {
            background: var(--cf-section) !important;
            font-weight: 700;
            color: #21351A;
            letter-spacing: .03em;
        }

        .cf-tabel .tabulator-row.cf-subsection .tabulator-cell {
            background: var(--cf-subsection) !important;
            font-weight: 700;
            color: #33502A;
        }

        .cf-tabel .tabulator-row.cf-group .tabulator-cell {
            background: var(--cf-group) !important;
            font-weight: 600;
            color: #2B3A4A;
        }

        .cf-tabel .tabulator-row.cf-detail .tabulator-cell {
            background: #fff !important;
        }

        .cf-tabel .tabulator-row.cf-plain .tabulator-cell {
            background: #FBFCFD !important;
        }

        .cf-tabel .tabulator-row.cf-subtotal .tabulator-cell {
            background: var(--cf-subtotal) !important;
            font-weight: 700;
            color: #22331A;
        }

        .cf-tabel .tabulator-row.cf-total .tabulator-cell {
            background: var(--cf-total) !important;
            color: #fff !important;
            font-weight: 700;
            letter-spacing: .03em;
        }

        .cf-tabel .tabulator-row.cf-net .tabulator-cell {
            background: #375623 !important;
            color: #fff !important;
            font-weight: 700;
        }

        .cf-tabel .tabulator-row.cf-closing .tabulator-cell {
            background: var(--cf-navy) !important;
            color: #fff !important;
            font-weight: 700;
            border-top: 2px solid #fff !important;
        }

        /* Baris jenjang memakai sel gabungan (Kode+Reference+Uraian), jadi
           kedalamannya ditandai lewat indentasi: bagian menempel di tepi kiri,
           sub-bagian dan totalnya menjorok sedikit ke dalam. */
        .cf-tabel .tabulator-row.cf-subsection .tabulator-cell[tabulator-field="uraian"],
        .cf-tabel .tabulator-row.cf-subtotal .tabulator-cell[tabulator-field="uraian"] {
            padding-left: 24px;
        }

        /* Baris antar-bagian: pemisah tipis tanpa isi */
        .cf-tabel .tabulator-row.cf-spacer .tabulator-cell {
            background: #EEF2F6 !important;
            padding-top: 2px !important;
            padding-bottom: 2px !important;
            border-right-color: #EEF2F6 !important;
        }

        /* Hover hanya untuk baris data agar blok warna tidak "berkedip" */
        .cf-tabel .tabulator-row.cf-detail:hover .tabulator-cell,
        .cf-tabel .tabulator-row.cf-plain:hover .tabulator-cell {
            background: #EAF2FB !important;
        }

        /* ── Angka: gaya SAP, tanda minus di belakang seperti berkas Excel ── */
        .cf-angka { font-variant-numeric: tabular-nums; }
        .cf-negatif { color: #C82333; }
        .cf-nol { color: #B7C0CA; }

        .cf-tabel .tabulator-row.cf-total .cf-negatif,
        .cf-tabel .tabulator-row.cf-net .cf-negatif,
        .cf-tabel .tabulator-row.cf-closing .cf-negatif { color: #FFD5D5; }

        .cf-tabel .tabulator-row.cf-total .cf-nol,
        .cf-tabel .tabulator-row.cf-net .cf-nol,
        .cf-tabel .tabulator-row.cf-closing .cf-nol { color: rgba(255, 255, 255, .6); }

        /* Indentasi uraian mengikuti kedalaman jenjang */
        .cf-indent { padding-left: 20px; display: inline-block; }
        /* Kode & Reference memakai huruf yang sama dengan kolom lain agar tampilan
           seragam; hanya lebar angkanya dibuat rata supaya kode tetap sejajar. */
        .cf-kode {
            font-family: inherit;
            font-size: inherit;
            font-variant-numeric: tabular-nums;
        }

        @media print {
            .cf-no-print { display: none !important; }
        }
    </style>
@endpush
