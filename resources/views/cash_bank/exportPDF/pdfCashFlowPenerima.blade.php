<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>CashFlow Realisasi {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
        }

        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
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
        }

        tfoot th {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>CashFlow Realisasi</h2>
        <p>Tahun: {{ $tahun }}</p>
    </div>

    @php
        $bulanList = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $totalPerBulan = array_fill_keys(array_keys(array_fill(1, 12, 0)), 0);
        $grandTotal = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                @foreach($bulanList as $b)
                    <th>{{ $b }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($result as $kategori => $bulanData)
                @php
                    $total = 0;
                @endphp
                <tr>
                    <td>{{ $kategori }}</td>
                    @foreach($bulanData as $bulan => $nilai)
                        @php
                            $nilai = $nilai ?? 0;
                            $total += $nilai;
                            $totalPerBulan[$bulan] += $nilai;
                            $grandTotal += $nilai;
                        @endphp
                        <td>{{ number_format($nilai, 0, ',', '.') }}</td>
                    @endforeach
                    <td style="font-weight: bold;">{{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                @foreach($totalPerBulan as $totalBulan)
                    <th>{{ number_format($totalBulan, 0, ',', '.') }}</th>
                @endforeach
                <th>{{ number_format($grandTotal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>