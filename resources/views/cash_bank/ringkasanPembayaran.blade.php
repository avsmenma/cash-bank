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

    /* Level 1 — Kategori */
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

    /* Level 2 — Sub Kategori */
    .rk-level2 {
        background: #eef5fb;
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

    /* Level 3 — Item Sub Kategori */
    .rk-level3 {
        background: #fff;
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
    .rk-nilai-link {
        display: inline-block;
        color: inherit;
        text-decoration: none;
        border-bottom: 1px dotted rgba(13, 59, 110, 0.35);
        cursor: pointer;
    }
    .rk-nilai-link:hover {
        color: #007bff !important;
        border-bottom-color: #007bff;
        text-decoration: none;
    }

    /* Child row toggle */
    .rk-child.rk-hidden { display: none; }

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
      <div class="cb-fullscreen-hide" style="padding: 12px 16px;">
        <span class="period-badge-rk">
          <i class="fas fa-calendar-alt mr-1"></i>
          Periode: {{ $bulanMap[$dariBulan] ?? '' }} — {{ $bulanMap[$sampaiBulan] ?? '' }} {{ $tahun }}
        </span>
      </div>

      <div class="tbl-scroll-rk">
        @if(count($hierarki) > 0)
        @php
          $detailUrl = function (array $params = [], $bulan = null) use ($tahun, $dariBulan, $sampaiBulan) {
              $period = [
                  'tahun' => $tahun,
                  'dari_bulan' => $bulan ?? $dariBulan,
                  'sampai_bulan' => $bulan ?? $sampaiBulan,
              ];

              return route('ringkasan.detail', array_merge($period, $params));
          };
        @endphp
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
                    <a class="rk-nilai-link"
                       title="Detail"
                       href="{{ $detailUrl(['kategori_id' => $katId], $bNum) }}">
                      {{ number_format($kat['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                    </a>
                  </td>
                @endforeach
                <td class="rk-nilai" style="color:#0d3b6e; font-weight:800;">
                  <a class="rk-nilai-link"
                     title="Detail"
                     href="{{ $detailUrl(['kategori_id' => $katId]) }}">
                    {{ number_format($kat['total'], 0, ',', '.') }}
                  </a>
                </td>
              </tr>

              @php $subIndex = 0; @endphp
              @foreach($kat['subs'] as $subKey => $sub)
                @php
                  $subIndex++;
                  $subLetter = chr(64 + $subIndex);
                @endphp

                {{-- LEVEL 2: Sub Kategori --}}
                <tr class="rk-level2 rk-child"
                    data-parent="kat-{{ $katId }}">
                  <td>
                    {{ $subLetter }}.&nbsp;&nbsp;{{ $sub['nama'] }}
                  </td>
                  @foreach($bulanAktif as $bNum => $bName)
                    <td class="rk-nilai" style="color:#1a5276;">
                      <a class="rk-nilai-link"
                         title="Detail"
                         href="{{ $detailUrl(['kategori_id' => $katId, 'sub_kriteria_id' => $sub['id']], $bNum) }}">
                        {{ number_format($sub['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                      </a>
                    </td>
                  @endforeach
                  <td class="rk-nilai" style="color:#1a5276; font-weight:700;">
                    <a class="rk-nilai-link"
                       title="Detail"
                       href="{{ $detailUrl(['kategori_id' => $katId, 'sub_kriteria_id' => $sub['id']]) }}">
                      {{ number_format($sub['total'], 0, ',', '.') }}
                    </a>
                  </td>
                </tr>

                @foreach($sub['items'] as $itemKey => $item)
                  {{-- LEVEL 3: Item Sub Kategori --}}
                  <tr class="rk-level3 rk-child"
                      data-parent="kat-{{ $katId }}">
                    <td>
                      -&nbsp;{{ $item['nama'] }}
                    </td>
                    @foreach($bulanAktif as $bNum => $bName)
                      <td class="rk-nilai">
                        <a class="rk-nilai-link"
                           title="Detail"
                           href="{{ $detailUrl(['kategori_id' => $katId, 'sub_kriteria_id' => $sub['id'], 'item_sub_kriteria_ids' => $item['ids']], $bNum) }}">
                          {{ number_format($item['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                        </a>
                      </td>
                    @endforeach
                    <td class="rk-nilai">
                      <a class="rk-nilai-link"
                         title="Detail"
                         href="{{ $detailUrl(['kategori_id' => $katId, 'sub_kriteria_id' => $sub['id'], 'item_sub_kriteria_ids' => $item['ids']]) }}">
                        {{ number_format($item['total'], 0, ',', '.') }}
                      </a>
                    </td>
                  </tr>
                @endforeach

                {{-- Sub Total --}}
                <tr class="rk-subtotal rk-child" data-parent="kat-{{ $katId }}">
                  <td>Sub Total {{ $subLetter }}.{{ $subIndex }}</td>
                  @foreach($bulanAktif as $bNum => $bName)
                    <td class="rk-nilai" style="color:#0d3b6e;">
                      <a class="rk-nilai-link"
                         title="Detail"
                         href="{{ $detailUrl(['kategori_id' => $katId, 'sub_kriteria_id' => $sub['id']], $bNum) }}">
                        {{ number_format($sub['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                      </a>
                    </td>
                  @endforeach
                  <td class="rk-nilai" style="color:#0d3b6e; font-weight:800;">
                    <a class="rk-nilai-link"
                       title="Detail"
                       href="{{ $detailUrl(['kategori_id' => $katId, 'sub_kriteria_id' => $sub['id']]) }}">
                      {{ number_format($sub['total'], 0, ',', '.') }}
                    </a>
                  </td>
                </tr>
              @endforeach

              {{-- Kategori Total --}}
              <tr class="rk-kategori-total">
                <td>Total {{ $kat['nama'] }}</td>
                @foreach($bulanAktif as $bNum => $bName)
                  <td class="rk-nilai" style="color:#aed6f1;">
                    <a class="rk-nilai-link"
                       title="Detail"
                       href="{{ $detailUrl(['kategori_id' => $katId], $bNum) }}">
                      {{ number_format($kat['bulan'][$bNum] ?? 0, 0, ',', '.') }}
                    </a>
                  </td>
                @endforeach
                <td class="rk-nilai" style="color:#fff; font-size:13px;">
                  <a class="rk-nilai-link"
                     title="Detail"
                     href="{{ $detailUrl(['kategori_id' => $katId]) }}">
                    {{ number_format($kat['total'], 0, ',', '.') }}
                  </a>
                </td>
              </tr>
            @endforeach

            {{-- GRAND TOTAL --}}
            <tr class="rk-grandtotal">
              <td>GRAND TOTAL PEMBAYARAN</td>
              @foreach($bulanAktif as $bNum => $bName)
                <td class="rk-nilai" style="color:#85c1e9;">
                  <a class="rk-nilai-link"
                     title="Detail"
                     href="{{ $detailUrl([], $bNum) }}">
                    {{ number_format($grandTotal[$bNum] ?? 0, 0, ',', '.') }}
                  </a>
                </td>
              @endforeach
              <td class="rk-nilai" style="color:#f9e79f; font-size:14px;">
                <a class="rk-nilai-link"
                   title="Detail"
                   href="{{ $detailUrl() }}">
                  {{ number_format($grandTotalAll, 0, ',', '.') }}
                </a>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // COLLAPSE / EXPAND per Kriteria
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

    $(document).on('click', '.rk-nilai-link', function(e) {
        e.stopPropagation();
    });

    // RESET FILTER
    $('#btn-reset-ringkasan').on('click', function() {
        window.location.href = '{{ route("ringkasan.index") }}';
    });
});
</script>
@endpush
