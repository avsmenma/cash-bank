<div class="modal fade" id="editKeluar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="formEdit" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <input type="hidden" id="id_bank_keluar">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Bank Masuk</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
        </div>

        <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="agenda_tahun">No Agenda</label>
                                <input type="text" id="agenda_tahun" name="agenda_tahun" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <div class="input-group date" id="reservationdate" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" name="tanggal" data-target="#reservationdate"/>
                                    <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>  
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bank Tujuan</label>
                                <select class="select2"  name="id_bank_tujuan">
                                    @foreach($bankTujuan as $bt)
                                        <option value="{{ $bt->id_bank_tujuan }}">
                                            {{ $bt->nama_tujuan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sumber Dana</label>
                                <select class="select2"  name="id_sumber_dana">
                                    @foreach($sumberDana as $sd)
                                    <option value="{{ $sd->id_sumber_dana }}">
                                            {{ $sd->nama_sumber_dana }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kategori</label>
                                <select class="select2"  name="id_kategori_kriteria">
                                     @foreach($kategoriKriteria as $k)
                                        <option value="{{ $k->id_kategori_kriteria }}">
                                            {{ $k->nama_kriteria }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sub Kategori</label>
                                <select id="sub_kriteria" name="id_sub_kriteria" class="select2">
                                    <option value="">-- Pilih Sub Kriteria --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Item Sub Kategori</label>
                                <select id="item_sub_kriteria" name="id_item_sub_kriteria" class="select2">
                                    <option value="">-- Pilih Item Sub Kriteria --</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bank Tujuan</label>
                                <select class="select2"  name="id_jenis_pembayaran">
                                    @foreach($jenisPembayaran as $sd)
                                    <option value="{{ $sd->id_jenis_pembayaran }}">
                                            {{ $sd->nama_jenis_pembayaran }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="uraian" class="col-sm-2 col-form-label">Uraian</label>
                            <div class="col-sm-12">
                                <textarea class="form-control" id="uraian" name="uraian" placeholder="uraian"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="penerima">Penerima</label>
                                <input type="text" class="form-control" id="penerima" name="penerima" placeholder="penerima">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kredit">Kredit</label>
                                <input type="number" id="kredit" class="form-control" value="2300" step="1" name="kredit">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="keterangan" class="col-sm-2 col-form-label">Keterangan</label>
                            <div class="col-sm-12">
                                <textarea class="form-control" id="keterangan" name="keterangan" placeholder="keterangan"></textarea>
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




