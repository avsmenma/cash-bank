<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Notifikasi Success/Error -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Berhasil!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Validasi Gagal!</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
<div class="modal fade" id="ModalCreate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data <span style="color:#FF7518">Bank Keluar</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('bank-keluar.store') }}" method="post" enctype="multipart/form-data"  id="formBankKeluar">
                @csrf
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Agenda</label>
                            <select name="agenda_tahun" id="dokumen_id" class="form-select" style="width:100%">
                                <option value="">Pilih Agenda atau ketik baru</option>
                                @foreach($agenda as $a)
                                    <option value="{{ $a->dokumen_id }}" data-uraian="{{ $a->uraian }}" ...>{{ $a->agenda_tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Sumber Dana</label>
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
                            <label class="form-label">Bank Tujuan</label>
                            <select name="id_bank_tujuan" id="id_bank_tujuan" class="form-select">
                                <option disabled selected>Pilih Bank Tujuan</option>
                                @foreach($bankTujuan as $bt)
                                    <option value="{{ $bt->id_bank_tujuan }}">{{ $bt->nama_tujuan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kriteria CF</label>
                            <select name="id_kategori_kriteria" id="kategori" class="form-select">
                               <option value="">Pilih Kriteria CF</option>
                                @foreach($kategoriKriteria as $kk)
                                    <option value="{{ $kk->id_kategori_kriteria }}">
                                        {{ $kk->nama_kriteria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Sub Kriteria</label>
                            <select name="id_sub_kriteria" id="sub_kriteria" class="form-select">
                                <option value="">Pilih Sub Kriteria</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Item Sub Kriteria</label>
                            <select name="id_item_sub_kriteria" id="item_sub_kriteria" class="form-select" >
                    <option value="">Pilih Item Sub Kriteria</option>
                </select>
                        </div>
                    </div>

                    <div class="mt-2">
                        <label class="form-label">Uraian</label>
                        <textarea rows="3"name="uraian" id="uraian" class="form-control" placeholder="Uraian"></textarea>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Penerima</label>
                            <input type="text" name="penerima" id="penerima" class="form-control" placeholder="Penerima">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Pembayaran</label>
                            <!-- <input type="text" name="pembayaran" id="pembayaran" class="form-control" placeholder="pembayaran"> -->
                             <select name="id_jenis_pembayaran" id="jenisPembayaran" class="form-select">
                                    <option disabled selected>-- Pilih Jenis Pembayaran --</option>
                                     @foreach($jenisPembayaran as $jk)
                                        <option value="{{ $jk->id_jenis_pembayaran }}">{{ $jk->nama_jenis_pembayaran }}</option>
                                    @endforeach
                                </select>
                        </div>

                        
                        
                    </div>
                    <div class="row mt-2"> 
                        <div class="col-md-4">
                            <label class="form-label">Kredit <span class="text-danger">*</span></label>
                            <!-- <input type="number" name="kredit" id="kredit" class="form-control" placeholder="0" step="0.01" required> -->
                             <input type="number" name="kredit" id="kreditt" class="form-control rupiah-input" placeholder="0" step="0.01" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Nilai Ajuan <span class="text-danger">*</span></label>
                            <input type="number" name="nilai_rupiah" id="nilai_rupiahh" class="form-control rupiah-input" placeholder="0" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" required>
                        </div>
                    </div>

                    <div class="mt-2">
                        <label class="form-label">Keterangan</label>
                        <textarea rows="4"name="keterangan" class="form-control"></textarea>
                    </div>

                </div>
                <div class="row mt-2 justify-content-end m-2 mb-2" >
                    <div class="col-md-12 justify-content-end ">
                        <div id="splits-container">
                            <button type="button" class="add-split-btn btn btn-sm bg-danger text-white" id="btnAddSplit">
                                Add Split Agenda
                            </button>
                        </div>
                    </div>
                </div>
                

                <div class="modal-footer">
                    <button type="button" class="btn bg-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn bg-primary" id="btnSubmit">
                        Simpan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {

    // format rupiah
    document.querySelectorAll('.rupiah').forEach(function(input){
        input.addEventListener('keyup', function(){
            let angka = this.value.replace(/[^0-9]/g, '');
            this.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        });
    });
    // ===============================
    // INIT SELECT2
    // ===============================
    // function initSelect2(modal) {
    //     modal.find('select').each(function () {
    //         if (!$(this).hasClass('select2-hidden-accessible')) {
    //             $(this).select2({
    //                 dropdownParent: modal,
    //                 width: '100%',
    //                 allowClear: true
    //             });
    //         }
    //     });
    // }

    // ===============================
    // SAAT MODAL DIBUKA
    // ===============================
    // $('#ModalCreate').on('shown.bs.modal', function () {
    //     initSelect2($(this));
    // });
    function initSelect2(modal) {
    modal.find('select').each(function () {
        const $select = $(this);
        const selectId = $select.attr('id');
        
        console.log('Checking select:', selectId, 'Has Select2:', $select.hasClass('select2-hidden-accessible'));
        
        if (!$select.hasClass('select2-hidden-accessible')) {
            try {
                $select.select2({
                    dropdownParent: modal,
                    width: '100%',
                    allowClear: true
                });
                console.log('✅ Select2 initialized for:', selectId);
            } catch(e) {
                console.error('❌ Failed to init Select2 for:', selectId, e);
            }
        }
    });
}

$(document).ready(function () {
    // Inisialisasi datetimepicker
    $('#reservationdate_tambah').datetimepicker({
        format: 'YYYY-MM-DD',
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'far fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    });

    $('[name="tanggal"]').val(moment().format('YYYY-MM-DD'));
});

// ===============================
// HELPER FUNCTION: Init Select2
// ===============================
function initSelect2(modal) {
    modal.find('select').each(function () {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            $(this).select2({
                dropdownParent: modal,
                width: '100%',
                theme: 'bootstrap4',
                allowClear: true
            });
        }
    });
}

// ===============================
// MODAL CREATE: INIT SEKALI SAJA
// ===============================
$('#ModalCreateKeluar').on('shown.bs.modal', function () {
    const modal = $(this);

    console.log('Modal opened, initializing Select2 (ONCE)');

    // Init Select2 SEKALI SAJA (tidak destroy)
    initSelect2(modal);

    // Reset form
    $('#formBankKeluar')[0].reset();
    
    // Reset dropdown dependent
    $('#sub_kriteria').html('<option value="">-- Pilih Sub Kriteria --</option>');
    $('#item_sub_kriteria').html('<option value="">-- Pilih Item Sub Kriteria --</option>');

    console.log('✅ Select2 initialized once');
});

// ===============================
// KATEGORI → SUB KRITERIA
// ===============================
$(document).on('change', '#kategori_kriteria', function () {
    let modal = $('#ModalCreateKeluar');
    let kategoriId = $(this).val();

    let sub = modal.find('#sub_kriteria');
    let item = modal.find('#item_sub_kriteria');

    console.log('🔄 Kategori changed to:', kategoriId);

    // Reset option (JANGAN destroy select2)
    sub.html('<option value="">-- Pilih Sub Kriteria --</option>');
    item.html('<option value="">-- Pilih Item Sub Kriteria --</option>');

    if (!kategoriId) {
        initSelect2(modal);
        return;
    }

    console.log('⏳ Loading sub kriteria untuk kategori:', kategoriId);

    $.ajax({
        url: '/get-sub-kriteria/' + kategoriId,
        type: 'GET',
        success: function (res) {
            console.log('✅ Sub kriteria loaded:', res);
            
            if (res.length > 0) {
                res.forEach(function (e) {
                    // Trim untuk hilangkan \r\n
                    let namaSub = (e.nama_sub_kriteria || '').trim();
                    sub.append(
                        '<option value="'+e.id_sub_kriteria+'">'+namaSub+'</option>'
                    );
                });
            }
            
            // Re-init select2 (hanya yang belum terinit)
            initSelect2(modal);
            
            console.log('✅ Sub kriteria options added');
        },
        error: function(xhr, status, error) {
            console.error('❌ Error loading sub kriteria:', error);
            sub.html('<option value="">-- Error memuat data --</option>');
        }
    });
});

// ===============================
// SUB KRITERIA → ITEM SUB KRITERIA
// ===============================
$(document).on('change', '#sub_kriteria', function () {
    let modal = $('#ModalCreateKeluar');
    let subId = $(this).val();

    let item = modal.find('#item_sub_kriteria');

    console.log('🔄 Sub kriteria changed to:', subId);

    // Reset option (JANGAN destroy select2)
    item.html('<option value="">-- Pilih Item Sub Kriteria --</option>');

    if (!subId) {
        initSelect2(modal);
        return;
    }

    console.log('⏳ Loading item sub kriteria untuk sub:', subId);

    $.ajax({
        url: '/get-item-sub-kriteria/' + subId,
        type: 'GET',
        success: function (res) {
            console.log('✅ Item sub kriteria loaded:', res);
            
            if (res.length > 0) {
                res.forEach(function (e) {
                    // Trim untuk hilangkan \r\n
                    let namaItem = (e.nama_item_sub_kriteria || '').trim();
                    item.append(
                        '<option value="'+e.id_item_sub_kriteria+'">'+namaItem+'</option>'
                    );
                });
            }
            
            // Re-init select2 (hanya yang belum terinit)
            initSelect2(modal);
            
            console.log('✅ Item sub kriteria options added');
        },
        error: function(xhr, status, error) {
            console.error('❌ Error loading item sub kriteria:', error);
            item.html('<option value="">-- Error memuat data --</option>');
        }
    });
});

// ===============================
// RESET SAAT MODAL DITUTUP
// ===============================
$('#ModalCreateKeluar').on('hidden.bs.modal', function () {
    const modal = $(this);

    // Reset value saja (JANGAN destroy select2)
    modal.find('select').val(null).trigger('change');

    console.log('🧹 Modal reset without destroying Select2');
});

// ===============================
// MODAL EDIT KELUAR
// ===============================
$('#editKeluar').on('shown.bs.modal', function (event) {
    let button = $(event.relatedTarget);
    let id       = button.data('id');
    let agenda   = button.data('agenda');
    let penerima = button.data('penerima');
    let tanggal  = button.data('tanggal');
    let bank     = button.data('bank');
    let sumber   = button.data('sumber');
    let kategori = button.data('kategori');
    let sub      = button.data('sub');
    let item     = button.data('item');
    let jenis    = button.data('jenis');
    let kredit   = button.data('kredit');
    let uraian   = button.data('uraian');
    let keterangan = button.data('keterangan');

    console.log('📝 Edit modal opened with kategori:', kategori);

    // INIT SELECT2 di modal edit
    const modal = $(this);
    initSelect2(modal);

    // Set form action
    $('#formEdit').attr('action', '/bank-keluar/' + id);

    // Input biasa
    $('#agenda_tahun').val(agenda);
    $('#penerima').val(penerima);
    $('#kredit').val(kredit);
    $('#keterangan').val(keterangan);
    $('#uraian').val(uraian);
    $('[name="tanggal"]').val(tanggal);

    // SET VALUE SELECT2 (trigger change untuk reload dependent dropdowns)
    $('[name="id_bank_tujuan"]').val(bank).trigger('change');
    $('[name="id_sumber_dana"]').val(sumber).trigger('change');
    $('[name="id_jenis_pembayaran"]').val(jenis).trigger('change');

    // SET KATEGORI → Load Sub → Set Sub → Load Item → Set Item
    $('[name="id_kategori_kriteria"]').val(kategori).trigger('change');

    // Load sub kriteria berdasarkan kategori
    if (kategori) {
        $.get('/get-sub-kriteria/' + kategori, function (subs) {
            let opt = '<option value="">-- Pilih Sub Kriteria --</option>';
            subs.forEach(s => {
                let namaSub = (s.nama_sub_kriteria || '').trim();
                opt += `<option value="${s.id_sub_kriteria}">${namaSub}</option>`;
            });
            $('#sub_kriteria').html(opt).val(sub).trigger('change');

            // Load item sub kriteria berdasarkan sub
            if (sub) {
                $.get('/get-item-sub-kriteria/' + sub, function (items) {
                    let opt2 = '<option value="">-- Pilih Item Sub Kriteria --</option>';
                    items.forEach(i => {
                        let namaItem = (i.nama_item_sub_kriteria || '').trim();
                        opt2 += `<option value="${i.id_item_sub_kriteria}">${namaItem}</option>`;
                    });
                    $('#item_sub_kriteria').html(opt2).val(item).trigger('change');
                });
            }
        });
    }
});

// ===============================
// CHECKBOX & DELETE
// ===============================
$(document).on('click', '#select_all_ids', function () {
    $('.checkbox_ids').prop('checked', this.checked);
});

$(document).on('click', '#deleteAllSelectedRecord', function (e) {
    e.preventDefault();

    let all_ids = [];
    $('.checkbox_ids:checked').each(function () {
        all_ids.push($(this).val());
    });

    if (all_ids.length === 0) {
        alert('Pilih data terlebih dahulu!');
        return;
    }

    if (!confirm(`Yakin ingin menghapus ${all_ids.length} data?`)) return;

    $.ajax({
        url: "{{ route('bank-keluar.delete') }}",
        type: "DELETE",
        data: {
            ids: all_ids,
            _token: '{{ csrf_token() }}'
        },
        success: function (res) {
            $('#example3').DataTable().ajax.reload(null, false);
            alert(res.success);
        },
        error: function () {
            alert('Gagal menghapus data');
        }
    });
});
let splitIndex = 0;

function hitungTotalSplit() {
    let total = 0;
    $('.kredit-split').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    return total;
}

function hitungSisa() {
    const nilaiAjuan = parseFloat($('#nilai_rupiahh').val()) || 0;
    const kreditUtama = parseFloat($('#kreditt').val()) || 0;
    const totalSplit = hitungTotalSplit();

    return nilaiAjuan - kreditUtama - totalSplit;
}

/* UPDATE SISA OTOMATIS */
$(document).on('input', '#kredit, .kredit-split', function () {
    const sisa = hitungSisa();
    if (sisa < 0) {
        alert('Total kredit melebihi nilai ajuan');
        $(this).val(0);
    }
});

// KATEGORI SPLIT → SUB KRITERIA
$(document).on('change', '.split-kategori', function () {

    let kategoriId = $(this).val();
    let row = $(this).closest('.split-row');
    let subSelect = row.find('.split-sub-kriteria');
    let itemSelect = row.find('.split-item-sub-kriteria');

    subSelect.empty().append('<option>Pilih Sub Kriteria</option>');
    itemSelect.empty().append('<option>Pilih Item Sub Kriteria</option>');

    if (!kategoriId) return;

    $.get('/get-sub-kriteria/' + kategoriId, function (res) {
        res.forEach(e => {
            subSelect.append(
                `<option value="${e.id_sub_kriteria}">
                    ${e.nama_sub_kriteria}
                </option>`
            );
        });
    });
});
// SUB KRITERIA SPLIT → ITEM SUB KRITERIA
$(document).on('change', '.split-sub-kriteria', function () {

    let subId = $(this).val();
    let row = $(this).closest('.split-row');
    let itemSelect = row.find('.split-item-sub-kriteria');

    itemSelect.empty().append('<option>Pilih Item Sub Kriteria</option>');

    if (!subId) return;

    $.get('/get-item-sub-kriteria/' + subId, function (res) {
        res.forEach(e => {
            itemSelect.append(
                `<option value="${e.id_item_sub_kriteria}">
                    ${e.nama_item_sub_kriteria}
                </option>`
            );
        });
    });
});



/* ===============================
TAMBAH SPLIT
================================ */
$(document).on('click', '#btnAddSplit', function () {

    console.log('BTN ADD SPLIT CLICKED');

    const nilaiAjuan = parseFloat($('#nilai_rupiahh').val()) || 0;
    const kreditUtama = parseFloat($('#kreditt').val()) || 0;

    if (nilaiAjuan <= 0) {
        alert('Nilai ajuan belum ada');
        return;
    }

    if (kreditUtama <= 0) {
        alert('Isi kredit utama dulu');
        return;
    }

    const sisa = hitungSisa();

    if (sisa <= 0) {
        alert('Sisa nilai sudah habis');
        return;
    }

    const html = `
    <div class="split-row border rounded p-3 mb-2">

        <div class="d-flex justify-content-between mb-2">
            <strong>Split #${splitIndex + 1}</strong>
            <button type="button" class="btn btn-sm bg-danger remove-split text-white">×</button>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label>Kategori</label>
                <select name="split[kategori][]" 
                        class="form-select split-kategori" required>
                    <option value="">Pilih Kategori</option>
                    @foreach($kategoriKriteria as $kk)
                        <option value="{{ $kk->id_kategori_kriteria }}">
                            {{ $kk->nama_kriteria }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label>Sub Kriteria</label>
                <select name="split[sub_kriteria][]" 
                        class="form-select split-sub-kriteria" required>
                    <option value="">Pilih Sub Kriteria</option>
                </select>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <label>Item Sub Kriteria</label>
                <select name="split[item_sub_kriteria][]" 
                        class="form-select split-item-sub-kriteria" required>
                    <option value="">Pilih Item Sub Kriteria</option>
                </select>
            </div>

            <div class="col-md-6">
                <label>Kredit</label>
                <input type="number"
                       name="split[kredit][]"
                       class="form-control kredit-split"
                       value="${sisa}"
                       step="0.01"
                       min="0"
                       required>
            </div>
        </div>
    </div>
    `; 

    $('#splits-container').append(html); 
    splitIndex++;
});

// $(document).on('focus', '.split-kategori, .split-sub-kriteria, .split-item-sub-kriteria', function () {
//     if (!$(this).hasClass('select2-hidden-accessible')) {
//         $(this).select2({
//             dropdownParent: $('#ModalCreate'),
//             width: '100%'
//         });
//     }
// });

/* ===============================
HAPUS SPLIT
================================ */
$(document).on('click', '.remove-split', function () {
    $(this).closest('.split-row').remove();
});

/* ===============================
SUBMIT FORM
================================ */
$(document).on('submit', '#formBankKeluar', function () {
    console.log('SUBMIT TERPANGGIL');

    $('#btnSubmit')
        .prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
});
</script>
