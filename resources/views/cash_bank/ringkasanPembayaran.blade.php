@extends('layouts/index')

@push('styles')
<style>
/* ============================================
   RINGKASAN PEMBAYARAN — MODERN HIERARKI
   ============================================ */

/* Page background */
.ringkasan-wrapper {
    background: #F8FAFC;
    min-height: 100vh;
    padding: 0;
}

/* Filter card */
.filter-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #E2E8F0;
    margin-bottom: 24px;
    overflow: hidden;
}
.filter-card .card-header {
    background: linear-gradient(135deg, #083E40 0%, #0D6B5F 100%);
    color: white;
    padding: 16px 24px;
    border: none;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 0.3px;
}
.filter-card .card-body {
    padding: 20px 24px;
}

/* Main table card */
.hierarchy-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    border: 1px solid #E2E8F0;
    overflow: hidden;
}

/* Period badge */
.period-badge {
    background: linear-gradient(135deg, #0D9488, #14B8A6);
    color: white;
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
    margin: 16px 24px;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px rgba(13,148,136,0.3);
}

/* TABLE STYLES */
.hierarchy-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.hierarchy-table thead th {
    background: linear-gradient(135deg, #083E40 0%, #0A4F47 100%);
    color: white;
    padding: 14px 16px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 700;
    white-space: nowrap;
    border: none;
    position: sticky;
    top: 0;
    z-index: 10;
}
.hierarchy-table thead th:first-child {
    border-radius: 0;
}
.hierarchy-table thead th:last-child {
    border-radius: 0;
}

/* Level 1 - Kriteria */
.row-level1 {
    background: #E6F4F1;
    border-left: 5px solid #0D9488;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.row-level1:hover {
    background: #D4EDE8 !important;
}
.row-level1 td {
    padding: 14px 16px;
    border-bottom: 1px solid #CCEDE8;
    color: #064E3B;
}
.row-level1 .collapse-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: #0D9488;
    color: white;
    font-size: 10px;
    margin-right: 10px;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
}
.row-level1.collapsed .collapse-icon {
    transform: rotate(-90deg);
}

/* Level 2 - Golongan / Sub Kriteria */
.row-level2 {
    background: #F0FAF8;
    border-left: 4px solid #14B8A6;
    cursor: pointer;
    transition: all 0.15s ease;
}
.row-level2 td {
    padding: 11px 16px;
    padding-left: 32px;
    border-bottom: 1px solid #E6F4F1;
    color: #0F766E;
    font-weight: 500;
}
.row-level2:hover {
    background: #CCEDE8 !important;
}
.row-level2:hover .nilai-cell {
    color: #065F46;
    font-weight: 700;
}
.row-level2:hover .detail-hint {
    opacity: 1;
    transform: translateX(0);
}

/* Level 3 - Item Sub Kriteria */
.row-level3 {
    background: #FFFFFF;
    border-left: 3px solid #99E6DC;
    cursor: pointer;
    transition: all 0.15s ease;
}
.row-level3 td {
    padding: 10px 16px;
    padding-left: 52px;
    border-bottom: 1px solid #F1F5F9;
    color: #334155;
    font-size: 12.5px;
}
.row-level3:hover {
    background: #CCEDE8 !important;
}
.row-level3:hover .nilai-cell {
    color: #065F46;
    font-weight: 700;
}
.row-level3:hover .detail-hint {
    opacity: 1;
    transform: translateX(0);
}

/* Detail hint */
.detail-hint {
    font-size: 10px;
    color: #0D9488;
    opacity: 0;
    transform: translateX(-8px);
    transition: all 0.2s ease;
    margin-left: 8px;
    white-space: nowrap;
    font-weight: 600;
}

/* Total rows */
.row-subtotal {
    background: linear-gradient(135deg, #E6F4F1, #D4EDE8);
    font-weight: 700;
    border-left: 5px solid #0D9488;
}
.row-subtotal td {
    padding: 12px 16px;
    border-bottom: 2px solid #CCEDE8;
    color: #064E3B;
    font-size: 12.5px;
}

.row-grandtotal {
    background: linear-gradient(135deg, #083E40 0%, #0A4F47 100%);
    font-weight: 800;
}
.row-grandtotal td {
    padding: 16px 16px;
    color: white !important;
    font-size: 14px;
    border: none;
}
.row-grandtotal .nilai-cell {
    color: #5EEAD4 !important;
}

/* Nilai cells */
.nilai-cell {
    text-align: right;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    color: #0D9488;
    font-weight: 600;
    white-space: nowrap;
    letter-spacing: -0.3px;
}

/* Row animation */
.child-row {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.child-row.hidden-row {
    display: none;
}

/* ============================================
   DRAWER STYLES
   ============================================ */
.drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(4px);
    z-index: 1050;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}
.drawer-overlay.active {
    opacity: 1;
    pointer-events: all;
}

.drawer-panel {
    position: fixed;
    top: 0;
    right: -680px;
    width: 680px;
    height: 100vh;
    background: white;
    z-index: 1051;
    box-shadow: -8px 0 32px rgba(0,0,0,0.15);
    transition: right 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
}
.drawer-panel.open {
    right: 0;
}

.drawer-header {
    background: linear-gradient(135deg, #083E40 0%, #0D6B5F 100%);
    padding: 24px 28px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    color: white;
    flex-shrink: 0;
}
.drawer-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: -0.2px;
}
.drawer-header .drawer-subtitle {
    font-size: 12px;
    color: rgba(255,255,255,0.7);
    margin-top: 4px;
}
.drawer-close-btn {
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    flex-shrink: 0;
}
.drawer-close-btn:hover {
    background: rgba(255,255,255,0.3);
}

.drawer-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1px;
    background: #E2E8F0;
    border-bottom: 1px solid #E2E8F0;
    flex-shrink: 0;
}
.drawer-stat {
    background: #F8FAFC;
    padding: 16px 24px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.drawer-stat .stat-label {
    font-size: 10px;
    color: #94A3B8;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 600;
}
.drawer-stat .stat-value {
    font-size: 20px;
    font-weight: 800;
    color: #0D9488;
    font-family: 'Consolas', 'Monaco', monospace;
}

.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}

.drawer-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.drawer-table thead th {
    background: #F1F5F9;
    padding: 10px 14px;
    text-align: left;
    font-size: 10px;
    text-transform: uppercase;
    color: #64748B;
    letter-spacing: 0.5px;
    font-weight: 700;
    position: sticky;
    top: 0;
    z-index: 5;
    border-bottom: 2px solid #E2E8F0;
}
.drawer-table tbody td {
    padding: 10px 14px;
    border-bottom: 1px solid #F1F5F9;
    vertical-align: top;
    color: #334155;
}
.drawer-table tbody tr:hover td {
    background: #F8FAFC;
}
.drawer-table .nilai-cell {
    font-weight: 700;
    color: #0D9488;
}

/* Loading shimmer */
.shimmer {
    background: linear-gradient(90deg, #F1F5F9 25%, #E2E8F0 50%, #F1F5F9 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
    height: 14px;
}
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Button styles */
.btn-filter {
    background: linear-gradient(135deg, #0D9488, #14B8A6);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(13,148,136,0.3);
}
.btn-filter:hover {
    background: linear-gradient(135deg, #0F766E, #0D9488);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13,148,136,0.4);
}
.btn-reset {
    background: white;
    color: #64748B;
    border: 2px solid #E2E8F0;
    padding: 9px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s;
}
.btn-reset:hover {
    background: #F8FAFC;
    color: #334155;
    border-color: #CBD5E1;
}

/* Responsive */
.table-scroll-wrapper {
    overflow-x: auto;
    border-radius: 0 0 16px 16px;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #94A3B8;
}
.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}
.empty-state h4 {
    color: #64748B;
    font-weight: 600;
    margin-bottom: 8px;
}
.empty-state p {
    font-size: 13px;
}
</style>
@endpush

@section('content')
<div class="ringkasan-wrapper">
    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-weight:800; color:#083E40;">📊 Ringkasan Pembayaran</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Ringkasan Pembayaran</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- FILTER CARD -->
        <div class="filter-card card">
            <div class="card-header">
                <i class="fas fa-sliders-h mr-2"></i> Filter Periode
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('ringkasan.index') }}" id="form-filter-ringkasan">
                    <div class="row align-items-end">
                        <div class="col-md-2">
                            <label style="font-weight:600; color:#334155; font-size:12px;">TAHUN</label>
                            <select class="form-control select2" name="tahun" id="filter-tahun">
                                @for($y = date('Y') - 2; $y <= date('Y') + 5; $y++)
                                    <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label style="font-weight:600; color:#334155; font-size:12px;">DARI BULAN</label>
                            <select class="form-control" name="dari_bulan" id="filter-dari-bulan">
                                @foreach($bulanMap as $noBulan => $namaBulan)
                                    <option value="{{ $noBulan }}" {{ $noBulan == $dariBulan ? 'selected' : '' }}>{{ $namaBulan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label style="font-weight:600; color:#334155; font-size:12px;">SAMPAI BULAN</label>
                            <select class="form-control" name="sampai_bulan" id="filter-sampai-bulan">
                                @foreach($bulanMap as $noBulan => $namaBulan)
                                    <option value="{{ $noBulan }}" {{ $noBulan == $sampaiBulan ? 'selected' : '' }}>{{ $namaBulan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex" style="gap:10px;">
                            <button type="submit" class="btn btn-filter">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <button type="button" class="btn btn-reset" id="btn-reset-ringkasan">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- HIERARCHY TABLE CARD -->
        <div class="hierarchy-card">
            <div class="period-badge">
                <i class="fas fa-calendar-alt mr-1"></i>
                Periode: {{ $bulanMap[$dariBulan] ?? '' }} — {{ $bulanMap[$sampaiBulan] ?? '' }} {{ $tahun }}
            </div>

            <div class="table-scroll-wrapper">
                @if(count($hierarki) > 0)
                <table class="hierarchy-table" id="tbl-ringkasan">
                    <thead>
                        <tr>
                            <th style="min-width:320px;">Uraian</th>
                            @foreach($bulanAktif as $bNum => $bName)
                                <th style="text-align:right; min-width:140px;">{{ $bName }} {{ $tahun }}</th>
                            @endforeach
                            <th style="text-align:right; min-width:160px;">
                                Sd {{ $bulanMap[$sampaiBulan] ?? '' }} {{ $tahun }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $katIndex = 0; @endphp
                        @foreach($hierarki as $katId => $kat)
                            @php $katIndex++; @endphp
                            {{-- LEVEL 1: Kriteria --}}
                            <tr class="row-level1" data-toggle-group="kat-{{ $katId }}">
                                <td>
                                    <span class="collapse-icon"><i class="fas fa-chevron-down" style="font-size:9px;"></i></span>
                                    {{ $kat['nama'] }}
                                </td>
                                @foreach($bulanAktif as $bNum => $bName)
                                    <td class="nilai-cell" style="color:#064E3B;">
                                        {{ number_format($kat['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                                    </td>
                                @endforeach
                                <td class="nilai-cell" style="color:#064E3B; font-size:14px;">
                                    {{ number_format($kat['total'], 0, ',', '.') }}
                                </td>
                            </tr>

                            @foreach($kat['subs'] as $subKey => $sub)
                                {{-- LEVEL 2: Golongan / Sub Kriteria --}}
                                <tr class="row-level2 child-row row-clickable"
                                    data-parent="kat-{{ $katId }}"
                                    data-kategori-id="{{ $katId }}"
                                    data-sub-id="{{ $sub['id'] }}"
                                    data-item-id=""
                                    data-label="{{ $sub['nama'] }}"
                                    data-breadcrumb="{{ $kat['nama'] }}">
                                    <td>
                                        {{ $sub['nama'] }}
                                        <span class="detail-hint">→ Lihat Detail</span>
                                    </td>
                                    @foreach($bulanAktif as $bNum => $bName)
                                        <td class="nilai-cell">
                                            {{ number_format($sub['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                                        </td>
                                    @endforeach
                                    <td class="nilai-cell" style="font-weight:700;">
                                        {{ number_format($sub['total'], 0, ',', '.') }}
                                    </td>
                                </tr>

                                @foreach($sub['items'] as $itemKey => $item)
                                    @if($item['id'])
                                    {{-- LEVEL 3: Item Sub Kriteria --}}
                                    <tr class="row-level3 child-row row-clickable"
                                        data-parent="kat-{{ $katId }}"
                                        data-kategori-id="{{ $katId }}"
                                        data-sub-id="{{ $sub['id'] }}"
                                        data-item-id="{{ $item['id'] }}"
                                        data-label="{{ $item['nama'] }}"
                                        data-breadcrumb="{{ $kat['nama'] }} › {{ $sub['nama'] }}">
                                        <td>
                                            {{ $item['nama'] }}
                                            <span class="detail-hint">→ Lihat Detail</span>
                                        </td>
                                        @foreach($bulanAktif as $bNum => $bName)
                                            <td class="nilai-cell">
                                                {{ number_format($item['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                                            </td>
                                        @endforeach
                                        <td class="nilai-cell">
                                            {{ number_format($item['total'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach

                                {{-- Sub Total Row --}}
                                <tr class="row-subtotal child-row" data-parent="kat-{{ $katId }}">
                                    <td style="padding-left:32px; font-style:italic;">
                                        Total {{ $sub['nama'] }}
                                    </td>
                                    @foreach($bulanAktif as $bNum => $bName)
                                        <td class="nilai-cell" style="color:#064E3B;">
                                            {{ number_format($sub['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                                        </td>
                                    @endforeach
                                    <td class="nilai-cell" style="color:#064E3B; font-weight:800;">
                                        {{ number_format($sub['total'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Kategori Total Row --}}
                            <tr class="row-subtotal" style="background: linear-gradient(135deg, #0D9488, #14B8A6);">
                                <td style="color:white; font-weight:800;">
                                    Total {{ $kat['nama'] }}
                                </td>
                                @foreach($bulanAktif as $bNum => $bName)
                                    <td class="nilai-cell" style="color:#CCFBF1;">
                                        {{ number_format($kat['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                                    </td>
                                @endforeach
                                <td class="nilai-cell" style="color:white; font-size:14px;">
                                    {{ number_format($kat['total'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- GRAND TOTAL --}}
                        <tr class="row-grandtotal">
                            <td>GRAND TOTAL PEMBAYARAN</td>
                            @foreach($bulanAktif as $bNum => $bName)
                                <td class="nilai-cell">
                                    {{ number_format($grandTotal[$bNum] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                            <td class="nilai-cell" style="font-size:16px;">
                                {{ number_format($grandTotalAll, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                @else
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>Tidak Ada Data</h4>
                    <p>Tidak ada transaksi pembayaran untuk periode yang dipilih.</p>
                </div>
                @endif
            </div>
        </div>
    </section>
</div>

<!-- ============================================
     DRAWER DETAIL TRANSAKSI
     ============================================ -->
<div class="drawer-overlay" id="drawer-overlay"></div>
<div class="drawer-panel" id="drawer-detail">
    <div class="drawer-header">
        <div>
            <h3 id="drawer-title">Detail Transaksi</h3>
            <div class="drawer-subtitle" id="drawer-subtitle"></div>
        </div>
        <button class="drawer-close-btn" id="drawer-close">✕</button>
    </div>
    <div class="drawer-stats">
        <div class="drawer-stat">
            <span class="stat-label">Total Transaksi</span>
            <span class="stat-value" id="drawer-count">-</span>
        </div>
        <div class="drawer-stat">
            <span class="stat-label">Total Nilai</span>
            <span class="stat-value" id="drawer-total">-</span>
        </div>
    </div>
    <div class="drawer-body">
        <table class="drawer-table">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th style="width:100px;">Tanggal</th>
                    <th>Uraian</th>
                    <th>Dibayarkan Kepada</th>
                    <th style="text-align:right; width:140px;">Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody id="drawer-tbody">
                <tr>
                    <td colspan="5" style="text-align:center; padding:60px 20px; color:#94A3B8;">
                        Klik baris golongan atau sub golongan untuk melihat detail transaksi
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({ theme: 'bootstrap4' });

    // =============================================
    // COLLAPSE / EXPAND per Kriteria
    // =============================================
    $(document).on('click', '.row-level1', function() {
        const group = $(this).data('toggle-group');
        const $children = $('[data-parent="' + group + '"]');
        const $icon = $(this).find('.collapse-icon i');

        if ($(this).hasClass('collapsed')) {
            // Expand
            $(this).removeClass('collapsed');
            $children.removeClass('hidden-row');
            $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        } else {
            // Collapse
            $(this).addClass('collapsed');
            $children.addClass('hidden-row');
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        }
    });

    // =============================================
    // DRAWER: Open on row click
    // =============================================
    $(document).on('click', '.row-clickable', function(e) {
        e.stopPropagation(); // prevent triggering Level1 collapse

        const kategoriId = $(this).data('kategori-id');
        const subId      = $(this).data('sub-id');
        const itemId     = $(this).data('item-id');
        const label      = $(this).data('label');
        const breadcrumb = $(this).data('breadcrumb');

        // Highlight active row
        $('.row-clickable').removeClass('active-row');
        $(this).addClass('active-row');

        // Update drawer header
        $('#drawer-title').text(label);
        $('#drawer-subtitle').text(breadcrumb);

        // Open drawer
        $('#drawer-detail').addClass('open');
        $('#drawer-overlay').addClass('active');

        // Loading state
        $('#drawer-tbody').html(
            '<tr><td colspan="5" style="text-align:center; padding:50px;">' +
            '<div style="color:#0D9488;"><i class="fas fa-spinner fa-spin fa-2x"></i>' +
            '<p style="margin-top:12px; color:#94A3B8; font-size:13px;">Memuat data transaksi...</p></div></td></tr>'
        );
        $('#drawer-count').text('...');
        $('#drawer-total').text('...');

        // AJAX fetch
        const params = {
            kategori_id:          kategoriId,
            sub_kriteria_id:      subId || '',
            item_sub_kriteria_id: itemId || '',
            tahun:                $('#filter-tahun').val(),
            dari_bulan:           $('#filter-dari-bulan').val(),
            sampai_bulan:         $('#filter-sampai-bulan').val(),
        };

        $.ajax({
            url: '{{ route("ringkasan.detail") }}',
            type: 'GET',
            data: params,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                // Stats
                const total = data.reduce((sum, d) => sum + parseFloat(d.nilai || 0), 0);
                $('#drawer-count').text(data.length.toLocaleString('id-ID') + ' transaksi');
                $('#drawer-total').text('Rp ' + total.toLocaleString('id-ID', {minimumFractionDigits: 0}));

                if (data.length === 0) {
                    $('#drawer-tbody').html(
                        '<tr><td colspan="5" style="text-align:center; padding:50px;">' +
                        '<div style="color:#94A3B8;"><i class="fas fa-inbox" style="font-size:36px; opacity:0.4;"></i>' +
                        '<p style="margin-top:12px; font-size:13px;">Tidak ada transaksi untuk kriteria ini</p></div></td></tr>'
                    );
                    return;
                }

                // Render rows
                let html = '';
                data.forEach((d, idx) => {
                    const tgl = d.tanggal ? new Date(d.tanggal).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : '-';
                    const nilai = parseFloat(d.nilai || 0).toLocaleString('id-ID', {minimumFractionDigits: 0});
                    html += `<tr>
                        <td style="color:#94A3B8; font-size:11px;">${idx + 1}</td>
                        <td style="white-space:nowrap; font-size:12px;">${tgl}</td>
                        <td style="font-size:12px; max-width:250px;">${d.uraian || '-'}</td>
                        <td style="font-size:12px;">${d.dibayarkan_kepada || '-'}</td>
                        <td class="nilai-cell">${nilai}</td>
                    </tr>`;
                });
                $('#drawer-tbody').html(html);
            },
            error: function() {
                $('#drawer-tbody').html(
                    '<tr><td colspan="5" style="text-align:center; padding:50px;">' +
                    '<div style="color:#EF4444;"><i class="fas fa-exclamation-triangle" style="font-size:32px;"></i>' +
                    '<p style="margin-top:12px; font-size:13px;">Gagal memuat data. Silakan coba lagi.</p></div></td></tr>'
                );
                $('#drawer-count').text('-');
                $('#drawer-total').text('-');
            }
        });
    });

    // =============================================
    // DRAWER: Close
    // =============================================
    function closeDrawer() {
        $('#drawer-detail').removeClass('open');
        $('#drawer-overlay').removeClass('active');
        $('.row-clickable').removeClass('active-row');
    }

    $('#drawer-close').on('click', closeDrawer);
    $('#drawer-overlay').on('click', closeDrawer);
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') closeDrawer();
    });

    // =============================================
    // RESET FILTER
    // =============================================
    $('#btn-reset-ringkasan').on('click', function() {
        window.location.href = '{{ route("ringkasan.index") }}';
    });
});
</script>
@endpush
