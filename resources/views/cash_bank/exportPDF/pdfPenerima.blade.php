<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Penerima {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h3 {
            margin: 0;
            font-size: 12px;
        }

        .header p {
            margin: 2px 0 0 0;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
        }

        /* Jarak antar tabel bulan */
        table.month-table {
            margin-bottom: 18px;
        }

        th,
        td {
            border: 1px solid #bbb;
            padding: 3px 4px;
        }

        /* Judul bulan (baris paling atas tiap tabel) — sama dengan .row-month-title web */
        .month-title td {
            background-color: #1a5632;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            text-align: left;
            padding: 5px 6px;
        }

        /* Header kolom — sama dengan .row-header web */
        th {
            background-color: #2d7a4a;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            padding: 4px 4px;
        }

        td {
            text-align: right;
        }

        td.text-left {
            text-align: left;
        }

        td.text-center {
            text-align: center;
        }

        /* Header kategori — sama dengan .row-kategori-header web */
        .kategori-header td {
            background-color: #e8f5e9;
            color: #1a5632;
            font-weight: bold;
            text-align: left;
            padding: 4px 6px;
        }

        /* Subtotal per kategori — sama dengan .row-subtotal web */
        .subtotal-row td {
            background-color: #fff3cd;
            color: #333333;
            font-weight: bold;
            border-top: 2px solid #c9a825;
        }

        /* Total per bulan — sama dengan .row-grand-total web */
        .month-total td {
            background-color: #1a5632;
            color: #ffffff;
            font-weight: bold;
            border-top: 2px solid #0d3b1f;
        }

        /* Grand total semua bulan */
        .grand-total td {
            background-color: #0d3b1f;
            color: #ffffff;
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        $bulanLabels = [
            0 => 'TANPA TANGGAL',
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];

        // Rentang bulan untuk subjudul dokumen
        $months = array_keys($grouped);
        $firstMonth = !empty($months) ? min($months) : 1;
        $lastMonth  = !empty($months) ? max($months) : 12;

        $grandVolume = 0;
        $grandNilai  = 0;
        $grandPpn    = 0;
        $grandPotppn = 0;
        $grandInc    = 0;
        $monthCount  = count($grouped);
    @endphp

    <div class="header">
        <h3>{{ $judulPenerimaan ?? 'PENERIMAAN ATAS PENJUALAN CPO, KERNEL, SIR 20, TBS, KSO & LAINNYA' }}</h3>
        <p>{{ $bulanLabels[$firstMonth] ?? '' }} {{ $firstMonth !== $lastMonth ? '- ' . ($bulanLabels[$lastMonth] ?? '') . ' ' : '' }}{{ $tahun }}</p>
    </div>

    @foreach($grouped as $bulanNum => $kategoriGroup)
        @php
            $monthVolume = 0;
            $monthNilai  = 0;
            $monthPpn    = 0;
            $monthPotppn = 0;
            $monthInc    = 0;
        @endphp

        {{-- Satu tabel per bulan, header kolom diulang tiap bulan --}}
        <table class="month-table">
            <thead>
                <tr class="month-title">
                    <td colspan="12">
                        {{ $judulPenerimaan ?? 'PENERIMAAN ATAS PENJUALAN CPO, KERNEL, SIR 20, TBS, KSO & LAINNYA' }} — {{ $bulanNames[$bulanNum] ?? '' }} {{ $tahun }}
                    </td>
                </tr>
                <tr>
                    <th style="width:25px;">No</th>
                    <th>Penerimaan</th>
                    <th>Kontrak</th>
                    <th>Pembeli</th>
                    <th>Tgl. Diterima</th>
                    <th>No. Rekg. Penerima</th>
                    <th>Volume (Kg)</th>
                    <th>Harga (Rp)</th>
                    <th>Nilai</th>
                    <th>PPN</th>
                    <th>Pot PPh</th>
                    <th>Nilai Inc. PPN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kategoriGroup as $kategoriName => $rows)
                    {{-- Header kategori --}}
                    <tr class="kategori-header">
                        <td colspan="12">{{ strtoupper($kategoriName) }}</td>
                    </tr>

                    @php
                        $subVolume = 0;
                        $subNilai  = 0;
                        $subPpn    = 0;
                        $subPotppn = 0;
                        $subInc    = 0;
                        $catNo     = 1;
                    @endphp

                    @foreach($rows as $row)
                        @php
                            $nilaiIncPpn = $row->nilai_inc_ppn ?? 0;
                            $subVolume += $row->volume;
                            $subNilai  += $row->nilai;
                            $subPpn    += $row->ppn;
                            $subPotppn += $row->potppn;
                            $subInc    += $nilaiIncPpn;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $catNo++ }}</td>
                            <td class="text-left">{{ $kategoriName }}</td>
                            <td class="text-left">{{ $row->kontrak }}</td>
                            <td class="text-left">{{ $row->pembeli }}</td>
                            <td class="text-center">{{ ($row->tanggal && $row->tanggal !== '0000-00-00') ? \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d M Y') : '-' }}</td>
                            <td class="text-left">{{ $row->no_reg }}</td>
                            <td>{{ number_format($row->volume, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->harga, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->nilai, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->ppn, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->potppn, 0, ',', '.') }}</td>
                            <td>{{ number_format($nilaiIncPpn, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    {{-- Subtotal per kategori --}}
                    @php
                        $monthVolume += $subVolume;
                        $monthNilai  += $subNilai;
                        $monthPpn    += $subPpn;
                        $monthPotppn += $subPotppn;
                        $monthInc    += $subInc;
                    @endphp
                    <tr class="subtotal-row">
                        <td colspan="6" class="text-left">JUMLAH {{ strtoupper($kategoriName) }}</td>
                        <td>{{ number_format($subVolume, 0, ',', '.') }}</td>
                        <td></td>
                        <td>{{ number_format($subNilai, 0, ',', '.') }}</td>
                        <td>{{ number_format($subPpn, 0, ',', '.') }}</td>
                        <td>{{ number_format($subPotppn, 0, ',', '.') }}</td>
                        <td>{{ number_format($subInc, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                {{-- Total per bulan --}}
                @php
                    $grandVolume += $monthVolume;
                    $grandNilai  += $monthNilai;
                    $grandPpn    += $monthPpn;
                    $grandPotppn += $monthPotppn;
                    $grandInc    += $monthInc;
                @endphp
                <tr class="month-total">
                    <td colspan="6" class="text-left">TOTAL {{ $bulanNames[$bulanNum] ?? '' }}</td>
                    <td>{{ number_format($monthVolume, 0, ',', '.') }}</td>
                    <td></td>
                    <td>{{ number_format($monthNilai, 0, ',', '.') }}</td>
                    <td>{{ number_format($monthPpn, 0, ',', '.') }}</td>
                    <td>{{ number_format($monthPotppn, 0, ',', '.') }}</td>
                    <td>{{ number_format($monthInc, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    {{-- Grand total semua bulan (hanya bila lebih dari satu bulan) --}}
    @if($monthCount > 1)
        <table>
            <tbody>
                <tr class="grand-total">
                    <td colspan="6" class="text-left" style="width:50%;">TOTAL {{ $tahun }}</td>
                    <td>{{ number_format($grandVolume, 0, ',', '.') }}</td>
                    <td></td>
                    <td>{{ number_format($grandNilai, 0, ',', '.') }}</td>
                    <td>{{ number_format($grandPpn, 0, ',', '.') }}</td>
                    <td>{{ number_format($grandPotppn, 0, ',', '.') }}</td>
                    <td>{{ number_format($grandInc, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</body>

</html>
