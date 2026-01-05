<div class="d-flex justify-content-between mb-3">
    <h3 class="card-title">Daftar Penerima PTPN VI Regional 5</h3>
    <button class="btn btn-success" data-toggle="modal" data-target="#modal-lg">
        <i class="bi bi-database-fill-add"></i> Tambah
    </button>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-lg">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Email address</label>
                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="penerima">Penerima</label>
                                <input type="email" class="form-control" id="penerima" placeholder="Enter penerima">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Tanggal Diterima</label>
                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="penerima">NO. REKG. PENERIMA</label>
                                <input type="email" class="form-control" id="penerima" placeholder="Enter penerima">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Volume (Kg)</label>
                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="penerima">Harga (Rp)</label>
                                <input type="email" class="form-control" id="penerima" placeholder="Enter penerima">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail1">NILAI</label>
                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="penerima">PPN</label>
                                <input type="email" class="form-control" id="penerima" placeholder="Enter penerima">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Pot PPh</label>
                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                    <label>Jenis Penerimaan</label>
                                    <select class="form-control select2 select2-danger" data-dropdown-css-class="select2-danger" style="width: 100%;">
                                        <option selected="selected">Alabama</option>
                                        <option>Alaska</option>
                                        <option>California</option>
                                        <option>Delaware</option>
                                        <option>Tennessee</option>
                                        <option>Texas</option>
                                        <option>Washington</option>
                                    </select>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Tabel DataTable -->
<div class="card-body">
    <table id="example1" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Rendering engine</th>
                <th>Browser</th>
                <th>Platform(s)</th>
                <th>Engine version</th>
                <th>CSS grade</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gecko</td>
                <td>Firefox 1.0</td>
                <td>Win 98+ / OSX.2+</td>
                <td>1.7</td>
                <td>A</td>
            </tr>
            <tr>
                <td>Trident</td>
                <td>Internet Explorer 4.0</td>
                <td>Win 95+</td>
                <td>4</td>
                <td>X</td>
            </tr>
            <tr>
                <td>Trident</td>
                <td>Internet Explorer 5.0</td>
                <td>Win 95+</td>
                <td>5</td>
                <td>C</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th>Rendering engine</th>
                <th>Browser</th>
                <th>Platform(s)</th>
                <th>Engine version</th>
                <th>CSS grade</th>
            </tr>
        </tfoot>
    </table>
</div>

@push('scripts')
<script>
$(function () {
    // Inisialisasi DataTable
    $("#example1").DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "buttons": ["excel", "pdf", "print"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    
    // Inisialisasi Select2 di dalam Modal
    // PENTING: dropdownParent harus diset ke modal agar dropdown tidak terpotong
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Pilih salah satu',
        allowClear: true,
        dropdownParent: $('#modal-lg') // INI YANG PENTING!
    });
    
    // Alternatif: Inisialisasi ulang saat modal dibuka (lebih aman)
    $('#modal-lg').on('shown.bs.modal', function () {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih salah satu',
            allowClear: true,
            dropdownParent: $('#modal-lg')
        });
    });
});
</script>
@endpush