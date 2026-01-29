<table>
    <thead>
        <tr>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">No</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">Tanggal</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">Kategori</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">Pembeli</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">No. Reg</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">Kontrak</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">Volume</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">Harga</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">Nilai</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">PPN</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">Pot. PPN</th>
            <th style="background-color: #4CAF50; color: white; font-weight: bold;">Nilai Inc. PPN</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->tanggal }}</td>
                <td>{{ $item->kategori->nama_kriteria ?? '-' }}</td>
                <td>{{ $item->pembeli }}</td>
                <td>{{ $item->no_reg }}</td>
                <td>{{ $item->kontrak }}</td>
                <td style="text-align: right;">{{ number_format($item->volume, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($item->harga, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($item->nilai, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($item->ppn, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($item->potppn, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format(($item->nilai + $item->ppn - $item->potppn), 2, ',', '.') }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>