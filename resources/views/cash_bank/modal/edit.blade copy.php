<div class="container-fluid">
    <div class="modal fade" id="edit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Bank Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEdit" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Terjadi kesalahan!</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4">
                                <label>Agenda Tahun</label>
                                <input type="text" name="agenda_tahun" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>Bank Tujuan</label>
                                <select name="id_bank_tujuan" class="form-select">
                                @foreach($bankTujuan as $bt)
                                    <option value="{{ $bt->id_bank_tujuan }}">
                                        {{ $bt->nama_tujuan }}
                                    </option>
                                @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label>Sumber Dana</label>
                            <select name="id_sumber_dana" class="form-select">
                                @foreach($sumberDana as $sd)
                                 <option value="{{ $sd->id_sumber_dana }}">
                                        {{ $sd->nama_sumber_dana }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mt-3">
                            <label>Kategori</label>
                            <select name="id_kategori_kriteria" class="form-select">
                                @foreach($kategoriKriteria as $k)
                                     <option value="{{ $k->id_kategori_kriteria }}">
                                        {{ $k->nama_kriteria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-3">
                            <label>Penerima</label>
                            <input type="text" name="penerima" class="form-control">
                        </div>
                       

                        <div class="row mt-3">
                            <!-- <div class="col-md-4">
                                <label>Nilai Rupiah</label>
                                <input type="number" name="nilai_rupiah"  class="form-control" disabled>
                            </div> -->
                            <div class="col-md-6">
                                <label>Debet</label>
                                <input type="number" name="debet"  class="form-control" disabled>
                            </div>
                            <div class="col-md-6">
                                <label>Jenis Pembayaran</label>
                                <select name="id_jenis_pembayaran" class="form-select">
                                @foreach($jenisPembayaran as $jp)
                                    <option value="{{ $jp->id_jenis_pembayaran }}">
                                        {{ $jp->nama_jenis_pembayaran }}
                                    </option>
                                @endforeach
                            </select>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-control"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn bg-primary text-white w-25 shadow-sm">Update</button>
                        </div>
                       
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>


