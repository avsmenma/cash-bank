<style>
    .spp-grouped-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .spp-grouped-table th,
    .spp-grouped-table td {
        border: 1px solid #bbb;
        padding: 4px 8px;
        white-space: nowrap;
        vertical-align: middle;
    }
    .spp-row-month-title td {
        background-color: #1a3256 !important;
        color: #fff;
        font-weight: bold;
        font-size: 14px;
        padding: 8px 10px !important;
    }
    .spp-row-header th {
        background-color: #2c5282 !important;
        color: #fff;
        font-weight: bold;
        text-align: center;
        padding: 6px 8px !important;
    }
    .spp-row-subtotal td {
        background-color: #fff3cd !important;
        font-weight: bold;
        border-top: 2px solid #c9a825;
    }
    .spp-grouped-table tbody tr.spp-row-data:hover {
        background-color: #e8eef5 !important;
    }
    .spp-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #888;
    }
    .spp-empty-state i {
        font-size: 40px;
        margin-bottom: 10px;
        display: block;
    }
    .badge-status {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .badge-belum { background: #ffeeba; color: #856404; }
    .badge-siap { background: #b8daff; color: #004085; }
    .badge-sudah { background: #c3e6cb; color: #155724; }
</style>

@if(isset($grouped) && count($grouped) > 0)
    <div class="table-responsive">
        <table class="spp-grouped-table">

            @foreach($grouped as $bulanNum => $rows)
                <tbody>
                    {{-- Month title row --}}
                    <tr class="spp-row-month-title">
                        <td colspan="13">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            DAFTAR SPP — {{ $bulanNames[$bulanNum] ?? '' }} {{ $tahun }}
                        </td>
                    </tr>
                    {{-- Column header row --}}
                    <tr class="spp-row-header">
                        <th style="width:35px;">No</th>
                        <th>No Agenda</th>
                        <th>Tanggal Masuk</th>
                        <th>No SPP</th>
                        <th>Tanggal SPP</th>
                        <th>Uraian SPP</th>
                        <th>Tanggal SPK</th>
                        <th>Tgl Berakhir SPK</th>
                        <th>Tanggal BA</th>
                        <th>Dibayar Kepada</th>
                        <th>Nilai Rupiah</th>
                        <th>Posisi Dokumen</th>
                        <th>Status</th>
                    </tr>

                    @php
                        $rowNo = 1;
                        $monthTotalNilai = 0;
                    @endphp

                    @foreach($rows as $row)
                        @php
                            $nilai = (float) ($row->nilai_rupiah ?? 0);
                            $monthTotalNilai += $nilai;

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

                    {{-- Subtotal row per month --}}
                    <tr class="spp-row-subtotal">
                        <td colspan="10" class="text-left">TOTAL {{ $bulanNames[$bulanNum] ?? '' }} ({{ $rowNo - 1 }} data)</td>
                        <td class="text-right">{{ number_format($monthTotalNilai, 0, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>

                    {{-- Spacer row between months --}}
                    <tr><td colspan="13" style="border:none; height:16px; background:#f0f0f0;"></td></tr>
                </tbody>
            @endforeach
        </table>
    </div>
@else
    <div class="spp-empty-state">
        <i class="fas fa-inbox"></i>
        <p>Tidak ada data SPP untuk periode ini.</p>
    </div>
@endif
