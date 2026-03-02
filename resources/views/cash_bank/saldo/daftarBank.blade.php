@extends("layouts/index")
@section('content')

@push('styles')
<style>
    /* TABLE */
    #example2 { table-layout: auto !important; width: 100% !important; }
    #example2 th, #example2 td { white-space: nowrap; vertical-align: middle; font-size: 12px; }

    /* HEADER TABEL */
    #example2 thead th {
        background: #0d3b6e !important;
        color: #fff !important;
        font-size: 11.5px;
        font-weight: 600;
        padding: 9px 10px;
        border-color: #1a5276 !important;
        text-align: center;
        vertical-align: middle;
    }

    /* PAGE TITLE CARD */
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
        padding: 10px 16px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* TABLE CARD */
    .table-card {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(13,59,110,.08);
        overflow: hidden;
        padding: 16px;
    }

    /* DataTable controls */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 12px;
    }
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
        font-size: 12.5px;
        font-weight: 500;
        color: #444;
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 3px 8px;
        font-size: 12.5px;
        height: 32px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 4px 10px;
        font-size: 12.5px;
        height: 32px;
        min-width: 200px;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: #0d3b6e;
        box-shadow: 0 0 0 2px rgba(13,59,110,.15);
    }
    .dataTables_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 4px;
    }
    tbody tr { background-color: #ffffff; }
    tbody tr:hover { background-color: #f0f5fb; }
</style>
@endpush

<section class="content">
  <div class="container-fluid">

    {{-- PAGE TITLE CARD --}}
    <div class="page-title-card mt-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:8px;">
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
      <div class="table-responsive">
        <table id="example2" class="table table-bordered mb-0">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Bank / VA</th>
              <th>Saldo Akhir</th>
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
        { data: 'saldo_akhir', orderable: false, searchable: false },
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