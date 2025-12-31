<!-- Modal -->
<div class="modal fade" id="ModalImportFileExcelMasuk" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="exampleModalLabel"><i class="fas fa-search"></i>Filter Tanggal </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
       <form action="{{ route('bank-masuk.importExcel') }}" method="POST" enctype="multipart/form-data" id="importExcel">
            @csrf
            <div class="form-group">
                <label for="fileExcel">File</label>
                <input type="file" name="fileExcel" id="fileExcel" class="form-control" required>
            </div>
            <div class="mt-4 text-end">
                <button type="submit" class="btn bg-primary btn-sm text-white" id="btnSubmit">Terapkan</button>
            </div>
        </form>

      </div>
    </div>
  </div>
</div>

<script>
  $(document).on('submit', '#importExcel', function () {
        console.log('SUBMIT TERPANGGIL');

        $('#btnSubmit')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> Sedang Upload...');
        });
</script>