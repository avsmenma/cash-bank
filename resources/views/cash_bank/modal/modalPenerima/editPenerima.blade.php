<div class="modal fade" id="editPenerima" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="formEditPenerima" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <input type="hidden" id="id_penerima">

        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title">Edit Penerima</h5>
            <button type="button" class="btn-close" data-dismiss="modal"></button>
        </div>

        <div class="modal-body">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kategori</label>
                                <select class="select2" id="edit_kategori" name="id_kategori_kriteria">
                                    <option value="" disable>-- pilih Kategori --</option>
                                    @foreach($kategoriKriteria as $kk)
                                        <option value="{{ $kk->id_kategori_kriteria }}">
                                            {{ $kk->nama_kriteria }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_kontrak">Kontrak</label>
                                <input type="text" id="edit_kontrak" name="kontrak" class="form-control" placeholder="1981/HO-PALMCO/CPO-L/N-IV/XII/2024">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_pembeli">Pembeli</label>
                                <input type="text" id="edit_pembeli" name="pembeli" class="form-control" placeholder="Pembeli">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-group">
                                  <label>Tanggal</label>
                                    <div class="input-group date" id="edit_reservationdate" data-target-input="nearest">
                                        <input type="text" class="form-control datetimepicker-input" data-target="#edit_reservationdate" name="tanggal" placeholder="YYYY/MM/DD"/>
                                        <div class="input-group-append" data-target="#edit_reservationdate" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_no_reg">No Rekening</label>
                                <input type="text" id="edit_no_reg" name="no_reg" class="form-control" placeholder="1050088001874">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_volume">Volume</label>
                                <input type="number" id="edit_volume" name="volume" class="form-control" placeholder="0">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_harga">Harga</label>
                                <input type="number" id="edit_harga" name="harga" class="form-control" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_nilai">Nilai</label>
                                <input type="number" id="edit_nilai" name="nilai" class="form-control" placeholder="0" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_potppn">Pot PPN</label>
                                <input type="number" id="edit_potppn" name="potppn" class="form-control" placeholder="0">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_ppn">PPN</label>
                                <input type="number" id="edit_ppn" name="ppn" class="form-control" placeholder="0" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_nilai_inc_ppn">Nilai Inc PPN</label>
                                <input type="number" id="edit_nilai_inc_ppn" name="nilai_inc_ppn" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end">
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>
  </div>
</div>



