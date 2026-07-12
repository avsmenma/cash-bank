{{-- ============ MODAL IMPORT MANDIRI BANK MASUK (TEMPLATE EXCEL) ============ --}}
<div class="modal fade" id="ModalImportTemplateMasuk" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header" style="background:#0d3b6e; color:#fff;">
        <h5 class="modal-title"><i class="fas fa-file-import mr-2"></i>Import Excel – Bank Masuk</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-3" style="font-size:12.5px;">
          Catat transaksi pada <strong>template Excel</strong> di bawah, lalu upload di sini.
          Tidak semua kolom wajib diisi — kolom yang kosong tetap ikut terimport dan
          tampil di tabel Bank Masuk (bisa dilengkapi kemudian lewat tombol edit).
        </p>
        <a href="{{ route('bank-masuk.templateImport') }}" class="btn btn-outline-success btn-sm btn-block mb-3">
          <i class="fas fa-download mr-1"></i>Download Template Excel
        </a>
        <div class="form-group mb-0">
          <div class="custom-file">
            <input type="file" class="form-control custom-file-input" id="inputImportTemplateMasuk"
                   accept=".xlsx,.xls,.csv">
            <label class="custom-file-label" for="inputImportTemplateMasuk">Pilih file...</label>
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm" id="btnPreviewTemplateMasuk">
          <i class="fas fa-eye mr-1"></i>Preview Sebelum Import
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ============ MODAL PREVIEW HASIL BACA TEMPLATE ============ --}}
<div class="modal fade" id="ModalPreviewTemplateMasuk" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header" style="background:#0d3b6e; color:#fff;">
        <h5 class="modal-title"><i class="fas fa-list-alt mr-2"></i>Preview Import Template – Bank Masuk</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body p-0">

        {{-- Info bar --}}
        <div class="d-flex align-items-center px-3 py-2"
             style="background:#f8f9fa; border-bottom:1px solid #dee2e6; gap:12px; flex-wrap:wrap;">
          <span class="badge badge-primary px-3 py-2" id="badgeTotalTemplateMasuk">Total: 0 baris</span>
          <span class="badge badge-warning px-3 py-2" id="badgeWarnTemplateMasuk" style="display:none;">⚠ 0 peringatan</span>
          <small class="text-muted ml-auto">
            <span style="background:#fff3cd;padding:2px 8px;border-radius:3px;">Kuning</span>
            = ada isian bermasalah (kolom itu akan dikosongkan) — belum ada data yang tersimpan
          </small>
        </div>

        {{-- Daftar peringatan --}}
        <div id="warnListTemplateMasuk" class="px-3 py-2" style="display:none; background:#fffbea;
             border-bottom:1px solid #f0e6c0; font-size:11.5px; max-height:110px; overflow-y:auto;"></div>

        {{-- Tabel preview --}}
        <div style="max-height:400px; overflow-y:auto; overflow-x:auto;">
          <table class="table table-bordered table-sm mb-0 tpl-preview-table" style="font-size:11px; min-width:1300px;">
            <thead>
              <tr style="background:#0d3b6e; color:#fff; position:sticky; top:0; z-index:1;">
                <th style="width:52px;">Baris</th>
                <th>No Agenda</th>
                <th>Tanggal</th>
                <th>Sumber Dana</th>
                <th>Bank Tujuan</th>
                <th>Kategori</th>
                <th>Jenis Pembayaran</th>
                <th>Dari / Penerima</th>
                <th>Uraian</th>
                <th class="text-right">Debet</th>
              </tr>
            </thead>
            <tbody id="previewBodyTemplateMasuk">
              <tr><td colspan="10" class="text-center text-muted py-4">Memuat data...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Batal
        </button>
        <button type="button" class="btn btn-success btn-sm" id="btnConfirmTemplateMasuk" disabled>
          <i class="fas fa-check mr-1"></i>Konfirmasi Import (<span id="confirmCountTemplateMasuk">0</span> baris)
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<style>
    /* Judul kolom preview harus putih — CSS global halaman menimpanya jadi gelap */
    .tpl-preview-table thead th {
        color: #fff !important;
    }
