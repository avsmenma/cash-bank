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
            margin-bottom: 10px;
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

        th,
        td {
            border: 1px solid #000;
            padding: 2px 4px;
        }

        th {
            background-color: #FF8C00;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
        }

        td {
            text-align: right;
        }

        td:nth-child(1),
        td:nth-child(2),
        td:nth-child(3),
        td:nth-child(4),
        td:nth-child(5),
        td:nth-child(6) {
            text-align: left;
        }

        .kategori-header td {
            background-color: #4CAF50;
            font-weight: bold;
            color: #ffffff;
        }

        .subtotal-row td {
            background-color: #FFD700;
            font-weight: bold;
            color: #000000ff;
        }

        .month-total td {
            background-color: #FF8C00;
            font-weight: bold;
            color: #000000ff;
        }

        .grand-total td {
            background-color: #FF8C00;
            font-weight: bold;
            color: #000000ff;
        }
    </style>
</head>

<body>
    @php
        $bulanLabels = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];

        // Determine month range for subtitle
        $months = array_keys($grouped);
        $firstMonth = !empty($months) ? min($months) : 1;
        $lastMonth  = !empty($months) ? max($months) : 12;

        $grandVolume = 0;
        $grandNilai  = 0;
        $grandPpn    = 0;
        $grandPotppn = 0;
        $grandInc    = 0;
    @endphp

    <div class="header">
        <h3>PENERIMAAN ATAS PENJUALAN CPO, KERNEL, SIR 20, TBS, KSO & LAINNYA</h3>
        <p>{{ $bulanLabels[$firstMonth] ?? '' }} {{ $firstMonth !== $lastMonth ? '- ' . ($bulanLabels[$lastMonth] ?? '') . ' ' : '' }}{{ $tahun }}</p>
    </div>

    <table>
        <thead>
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
            @foreach($grouped as $bulanNum => $kategoriGroup)
                @php
                    $monthVolume = 0;
                    $monthNilai  = 0;
                    $monthPpn    = 0;
                    $monthPotppn = 0;
                    $monthInc    = 0;
                @endphp

                @foreach($kategoriGroup as $kategoriName => $rows)
                    {{-- Kategori header --}}
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
                            <td style="text-align:center;">{{ $catNo++ }}</td>
                            <td>{{ $kategoriName }}</td>
                            <td>{{ $row->kontrak }}</td>
                            <td>{{ $row->pembeli }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}</td>
                            <td>{{ $row->no_reg }}</td>
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
                        <td colspan="6">JUMLAH {{ strtoupper($kategoriName) }}</td>
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
                    <td colspan="6">TOTAL {{ $bulanNames[$bulanNum] ?? '' }}</td>
                    <td>{{ number_format($monthVolume, 0, ',', '.') }}</td>
                    <td></td>
                    <td>{{ number_format($monthNilai, 0, ',', '.') }}</td>
                    <td>{{ number_format($monthPpn, 0, ',', '.') }}</td>
                    <td>{{ number_format($monthPotppn, 0, ',', '.') }}</td>
                    <td>{{ number_format($monthInc, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            {{-- Grand total --}}
            <tr class="grand-total">
                <td colspan="6">TOTAL</td>
                <td>{{ number_format($grandVolume, 0, ',', '.') }}</td>
                <td></td>
                <td>{{ number_format($grandNilai, 0, ',', '.') }}</td>
                <td>{{ number_format($grandPpn, 0, ',', '.') }}</td>
                <td>{{ number_format($grandPotppn, 0, ',', '.') }}</td>
                <td>{{ number_format($grandInc, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
