<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Saldo Kas & Bank</title>
</head>
<body>
<table>
    {{-- HEADER UTAMA --}}
    <tr>
        <td colspan="2" style="background-color:#bdc3c7; font-weight:bold; text-align:center; border:1px solid #888;">Saldo Kas &amp; Bank</td>
        <td style="background-color:#bdc3c7; font-weight:bold; text-align:center; border:1px solid #888;">Tanggal</td>
        <td style="background-color:#bdc3c7; font-weight:bold; text-align:center; border:1px solid #888;">Nilai (Rp)</td>
    </tr>
    <tr>
        <td style="background-color:#ecf0f1; font-weight:bold; text-align:center; border:1px solid #bbb; width:40px;">No.</td>
        <td style="background-color:#ecf0f1; font-weight:bold; border:1px solid #bbb;">Uraian</td>
        <td style="background-color:#ecf0f1; font-weight:bold; text-align:center; border:1px solid #bbb;">No. Rek</td>
        <td style="background-color:#ecf0f1; font-weight:bold; text-align:center; border:1px solid #bbb;">Nilai (Rp)</td>
    </tr>

    {{-- A. SALDO --}}
    <tr>
        <td style="background-color:#f9e400; font-weight:bold; text-align:center; border:1px solid #bbb;">A.</td>
        <td colspan="2" style="background-color:#f9e400; font-weight:bold; border:1px solid #bbb;">SALDO</td>
        <td style="background-color:#f9e400; border:1px solid #bbb;"></td>
    </tr>

    {{-- I. Saldo Kas --}}
    <tr>
        <td style="text-align:center; border:1px solid #ddd;">I.</td>
        <td style="border:1px solid #ddd;">Saldo Kas</td>
        <td style="border:1px solid #ddd;"></td>
        <td style="text-align:right; border:1px solid #ddd;">-</td>
    </tr>

    {{-- II. Saldo Bank --}}
    <tr>
        <td style="text-align:center; border:1px solid #ddd;">II.</td>
        <td colspan="3" style="font-weight:bold; border:1px solid #ddd;">Saldo Bank :</td>
    </tr>

    {{-- DAFTAR SUMBER DANA --}}
    @forelse($sumberDanaList as $sd)
    @php
        $exNoRek = '';
        $exNama  = $sd->nama_sumber_dana;
        if (preg_match('/\*\s*([\d\-\/]+)\s*$/', $sd->nama_sumber_dana, $exM)) {
            $exNoRek = trim($exM[1]);
            $exNama  = trim(preg_replace('/\s*\*\s*[\d\-\/]+\s*$/', '', $sd->nama_sumber_dana));
        }
        if ($sd->saldo_va < 0) {
            $exNilai = '(' . number_format(abs($sd->saldo_va), 0, ',', '.') . ')';
        } elseif ($sd->saldo_va > 0) {
            $exNilai = number_format($sd->saldo_va, 0, ',', '.');
        } else {
            $exNilai = '-';
        }
    @endphp
    <tr>
        <td style="border:1px solid #ddd;"></td>
        <td style="border:1px solid #ddd;">- {{ $exNama }}</td>
        <td style="text-align:center; border:1px solid #ddd;">{{ $exNoRek }}</td>
        <td style="text-align:right; border:1px solid #ddd; {{ $sd->saldo_va != 0 ? 'font-weight:bold;' : '' }}">{{ $exNilai }}</td>
    </tr>
    @empty
    <tr>
        <td style="border:1px solid #ddd;"></td>
        <td colspan="3" style="text-align:center; color:#888; border:1px solid #ddd;">Tidak ada data sumber dana</td>
    </tr>
    @endforelse

    {{-- TOTAL SALDO BANK --}}
    @php
        if ($totalSaldoBank < 0) {
            $exTotal = '(' . number_format(abs($totalSaldoBank), 0, ',', '.') . ')';
        } else {
            $exTotal = number_format($totalSaldoBank, 0, ',', '.');
        }
    @endphp
    <tr>
        <td colspan="3" style="background-color:#f9e400; font-weight:bold; text-align:center; border:1px solid #bbb;">Saldo Bank</td>
        <td style="background-color:#f9e400; font-weight:bold; text-align:right; border:1px solid #bbb;">{{ $exTotal }}</td>
    </tr>

    {{-- FOOTER: baris kosong --}}
    <tr><td colspan="4"></td></tr>
    <tr><td colspan="4"></td></tr>

    {{-- Kota & Tanggal (di kolom kanan) --}}
    <tr>
        <td colspan="2"></td>
        <td colspan="2" style="text-align:center;">{{ $tanggal }}</td>
    </tr>

    {{-- Baris kosong untuk ruang tanda tangan --}}
    <tr><td colspan="4"></td></tr>
    <tr><td colspan="4"></td></tr>
    <tr><td colspan="4"></td></tr>
    <tr><td colspan="4"></td></tr>

    {{-- Nama & Jabatan --}}
    <tr>
        <td colspan="2"></td>
        <td colspan="2" style="text-align:center; font-weight:bold; text-decoration:underline;">{{ $nama }}</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td colspan="2" style="text-align:center; color:#c0392b;">{{ $jabatan }}</td>
    </tr>
</table>
</body>
</html>
