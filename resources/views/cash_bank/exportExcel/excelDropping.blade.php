<table>
    <tr>
        <td colspan="7" style="font-weight:bold; font-size:14px; text-align:center;">
            DROPPING — {{ strtoupper($namaKategori) }} / {{ strtoupper($namaSub) }}
        </td>
    </tr>
    <tr>
        <td colspan="7" style="text-align:center;">{{ $namaBulan }} {{ $tahun }}</td>
    </tr>
    <tr><td colspan="7"></td></tr>
    <tr>
        <th style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000; text-align:center;">No</th>
        <th style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000; text-align:center;">Item</th>
        <th style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000; text-align:center;">Minggu 1</th>
        <th style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000; text-align:center;">Minggu 2</th>
        <th style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000; text-align:center;">Minggu 3</th>
        <th style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000; text-align:center;">Minggu 4</th>
        <th style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000; text-align:center;">Jumlah</th>
    </tr>

    @php
        $totM1 = 0; $totM2 = 0; $totM3 = 0; $totM4 = 0;
    @endphp

    @foreach($items as $i => $item)
        @php
            $d = $data[$item->id_item_sub_kriteria] ?? ['M1' => 0, 'M2' => 0, 'M3' => 0, 'M4' => 0];
            $jumlah = $d['M1'] + $d['M2'] + $d['M3'] + $d['M4'];
            $totM1 += $d['M1']; $totM2 += $d['M2']; $totM3 += $d['M3']; $totM4 += $d['M4'];
        @endphp
        <tr>
            <td style="border:1px solid #000; text-align:center;">{{ $i + 1 }}</td>
            <td style="border:1px solid #000;">{{ $item->nama_item_sub_kriteria }}</td>
            <td style="border:1px solid #000;">{{ $d['M1'] }}</td>
            <td style="border:1px solid #000;">{{ $d['M2'] }}</td>
            <td style="border:1px solid #000;">{{ $d['M3'] }}</td>
            <td style="border:1px solid #000;">{{ $d['M4'] }}</td>
            <td style="border:1px solid #000; font-weight:bold;">{{ $jumlah }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="2" style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000;">TOTAL</td>
        <td style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000;">{{ $totM1 }}</td>
        <td style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000;">{{ $totM2 }}</td>
        <td style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000;">{{ $totM3 }}</td>
        <td style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000;">{{ $totM4 }}</td>
        <td style="font-weight:bold; background:#1e3a5f; color:#ffffff; border:1px solid #000;">{{ $totM1 + $totM2 + $totM3 + $totM4 }}</td>
    </tr>
</table>
