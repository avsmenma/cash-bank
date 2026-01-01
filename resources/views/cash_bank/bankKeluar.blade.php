@extends('layouts/index')
@section('content')

<div class="container-fluid mt-4 px-3 px-md-4">

    <!-- Title Section -->
    <div class="mb-4">
        <h1 class="tittle mb-2 fs-3 fs-md-2 fw-bold">Bank<span style="color: #FF7518"> Keluar</span></h1>
        <small class="text-muted d-block">Ini data Bank Keluar (Uang Keluar) PTPN IV Regional V</small>
    </div>

    <!-- Search + Action Buttons -->
    <!--  -->

    <!-- Table Section -->
    <div class="row">
        <div class="col-12">
            <div class="card rounded-3 border-0 shadow-sm" id="printArea">
                <div class="card-body">
                    <div class="justify-content-start align-items-center mb-3">
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn bg-danger btn-sm d-flex align-items-center gap-1" id="deleteAllSelectedRecord" style="background-color:#dc3545; color: white;">
                                    <i class="bi bi-trash"></i>
                                    <span class="d-none d-sm-inline">Delete All</span>
                                </button>
                                
                                <button class="btn btn-sm d-flex align-items-center gap-1" 
                                    style="background-color:#FF7518; color: white;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ModalImportFileExcel">
                                    <i class="bi bi-database-fill-add"></i>
                                    <span class="d-none d-sm-inline">Import Excel</span>
                                </button>
                                <a href="{{ url('/bank-keluar/export_excel')}}" class="btn btn-outline-success"><i class="bi bi-printer"></i>
                                        Download Excel
                                    </a>
                                 <a href="{{ url('/bank-keluar/view/pdf')}}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-printer"></i>Download PDF</a>
                                <!-- <button onclick="window.print()" 
                                    class="btn bg-success btn-sm d-flex align-items-center gap-1" style=" color: white;">
                                    <i class="bi bi-printer"></i>
                                    <span class="d-none d-sm-inline">Export PDF</span>
                                </button> -->
                                
                                <button class="btn bg-primary btn-sm d-flex align-items-center gap-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ModalCreate" style=" color: white;">
                                    <i class="bi bi-database-fill-add"></i>
                                    <span>Tambah</span>
                                </button>
                            </div>
                    </div>
                    <hr class="mt-0">
                    
                    <!-- Desktop Table -->
                    <div class="table-wrapper d-none d-xl-block">
                        @include('cash_bank.exportExcel.excelKeluar', ['data' => $data])
                    </div>

                    <!-- Mobile/Tablet Card View -->
                    <div class="d-xl-none">
                        @forelse ($data as $index => $row)
                        <div class="card mb-3 shadow-sm" id="employee_ids{{ $row->id_bank_keluar}}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input checkbox_ids" type="checkbox" 
                                            value="{{ $row->id_bank_keluar }}" name="ids">
                                        <label class="form-check-label fw-bold">
                                            #{{ $index + 1 }} - {{ $row->agenda_tahun }}
                                        </label>
                                    </div>
                                    <span class="badge bg-primary">{{ $row->tanggal }}</span>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Penerima</small>
                                        <strong>{{ $row->penerima }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Kredit</small>
                                        <strong class="text-info">@currency($row->kredit)</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Nilai Rupiah</small>
                                        <strong class="text-success">@currency($row->nilai_rupiah)</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Sumber Dana</small>
                                        <strong>{{ $row->sumberDana->nama_sumber_dana ?? '-' }}</strong>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted d-block">Uraian</small>
                                    <p class="mb-0">{{ $row->uraian }}</p>
                                </div>

                                <div class="accordion accordion-flush" id="detail{{ $row->id_bank_keluar }}">
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed px-0 py-2" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse{{ $row->id_bank_keluar }}">
                                                <small>Detail Lainnya</small>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $row->id_bank_keluar }}" 
                                            class="accordion-collapse collapse" 
                                            data-bs-parent="#detail{{ $row->id_bank_keluar }}">
                                            <div class="accordion-body px-0">
                                                <div class="row g-2">
                                                    <div class="col-12">
                                                        <small class="text-muted">Bank Tujuan:</small>
                                                        <span>{{ $row->bankTujuan->nama_tujuan ?? '-' }}</span>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted">Kriteria:</small>
                                                        <span>{{ $row->kategori->nama_kriteria ?? '-' }}</span>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted">Sub Kriteria:</small>
                                                        <span>{{ $row->subKriteria->nama_sub_kriteria ?? '-' }}</span>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted">Item Sub Kriteria:</small>
                                                        <span>{{ $row->itemSubKriteria->nama_item_sub_kriteria ?? '-' }}</span>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted">Jenis Pembayaran:</small>
                                                        <span>{{ $row->jenisPembayaran->nama_jenis_pembayaran ?? '-' }}</span>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted">Keterangan:</small>
                                                        <span>{{ $row->keterangan }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editKeluar"
                                        data-id="{{ $row->id_bank_keluar }}"
                                        data-agenda="{{ $row->agenda_tahun }}"
                                        data-penerima="{{ $row->penerima }}"
                                        data-uraian="{{ $row->uraian }}"
                                        data-tanggal="{{ $row->tanggal }}"
                                        data-bank="{{ $row->id_bank_tujuan }}"
                                        data-sumber="{{ $row->id_sumber_dana }}"
                                        data-kategori="{{ $row->id_kategori_kriteria }}"
                                        data-sub="{{ $row->id_sub_kriteria }}"
                                        data-item="{{ $row->id_item_sub_kriteria }}"
                                        data-jenis="{{ $row->id_jenis_pembayaran }}"
                                        data-kredit="{{ $row->kredit }}">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <form action="{{ route('bank-keluar.destroy', $row->id_bank_keluar) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <p class="text-muted">Data yang anda cari tidak ada</p>
                        </div>
                        @endforelse
                    </div>

                    @include('cash_bank.modal.editKeluar')

                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).on('click', '[data-bs-target="#editKeluar"]', function () {

    const btn   = this;
    const modal = $('#editKeluar');

    // reset dulu (PENTING)
    modal.find('form')[0].reset();
    $('#sub_kriteria').empty();
    $('#item_sub_kriteria').empty();

    // set action
    modal.find('form').attr('action', `/bank-keluar/${btn.dataset.id}`);

    // set field
    modal.find('[name="agenda_tahun"]').val(btn.dataset.agenda);
    modal.find('[name="penerima"]').val(btn.dataset.penerima);
    modal.find('[name="uraian"]').val(btn.dataset.uraian);
    modal.find('[name="tanggal"]').val(btn.dataset.tanggal);
    modal.find('[name="id_bank_tujuan"]').val(btn.dataset.bank);
    modal.find('[name="id_sumber_dana"]').val(btn.dataset.sumber);
    modal.find('[name="id_jenis_pembayaran"]').val(btn.dataset.jenis);
    modal.find('[name="kredit"]').val(btn.dataset.kredit);
    modal.find('[name="nilai_rupiah"]').val(btn.dataset.nilai);
    modal.find('[name="keterangan"]').val(btn.dataset.keterangan);
    modal.find('[name="uraian"]').val(btn.dataset.uraian);

    const kategoriID = btn.dataset.kategori;
    const subID      = btn.dataset.sub;
    const itemID     = btn.dataset.item;

    $('#kategori').val(kategoriID);

    // load sub
    $.get('/get-sub-kriteria/' + kategoriID, function (subs) {
        let opt = '<option value="">Pilih Sub</option>';
        subs.forEach(s => {
            opt += `<option value="${s.id_sub_kriteria}">${s.nama_sub_kriteria}</option>`;
        });
        $('#sub_kriteria').html(opt).val(subID);

        // load item
        $.get('/get-item-sub-kriteria/' + subID, function (items) {
            let opt2 = '<option value="">Pilih Item</option>';
            items.forEach(i => {
                opt2 += `<option value="${i.id_item_sub_kriteria}">${i.nama_item_sub_kriteria}</option>`;
            });
            $('#item_sub_kriteria').html(opt2).val(itemID);
        });
    });

    modal.modal('show');
});

