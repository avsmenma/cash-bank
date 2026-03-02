@extends('layouts/index')
@section('content')

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
                            {{-- Coba ekstrak nomor rekening dari nama (format: nomor/-/nama bank) --}}
                            $noRek = '-';
                            if (preg_match('/^([\d\-]+)\/?/', $sd->nama_sumber_dana, $m)) {
                                $noRek = trim($m[1], '-/ ');
                            }
                            {{-- Ambil nama nama bank saja (hapus nomor di depan) --}}
                            $namaBersih = preg_replace('/^[\d\-]+\s*\/?\s*[-]?\s*/', '', $sd->nama_sumber_dana);
                            if (empty(trim($namaBersih))) $namaBersih = $sd->nama_sumber_dana;
                        @endphp
                        <tr>
                            <td class="align-middle"></td>
                            <td class="align-middle">- {{ $sd->nama_sumber_dana }}</td>
                            <td class="text-center align-middle text-muted" style="font-size:11.5px; white-space:nowrap;">
                                {{ $noRek !== '-' ? $noRek : '' }}
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

                        {{-- Baris kosong pembatas --}}
                        <tr><td colspan="4" style="padding:4px;"></td></tr>

                        {{-- III. Valuta Asing --}}
                        <tr>
                            <td class="text-center align-middle">III.</td>
                            <td class="font-weight-bold align-middle" colspan="3">Valuta Asing</td>
                        </tr>
                        <tr>
                            <td class="align-middle"></td>
                            <td class="align-middle">- PT. Bank Mandiri</td>
                            <td class="align-middle"></td>
                            <td class="text-right align-middle text-muted">-</td>
                        </tr>
                        <tr>
                            <td class="align-middle"></td>
                            <td class="align-middle">- PT. Bank BRI</td>
                            <td class="text-center align-middle text-muted" style="font-size:11.5px;">007102000017305</td>
                            <td class="text-right align-middle text-muted">-</td>
                        </tr>

                        {{-- IV. Deposito --}}
                        <tr>
                            <td class="text-center align-middle">IV.</td>
                            <td class="font-weight-bold align-middle" colspan="3">Deposito Bank DBS</td>
                        </tr>
                        <tr>
                            <td class="align-middle"></td>
                            <td class="align-middle"></td>
                            <td class="align-middle"></td>
                            <td class="text-right align-middle text-muted">-</td>
                        </tr>

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

        <p class="text-muted small mt-2 ml-1">
            <i class="fas fa-info-circle"></i>
            Saldo VA dihitung dari total Bank Masuk (Debet) dikurangi Bank Keluar (Kredit) per Sumber Dana.
            &nbsp;|&nbsp; No. Rek dapat diisi manual sesuai data aktual per sumber dana.
        </p>
    </section>
</div>

@endsection
