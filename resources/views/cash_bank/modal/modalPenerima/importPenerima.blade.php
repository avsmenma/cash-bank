<div class="modal fade" id="ModalImportPenerima">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import Data Dropping/Realisasi</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="import_kategori" id="import_kategori" class="select2 form-control">
                            <option value="" disable>-- pilih Kategori --</option>
                            @foreach($kategoriKriteria as $kk)
                                <option value="{{ $kk->id_kategori_kriteria }}">
                                    {{ $kk->nama_kriteria }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label>Paste Data dari Excel</label>
                        <p class="text-muted small mb-1">
                            Format kolom (tab-separated): <strong>Kontrak, Pembeli, Tanggal, No Rekening, Volume, Harga,
                                Nilai, PPN, Pot PPN, Nilai Inc PPN</strong>
                        </p>
                        <textarea id="import_data_text" class="form-control" rows="15"
                            placeholder="Paste data dari Excel di sini...&#10;&#10;Contoh:&#10;2404/HO-PALMCO/CPO-L/N-IV/XII/2025&#9;PBI&#9;02 Jan 2026&#9;1050088001874&#9;250.000&#9;13.950&#9;3.487.500.000&#9;0&#9;&#9;3.487.500.000"
                            style="font-family: monospace; font-size: 12px;"></textarea>
                    </div>

                    {{-- Preview area --}}
                    <div id="import_preview_area" class="mt-3" style="display:none;">
                        <h5>Preview Data <span id="import_row_count" class="badge badge-info">0</span> baris</h5>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-bordered table-sm table-striped" id="import_preview_table">
                                <thead class="bg-navy">
                                    <tr>
                                        <th>No</th>
                                        <th>Kontrak</th>
                                        <th>Pembeli</th>
                                        <th>Tanggal</th>
                                        <th>No Rekening</th>
                                        <th>Volume</th>
                                        <th>Harga</th>
                                        <th>Nilai</th>
                                        <th>PPN</th>
                                        <th>Pot PPN</th>
                                        <th>Nilai Inc PPN</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-info" id="btnPreviewImport">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <button type="button" class="btn btn-primary" id="btnSubmitImport">
                    <i class="fas fa-upload"></i> Import Data
                </button>
            </div>
        </div>
    </div>
</div>