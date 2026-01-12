<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="modal fade" id="ModalCreateMasuk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data <span style="color:#FF7518">Bank Masuk</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('bank-masuk.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">
                           <label class="form-label" for="agenda_tahun">Agenda</label>
                            <input type="text" name="agenda_tahun" id="agenda_tahun" class="form-control" placeholder="agenda_tahun">
                        </div>
                        
                        <div class="col-md-6">
                           <label class="form-label" for="id_sumber_dana">Sumber Dana</label>
                            <select name="id_sumber_dana" id="id_sumber_dana" class="form-select">
                                <option disabled selected>Pilih Sumber Dana</option>
                                @foreach($sumberDana as $sd)
                                    <option value="{{ $sd->id_sumber_dana }}">{{ $sd->nama_sumber_dana }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label" for="id_bank_tujuan">Bank Tujuan</label>
                             <select name="id_bank_tujuan" id="id_bank_tujuan" class="form-select">
                                <option disabled selected>Pilih Bank Tujuan</option>
                                @foreach($bankTujuan as $bt)
                                    <option value="{{ $bt->id_bank_tujuan }}">{{ $bt->nama_tujuan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="kategori">Kriteria CF</label>
                            <select name="id_kategori_kriteria" id="kategori" class="form-select">
                                <option disabled selected>Pilih Kriteria CF</option>
                                @foreach($kategoriKriteria as $kk)
                                    <option value="{{ $kk->id_kategori_kriteria }}">{{ $kk->nama_kriteria }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
<!-- 
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label" for="sub_kriteria">Sub Kriteria</label>
                            <select name="id_sub_kriteria" id="sub_kriteria" class="form-select" required>
                                <option value="" disabled selected>Pilih Sub Kriteria</option>
                            </select>
                        </div> -->

                        <!-- <div class="col-md-6">
                            <label class="form-label" for="item_sub_kriteria">Item Sub Kriteria</label>
                            <select name="id_item_sub_kriteria" id="item_sub_kriteria" class="form-select">
                                <option value="" disabled selected>Pilih Item Sub Kriteria</option>
                            </select>
                        </div> -->
                    <!-- </div> -->

                    <div class="mt-2">
                       <label class="form-label" for="uraian">Uraian</label>
                        <textarea rows="4"name="uraian" id="uraian" class="form-control" placeholder="Uraian"></textarea>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label" for="penerima">Penerima</label>
                            <input type="text" name="penerima" id="penerima" class="form-control" placeholder="Penerima">
                        </div>
                        <div class="col-md-6">
                                <label class="form-label">Jenis Pembayaran</label>
                                <select name="id_jenis_pembayaran" id="jenisPembayaran" class="form-select">
                                    <option disabled selected>-- Pilih Jenis Pembayaran --</option>
                                     @foreach($jenisPembayaran as $jk)
                                        <option value="{{ $jk->id_jenis_pembayaran }}">{{ $jk->nama_jenis_pembayaran }}</option>
                                    @endforeach
                                </select>
                        </div>
                        
                    </div>

                    <div class=" row mt-2">
                        <!-- <div class="col-md-4">
                            <label class="form-label" for="debet">Jenis Pembayaran <span class="text-danger">*</span></label>
                            <input type="number" name="pembayaran" id="pembayaran" class="form-control rupiah-input" placeholder="0" step="0.01">
                        </div> -->

                        <div class="col-md-6" >
                            <label class="form-label" for="debet">Debet <span class="text-danger">*</span></label>
                            <input type="number" name="debet" id="debet" class="form-control rupiah-input" placeholder="0" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="tanggal">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" required>
                        </div>

                        <!-- <div class="col-md-4"">
                            <label class="form-label" for="nilai_rupiah">Nilai Ajuan <span class="text-danger">*</span></label>
                            <input type="number" name="nilai_rupiah" id="nilai_rupiah" class="form-control rupiah-input" placeholder="0" step="0.01">
                        </div> -->
                    </div>

                    <div class="mt-2">
                        <label class="form-label" for="keterangan">Keterangan</label>
                        <textarea rows="4"name="keterangan" class="form-control" id="keterangan"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn bg-danger text-white" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn bg-primary text-white">Simpan</button>
                </div>

            </form>

        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {

    // Format rupiah untuk input
    document.querySelectorAll('.rupiah').forEach(function(input){
        input.addEventListener('keyup', function(){
            let angka = this.value.replace(/[^0-9]/g, '');
            this.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        });
    });
    // Inisialisasi Select2 saat modal dibuka
    $('#ModalCreateMasuk').on('shown.bs.modal', function () {
        console.log(' Modal opened');
        
        if (!$('#id_sumber_dana').hasClass('select2-hidden-accessible')) {
            $('#id_sumber_dana').select2({
                 tags: true, 
                dropdownParent: $('#ModalCreateMasuk'),
                placeholder: 'Pilih Sumber Dana',
                allowClear: true
            });
            console.log('Select2 initialized');
        }
    });
    // Inisialisasi Select2 saat modal dibuka
    $('#ModalCreateMasuk').on('shown.bs.modal', function () {
        console.log(' Modal opened');
        
        if (!$('#id_bank_tujuan').hasClass('select2-hidden-accessible')) {
            $('#id_bank_tujuan').select2({
                 tags: true, 
                dropdownParent: $('#ModalCreateMasuk'),
                placeholder: 'Pilih Bank Tujuan',
                allowClear: true
            });
            console.log('Select2 initialized');
        }
    });
    // Inisialisasi Select2 saat modal dibuka
    $('#ModalCreateMasuk').on('shown.bs.modal', function () {
        console.log(' Modal opened');
        
        if (!$('#kategori').hasClass('select2-hidden-accessible')) {
            $('#kategori').select2({
                 tags: true, 
                dropdownParent: $('#ModalCreateMasuk'),
                placeholder: 'Pilih Kategori',
                allowClear: true
            });
            console.log('Select2 initialized');
        }
    });
    // Inisialisasi Select2 saat modal dibuka
    $('#ModalCreateMasuk').on('shown.bs.modal', function () {
        console.log(' Modal opened');
        
        if (!$('#jenisPembayaran').hasClass('select2-hidden-accessible')) {
            $('#jenisPembayaran').select2({
                 tags: true, 
                dropdownParent: $('#ModalCreateMasuk'),
                placeholder: 'Pilih jenisPembayaran',
                allowClear: true
            });
            console.log('Select2 initialized');
        }
    });

    // Destroy Select2 saat modal ditutup (cleanup)
    $('#ModalCreateMasuk').on('hidden.bs.modal', function () {
        if ($('#dokumen_id').hasClass('select2-hidden-accessible')) {
            $('#dokumen_id').select2('destroy');
        }
        // Clear form
        $('#uraian').val('');
        $('#nilai_rupiah').val('');
        $('#penerima').val('');
        $('#pembayaran').val('');
    });

    // Load sub-kriteria berdasarkan kategori yang dipilih
    // $('#kategori').on('change', function () {
    //     let id = $(this).val();
        
    //     // Reset sub kriteria dan item sub kriteria
    //     $('#sub_kriteria').html('<option value="" disabled selected>Pilih Sub Kriteria</option>');
    //     $('#item_sub_kriteria').html('<option value="" disabled selected>Pilih Item Sub Kriteria</option>');
        
    //     if(id){
    //         $.ajax({
    //             url: '/sub-kriteria/' + id,
    //             type: 'GET',
    //             success: function (data) {
    //                 if(data.length > 0) {
    //                     data.forEach(function (item) {
    //                         $('#sub_kriteria').append(
    //                             '<option value="' + item.id_sub_kriteria + '">' + item.nama_sub_kriteria + '</option>'
    //                         );
    //                     });
    //                 } else {
    //                     $('#sub_kriteria').append('<option value="" disabled>Tidak ada data</option>');
    //                 }
    //             },
    //             error: function(xhr, status, error) {
    //                 console.error('Error loading sub kriteria:', error);
    //                 alert('Gagal memuat data sub kriteria');
    //             }
    //         });
    //     }
    // });

    // Load item sub-kriteria berdasarkan sub kriteria yang dipilih
    // $('#sub_kriteria').on('change', function () {
    //     let id = $(this).val();
        
    //     // Reset item sub kriteria
    //     $('#item_sub_kriteria').html('<option value="" disabled selected>Pilih Item Sub Kriteria</option>');
        
    //     if(id){
    //         $.ajax({
    //             url: '/item-sub-kriteria/' + id,
    //             type: 'GET',
    //             success: function (data) {
    //                 if(data.length > 0) {
    //                     data.forEach(function (item) {
    //                         $('#item_sub_kriteria').append(
    //                             '<option value="' + item.id_item_sub_kriteria + '">' + item.nama_item_sub_kriteria + '</option>'
    //                         );
    //                     });
    //                 } else {
    //                     $('#item_sub_kriteria').append('<option value="" disabled>Tidak ada data</option>');
    //                 }
    //             },
    //             error: function(xhr, status, error) {
    //                 console.error('Error loading item sub kriteria:', error);
    //                 alert('Gagal memuat data item sub kriteria');
    //             }
    //         });
    //     }
    // });

    $('form').on('submit', function() {
        $('.rupiah-input').each(function() {
            let nilai = $(this).val().replace(/\./g, '');  
            $(this).val(nilai);
        });
    });

});
</script>
