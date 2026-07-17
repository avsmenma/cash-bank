@extends('layouts/index')
@section('content')

@php
    $bulanList = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
    ];
    $hariIni   = (int) date('d');
    $bulanIni  = (int) date('m');
    $tahunIni  = (int) date('Y');
@endphp

<div class="container-fluid mt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="font-weight:700;color:#0d3b6e;">
                        <i class="fas fa-university mr-2"></i>Saldo Kas &amp; Bank
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Bank</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        {{-- TOMBOL EXPORT --}}
        <div class="mb-3 cb-fullscreen-hide">
            <button type="button" class="btn btn-success btn-sm mr-2" onclick="openExportModal('excel')">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="openExportModal('pdf')">
                <i class="fas fa-file-pdf mr-1"></i> Export PDF
            </button>
        </div>

        <style>
            /* ===== Tabel Bank VA — Tabulator (spreadsheet ala Bank Masuk/Keluar) ===== */
            #tblSaldoVA { font-size: 12.5px; border: 1px solid #cdd9e5; }
            #tblSaldoVA:focus { outline: none; }
            #tblSaldoVA .tabulator-header { background: #1a5276; border-bottom: 2px solid #0e3a56; }
            #tblSaldoVA .tabulator-header .tabulator-col {
                background: #1a5276 !important; color: #fff !important; font-weight: 600; font-size: 11.5px;
                border-right: 1px solid rgba(255,255,255,.25) !important;
            }
            #tblSaldoVA .tabulator-header .tabulator-col .tabulator-col-title { color: #fff; white-space: normal; }
            #tblSaldoVA .tabulator-tableholder { scrollbar-gutter: stable; }
            #tblSaldoVA .tabulator-cell { border-right: 1px solid #d5e0ea; padding: 8px; }
            #tblSaldoVA .tabulator-row.tabulator-row-even { background: #fbfdff; }
            #tblSaldoVA .tabulator-row:hover .tabulator-cell { background: #f0f5fb; }
            #tblSaldoVA .va-name-link { color: #000; font-weight: 600; text-decoration: none; }
            #tblSaldoVA .va-name-link:hover { text-decoration: underline; }
            #tblSaldoVA .tabulator-cell.va-editable { cursor: cell; }

            /* Baris total (bottomCalc) — biru tua seperti footer lama */
            #tblSaldoVA .tabulator-calcs-holder,
            #tblSaldoVA .tabulator-row.tabulator-calcs { background: #1a5276 !important; }
            #tblSaldoVA .tabulator-row.tabulator-calcs .tabulator-cell {
                background: #1a5276 !important; color: #fff !important; font-weight: 700;
                border-right-color: rgba(255,255,255,.2) !important;
            }

            /* Sel aktif / blok / status simpan (ala spreadsheet) */
            #tblSaldoVA .tabulator-cell.va-active-cell { outline: 2px solid #1b6fd8; outline-offset: -2px; background: #e8f1fd !important; }
            #tblSaldoVA .tabulator-row:hover .tabulator-cell.va-active-cell { background: #e8f1fd !important; }
            #tblSaldoVA .tabulator-cell.va-range-cell { background: #dbeafe !important; }
            #tblSaldoVA .tabulator-row:hover .tabulator-cell.va-range-cell { background: #dbeafe !important; }
            #tblSaldoVA.va-noselect, #tblSaldoVA.va-noselect * { user-select: none; }
            #tblSaldoVA .tabulator-cell.cb-saving-cell { background: #fff8df !important; }
            #tblSaldoVA .tabulator-cell.cb-saved-cell  { background: #e9f8ef !important; }
            #tblSaldoVA .tabulator-cell.cb-error-cell  { background: #fdecec !important; box-shadow: inset 0 0 0 2px #dc3545; }

            /* Popup jumlah blok sel */
            .va-sum-pop {
                position: fixed; z-index: 1080; display: none;
                background: #0d3b6e; color: #fff; font-size: 12px; line-height: 1;
                padding: 8px 12px; border-radius: 6px; box-shadow: 0 4px 14px rgba(0,0,0,.28);
                pointer-events: none; white-space: nowrap;
            }
            .va-sum-pop b { color: #ffd166; }
        </style>

        {{-- LAYOUT BERSEBELAHAN --}}
        <div class="d-flex flex-wrap cb-fullscreen-table" style="gap:20px;">

            {{-- ====== KOLOM KIRI (TABEL + INFO) ====== --}}
            <div id="cbLeftCol" style="flex:1; min-width:380px; align-self:flex-start;">
            <div class="card shadow" style="border-top:4px solid #0d3b6e;">
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="tblSaldoBank" style="font-size:12.5px;">
                        {{-- HEADER UTAMA --}}
                        <thead>
                            <tr style="background:#bdc3c7;">
                                <th colspan="2" class="text-center font-weight-bold" style="padding:10px 8px;">Saldo Kas &amp; Bank</th>
                                <th class="text-center font-weight-bold" style="padding:10px 8px; min-width:150px;">Tanggal</th>
                                <th class="text-center font-weight-bold" style="padding:10px 8px; min-width:170px;">Nilai (Rp)</th>
                            </tr>
                            <tr style="background:#ecf0f1;">
                                <th class="text-center" style="width:40px;">No.</th>
                                <th>Uraian</th>
                                <th class="text-center">No. Rek</th>
                                <th class="text-center">Nilai (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- BARIS A. SALDO --}}
                            <tr style="background:#f9e400;">
                                <td class="font-weight-bold text-center align-middle">A.</td>
                                <td class="font-weight-bold align-middle" colspan="2">SALDO</td>
                                <td class="align-middle"></td>
                            </tr>

                            {{-- I. Saldo Kas --}}
                            <tr>
                                <td class="text-center align-middle">I.</td>
                                <td class="align-middle">Saldo Kas</td>
                                <td class="align-middle"></td>
                                <td class="text-right align-middle">-</td>
                            </tr>

                            {{-- II. Saldo Bank --}}
                            <tr>
                                <td class="text-center align-middle">II.</td>
                                <td class="font-weight-bold align-middle" colspan="3">Saldo Bank :</td>
                            </tr>

                            {{-- DAFTAR SUMBER DANA --}}
                            @forelse($sumberDanaList as $sd)
                            @php
                                $noRek = '';
                                $namaBersih = $sd->nama_sumber_dana;
                                if (preg_match('/\*\s*([\d\-\/]+)\s*$/', $sd->nama_sumber_dana, $m)) {
                                    $noRek = trim($m[1]);
                                    $namaBersih = trim(preg_replace('/\s*\*\s*[\d\-\/]+\s*$/', '', $sd->nama_sumber_dana));
                                }
                            @endphp
                            <tr>
                                <td class="align-middle"></td>
                                <td class="align-middle">- {{ $namaBersih }}</td>
                                <td class="text-center align-middle text-muted" style="font-size:11.5px; white-space:nowrap;">
                                    {{ $noRek }}
                                </td>
                                <td class="text-right align-middle {{ $sd->saldo_va != 0 ? 'font-weight-bold' : 'text-muted' }}">
                                    @if($sd->saldo_va < 0)
                                        ({{ number_format(abs($sd->saldo_va), 0, ',', '.') }})
                                    @elseif($sd->saldo_va > 0)
                                        {{ number_format($sd->saldo_va, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td></td>
                                <td colspan="3" class="text-center text-muted py-3">
                                    Tidak ada data sumber dana
                                </td>
                            </tr>
                            @endforelse

                            {{-- TOTAL SALDO BANK --}}
                            <tr style="background:#f9e400;">
                                <td colspan="3" class="text-center font-weight-bold align-middle">Saldo Bank</td>
                                <td class="text-right font-weight-bold align-middle">
                                    @if($totalSaldoBank < 0)
                                        ({{ number_format(abs($totalSaldoBank), 0, ',', '.') }})
                                    @else
                                        {{ number_format($totalSaldoBank, 0, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>{{-- end card --}}

            {{-- INFO RINGKASAN SALDO --}}
            <div class="mt-3 cb-fullscreen-hide" style="
                background: #f8fafc;
                border: 1px solid #d5dfe8;
                border-left: 4px solid #0d3b6e;
                border-radius: 6px;
                padding: 14px 18px;
                font-size: 13px;
                line-height: 1.8;
            ">
                <div class="font-weight-bold mb-2" style="color:#0d3b6e; font-size:13.5px;">
                    <i class="fas fa-info-circle mr-1"></i> Informasi Saldo
                </div>
                <table style="width:100%; font-size:13px;">
                    <tr>
                        <td style="padding:3px 0; white-space:nowrap;">Saldo Rek {{ $digitAkhirRek }}<span class="text-muted" style="font-size:11.5px;"> ({{ $noRek408 }})</span></td>
                        <td style="padding:3px 8px; width:20px; text-align:center;">:</td>
                        <td style="padding:3px 0; text-align:right; font-weight:600;">
                            @if($saldoRek408 < 0)
                                <span style="color:#c0392b;">{{ number_format(abs($saldoRek408), 0, ',', '.') }}</span>
                            @else
                                {{ number_format($saldoRek408, 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:3px 0;">Saldo Virtual Account (VA) Unit</td>
                        <td style="padding:3px 8px; text-align:center;">:</td>
                        <td style="padding:3px 0; text-align:right; font-weight:600;">
                            @if($totalSaldoVA < 0)
                                <span style="color:#c0392b;">{{ number_format(abs($totalSaldoVA), 0, ',', '.') }}</span>
                            @else
                                {{ number_format($totalSaldoVA, 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                    <tr style="border-top:2px solid #0d3b6e;">
                        <td style="padding:6px 0 3px; font-weight:700; color:#0d3b6e;">
                            Saldo Rek {{ $digitAkhirRek }} yg digunakan Region
                        </td>
                        <td style="padding:6px 8px 3px; text-align:center; font-weight:700;">:</td>
                        <td style="padding:6px 0 3px; text-align:right; font-weight:700; color:#0d3b6e; font-size:14px;">
                            @if($saldoRegion < 0)
                                <span style="color:#c0392b;">{{ number_format(abs($saldoRegion), 0, ',', '.') }}</span>
                            @else
                                {{ number_format($saldoRegion, 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            </div>{{-- end kolom kiri --}}

            {{-- ====== TABEL BANK VIRTUAL ACCOUNT (KANAN) — Tabulator spreadsheet ====== --}}
            <div id="cbVACard" class="card shadow mb-0" style="border-top:4px solid #1a5276; flex:1; min-width:380px; align-self:flex-start;">
                <div class="card-body p-0">
                    {{-- Klik sel = pilih; dobel-klik/Enter SALDO SAP = edit (tersimpan otomatis);
                         panah = navigasi; drag/Shift = blok + jumlah; Ctrl+C = salin. --}}
                    <div id="tblSaldoVA"></div>
                </div>
            </div>

        </div>{{-- end d-flex --}}
    </section>
</div>

{{-- ===================== MODAL EXPORT ===================== --}}
<div class="modal fade" id="modalExport" tabindex="-1" role="dialog" aria-labelledby="modalExportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:460px;">
        <div class="modal-content" style="border-top:4px solid #0d3b6e; border-radius:8px;">
            <div class="modal-header py-3" style="background:#f8f9fa; border-bottom:1px solid #dee2e6;">
                <h6 class="modal-title font-weight-bold text-dark mb-0" id="modalExportLabel">
                    <i class="fas fa-file-export mr-2 text-primary"></i>
                    <span id="modalExportTitle">Export Dokumen</span>
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Lengkapi data berikut sebelum mengekspor dokumen.
                </p>

                {{-- Tanggal Dokumen --}}
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-calendar-alt mr-1 text-primary"></i>Tanggal Dokumen
                    </label>
                    <div class="d-flex align-items-center" style="gap:6px;">
                        <span class="text-muted" style="white-space:nowrap; font-size:13px;">Pontianak,</span>
                        {{-- Hari --}}
                        <select id="selHari" class="form-control form-control-sm" style="width:65px; font-size:12px;">
                            @for($h = 1; $h <= 31; $h++)
                                <option value="{{ $h }}" {{ $h == $hariIni ? 'selected' : '' }}>{{ $h }}</option>
                            @endfor
                        </select>
                        {{-- Bulan --}}
                        <select id="selBulan" class="form-control form-control-sm" style="width:110px; font-size:12px;">
                            @foreach($bulanList as $no => $nama)
                                <option value="{{ $nama }}" {{ $no == $bulanIni ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                        {{-- Tahun --}}
                        <select id="selTahun" class="form-control form-control-sm" style="width:80px; font-size:12px;">
                            @for($y = $tahunIni - 3; $y <= $tahunIni + 2; $y++)
                                <option value="{{ $y }}" {{ $y == $tahunIni ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Nama Penandatangan --}}
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-user mr-1 text-primary"></i>Nama Penandatangan
                    </label>
                    <input type="text" id="inpNama" class="form-control form-control-sm"
                           value="Herry Wahyudi"
                           style="font-size:12px;">
                </div>

                {{-- Jabatan --}}
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-id-badge mr-1 text-primary"></i>Jabatan
                    </label>
                    <input type="text" id="inpJabatan" class="form-control form-control-sm"
                           value="Kepala Bagian Akuntansi &amp; Keuangan"
                           style="font-size:12px;">
                </div>
            </div>
            <div class="modal-footer py-2" style="background:#f8f9fa;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-sm" id="btnDoExport" style="min-width:120px;">
                    <i class="fas fa-download mr-1"></i>
                    <span id="btnExportLabel">Export</span>
                </button>
            </div>
        </div>
    </div>
</div>
{{-- ======================================================= --}}

<script>
    var _exportType = 'excel';

    function openExportModal(type) {
        _exportType = type;
        if (type === 'excel') {
            document.getElementById('modalExportTitle').textContent = 'Export Excel';
            document.getElementById('btnExportLabel').textContent   = 'Export Excel';
            document.getElementById('btnDoExport').className        = 'btn btn-success btn-sm';
            document.getElementById('btnDoExport').querySelector('i').className = 'fas fa-file-excel mr-1';
        } else {
            document.getElementById('modalExportTitle').textContent = 'Export PDF';
            document.getElementById('btnExportLabel').textContent   = 'Export PDF';
            document.getElementById('btnDoExport').className        = 'btn btn-danger btn-sm';
            document.getElementById('btnDoExport').querySelector('i').className = 'fas fa-file-pdf mr-1';
        }
        $('#modalExport').modal('show');
    }

    document.getElementById('btnDoExport').addEventListener('click', function () {
        var hari    = document.getElementById('selHari').value;
        var bulan   = document.getElementById('selBulan').value;
        var tahun   = document.getElementById('selTahun').value;
        var nama    = document.getElementById('inpNama').value;
        var jabatan = document.getElementById('inpJabatan').value;

        var tanggal = 'Pontianak, ' + hari + ' ' + bulan + ' ' + tahun;

        var params = new URLSearchParams({
            tanggal: tanggal,
            nama: nama,
            jabatan: jabatan
        });

        $('#modalExport').modal('hide');

        if (_exportType === 'excel') {
            window.location.href = '{{ route("dashboard.bank.excel") }}?' + params.toString();
        } else {
            window.open('{{ route("dashboard.bank.pdf") }}?' + params.toString(), '_blank');
        }
    });

    // (Tabel Bank VA kini memakai Tabulator; skripnya ada di bawah pada stack
    //  'scripts' agar dijalankan setelah jQuery & Tabulator selesai dimuat.)
</script>

{{-- ============================================================
     TABEL BANK VA — TABULATOR (spreadsheet penuh ala Bank Masuk/Keluar)
     Ditaruh di stack 'scripts' (dirender SETELAH jQuery & Tabulator).
     Fitur: pilih sel (klik), navigasi panah, blok drag/Shift + jumlah,
     Ctrl+C salin, dobel-klik/Enter untuk edit SALDO SAP (tersimpan
     otomatis via PATCH), dan baris Total (bottomCalc).
     ============================================================ --}}
@push('scripts')
<script>
(function () {
    'use strict';

    var el = document.getElementById('tblSaldoVA');
    if (!el || !window.Tabulator) { return; }
    el.setAttribute('tabindex', '-1');   // bisa difokuskan → event 'paste' terarah ke tabel

    // Data VA sudah dirender server-side — tak perlu AJAX terpisah.
    var VA_DATA = @json($bankVAList->values());
    var BASE = "{{ url('/daftarBank') }}";
    function csrf() { return $('meta[name="csrf-token"]').attr('content'); }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
    function idNum(v) { return (Math.round(Number(v) || 0)).toLocaleString('id-ID'); }

    // ── Formatter tampilan ──
    function fmtSaldo(cell) {
        var v = Number(cell.getValue()) || 0;
        if (v === 0) return '<span class="text-muted">-</span>';
        if (v < 0)  return '<span style="color:#c0392b;">(' + idNum(Math.abs(v)) + ')</span>';
        return idNum(v);
    }
    function fmtSap(cell) {
        var v = Number(cell.getValue()) || 0;
        return v === 0 ? '<span class="text-muted">-</span>' : idNum(v);
    }
    // SELISIH = Saldo Akhir - Saldo SAP (negatif ditandai merah + kurung).
    function fmtSelisih(cell) {
        var v = Number(cell.getValue()) || 0;
        if (v === 0) return '<span class="text-muted">-</span>';
        if (v < 0)  return '<span style="color:#c0392b;">(' + idNum(Math.abs(v)) + ')</span>';
        return idNum(v);
    }
    function fmtNama(cell) {
        var d = cell.getRow().getData();
        var url = BASE + '/' + d.id_bank_tujuan + '/detail';
        return '<a href="' + url + '" class="va-name-link" '
            + 'title="Lihat detail transaksi VA ini">' + esc(d.nama_tujuan) + '</a>';
    }

    // ── Baris Total (bottomCalc) ──
    function calcSaldo(values, data)   { var s = 0; data.forEach(function (r) { s += Number(r.saldo) || 0; }); return s; }
    function calcSap(values, data)     { var s = 0; data.forEach(function (r) { s += Number(r.sap_nilai) || 0; }); return s; }
    function calcSelisih(values, data) { var s = 0; data.forEach(function (r) { s += Number(r.selisih) || 0; }); return s; }
    function calcFmtSaldo(cell)   { var v = Number(cell.getValue()) || 0; return v < 0 ? '(' + idNum(Math.abs(v)) + ')' : idNum(v); }
    function calcFmtSap(cell)     { var v = Number(cell.getValue()) || 0; return v === 0 ? '-' : idNum(v); }
    function calcFmtSelisih(cell) { var v = Number(cell.getValue()) || 0; return v < 0 ? '(' + idNum(Math.abs(v)) + ')' : idNum(v); }

    var table = new Tabulator(el, {
        index: 'id_bank_tujuan',
        data: VA_DATA,
        layout: 'fitColumns',
        // Tanpa tinggi tetap: tabel merender SEMUA baris & memanjang ke bawah —
        // tidak ada scrollbar vertikal internal, dan lebih ringan karena tak ada
        // render virtual (itu yang bikin jeda/lag sesaat saat sel SAP disimpan).
        movableColumns: false,
        editTriggerEvent: 'dblclick',            // dobel-klik = edit; klik tunggal = pilih sel
        columnHeaderVertAlign: 'middle',
        placeholder: 'Tidak ada data Bank Virtual Account.',
        columnDefaults: { resizable: true, headerSort: false, minWidth: 40 },
        columns: [
            { title: 'No', formatter: 'rownum', width: 52, hozAlign: 'center', headerHozAlign: 'center' },
            { title: 'Nama Bank / VA', field: 'nama_tujuan', minWidth: 150, widthGrow: 3, formatter: fmtNama,
              bottomCalc: function () { return 'Total Saldo VA'; },
              bottomCalcFormatter: function (cell) { return cell.getValue(); } },
            { title: 'Saldo Akhir (Rp)', field: 'saldo', width: 160, hozAlign: 'right', headerHozAlign: 'center',
              formatter: fmtSaldo, bottomCalc: calcSaldo, bottomCalcFormatter: calcFmtSaldo },
            { title: 'SALDO SAP', field: 'sap_nilai', width: 160, hozAlign: 'right', headerHozAlign: 'center',
              formatter: fmtSap, editor: 'number', editorParams: { min: 0, selectContents: true },
              cssClass: 'va-editable', bottomCalc: calcSap, bottomCalcFormatter: calcFmtSap },
            { title: 'SELISIH', field: 'selisih', width: 160, hozAlign: 'right', headerHozAlign: 'center',
              formatter: fmtSelisih, bottomCalc: calcSelisih, bottomCalcFormatter: calcFmtSelisih }
        ],

        rowFormatter: function (row) {
            // Virtual DOM membuat ulang baris saat scroll — pasang lagi sorotan sel aktif & blok.
            var d = row.getData();
            if (vaActive && String(d.id_bank_tujuan) === String(vaActive.id)) {
                var c = row.getCell(vaActive.field);
                if (c) c.getElement().classList.add('va-active-cell');
            }
            vaPaintRow(row);
        }
    });

    // ============ UNDO / REDO (Ctrl+Z / Ctrl+Y) ============
    // Manajer undo generik: mengingat nilai lama tiap perubahan, lalu saat undo
    // mengembalikan nilai lama DI LAYAR sekaligus menyimpannya kembali ke server.
    function createUndoManager(cfg) {
        var undoStack = [], redoStack = [], MAX = 100, pending = null;
        function snap(row) { var d = row.getData(), s = {}; cfg.trackFields.forEach(function (f) { s[f] = d[f]; }); (cfg.companions || []).forEach(function (cf) { s[cf] = d[cf]; }); return s; }
        function diff(b, a) { return cfg.trackFields.filter(function (f) { return String(b[f] == null ? '' : b[f]) !== String(a[f] == null ? '' : a[f]); }); }
        function push(entry) { if (!entry.length) return; undoStack.push(entry); if (undoStack.length > MAX) undoStack.shift(); redoStack = []; }
        function findRow(id) { var rows = cfg.table.getRows(); for (var i = 0; i < rows.length; i++) { if (String(cfg.getId(rows[i].getData())) === String(id)) return rows[i]; } return null; }
        return {
            snapshotRows: function (ids) { var m = {}; ids.forEach(function (id) { var r = findRow(id); if (r) m[String(id)] = snap(r); }); return m; },
            recordBatch: function (before, after) { var e = []; Object.keys(after).forEach(function (id) { var b = before[id], a = after[id]; if (!b || !a) return; var ch = diff(b, a); if (ch.length) e.push({ id: id, before: b, after: a, fields: ch }); }); push(e); },
            beginEdit: function (row) { pending = { id: String(cfg.getId(row.getData())), before: snap(row) }; },
            commitEdit: function (row) { if (!pending) return; var id = String(cfg.getId(row.getData())); if (pending.id !== id) { pending = null; return; } var a = snap(row); var ch = diff(pending.before, a); if (ch.length) push([{ id: id, before: pending.before, after: a, fields: ch }]); pending = null; },
            undo: function () { if (!undoStack.length) return false; var e = undoStack.pop(); e.forEach(function (rc) { var row = findRow(rc.id); if (row) cfg.applyState(row, rc.before, rc.fields); }); redoStack.push(e); return true; },
            redo: function () { if (!redoStack.length) return false; var e = redoStack.pop(); e.forEach(function (rc) { var row = findRow(rc.id); if (row) cfg.applyState(row, rc.after, rc.fields); }); undoStack.push(e); return true; }
        };
    }

    var vaUndo = createUndoManager({
        table: table,
        getId: function (d) { return d.id_bank_tujuan; },
        trackFields: ['sap_nilai'],
        companions: [],
        applyState: function (row, state, fields) {
            var id = row.getData().id_bank_tujuan;
            var sap = Math.round(Number(state.sap_nilai) || 0);
            row.update({ sap_nilai: sap, selisih: (Number(row.getData().saldo) || 0) - sap });
            var cell = row.getCell('sap_nilai'); var ce = cell && cell.getElement();
            if (ce) { ce.classList.remove('cb-saved-cell', 'cb-error-cell'); ce.classList.add('cb-saving-cell'); }
            $.ajax({ url: BASE + '/' + id + '/sap', method: 'PATCH', global: false, data: { sap: sap }, headers: { 'X-CSRF-TOKEN': csrf() } })
                .done(function () { if (ce) { ce.classList.remove('cb-saving-cell', 'cb-error-cell'); ce.classList.add('cb-saved-cell'); setTimeout(function () { ce.classList.remove('cb-saved-cell'); }, 900); } })
                .fail(function () { if (ce) { ce.classList.remove('cb-saving-cell'); ce.classList.add('cb-error-cell'); } });
        }
    });

    // Rekam nilai lama saat mulai edit inline sel SAP (dobel-klik).
    table.on('cellEditing', function (cell) { if (cell.getField() === 'sap_nilai') vaUndo.beginEdit(cell.getRow()); });

    // ============ SIMPAN SAP SAAT SEL DIEDIT ============
    table.on('cellEdited', function (cell) {
        if (cell.getField() !== 'sap_nilai') return;
        var row = cell.getRow();
        var id = row.getData().id_bank_tujuan;
        var value = Math.round(Number(cell.getValue()) || 0);
        vaUndo.commitEdit(row);

        // SELISIH = Saldo Akhir - SAP baru → perbarui langsung; sel & total SELISIH
        // di footer ikut dihitung ulang oleh Tabulator.
        row.update({ selisih: (Number(row.getData().saldo) || 0) - value });

        var ce = cell.getElement();
        ce.classList.remove('cb-saved-cell', 'cb-error-cell');
        ce.classList.add('cb-saving-cell');

        $.ajax({
            url: BASE + '/' + id + '/sap',
            method: 'PATCH',
            global: false,                 // jangan picu event ajax global (lebih ringan)
            data: { sap: value },
            headers: { 'X-CSRF-TOKEN': csrf() },
            success: function () {
                ce.classList.remove('cb-saving-cell', 'cb-error-cell');
                ce.classList.add('cb-saved-cell');
                setTimeout(function () { ce.classList.remove('cb-saved-cell'); }, 900);
            },
            error: function () {
                ce.classList.remove('cb-saving-cell');
                ce.classList.add('cb-error-cell');
                alert('Gagal menyimpan SAP. Silakan coba lagi.');
            }
        });
    });

    // ============ SEL AKTIF + BLOK SEL (ala spreadsheet) + COPY + JUMLAH ============
    var vaActive = null;   // { id, field }
    var vaAnchor = null;   // { r, c }
    var vaRange  = null;   // { r1, c1, r2, c2 }
    var vaDrag   = false;

    var pop = document.createElement('div');
    pop.className = 'va-sum-pop';
    document.body.appendChild(pop);

    function vaCols() { return table.getColumns().filter(function (c) { return c.isVisible(); }); }
    function vaCellPos(cell) {
        var p = cell.getRow().getPosition();
        var cols = vaCols(), ci = -1;
        for (var i = 0; i < cols.length; i++) { if (cols[i].getField() === cell.getField()) { ci = i; break; } }
        return (p === false || ci < 0) ? null : { r: p - 1, c: ci };
    }
    function vaClearActiveEl() {
        el.querySelectorAll('.tabulator-cell.va-active-cell').forEach(function (n) { n.classList.remove('va-active-cell'); });
    }
    function vaSetActive(cell) {
        vaClearActiveEl();
        vaActive = { id: cell.getRow().getData().id_bank_tujuan, field: cell.getField() };
        cell.getElement().classList.add('va-active-cell');
    }
    function vaGetActiveCell() {
        if (!vaActive) return null;
        var row = table.getRows().find(function (r) { return String(r.getData().id_bank_tujuan) === String(vaActive.id); });
        return row ? row.getCell(vaActive.field) : null;
    }
    function vaPaintRow(row) {
        if (!vaRange) return;
        var p = row.getPosition();
        if (p === false) return;
        var r = p - 1;
        if (r < vaRange.r1 || r > vaRange.r2) return;
        var cols = vaCols();
        for (var c = vaRange.c1; c <= vaRange.c2 && c < cols.length; c++) {
            var cell = row.getCell(cols[c]);
            var ce = cell && cell.getElement();
            if (ce && ce.classList) ce.classList.add('va-range-cell');
        }
    }
    function vaApplyRange() {
        el.querySelectorAll('.tabulator-cell.va-range-cell').forEach(function (n) { n.classList.remove('va-range-cell'); });
        if (!vaRange) return;
        table.getRows().slice(vaRange.r1, vaRange.r2 + 1).forEach(vaPaintRow);
    }
    function vaSetRange(a, b) {
        vaRange = { r1: Math.min(a.r, b.r), r2: Math.max(a.r, b.r), c1: Math.min(a.c, b.c), c2: Math.max(a.c, b.c) };
        vaApplyRange();
    }
    function vaClearRange() { vaRange = null; vaApplyRange(); pop.style.display = 'none'; }

    // Angka format Indonesia: "1.500.000", "(2.500)". Teks ditolak.
    function vaParseNum(v) {
        if (v === null || v === undefined) return null;
        var s = String(v).replace(/<[^>]*>/g, '').trim();
        if (!s || s === '-') return null;
        var neg = /^\(.*\)$/.test(s);
        s = s.replace(/[()\s]/g, '').replace(/^Rp/i, '');
        if (!/^-?\d{1,3}(\.\d{3})*(,\d+)?$/.test(s) && !/^-?\d+(,\d+)?$/.test(s)) return null;
        var n = parseFloat(s.replace(/\./g, '').replace(',', '.'));
        if (isNaN(n)) return null;
        return neg ? -n : n;
    }
    function vaPosXY(mx, my) {
        var x = mx || 0, y = my || 0;
        var lastRow = table.getRows()[vaRange.r2];
        var lastCell = lastRow && lastRow.getCell(vaCols()[vaRange.c2]);
        var ce = lastCell && lastCell.getElement();
        if (ce && ce.getBoundingClientRect) {
            var rc = ce.getBoundingClientRect();
            if (rc.width || rc.height) { x = rc.right + 8; y = rc.bottom + 8; }
        }
        var pr = pop.getBoundingClientRect();
        x = Math.min(Math.max(8, x), window.innerWidth - pr.width - 8);
        y = Math.min(Math.max(8, y), window.innerHeight - pr.height - 8);
        pop.style.left = x + 'px';
        pop.style.top = y + 'px';
    }
    function vaShowSum(mx, my) {
        if (!vaRange || (vaRange.r1 === vaRange.r2 && vaRange.c1 === vaRange.c2)) { pop.style.display = 'none'; return; }
        var rows = table.getRows().slice(vaRange.r1, vaRange.r2 + 1);
        var cols = vaCols().slice(vaRange.c1, vaRange.c2 + 1);
        var sum = 0, n = 0;
        rows.forEach(function (row) {
            cols.forEach(function (col) {
                var cell = row.getCell(col);
                var val = cell ? vaParseNum(cell.getElement().innerText) : null;
                if (val !== null) { sum += val; n++; }
            });
        });
        if (!n) { pop.style.display = 'none'; return; }
        pop.innerHTML = 'Jumlah: <b>' + sum.toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '</b>'
            + (n > 1 ? ' &nbsp;·&nbsp; Data angka: <b>' + n + '</b>' : '');
        pop.style.display = 'block';
        vaPosXY(mx, my);
    }

    function fromLink(e) { return e.target && e.target.closest && e.target.closest('a'); }
    function isEditorTag(t) { return t === 'INPUT' || t === 'SELECT' || t === 'TEXTAREA'; }

    table.on('cellMouseDown', function (e, cell) {
        if (e.button !== 0) return;
        if (fromLink(e)) return;                          // klik link nama → biarkan navigasi
        if (isEditorTag((e.target && e.target.tagName) || '')) return;
        var p = vaCellPos(cell);
        if (!p) return;
        try { el.focus({ preventScroll: true }); } catch (err) { try { el.focus(); } catch (e2) {} }
        pop.style.display = 'none';
        if (e.shiftKey && vaAnchor) {
            vaSetRange(vaAnchor, p); vaShowSum(e.clientX, e.clientY); e.preventDefault(); return;
        }
        vaAnchor = p; vaDrag = true;
        el.classList.add('va-noselect');
        vaSetActive(cell); vaSetRange(p, p);
        e.preventDefault();
    });
    table.on('cellMouseEnter', function (e, cell) {
        if (!vaDrag || !vaAnchor) return;
        var p = vaCellPos(cell);
        if (p) vaSetRange(vaAnchor, p);
    });
    document.addEventListener('mouseup', function (e) {
        if (!vaDrag) return;
        vaDrag = false;
        el.classList.remove('va-noselect');
        if (vaRange && vaRange.r1 === vaRange.r2 && vaRange.c1 === vaRange.c2) vaClearRange();
        else vaShowSum(e.clientX, e.clientY);
    });
    table.on('cellClick', function (e, cell) {
        if (fromLink(e)) return;
        if (isEditorTag((e.target && e.target.tagName) || '')) return;
        vaSetActive(cell);
    });

    // Navigasi panah + Esc + Enter(F2) edit + Ctrl/Cmd+C copy
    document.addEventListener('keydown', function (e) {
        if (isEditorTag((e.target && e.target.tagName) || '') ||
            isEditorTag((document.activeElement && document.activeElement.tagName) || '')) return;

        // Undo (Ctrl+Z) / Redo (Ctrl+Y atau Ctrl+Shift+Z)
        if ((e.ctrlKey || e.metaKey) && !e.shiftKey && (e.key === 'z' || e.key === 'Z')) { e.preventDefault(); vaUndo.undo(); return; }
        if ((e.ctrlKey || e.metaKey) && ((e.key === 'y' || e.key === 'Y') || (e.shiftKey && (e.key === 'z' || e.key === 'Z')))) { e.preventDefault(); vaUndo.redo(); return; }
        if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'C')) {
            if (!vaRange && !vaActive) return;
            e.preventDefault(); vaCopy(e); return;
        }
        // Delete / Backspace → kosongkan SALDO SAP pada sel aktif / seluruh blok
        // (tanpa masuk mode edit). preventDefault penting agar Backspace tidak
        // memicu "kembali" di browser.
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (!vaActive && !vaRange) return;
            if (vaClearSelection()) e.preventDefault();
            return;
        }
        if (!vaActive) return;
        if (e.key === 'Escape') { vaClearActiveEl(); vaActive = null; vaClearRange(); return; }
        if (e.key === 'Enter' || e.key === 'F2') {
            var ec = vaGetActiveCell();
            if (ec) { e.preventDefault(); ec.edit(true); }   // hanya kolom SAP yang punya editor
            return;
        }
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].indexOf(e.key) === -1) return;

        var cols = vaCols();
        var rows = table.getRows();
        var cell = vaGetActiveCell();
        if (!cell) return;
        var pos = vaCellPos(cell);
        if (!pos) return;
        e.preventDefault();

        // Shift+Panah: perluas blok dari sel aktif.
        if (e.shiftKey) {
            if (!vaRange || !vaAnchor) vaAnchor = pos;
            var nr = pos.r, nc = pos.c;
            if (e.key === 'ArrowUp')         nr = Math.max(0, pos.r - 1);
            else if (e.key === 'ArrowDown')  nr = Math.min(rows.length - 1, pos.r + 1);
            else if (e.key === 'ArrowLeft')  nc = Math.max(0, pos.c - 1);
            else if (e.key === 'ArrowRight') nc = Math.min(cols.length - 1, pos.c + 1);
            if (nr === pos.r && nc === pos.c) return;
            var trow = rows[nr];
            if (!trow) return;
            vaActive = { id: trow.getData().id_bank_tujuan, field: cols[nc].getField() };
            vaClearActiveEl();
            vaSetRange(vaAnchor, { r: nr, c: nc });
            var applyActive = function () {
                var ac = vaGetActiveCell();
                if (ac && ac.getElement()) ac.getElement().classList.add('va-active-cell');
                vaApplyRange(); vaShowSum();
            };
            var pr1 = table.scrollToRow(trow, 'nearest', false);
            if (pr1 && pr1.then) pr1.then(applyActive).catch(applyActive);
            else window.requestAnimationFrame(applyActive);
            return;
        }

        // Panah biasa: pindah sel aktif + hapus blok.
        vaClearRange();
        var row = cell.getRow();
        var ci = cols.findIndex(function (c) { return c.getField() === vaActive.field; });
        var target = null;
        if (e.key === 'ArrowLeft'  && ci > 0)               target = row.getCell(cols[ci - 1]);
        if (e.key === 'ArrowRight' && ci < cols.length - 1) target = row.getCell(cols[ci + 1]);
        if (e.key === 'ArrowUp')   { var pv = row.getPrevRow(); if (pv) target = pv.getCell(vaActive.field); }
        if (e.key === 'ArrowDown') { var nx = row.getNextRow(); if (nx) target = nx.getCell(vaActive.field); }
        if (target) {
            vaSetActive(target);
            if (target.getElement()) target.getElement().scrollIntoView({ block: 'nearest', inline: 'nearest' });
        }
    });

    // ── Salin blok/sel aktif ke clipboard sebagai TSV ──
    function vaCopy(e) {
        var cols0 = vaCols(), rows, cols;
        if (vaRange) {
            rows = table.getRows().slice(vaRange.r1, vaRange.r2 + 1);
            cols = cols0.slice(vaRange.c1, vaRange.c2 + 1);
        } else {
            var c = vaGetActiveCell();
            if (!c) return;
            rows = [c.getRow()];
            var ci = cols0.findIndex(function (x) { return x.getField() === vaActive.field; });
            cols = ci >= 0 ? [cols0[ci]] : [];
        }
        if (!cols.length) return;
        var tsv = rows.map(function (row) {
            return cols.map(function (col) {
                var cell = row.getCell(col);
                var t = cell ? (cell.getElement().innerText || '') : '';
                return t.replace(/\r?\n/g, ' ').replace(/\t/g, ' ').trim();
            }).join('\t');
        }).join('\n');

        var flash = function () { vaFlash(e); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(tsv).then(flash).catch(function () { vaFallbackCopy(tsv); flash(); });
        } else { vaFallbackCopy(tsv); flash(); }
    }
    function vaFallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); } catch (err) {}
        document.body.removeChild(ta);
    }
    function vaFlash(e) {
        pop.innerHTML = '<b>✓</b> Disalin';
        pop.style.display = 'block';
        if (vaRange) { vaPosXY(e && e.clientX, e && e.clientY); }
        else {
            var c = vaGetActiveCell();
            var rc = c && c.getElement().getBoundingClientRect();
            var x = rc ? rc.right + 8 : (e ? e.clientX : 20);
            var y = rc ? rc.bottom + 8 : (e ? e.clientY : 20);
            var pr = pop.getBoundingClientRect();
            pop.style.left = Math.min(Math.max(8, x), window.innerWidth - pr.width - 8) + 'px';
            pop.style.top = Math.min(Math.max(8, y), window.innerHeight - pr.height - 8) + 'px';
        }
        setTimeout(function () {
            if (vaRange && !(vaRange.r1 === vaRange.r2 && vaRange.c1 === vaRange.c2)) vaShowSum();
            else pop.style.display = 'none';
        }, 900);
    }

    // ── TEMPEL (PASTE) ke kolom SALDO SAP tanpa masuk mode edit ──
    // Salin satu kolom angka (dari Excel atau dari kolom SAP), pilih sel SAP awal,
    // lalu Ctrl+V → nilai mengisi ke bawah. Hanya kolom SAP yang menerima nilai;
    // tiap sel tersimpan otomatis (memicu cellEdited) dan SELISIH ikut dihitung.
    function vaDigits(s) { return String(s == null ? '' : s).split(',')[0].replace(/\D/g, ''); }
    function vaParseClip(text) {
        var t = String(text).replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        var lines = t.split('\n');
        while (lines.length && lines[lines.length - 1] === '') lines.pop();
        return lines.map(function (l) { return l.split('\t'); });
    }
    // Simpan satu nilai SAP ke server + umpan-balik warna pada sel-nya.
    function vaSaveSap(id, val) {
        var row = table.getRow(id);
        var cell = row && row.getCell('sap_nilai');
        var ce = cell && cell.getElement();
        if (ce) { ce.classList.remove('cb-saved-cell', 'cb-error-cell'); ce.classList.add('cb-saving-cell'); }
        $.ajax({
            url: BASE + '/' + id + '/sap',
            method: 'PATCH', global: false,
            data: { sap: val },
            headers: { 'X-CSRF-TOKEN': csrf() },
            success: function () {
                if (ce) { ce.classList.remove('cb-saving-cell', 'cb-error-cell'); ce.classList.add('cb-saved-cell'); setTimeout(function () { ce.classList.remove('cb-saved-cell'); }, 900); }
            },
            error: function () {
                if (ce) { ce.classList.remove('cb-saving-cell'); ce.classList.add('cb-error-cell'); }
            }
        });
    }

    function vaPaste(text) {
        var matrix = vaParseClip(text);
        if (!matrix.length) return;
        var rows = table.getRows();
        var vals = matrix.map(function (rr) { return vaDigits(rr[0] || ''); });   // 1 kolom angka
        if (!vals.length) return;

        // Kumpulkan baris target + indeks nilai clipboard yang dipakai.
        var picks = [];   // { row: RowComponent, vi: indeks nilai }
        if (vaRange && !(vaRange.r1 === vaRange.r2 && vaRange.c1 === vaRange.c2)) {
            // ADA BLOK → semua baris di blok; nilai clipboard diulang (tiling) bila pendek.
            var k = 0;
            for (var r = vaRange.r1; r <= vaRange.r2; r++, k++) { if (rows[r]) picks.push({ row: rows[r], vi: k }); }
        } else {
            // TANPA blok → menurun mulai sel aktif.
            var startR;
            if (vaActive) { var ac = vaGetActiveCell(); var p = ac && vaCellPos(ac); if (!p) return; startR = p.r; }
            else return;
            for (var i = 0; i < vals.length; i++) { if (rows[startR + i]) picks.push({ row: rows[startR + i], vi: i }); }
        }

        // Bangun update SEKALIGUS (satu render — hindari re-render per sel yang bikin
        // referensi baris kadaluarsa sehingga hanya sel pertama tersimpan) + daftar simpan.
        var dataUpdates = [], saves = [];
        picks.forEach(function (pk) {
            var v = vals[pk.vi % vals.length];
            if (v === '') return;
            var val = parseInt(v, 10) || 0;
            var d = pk.row.getData();
            dataUpdates.push({ id_bank_tujuan: d.id_bank_tujuan, sap_nilai: val, selisih: (Number(d.saldo) || 0) - val });
            saves.push({ id: d.id_bank_tujuan, val: val });
        });
        if (!dataUpdates.length) return;

        var undoBefore = vaUndo.snapshotRows(saves.map(function (s) { return s.id; }));
        var undoAfter = {}; saves.forEach(function (s) { undoAfter[String(s.id)] = { sap_nilai: s.val }; });

        function afterRender() { saves.forEach(function (s) { vaSaveSap(s.id, s.val); }); }
        var pr = table.updateData(dataUpdates);   // satu kali render; SELISIH & total ikut
        if (pr && pr.then) { pr.then(afterRender)['catch'](afterRender); }
        else { afterRender(); }
        vaUndo.recordBatch(undoBefore, undoAfter);
    }

    // Kosongkan SALDO SAP pada sel aktif / seluruh blok (dipicu Delete/Backspace).
    // Nilai jadi 0 (tampil "-"), SELISIH = Saldo Akhir, dan di server disimpan null.
    // Mengembalikan true bila ada aksi yang ditangani.
    function vaClearSelection() {
        var cols = vaCols(), rows = table.getRows();
        var sapIdx = -1;
        for (var i = 0; i < cols.length; i++) { if (cols[i].getField() === 'sap_nilai') { sapIdx = i; break; } }
        if (sapIdx < 0) return false;

        var pickRows = [];
        if (vaRange && !(vaRange.r1 === vaRange.r2 && vaRange.c1 === vaRange.c2)) {
            if (sapIdx < vaRange.c1 || sapIdx > vaRange.c2) return false;   // blok tak mencakup kolom SAP
            for (var r = vaRange.r1; r <= vaRange.r2; r++) { if (rows[r]) pickRows.push(rows[r]); }
        } else if (vaActive && vaActive.field === 'sap_nilai') {
            var row = table.getRow(vaActive.id);
            if (row) pickRows.push(row);
        } else {
            return false;   // sel aktif bukan kolom SAP → tak ada yang dihapus
        }
        if (!pickRows.length) return false;

        var dataUpdates = [], saves = [];
        pickRows.forEach(function (row) {
            var d = row.getData();
            if ((Number(d.sap_nilai) || 0) === 0) return;   // sudah kosong → lewati
            dataUpdates.push({ id_bank_tujuan: d.id_bank_tujuan, sap_nilai: 0, selisih: (Number(d.saldo) || 0) });
            saves.push(d.id_bank_tujuan);
        });
        if (!dataUpdates.length) return true;   // tidak ada yang perlu dihapus, tapi tetap ditangani

        var undoBefore = vaUndo.snapshotRows(saves);
        var undoAfter = {}; saves.forEach(function (id) { undoAfter[String(id)] = { sap_nilai: 0 }; });

        function afterClear() { saves.forEach(function (id) { vaSaveSap(id, ''); }); }   // '' → server simpan null
        var pr = table.updateData(dataUpdates);
        if (pr && pr.then) { pr.then(afterClear)['catch'](afterClear); }
        else { afterClear(); }
        vaUndo.recordBatch(undoBefore, undoAfter);
        return true;
    }

    document.addEventListener('paste', function (e) {
        // Jangan ganggu paste normal saat sedang mengetik di editor/input.
        if (isEditorTag((e.target && e.target.tagName) || '') ||
            isEditorTag((document.activeElement && document.activeElement.tagName) || '')) return;
        if (!vaActive && !vaRange) return;              // tak ada sel terpilih → abaikan
        var cd = e.clipboardData || window.clipboardData;
        if (!cd) return;
        var text = cd.getData('text/plain') || cd.getData('text') || '';
        if (!text) return;
        e.preventDefault();
        vaPaste(text);
    });
})();
</script>
@endpush

@endsection
