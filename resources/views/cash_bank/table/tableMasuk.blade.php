@push('styles')
    <style>
        #example2 {
            table-layout: fixed !important;
            width: 100% !important;
            min-width: 2240px;
        }
        #example2 th,
        #example2 td {
            white-space: nowrap;
            vertical-align: middle;
            overflow: hidden;
            text-overflow: clip;
        }
        /* Ukuran berbasis data: uraian rata-rata 69 karakter, 91% <= 120 karakter, outlier dibungkus ke bawah. */
        #example2 td:nth-child(6),  /* Sumber Dana */
        #example2 th:nth-child(6),
        #example2 td:nth-child(7),  /* Bank Tujuan */
        #example2 th:nth-child(7),
        #example2 td:nth-child(9),  /* Penerima */
        #example2 th:nth-child(9),
        #example2 td:nth-child(10), /* Uraian */
        #example2 th:nth-child(10),
        #example2 td:nth-child(13), /* Keterangan */
        #example2 th:nth-child(13) {
            white-space: normal !important;
            overflow-wrap: anywhere;
            word-break: normal;
            vertical-align: top;
            line-height: 1.35;
            overflow: visible !important;
            text-overflow: clip !important;
        }
        /* Header navy */
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
                deferRender: true,
                ordering: false,
                autoWidth: false,
                scrollX: true,
                scrollY: '60vh',
                scroller: {
                    loadingIndicator: true,
                    displayBuffer: 9
                },
                pageLength: 50,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                ajax: "{{ route('bank-masuk.data') }}",
                columns: [
                    { data: 'checkbox',          width: '35px' },
                    { data: 'DT_RowIndex',       width: '45px',  orderable: false, searchable: false, title: 'No' },
                    { data: 'agenda_tahun',      width: '100px' },
                    { data: 'DT_RowIndex',       width: '72px',  orderable: false, searchable: false, title: 'No Bukti' },
                    { data: 'tanggal',           width: '110px' },
                    { data: 'sumber_dana',       width: '250px' },
                    { data: 'bank_tujuan',       width: '180px' },
                    { data: 'kategori_kriteria', width: '170px' },
                    { data: 'penerima',          width: '180px' },
                    { data: 'uraian',            width: '560px' },
                    { data: 'jenis_pembayaran',  width: '100px' },
                    { data: 'debet',             width: '130px' },
                    { data: 'keterangan',        width: '240px' },
                    { data: 'aksi',              width: '70px', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endpush
@include('cash_bank.modal.edit')
