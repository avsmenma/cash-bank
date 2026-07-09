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
    <div class="page-title-card mt-3 cb-fullscreen-hide">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1><i class="fas fa-arrow-circle-up mr-2" style="color:#c0392b;"></i>Input Bank Keluar</h1>
        <ol class="breadcrumb mb-0" style="background:none;padding:0;">
          <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
          <li class="breadcrumb-item">Transaksi Bank</li>
          <li class="breadcrumb-item active">Bank Keluar</li>
        </ol>
      </div>
    </div>

    {{-- ACTION BAR --}}
    <div class="action-bar cb-fullscreen-hide">
      <a href="#" rel="noopener" class="btn btn-danger btn-sm" id="deleteAllSelectedRecord">
        <i class="fas fa-trash mr-1"></i>Hapus
      </a>
      <a href="#" class="btn btn-warning btn-sm text-white" data-toggle="modal" data-target="#ModalImportFileExcel">
        <i class="fas fa-file-import mr-1"></i>Import Excel
      </a>
      <a href="javascript:void(0)" class="btn btn-success btn-sm" data-toggle="modal" data-target="#ModalCreateKeluar">
        <i class="fas fa-plus mr-1"></i>Tambah Data
      </a>
      <button type="button" class="btn btn-info btn-sm" id="btnRefreshTable" onclick="window.cbTableReload('#example3');">
        <i class="fas fa-sync-alt mr-1"></i>Refresh
      </button>
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
    <div class="table-card p-3 cb-fullscreen-table">
      @include('cash_bank.table.tableKeluar')
    </div>

  </div>
</section>

{{-- MODAL CREATE & IMPORT --}}
@include('cash_bank.modal.create')
@include('cash_bank.modal.importExcel')

{{-- Modal Konfirmasi Hapus --}}
<div class="modal fade" id="modalConfirmHapus" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content" style="border-radius:10px; border:none; box-shadow:0 8px 30px rgba(0,0,0,.15);">
      <div class="modal-body text-center py-4 px-4">
        <div style="font-size:3rem; color:#e74c3c; margin-bottom:12px;"><i class="fas fa-exclamation-triangle"></i></div>
        <h5 class="font-weight-bold mb-1">Konfirmasi Hapus</h5>
        <p class="text-muted mb-3" style="font-size:13px;">
          Yakin ingin menghapus <strong id="deleteConfirmCount">0</strong> data yang dipilih?<br>
          <span style="color:#e74c3c; font-size:11.5px;">Tindakan ini tidak dapat dibatalkan.</span>
        </p>
        <div class="d-flex justify-content-center" style="gap:10px;">
          <button type="button" class="btn btn-sm btn-light px-4" data-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-sm btn-danger px-4" id="btnConfirmHapus">
            <i class="fas fa-trash mr-1"></i>Ya, Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal Info (sukses / gagal / peringatan) --}}
