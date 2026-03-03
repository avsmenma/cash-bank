@push('styles')
    <style>
        #example3 {
            table-layout: fixed !important;
            width: 100% !important;
        }
        #example3 th,
        #example3 td {
            white-space: nowrap;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* Kolom yang boleh wrap teks ke bawah */
        #example3 td:nth-child(6),  /* Sumber Dana */
        #example3 th:nth-child(6),
        #example3 td:nth-child(7),  /* Bank Tujuan */
        #example3 th:nth-child(7),
        #example3 td:nth-child(11), /* Penerima */
        #example3 th:nth-child(11),
        #example3 td:nth-child(12), /* Uraian */
        #example3 th:nth-child(12),
        #example3 td:nth-child(15), /* Keterangan */
        #example3 th:nth-child(15) {
            white-space: normal !important;
            word-break: break-word;
        }
        /* Header navy */
        #example3 thead th,
        .dataTables_scrollHead thead th {
            background: #0d3b6e !important;
            color: #fff !important;
            font-size: 11.5px;
            font-weight: 600;
            padding: 9px 8px;
            border-color: #1a5276 !important;
            text-align: center;
        }
    </style>
@endpush

<table id="example3" class="table table-bordered table-hover">
    <thead>
        <tr>
            <th><input type="checkbox" id="select_all_ids"></th>
            <th>No</th>
            <th>Agenda</th>
            <th>No Bukti</th>
            <th>Tanggal</th>
            <th>Sumber Dana</th>
            <th>Bank Tujuan</th>
            <th>Kriteria</th>
            <th>Sub Kriteria</th>
            <th>Item Sub Kriteria</th>
            <th>Penerima</th>
            <th>Uraian</th>
            <th>Jenis Pembayaran</th>
            <th>Kredit</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#example3').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                autoWidth: false,
                scrollX: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
                ajax: "{{ route('bank-keluar.data') }}",
                columns: [
                    { data: 'checkbox',            width: '35px' },
                    { data: 'DT_RowIndex',         width: '45px',  orderable: false, searchable: false, title: 'No' },
                    { data: 'agenda_tahun',        width: '110px' },
                    { data: 'DT_RowIndex',         width: '70px',  orderable: false, searchable: false, title: 'No Bukti' },
                    { data: 'tanggal',             width: '90px' },
                    { data: 'sumber_dana',         width: '180px' },
                    { data: 'bank_tujuan',         width: '160px' },
                    { data: 'kategori_kriteria',   width: '130px' },
                    { data: 'sub_kriteria',        width: '130px' },
                    { data: 'item_sub_kriteria',   width: '130px' },
                    { data: 'penerima',            width: '150px' },
                    { data: 'uraian',              width: '250px' },
                    { data: 'jenis_pembayaran',    width: '120px' },
                    {
                        data: 'kredit',
                        width: '110px',
                        className: 'text-right',
                        render: function (data) {
                            if (data === null || data === undefined || data === '') return '0';
                            return data;
                        }
                    },
                    { data: 'keterangan',          width: '180px' },
                    { data: 'aksi',                width: '70px', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endpush
@include('cash_bank.modal.editKeluar')