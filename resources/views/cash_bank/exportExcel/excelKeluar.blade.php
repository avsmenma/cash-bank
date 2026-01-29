<table>
    <thead>
        <tr>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">No</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Agenda</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Tanggal</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Sumber Dana</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Bank Tujuan</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Kriteria</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Sub Kriteria</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Item Sub Kriteria</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Penerima</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Uraian</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Jenis Pembayaran</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Kredit</th>
            <th style="background-color: #dc3545; color: white; font-weight: bold;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->agenda_tahun }}</td>
                <td>{{ $item->tanggal }}</td>
                <td>{{ $item->sumberDana->nama_sumber_dana ?? '-' }}</td>
                <td>{{ $item->bankTujuan->nama_tujuan ?? '-' }}</td>
                <td>{{ $item->kategori->nama_kriteria ?? '-' }}</td>
                <td>{{ $item->subKriteria->nama_sub ?? '-' }}</td>
                <td>{{ $item->itemSubKriteria->nama_item ?? '-' }}</td>
                <td>{{ $item->penerima }}</td>
                <td>{{ $item->uraian }}</td>
                <td>{{ $item->jenisPembayaran->nama_jenis_pembayaran ?? '-' }}</td>
                <td style="text-align: right;">{{ number_format($item->kredit, 2, ',', '.') }}</td>
                <td>{{ $item->keterangan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>