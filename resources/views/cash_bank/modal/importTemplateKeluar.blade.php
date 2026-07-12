{{-- ============ MODAL IMPORT MANDIRI (TEMPLATE EXCEL) ============ --}}
<div class="modal fade" id="ModalImportTemplateKeluar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header" style="background:#0d3b6e; color:#fff;">
        <h5 class="modal-title"><i class="fas fa-file-import mr-2"></i>Import Excel – Bank Keluar</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-3" style="font-size:12.5px;">
          Catat transaksi pada <strong>template Excel</strong> di bawah, lalu upload di sini.
          Tidak semua kolom wajib diisi — kolom yang kosong tetap ikut terimport dan
          tampil di tabel Bank Keluar (bisa dilengkapi kemudian lewat tombol edit).
        </p>
        <a href="{{ route('bank-keluar.templateImport') }}" class="btn btn-outline-success btn-sm btn-block mb-3">
          <i class="fas fa-download mr-1"></i>Download Template Excel
        </a>
        <div class="form-group mb-0">
          <div class="custom-file">
            <input type="file" class="form-control custom-file-input" id="inputImportTemplateKeluar"
                   accept=".xlsx,.xls,.csv">
            <label class="custom-file-label" for="inputImportTemplateKeluar">Pilih file...</label>
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm" id="btnImportTemplateKeluar">
          <i class="fas fa-file-import mr-1"></i>Import
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    // Update label file input
    $(document).on('change', '#inputImportTemplateKeluar', function () {
        var fileName = this.files[0] ? this.files[0].name : 'Pilih file...';
        $(this).next('.custom-file-label').text(fileName);
    });

    // Reset modal saat dibuka
    $('#ModalImportTemplateKeluar').on('show.bs.modal', function () {
        $('#inputImportTemplateKeluar').val('');
        $(this).find('.custom-file-label').text('Pilih file...');
    });

    // Tombol Import
    $(document).on('click', '#btnImportTemplateKeluar', function () {
        var input = document.getElementById('inputImportTemplateKeluar');
        if (!input || !input.files.length) {
            alert('Pilih file terlebih dahulu!');
            return;
        }
        if (!confirm('Import data dari file ini ke Bank Keluar?')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Mengimport...');

        var fd = new FormData();
        fd.append('fileTemplate', input.files[0]);
        fd.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route("bank-keluar.importTemplate") }}',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (res) {
                btn.prop('disabled', false).html('<i class="fas fa-file-import mr-1"></i>Import');
                $('#ModalImportTemplateKeluar').modal('hide');
                var msg = '✅ ' + res.success;
                if (res.warning_details && res.warning_details.length) {
                    msg += '\n\nPerhatian:\n- ' + res.warning_details.join('\n- ');
                }
                alert(msg);
                if ($.fn.DataTable.isDataTable('#example3')) {
                    window.cbTableReload('#example3');
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-file-import mr-1"></i>Import');
                var msg = 'Import gagal. Periksa kembali file Anda.';
                if (xhr.responseJSON) {
                    msg = xhr.responseJSON.message || xhr.responseJSON.error || msg;
                    // Error validasi Laravel (422) menyimpan detail di errors
                    if (xhr.responseJSON.errors) {
                        var first = Object.values(xhr.responseJSON.errors)[0];
                        if (first && first.length) msg = first[0];
                    }
                }
                alert('❌ ' + msg);
            }
        });
    });
});
</script>
@endpush
