@extends('layouts/index')

@push('styles')
<style>
    .page-title-card {
        background: #fff;
        border-top: 4px solid #0d3b6e;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(13,59,110,.10);
        padding: 18px 24px 14px;
        margin-bottom: 18px;
    }
    .page-title-card h1 {
        font-size: 1.4rem;
        font-weight: 700;
        color: #0d3b6e;
        margin: 0;
    }
    .page-title-card .subtitle {
        font-size: 12px;
        color: #888;
        margin-top: 4px;
    }
    .info-bar {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 8px rgba(13,59,110,.07);
        padding: 12px 18px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .info-bar .badge-info-rk {
        background: #0d3b6e;
        color: #fff;
        padding: 5px 14px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    .info-bar .stat {
        font-size: 13px;
        color: #444;
        font-weight: 600;
    }
    .info-bar .stat .val {
        color: #0d3b6e;
        font-family: 'Consolas', monospace;
        font-weight: 800;
    }
    .table-card-detail {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(13,59,110,.08);
        overflow: hidden;
    }
    #tbl-detail thead th {
        background: #0d3b6e !important;
        color: #fff !important;
        font-size: 10.5px;
        font-weight: 600;
        padding: 8px 8px;
        border-color: #1a5276 !important;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
    }
    #tbl-detail td {
        font-size: 11.5px;
        vertical-align: middle;
        padding: 6px 8px;
    }
    #tbl-detail tbody tr { background: #fff; }
    #tbl-detail tbody tr:hover { background: #f0f5fb; }
    .td-nilai {
        text-align: right;
        font-family: 'Consolas', monospace;
        font-weight: 600;
        color: #0d3b6e;
        white-space: nowrap;
    }
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
        font-size: 12px; font-weight: 500; color: #444;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ced4da; border-radius: 4px;
        padding: 4px 10px; font-size: 12px; height: 30px; min-width: 180px;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        outline: none; border-color: #0d3b6e;
        box-shadow: 0 0 0 2px rgba(13,59,110,.15);
    }
    .tfoot-total td {
        background: #0d3b6e !important;
        color: #fff !important;
        font-weight: 800 !important;
        font-size: 12px;
        padding: 10px 8px !important;
    }
</style>
@endpush

@section('content')
<section class="content-header" style="display:none;"></section>

<section class="content">
  <div class="container-fluid">

    {{-- PAGE TITLE --}}
    <div class="page-title-card mt-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <h1><i class="fas fa-list-alt mr-2" style="color:#0d3b6e;"></i>{{ $label }}</h1>
          @if($breadcrumb && $breadcrumb !== $label)
            <div class="subtitle">{{ $breadcrumb }}</div>
          @endif
        </div>
        <ol class="breadcrumb mb-0" style="background:none;padding:0;">
          <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('ringkasan.index', ['tahun' => $tahun, 'dari_bulan' => $dariBulan, 'sampai_bulan' => $sampaiBulan]) }}">Ringkasan Pembayaran</a></li>
          <li class="breadcrumb-item active">Detail</li>
        </ol>
      </div>
    </div>

    {{-- INFO BAR --}}
    <div class="info-bar">
      <div class="d-flex align-items-center" style="gap:12px;">
        <span class="badge-info-rk">
          <i class="fas fa-calendar-alt mr-1"></i> {{ $periodeLabel }}
        </span>
        <a href="{{ route('ringkasan.index', ['tahun' => $tahun, 'dari_bulan' => $dariBulan, 'sampai_bulan' => $sampaiBulan]) }}" class="btn btn-default btn-sm">
          <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
      </div>
      <div class="d-flex" style="gap:20px;">
        <span class="stat">Total Transaksi: <span class="val">{{ number_format(count($transaksi), 0, ',', '.') }}</span></span>
        <span class="stat">Total Nilai: <span class="val">Rp {{ number_format($totalNilai, 0, ',', '.') }}</span></span>
      </div>
    </div>

    {{-- TABLE --}}
    <div class="table-card-detail p-3">
      <div style="overflow-x:auto;">
        <table id="tbl-detail" class="table table-bordered table-striped" style="width:100%;">
          <thead>
            <tr>
              <th style="width:35px;">No</th>
              <th style="width:90px;">Tanggal Bayar</th>
              <th style="min-width:200px;">Uraian</th>
              <th style="min-width:140px;">Dibayarkan Kepada</th>
              <th>Kriteria</th>
              <th>Sub Kriteria</th>
              <th>Item Sub Kriteria</th>
              <th>Kebun</th>
              <th>Jenis Pembayaran</th>
              <th style="min-width:120px; text-align:right;">Nilai</th>
            </tr>
          </thead>
          <tbody>
            @forelse($transaksi as $idx => $t)
            <tr>
              <td style="text-align:center; color:#999;">{{ $idx + 1 }}</td>
              <td style="white-space:nowrap;">{{ $t->tanggal ? \Carbon\Carbon::parse($t->tanggal)->format('d/m/Y') : '-' }}</td>
              <td>{{ $t->uraian ?? '-' }}</td>
              <td>{{ $t->penerima ?? '-' }}</td>
              <td>{{ $t->nama_kriteria ?? '-' }}</td>
              <td>{{ $t->nama_sub_kriteria ?? '-' }}</td>
              <td>{{ $t->nama_item_sub_kriteria ?? '-' }}</td>
              <td>{{ $t->kebun ?? '-' }}</td>
              <td>{{ $t->nama_jenis_pembayaran ?? $t->jenis_pembayaran_str ?? '-' }}</td>
              <td class="td-nilai">{{ number_format($t->kredit ?? 0, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="10" style="text-align:center; padding:40px; color:#999;">
                <i class="fas fa-inbox" style="font-size:32px; opacity:0.3;"></i>
                <p style="margin-top:8px;">Tidak ada transaksi untuk kriteria ini.</p>
              </td>
            </tr>
            @endforelse
          </tbody>
          @if(count($transaksi) > 0)
          <tfoot>
            <tr class="tfoot-total">
              <td colspan="9" style="text-align:right;">TOTAL</td>
              <td class="td-nilai" style="color:#f9e79f !important;">{{ number_format($totalNilai, 0, ',', '.') }}</td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
    </div>

  </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#tbl-detail').DataTable({
        paging: true,
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100, 500],
        ordering: true,
        order: [[1, 'asc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: { previous: "‹", next: "›" },
            emptyTable: "Tidak ada data",
            zeroRecords: "Tidak ditemukan data yang cocok"
        },
        columnDefs: [
            { orderable: false, targets: [0] }
        ],
        drawCallback: function(settings) {
            // Re-number rows after sort/filter
            var api = this.api();
            api.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }
    });
});
</script>
@endpush
