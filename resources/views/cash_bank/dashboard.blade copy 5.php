@extends('layouts/index')
@section('content')

<div class="container-fuild m-3">
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
              <li class="breadcrumb-item"><a href="{{route('dashboard.pembayaran.index')}}">Dashboard Versi 2</a></li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <div class="col-12">
                <div class="d-flex justify-content-between">
                    <div class="d-flex gap-2">
                      <div class="form-group m-2">
                        <label>Tahun:</label>
                        <select class="form-control select2" id="tahunGabungan">
                            @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                <option value="{{ $t }}" {{ $t == date('Y') ? 'selected' : '' }}>{{ $t }}</option>
                            @endfor
                        </select>
                      </div>
                      <div class="form-group m-2">
                        <label>Dari Bulan:</label>
                        <select class="form-control" id="bulanDari">
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                      </div>
                      <div class="form-group m-2">
                        <label>Dari Bulan:</label>
                        <select class="form-control" id="bulanSampai">
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12" selected>Desember</option>
                        </select>
                      </div>
                      <div class="form-group m-2">
                          <label>&nbsp;</label>
                          <button class="btn btn-primary btn-block" id="filterGabungan">
                              <i class="fas fa-filter"></i> Filter
                          </button>
                      </div>
                    </div>
                </div>
              </div>
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
              <div class="col-12">
                
                    
                      <h5>CashFlow Pembayaran (Penerima, Dropping, Pembayaran) </h5>
                      @include('cash_bank.dashbordPertama')
                   
                    <div class="overlay" id="loading" style="display: none;">
                      <i class="fas fa-2x fa-sync fa-spin"></i>
                    </div>
               
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
</div>

@push('scripts')
<script>
  // Load data pertama kali
    loadGabungan();
    
    // Filter button click
    $('#filterGabungan').click(function() {
        loadGabungan();
    });
    
    
    // Enter key pada select
    $('#tahunGabungan, #bulanDari, #bulanSampai').keypress(function(e) {
        if(e.which == 13) {
            loadGabungan();
        }
    });
    
    function loadGabungan() {
        var tahun = $('#tahunGabungan').val();
        var bulanDari = $('#bulanDari').val();
        var bulanSampai = $('#bulanSampai').val();
        
        // Validasi bulan
        if(parseInt(bulanDari) > parseInt(bulanSampai)) {
            alert('Bulan dari tidak boleh lebih besar dari bulan sampai');
            return;
        }
        
        $('#gabungan-content').html('<div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
        
        $.ajax({
            url: '{{ route("penerima.gabungan") }}',
            type: 'GET',
            data: {
                tahun: tahun,
                bulan_dari: bulanDari,
                bulan_sampai: bulanSampai
            },
            success: function(response) {
                $('#gabungan-content').html(response);
            },
            error: function() {
                $('#gabungan-content').html('<div class="alert alert-danger">Gagal memuat data</div>');
            }
        });
    }
</script>
@endpush
@endsection

