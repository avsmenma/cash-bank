<style>
    .spp-grouped-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }
    .spp-grouped-table th,
    .spp-grouped-table td {
        border: 1px solid #bbb;
        border-top: 0;
        border-left: 0;
        padding: 4px 8px;
        white-space: nowrap;
        vertical-align: middle;
    }
    .spp-grouped-table th:first-child,
    .spp-grouped-table td:first-child {
        border-left: 1px solid #bbb;
    }
    .spp-grouped-table thead th {
        border-top: 1px solid #bbb;
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

    /* Scrollbox infinite scroll: header menempel, baris berikutnya dimuat saat
       scroll mendekati dasar (tanpa pagination). */
    .spp-scroll {
        max-height: calc(100vh - 330px);
        min-height: 300px;
        overflow: auto;
        position: relative;
    }
    .spp-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 5;
    }

    .spp-load-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 4px;
        font-size: 13px;
        color: #555;
        flex-wrap: wrap;
        gap: 8px;
        font-weight: 500;
    }
    .spp-loader-row td {
        text-align: center !important;
        padding: 12px !important;
        font-weight: 600;
        color: #2c5282;
        background: #f0f5fb;
    }
    .spp-loader-row.has-error td {
        color: #b02a37;
        background: #fdf0f1;
        cursor: pointer;
    }
</style>

@if(isset($allData) && count($allData) > 0)
    <div class="table-responsive spp-scroll" id="sppScrollBox"
         data-total="{{ $pagination['total_records'] ?? count($allData) }}"
         data-page="{{ $pagination['current_page'] ?? 1 }}"
         data-per-page="{{ $pagination['per_page'] ?? count($allData) }}">
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
            <tbody id="sppTableBody">
                @include('cash_bank.dataSPPRows', ['allData' => $allData, 'startIndex' => $startIndex])
            </tbody>
        </table>
    </div>

    <div class="spp-load-status">
        <div id="sppLoadInfo">
            {{ number_format(min(($startIndex ?? 0) + count($allData), $pagination['total_records'] ?? count($allData)), 0, ',', '.') }}
            dari {{ number_format($pagination['total_records'] ?? count($allData), 0, ',', '.') }} data dimuat
        </div>
    </div>
@else
    <div class="spp-empty-state">
        <i class="fas fa-inbox"></i>
        <p>Tidak ada data SPP untuk periode ini.</p>
    </div>
@endif
