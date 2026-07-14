{{-- ========================= MODAL UPLOAD ========================= --}}
<div class="modal fade" id="ModalImportFileExcelMasuk">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header" style="background:#0d3b6e; color:#fff;">
        <h5 class="modal-title"><i class="fas fa-file-import mr-2"></i>Import File Excel / CSV</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-3" style="font-size:12.5px;">
          Pilih file <code>.xlsx</code>, <code>.xls</code>, atau <code>.csv</code> dengan kolom:<br>
          <strong>Agenda Tahun, Tanggal, Sumber Dana, Bank Tujuan, Kategori, Penerima, Uraian, Debet, Jenis Pembayaran</strong>
        </p>
        <div class="form-group">
          <div class="input-group">
            <div class="custom-file">
              <input type="file" name="fileExcel" class="form-control custom-file-input" id="inputImportExcelMasuk"
                     accept=".xlsx,.xls,.csv" required>
              <label class="custom-file-label" for="inputImportExcelMasuk">Pilih file...</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm" id="btnPreviewImportMasuk">
          <i class="fas fa-eye mr-1"></i>Preview Sebelum Import
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ========================= MODAL PREVIEW ========================= --}}
<div class="modal fade" id="ModalPreviewImportMasuk" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header" style="background:#0d3b6e; color:#fff;">
        <h5 class="modal-title"><i class="fas fa-list-alt mr-2"></i>Preview Data Import Bank Masuk</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body p-0">

        {{-- Info bar --}}
        <div id="previewInfoBar" class="d-flex align-items-center px-3 py-2" style="background:#f8f9fa; border-bottom:1px solid #dee2e6; gap:12px; flex-wrap:wrap;">
          <span class="badge badge-primary px-3 py-2" id="badgeTotal">Total: 0 baris</span>
          <span class="badge badge-warning px-3 py-2" id="badgeWarn"  style="display:none;">⚠ 0 referensi tidak cocok</span>
          <small class="text-muted ml-auto"><span style="background:#fff3cd;padding:2px 8px;border-radius:3px;">Kuning</span> = referensi tidak cocok di master data</small>
        </div>

        {{-- Tabel scroll --}}
        <div style="max-height:420px; overflow-y:auto; overflow-x:auto;">
          <table class="table table-bordered table-sm mb-0 csv-preview-table" style="font-size:11.5px; min-width:1000px;">
            <thead>
              <tr style="background:#0d3b6e; color:#fff; position:sticky; top:0; z-index:1;">
                <th style="width:40px;">No</th>
                <th>Agenda</th>
                <th>Tanggal</th>
                <th>No Bukti</th>
                <th>Sumber Dana</th>
                <th>Bank Tujuan</th>
                <th>Kategori</th>
                <th>Jenis Pembayaran</th>
                <th>Penerima</th>
                <th>Uraian</th>
                <th class="text-right">Debet</th>
              </tr>
            </thead>
            <tbody id="previewTableBody">
              <tr><td colspan="10" class="text-center text-muted py-4">Memuat data...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Batal
        </button>
        <button type="button" class="btn btn-success btn-sm" id="btnKonfirmasiImportMasuk" disabled>
          <i class="fas fa-check mr-1"></i>Konfirmasi Import (<span id="confirmCount">0</span> baris)
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<style>
    /* Judul kolom preview harus putih — CSS global halaman menimpanya jadi gelap */
    .csv-preview-table thead th {
        color: #fff !important;
    }
</style>
<script>
$(document).ready(function () {
    // Update label file input
    $(document).on('change', '#inputImportExcelMasuk', function () {
        var fileName = this.files[0] ? this.files[0].name : 'Pilih file...';
        $(this).next('.custom-file-label').text(fileName);
    });

    // Tombol Preview
    $(document).on('click', '#btnPreviewImportMasuk', function () {
        var fileInput = document.getElementById('inputImportExcelMasuk');
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
        $('#previewTableBody').html('<tr><td colspan="10" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Memproses data...</td></tr>');
        $('#badgeWarn').hide();
        $('#btnKonfirmasiImportMasuk').prop('disabled', true);

        // Tutup modal upload, buka preview
        $('#ModalImportFileExcelMasuk').modal('hide');
        setTimeout(function () { $('#ModalPreviewImportMasuk').modal('show'); }, 400);

        $.ajax({
            url: '{{ route("bank-masuk.previewImport") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                btn.prop('disabled', false).html('<i class="fas fa-eye mr-1"></i>Preview Sebelum Import');

                 $('#badgeTotal').text('Total: ' + res.total + ' baris');
                $('#confirmCount').text(res.total);
                $('#btnKonfirmasiImportMasuk').prop('disabled', res.total === 0);

                if (res.warnings > 0) {
                    $('#badgeWarn').text('⚠ ' + res.warnings + ' referensi tidak cocok').show();
                } else { $('#badgeWarn').hide(); }

                var html = '';
                if (res.rows.length === 0) {
                    html = '<tr><td colspan="10" class="text-center text-muted py-4">Tidak ada data.</td></tr>';
                } else {
                    res.rows.forEach(function (row) {
                        var bg = row.warning ? 'background:#fff8dc;' : '';
                        var warnIcon = function(flag) { return flag ? ' <span title="Tidak ditemukan di referensi" style="color:#e67e22;">⚠</span>' : ''; };
                        html += '<tr style="' + bg + '">'
                            + '<td class="text-center">' + row.no + '</td>'
                            + '<td>' + (row.agenda || '-') + '</td>'
                            + '<td>' + (row.tanggal || '-') + '</td>'
                            + '<td>' + (row.bukti || '-') + '</td>'
                            + '<td>' + row.sumber + warnIcon(row.warn_sumber) + '</td>'
                            + '<td>' + row.bank + warnIcon(row.warn_bank) + '</td>'
                            + '<td>' + row.kategori + warnIcon(row.warn_kategori) + '</td>'
                            + '<td>' + row.jenis + warnIcon(row.warn_jenis) + '</td>'
                            + '<td>' + row.penerima + '</td>'
                            + '<td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + row.uraian + '">' + row.uraian + '</td>'
                            + '<td class="text-right">' + row.debet + '</td>'
                            + '</tr>';
                    });
                }
                $('#previewTableBody').html(html);
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-eye mr-1"></i>Preview Sebelum Import');
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                $('#previewTableBody').html('<tr><td colspan="10" class="text-center text-danger py-4">❌ ' + msg + '</td></tr>');
            }
        });
    });

    // Tombol Konfirmasi Import
    $(document).on('click', '#btnKonfirmasiImportMasuk', function () {
        if (!confirm('Yakin ingin mengimport data ini ke database?')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: '{{ route("bank-masuk.confirmImport") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                $('#ModalPreviewImportMasuk').modal('hide');
                alert('✅ ' + res.success);
                // Reload DataTable
                if ($.fn.DataTable.isDataTable('#example2')) {
                    window.cbTableReload('#example2');
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
    $('#ModalImportFileExcelMasuk').on('show.bs.modal', function () {
        $('#inputImportExcelMasuk').val('');
        $(this).find('.custom-file-label').text('Pilih file...');
    });

    // init bsCustomFileInput jika ada
    if (typeof bsCustomFileInput !== 'undefined') bsCustomFileInput.init();
});
</script>
@endpush