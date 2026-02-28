@extends("layouts/index")
@section('content')
@push('styles')
<style>
    /* TABLE */
    #example2 { table-layout: auto !important; width: 100% !important; }
    #example2 th, #example2 td { white-space: nowrap; vertical-align: middle; font-size: 12px; }

    /* HEADER */
    #example2 thead th {
        background: #0d3b6e !important;
        color: #fff !important;
        font-size: 11.5px;
        font-weight: 600;
        padding: 9px 10px;
        border-color: #1a5276 !important;
        text-align: center;
    }
    /* PAGE TITLE */
    .page-title-card {
        background: #fff;
        border-top: 4px solid #0d3b6e;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(13,59,110,.10);
        padding: 18px 24px 14px;
        margin-bottom: 18px;
    }
    .page-title-card h1 {
        font-size: 1.6rem;
        font-weight: 700;
        color: #0d3b6e;
        margin: 0;
    }
    /* ACTION BAR */
    .action-bar {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 8px rgba(13,59,110,.07);
        padding: 12px 18px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    /* TABLE CARD */
    .table-card {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(13,59,110,.08);
        overflow: hidden;
    }
    tbody tr { background-color: #ffffff; }
    tbody tr:hover { background-color: #f0f5fb; }
</style>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Daftar Virtual Account (VA)</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
          <li class="breadcrumb-item active">Daftar VA</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">

    {{-- PAGE TITLE CARD --}}
    <div class="page-title-card">
      <div class="d-flex align-items-center justify-content-between">
        <h1><i class="fas fa-university mr-2"></i>Daftar Virtual Account (VA)</h1>
        <ol class="breadcrumb mb-0" style="background:none;padding:0;">
          <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
          <li class="breadcrumb-item active">Daftar VA</li>
        </ol>
      </div>
    </div>

    {{-- ACTION BAR --}}
    <div class="action-bar">
      <a href="javascript:void(0)" class="btn btn-success btn-sm" data-toggle="modal" data-target="#ModalTambahBank">
        <i class="fas fa-plus mr-1"></i>Tambah Data
      </a>
    </div>

    {{-- TABLE CARD --}}
    <div class="table-card">
      <div class="table-responsive p-0">
        <table id="example2" class="table table-bordered mb-0">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Bank / VA</th>
              <th>Dibuat</th>
              <th>Diperbarui</th>
              <th>Aksi</th>
            </tr>
          </thead>
        </table>
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
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
      ajax: "{{ route('daftarBank.data') }}",
      columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false, title: 'No' },
        { data: 'nama_tujuan' },
        { data: 'created_at' },
        { data: 'updated_at' },
        { data: 'aksi', orderable: false, searchable: false }
      ]
    });
  });
</script>
@endpush

{{-- MODAL CREATE & EDIT --}}
@include('cash_bank.modal.tambahBank')
@include('cash_bank.modal.editBankTujuan')

@endsection