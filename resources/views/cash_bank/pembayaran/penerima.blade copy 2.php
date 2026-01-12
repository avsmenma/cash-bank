@extends('layouts/index')
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <p>sendal</p>
      </div>
    </div>
  </div>
  <div class="col-md-12">
            <div class="card">
                <div class="card-header p-2">
                  <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Daftar Penerima</a></li>
                    <li class="nav-item"> <a class="nav-link" id="cashflow-tab" data-toggle="tab" href="#timeline">CashFlow</a></li>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                      <div class="active tab-pane" id="activity">
                        <div class="row no-print">
                          <div class="col-12 gap-4">
                            <a href="#" rel="noopener"  class="btn btn-danger" id="deleteAllSelectedRecord"><i class="fas fa-trash"></i> Delete All</a>
                            <a href="#" class="btn btn-warning text-white" data-toggle="modal"
                                    data-target="#ModalImportFileExcelMasuk"><i class="fas fa-file-import "></i> Import Excel</a>
                            <a href="javascript:void(0)" class="btn btn-success" data-toggle="modal" data-target="#ModalCreatePenerima"><i class="fas fa-plus"></i> Tambah Data</a>
                            <a href="{{ url('/bank-masuk/view/pdf')}}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a>
                            <a href="{{ url('/bank-masuk/export_excel')}}" class="btn btn-outline-danger " ><i class=" nav-icon fas fa-file-excel"></i></i> Download Excel</a>
                            <button class="btn btn-primary" 
                                    type="button"
                                    data-toggle="collapse"
                                    data-target="#filterCollapse"><i class=" nav-icon fas fa-filter "></i></i> Filter
                            </button>
                          </div>
                        </div>
                        <div class="collapse mt-3" id="filterCollapse" >
                          <div class="d-flex gap-2">
                              <div class="form-group mb-0">
                                  <select class="select2" name="tahun">
                                      <option value="" disabled selected>-- Pilih Tahun --</option>
                                      <option value="2026">2026</option>
                                      <option value="2027">2027</option>
                                      <option value="2028">2028</option>
                                  </select>
                              </div>
                              <div class="form-group mb-0">
                                  <select class="select2" name="bulam">
                                      <option value="" disabled selected>-- Pilih Bulan --</option>
                                      <option value="2026">Januari</option>
                                      <option value="2027">Februari</option>
                                      <option value="2028">Maret</option>
                                  </select>
                              </div>
                              <div class="form-group mb-0">
                                  <select class="select2" name="bulam">
                                      <option value="" disabled selected>-- Pilih Tanggal --</option>
                                      <option value="2026">Januari</option>
                                      <option value="2027">Februari</option>
                                      <option value="2028">Maret</option>
                                  </select>
                              </div>

                              <div class="form-group mb-0">
                                  <select class="select2" name="jenis_pembeli">
                                      <option value="" disabled selected>-- Pilih Kategori --</option>
                                      <option value="umum">Umum</option>
                                      <option value="member">Member</option>
                                  </select>
                              </div>
                              <div class="form-group mb-0">
                                  <select class="select2" name="jenis_pembeli">
                                      <option value="" disabled selected>-- Pilih Jenis Pembeli --</option>
                                      <option value="umum">Umum</option>
                                      <option value="member">Member</option>
                                  </select>
                              </div>
                              <a href="3" class="btn btn-danger " ><i class=" nav-icon fas fa-user-secret"></i></i> Reset Filter</a>

                          </div>
                        </div>
                      
                        @include('cash_bank.pembayaran.dataPenerima')
                      
                      </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="timeline">
                      <div class="row no-print">
                        <div class="d-flex gap-4 no-print">
                            <a href="{{ url('/bank-masuk/view/pdf')}}" target="_blank" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a>
                            <a href="{{ url('/bank-masuk/export_excel')}}" class="btn btn-outline-danger " ><i class=" nav-icon fas fa-file-excel"></i></i> Download Excel</a>
                            <div class="col-md-3">
                                 <select class="select2" name="tahun">
                                      <option value="" disabled selected>-- Pilih Tahun --</option>
                                      <option value="2026">2026</option>
                                      <option value="2027">2027</option>
                                      <option value="2028">2028</option>
                                  </select>
                            </div>
                        </div>
                      </div>
                      <div id="cashflow-content" class="p-3 text-center">
                              <span class="text-muted">Memuat data...</span>
                          </div>
                    </div>
                  </div>
                  <!-- /.tab-content -->
                </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
</div>
</div>
@push('scripts')
<script>
  // INIT SELECT2 (WAJIB DI MODAL)
  $(document).ready(function () {
      $(this).find('.select2').select2({
          theme: 'bootstrap4',
          // dropdownParent: $('#editPenerima'),
          width: '100%'
      });
  });
$('#editPenerima').on('shown.bs.modal', function (event) {
    
    let button = $(event.relatedTarget);
    let id       = button.data('id');
    let kategori   = button.data('kategori');
    let pembeli = button.data('pembeli');
    let tanggal  = button.data('tanggal');
    let no_reg  = button.data('no_reg');
    let kontrak = button.data('kontrak');
    let volume    = button.data('volume');
    let harga   = button.data('harga');
    let nilai    = button.data('nilai');
    let ppn    = button.data('ppn');
    let potppn    = button.data('potppn');

    // set form action
    $('#formEditPenerima').attr('action', '/penerima/' + id);

    // input biasa
    $('#pembeli').val(pembeli);
    $('#ppn').val(ppn);
    $('#potppn').val(potppn);
    $('#no_reg').val(no_reg);
    $('#kontrak').val(kontrak);
    $('#volume').val(volume);
    $('#nilai').val(nilai);
    $('#harga').val(harga);

    // datepicker
    $('[name="tanggal"]').val(tanggal);
    
    // INIT SELECT2 (WAJIB DI MODAL)
    $(this).find('.select2').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#editPenerima'),
        width: '100%'
    });

    // SET VALUE SELECT2 (WAJIB trigger)
    $('[name="id_kategori_kriteria"]').val(kategori).trigger('change');
});
$('#cashflow-tab').on('shown.bs.tab', function () {
    if (!$('#timeline').data('loaded')) {
        $.get("{{ route('penerima.cashflow') }}", function (res) {
            $('#cashflow-content').html(res);
            $('#timeline').data('loaded', true);
        });
    }
});
</script>
@endpush


{{-- MODAL CREATE & EDIT --}}
@include('cash_bank.modal.modalPenerima.createPenerima')
@endsection