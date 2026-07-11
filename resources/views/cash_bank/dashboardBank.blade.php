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
            /* Header & footer tabel VA menempel saat isi tabel di-scroll */
            #cbVAScroll {
                overflow: auto;
                flex: 1;
                min-height: 0;
            }
            #tblSaldoVA thead th {
                position: sticky;
                top: 0;
                background: #1a5276;
                z-index: 5;
            }
            #tblSaldoVA tfoot td {
                position: sticky;
                bottom: 0;
                background: #1a5276;
                z-index: 5;
            }
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

            {{-- ====== TABEL BANK VIRTUAL ACCOUNT (KANAN) ====== --}}
            {{-- Tinggi card disamakan dengan kolom kiri oleh syncVAHeight(); sisanya scroll --}}
            <div id="cbVACard" class="card shadow mb-0" style="border-top:4px solid #1a5276; flex:1; min-width:380px; display:flex; flex-direction:column; align-self:flex-start;">
                <div id="cbVAScroll" class="card-body p-0">
                    <table class="table table-bordered mb-0" id="tblSaldoVA" style="font-size:12.5px;">
                        <thead>
                            <tr style="background:#1a5276;">
                                <th class="text-center font-weight-bold text-white" style="padding:10px 8px; width:40px;">No.</th>
                                <th class="font-weight-bold text-white" style="padding:10px 8px;">Nama Bank / VA</th>
                                <th class="text-center font-weight-bold text-white" style="padding:10px 8px; min-width:160px;">Saldo Akhir (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bankVAList as $index => $va)
                            <tr>
                                <td class="text-center align-middle">{{ $index + 1 }}.</td>
                                <td class="align-middle">{{ $va->nama_tujuan }}</td>
                                <td class="text-right align-middle {{ $va->saldo != 0 ? 'font-weight-bold' : 'text-muted' }}">
                                    @if($va->saldo < 0)
                                        <span style="color:#c0392b;">({{ number_format(abs($va->saldo), 0, ',', '.') }})</span>
                                    @elseif($va->saldo > 0)
                                        {{ number_format($va->saldo, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    Tidak ada data Bank Virtual Account
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background:#1a5276;">
                                <td colspan="2" class="text-center font-weight-bold text-white align-middle" style="padding:10px 8px;">
                                    Total Saldo VA
                                </td>
                                <td class="text-right font-weight-bold text-white align-middle" style="padding:10px 8px;">
                                    @if($totalSaldoVA < 0)
                                        ({{ number_format(abs($totalSaldoVA), 0, ',', '.') }})
                                    @else
                                        {{ number_format($totalSaldoVA, 0, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
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

    // Samakan tinggi card Bank VA dengan kolom kiri (tabel Saldo + Informasi Saldo).
    // Saat layout menyempit dan card turun ke bawah kolom kiri, tinggi dibiarkan auto.
    function syncVAHeight() {
        var left = document.getElementById('cbLeftCol');
        var card = document.getElementById('cbVACard');
        if (!left || !card) return;

        card.style.height = 'auto';

        var stacked = card.getBoundingClientRect().top >= left.getBoundingClientRect().bottom - 5;
        if (!stacked && card.offsetHeight > left.offsetHeight) {
            card.style.height = left.offsetHeight + 'px';
        }
    }

    window.addEventListener('load', syncVAHeight);
    window.addEventListener('resize', syncVAHeight);
</script>

@endsection
