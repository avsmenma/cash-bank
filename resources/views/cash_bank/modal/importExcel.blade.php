{{-- ========================= MODAL UPLOAD ========================= --}}
<div class="modal fade" id="ModalImportFileExcel">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header" style="background:#0d3b6e; color:#fff;">
        <h5 class="modal-title"><i class="fas fa-file-import mr-2"></i>Import File CSV – Bank Keluar</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-3" style="font-size:12.5px;">
          Pilih file <code>.csv</code>, <code>.xlsx</code>, atau <code>.xls</code> dengan kolom:<br>
          <strong>Agenda Tahun, Tanggal, Sumber Dana, Bank Tujuan, Kategori, Penerima, Uraian, Debet, Jenis Pembayaran</strong><br>
          <span class="text-info">Delimiter: titik koma (<code>;</code>)</span>
        </p>
        <div class="form-group">
          <div class="input-group">
            <div class="custom-file">
              <input type="file" class="form-control custom-file-input" id="inputImportExcelKeluar"
                     accept=".csv,.xlsx,.xls" required>
              <label class="custom-file-label" for="inputImportExcelKeluar">Pilih file...</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm" id="btnPreviewImportKeluar">
          <i class="fas fa-eye mr-1"></i>Preview Sebelum Import
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ========================= MODAL PREVIEW ========================= --}}
<div class="modal fade" id="ModalPreviewImportKeluar" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header" style="background:#0d3b6e; color:#fff;">
        <h5 class="modal-title"><i class="fas fa-list-alt mr-2"></i>Preview Data Import Bank Keluar</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body p-0">

        {{-- Info bar --}}
        <div id="previewInfoBarKeluar" class="d-flex align-items-center px-3 py-2"
             style="background:#f8f9fa; border-bottom:1px solid #dee2e6; gap:12px; flex-wrap:wrap;">
          <span class="badge badge-primary px-3 py-2" id="badgeTotalKeluar">Total: 0 baris</span>
          <span class="badge badge-danger  px-3 py-2" id="badgeDuplikKeluar" style="display:none;">🔴 0 duplikat (di-skip)</span>
          <span class="badge badge-warning px-3 py-2" id="badgeWarnKeluar"  style="display:none;">⚠ 0 referensi tidak cocok</span>
          <small class="text-muted ml-auto"><span style="background:#fff3cd;padding:2px 8px;border-radius:3px;">Kuning</span> = referensi tidak cocok &nbsp;<span style="background:#fde8e8;padding:2px 8px;border-radius:3px;">Merah</span> = duplikat (di-skip)</small>
        </div>

        {{-- Tabel scroll --}}
        <div style="max-height:420px; overflow-y:auto; overflow-x:auto;">
          <table class="table table-bordered table-sm mb-0" style="font-size:11px; min-width:1400px;">
            <thead>
              <tr style="background:#0d3b6e; color:#fff; position:sticky; top:0; z-index:1;">
                <th style="width:40px;">No</th>
                <th>Agenda</th>
                <th>Tanggal</th>
                <th>Sumber Dana</th>
                <th>Bank Tujuan</th>
                <th>Kategori/Kriteria</th>
                <th>Sub Kriteria</th>
                <th>Item Sub Kriteria</th>
                <th>Jenis Pembayaran</th>
                <th>Penerima</th>
                <th>Uraian</th>
                <th class="text-right">Kredit</th>
              </tr>
            </thead>
            <tbody id="previewTableBodyKeluar">
              <tr><td colspan="12" class="text-center text-muted py-4">Memuat data...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Batal
        </button>
        <button type="button" class="btn btn-success btn-sm" id="btnKonfirmasiImportKeluar" disabled>
          <i class="fas fa-check mr-1"></i>Konfirmasi Import (<span id="confirmCountKeluar">0</span> baris)
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    // Update label file input
    $(document).on('change', '#inputImportExcelKeluar', function () {
        var fileName = this.files[0] ? this.files[0].name : 'Pilih file...';
        $(this).next('.custom-file-label').text(fileName);
    });

    // Tombol Preview
    $(document).on('click', '#btnPreviewImportKeluar', function () {
        var fileInput = document.getElementById('inputImportExcelKeluar');
        if (!fileInput || !fileInput.files.length) {
            alert('Pilih file terlebih dahulu!');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memuat...');

        var formData = new FormData();
        formData.append('fileExcel', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        // Reset preview
        $('#previewTableBodyKeluar').html('<tr><td colspan="9" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Memproses data...</td></tr>');
        $('#badgeWarnKeluar').hide();
        $('#btnKonfirmasiImportKeluar').prop('disabled', true);

        // Pindah ke modal preview
        $('#ModalImportFileExcel').modal('hide');
        setTimeout(function () { $('#ModalPreviewImportKeluar').modal('show'); }, 400);

        $.ajax({
            url: '{{ route("bank-keluar.previewImport") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                btn.prop('disabled', false).html('<i class="fas fa-eye mr-1"></i>Preview Sebelum Import');

                $('#badgeTotalKeluar').text('Total: ' + res.total + ' baris');
                var newCount = res.new !== undefined ? res.new : res.total;
                $('#confirmCountKeluar').text(newCount);
                $('#btnKonfirmasiImportKeluar').prop('disabled', newCount === 0);

                if (res.duplicates > 0) {
                    $('#badgeDuplikKeluar').text('🔴 ' + res.duplicates + ' duplikat (di-skip)').show();
                } else { $('#badgeDuplikKeluar').hide(); }
                if (res.warnings > 0) {
                    $('#badgeWarnKeluar').text('⚠ ' + res.warnings + ' referensi tidak cocok').show();
                } else { $('#badgeWarnKeluar').hide(); }

                var html = '';
                if (res.rows.length === 0) {
                    html = '<tr><td colspan="12" class="text-center text-muted py-4">Tidak ada data valid.</td></tr>';
                } else {
                    res.rows.forEach(function (row) {
                        var bg = row.duplicate ? 'background:#fde8e8;' : (row.warning ? 'background:#fff8dc;' : '');
                        var dupBadge = row.duplicate ? ' <span style="background:#e74c3c;color:#fff;font-size:10px;padding:1px 5px;border-radius:3px;">DUPLIKAT</span>' : '';
                        var warnIcon = function(flag) { return flag ? ' <span title="Tidak ditemukan di referensi" style="color:#e67e22;">⚠</span>' : ''; };
                    html += '<tr style="' + bg + (row.duplicate ? 'text-decoration:line-through;opacity:0.7;' : '') + '">'
                            + '<td class="text-center">' + row.no + '</td>'
                            + '<td>' + (row.agenda || '-') + '</td>'
                            + '<td>' + (row.tanggal || '-') + '</td>'
                            + '<td>' + row.sumber + warnIcon(row.warn_sumber) + dupBadge + '</td>'
                            + '<td>' + row.bank + warnIcon(row.warn_bank) + '</td>'
                            + '<td>' + row.kategori + warnIcon(row.warn_kategori) + '</td>'
                            + '<td>' + (row.sub_kriteria || '-') + warnIcon(row.warn_sub_krit) + '</td>'
                            + '<td>' + (row.item_sub_krit || '-') + warnIcon(row.warn_item_sub) + '</td>'
                            + '<td>' + row.jenis + warnIcon(row.warn_jenis) + '</td>'
                            + '<td>' + row.penerima + '</td>'
                            + '<td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + row.uraian + '">' + row.uraian + '</td>'
                            + '<td class="text-right">' + row.kredit + '</td>'
                            + '</tr>';
                    });
                }
                $('#previewTableBodyKeluar').html(html);
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-eye mr-1"></i>Preview Sebelum Import');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                $('#previewTableBodyKeluar').html('<tr><td colspan="9" class="text-center text-danger py-4">❌ ' + msg + '</td></tr>');
            }
        });
    });

    // Tombol Konfirmasi Import
    $(document).on('click', '#btnKonfirmasiImportKeluar', function () {
        if (!confirm('Yakin ingin mengimport data ini ke database?')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: '{{ route("bank-keluar.confirmImport") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                $('#ModalPreviewImportKeluar').modal('hide');
                alert('✅ ' + res.success);
                // Reload DataTable
                if ($.fn.DataTable.isDataTable('#example3')) {
                    $('#example3').DataTable().ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i>Konfirmasi Import');
                var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Import gagal.';
                alert('❌ ' + msg);
            }
        });
    });

    // Reset modal upload saat dibuka
    $('#ModalImportFileExcel').on('show.bs.modal', function () {
        $('#inputImportExcelKeluar').val('');
        $(this).find('.custom-file-label').text('Pilih file...');
    });

    if (typeof bsCustomFileInput !== 'undefined') bsCustomFileInput.init();
});
</script>
@endpush