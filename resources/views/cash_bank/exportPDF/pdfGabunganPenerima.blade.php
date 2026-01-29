<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>CashFlow Gabungan {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 14px;
        }

        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px;
        }

        th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }

        td {
            text-align: right;
        }

        td:first-child {
            text-align: left;
            white-space: nowrap;
        }

        .bg-rencana {
            background-color: #e2e3e5;
        }

        .bg-realisasi {
            background-color: #d4edda;
        }

        .bg-selisih {
            background-color: #fff3cd;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>CashFlow Gabungan (Rencana vs Realisasi)</h2>
        <p>Tahun: {{ $tahun }} | Periode: {{ ucfirst(array_keys($bulanListFiltered)[0]) }} -
            {{ ucfirst(array_keys($bulanListFiltered)[count($bulanListFiltered) - 1]) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Kategori</th>
                @foreach($bulanListFiltered as $namaBulan => $noBulan)
                    <th colspan="4">{{ ucfirst(substr($namaBulan, 0, 3)) }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($bulanListFiltered as $namaBulan => $noBulan)
                    <th style="font-size: 7px;">Renc</th>
                    <th style="font-size: 7px;">Real</th>
                    <th style="font-size: 7px;">Selisih</th>
                    <th style="font-size: 7px;">%</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $kategori => $bulanData)
                <tr>
                    <td>{{ $kategori }}</td>
                    @foreach($bulanListFiltered as $namaBulan => $noBulan)
                        @php
                            $d = $bulanData[$namaBulan] ?? ['rencana' => 0, 'realisasi' => 0, 'selisih' => 0, 'persen' => 0];
                        @endphp
                        <td class="bg-rencana">{{ number_format($d['rencana'], 0, ',', '.') }}</td>
                        <td class="bg-realisasi">{{ number_format($d['realisasi'], 0, ',', '.') }}</td>
                        <td class="bg-selisih">{{ number_format($d['selisih'], 0, ',', '.') }}</td>
                        <td>{{ number_format($d['persen'], 1, ',', '.') }}%</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>