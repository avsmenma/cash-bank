<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<table>
    <thead>
        <tr>
            <th colspan="{{ count($bulanListFiltered) * 3 + 6 }}" style="font-size:14px; font-weight:bold; text-align:center;">
                REKAPAN CASHFLOW PVD TAHUN {{ $tahun }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ count($bulanListFiltered) * 3 + 6 }}" style="font-size:11px; text-align:center;">
                Periode: Bulan {{ reset($bulanListFiltered) }} s/d {{ end($bulanListFiltered) }} {{ $tahun }}
            </th>
        </tr>
        <tr>
            <th rowspan="2" style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center; vertical-align:middle;">No.</th>
            <th rowspan="2" style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center; vertical-align:middle;">Payments for {{ $tahun }} transactions - Accounts</th>
            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                <th colspan="3" style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">{{ $namaBulan }} {{ $tahun }}</th>
            @endforeach
            <th colspan="3" style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">Total</th>
            <th colspan="2" style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">%Tase Pembayaran Thdp</th>
        </tr>
        <tr>
            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">Permintaan RD (1)</th>
                <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">Dropping HO (2)</th>
                <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">Pembayaran (3)</th>
            @endforeach
            <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">Permintaan RD (1)</th>
            <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">Dropping HO (2)</th>
            <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">Pembayaran (3)</th>
            <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">Permintaan (1)</th>
            <th style="font-weight:bold; background-color:#1e3a5f; color:#ffffff; border:1px solid #000000; text-align:center;">Dropping (2)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pvdRows as $row)
            @php
                $type = $row['type'];
                $bg = '#ffffff';
                $color = '#000000';
                $bold = false;
                if ($type === 'kat' || $type === 'kattotal' || $type === 'grand') {
                    $bg = '#1e3a5f';
                    $color = '#ffffff';
                    $bold = true;
                } elseif ($type === 'sub') {
                    $bg = '#f1f5f9';
                    $color = '#1e3a5f';
                    $bold = true;
                } elseif ($type === 'subtotal') {
                    $bg = '#e2e8f0';
                    $bold = true;
                }
            @endphp
            <tr>
                <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:center;">
                    {{ $row['no'] }}
                </td>
                <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1;">
                    {{ $row['uraian'] }}
                </td>
                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                    <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:right;">
                        {{ $row['m' . $noBulan . '_p'] !== null ? number_format($row['m' . $noBulan . '_p'], 0, ',', '.') : '' }}
                    </td>
                    <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:right;">
                        {{ $row['m' . $noBulan . '_d'] !== null ? number_format($row['m' . $noBulan . '_d'], 0, ',', '.') : '' }}
                    </td>
                    <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:right;">
                        {{ $row['m' . $noBulan . '_b'] !== null ? number_format($row['m' . $noBulan . '_b'], 0, ',', '.') : '' }}
                    </td>
                @endforeach
                <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:right;">
                    {{ $row['tot_p'] !== null ? number_format($row['tot_p'], 0, ',', '.') : '' }}
                </td>
                <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:right;">
                    {{ $row['tot_d'] !== null ? number_format($row['tot_d'], 0, ',', '.') : '' }}
                </td>
                <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:right;">
                    {{ $row['tot_b'] !== null ? number_format($row['tot_b'], 0, ',', '.') : '' }}
                </td>
                <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:right;">
                    {{ $row['pct_p'] !== null ? number_format($row['pct_p'], 2, ',', '.') . '%' : '' }}
                </td>
                <td style="background-color:{{ $bg }}; color:{{ $color }}; {{ $bold ? 'font-weight:bold;' : '' }} border:1px solid #cbd5e1; text-align:right;">
                    {{ $row['pct_d'] !== null ? number_format($row['pct_d'], 2, ',', '.') . '%' : '' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>