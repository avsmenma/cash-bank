@push('styles')
    <style>
        #example2 {
            table-layout: auto !important;
            width: 100% !important;
        }

        #example2 th,
        #example2 td {
            white-space: nowrap;
            /* biar kolom melebar */
            vertical-align: middle;
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