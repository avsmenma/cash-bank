<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Permintaan {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h3 {
            margin: 0;
            font-size: 13px;
        }

        .header p {
            margin: 3px 0 0 0;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #bbb;
            padding: 4px 6px;
        }

        th {
            background-color: #1e3a5f;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
        }

        td.num {
            text-align: right;
        }

        tr.total-row td {
            background-color: #1e3a5f;
            color: #ffffff;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h3>PERMINTAAN — {{ strtoupper($namaKategori) }} / {{ strtoupper($namaSub) }}</h3>
        <p>{{ $namaBulan }} {{ $tahun }}</p>
    </div>

    @php
        $totM1 = 0; $totM2 = 0; $totM3 = 0; $totM4 = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Item</th>
                <th style="width:85px;">Minggu 1</th>
                <th style="width:85px;">Minggu 2</th>
                <th style="width:85px;">Minggu 3</th>
                <th style="width:85px;">Minggu 4</th>
                <th style="width:95px;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
                @php
                    $d = $data[$item->id_item_sub_kriteria] ?? ['M1' => 0, 'M2' => 0, 'M3' => 0, 'M4' => 0];
                    $jumlah = $d['M1'] + $d['M2'] + $d['M3'] + $d['M4'];
                    $totM1 += $d['M1']; $totM2 += $d['M2']; $totM3 += $d['M3']; $totM4 += $d['M4'];
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $i + 1 }}</td>
                    <td>{{ $item->nama_item_sub_kriteria }}</td>
                    <td class="num">{{ number_format($d['M1'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($d['M2'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($d['M3'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($d['M4'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($jumlah, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">Tidak ada item</td>
                </tr>
            @endforelse

            <tr class="total-row">
                <td colspan="2">TOTAL</td>
                <td class="num">{{ number_format($totM1, 0, ',', '.') }}</td>
                <td class="num">{{ number_format($totM2, 0, ',', '.') }}</td>
                <td class="num">{{ number_format($totM3, 0, ',', '.') }}</td>
                <td class="num">{{ number_format($totM4, 0, ',', '.') }}</td>
                <td class="num">{{ number_format($totM1 + $totM2 + $totM3 + $totM4, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
