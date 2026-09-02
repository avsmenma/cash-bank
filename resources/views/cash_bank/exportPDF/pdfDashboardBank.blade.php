<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Saldo Kas &amp; Bank</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        /* Ukuran diset untuk kertas A4 penuh: tabel melebar 100% area cetak,
           font & padding diperbesar agar hasil print tidak kecil di pojok kertas. */
        body { font-family: Arial, sans-serif; font-size: 13.5px; color: #222; padding: 20px; }
        h2 { font-size: 16px; margin-bottom: 14px; color: #0d3b6e; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #aaa; padding: 7px 9px; }
        .bg-header { background-color: #bdc3c7; font-weight: bold; text-align: center; }
        .bg-subheader { background-color: #ecf0f1; font-weight: bold; }
        .bg-yellow { background-color: #f9e400; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .no-border td { border: none; padding: 4px 0; }
        .footer-section { margin-top: 24px; width: 100%; }
        .footer-right { text-align: center; float: right; width: 45%; }
        .signature-name { font-weight: bold; text-decoration: underline; }
        .signature-title { color: #222; }
        .clearfix::after { content: ''; display: table; clear: both; }

        /* Kartu Informasi Saldo (ikut layar /dashboard-bank) */
        .info-saldo {
            margin-top: 18px;
            width: 100%;
            border: 1px solid #aaa;
            border-left: 4px solid #0d3b6e;
            border-radius: 4px;
            padding: 10px 14px;
            page-break-inside: avoid;
        }
        .info-saldo .info-title { font-weight: bold; color: #0d3b6e; margin-bottom: 6px; font-size: 14px; }
        .info-saldo table { border-collapse: collapse; width: 100%; }
        .info-saldo td { border: none; padding: 3px 0; }
        .info-saldo .info-sep { padding: 3px 8px; width: 20px; text-align: center; }
        .info-saldo .info-val { text-align: right; font-weight: bold; }
        .info-saldo tr.info-total td {
            border-top: 2px solid #0d3b6e;
            font-weight: bold;
            color: #0d3b6e;
            padding-top: 6px;
        }
        .neg { color: #c0392b; }

        /* Tabel Saldo VA di halaman berikutnya */
        .page-break { page-break-before: always; }
        .va-table thead th { background-color: #1a5276; color: #fff; }
        .va-table .va-total-row td { background-color: #1a5276; color: #fff; font-weight: bold; }

        /* Hapus header & footer browser saat print (tanggal, judul, URL) */
        @page {
            size: A4 portrait;
            margin: 0;
        }
        @media print {
            body {
                padding: 15mm 18mm;
            }
            /* Paksa browser cetak background color */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<table>
    {{-- HEADER UTAMA --}}
    <thead>
        <tr>
            <th class="bg-header" colspan="2">Saldo Kas &amp; Bank</th>
            <th class="bg-header" style="min-width:140px;">
                @if(!empty($labelFilter) && $labelFilter !== 'Semua Waktu (Seluruh Data)')
                    {{ $labelFilter }}
                @else
                    Tanggal
                @endif
            </th>
            <th class="bg-header" style="min-width:140px;">Nilai (Rp)</th>
        </tr>
        <tr>
            <th class="bg-subheader text-center" style="width:40px;">No.</th>
            <th class="bg-subheader">Uraian</th>
            <th class="bg-subheader text-center">No. Rek</th>
            <th class="bg-subheader text-center">Nilai (Rp)</th>
        </tr>
    </thead>
    <tbody>
        {{-- A. SALDO --}}
        <tr class="bg-yellow">
            <td class="text-center font-bold">A.</td>
            <td class="font-bold" colspan="2">SALDO</td>
            <td></td>
        </tr>

        {{-- I. Saldo Kas --}}
        <tr>
            <td class="text-center">I.</td>
            <td>Saldo Kas</td>
            <td></td>
            <td class="text-right">-</td>
        </tr>

        {{-- II. Saldo Bank --}}
        <tr>
            <td class="text-center">II.</td>
            <td class="font-bold" colspan="3">Saldo Bank :</td>
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
            <td></td>
            <td>- {{ $namaBersih }}</td>
            <td class="text-center" style="font-size:12px; white-space:nowrap;">{{ $noRek }}</td>
            <td class="text-right {{ $sd->saldo_va != 0 ? 'font-bold' : '' }}" style="color:{{ $sd->saldo_va != 0 ? '#222' : '#888' }};">
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
            <td colspan="3" class="text-center" style="color:#888; padding:10px 0;">Tidak ada data sumber dana</td>
        </tr>
        @endforelse

        {{-- TOTAL SALDO BANK --}}
        <tr class="bg-yellow">
            <td colspan="3" class="text-center font-bold">Saldo Bank</td>
            <td class="text-right font-bold">
                @if($totalSaldoBank < 0)
                    ({{ number_format(abs($totalSaldoBank), 0, ',', '.') }})
                @else
                    {{ number_format($totalSaldoBank, 0, ',', '.') }}
                @endif
            </td>
        </tr>
    </tbody>
</table>

{{-- INFORMASI SALDO (sama seperti kartu di halaman /dashboard-bank) --}}
<div class="info-saldo">
    <div class="info-title">Informasi Saldo</div>
    <table>
        <tr>
            <td style="white-space:nowrap;">Saldo Rek {{ $digitAkhirRek }} <span style="color:#888; font-size:12px;">({{ $noRek408 }})</span></td>
            <td class="info-sep">:</td>
            <td class="info-val">
                @if($saldoRek408 < 0)
                    <span class="neg">({{ number_format(abs($saldoRek408), 0, ',', '.') }})</span>
                @else
                    {{ number_format($saldoRek408, 0, ',', '.') }}
                @endif
            </td>
        </tr>
        <tr>
            <td>Saldo Virtual Account (VA) Unit</td>
            <td class="info-sep">:</td>
            <td class="info-val">
                @if($totalSaldoVA < 0)
                    <span class="neg">({{ number_format(abs($totalSaldoVA), 0, ',', '.') }})</span>
                @else
                    {{ number_format($totalSaldoVA, 0, ',', '.') }}
                @endif
            </td>
        </tr>
        <tr class="info-total">
            <td>Saldo Rek {{ $digitAkhirRek }} yg digunakan Region</td>
            <td class="info-sep">:</td>
            <td class="info-val">
                @if($saldoRegion < 0)
                    <span class="neg">({{ number_format(abs($saldoRegion), 0, ',', '.') }})</span>
                @else
                    {{ number_format($saldoRegion, 0, ',', '.') }}
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- FOOTER TANDA TANGAN --}}
<div class="footer-section clearfix" style="margin-top:30px;">
    <div class="footer-right">
        <p>{{ $tanggal }}</p>
        <br><br><br><br>
        <p class="signature-name">{{ $nama }}</p>
        <p class="signature-title">{{ $jabatan }}</p>
    </div>
</div>

{{-- HALAMAN 2 DST: SALDO BANK VIRTUAL ACCOUNT --}}
<div class="page-break">
    <h2>Saldo Bank Virtual Account (VA)</h2>
    <table class="va-table">
        <thead>
            <tr>
                <th class="text-center" style="width:40px;">No.</th>
                <th>Nama Bank / VA</th>
                <th class="text-center" style="min-width:150px;">Saldo Akhir (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bankVAList as $index => $va)
            <tr>
                <td class="text-center">{{ $index + 1 }}.</td>
                <td>{{ $va->nama_tujuan }}</td>
                <td class="text-right {{ $va->saldo != 0 ? 'font-bold' : '' }}" style="color:{{ $va->saldo != 0 ? '#222' : '#888' }};">
                    @if($va->saldo < 0)
                        <span class="neg">({{ number_format(abs($va->saldo), 0, ',', '.') }})</span>
                    @elseif($va->saldo > 0)
                        {{ number_format($va->saldo, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center" style="color:#888; padding:10px 0;">Tidak ada data Bank Virtual Account</td>
            </tr>
            @endforelse
            {{-- Total sebagai baris tbody biasa (bukan tfoot) agar saat print
                 tidak diulang di setiap halaman --}}
            <tr class="va-total-row">
                <td colspan="2" class="text-center">Total Saldo VA</td>
                <td class="text-right">
                    @if($totalSaldoVA < 0)
                        ({{ number_format(abs($totalSaldoVA), 0, ',', '.') }})
                    @else
                        {{ number_format($totalSaldoVA, 0, ',', '.') }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    window.onload = function() { window.print(); };
</script>
</body>
</html>
