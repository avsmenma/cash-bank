<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rencana Penerima {{ $tahun }}</title>
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

        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        tfoot th {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Rencana Penerima (Permintaan)</h2>
        <p>Tahun: {{ $tahun }}</p>
    </div>

    @php
        $bulanList = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
        $totalPerBulan = array_fill_keys($bulanList, 0);
        $grandTotal = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                @foreach($bulanList as $b)
                    <th>{{ ucfirst(substr($b, 0, 3)) }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kategori as $k)
                @php
                    $row = $data[$k->id_kategori_kriteria] ?? null;
                    $total = 0;
                @endphp
                <tr>
                    <td>{{ $k->nama_kriteria }}</td>
                    @foreach($bulanList as $b)
                        @php
                            $nilai = $row->$b ?? 0;
                            $total += $nilai;
                            $totalPerBulan[$b] += $nilai;
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
                @foreach($bulanList as $b)
                    <th>{{ number_format($totalPerBulan[$b], 0, ',', '.') }}</th>
                @endforeach
                <th>{{ number_format($grandTotal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>