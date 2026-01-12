
<div class="modal fade" id="ModalImportFileExcelMasuk">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">File Excel</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
            <form action="{{ route('bank-masuk.importExcel') }}" method="POST" enctype="multipart/form-data" id="importExcel">
                @csrf    
                <div class="card-body">
                  <div class="form-group">
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" name="fileExcel" class="form-control custom-file-input" id="inputImportExcel"  required>
                        <label class="custom-file-label" for="inputImportExcel" >Choose file</label>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="modal-footer">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form>
            </div>
          </div>
        </div>

</div>
@push('scripts')
<script>
  $(document).on('submit', '#importExcel', function () {
        console.log('SUBMIT TERPANGGIL');

        $('#btnSubmit')
            .prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> Sedang Upload...');
        });
  $(document).ready(function () {
    bsCustomFileInput.init();
});
</script>
@endpush