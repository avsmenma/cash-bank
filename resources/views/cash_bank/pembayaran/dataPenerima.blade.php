@push('styles')
    <style>
        #example {
            table-layout: auto !important;
            width: 100% !important;
        }

        #example th,
        #example td {
            white-space: nowrap;
            /* biar kolom melebar */
            vertical-align: middle;
        }
    </style>
@endpush

<div class="row">
    <div class="col-12 table-responsive">
        <table id="example" class="table table-bordered table-hover">
            <thead class="bg-navy">
                <tr id="employee_ids">
                    <th><input type="checkbox" id="select_all_ids"></th>
                    <th>No</th>
                    <th>Penerimaan</th>
                    <th>No Rekening Penerima</th>
                    <th>Pembeli</th>
                    <th>Tanggal</th>
                    <th>Kontrak</th>
                    <th>Volume (Kg)</th>
                    <th>Harga (Rp)</th>
                    <th>Nilai</th>
                    <th>PPN</th>
                    <th>Pot PPN</th>
                    <th>Nilai Inc. PPN</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


@push('scripts')

    <script>
        var table = null;
        var tableReady = false;
        $(document).ready(function () {
            // Format angka Indonesia: 250000 -> 250.000 (tanpa desimal)
            function fmtAngka(val) {
                let num = parseFloat(val);
                if (isNaN(num)) return '0';
                return Math.round(num).toLocaleString('id-ID');
            }

            $('#example').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                autoWidth: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                ajax: {
                    url: "{{ route('penerima.data') }}",
                    data: function (d) {
                        d.tahun = $('#filterTahunRealisasi').val();
                        d.bulan = $('#filterBulanRealisasi').val();
                        d.kategori = $('#filterKategoriRealisasi').val();
                    }
                },
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false },
                    {
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        title: 'No', className: 'text-center',
                    },
                    // {data: 'id_ka'},
                    { data: 'kategori_kriteria', className: 'text-center', },
                    { data: 'no_reg', className: 'text-center', },
                    { data: 'pembeli', className: 'text-center', },
                    { data: 'tanggal', className: 'text-center', },
                    { data: 'kontrak', className: 'text-center', },
                    { data: 'volume', className: 'text-right', render: function (data) { return fmtAngka(data); } },
                    { data: 'harga', className: 'text-right', render: function (data) { return fmtAngka(data); } },
                    { data: 'nilai', className: 'text-right', render: function (data) { return fmtAngka(data); } },
                    { data: 'ppn', className: 'text-right', render: function (data) { return fmtAngka(data); } },
                    { data: 'potppn', className: 'text-right', render: function (data) { return fmtAngka(data); } },
                    { data: 'nilai_inc_ppn', className: 'text-right', render: function (data) { return fmtAngka(data); } },
                    { data: 'aksi', orderable: false, searchable: false }
                ],
                drawCallback: function (settings) {
                    drawTotalPerKategori.call(this, settings);
                }
            });
            setTimeout(() => {
                table.ajax.reload();
            }, 300);
            $('#filterTahunRealisasi, #filterBulanRealisasi, #filterKategoriRealisasi')
                .on('change select2:select select2:clear', function () {

                    if (!table) {
                        console.error('TABLE BELUM ADA');
                        return;
                    }

                    console.log('FILTER JALAN, reload datatable');
                    table.ajax.reload();
                });
            tableReady = true;
            console.log('TABLE SIAP');
        });
        function drawTotalPerKategori(settings) {

            let api = this.api();
            let rows = api.rows({ page: 'current' }).nodes();
            let data = api.rows({ page: 'current' }).data();

            let lastKategori = null;
            let total = {
                volume: 0, nilai: 0, ppn: 0, potppn: 0, inc: 0
            };

            data.each(function (row, i) {

                if (lastKategori !== null && row.kategori_kriteria !== lastKategori) {
                    insertTotalRow(rows, i - 1, lastKategori, total);
                    resetTotal(total);
                }

                total.volume += Number(row.volume || 0);
                total.nilai += Number(row.nilai || 0);
                total.ppn += Number(row.ppn || 0);
                total.potppn += Number(row.potppn || 0);
                total.inc += Number(row.nilai_inc_ppn || 0);

                lastKategori = row.kategori_kriteria;
            });

            if (lastKategori !== null) {
                insertTotalRow(rows, rows.length - 1, lastKategori, total);
            }
        }

        function insertTotalRow(rows, index, kategori, total) {
            let hargaRata = total.volume > 0 ? total.nilai / total.volume : 0;

            $(rows).eq(index).after(`
                                <tr class="table-warning font-weight-bold text-right">
                            <td colspan="7" class="text-left">TOTAL ${kategori}</td>
                                    <td>${Math.round(total.volume).toLocaleString('id-ID')}</td>
                                    <td>${Math.round(hargaRata).toLocaleString('id-ID')}</td>
                                    <td>${Math.round(total.nilai).toLocaleString('id-ID')}</td>
                                    <td>${Math.round(total.ppn).toLocaleString('id-ID')}</td>
                                    <td>${Math.round(total.potppn).toLocaleString('id-ID')}</td>
                                    <td>${Math.round(total.inc).toLocaleString('id-ID')}</td>
                                    <td></td>
                                </tr>
                            `);
        }

        function resetTotal(total) {
            Object.keys(total).forEach(k => total[k] = 0);
        }
    </script>
@endpush
@include('cash_bank.modal.modalPenerima.editPenerima')