$(function(e){
        $("#select_all_ids").click(function(){
            $('.checkbox_ids').prop('checked',$(this).prop('checked'));
        });

        $('#deleteAllSelectedRecord').click(function(e){
            e.preventDefault();

            let all_ids = [];
            $('input[name="ids"]:checked').each(function(){
                all_ids.push($(this).val());
            });

            if(all_ids.length === 0){
                alert('Pilih data terlebih dahulu!');
                return;
            }

            if(!confirm('Yakin ingin menghapus ' + all_ids.length + ' data terpilih?')) {
                return;
            }

            $.ajax({
                url: "{{ route('bank-keluar.delete') }}",
                type: "DELETE",
                data: {
                    ids: all_ids,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res){
                    all_ids.forEach(id => {
                        $('#employee_ids'+id).remove();
                    });
                    alert(res.success);
                    location.reload();
                },
                error: function(xhr){
                    alert('Terjadi kesalahan saat menghapus data');
                }
            });
        });
    });

</script>

<style>
/* Print Styles */
@media print {
    body * {
        visibility: hidden;
    }

    #printArea, #printArea * {
        visibility: visible;
    }

    #printArea button,
    #printArea .btn,
    #printArea .accordion-button {
        display: none !important;
    }

    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .d-xl-none {
        display: none !important;
    }

    .d-none.d-xl-block {
        display: block !important;
    }
}

/* Enhanced Styles */
.search-box .input-group-text {
    border-radius: 0.375rem 0 0 0.375rem;
}

.search-box .form-control {
    border-radius: 0 0.375rem 0.375rem 0;
}

.search-box .form-control:focus {
    box-shadow: none;
    border-color: #dee2e6;
}

/* fixed kolom dan header table */



th{
    position: sticky;
    top: 0px;

}
/* Table Wrapper dengan Sticky Columns */
.table-wrapper {
    overflow-x: auto;
    /* width: 80%; */
    /* margin: 20px; */

}

