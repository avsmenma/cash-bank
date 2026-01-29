@php
    $bulanList = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
    $totalPerBulan = array_fill_keys($bulanList, 0);
    $grandTotal = 0;
@endphp
<table>
    <thead>
        <tr>
            <th style="background-color: #343a40; color: white; font-weight: bold;">Kategori</th>
            @foreach($bulanList as $b)
                <th style="background-color: #343a40; color: white; font-weight: bold;">{{ ucfirst($b) }}</th>
            @endforeach
            <th style="background-color: #343a40; color: white; font-weight: bold;">Total</th>
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
                    <td style="text-align: right;">{{ number_format($nilai, 0, ',', '.') }}</td>
                @endforeach
                <td style="text-align: right; font-weight: bold;">{{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th style="background-color: #343a40; color: white; font-weight: bold;">Total</th>
            @foreach($bulanList as $b)
                <th style="background-color: #343a40; color: white; font-weight: bold; text-align: right;">
                    {{ number_format($totalPerBulan[$b], 0, ',', '.') }}</th>
            @endforeach
            <th style="background-color: #343a40; color: white; font-weight: bold; text-align: right;">
                {{ number_format($grandTotal, 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>