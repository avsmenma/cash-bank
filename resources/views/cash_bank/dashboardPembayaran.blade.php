@extends('layouts/index')
@section('content')
<div class="container-fluid m-3">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Dashboard PD & PvD</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="{{ route('dashboard.index') }}">Dashboard Pembayaran</a></li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Filter Data</h3>
            </div>
            <div class="card-body">
              <form method="GET" action="{{ route('dashboard.pembayaran.index') }}" id="form-filter">
                <div class="row">
                  <div class="col-md-12 d-flex">
                    <div class="form-group col-md-2">
                      <label>Tahun</label>
                      <select class="form-control select2" name="tahun" id="tahun">
                        @for($y = date('Y') - 2; $y <= date('Y') + 5; $y++)
                          <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                      </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Dari Bulan:</label>
                        <select name="bulan_dari" id="bulanDari" class="form-control">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $noBulan => $namaBulan)
                                <option value="{{ $noBulan }}" {{ $noBulan == request('bulan_dari', 1) ? 'selected' : '' }}>
                                    {{ $namaBulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                                    
                    <div class="form-group col-md-2">
                        <label>Sampai Bulan:</label>
                        <select name="bulan_sampai" id="bulanSampai" class="form-control">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $noBulan => $namaBulan)
                                <option value="{{ $noBulan }}" {{ $noBulan == request('bulan_sampai', 12) ? 'selected' : '' }} >
                                    {{ $namaBulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-9">
                      <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button type="submit" class="btn btn-primary" id="btn-filter">
                          <i class="fas fa-filter"></i> Filter Data
                        </button>
                        <button type="button" class="btn btn-secondary" id="btn-reset">
                          <i class="fas fa-sync"></i> Reset
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <div class="card">
            <div class="card-body">
              <div id="table-wrapper">
                <h5>CashFlow PD VS MK {{ $tahun }} dari bulan {{ $bulanDari }} sampai dengan bulan {{ $bulanSampai }}</h5>
                @include('cash_bank.pembayaran.dashboardPembayaran')
              </div>
            </div>
            <div class="overlay" id="loading" style="display: none;">
              <i class="fas fa-2x fa-sync fa-spin"></i>
            </div>
          </div>
        </div>        
      </div>
    </section>
</div>

@push('script')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Event: Tombol reset
    $('#btn-reset').on('click', function() {
        window.location.href = '{{ route("dashboard.pembayaran.index") }}?tahun={{ date("Y") }}';
    });

   // Event: Change filter dengan AJAX
    $('#tahun, #bulanDari, #bulanSampai').on('change', function() {
        loadData();
    });

   function loadData() {
    let tahun = $('#tahun').val();
    let bulanDari = $('#bulanDari').val();
    let bulanSampai = $('#bulanSampai').val();

    if bulanDari > bulanSampai{
      alert('Bulan dari tidak boleh lebih besar dari bulan sampai');
      return false;
      loadData();
    }
    
    $('#loading').show();

    $.ajax({
        url: '{{ route("dashboard.pembayaran.data") }}',
        type: 'GET',
        data: { 
            tahun: tahun,
            bulan_dari: bulanDari,
            bulan_sampai: bulanSampai
        },
        success: function(response) {
            $('#table-wrapper').html(response);
            $('#loading').hide();

            // Set kembali value dropdown
            $('#tahun').val(tahun).trigger('change');
            $('#bulanDari').val(bulanDari).trigger('change');
            $('#bulanSampai').val(bulanSampai).trigger('change');

            updateDownloadLinks(tahun, bulanDari, bulanSampai);
        },
        error: function(xhr) {
            $('#loading').hide();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Gagal memuat data'
            });
        }
    });
}


    // Update link download
    function updateDownloadLinks(tahun) {
        let pdfUrl = '{{ route("dashboard.pembayaran.pdf") }}?tahun=' + tahun;
        let excelUrl = '{{ route("dashboard.pembayaran.excel") }}?tahun=' + tahun;
        
        $('a[href*="dashboard.pembayaran.pdf"]').attr('href', pdfUrl);
        $('a[href*="dashboard.pembayaran.excel"]').attr('href', excelUrl);
    }
});
</script>
@endpush

{{-- Print Styles --}}
<style>
@media print {
    .no-print,
    .breadcrumb,
    .btn,
    form,
    .card-header {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    body {
        background: white !important;
    }
    
    #cashflow-table {
        font-size: 9px !important;
    }
}
</style>
@endsection