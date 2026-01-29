@push('styles')
    <style>
        #example3 {
            table-layout: auto !important;
            width: 100% !important;
        }

        #example3 th,
        #example3 td {
            white-space: nowrap;
            /* biar kolom melebar */
            vertical-align: middle;
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
            <th>Sub Kriteria </th>
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
                ajax: "{{ route('bank-keluar.data') }}",
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
                    { data: 'sub_kriteria' },
                    { data: 'item_sub_kriteria' },

                    { data: 'penerima' },
                    { data: 'uraian', width: '50px' },
                    { data: 'jenis_pembayaran' },
                    {
                        data: 'kredit',
                        className: 'text-right',
                        render: function (data) {
                            if (!data || data == 0) {
                                return '0';
                            }

                            return Number(data).toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    { data: 'keterangan' },
                    { data: 'aksi', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endpush
@include('cash_bank.modal.editKeluar')