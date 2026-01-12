
<div class="modal fade" id="ModalCreatePenerima">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Input Penerima</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
            <form action="{{route('penerima.store')}}" method="POST" enctype="multipart/form-data" id="importExcel">
                @csrf    
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 d-flex">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kategori</label>
                                        <select name="id_kategori_kriteria" id="create_kategori" class="select2 form-control">
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
                                        <label for="create_kontrak">Kontrak</label>
                                        <input type="text" id="create_kontrak" name="kontrak" class="form-control" placeholder="1981/HO-PALMCO/CPO-L/N-IV/XII/2024">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 d-flex">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create_pembeli">Pembeli</label>
                                        <input type="text" id="create_pembeli" name="pembeli" class="form-control" placeholder="Pembeli">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal</label>
                                        <div class="input-group date" id="create_reservationdate" data-target-input="nearest">
                                            <input type="text" name="tanggal"
                                                class="form-control datetimepicker-input"
                                                data-target="#create_reservationdate">
                                            <div class="input-group-append"
                                                data-target="#create_reservationdate"
                                                data-toggle="datetimepicker">
                                                <div class="input-group-text">
                                                    <i class="far fa-calendar-alt"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 d-flex">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create_no_reg">No Rekening</label>
                                        <input type="text" id="create_no_reg" name="no_reg" class="form-control" placeholder="1050088001874">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create_volume">Volume</label>
                                        <input type="number" id="create_volume" name="volume" class="form-control" placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 d-flex">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create_harga">Harga</label>
                                        <input type="number" id="create_harga" name="harga" class="form-control" placeholder="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create_nilai">Nilai</label>
                                        <input type="number" id="create_nilai" name="nilai" class="form-control" placeholder="0" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 d-flex">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create_ppn">PPN</label>
                                        <input type="number" id="create_ppn" name="ppn" class="form-control" placeholder="0" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="create_potppn">Pot PPN</label>
                                        <input type="number" id="create_potppn" name="potppn" class="form-control" placeholder="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="create_nilai_inc_ppn">Nilai Inc PPN</label>
                                    <input type="number" id="create_nilai_inc_ppn" name="nilai_inc_ppn" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer justify-content-end">
                <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
                 
                </div>
              </form>
            </div>
          </div>
        </div>
</div>

