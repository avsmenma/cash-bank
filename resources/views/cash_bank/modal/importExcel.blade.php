<!-- Modal -->
<div class="modal fade" id="ModalImportFileExcel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="exampleModalLabel"><i class="fas fa-file-import"></i> Import Data Bank
          Keluar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('bank-keluar.importExcel') }}" method="POST" enctype="multipart/form-data"
          id="importExcelKeluar">
          @csrf
          <div class="form-group">
            <label for="fileExcel">File CSV</label>
            <input type="file" name="fileExcel" id="fileExcel" class="form-control" accept=".csv" required>
            <small class="text-muted">
              Format: CSV dengan delimiter semicolon (;)<br>
              Kolom: Agenda Tahun, Tanggal, Sumber Dana, Bank Tujuan, Kategori, Penerima, Uraian, Debet, Jenis
              Pembayaran
            </small>
          </div>
          <div class="mt-4 text-end">
            <button type="submit" class="btn bg-primary btn-sm text-white" id="btnSubmitKeluar">
              <i class="fas fa-upload"></i> Import CSV
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
<script>
  $(document).on('submit', '#importExcelKeluar', function () {
    console.log('SUBMIT TERPANGGIL');

    $('#btnSubmitKeluar')
      .prop('disabled', true)
      .html('<span class="spinner-border spinner-border-sm"></span> Sedang Upload...');
  });
</script>