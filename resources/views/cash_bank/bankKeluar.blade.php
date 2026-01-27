@extends('layouts/index')
@section('content')
    <!-- <div class="content-wrapper"> -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Bank Keluar</h1>
                </div>

                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard Pembayaran</a></li>
                        <li class="breadcrumb-item active">Daftar Bank</li>
                        <li class="breadcrumb-item active">Bank Keluar</li>
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
                                <a href="#" rel="noopener" class="btn btn-danger" id="deleteAllSelectedRecord"><i
                                        class="fas fa-trash"></i> Delete All</a>
                                <a href="#" class="btn btn-warning text-white" data-toggle="modal"
                                    data-target="#ModalImportFileExcel"><i class="fas fa-file-import "></i> Import Excel</a>
                                <a href="javascript:void(0)" class="btn btn-success" data-toggle="modal"
                                    data-target="#ModalCreateKeluar"><i class="fas fa-plus"></i> Tambah Data</a>
                                <a href="{{ url('/bank-keluar/view/pdf')}}" target="_blank"
                                    class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a>
                                <a href="{{ url('/bank-keluar/export_excel')}}" class="btn btn-outline-danger "><i
                                        class=" nav-icon fas fa-file-excel"></i></i> Download Excel</a>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-12 table-responsive">
                                @include('cash_bank.exportExcel.excelKeluar')
                            </div>
                            <!-- /.col -->
                        </div>
                    </div>
                    <!-- /.invoice -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#reservationdate_tambah').datetimepicker({
                    format: 'YYYY-MM-DD',
                    icons: {
                        time: 'far fa-clock',
                        date: 'far fa-calendar',
                        up: 'fas fa-chevron-up',
                        down: 'fas fa-chevron-down',
                        previous: 'fas fa-chevron-left',
                        next: 'fas fa-chevron-right',
                        today: 'far fa-calendar-check',
                        clear: 'far fa-trash-alt',
                        close: 'far fa-times-circle'
                    }
                });

                $('[name="tanggal"]').val(moment().format('YYYY-MM-DD'));
            });

            $('#ModalCreateKeluar').on('shown.bs.modal', function () {
                const modal = $(this);

                // Destroy Select2 jika sudah ada
                modal.find('.select2').each(function () {
                    if ($(this).hasClass("select2-hidden-accessible")) {
                        $(this).select2('destroy');
                    }
                });

                // Reset form
                $('#formBankKeluar')[0].reset();

                // Inisialisasi Select2
                modal.find('.select2').select2({
                    theme: 'bootstrap4',
                    dropdownParent: modal,
                    width: '100%'
                });

                // Reset dropdown dependent
                $('#sub_kriteria').html('<option value="">-- Pilih Sub Kriteria --</option>');
                $('#item_sub_kriteria').html('<option value="">-- Pilih Item Sub Kriteria --</option>');
            });

            // ✅ PERBAIKAN UTAMA - Event handler untuk perubahan kategori
            $(document).on('change', '#kategori_kriteria', function () {
                let kategoriID = $(this).val();

                console.log('Kategori berubah:', kategoriID);

                // Reset dropdown dependent
                let $subKriteria = $('#sub_kriteria');
                let $itemSubKriteria = $('#item_sub_kriteria');

                // Clear dan disable sementara
                $subKriteria.html('<option value="">Memuat...</option>').prop('disabled', true);
                $itemSubKriteria.html('<option value="">-- Pilih Item --</option>').prop('disabled', true);

                if (!kategoriID) {
                    $subKriteria.html('<option value="">-- Pilih Sub Kriteria --</option>').prop('disabled', false);
                    return;
                }

                // Load sub kriteria
                $.ajax({
                    url: '/get-sub-kriteria/' + kategoriID,
                    method: 'GET',
                    success: function (subs) {
                        console.log('RESP SUB:', subs);

                        let opt = '<option value="">-- Pilih Sub Kriteria --</option>';

                        if (Array.isArray(subs) && subs.length > 0) {
                            subs.forEach(function (s) {
                                // Trim untuk menghilangkan \r\n
                                let namaSubKriteria = s.nama_sub_kriteria.trim();
                                opt += `<option value="${s.id_sub_kriteria}">${namaSubKriteria}</option>`;
                            });
                        }

                        // Update HTML dan enable kembali
                        $subKriteria.html(opt).prop('disabled', false);

                        // ⚠️ KUNCI UTAMA: Trigger change SETELAH HTML updated
                        // Tapi jangan gunakan .trigger('change') karena bisa loop
                        // Gunakan .trigger('change.select2') untuk update tampilan Select2 saja
                        if ($subKriteria.hasClass("select2-hidden-accessible")) {
                            $subKriteria.select2('destroy').select2({
                                theme: 'bootstrap4',
                                dropdownParent: $('#ModalCreateKeluar'),
                                width: '100%'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error loading sub kriteria:', error);
                        $subKriteria.html('<option value="">-- Error memuat data --</option>').prop('disabled', false);
                    }
                });
            });


            $(document).on('change', '#sub_kriteria', function () {
                let subID = $(this).val();

                console.log('Sub Kriteria berubah:', subID);

                let $itemSubKriteria = $('#item_sub_kriteria');

                // Clear dan disable sementara
                $itemSubKriteria.html('<option value="">Memuat...</option>').prop('disabled', true);

                if (!subID) {
                    $itemSubKriteria.html('<option value="">-- Pilih Item Sub Kriteria --</option>').prop('disabled', false);
                    return;
                }

                // Load item sub kriteria
                $.ajax({
                    url: '/get-item-sub-kriteria/' + subID,
                    method: 'GET',
                    success: function (items) {
                        console.log('RESP ITEM:', items);

                        let opt = '<option value="">-- Pilih Item Sub Kriteria --</option>';

                        if (Array.isArray(items) && items.length > 0) {
                            items.forEach(function (i) {
                                // Trim untuk menghilangkan \r\n
                                let namaItem = i.nama_item_sub_kriteria.trim();
                                opt += `<option value="${i.id_item_sub_kriteria}">${namaItem}</option>`;
                            });
                        }

                        // Update HTML dan enable kembali
                        $itemSubKriteria.html(opt).prop('disabled', false);

                        // Re-initialize Select2 untuk refresh tampilan
                        if ($itemSubKriteria.hasClass("select2-hidden-accessible")) {
                            $itemSubKriteria.select2('destroy').select2({
                                theme: 'bootstrap4',
                                dropdownParent: $('#ModalCreateKeluar'),
                                width: '100%'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error loading item sub kriteria:', error);
                        $itemSubKriteria.html('<option value="">-- Error memuat data --</option>').prop('disabled', false);
                    }
                });
            });
            $('#editKeluar').on('shown.bs.modal', function (event) {

                let button = $(event.relatedTarget);
                let id = button.data('id');
                let agenda = button.data('agenda');
                let penerima = button.data('penerima');
                let tanggal = button.data('tanggal');
                let bank = button.data('bank');
                let sumber = button.data('sumber');
                let kategori = button.data('kategori');
                let sub = button.data('sub');
                let item = button.data('item');
                let jenis = button.data('jenis');
                let kredit = button.data('kredit');
                let uraian = button.data('uraian');
                let keterangan = button.data('keterangan');

                $('[name="id_kategori_kriteria"]').val(kategori).trigger('change');

                // Load sub → set sub → load item → set item
                $.get('/get-sub-kriteria/' + kategori, function (subs) {
                    let opt = '<option value="">Pilih Sub</option>';
                    subs.forEach(s => {
                        opt += `<option value="${s.id_sub_kriteria}">${s.nama_sub_kriteria}</option>`;
                    });
                    $('#sub_kriteria').html(opt).val(sub).trigger('change');

                    $.get('/get-item-sub-kriteria/' + sub, function (items) {
                        let opt2 = '<option value="">Pilih Item</option>';
                        items.forEach(i => {
                            opt2 += `<option value="${i.id_item_sub_kriteria}">${i.nama_item_sub_kriteria}</option>`;
                        });
                        $('#item_sub_kriteria').html(opt2).val(item).trigger('change');
                    });
                });

                // set form action
                $('#formEdit').attr('action', '/bank-keluar/' + id);

                // input biasa
                $('#agenda_tahun').val(agenda);
                $('#penerima').val(penerima);
                $('#kredit').val(formatRupiah(kredit));
                $('#keterangan').val(keterangan);
                $('#uraian').val(uraian);

                // datepicker
                $('[name="tanggal"]').val(tanggal);

                // INIT SELECT2 (WAJIB DI MODAL)
                $(this).find('.select2').select2({
                    theme: 'bootstrap4',
                    dropdownParent: $('#editKeluar'),
                    width: '100%'
                });

                // SET VALUE SELECT2 (WAJIB trigger)
                $('[name="id_bank_tujuan"]').val(bank).trigger('change');
                $('[name="id_sumber_dana"]').val(sumber).trigger('change');
                $('[name="id_jenis_pembayaran"]').val(jenis).trigger('change');
            });

            $(document).on('click', '#select_all_ids', function () {
                $('.checkbox_ids').prop('checked', this.checked);
            });
            $(document).on('click', '#deleteAllSelectedRecord', function (e) {
                e.preventDefault();

                let all_ids = [];

                $('.checkbox_ids:checked').each(function () {
                    all_ids.push($(this).val());
                });

                if (all_ids.length === 0) {
                    alert('Pilih data terlebih dahulu!');
                    return;
                }

                if (!confirm(`Yakin ingin menghapus ${all_ids.length} data?`)) return;

                $.ajax({
                    url: "{{ route('bank-keluar.delete') }}",
                    type: "DELETE",
                    data: {
                        ids: all_ids,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        $('#example3').DataTable().ajax.reload(null, false);
                        alert(res.success);
                    },
                    error: function () {
                        alert('Gagal menghapus data');
                    }
                });
            });

            $('#formEdit').on('submit', function () {
                let credit = $('#kredit').val();
                // Remove dots before submit
                $('#kredit').val(credit.replace(/\./g, ''));
            });
        </script>
    @endpush
    {{-- MODAL CREATE & IMPORT --}}
    @include('cash_bank.modal.create')
@endsection