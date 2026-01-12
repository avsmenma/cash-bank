@extends('layouts/index')
@section('content')
<div class="container-fluid m-3">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Dashboard Pembayaran</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Dashboard Pembayaran</li>
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
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Tahun</label>
                      <select class="form-control select2" name="tahun" id="tahun">
                        @for($y = date('Y') - 2; $y <= date('Y') + 5; $y++)
                          <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                      </select>
                    </div>
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
                      <a href="{{ route('dashboard.pembayaran.pdf', ['tahun' => $tahun]) }}" target="_blank" class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf"></i> Download PDF
                      </a>
                      <a href="{{ route('dashboard.pembayaran.excel', ['tahun' => $tahun]) }}" class="btn btn-outline-success">
                        <i class="fas fa-file-excel"></i> Download Excel
                      </a>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div id="table-wrapper">
                <h5>Realisasi Dropping HO dibandingkan Penerimaan Core dan Non Core.</h5>
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
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <div id="table-wrapper">
                <form method="GET" action="{{ route('dashboard.pembayaran.index') }}" id="form-filter">
                  <div class="row">
                    <div class="col-md-9">
                      <div class="form-group">
                        <button type="button" class="btn btn-secondary" id="btn-reset">
                          <i class="fas fa-sync"></i> Reset
                        </button>
                        <a href="{{ route('dashboard.pembayaran.pdf', ['tahun' => $tahun]) }}" target="_blank" class="btn btn-outline-danger">
                          <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                        <a href="{{ route('dashboard.pembayaran.excel', ['tahun' => $tahun]) }}" class="btn btn-outline-success">
                          <i class="fas fa-file-excel"></i> Download Excel
                        </a>
                      </div>
                    </div>
                  </div>
                </form>
                <h5>Realisasi Dropping HO dibandingkan Penerimaan Core dan Non Core & Pembayaran.</h5>
                @include('cash_bank.pembayaran.dashboardPembayaran2', $data2)
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

    // Event: Change tahun dengan AJAX (optional - lebih smooth)
    $('#tahun').on('change', function() {
        loadData();
    });

    // Load data dengan AJAX (optional - untuk pengalaman lebih smooth)
    function loadData() {
        let tahun = $('#tahun').val();
        $('#loading').show();

        $.ajax({
            url: '{{ route("dashboard.pembayaran.data") }}',
            type: 'GET',
            data: {
                tahun: tahun
            },
            success: function(response) {
                $('#table-wrapper').html(response);
                $('#loading').hide();
                
                // Update link download
                updateDownloadLinks(tahun);
            },
            error: function(xhr) {
                $('#loading').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal memuat data: ' + (xhr.responseJSON?.message || xhr.statusText)
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
@endsection