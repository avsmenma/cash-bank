
<div class="modal fade" id="ModalTambahBank">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Input Akun VA Baru</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
            <form action="{{route('daftarBank.store')}}" method="POST" enctype="multipart/form-data" id="importExcel">
                @csrf    
                <div class="card-body">
                  <div class="input-group input-group-sm">
                  <input type="text" name="nama_tujuan" id="namaTujuan" class="form-control" placeholder="Nama Bank Tujuan" step="0.01">
                  <span class="input-group-append">
                    <button type="submit" class="btn btn-info btn-flat">Go!</button>
                  </span>
                </div>
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