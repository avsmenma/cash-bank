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
    #example3 thead th {
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
    #example3 td { font-size: 12px; vertical-align: middle; }
    tbody tr { background-color: #fff; }
    tbody tr:hover { background-color: #f0f5fb; }
</style>
@endpush

<section class="content-header" style="display:none;"></section>

<section class="content">
  <div class="container-fluid">

    {{-- PAGE TITLE CARD --}}
    <div class="page-title-card mt-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1><i class="fas fa-arrow-circle-up mr-2" style="color:#c0392b;"></i>Input Bank Keluar</h1>
        <ol class="breadcrumb mb-0" style="background:none;padding:0;">
          <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
          <li class="breadcrumb-item">Daftar Bank</li>
          <li class="breadcrumb-item active">Bank Keluar</li>
        </ol>
      </div>
    </div>

    {{-- ACTION BAR --}}
    <div class="action-bar">
      <a href="#" rel="noopener" class="btn btn-danger btn-sm" id="deleteAllSelectedRecord">
        <i class="fas fa-trash mr-1"></i>Hapus Terpilih
      </a>
      <a href="#" class="btn btn-warning btn-sm text-white" data-toggle="modal" data-target="#ModalImportFileExcel">
        <i class="fas fa-file-import mr-1"></i>Import Excel
      </a>
      <a href="javascript:void(0)" class="btn btn-success btn-sm" data-toggle="modal" data-target="#ModalCreateKeluar">
        <i class="fas fa-plus mr-1"></i>Tambah Data
      </a>
      <div class="ml-auto d-flex" style="gap:8px;">
        <a href="{{ url('/bank-keluar/view/pdf') }}" target="_blank" class="btn btn-outline-primary btn-sm">
          <i class="fas fa-print mr-1"></i>Download PDF
        </a>
        <a href="{{ url('/bank-keluar/export_excel') }}" class="btn btn-outline-success btn-sm">
          <i class="fas fa-file-excel mr-1"></i>Download Excel
        </a>
      </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="table-card">
      <div class="table-responsive p-0">
        @include('cash_bank.table.tableKeluar')
      </div>
    </div>

  </div>
</section>

{{-- MODAL CREATE & IMPORT --}}
@include('cash_bank.modal.create')
@include('cash_bank.modal.importExcel')

@push('scripts')
<script>
    $(document).ready(function () {
        $('#reservationdate_tambah').datetimepicker({
            format: 'YYYY-MM-DD',
            icons: {
                time: 'far fa-clock', date: 'far fa-calendar',
                up: 'fas fa-chevron-up', down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left', next: 'fas fa-chevron-right',
                today: 'far fa-calendar-check', clear: 'far fa-trash-alt', close: 'far fa-times-circle'
            }
        });
        $('[name="tanggal"]').val(moment().format('YYYY-MM-DD'));
    });

    $('#ModalCreateKeluar').on('shown.bs.modal', function () {
        const modal = $(this);
        modal.find('.select2').each(function () {
            if ($(this).hasClass("select2-hidden-accessible")) $(this).select2('destroy');
        });
        $('#formBankKeluar')[0].reset();
        modal.find('.select2').select2({ theme: 'bootstrap4', dropdownParent: modal, width: '100%' });
        $('#sub_kriteria').html('<option value="">-- Pilih Sub Kriteria --</option>');
        $('#item_sub_kriteria').html('<option value="">-- Pilih Item Sub Kriteria --</option>');
    });

    $(document).on('change', '#kategori_kriteria', function () {
        let kategoriID = $(this).val();
        let $sub = $('#sub_kriteria');
        let $item = $('#item_sub_kriteria');
        $sub.html('<option value="">Memuat...</option>').prop('disabled', true);
        $item.html('<option value="">-- Pilih Item --</option>').prop('disabled', true);
        if (!kategoriID) {
            $sub.html('<option value="">-- Pilih Sub Kriteria --</option>').prop('disabled', false);
            return;
        }
        $.ajax({
            url: '/get-sub-kriteria/' + kategoriID, method: 'GET',
            success: function (subs) {
                let opt = '<option value="">-- Pilih Sub Kriteria --</option>';
                if (Array.isArray(subs) && subs.length > 0) {
                    subs.forEach(s => { opt += `<option value="${s.id_sub_kriteria}">${s.nama_sub_kriteria.trim()}</option>`; });
                }
                $sub.html(opt).prop('disabled', false);
                if ($sub.hasClass("select2-hidden-accessible")) {
                    $sub.select2('destroy').select2({ theme: 'bootstrap4', dropdownParent: $('#ModalCreateKeluar'), width: '100%' });
                }
            }
        });
    });

    $(document).on('change', '#sub_kriteria', function () {
        let subID = $(this).val();
        let $item = $('#item_sub_kriteria');
        $item.html('<option value="">Memuat...</option>').prop('disabled', true);
        if (!subID) { $item.html('<option value="">-- Pilih Item Sub Kriteria --</option>').prop('disabled', false); return; }
        $.ajax({
            url: '/get-item-sub-kriteria/' + subID, method: 'GET',
            success: function (items) {
                let opt = '<option value="">-- Pilih Item Sub Kriteria --</option>';
                if (Array.isArray(items) && items.length > 0) {
                    items.forEach(i => { opt += `<option value="${i.id_item_sub_kriteria}">${i.nama_item_sub_kriteria.trim()}</option>`; });
                }
                $item.html(opt).prop('disabled', false);
                if ($item.hasClass("select2-hidden-accessible")) {
                    $item.select2('destroy').select2({ theme: 'bootstrap4', dropdownParent: $('#ModalCreateKeluar'), width: '100%' });
                }
            }
        });
    });

    function formatRupiah(angka) {
        if (angka === null || angka === undefined || angka === '' || isNaN(angka)) return '0';
        return Math.round(Number(angka)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    $('#editKeluar').on('shown.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let id = button.data('id'), agenda = button.data('agenda'), penerima = button.data('penerima');
        let tanggal = button.data('tanggal'), bank = button.data('bank'), sumber = button.data('sumber');
        let kategori = button.data('kategori'), sub = button.data('sub'), item = button.data('item');
        let jenis = button.data('jenis'), kredit = button.data('kredit'), uraian = button.data('uraian');
        let keterangan = button.data('keterangan');

        $('#formEditKeluar').attr('action', '/bank-keluar/' + id);
        $('#edit_keluar_agenda').val(agenda);
        $('#edit_keluar_penerima').val(penerima);
        $('#edit_keluar_kredit').val(formatRupiah(kredit));
        $('#edit_keluar_keterangan').val(keterangan);
        $('#edit_keluar_uraian').val(uraian);
        $('[name="tanggal"]', '#editKeluar').val(tanggal);
        $('#edit_keluar_date').datetimepicker({ format: 'YYYY-MM-DD' });
        $(this).find('.select2').select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
        $('[name="id_bank_tujuan"]', '#editKeluar').val(bank).trigger('change');
        $('[name="id_sumber_dana"]', '#editKeluar').val(sumber).trigger('change');
        $('[name="id_kategori_kriteria"]', '#editKeluar').val(kategori).trigger('change');
        $('[name="id_jenis_pembayaran"]', '#editKeluar').val(jenis).trigger('change');

        $.get('/get-sub-kriteria/' + kategori, function (subs) {
            let opt = '<option value="">Pilih Sub</option>';
            subs.forEach(s => { opt += `<option value="${s.id_sub_kriteria}">${s.nama_sub_kriteria}</option>`; });
            $('#edit_keluar_sub_kriteria').html(opt).val(sub);
            if ($('#edit_keluar_sub_kriteria').hasClass("select2-hidden-accessible")) $('#edit_keluar_sub_kriteria').select2('destroy');
            $('#edit_keluar_sub_kriteria').select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
            $.get('/get-item-sub-kriteria/' + sub, function (items) {
                let opt2 = '<option value="">Pilih Item</option>';
                items.forEach(i => { opt2 += `<option value="${i.id_item_sub_kriteria}">${i.nama_item_sub_kriteria}</option>`; });
                $('#edit_keluar_item_sub_kriteria').html(opt2).val(item);
                if ($('#edit_keluar_item_sub_kriteria').hasClass("select2-hidden-accessible")) $('#edit_keluar_item_sub_kriteria').select2('destroy');
                $('#edit_keluar_item_sub_kriteria').select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
            });
        });
    });

    $('#edit_keluar_kredit').on('input', function () {
        let cursorPos = this.selectionStart, oldLen = this.value.length;
        let raw = this.value.replace(/\D/g, '');
        let formatted = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        this.value = formatted;
        cursorPos = cursorPos + (formatted.length - oldLen);
        this.setSelectionRange(cursorPos, cursorPos);
    });

    $('#formEditKeluar').on('submit', function () {
        $('#edit_keluar_kredit').val($('#edit_keluar_kredit').val().replace(/\./g, ''));
    });

    $(document).on('click', '#select_all_ids', function () {
        $('.checkbox_ids').prop('checked', this.checked);
    });

    $(document).on('click', '#deleteAllSelectedRecord', function (e) {
        e.preventDefault();
        let all_ids = [];
        $('.checkbox_ids:checked').each(function () { all_ids.push($(this).val()); });
        if (all_ids.length === 0) { alert('Pilih data terlebih dahulu!'); return; }
        if (!confirm(`Yakin ingin menghapus ${all_ids.length} data?`)) return;
        $.ajax({
            url: "{{ route('bank-keluar.delete') }}", type: "DELETE",
            data: { ids: all_ids, _token: '{{ csrf_token() }}' },
            success: function (res) {
                $('#example3').DataTable().ajax.reload(null, false);
                alert(res.success);
            },
            error: function () { alert('Gagal menghapus data'); }
        });
    });
</script>
@endpush

@endsection