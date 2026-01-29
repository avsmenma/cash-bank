@php
    $bulanList = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
    $totalPerBulan = array_fill_keys(array_keys(array_fill(1, 12, 0)), 0);
    $grandTotal = 0;
@endphp
<table>
    <thead>
        <tr>
            <th style="background-color: #343a40; color: white; font-weight: bold;">Kategori</th>
            @foreach($bulanList as $index => $b)
                <th style="background-color: #343a40; color: white; font-weight: bold;">{{ ucfirst($b) }}</th>
            @endforeach
            <th style="background-color: #343a40; color: white; font-weight: bold;">Total</th>
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
                    <td style="text-align: right;">{{ number_format($nilai, 0, ',', '.') }}</td>
                @endforeach
                <td style="text-align: right; font-weight: bold;">{{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th style="background-color: #343a40; color: white; font-weight: bold;">Total</th>
            @foreach($totalPerBulan as $totalBulan)
                <th style="background-color: #343a40; color: white; font-weight: bold; text-align: right;">
                    {{ number_format($totalBulan, 0, ',', '.') }}</th>
            @endforeach
            <th style="background-color: #343a40; color: white; font-weight: bold; text-align: right;">
                {{ number_format($grandTotal, 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>