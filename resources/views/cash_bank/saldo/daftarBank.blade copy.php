@extends("layouts/index")
@section('content')

<div class="container-fluid mt-4">
    <h1 class="tittle">Daftar <span style="color: #FF7518">Bank Tujuan</span></h1>
    <small>Ini daftar Bank PTPN</small>

    <!-- Tabel -->
    <div class="row mt-3">
        <div class="col-md-12">
            
            <div class="card rounded-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="justify-content-start align-items-center mb-3">
                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                            <button 
                                class="btn-tambah btn-success rounded-2 shadow-sm border-0 p-2"
                                data-bs-toggle="modal"
                                data-bs-target="#ModalTambahBank">
                                <i class="bi bi-database-fill-add"></i> Tambah
                            </button>
                        </div>
                        <hr>
                        
                        <table class="table table-hover table-bordered align-middle display" id="example">
                            <thead class="text-center table-primary ">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Bank Tujuan</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Tanggal Update</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody >
                                @foreach ($data as $index => $row)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $row->nama_tujuan }}</td>
                                    <td class="text-center">{{ $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="text-center">{{ $row->updated_at ? $row->updated_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editBankTujuan"
                                                data-id="{{ $row->id_bank_tujuan }}"
                                                data-nama="{{ $row->nama_tujuan }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="{{ route('daftarBank.destroy', $row->id_bank_tujuan) }}"
                                            method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @include('cash_bank.modal.editBankTujuan')

                </div>
            </div>
        </div>
    </div>
</div>
<script>
        new DataTable('#example');
    </script>
{{-- MODAL CREATE & EDIT --}}
@include('cash_bank.modal.tambahBank')


@endsection
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
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
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


@endsection