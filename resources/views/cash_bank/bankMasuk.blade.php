@extends('layouts/index')
@section('content')
    <!-- <div class="content-wrapper"> -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Bank Masuk</h1>
                </div>

                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                        <li class="breadcrumb-item active">Daftar Bank</li>
                        <li class="breadcrumb-item active">Bank Masuk</li>
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
                                    data-target="#ModalImportFileExcelMasuk"><i class="fas fa-file-import "></i> Import
                                    Excel</a>
                                <a href="javascript:void(0)" class="btn btn-success" data-toggle="modal"
                                    data-target="#ModalCreateMasuk"><i class="fas fa-plus"></i> Tambah Data</a>
                                <a href="{{ url('/bank-masuk/view/pdf')}}" target="_blank"
                                    class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a>
                                <a href="{{ url('/bank-masuk/export_excel')}}" class="btn btn-outline-danger "><i
                                        class=" nav-icon fas fa-file-excel"></i></i> Download Excel</a>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-12 table-responsive">
                                @include('cash_bank.table.tableMasuk')
                            </div>
                            <!-- /.col -->
                        </div>
                    </div>
                    <!-- /.invoice -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- Modals -->
    @include('cash_bank.modal.tambahMasuk')
    @include('cash_bank.modal.importExcelMasuk')


    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#reservationdate').datetimepicker({
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
            });
            $('#ModalCreateMasuk').on('shown.bs.modal', function () {
                $('#formCreateMasuk')[0].reset();

                $(this).find('.select2').select2({
                    theme: 'bootstrap4',
                    dropdownParent: $('#ModalCreateMasuk'),
                    width: '100%'
                });

                $('[name="tanggal"]').val(moment().format('YYYY-MM-DD'));
            });
            $('#edit').on('shown.bs.modal', function (event) {

                let button = $(event.relatedTarget);
                let id = button.data('id');
                let agenda = button.data('agenda');
                let penerima = button.data('penerima');
                let tanggal = button.data('tanggal');
                let bank = button.data('bank');
                let sumber = button.data('sumber');
                let kategori = button.data('kategori');
                let jenis = button.data('jenis');
                let debet = button.data('debet');
                let uraian = button.data('uraian');
                let keterangan = button.data('keterangan');

                // set form action
                $('#formEdit').attr('action', '/bank-masuk/' + id);

                // input biasa
                $('#agenda_tahun').val(agenda);
                $('#penerima').val(penerima);
                $('#debet').val(debet);
                $('#keterangan').val(keterangan);
                $('#uraian').val(uraian);

                // datepicker
                $('[name="tanggal"]').val(tanggal);

                // INIT SELECT2 (WAJIB DI MODAL)
                $(this).find('.select2').select2({
                    theme: 'bootstrap4',
                    dropdownParent: $('#edit'),
                    width: '100%'
                });

                // SET VALUE SELECT2 (WAJIB trigger)
                $('[name="id_bank_tujuan"]').val(bank).trigger('change');
                $('[name="id_sumber_dana"]').val(sumber).trigger('change');
                $('[name="id_kategori_kriteria"]').val(kategori).trigger('change');
                $('[name="id_jenis_pembayaran"]').val(jenis).trigger('change');
            });

            $(document).on('click', '#select_all_ids', function () {
                $('.checkbox_ids').prop('checked', $(this).prop('checked'));
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
                    url: "{{ route('bank-masuk.delete') }}",
                    type: "DELETE",
                    data: {
                        ids: all_ids,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        $('#example2').DataTable().ajax.reload(null, false);
                        alert(res.success);
                    },
                    error: function () {
                        alert('Gagal menghapus data');
                    }
                });
            });
        </script>
    @endpush
    @push('style')
        <style>
            /* ===============================
           TABLE WRAPPER
        ================================ */


            /* ===============================
           MOBILE RESPONSIVE
        ================================ */
            @media (max-width: 768px) {
                table {
                    width: 1200px;
                }
            }

            /* ===============================
           UTILITY
        ================================ */
            .font-monospace {
                font-family: 'Courier New', monospace;
            }
        </style>
    @endpush
@endsection