<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Saldo Kas &amp; Bank</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #222; padding: 20px; }
        h2 { font-size: 13px; margin-bottom: 12px; color: #0d3b6e; }
        table { border-collapse: collapse; width: 100%; max-width: 680px; }
        th, td { border: 1px solid #aaa; padding: 5px 7px; }
        .bg-header { background-color: #bdc3c7; font-weight: bold; text-align: center; }
        .bg-subheader { background-color: #ecf0f1; font-weight: bold; }
        .bg-yellow { background-color: #f9e400; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .no-border td { border: none; padding: 4px 0; }
        .footer-section { margin-top: 20px; width: 100%; max-width: 680px; }
        .footer-right { text-align: center; float: right; width: 45%; }
        .signature-name { font-weight: bold; text-decoration: underline; }
        .signature-title { color: #222; }
        .clearfix::after { content: ''; display: table; clear: both; }

        /* Hapus header & footer browser saat print (tanggal, judul, URL) */
        @page {
            margin: 0;
        }
        @media print {
            body {
                padding: 15mm 18mm;
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
            <th class="bg-header" style="min-width:140px;">Tanggal</th>
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
            <td class="text-center" style="font-size:10px; white-space:nowrap;">{{ $noRek }}</td>
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

{{-- FOOTER TANDA TANGAN --}}
<div class="footer-section clearfix" style="margin-top:30px;">
    <div class="footer-right">
        <p>{{ $tanggal }}</p>
        <br><br><br><br>
        <p class="signature-name">{{ $nama }}</p>
        <p class="signature-title">{{ $jabatan }}</p>
    </div>
</div>

<script type="text/javascript">
    window.onload = function() { window.print(); };
</script>
</body>
</html>