</style>
<script>
$(document).ready(function () {
    function escTplM(v) {
        return String(v === null || v === undefined ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // Update label file input
    $(document).on('change', '#inputImportTemplateMasuk', function () {
        var fileName = this.files[0] ? this.files[0].name : 'Pilih file...';
        $(this).next('.custom-file-label').text(fileName);
    });

    // Reset modal upload saat dibuka
    $('#ModalImportTemplateMasuk').on('show.bs.modal', function () {
        $('#inputImportTemplateMasuk').val('');
        $(this).find('.custom-file-label').text('Pilih file...');
    });

    // ── Tombol Preview: parse di server TANPA menyimpan ──
    $(document).on('click', '#btnPreviewTemplateMasuk', function () {
        var input = document.getElementById('inputImportTemplateMasuk');
        if (!input || !input.files.length) {
            alert('Pilih file terlebih dahulu!');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memuat...');

        var fd = new FormData();
        fd.append('fileTemplate', input.files[0]);
        fd.append('_token', '{{ csrf_token() }}');

        $('#previewBodyTemplateMasuk').html('<tr><td colspan="10" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Memproses data...</td></tr>');
        $('#badgeWarnTemplateMasuk').hide();
        $('#warnListTemplateMasuk').hide().empty();
        $('#btnConfirmTemplateMasuk').prop('disabled', true);

        $('#ModalImportTemplateMasuk').modal('hide');
        setTimeout(function () { $('#ModalPreviewTemplateMasuk').modal('show'); }, 400);

        $.ajax({
            url: '{{ route("bank-masuk.previewTemplate") }}',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (res) {
                btn.prop('disabled', false).html('<i class="fas fa-eye mr-1"></i>Preview Sebelum Import');

                $('#badgeTotalTemplateMasuk').text('Total: ' + res.total + ' baris');
                $('#confirmCountTemplateMasuk').text(res.total);
                $('#btnConfirmTemplateMasuk').prop('disabled', res.total === 0);

                if (res.warnings > 0) {
                    $('#badgeWarnTemplateMasuk').text('⚠ ' + res.warnings + ' peringatan').show();
                    var wl = '<strong>Peringatan (kolom terkait akan dikosongkan):</strong><br>';
                    res.warning_details.forEach(function (w) { wl += '• ' + escTplM(w) + '<br>'; });
                    if (res.warnings > res.warning_details.length) {
                        wl += '… dan ' + (res.warnings - res.warning_details.length) + ' peringatan lain.';
                    }
                    $('#warnListTemplateMasuk').html(wl).show();
                }

                var html = '';
                if (!res.rows.length) {
                    html = '<tr><td colspan="10" class="text-center text-muted py-4">Tidak ada baris berisi data.</td></tr>';
                } else {
                    res.rows.forEach(function (r) {
                        var w = r.warn || {};
                        var adaWarn = Object.keys(w).length > 0;
                        var ic = function (flag) { return flag ? ' <span title="Bermasalah — akan dikosongkan" style="color:#e67e22;">⚠</span>' : ''; };
                        html += '<tr style="' + (adaWarn ? 'background:#fff8dc;' : '') + '">'
                            + '<td class="text-center">' + r.baris + '</td>'
                            + '<td>' + (escTplM(r.agenda) || '-') + '</td>'
                            + '<td>' + (escTplM(r.tanggal) || '-') + ic(w.tanggal) + '</td>'
                            + '<td>' + (escTplM(r.sumber) || '-') + ic(w.sumber) + '</td>'
                            + '<td>' + (escTplM(r.bank) || '-') + ic(w.bank) + '</td>'
                            + '<td>' + (escTplM(r.kategori) || '-') + ic(w.kategori) + '</td>'
                            + '<td>' + (escTplM(r.jenis) || '-') + ic(w.jenis) + '</td>'
                            + '<td>' + (escTplM(r.penerima) || '-') + '</td>'
                            + '<td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + escTplM(r.uraian) + '">' + (escTplM(r.uraian) || '-') + '</td>'
                            + '<td class="text-right">' + escTplM(r.debet) + ic(w.debet) + '</td>'
                            + '</tr>';
                    });
                }
                $('#previewBodyTemplateMasuk').html(html);
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-eye mr-1"></i>Preview Sebelum Import');
                var msg = 'Terjadi kesalahan.';
                if (xhr.responseJSON) {
                    msg = xhr.responseJSON.message || msg;
                    if (xhr.responseJSON.errors) {
                        var first = Object.values(xhr.responseJSON.errors)[0];
                        if (first && first.length) msg = first[0];
                    }
                }
                $('#previewBodyTemplateMasuk').html('<tr><td colspan="10" class="text-center text-danger py-4">❌ ' + escTplM(msg) + '</td></tr>');
            }
        });
    });

    // ── Tombol Konfirmasi: baru di sini data disimpan ──
    $(document).on('click', '#btnConfirmTemplateMasuk', function () {
        if (!confirm('Yakin ingin menyimpan data ini ke Bank Masuk?')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: '{{ route("bank-masuk.confirmTemplate") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                $('#ModalPreviewTemplateMasuk').modal('hide');
                alert('✅ ' + res.success);
                if ($.fn.DataTable.isDataTable('#example2')) {
                    window.cbTableReload('#example2');
                }
                btn.html('<i class="fas fa-check mr-1"></i>Konfirmasi Import (<span id="confirmCountTemplateMasuk">0</span> baris)');
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i>Konfirmasi Import');
                var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Import gagal.';
                alert('❌ ' + msg);
            }
        });
    });
});
</script>
@endpush
