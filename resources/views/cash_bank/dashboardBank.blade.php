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
        <div class="mb-3" style="max-width:820px;">
            <button type="button" class="btn btn-success btn-sm mr-2" onclick="doExport('excel')">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="doExport('pdf')">
                <i class="fas fa-file-pdf mr-1"></i> Export PDF
            </button>
        </div>

        <div class="card shadow" style="border-top:4px solid #0d3b6e; max-width:820px;">
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
                            // Ekstrak nomor rekening dari akhir string (pola: "* 146-00-0443935-7")
                            $noRek = '';
                            $namaBersih = $sd->nama_sumber_dana;
                            if (preg_match('/\*\s*([\d\-\/]+)\s*$/', $sd->nama_sumber_dana, $m)) {
                                $noRek = trim($m[1]);
                                // Hapus bagian nomor rek dari nama (beserta spasi sebelum *)
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
        </div>


        {{-- SECTION EDITABLE: TANGGAL & TANDA TANGAN --}}
        <div class="card shadow mt-3" style="max-width:820px; border-top:3px solid #0d3b6e;">
            <div class="card-body py-3 px-4">
                <div class="row align-items-start">
                    {{-- Kolom kiri: kosong (placeholder tanda tangan kiri jika diperlukan) --}}
                    <div class="col-md-6">
                        {{-- Bisa dikembangkan di masa mendatang --}}
                    </div>

                    {{-- Kolom kanan: Kota, Tanggal, Nama, Jabatan --}}
                    <div class="col-md-6">
                        {{-- Kota + Tanggal --}}
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted mb-1">
                                <i class="fas fa-calendar-alt mr-1"></i>Tanggal Dokumen
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
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold text-muted mb-1">
                                <i class="fas fa-user mr-1"></i>Nama Penandatangan
                            </label>
                            <input type="text" id="inpNama" class="form-control form-control-sm"
                                   value="Herry Wahyudi"
                                   style="font-size:12px; max-width:280px;">
                        </div>

                        {{-- Jabatan --}}
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-muted mb-1">
                                <i class="fas fa-id-badge mr-1"></i>Jabatan
                            </label>
                            <input type="text" id="inpJabatan" class="form-control form-control-sm"
                                   value="Kepala Bagian Akuntansi &amp; Keuangan"
                                   style="font-size:12px; max-width:300px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    function doExport(type) {
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

        if (type === 'excel') {
            window.location.href = '{{ route("dashboard.bank.excel") }}?' + params.toString();
        } else {
            window.open('{{ route("dashboard.bank.pdf") }}?' + params.toString(), '_blank');
        }
    }
</script>

@endsection
