<div class="modal fade" id="ModalCreateMasuk" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form
        id="formCreateMasuk"
        action="{{ route('bank-masuk.store') }}"
        method="POST">
        @csrf
      <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Tambah Bank Masuk</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            
                                <label class="form-label" for="agenda_tahun">Agenda</label>
                                    <input type="text" name="agenda_tahun" id="agenda_tahun" class="form-control" placeholder="agenda_tahun">

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
                                <label class="form-label" for="id_bank_tujuan">Bank Tujuan</label>
                                <select name="id_bank_tujuan" id="id_bank_tujuan" class="select2">
                                    <option disabled selected>Pilih Bank Tujuan</option>
                                    @foreach($bankTujuan as $bt)
                                        <option value="{{ $bt->id_bank_tujuan }}">{{ $bt->nama_tujuan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="id_sumber_dana">Sumber Dana</label>
                                <select name="id_sumber_dana" id="id_sumber_dana" class="select2">
                                    <option disabled selected>Pilih Sumber Dana</option>
                                    @foreach($sumberDana as $sd)
                                        <option value="{{ $sd->id_sumber_dana }}">{{ $sd->nama_sumber_dana }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="kategori">Kriteria CF</label>
                                <select name="id_kategori_kriteria" id="kategori" class="select2">
                                    <option disabled selected>Pilih Kriteria CF</option>
                                    @foreach($kategoriKriteria as $kk)
                                        <option value="{{ $kk->id_kategori_kriteria }}">{{ $kk->nama_kriteria }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bank Tujuan</label>
                                <select class="select2"  id="id_jenis_pembayaran">
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
                                
                                <textarea rows="4"name="uraian" id="uraian" class="form-control" placeholder="Uraian"></textarea>
                            
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="penerima">Penerima</label>
                                <input type="text" name="penerima" id="penerima" class="form-control" placeholder="Penerima">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="debet">Debet <span class="text-danger">*</span></label>
                                <input type="number" name="debet" id="debet" class="form-control rupiah-input" placeholder="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="keterangan" class="col-sm-2 col-form-label">Keterangan</label>
                            <div class="col-sm-12">
                                <textarea class="form-control" name="keterangan"id="keterangan"  placeholder="keterangan"></textarea>
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




