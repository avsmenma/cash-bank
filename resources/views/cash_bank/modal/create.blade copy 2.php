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
<div class="modal fade" id="ModalCreateKeluar" tabindex="-1" aria-hidden="true">
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
                            <select name="agenda_tahun" id="dokumen_id" class="select2" style="width:100%">
                                <option value="">Pilih Agenda atau ketik baru</option>
                                @foreach($agenda as $a)
                                    <option value="{{ $a->dokumen_id }}" data-uraian="{{ $a->uraian }}" ...>{{ $a->agenda_tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Sumber Dana</label>
                            <select name="id_sumber_dana" id="id_sumber_dana" class="select2">
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
                            <select name="id_bank_tujuan" id="id_bank_tujuan" class="select2">
                                <option disabled selected>Pilih Bank Tujuan</option>
                                @foreach($bankTujuan as $bt)
                                    <option value="{{ $bt->id_bank_tujuan }}">{{ $bt->nama_tujuan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kriteria CF</label>
                            <select name="id_kategori_kriteria" id="kategori" class="select2">
                                <option disabled selected>Pilih Kriteria CF</option>
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
                            <select name="id_sub_kriteria" id="sub_kriteria" class="select2">
                                <option value="">Pilih Sub Kriteria</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Item Sub Kriteria</label>
                            <select name="id_item_sub_kriteria" id="item_sub_kriteria" class="select2" >
                    <option value="">Pilih Item Sub Kriteria</option>
                </select>
                        </div>
                    </div>

                    <div class="mt-2">
                        <label class="form-label">Uraian</label>
                        <textarea rows="4"name="uraian" id="uraian" class="form-control" placeholder="Uraian"></textarea>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Penerima</label>
                            <input type="text" name="penerima" id="penerima" class="form-control" placeholder="Penerima">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Pembayaran</label>
                            <!-- <input type="text" name="pembayaran" id="pembayaran" class="form-control" placeholder="pembayaran"> -->
                             <select name="id_jenis_pembayaran" id="jenisPembayaran" class="select2">
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
                            <label>Date:</label>
                            <div class="input-group date" id="reservationdate" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" data-target="#reservationdate"/>
                                <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>    
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

    // Flag untuk auto-fill
    let isAutoFilling = false;

    // format rupiah
    document.querySelectorAll('.rupiah-input').forEach(function(input){
        input.addEventListener('input', function(){
            let angka = this.value.replace(/[^0-9]/g, '');
            this.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        });
    });
   
    // ===============================
    // INIT SELECT2
    // ===============================
    function initSelect2() {
        $('.select2').select2({
            placeholder: 'Pilih...',
            allowClear: true,
            width: '100%'
        });
    }
    
    //Date picker
    $('#reservationdate').datetimepicker({
        format: 'YYYY/MM/DD'
    });

    // ===============================
    // SAAT MODAL DIBUKA
    // ===============================
    $('#ModalCreateKeluar').on('shown.bs.modal', function () {
        initSelect2();
    });

    // ===============================
    // KATEGORI → SUB KRITERIA
    // ===============================
    $(document).on('change', '#kategori', function () {
        // Skip jika sedang auto-fill
        if (isAutoFilling) {
            console.log('Kategori berubah (auto-fill, loading sub kriteria...)');
            return;
        }

        let modal = $('#ModalCreateKeluar');
        let kategoriId = $(this).val();

        let sub = modal.find('#sub_kriteria');
        let item = modal.find('#item_sub_kriteria');

        // reset option
        sub.html('<option value="">Pilih Sub Kriteria</option>');
        item.html('<option value="">Pilih Item Sub Kriteria</option>');

        if (!kategoriId) {
            initSelect2();
            return;
        }

        $.ajax({
            url: '/get-sub-kriteria/' + kategoriId,
            type: 'GET',
            success: function (res) {
                console.log('Sub kriteria loaded:', res.length);
                if (res.length > 0) {
                    res.forEach(function (e) {
                        sub.append(
                            '<option value="'+e.id_sub_kriteria+'">'+e.nama_sub_kriteria+'</option>'
                        );
                    });
                }
                initSelect2();
            }
        });
    });

    // ===============================
    // SUB KRITERIA → ITEM SUB KRITERIA
    // ===============================
    $(document).on('change', '#sub_kriteria', function () {
        let subId = $(this).val();
        
        console.log('Sub Kriteria berubah:', subId);
        console.log('Auto-filling status:', isAutoFilling);
        
        // Skip jika sedang auto-fill
        if (isAutoFilling) {
            console.log('Skip AJAX karena sedang auto-fill');
            return;
        }

        let modal = $('#ModalCreateKeluar');
        let item = modal.find('#item_sub_kriteria');

        item.html('<option value="">Pilih Item Sub Kriteria</option>');

        if (!subId) {
            console.log('Sub ID kosong, skip AJAX');
            initSelect2();
            return;
        }

        console.log('Fetching item sub kriteria untuk subId:', subId);

        $.ajax({
            url: '/get-item-sub-kriteria/' + subId,
            type: 'GET',
            success: function (res) {
                console.log('Item sub kriteria response:', res);
                if (res.length > 0) {
                    res.forEach(function (e) {
                        item.append(
                            '<option value="'+e.id_item_sub_kriteria+'">'+e.nama_item_sub_kriteria+'</option>'
                        );
                    });
                }
                initSelect2();
            },
            error: function(xhr, status, error) {
                console.error('Error fetching item sub kriteria:', error);
            }
        });
    });

    // ===============================
    // AGENDA CHANGE - AMBIL DATA
    // ===============================
    $(document).on('change', '#dokumen_id', function() {
    const dokumenId = $(this).val();
    
    console.log('Agenda selected:', dokumenId);
    
    if(dokumenId && dokumenId !== '') {
        console.log('Fetching data...');
        
        isAutoFilling = true;
        
        $('.select2').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('close');
            }
        });
        
        $('#uraian').val('Memuat data...');
        $('#nilai_rupiahh').attr('placeholder', 'Memuat data...').val('');
        $('#penerima').val('Memuat data...');
        
        $.ajax({
            url: '/get-dokumen-detail/' + dokumenId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Data received:', response);

                if(response.success && response.data) {
                    const dataAgenda = response.data;

                    $('#uraian').val(dataAgenda.uraian || '');
                    $('#nilai_rupiahh').val(dataAgenda.nilai_rupiah || '');
                    $('#penerima').val(dataAgenda.penerima || '');

                    if (dataAgenda.jenis_pembayaran_id) {
                        $('#jenisPembayaran').val(dataAgenda.jenis_pembayaran_id).trigger('change.select2');
                    }
                    
                    if (dataAgenda.kategori_id) {
                        $('#kategori').val(dataAgenda.kategori_id).trigger('change.select2');
                        
                        console.log('Loading sub kriteria untuk kategori:', dataAgenda.kategori_id);
                        $.ajax({
                            url: '/get-sub-kriteria/' + dataAgenda.kategori_id,
                            type: 'GET',
                            success: function (subKriteriaList) {
                                console.log('Sub kriteria loaded:', subKriteriaList.length, 'items');
                                
                                let subSelect = $('#sub_kriteria');
                                
                                // Destroy Select2 dulu
                                if (subSelect.hasClass('select2-hidden-accessible')) {
                                    subSelect.select2('destroy');
                                }
                                
                                // Build options HTML
                                let subOptions = '<option value="">Pilih Sub Kriteria</option>';
                                if (subKriteriaList.length > 0) {
                                    subKriteriaList.forEach(function (e) {
                                        let selected = (e.id_sub_kriteria == dataAgenda.sub_kriteria_id) ? 'selected' : '';
                                        subOptions += '<option value="'+e.id_sub_kriteria+'" '+selected+'>'+e.nama_sub_kriteria+'</option>';
                                    });
                                }
                                
                                // Set HTML sekali saja dengan selected sudah ada
                                subSelect.html(subOptions);
                                console.log('Sub kriteria options added with selected:', dataAgenda.sub_kriteria_id);
                                
                                // Init Select2 setelah HTML complete
                                subSelect.select2({
                                    placeholder: 'Pilih Sub Kriteria',
                                    allowClear: true,
                                    width: '100%'
                                });
                                
                                // Load item sub kriteria
                                if (dataAgenda.sub_kriteria_id) {
                                    console.log('Loading item sub kriteria untuk sub:', dataAgenda.sub_kriteria_id);
                                    $.ajax({
                                        url: '/get-item-sub-kriteria/' + dataAgenda.sub_kriteria_id,
                                        type: 'GET',
                                        success: function (itemList) {
                                            console.log('Item sub kriteria loaded:', itemList.length, 'items');
                                            
                                            let itemSelect = $('#item_sub_kriteria');
                                            
                                            // Destroy Select2 dulu
                                            if (itemSelect.hasClass('select2-hidden-accessible')) {
                                                itemSelect.select2('destroy');
                                            }
                                            
                                            // Build options HTML dengan selected
                                            let itemOptions = '<option value="">Pilih Item Sub Kriteria</option>';
                                            if (itemList.length > 0) {
                                                itemList.forEach(function (e) {
                                                    let selected = (e.id_item_sub_kriteria == dataAgenda.item_sub_kriteria_id) ? 'selected' : '';
                                                    itemOptions += '<option value="'+e.id_item_sub_kriteria+'" '+selected+'>'+e.nama_item_sub_kriteria+'</option>';
                                                });
                                            }
                                            
                                            // Set HTML sekali saja
                                            itemSelect.html(itemOptions);
                                            console.log('Item sub kriteria options added with selected:', dataAgenda.item_sub_kriteria_id);
                                            
                                            // Init Select2
                                            itemSelect.select2({
                                                placeholder: 'Pilih Item Sub Kriteria',
                                                allowClear: true,
                                                width: '100%'
                                            });
                                            
                                            // Reset flag
                                            setTimeout(() => {
                                                isAutoFilling = false;
                                                console.log('Auto-fill completed! Flag reset.');
                                            }, 300);
                                        },
                                        error: function(xhr, status, error) {
                                            console.error('Error loading item sub kriteria:', error);
                                            isAutoFilling = false;
                                        }
                                    });
                                } else {
                                    console.log('Sub kriteria ID tidak ada');
                                    isAutoFilling = false;
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error loading sub kriteria:', error);
                                isAutoFilling = false;
                            }
                        });
                    } else {
                        console.log('Kategori ID tidak ada');
                        isAutoFilling = false;
                    }

                    console.log('Form filled successfully!');
                } else {
                    console.error('Invalid response format');
                    alert('Data tidak dapat diambil dari Agenda');
                    clearForm();
                    isAutoFilling = false;
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                alert('Gagal mengambil data: ' + error);
                clearForm();
                isAutoFilling = false;
            }
        });
    } else {
        clearForm();
    }
});

    // ===============================
    // RESET SAAT MODAL DITUTUP
    // ===============================
    $('#ModalCreateKeluar').on('hidden.bs.modal', function () {
        let modal = $(this);

        modal.find('select').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            this.selectedIndex = 0;
        });
        
        clearForm();
        isAutoFilling = false; // Reset flag
    });

    // ===============================
    // AUTO PILIH BANK BERDASARKAN URAIAN
    // ===============================
    const bankMap = {
        "81029155533": "81029155533 - PPPBB",
        "81029155531": "81029155531 - UGKB",
        "81029155528": "81029155528 - GUNME",
        "81029155527": "81029155527 - PAGUN",
        "81029155526": "81029155526 - GUMAS",
        "81029155525": "81029155525 - RIMBA",
        "81029155524": "81029155524 - PARBA",
        "81029155523": "81029155523 - SINTANG",
        "81029155522": "81029155522 - NGABANG",
        "81029155521": "81029155521 - PANGA",
        "81029155520": "81029155520 - PARINDU",
        "81029155519": "81029155519 - PAPAR",
        "81029155518": "81029155518 - BAYAN",
        "81029155517": "81029155517 - PAKEM",
        "81029155530": "81029155530 - UGKST",
        "81029155516": "81029155516 - DASAL",
        "81029155515": "81029155515 - TAMBA",
        "81029155514": "81029155514 - PAMUKAN",
        "81029155513": "81029155513 - PAPAM",
        "81029155512": "81029155512 - BALIN",
        "81029155511": "81029155511 - PELAIHARI",
        "81029155510": "81029155510 - PALAI",
        "81029155509": "81029155509 - KUMAI",
        "81029155532": "81029155532 - PRYBB",
        "81029155529": "81029155529 - UGKT",
        "81029155508": "81029155508 - TABARA",
        "81029155507": "81029155507 - TAJATI",
        "81029155506": "81029155506 - PANDAWA",
        "81029155505": "81029155505 - PALPI",
        "81029155504": "81029155504 - PASAM",
        "81029155503": "81029155503 - LONGKALI",
        "81029155502": "81029155502 - DEKAN",
        "81029155501": "81029155501 - RAREN"
    };

    $('#uraian').on('keyup change', function () {
        let uraian = $(this).val().trim();
        if (!uraian) return;

        let kode = uraian.split('|')[0];

        if (bankMap[kode]) {
            let namaBank = bankMap[kode];
            $('#id_bank_tujuan option').each(function () {
                if ($(this).text().includes(namaBank)) {
                    $('#id_bank_tujuan').val($(this).val()).trigger('change');
                }
            });
        }
    });

    // ===============================
    // CLEAR FORM FUNCTION
    // ===============================
    function clearForm() {
        $('#uraian').val('');
        $('#nilai_rupiahh').val('');
        $('#penerima').val('');
        $('#kreditt').val('');
        $('#jenisPembayaran').val('').trigger('change');
        $('#kategori').val('').trigger('change');
        $('#sub_kriteria').html('<option value="">Pilih Sub Kriteria</option>').trigger('change');
        $('#item_sub_kriteria').html('<option value="">Pilih Item Sub Kriteria</option>').trigger('change');
        $('#id_bank_tujuan').val('').trigger('change');
        $('#id_sumber_dana').val('').trigger('change');
    }

    // ===============================
    // VALIDASI KREDIT VS NILAI AJUAN
    // ===============================
    $(document).on('input', '#nilai_rupiahh', function () {
        let kredit = parseInt($('#kreditt').val().replace(/\./g,'')) || 0;
        let nilai = parseInt($(this).val().replace(/\./g,'')) || 0;

        if (kredit > 0 && kredit < nilai) {
            alert('Total kredit kurang dari nilai ajuan');
            $(this).val('');
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
$(document).on('input', '#kreditt, .kredit-split', function () {
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
                        class="select2 split-kategori" required>
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
                        class="select2 split-sub-kriteria" required>
                    <option value="">Pilih Sub Kriteria</option>
                </select>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <label>Item Sub Kriteria</label>
                <select name="split[item_sub_kriteria][]" 
                        class="select2 split-item-sub-kriteria" required>
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
//             dropdownParent: $('#ModalCreateKeluar'),
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
