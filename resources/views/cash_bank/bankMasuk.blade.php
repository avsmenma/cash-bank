@extends('layouts/index')
@section('content')

@push('styles')
<style>
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
        font-size: 1.55rem;
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
    }
    /* TABLE HEADER */
    #example2 thead th {
        background: #0d3b6e !important;
        color: #fff !important;
        font-size: 11.5px;
        font-weight: 600;
        padding: 9px 8px;
        border-color: #1a5276 !important;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
    }
    #example2 td { font-size: 12px; vertical-align: middle; }
    tbody tr { background-color: #fff; }
    tbody tr:hover { background-color: #f0f5fb; }

    /* DataTable controls */
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
        font-size: 12.5px; font-weight: 500; color: #444;
        display: flex; align-items: center; gap: 6px; margin: 0;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #ced4da; border-radius: 4px;
        padding: 3px 8px; font-size: 12.5px; height: 32px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ced4da; border-radius: 4px;
        padding: 4px 10px; font-size: 12.5px; height: 32px; min-width: 200px;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        outline: none; border-color: #0d3b6e;
        box-shadow: 0 0 0 2px rgba(13,59,110,.15);
    }
</style>
@endpush

<section class="content-header" style="display:none;"></section>

<section class="content">
  <div class="container-fluid">

    {{-- PAGE TITLE CARD --}}
    <div class="page-title-card mt-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1><i class="fas fa-arrow-circle-down mr-2" style="color:#1a7a3d;"></i>Input Bank Masuk</h1>
        <ol class="breadcrumb mb-0" style="background:none;padding:0;">
          <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
          <li class="breadcrumb-item">Daftar Bank</li>
          <li class="breadcrumb-item active">Bank Masuk</li>
        </ol>
      </div>
    </div>

    {{-- ACTION BAR --}}
    <div class="action-bar">
      <a href="#" rel="noopener" class="btn btn-danger btn-sm" id="deleteAllSelectedRecord">
        <i class="fas fa-trash mr-1"></i>Hapus
      </a>
      <a href="#" class="btn btn-warning btn-sm text-white" data-toggle="modal" data-target="#ModalImportFileExcelMasuk">
        <i class="fas fa-file-import mr-1"></i>Import Excel
      </a>
      <a href="javascript:void(0)" class="btn btn-success btn-sm" data-toggle="modal" data-target="#ModalCreateMasuk">
        <i class="fas fa-plus mr-1"></i>Tambah Data
      </a>
      <div class="ml-auto d-flex gap-2" style="gap:8px;">
        <a href="{{ url('/bank-masuk/view/pdf') }}" target="_blank" class="btn btn-outline-primary btn-sm">
          <i class="fas fa-print mr-1"></i>Download PDF
        </a>
        <a href="{{ url('/bank-masuk/export_excel') }}" class="btn btn-outline-success btn-sm">
          <i class="fas fa-file-excel mr-1"></i>Download Excel
        </a>
      </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="table-card">
      <div class="table-responsive p-0">
        @include('cash_bank.table.tableMasuk')
      </div>
    </div>

  </div>
</section>

{{-- Modals --}}
@include('cash_bank.modal.tambahMasuk')
@include('cash_bank.modal.importExcelMasuk')

@push('scripts')
<script>
    $(document).ready(function () {});
    $('#ModalCreateMasuk').on('shown.bs.modal', function () {
        $('#formCreateMasuk')[0].reset();
        $(this).find('.select2').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#ModalCreateMasuk'),
            width: '100%'
        });
        var today = new Date().toISOString().split('T')[0];
        $('#tanggal_masuk').val(today);
    });
    $('#edit').on('shown.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let id       = button.data('id');
        let agenda   = button.data('agenda');
        let penerima = button.data('penerima');
        let tanggal  = button.data('tanggal');
        let bank     = button.data('bank');
        let sumber   = button.data('sumber');
        let kategori = button.data('kategori');
        let jenis    = button.data('jenis');
        let debet    = button.data('debet');
        let uraian   = button.data('uraian');
        let keterangan = button.data('keterangan');

        $('#formEdit').attr('action', '/bank-masuk/' + id);
        $('#agenda_tahun').val(agenda);
        $('#penerima').val(penerima);
        let debetNum = parseFloat(debet) || 0;
        $('#debet').val(debetNum.toLocaleString('id-ID'));
        $('#keterangan').val(keterangan);
        $('#uraian').val(uraian);
        $('[name="tanggal"]').val(tanggal);

        $(this).find('.select2').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#edit'),
            width: '100%'
        });
        $('[name="id_bank_tujuan"]').val(bank).trigger('change');
        $('[name="id_sumber_dana"]').val(sumber).trigger('change');
        $('[name="id_kategori_kriteria"]').val(kategori).trigger('change');
        $('[name="id_jenis_pembayaran"]').val(jenis).trigger('change');
    });

    $('#formEdit').on('submit', function () {
        let debetVal = $('#debet').val();
        $('#debet').val(debetVal.replace(/\./g, ''));
    });

    $('#debet').on('input', function () {
        let cursorPos = this.selectionStart;
        let oldLen = this.value.length;
        let raw = this.value.replace(/\D/g, '');
        let formatted = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        this.value = formatted;
        let newLen = formatted.length;
        cursorPos = cursorPos + (newLen - oldLen);
        this.setSelectionRange(cursorPos, cursorPos);
    });

    $(document).on('click', '#select_all_ids', function () {
        $('.checkbox_ids').prop('checked', $(this).prop('checked'));
    });

    $(document).on('click', '#deleteAllSelectedRecord', function (e) {
        e.preventDefault();
        let all_ids = [];
        $('.checkbox_ids:checked').each(function () { all_ids.push($(this).val()); });
        if (all_ids.length === 0) { alert('Pilih data terlebih dahulu!'); return; }
        if (!confirm(`Yakin ingin menghapus ${all_ids.length} data?`)) return;
        $.ajax({
            url: "{{ route('bank-masuk.delete') }}",
            type: "DELETE",
            data: { ids: all_ids, _token: '{{ csrf_token() }}' },
            success: function (res) {
                $('#example2').DataTable().ajax.reload(null, false);
                alert(res.success);
            },
            error: function () { alert('Gagal menghapus data'); }
        });
    });
</script>
@endpush

@endsection