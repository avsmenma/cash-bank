<table>
    <tr>
        <th colspan="{{ count($bulanListFiltered) + 2 }}" style="font-size:14px; font-weight:bold; text-align:center;">
            REKAPAN CASHFLOW PD TAHUN {{ $tahun }}
        </th>
    </tr>
    <tr>
        <th colspan="{{ count($bulanListFiltered) + 2 }}" style="font-size:11px; text-align:center;">
            Periode: Bulan {{ reset($bulanListFiltered) }} s/d {{ end($bulanListFiltered) }} {{ $tahun }}
        </th>
    </tr>
    <tr><td colspan="{{ count($bulanListFiltered) + 2 }}"></td></tr>
    <thead>
        <tr>
            <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">URAIAN</th>
            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">{{ $namaBulan }}</th>
            @endforeach
            <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cfRows as $row)
            @php
                $type = $row['type'];
                $bg = '#ffffff';
                $color = '#000000';
                $bold = false;
                if ($type === 'section') {
                    $bg = '#dbeafe';
                    $color = '#1e3a5f';
                    $bold = true;
                } elseif ($type === 'kat') {
                    $bg = '#f1f5f9';
                    $color = '#1e3a5f';
                    $bold = true;
                } elseif ($type === 'total' || $type === 'selisih') {
                    $bg = '#1e3a5f';
                    $color = '#ffffff';
                    $bold = true;
                } elseif ($type === 'subtotal' || $type === 'gaji') {
                    $bg = '#e2e8f0';
                    $bold = true;
                } elseif ($type === 'summary') {
                    $bg = '#fef3c7';
                    $bold = true;
                }
            @endphp
            <tr>
                <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1;">
                    {{ $row['uraian'] }}
                </td>
                @foreach($bulanListFiltered as $b => $n)
                    @php
                        $val = $row['m' . $b] ?? null;
                    @endphp
                    <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:right;">
                        {{ $val !== null ? number_format($val, 0, ',', '.') : '' }}
                    </td>
                @endforeach
                <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:right;">
                    {{ $row['total'] !== null ? number_format($row['total'], 0, ',', '.') : '' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