/* Reset table untuk menghilangkan gap */
table {
    border-collapse: collapse;
    border-spacing: 0;
    width: 100%;
    table-layout: auto;
}

/* Style untuk semua cell */
td, th {
    border: 1px solid #e2e2e2;
    padding: 12px;
    white-space: nowrap;
    min-width: 80px;
}

/* Header background */
/* th {
    background-color: #d4e9f7;
    font-weight: 600;
    text-align: left;
} */

/* Alternate row colors */
tbody tr:nth-child(even) {
    background-color: #f8f9fc;
}

tbody tr:nth-child(odd) {
    background-color: #ffffff;
}

/* Hover effect */
tbody tr:hover {
    background-color: #E8EDF3;
}

/* STICKY COLUMNS - CHECKBOX */
td:first-child, 
th:first-child {
    position: sticky;
    left: 0;
    z-index: 2;
    background-color: inherit;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    min-width: 50px;
    text-align: center;
}

/* STICKY COLUMNS - NO */
td:nth-child(2), 
th:nth-child(2) {
    position: sticky;
    left: 50px;
    z-index: 2;
    background-color: inherit;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    min-width: 60px;
}

/* STICKY COLUMNS - AGENDA */
td:nth-child(3), 
th:nth-child(3) {
    position: sticky;
    left: 110px;
    z-index: 2;
    background-color: inherit;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    min-width: 100px;
}

/* STICKY COLUMNS - NO BUKTI */
td:nth-child(4), 
th:nth-child(4) {
    position: sticky;
    left: 210px;
    z-index: 2;
    background-color: inherit;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    min-width: 80px;
}

/* STICKY COLUMNS - TANGGAL */
td:nth-child(5), 
th:nth-child(5) {
    position: sticky;
    left: 290px;
    z-index: 2;
    background-color: inherit;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    min-width: 120px;
}

/* Header sticky harus lebih tinggi z-index */
thead th:first-child,
thead th:nth-child(2),
thead th:nth-child(3),
thead th:nth-child(4),
thead th:nth-child(5) {
    z-index: 3;
    background-color: #d4e9f7 !important;
}

/* Maintain background untuk even rows pada sticky columns */
tbody tr:nth-child(even) td:first-child,
tbody tr:nth-child(even) td:nth-child(2),
tbody tr:nth-child(even) td:nth-child(3),
tbody tr:nth-child(even) td:nth-child(4),
tbody tr:nth-child(even) td:nth-child(5) {
    background-color: #f8f9fc;
}

/* Maintain background untuk odd rows pada sticky columns */
tbody tr:nth-child(odd) td:first-child,
tbody tr:nth-child(odd) td:nth-child(2),
tbody tr:nth-child(odd) td:nth-child(3),
tbody tr:nth-child(odd) td:nth-child(4),
tbody tr:nth-child(odd) td:nth-child(5) {
    background-color: #ffffff;
}

/* Hover effect untuk sticky columns */
tbody tr:hover td:first-child,
tbody tr:hover td:nth-child(2),
tbody tr:hover td:nth-child(3),
tbody tr:hover td:nth-child(4),
tbody tr:hover td:nth-child(5) {
    background-color: #E8EDF3;
}

/* Checkbox styling */
input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* Scrollbar styling (opsional) */
/* .table-wrapper::-webkit-scrollbar {
    width: 10px;
    height: 10px;
} */

/* .table-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
} */

/* .table-wrapper::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
} */

/* .table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #555;
} */
/* th:nth-child(1){
    width: 160px;height:20px; position: absolute; z-index: 1;
}

td:nth-child(1){
    box-shadow:5px 0 3px -2px #ccc;
    width: 160px;
    position: fixed;
    position: absolute;
    z-index: 1;
}
td:nth-child(2){
    box-shadow:5px 0 3px -2px #ccc;
    width: 160px;
    position: fixed;
    position: absolute;
    z-index: 1;
}
td:nth-child(3){
    box-shadow:5px 0 3px -2px #ccc;
    width: 160px;
    position: fixed;
    position: absolute;
    z-index: 1;
}
td:nth-child(4){
    box-shadow:5px 0 3px -2px #ccc;
    width: 160px;
    position: fixed;
    position: absolute;
    z-index: 1;
}

td:nth-child(2){
    width: 200px;
}

 */

/* 
.table > :not(caption) > * > * {
    padding: 0.75rem 0.5rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn {
    transition: all 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Mobile Card Styles */
.accordion-button:not(.collapsed) {
    background-color: transparent;
    color: inherit;
}

.accordion-button:focus {
    box-shadow: none;
}



/* Responsive adjustments */
@media (max-width: 576px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
}

@media only screen and (max-width:700px){
    table{
        width: 1000px;
    }
    tbody td{
        width: 100px;
    }
    td:nth-child(1){
        width: 700px;
    }
}

/* Table responsive improvements */
/* .table-responsive {
    -webkit-overflow-scrolling: touch;
} */

.font-monospace {
    font-family: 'Courier New', monospace;
}
</style>

{{-- MODAL CREATE & IMPORT --}}
@include('cash_bank.modal.create')
@include('cash_bank.modal.importExcel')

@endsection