<div class="modal fade" id="modalInfo" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content" style="border-radius:10px; border:none; box-shadow:0 8px 30px rgba(0,0,0,.15);">
      <div class="modal-body text-center py-4 px-4">
        <h6 class="font-weight-bold mb-2">
          <i id="modalInfoIcon" class="fas fa-info-circle text-info mr-2"></i>
          <span id="modalInfoTitle">Info</span>
        </h6>
        <p class="text-muted mb-3" style="font-size:13px;" id="modalInfoMsg"></p>
        <button type="button" class="btn btn-sm btn-primary px-4" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

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

        if (kategori === '-' || kategori === null || kategori === '') {
            // Jika kategori "-", set sub dan item juga ke "-"
            let $sub = $('#edit_keluar_sub_kriteria');
            let $item = $('#edit_keluar_item_sub_kriteria');
            $sub.html('<option value="-">-</option>').val('-');
            if ($sub.hasClass("select2-hidden-accessible")) $sub.select2('destroy');
            $sub.select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
            $item.html('<option value="-">-</option>').val('-');
            if ($item.hasClass("select2-hidden-accessible")) $item.select2('destroy');
            $item.select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
        } else {
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
        }
    });

    // === Cascading dropdown handlers untuk EDIT Keluar modal ===
    $(document).on('change', '#editKeluar [name="id_kategori_kriteria"]', function () {
        let kategoriID = $(this).val();
        let $sub = $('#edit_keluar_sub_kriteria');
        let $item = $('#edit_keluar_item_sub_kriteria');

        // Jika user memilih "-", reset semua sub kategori dan item sub kategori ke "-"
        if (kategoriID === '-') {
            $sub.html('<option value="-">-</option>').val('-').prop('disabled', false);
            if ($sub.hasClass("select2-hidden-accessible")) $sub.select2('destroy');
            $sub.select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
            $item.html('<option value="-">-</option>').val('-').prop('disabled', false);
            if ($item.hasClass("select2-hidden-accessible")) $item.select2('destroy');
            $item.select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
            return;
        }

        $sub.html('<option value="">Memuat...</option>').prop('disabled', true);
        $item.html('<option value="">-- Pilih Item Sub Kriteria --</option>').prop('disabled', true);
        if ($item.hasClass("select2-hidden-accessible")) $item.select2('destroy');
        $item.select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
        if (!kategoriID) {
            $sub.html('<option value="">-- Pilih Sub Kriteria --</option>').prop('disabled', false);
            if ($sub.hasClass("select2-hidden-accessible")) $sub.select2('destroy');
            $sub.select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
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
                if ($sub.hasClass("select2-hidden-accessible")) $sub.select2('destroy');
                $sub.select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
            }
        });
    });

    $(document).on('change', '#edit_keluar_sub_kriteria', function () {
        let subID = $(this).val();
        let $item = $('#edit_keluar_item_sub_kriteria');
        $item.html('<option value="">Memuat...</option>').prop('disabled', true);
        if (!subID) {
            $item.html('<option value="">-- Pilih Item Sub Kriteria --</option>').prop('disabled', false);
            if ($item.hasClass("select2-hidden-accessible")) $item.select2('destroy');
            $item.select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
            return;
        }
        $.ajax({
            url: '/get-item-sub-kriteria/' + subID, method: 'GET',
            success: function (items) {
                let opt = '<option value="">-- Pilih Item Sub Kriteria --</option>';
                if (Array.isArray(items) && items.length > 0) {
                    items.forEach(i => { opt += `<option value="${i.id_item_sub_kriteria}">${i.nama_item_sub_kriteria.trim()}</option>`; });
                }
                $item.html(opt).prop('disabled', false);
                if ($item.hasClass("select2-hidden-accessible")) $item.select2('destroy');
                $item.select2({ theme: 'bootstrap4', dropdownParent: $('#editKeluar'), width: '100%' });
            }
        });
    });

    // Auto-fill penerima ketika memilih MPN di dropdown jenis pembayaran
    $(document).on('change', '#editKeluar [name="id_jenis_pembayaran"]', function () {
        let selectedText = $(this).find('option:selected').text().trim();
        let $penerimaField = $('#edit_keluar_penerima');
        
        if (selectedText === 'MPN') {
            $penerimaField.val('Modul Penerimaan Negara (MPN)');
        }
    });

    $('#edit_keluar_kredit').on('input', function () {
        let cursorPos = this.selectionStart, oldLen = this.value.length;
        let raw = this.value.replace(/\D/g, '');
        let formatted = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        this.value = formatted;
        cursorPos = cursorPos + (formatted.length - oldLen);
        this.setSelectionRange(cursorPos, cursorPos);
    });

    $('#formEditKeluar').on('submit', function (e) {
        e.preventDefault();

        // Bersihkan format rupiah sebelum kirim
        let kreditRaw = $('#edit_keluar_kredit').val().replace(/\./g, '');
        $('#edit_keluar_kredit').val(kreditRaw);

        let form = $(this);
        let actionUrl = form.attr('action');
        let formData = new FormData(this);
        // Laravel PUT via FormData perlu override method
        formData.set('_method', 'PUT');

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#editKeluar').modal('hide');
                // Reload DataTable tanpa reset halaman/entries
                window.cbTableReload('#example3');
                // Tampilkan notifikasi
                $('#modalInfoTitle').text('Berhasil');
                $('#modalInfoIcon').attr('class', 'fas fa-check-circle text-success mr-2');
                $('#modalInfoMsg').text('Data berhasil diupdate.');
                $('#modalInfo').modal('show');
            },
            error: function (xhr) {
                // Kembalikan nilai kredit jika error
                $('#edit_keluar_kredit').val($('#edit_keluar_kredit').val().replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan data.';
                $('#modalInfoTitle').text('Gagal');
                $('#modalInfoIcon').attr('class', 'fas fa-times-circle text-danger mr-2');
                $('#modalInfoMsg').text(msg);
                $('#modalInfo').modal('show');
            }
        });
    });

    $(document).on('click', '#select_all_ids', function () {
        $('.checkbox_ids').prop('checked', this.checked);
    });

    // Tampung IDs yang akan dihapus
    var _pendingDeleteIds = [];

    $(document).on('click', '#deleteAllSelectedRecord', function (e) {
        e.preventDefault();
        _pendingDeleteIds = [];
        $('.checkbox_ids:checked').each(function () { _pendingDeleteIds.push($(this).val()); });

        if (_pendingDeleteIds.length === 0) {
            $('#modalInfoTitle').text('Perhatian');
            $('#modalInfoIcon').attr('class', 'fas fa-exclamation-circle text-warning mr-2');
            $('#modalInfoMsg').text('Pilih minimal satu data terlebih dahulu.');
            $('#modalInfo').modal('show');
            return;
        }

        $('#deleteConfirmCount').text(_pendingDeleteIds.length);
        $('#modalConfirmHapus').modal('show');
    });

    $('#btnConfirmHapus').on('click', function () {
        $('#modalConfirmHapus').modal('hide');
        $.ajax({
            url: "{{ route('bank-keluar.delete') }}",
            type: "DELETE",
            contentType: "application/json",
            data: JSON.stringify({ ids: _pendingDeleteIds, _token: '{{ csrf_token() }}' }),
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                window.cbTableReload('#example3');
                $('#select_all_ids').prop('checked', false);
                $('#modalInfoTitle').text('Berhasil');
                $('#modalInfoIcon').attr('class', 'fas fa-check-circle text-success mr-2');
                $('#modalInfoMsg').text(res.success);
                $('#modalInfo').modal('show');
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Gagal menghapus data.';
                $('#modalInfoTitle').text('Gagal');
                $('#modalInfoIcon').attr('class', 'fas fa-times-circle text-danger mr-2');
                $('#modalInfoMsg').text(msg);
                $('#modalInfo').modal('show');
            }
        });
    });
</script>
@endpush

@endsection
