<div class="modal fade" id="editBankTujuan" tabindex="-1">
  <div class="modal-dialog">
    <form id="formEditBankTujuan" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <input type="hidden" id="id_bank_tujuan">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Bank Tujuan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
        </div>

        <div class="modal-body">
           <input type="text" class="form-control" id="edit_nama_tujuan" name="nama_tujuan">
        </div>

        <div class="modal-footer">
          <button class="btn bg-primary text-white">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
@push('scripts')
<script>
$('#editBankTujuan').on('show.bs.modal', function (event) {
    let button = $(event.relatedTarget);
    let id = button.data('id');
    let nama = button.data('nama');

    $('#edit_nama_tujuan').val(nama);
    $('#id_bank_tujuan').val(id);

    $('#formEditBankTujuan').attr('action', '/daftarBank/' + id);
});
</script>
@endpush
