<div class="modal fade" id="editKeluar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Edit <span class="text-warning">Bank Keluar</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formEditKeluar" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="modal-body">
                    {{-- AGENDA --}}
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

                    {{-- SUMBER DANA --}}
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

                    {{-- KATEGORI --}}
                    <div class="mt-3">
                        <label>Kategori</label>
                        <select name="id_kategori_kriteria" class="form-select" id="kategori">
                            @foreach($kategoriKriteria as $k)
                                <option value="{{ $k->id_kategori_kriteria }}">
                                    {{ $k->nama_kriteria }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- SUB KATEGORI --}}
                    <div class="mt-3">
                        <label>Sub Kategori</label>
                        <select name="id_sub_kriteria" class="form-select" id="sub_kriteria">
                            @foreach($subKriteria as $sk)
                                <option value="{{ $sk->id_sub_kriteria }}">
                                    {{ $sk->nama_sub_kriteria }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ITEM SUB --}}
                    <div class="mt-3">
                        <label>Item Sub Kriteria</label>
                        <select name="id_item_sub_kriteria" class="form-select" id="item_sub_kriteria">
                            @foreach($itemSubKriteria as $ik)
                                <option value="{{ $ik->id_item_sub_kriteria }}">
                                    {{ $ik->nama_item_sub_kriteria }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- PENERIMA --}}
                    <div class="mt-3">
                        <label>Penerima</label>
                        <input type="text" name="penerima" class="form-control">
                    </div>

                     <div class="mt-3">
                        <label>Uraian</label>
                        <textarea name="uraian" class="form-control"></textarea>
                    </div>

                    {{-- KETERANGAN --}}
                    <div class="mt-3">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control"></textarea>
                    </div>

                    {{-- NILAI --}}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label>Nilai Rupiah</label>
                            <input type="number" name="nilai_rupiah" class="form-control" id="nilai_rupiah" readonly disabled>
                        </div>

                        <div class="col-md-4">
                            <label>Kredit</label>
                            <input type="number" name="kredit" class="form-control" id="kredit" readonly disabled>
                        </div>

                        <div class="col-md-4">
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
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn bg-primary w-25 text-white">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>