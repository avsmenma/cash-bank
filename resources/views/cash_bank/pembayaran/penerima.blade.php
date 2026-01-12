@extends('layouts/index')
@section('content')
<div class="container-fuild">
  <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Penerima</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active"><a href="{{route('dashboard.index')}}">Dashboard Pembayaran</a></li>
                <li class="breadcrumb-item"><a href="{{route('dashboard.pembayaran.index')}}">Dashboard Versi 2</a></li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <div class="col-md-12">
            <div class="card">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Dropping/Realisasi</a></li>
                    <li class="nav-item"><a class="nav-link" id="rencana-tab" href="#rencana" data-toggle="tab">Permintaan</a></li>
                    <li class="nav-item"> <a class="nav-link" id="cashflow-tab" data-toggle="tab" href="#timeline">CashFlow Realisasi</a></li>
                    <li class="nav-item"> <a class="nav-link" id="cashflowGabungan-tab" data-toggle="tab" href="#gabungan">CashFlow Gabungan</a></li>
                  </ul>
                </div>
                <div class="card-body">
                  <div class="tab-content">
                      <div class="active tab-pane" id="activity">
                        <div class="row no-print">
                            <div class="col-12 gap-4">
                                <div class="row no-print mb-3 gap-2">
                                    <div style="width:150px" class="gap-2">
                                        <select class="select2" id="tahunRealisasi">
                                            @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                                <option value="{{ $t }}"{{ request('tahun', date('Y')) == $t ? 'selected' : '' }}>
                                                    {{ $t }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                
                                    <a href="#" rel="noopener"  class="btn btn-danger" id="deleteAllSelectedRecord"><i class="fas fa-trash"></i> Delete All</a>
                                    <a href="javascript:void(0)" class="btn btn-success" data-toggle="modal" data-target="#ModalCreatePenerima"><i class="fas fa-plus"></i> Tambah Data</a>
                                    <!-- <a href="{{ url('/bank-masuk/view/pdf')}}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a> -->
                                    <a href="#" class="btn btn-outline-danger " ><i class=" nav-icon fas fa-file-excel"></i></i> Download Excel</a>
                                </div>
                            </div>
                          
                          
                        </div>
    
                      
                        @include('cash_bank.pembayaran.dataPenerima')
                      
                      </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="timeline">
                      <div class="row no-print mb-3">
                        <div class="col-12">
                          <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a>
                            <a href="#" class="btn btn-outline-danger"><i class="fas fa-file-excel"></i> Download Excel</a>
                            <div class="col-md-3">
                                 <select class="select2" id="tahunCashflow">
                                  @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                    <option value="{{ $t }}" {{ request('tahun', date('Y')) == $t ? 'selected' : '' }}> {{ $t }}
                                        </option>
                                  @endfor
                                </select>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div id="cashflow-content" class="p-3 text-center">
                              <span class="text-muted">Memuat data...</span>
                          </div>
                    </div>
                    {{-- TAB RENCANA --}}
                    <div class="tab-pane" id="rencana">
                      <div class="row no-print mb-3">
                        <div class="col-12">
                          <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a>
                            <a href="#" class="btn btn-outline-danger"><i class="fas fa-file-excel"></i> Download Excel</a>
                            <div class="col-md-3">
                                 <select class="select2" id="tahunRencana">
                                  @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                    <option value="{{ $t }}" {{ request('tahun', date('Y')) == $t ? 'selected' : '' }}> {{ $t }}
                                        </option>
                                  @endfor
                                </select>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      {{-- RENCANA CONTENT - ID UNIK --}}
                      <div id="rencana-content" class="p-3">
                        <div class="text-center text-muted">
                          <i class="fas fa-spinner fa-spin"></i> Memuat data rencana...
                        </div>
                      </div>
                    </div>
                    {{-- TAB GABUNGAN --}}
              <div class="tab-pane" id="gabungan">
                  <div class="row no-print mb-3">
                      <div class="col-12">
                          <div class="d-flex gap-2 align-items-center">
                                  <a href="#" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a>
                                  <a href="#" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a>
                              <div class="col-md-2">
                                  <label>Tahun:</label>
                                  <select class="select2" id="tahunGabungan">
                                      @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                          <option value="{{ $t }}" {{ $t == date('Y') ? 'selected' : '' }}>{{ $t }}</option>
                                      @endfor
                                  </select>
                              </div>
                              
                              <div class="col-md-2">
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
                              
                              <div class="col-md-2">
                                  <label>Sampai Bulan:</label>
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
                              
                              <div class="col-md-1">
                                  <label>&nbsp;</label>
                                  <button class="btn btn-primary btn-block" id="filterGabungan">
                                      <i class="fas fa-filter"></i> Filter
                                  </button>
                              </div>
                              <div class="col-4">
                                  
                                </div>
                              <div class="col-1">
                                  <a href="#" class="btn btn-outline-danger"><i class="fas fa-file-excel"></i> Download Excel</a>
                              </div>
                            
                          </div>
                      </div>
                  </div>
                  
                  {{-- GABUNGAN CONTENT - ID UNIK --}}
                  <div id="gabungan-content" class="p-3">
                      <div class="text-center text-muted">
                          <i class="fas fa-spinner fa-spin"></i> Memuat CashFlow data gabungan rencana & realisasi...
                      </div>
                  </div>
                  <!-- /.tab-content -->
                </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
</div>
</div>
</div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    console.log('=== SCRIPT LOADED ===');

    // INIT SELECT2
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
    $('#tahunRealisasi').on('change', function () {
        loadRealisasi();
    });

    function loadRealisasi() {
        let tahun = $('#tahunRealisasi').val();

        $('#realisasi-content').html(
            '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>'
        );

        $.ajax({
            url: "{{ route('penerima.index') }}",
            type: "GET",
            data: { tahun: tahun },
            success: function (res) {
                $('#realisasi-content').html(res);
            },
            error: function () {
                $('#realisasi-content').html(
                    '<div class="alert alert-danger">Gagal memuat data</div>'
                );
            }
        });
    }
    

    // ===== TAB CASHFLOW =====
    $('#cashflow-tab').on('shown.bs.tab', function () {
        console.log('Cashflow tab shown');
        loadCashflow();
    });

    $('#tahunCashflow').on('change', function () {
        console.log('Tahun cashflow changed:', $(this).val());
        loadCashflow();
    });

    function loadCashflow() {
        const tahun = $('#tahunCashflow').val();
        console.log('Loading cashflow for year:', tahun);
        
        $('#cashflow-content').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
        
        $.ajax({
            url: "{{ route('penerima.cashflow') }}",
            method: 'GET',
            data: { tahun: tahun },
            success: function (response) {
                console.log('Cashflow loaded successfully');
                $('#cashflow-content').html(response);
            },
            error: function(xhr) {
                console.error('Cashflow error:', xhr);
                $('#cashflow-content').html('<div class="alert alert-danger">Gagal memuat data cashflow</div>');
            }
        });
    }

    // ===== TAB RENCANA =====
    $('#rencana-tab').on('shown.bs.tab', function () {
        console.log('Rencana tab shown');
        loadRencana();
    });

    $('#tahunRencana').on('change', function () {
        console.log('Tahun rencana changed:', $(this).val());
        loadRencana();
    });

    function loadRencana() {
        const tahun = $('#tahunRencana').val();
        console.log('Loading rencana for year:', tahun);
        
        $('#rencana-content').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
        
        $.ajax({
            url: "{{ route('penerima.rencana') }}",
            method: 'GET',
            data: { tahun: tahun },
            success: function (response) {
                console.log('Rencana loaded successfully');
                $('#rencana-content').html(response);
            },
            error: function(xhr) {
                console.error('Rencana error:', xhr);
                console.error('Response:', xhr.responseText);
                $('#rencana-content').html('<div class="alert alert-danger">Gagal memuat data rencana: ' + xhr.statusText + '</div>');
            }
        });
    }
  
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

    // ===== SAVE CELL RENCANA =====
    $(document).on('blur', '.cell', function () {
        const $cell = $(this);
        console.log('Cell blur - saving data');
        
        $.ajax({
            url: "{{ route('penerima.rencana.save') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: $cell.data('id'),
                kategori: $cell.data('kategori'),
                bulan: $cell.data('bulan'),
                tahun: $cell.data('tahun'),
                nilai: $cell.text().replace(/\./g, '').replace(/,/g, '').trim()
            },
            success: function(response) {
                if(response.success) {
                  
                    console.log('Data saved successfully');
                    $cell.data('id', response.id);
                }
                 // Reload table to update totals
                    setTimeout(() => {
                        loadRencana();
                    }, 500);
            },
            error: function(xhr) {
                console.error('Save error:', xhr);
                alert('Gagal menyimpan data');
            }
        });
    });

    // ===== MODAL CREATE =====
    function hitungNilaiCreate() {
        let volume = parseFloat($('#create_volume').val()) || 0;
        let harga = parseFloat($('#create_harga').val()) || 0;
        let nilai = volume * harga;
        $('#create_nilai').val(nilai.toFixed(2));
        let ppn = Math.round(nilai * 0.11);
        $('#create_ppn').val(ppn);
        let potppn = parseFloat($('#create_potppn').val()) || 0;
        let nilaiInc = nilai + ppn - potppn;
        $('#create_nilai_inc_ppn').val(nilaiInc.toFixed(2));
    }

    $(document).on('input change', '#create_volume, #create_harga, #create_potppn', function () {
        hitungNilaiCreate();
    });

    $('#ModalCreatePenerima').on('shown.bs.modal', function () {
        $('#importExcel')[0].reset();
        if (!$('#create_reservationdate').data('datetimepicker')) {
            $('#create_reservationdate').datetimepicker({ format: 'YYYY-MM-DD' });
        }
        $(this).find('.select2').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#ModalCreatePenerima'),
            width: '100%'
        });
    });

    // ===== MODAL EDIT =====
    function hitungNilaiEdit() {
        let volume = parseFloat($('#edit_volume').val()) || 0;
        let harga = parseFloat($('#edit_harga').val()) || 0;
        let nilai = volume * harga;
        $('#edit_nilai').val(nilai.toFixed(2));
        let ppn = Math.round(nilai * 0.11);
        $('#edit_ppn').val(ppn);
        let potppn = parseFloat($('#edit_potppn').val()) || 0;
        let nilaiInc = nilai + ppn - potppn;
        $('#edit_nilai_inc_ppn').val(nilaiInc.toFixed(2));
    }

    $(document).on('input change', '#edit_volume, #edit_harga, #edit_potppn', function () {
        hitungNilaiEdit();
    });

    $('#editPenerima').on('shown.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let id = button.data('id');
        let kategori = button.data('kategori');
        let pembeli = button.data('pembeli');
        let tanggal = button.data('tanggal');
        let no_reg = button.data('no_reg');
        let kontrak = button.data('kontrak');
        let volume = button.data('volume');
        let harga = button.data('harga');
        let nilai = button.data('nilai');
        let ppn = button.data('ppn');
        let potppn = button.data('potppn');

        $('#formEditPenerima').attr('action', '/penerima/' + id);
        $('#edit_pembeli').val(pembeli);
        $('#edit_ppn').val(ppn);
        $('#edit_potppn').val(potppn);
        $('#edit_no_reg').val(no_reg);
        $('#edit_kontrak').val(kontrak);
        $('#edit_volume').val(volume);
        $('#edit_nilai').val(nilai);
        $('#edit_harga').val(harga);

        let nilaiInc = (parseFloat(nilai) || 0) + (parseFloat(ppn) || 0) - (parseFloat(potppn) || 0);
        $('#edit_nilai_inc_ppn').val(nilaiInc.toFixed(2));

        if (!$('#edit_reservationdate').data('datetimepicker')) {
            $('#edit_reservationdate').datetimepicker({ format: 'YYYY-MM-DD' });
        }
        $('#edit_reservationdate').datetimepicker('date', tanggal);

        $(this).find('.select2').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#editPenerima'),
            width: '100%'
        });

        $('#edit_kategori').val(kategori).trigger('change');
    });
});
</script>
@endpush

@include('cash_bank.modal.modalPenerima.createPenerima')
@endsection