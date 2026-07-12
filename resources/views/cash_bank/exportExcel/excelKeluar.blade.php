<table>
    {{-- Kop laporan --}}
    <tr>
        <td colspan="13" style="font-weight: bold; font-size: 16px; color: #1E3A5F;">LAPORAN BANK KELUAR</td>
    </tr>
    <tr>
        <td colspan="13" style="font-size: 11px; color: #6B7280;">{{ $filterInfo }} • {{ number_format($data->count(), 0, ',', '.') }} transaksi • diexport {{ now()->format('d/m/Y H:i') }}</td>
    </tr>
    <tr><td></td></tr>

    {{-- Header tabel (baris 4) --}}
    <tr>
        @foreach (['No', 'Agenda', 'Tanggal', 'Sumber Dana', 'Bank Tujuan', 'Kriteria', 'Sub Kriteria', 'Item Sub Kriteria', 'Penerima', 'Uraian', 'Jenis Pembayaran', 'Kredit (Rp)', 'Keterangan'] as $judul)
            <th style="background-color: #1E3A5F; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #FFFFFF;">{{ $judul }}</th>
        @endforeach
    </tr>

    {{-- Data (mulai baris 5) --}}
    @foreach ($data as $index => $item)
        @php $bg = $loop->even ? 'background-color: #F5F8FB;' : ''; @endphp
        <tr>
            <td style="{{ $bg }} border: 1px solid #B3BFCC; text-align: center;">{{ $index + 1 }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC; text-align: center;">{{ $item->agenda_tahun ?: '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC; text-align: center;">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC;">{{ $item->sumberDana->nama_sumber_dana ?? '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC;">{{ $item->bankTujuan->nama_tujuan ?? '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC;">{{ $item->kategori->nama_kriteria ?? '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC;">{{ $item->subKriteria->nama_sub_kriteria ?? '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC;">{{ $item->itemSubKriteria->nama_item_sub_kriteria ?? '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC;">{{ $item->penerima ?: '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC;">{{ $item->uraian ?: '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC;">{{ $item->jenisPembayaran->nama_jenis_pembayaran ?? '-' }}</td>
            {{-- angka mentah agar Excel membacanya sebagai numerik (bukan teks) --}}
            <td style="{{ $bg }} border: 1px solid #B3BFCC; text-align: right;">{{ $item->kredit + 0 }}</td>
            <td style="{{ $bg }} border: 1px solid #B3BFCC;">{{ $item->keterangan ?: '-' }}</td>
        </tr>
    @endforeach

    {{-- Baris total --}}
    @if ($data->count())
        <tr>
            <td colspan="11" style="background-color: #1E3A5F; color: #FFFFFF; font-weight: bold; text-align: right; border: 1px solid #FFFFFF;">TOTAL</td>
            <td style="background-color: #1E3A5F; color: #FFFFFF; font-weight: bold; text-align: right; border: 1px solid #FFFFFF;">{{ $data->sum('kredit') + 0 }}</td>
            <td style="background-color: #1E3A5F; border: 1px solid #FFFFFF;"></td>
        </tr>
    @endif
</table>
