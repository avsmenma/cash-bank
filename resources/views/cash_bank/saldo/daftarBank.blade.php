@extends("layouts/index")
@section('content')
@push('styles')
    <style>
        #example2 {
        table-layout: auto !important;
        width: 100% !important;
        }

        #example2 th,
        #example2 td {
            white-space: nowrap;        /* biar kolom melebar */
            vertical-align: middle;
        }
    </style>
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Daftar Virtual Account (VA)</h1>
            </div>
         
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
              <li class="breadcrumb-item active">Daftar VA</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid ">
            <div class="row">
            <div class="col-12 ">
                <!-- Main content -->
                <div class="invoice p-3 mb-3">
                    
                <!-- title row -->
                    <div class="row no-print">
                        <div class="col-12 gap-4">
                            <a href="javascript:void(0)" class="btn btn-success" data-toggle="modal" data-target="#ModalTambahBank"><i class="fas fa-plus"></i> Tambah Data</a>
                        </div>
                    </div>
                    <hr>
                     <div class="row">
                        <div class="col-12 table-responsive">
                            <table id="example2" class="table table-bordered table-hover  justify-content-center">
                                <thead>
                                    <tr id="employee_ids">
                                        <th>No</th>
                                        <th>Nama Bank Tujuan</th>
                                        <th>Created</th>
                                        <th>Update</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <!-- /.col -->
                    </div> 
                </div>
                
                <!-- /.invoice -->
            </div>
            </div>
            
        </div>
    </section>
@push('scripts')
<script>
   $(function () {
       $('#example2').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: "{{ route('daftarBank.data') }}",
            columns: [
            {
                data: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                title: 'No'
            },
                {data: 'nama_tujuan'},
                {data: 'created_at'},
                { data: 'updated_at' },
                {data: 'aksi', orderable:false, searchable:false}
            ]
        });
    });
</script>
@endpush

{{-- MODAL CREATE & EDIT --}}
@include('cash_bank.modal.tambahBank')
@include('cash_bank.modal.editBankTujuan')


@endsection