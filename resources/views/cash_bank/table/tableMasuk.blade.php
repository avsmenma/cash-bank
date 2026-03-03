@push('styles')
    <style>
        #example2 {
            table-layout: auto !important;
            width: 100% !important;
        }
        #example2 th,
        #example2 td {
            white-space: nowrap;
            vertical-align: middle;
        }
        /* Kolom uraian: lebar fix, teks wrap ke bawah */
        #example2 td:nth-child(10),
        #example2 th:nth-child(10) {
            white-space: normal !important;
            min-width: 220px;
            max-width: 350px;
            word-break: break-word;
        }
        /* Header navy - harus di sini agar berlaku saat scrollX aktif */
        #example2 thead th,
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
<table id="example2" class="table table-bordered table-hover">
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
            <th>Penerima</th>
            <th>Uraian</th>
            <th>Jenis</th>
            <th>Debet</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#example2').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                autoWidth: false,
                scrollX: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
                ajax: "{{ route('bank-masuk.data') }}",
                columns: [
                    { data: 'checkbox' },
                    {
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        title: 'No'
                    },
                    { data: 'agenda_tahun' },
                    {
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        title: 'No Bukti'
                    },
                    { data: 'tanggal' },
                    { data: 'sumber_dana' },
                    { data: 'bank_tujuan' },
                    { data: 'kategori_kriteria' },

                    { data: 'penerima' },
                    { data: 'uraian', width: '50px' },
                    { data: 'jenis_pembayaran' },
                    { data: 'debet' },
                    { data: 'keterangan' },
                    { data: 'aksi', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endpush
@include('cash_bank.modal.edit')