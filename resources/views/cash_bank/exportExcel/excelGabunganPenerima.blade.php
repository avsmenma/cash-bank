<table>
    <thead>
        <tr>
            <th rowspan="2"
                style="background-color: #343a40; color: white; font-weight: bold; vertical-align: middle; text-align: center;">
                Kategori</th>
            @foreach($bulanListFiltered as $namaBulan => $noBulan)
                <th colspan="4" style="background-color: #343a40; color: white; font-weight: bold; text-align: center;">
                    {{ ucfirst($namaBulan) }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($bulanListFiltered as $namaBulan => $noBulan)
                <th style="background-color: #17a2b8; color: white; text-align: center;">Rencana</th>
                <th style="background-color: #28a745; color: white; text-align: center;">Realisasi</th>
                <th style="background-color: #ffc107; color: black; text-align: center;">Selisih</th>
                <th style="background-color: #6c757d; color: white; text-align: center;">%</th>
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
                    <td style="text-align: right;">{{ number_format($d['rencana'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($d['realisasi'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($d['selisih'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($d['persen'], 2, ',', '.') }}%</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>