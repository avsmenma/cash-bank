@extends('layouts/index')

@push('styles')
<style>
    /* PAGE TITLE CARD — matches bankKeluar */
    .page-title-card {
        background: #fff;
        border-top: 4px solid #0d3b6e;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(13,59,110,.10);
        padding: 18px 24px 14px;
        margin-bottom: 18px;
    }
    .page-title-card h1 {
        font-size: 1.55rem;
        font-weight: 700;
        color: #0d3b6e;
        margin: 0;
    }

    /* FILTER CARD */
    .filter-card-ringkasan {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(13,59,110,.08);
        margin-bottom: 18px;
        overflow: hidden;
    }
    .filter-card-ringkasan .card-header {
        background: #0d3b6e;
        color: #fff;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
    }
    .filter-card-ringkasan .card-body {
        padding: 16px 18px;
    }

    /* PERIOD BADGE */
    .period-badge-rk {
        background: #0d3b6e;
        color: #fff;
        padding: 6px 16px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 12px;
    }

    /* TABLE CARD */
    .table-card-rk {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(13,59,110,.08);
        overflow: hidden;
    }

    /* HIERARCHY TABLE */
    .tbl-ringkasan {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .tbl-ringkasan thead th {
        background: #0d3b6e !important;
        color: #fff !important;
        font-size: 11px;
        font-weight: 600;
        padding: 9px 10px;
        border-color: #1a5276 !important;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .tbl-ringkasan thead th:first-child {
        text-align: left;
    }

    /* Level 1 — Kategori (numbered) */
    .rk-level1 {
        background: #d6e9f8;
        font-weight: 700;
        cursor: pointer;
    }
    .rk-level1:hover { background: #c5dff1 !important; }
    .rk-level1 td {
        padding: 10px 10px;
        border-bottom: 2px solid #b8d4ea;
        color: #0d3b6e;
        font-size: 12.5px;
    }
    .rk-level1 .collapse-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 3px;
        background: #0d3b6e;
        color: white;
        font-size: 9px;
        margin-right: 8px;
        transition: transform 0.25s ease;
        flex-shrink: 0;
    }
    .rk-level1.collapsed .collapse-icon {
        transform: rotate(-90deg);
    }

    /* Level 2 — Sub Kategori (lettered) */
    .rk-level2 {
        background: #eef5fb;
        cursor: pointer;
    }
    .rk-level2:hover { background: #ddeaf5 !important; }
    .rk-level2 td {
        padding: 8px 10px;
        padding-left: 36px;
        border-bottom: 1px solid #dce8f3;
        color: #1a5276;
        font-weight: 600;
        font-size: 12px;
    }

    /* Level 3 — Item Sub Kategori (dashed) */
    .rk-level3 {
        background: #fff;
        cursor: pointer;
    }
    .rk-level3:hover { background: #f0f6fc !important; }
    .rk-level3 td {
        padding: 6px 10px;
        padding-left: 52px;
        border-bottom: 1px solid #f0f0f0;
        color: #444;
        font-size: 11.5px;
    }

    /* Sub Total row */
    .rk-subtotal {
        background: #e8f0f8;
        font-weight: 700;
    }
    .rk-subtotal td {
        padding: 8px 10px;
        padding-left: 36px;
        border-bottom: 2px solid #c5dff1;
        color: #0d3b6e;
        font-size: 12px;
        font-style: italic;
    }

    /* Kategori Total row */
    .rk-kategori-total {
        background: #0d3b6e;
    }
    .rk-kategori-total td {
        padding: 10px 10px;
        color: #fff !important;
        font-weight: 700;
        font-size: 12px;
        border-bottom: 3px solid #092d54;
    }

    /* Grand Total row */
    .rk-grandtotal {
        background: #092d54;
    }
    .rk-grandtotal td {
        padding: 12px 10px;
        color: #fff !important;
        font-weight: 800;
        font-size: 13px;
        border: none;
    }

    /* Nilai cells */
    .rk-nilai {
        text-align: right;
        font-family: 'Consolas', 'Courier New', monospace;
        white-space: nowrap;
        letter-spacing: -0.3px;
    }

    /* Child row animation */
    .rk-child.rk-hidden { display: none; }

    /* Detail hint on hover */
    .rk-detail-hint {
        font-size: 10px;
        color: #3498db;
        opacity: 0;
        margin-left: 6px;
        transition: opacity 0.15s;
        font-weight: 600;
    }
    .rk-level2:hover .rk-detail-hint,
    .rk-level3:hover .rk-detail-hint { opacity: 1; }

    /* Table scroll */
    .tbl-scroll-rk {
        overflow-x: auto;
    }

    /* Empty state */
    .rk-empty {
        text-align: center;
        padding: 50px 20px;
        color: #999;
    }
    .rk-empty i { font-size: 40px; margin-bottom: 12px; opacity: 0.4; }
    .rk-empty h5 { color: #666; font-weight: 600; margin-bottom: 6px; }

    /* ============ DRAWER ============ */
    .rk-drawer-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.35);
        z-index: 1050;
        opacity: 0; pointer-events: none;
        transition: opacity 0.25s;
    }
    .rk-drawer-overlay.active { opacity: 1; pointer-events: all; }

    .rk-drawer {
        position: fixed; top: 0; right: -650px;
        width: 650px; height: 100vh;
        background: #fff; z-index: 1051;
        box-shadow: -4px 0 20px rgba(0,0,0,0.12);
        transition: right 0.3s ease;
        display: flex; flex-direction: column;
    }
    .rk-drawer.open { right: 0; }

    .rk-drawer-head {
        background: #0d3b6e;
        padding: 18px 22px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        color: #fff;
        flex-shrink: 0;
    }
    .rk-drawer-head h5 { margin: 0; font-size: 15px; font-weight: 700; }
    .rk-drawer-head .rk-sub { font-size: 11px; color: rgba(255,255,255,0.7); margin-top: 3px; }
    .rk-drawer-close {
        background: rgba(255,255,255,0.15); border: none; color: #fff;
        width: 30px; height: 30px; border-radius: 50%;
        cursor: pointer; font-size: 16px;
        display: flex; align-items: center; justify-content: center;
    }
    .rk-drawer-close:hover { background: rgba(255,255,255,0.3); }

    .rk-drawer-stats {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 1px; background: #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
        flex-shrink: 0;
    }
    .rk-drawer-stat {
        background: #f8f9fa; padding: 12px 18px;
    }
    .rk-drawer-stat .label { font-size: 10px; color: #888; text-transform: uppercase; font-weight: 600; }
    .rk-drawer-stat .value { font-size: 17px; font-weight: 800; color: #0d3b6e; font-family: 'Consolas', monospace; }

    .rk-drawer-body { flex: 1; overflow-y: auto; padding: 0; }

    .rk-drawer-tbl { width: 100%; border-collapse: collapse; font-size: 12px; }
    .rk-drawer-tbl thead th {
        background: #f1f3f5; padding: 8px 12px; text-align: left;
        font-size: 10px; text-transform: uppercase; color: #666;
        font-weight: 700; position: sticky; top: 0; z-index: 5;
        border-bottom: 2px solid #ddd;
    }
    .rk-drawer-tbl tbody td {
        padding: 8px 12px; border-bottom: 1px solid #f0f0f0;
        vertical-align: top; color: #444;
    }
    .rk-drawer-tbl tbody tr:hover td { background: #f8f9fa; }
</style>
@endpush

@section('content')
<section class="content-header" style="display:none;"></section>

<section class="content">
  <div class="container-fluid">

    {{-- PAGE TITLE CARD --}}
    <div class="page-title-card mt-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1><i class="fas fa-clipboard-list mr-2" style="color:#0d3b6e;"></i>Ringkasan Pembayaran</h1>
        <ol class="breadcrumb mb-0" style="background:none;padding:0;">
          <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Ringkasan Pembayaran</li>
        </ol>
      </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="filter-card-ringkasan">
      <div class="card-header">
        <i class="fas fa-sliders-h mr-1"></i> Filter Periode
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('ringkasan.index') }}" id="form-filter-ringkasan">
          <div class="row align-items-end">
            <div class="col-md-2">
              <label style="font-weight:600; font-size:11px; color:#555;">TAHUN</label>
              <select class="form-control form-control-sm" name="tahun" id="filter-tahun">
                @for($y = date('Y') - 2; $y <= date('Y') + 5; $y++)
                  <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
              </select>
            </div>
            <div class="col-md-2">
              <label style="font-weight:600; font-size:11px; color:#555;">DARI BULAN</label>
              <select class="form-control form-control-sm" name="dari_bulan" id="filter-dari-bulan">
                @foreach($bulanMap as $noBulan => $namaBulan)
                  <option value="{{ $noBulan }}" {{ $noBulan == $dariBulan ? 'selected' : '' }}>{{ $namaBulan }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label style="font-weight:600; font-size:11px; color:#555;">SAMPAI BULAN</label>
              <select class="form-control form-control-sm" name="sampai_bulan" id="filter-sampai-bulan">
                @foreach($bulanMap as $noBulan => $namaBulan)
                  <option value="{{ $noBulan }}" {{ $noBulan == $sampaiBulan ? 'selected' : '' }}>{{ $namaBulan }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 d-flex" style="gap:8px;">
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter mr-1"></i> Filter
              </button>
              <button type="button" class="btn btn-default btn-sm" id="btn-reset-ringkasan">
                <i class="fas fa-undo mr-1"></i> Reset
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="table-card-rk">
      <div style="padding: 12px 16px;">
        <span class="period-badge-rk">
          <i class="fas fa-calendar-alt mr-1"></i>
          Periode: {{ $bulanMap[$dariBulan] ?? '' }} — {{ $bulanMap[$sampaiBulan] ?? '' }} {{ $tahun }}
        </span>
      </div>

      <div class="tbl-scroll-rk">
        @if(count($hierarki) > 0)
        <table class="tbl-ringkasan" id="tbl-ringkasan">
          <thead>
            <tr>
              <th style="min-width:380px; text-align:left;">URAIAN</th>
              @foreach($bulanAktif as $bNum => $bName)
                <th style="min-width:130px;">{{ strtoupper($bName) }} {{ $tahun }}</th>
              @endforeach
              <th style="min-width:150px;">SD {{ strtoupper($bulanMap[$sampaiBulan] ?? '') }} {{ $tahun }}</th>
            </tr>
          </thead>
          <tbody>
            @php $katIndex = 0; @endphp
            @foreach($hierarki as $katId => $kat)
              @php $katIndex++; @endphp

              {{-- LEVEL 1: Kategori --}}
              <tr class="rk-level1" data-toggle-group="kat-{{ $katId }}">
                <td>
                  <span class="collapse-icon"><i class="fas fa-chevron-down" style="font-size:8px;"></i></span>
                  {{ $katIndex }}.&nbsp;&nbsp;{{ $kat['nama'] }}
                </td>
                @foreach($bulanAktif as $bNum => $bName)
                  <td class="rk-nilai" style="color:#0d3b6e;">
                    {{ number_format($kat['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                  </td>
                @endforeach
                <td class="rk-nilai" style="color:#0d3b6e; font-weight:800;">
                  {{ number_format($kat['total'], 0, ',', '.') }}
                </td>
              </tr>

              @php $subIndex = 0; @endphp
              @foreach($kat['subs'] as $subKey => $sub)
                @php
                  $subIndex++;
                  $subLetter = chr(64 + $subIndex); // A, B, C...
                @endphp

                {{-- LEVEL 2: Sub Kategori --}}
                <tr class="rk-level2 rk-child rk-clickable"
                    data-parent="kat-{{ $katId }}"
                    data-kategori-id="{{ $katId }}"
                    data-sub-id="{{ $sub['id'] }}"
                    data-item-id=""
                    data-label="{{ $sub['nama'] }}"
                    data-breadcrumb="{{ $kat['nama'] }}">
                  <td>
                    {{ $subLetter }}.&nbsp;&nbsp;{{ $sub['nama'] }}
                    <span class="rk-detail-hint">→ detail</span>
                  </td>
                  @foreach($bulanAktif as $bNum => $bName)
                    <td class="rk-nilai" style="color:#1a5276;">
                      {{ number_format($sub['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                    </td>
                  @endforeach
                  <td class="rk-nilai" style="color:#1a5276; font-weight:700;">
                    {{ number_format($sub['total'], 0, ',', '.') }}
                  </td>
                </tr>

                @foreach($sub['items'] as $itemKey => $item)
                  {{-- LEVEL 3: Item Sub Kategori --}}
                  <tr class="rk-level3 rk-child rk-clickable"
                      data-parent="kat-{{ $katId }}"
                      data-kategori-id="{{ $katId }}"
                      data-sub-id="{{ $sub['id'] }}"
                      data-item-id="{{ $item['id'] }}"
                      data-label="{{ $item['nama'] }}"
                      data-breadcrumb="{{ $kat['nama'] }} › {{ $sub['nama'] }}">
                    <td>
                      -{{ $item['nama'] }}
                    </td>
                    @foreach($bulanAktif as $bNum => $bName)
                      <td class="rk-nilai">
                        {{ number_format($item['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                      </td>
                    @endforeach
                    <td class="rk-nilai">
                      {{ number_format($item['total'], 0, ',', '.') }}
                    </td>
                  </tr>
                @endforeach

                {{-- Sub Total --}}
                <tr class="rk-subtotal rk-child" data-parent="kat-{{ $katId }}">
                  <td>Sub Total {{ $subLetter }}.{{ $subIndex }}</td>
                  @foreach($bulanAktif as $bNum => $bName)
                    <td class="rk-nilai" style="color:#0d3b6e;">
                      {{ number_format($sub['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                    </td>
                  @endforeach
                  <td class="rk-nilai" style="color:#0d3b6e; font-weight:800;">
                    {{ number_format($sub['total'], 0, ',', '.') }}
                  </td>
                </tr>
              @endforeach

              {{-- Kategori Total --}}
              <tr class="rk-kategori-total">
                <td>Total {{ $kat['nama'] }}</td>
                @foreach($bulanAktif as $bNum => $bName)
                  <td class="rk-nilai" style="color:#aed6f1;">
                    {{ number_format($kat['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                  </td>
                @endforeach
                <td class="rk-nilai" style="color:#fff; font-size:13px;">
                  {{ number_format($kat['total'], 0, ',', '.') }}
                </td>
              </tr>
            @endforeach

            {{-- GRAND TOTAL --}}
            <tr class="rk-grandtotal">
              <td>GRAND TOTAL PEMBAYARAN</td>
              @foreach($bulanAktif as $bNum => $bName)
                <td class="rk-nilai" style="color:#85c1e9;">
                  {{ number_format($grandTotal[$bNum] ?? 0, 0, ',', '.') }}
                </td>
              @endforeach
              <td class="rk-nilai" style="color:#f9e79f; font-size:14px;">
                {{ number_format($grandTotalAll, 0, ',', '.') }}
              </td>
            </tr>
          </tbody>
        </table>
        @else
        <div class="rk-empty">
          <i class="fas fa-inbox"></i>
          <h5>Tidak Ada Data</h5>
          <p style="font-size:12px;">Tidak ada transaksi pembayaran untuk periode yang dipilih.</p>
        </div>
        @endif
      </div>
    </div>

  </div>
</section>

{{-- ============ DRAWER DETAIL ============ --}}
<div class="rk-drawer-overlay" id="rk-overlay"></div>
<div class="rk-drawer" id="rk-drawer">
  <div class="rk-drawer-head">
    <div>
      <h5 id="rk-drawer-title">Detail Transaksi</h5>
      <div class="rk-sub" id="rk-drawer-sub"></div>
    </div>
    <button class="rk-drawer-close" id="rk-drawer-close">✕</button>
  </div>
  <div class="rk-drawer-stats">
    <div class="rk-drawer-stat">
      <span class="label">Total Transaksi</span>
      <span class="value" id="rk-drawer-count">-</span>
    </div>
    <div class="rk-drawer-stat">
      <span class="label">Total Nilai</span>
      <span class="value" id="rk-drawer-total">-</span>
    </div>
  </div>
  <div class="rk-drawer-body">
    <table class="rk-drawer-tbl">
      <thead>
        <tr>
          <th style="width:40px;">No</th>
          <th style="width:90px;">Tanggal</th>
          <th>Uraian</th>
          <th>Dibayarkan Kepada</th>
          <th style="text-align:right; width:130px;">Nilai (Rp)</th>
        </tr>
      </thead>
      <tbody id="rk-drawer-tbody">
        <tr>
          <td colspan="5" style="text-align:center; padding:50px; color:#999;">
            Klik baris sub kategori atau item untuk melihat detail transaksi
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
    // =============================================
    // COLLAPSE / EXPAND per Kriteria
    // =============================================
    $(document).on('click', '.rk-level1', function() {
        const group = $(this).data('toggle-group');
        const $children = $('[data-parent="' + group + '"]');
        const $icon = $(this).find('.collapse-icon i');

        if ($(this).hasClass('collapsed')) {
            $(this).removeClass('collapsed');
            $children.removeClass('rk-hidden');
            $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        } else {
            $(this).addClass('collapsed');
            $children.addClass('rk-hidden');
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        }
    });

    // =============================================
    // DRAWER: Open on row click
    // =============================================
    $(document).on('click', '.rk-clickable', function(e) {
        e.stopPropagation();

        const kategoriId = $(this).data('kategori-id');
        const subId      = $(this).data('sub-id');
        const itemId     = $(this).data('item-id');
        const label      = $(this).data('label');
        const breadcrumb = $(this).data('breadcrumb');

        $('.rk-clickable').css('outline', 'none');
        $(this).css('outline', '2px solid #0d3b6e');

        $('#rk-drawer-title').text(label);
        $('#rk-drawer-sub').text(breadcrumb);
        $('#rk-drawer').addClass('open');
        $('#rk-overlay').addClass('active');

        // Loading
        $('#rk-drawer-tbody').html(
            '<tr><td colspan="5" style="text-align:center; padding:50px;">' +
            '<i class="fas fa-spinner fa-spin fa-2x" style="color:#0d3b6e;"></i>' +
            '<p style="margin-top:10px; color:#999; font-size:12px;">Memuat data...</p></td></tr>'
        );
        $('#rk-drawer-count').text('...');
        $('#rk-drawer-total').text('...');

        $.ajax({
            url: '{{ route("ringkasan.detail") }}',
            type: 'GET',
            data: {
                kategori_id:          kategoriId,
                sub_kriteria_id:      subId || '',
                item_sub_kriteria_id: itemId || '',
                tahun:                $('#filter-tahun').val(),
                dari_bulan:           $('#filter-dari-bulan').val(),
                sampai_bulan:         $('#filter-sampai-bulan').val(),
            },
            success: function(data) {
                const total = data.reduce((s, d) => s + parseFloat(d.nilai || 0), 0);
                $('#rk-drawer-count').text(data.length.toLocaleString('id-ID') + ' transaksi');
                $('#rk-drawer-total').text('Rp ' + total.toLocaleString('id-ID', {minimumFractionDigits: 0}));

                if (data.length === 0) {
                    $('#rk-drawer-tbody').html(
                        '<tr><td colspan="5" style="text-align:center; padding:50px; color:#999;">' +
                        '<i class="fas fa-inbox" style="font-size:32px; opacity:0.3;"></i>' +
                        '<p style="margin-top:10px; font-size:12px;">Tidak ada transaksi</p></td></tr>'
                    );
                    return;
                }

                let html = '';
                data.forEach((d, idx) => {
                    const tgl = d.tanggal ? new Date(d.tanggal).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : '-';
                    const nilai = parseFloat(d.nilai || 0).toLocaleString('id-ID', {minimumFractionDigits: 0});
                    html += `<tr>
                        <td style="color:#999; font-size:11px;">${idx + 1}</td>
                        <td style="white-space:nowrap;">${tgl}</td>
                        <td style="max-width:220px;">${d.uraian || '-'}</td>
                        <td>${d.dibayarkan_kepada || '-'}</td>
                        <td class="rk-nilai" style="color:#0d3b6e; font-weight:700;">${nilai}</td>
                    </tr>`;
                });
                $('#rk-drawer-tbody').html(html);
            },
            error: function() {
                $('#rk-drawer-tbody').html(
                    '<tr><td colspan="5" style="text-align:center; padding:50px; color:#c0392b;">' +
                    '<i class="fas fa-exclamation-triangle" style="font-size:28px;"></i>' +
                    '<p style="margin-top:10px; font-size:12px;">Gagal memuat data</p></td></tr>'
                );
                $('#rk-drawer-count').text('-');
                $('#rk-drawer-total').text('-');
            }
        });
    });

    // =============================================
    // DRAWER: Close
    // =============================================
    function closeDrawer() {
        $('#rk-drawer').removeClass('open');
        $('#rk-overlay').removeClass('active');
        $('.rk-clickable').css('outline', 'none');
    }

    $('#rk-drawer-close').on('click', closeDrawer);
    $('#rk-overlay').on('click', closeDrawer);
    $(document).on('keydown', function(e) { if (e.key === 'Escape') closeDrawer(); });

    // RESET FILTER
    $('#btn-reset-ringkasan').on('click', function() {
        window.location.href = '{{ route("ringkasan.index") }}';
    });
});
</script>
@endpush
