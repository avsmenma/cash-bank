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
    .spp-row-header th {
        background-color: #2c5282 !important;
        color: #fff !important;
        font-weight: bold;
        text-align: center;
        padding: 6px 8px !important;
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

    /* Pagination */
    .spp-pagination-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 4px;
        font-size: 13px;
        color: #555;
        flex-wrap: wrap;
        gap: 8px;
    }
    .spp-pagination-bar .spp-page-info { font-weight: 500; }
    .spp-pagination-bar .spp-page-buttons {
        display: flex;
        gap: 4px;
    }
    .spp-pagination-bar .spp-page-btn {
        border: 1px solid #ccc;
        background: #fff;
        color: #333;
        padding: 4px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        transition: all .15s;
    }
    .spp-pagination-bar .spp-page-btn:hover { background: #e8eef5; border-color: #2c5282; color: #2c5282; }
    .spp-pagination-bar .spp-page-btn.active { background: #2c5282; color: #fff; border-color: #2c5282; }
    .spp-pagination-bar .spp-page-btn:disabled { opacity: .4; cursor: not-allowed; }
</style>

@if(isset($allData) && count($allData) > 0)
    <div class="table-responsive">
        <table class="spp-grouped-table">
            <thead>
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
            </thead>
            <tbody>
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
            </tbody>
        </table>
    </div>

    {{-- Pagination Controls --}}
    @if(isset($pagination))
    <div class="spp-pagination-bar">
        <div class="spp-page-info">
            Menampilkan {{ $pagination['from'] }} - {{ $pagination['to'] }} dari {{ number_format($pagination['total_records'], 0, ',', '.') }} data
        </div>
        @if($pagination['total_pages'] > 1)
        <div class="spp-page-buttons">
            <button class="spp-page-btn" data-page="1" {{ $pagination['current_page'] <= 1 ? 'disabled' : '' }}>
                <i class="fas fa-angle-double-left"></i>
            </button>
            <button class="spp-page-btn" data-page="{{ $pagination['current_page'] - 1 }}" {{ $pagination['current_page'] <= 1 ? 'disabled' : '' }}>
                <i class="fas fa-angle-left"></i>
            </button>

            @php
                $startP = max(1, $pagination['current_page'] - 2);
                $endP = min($pagination['total_pages'], $pagination['current_page'] + 2);
            @endphp
            @for($p = $startP; $p <= $endP; $p++)
                <button class="spp-page-btn {{ $p == $pagination['current_page'] ? 'active' : '' }}" data-page="{{ $p }}">{{ $p }}</button>
            @endfor

            <button class="spp-page-btn" data-page="{{ $pagination['current_page'] + 1 }}" {{ $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' }}>
                <i class="fas fa-angle-right"></i>
            </button>
            <button class="spp-page-btn" data-page="{{ $pagination['total_pages'] }}" {{ $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' }}>
                <i class="fas fa-angle-double-right"></i>
            </button>
        </div>
        @endif
    </div>
    @endif
@else
    <div class="spp-empty-state">
        <i class="fas fa-inbox"></i>
        <p>Tidak ada data SPP untuk periode ini.</p>
    </div>
@endif
