{{--
  Baris tabel Daftar SPP (dipakai render awal & chunk lanjutan infinite scroll).
  Variabel: $allData, $startIndex
--}}
@php $rowNo = $startIndex + 1; @endphp
@foreach($allData as $row)
    @php
        $nilai = (float) ($row->nilai_rupiah ?? 0);

        $statusLabel = '-';
        $statusClass = '';
        if ($row->status_pembayaran === 'sudah_dibayar') {
            $statusLabel = 'Sudah Dibayar';
            $statusClass = 'badge-sudah';
        } elseif ($row->status_pembayaran === 'siap_dibayar') {
            $statusLabel = 'Siap Bayar';
            $statusClass = 'badge-siap';
        } else {
            $statusLabel = 'Belum Siap';
            $statusClass = 'badge-belum';
        }
    @endphp
    <tr class="spp-row-data">
        <td class="text-center">{{ $rowNo++ }}</td>
        <td>{{ $row->nomor_agenda ?? '-' }}</td>
        <td class="text-center">{{ $row->tanggal_masuk ? \Carbon\Carbon::parse($row->tanggal_masuk)->translatedFormat('d M Y') : '-' }}</td>
        <td>{{ $row->nomor_spp ?? '-' }}</td>
        <td class="text-center">{{ $row->tanggal_spp ? \Carbon\Carbon::parse($row->tanggal_spp)->translatedFormat('d M Y') : '-' }}</td>
        <td style="white-space:normal; max-width:250px;">{{ $row->uraian_spp ?? '-' }}</td>
        <td class="text-center">{{ $row->tanggal_spk ? \Carbon\Carbon::parse($row->tanggal_spk)->translatedFormat('d M Y') : '-' }}</td>
        <td class="text-center">{{ $row->tanggal_berakhir_spk ? \Carbon\Carbon::parse($row->tanggal_berakhir_spk)->translatedFormat('d M Y') : '-' }}</td>
        <td class="text-center">{{ $row->tanggal_berita_acara ? \Carbon\Carbon::parse($row->tanggal_berita_acara)->translatedFormat('d M Y') : '-' }}</td>
        <td>{{ $row->dibayar_kepada ?? '-' }}</td>
        <td class="text-right">{{ number_format($nilai, 0, ',', '.') }}</td>
        <td>{{ $row->current_handler ?? '-' }}</td>
        <td class="text-center">
            <span class="badge-status {{ $statusClass }}">{{ $statusLabel }}</span>
        </td>
    </tr>
@endforeach
