@extends('layouts/index')
@section('content')

<div class="container-fluid mt-3">

    <!-- Page Header -->
    <div class="page-header mb-4">
        <h1 class="page-title">Bank <span class="text-primary-custom">Masuk</span></h1>
        <p class="page-subtitle text-muted">Data Bank Masuk (Uang Masuk) PTPN IV Regional V</p>
    </div>

    <!-- Action Bar -->

    <!-- Table Card -->
     <div class="row">
        <div class="col-12">
            <div class="card rounded-3 border-0 shadow-sm" >
                <div class="card-body">
                    <div class="justify-content-start align-items-center mb-3">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-start">
                                <button class="btn bg-danger btn-sm d-flex align-items-center gap-1" id="deleteAllSelectedRecord" style="background-color:#dc3545; color: white;">
                                    <i class="bi bi-trash"></i>
                                    <span class="d-none d-sm-inline">Delete All</span>
                                </button>
                                
                                <button class="btn btn-sm d-flex align-items-center gap-1  btn-outline-success" 
                                    style="background-color:#FF7518; color: white;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ModalImportFileExcelMasuk">
                                    <i class="bi bi-file-earmark-spreadsheet"></i>
                                    <span class="d-none d-sm-inline">Import Excel</span>
                                </button>
                                
                                <a href="{{ url('/bank-masuk/export_excel')}}" class="btn  btn-outline-success"><i class="bi bi-file-earmark-spreadsheet"></i>Download Excel</a>
                                <a href="{{ url('/bank-masuk/view/pdf')}}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-printer"></i>Download PDF</a>
                                
                                <button 
                                class="btn-tambah btn-success rounded-2 shadow-sm border-0"
                                data-bs-toggle="modal"
                                data-bs-target="#ModalCreateMasuk">
                                <i class="bi bi-database-fill-add"></i> Tambah
                            </button>
                            </div>
                        </div>
                    </div>
                    <hr class="mt-0">
                    <!-- Desktop Table -->
                    <div class="table-wrapper d-none d-xl-block" id="printArea">
                            @include('cash_bank.exportExcel.excelMasuk')
                            {{-- atau @include('cash_bank.exportExcel.excelMasuk', ['data' => $data]) --}}
                    </div>
                    
                   
                    <!-- Mobile/Tablet Card View -->
                    <div class="d-xl-none">
                        @foreach ($data as $index => $row)
                        <div class="card mb-3 shadow-sm" id="employee_ids{{ $row->id_bank_masuk}}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input checkbox_ids" type="checkbox" 
                                            value="{{ $row->id_bank_masuk }}" name="ids">
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
                                        <small class="text-muted d-block">Nilai Rupiah</small>
                                        <strong class="text-success">@currency($row->nilai_rupiah)</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Debet</small>
                                        <strong class="text-info">@currency($row->debet)</strong>
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

                                <div class="accordion accordion-flush" id="detail{{ $row->id_bank_masuk }}">
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed px-0 py-2" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse{{ $row->id_bank_masuk }}">
                                                <small>Detail Lainnya</small>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $row->id_bank_masuk }}" 
                                            class="accordion-collapse collapse" 
                                            data-bs-parent="#detail{{ $row->id_bank_masuk }}">
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
                                <div class="d-flex justify-content-end">
                                    <button
                                        class="btn btn-edit bg-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#edit"

                                        data-id="{{ $row->id_bank_masuk }}"
                                        data-agenda="{{ $row->agenda_tahun }}"
                                        data-penerima="{{ $row->penerima }}"
                                        data-uraian="{{ $row->uraian }}"
                                        data-tanggal="{{ $row->tanggal }}"
                                        data-bank="{{ $row->id_bank_tujuan }}"
                                        data-sumber="{{ $row->id_sumber_dana }}"
                                        data-kategori="{{ $row->id_kategori_kriteria }}"
                                        data-jenis="{{ $row->id_jenis_pembayaran }}"
                                        data-debet="{{ $row->debet }}"
                                        >
                                        <i class="bi bi-pencil-square text-white"></i>
                                        </button>
                                    <form action="{{ route('bank-masuk.destroy', $row->id_bank_masuk) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn bg-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @include('cash_bank.modal.edit')

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('cash_bank.modal.tambahMasuk')
@include('cash_bank.modal.importExcelMasuk')


<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('edit');
    const form = document.getElementById('formEdit');

    if (modal && form) {
        modal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            
            // Update form action
            form.action = `/bank-masuk/${btn.dataset.id}`;

            // Populate form fields
            const fields = {
                'agenda_tahun': btn.dataset.agenda,
                'penerima': btn.dataset.penerima,
                'uraian': btn.dataset.uraian,
                'tanggal': btn.dataset.tanggal,
                'id_bank_tujuan': btn.dataset.bank,
                'id_sumber_dana': btn.dataset.sumber,
                'id_kategori_kriteria': btn.dataset.kategori,
                'id_jenis_pembayaran': btn.dataset.jenis,
                'debet': btn.dataset.debet
            };

            Object.keys(fields).forEach(key => {
                const input = modal.querySelector(`[name="${key}"]`);
                if (input) input.value = fields[key];
            });
        });
    }
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
                url: "{{ route('bank-masuk.delete') }}",
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
// $(document).ready(function(){
//     // Select All Checkbox
//     $("#select_all_ids").on('click', function(){
//         $('.checkbox_ids').prop('checked', $(this).prop('checked'));
//     });

//     // Delete All Selected Records
//     $('#deleteAllSelectedRecord').on('click', function(e){
//         e.preventDefault();

//         let all_ids = [];
//         $('input[name="ids"]:checked').each(function(){
//             all_ids.push($(this).val());
//         });

//         console.log('=== DELETE ALL DEBUG ===');
//         console.log('Selected IDs:', all_ids);
//         console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
//         console.log('Route URL:', '/bank-masuk/delete-selected');

//         if(all_ids.length === 0){
//             alert('Pilih data terlebih dahulu!');
//             return;
//         }

//         if(!confirm('Yakin ingin menghapus ' + all_ids.length + ' data terpilih?')) {
//             return;
//         }

//         $.ajax({
//             url: '/bank-masuk/delete-selected',
//             type: 'POST', // Ganti ke POST
//             data: {
//                 _method: 'DELETE', // Method spoofing
//                 ids: all_ids,
//                 _token: $('meta[name="csrf-token"]').attr('content')
//             },
//             dataType: 'json',
//             beforeSend: function() {
//                 console.log('Sending request...');
//                 $('#deleteAllSelectedRecord').prop('disabled', true)
//                     .html('<span class="spinner-border spinner-border-sm"></span> Menghapus...');
//             },
//             success: function(response){
//                 console.log('=== SUCCESS ===');
//                 console.log('Response:', response);
//                 alert(response.success || 'Data berhasil dihapus!');
//                 location.reload();
//             },
//             error: function(xhr, status, error){
//                 console.log('=== ERROR ===');
//                 console.log('XHR Status:', xhr.status);
//                 console.log('XHR Response:', xhr.responseText);
//                 console.log('Status:', status);
//                 console.log('Error:', error);
                
//                 let errorMessage = 'Terjadi kesalahan saat menghapus data';
                
//                 try {
//                     if(xhr.responseJSON) {
//                         console.log('Response JSON:', xhr.responseJSON);
//                         errorMessage = xhr.responseJSON.message || xhr.responseJSON.error || errorMessage;
//                     }
//                 } catch(e) {
//                     console.log('Failed to parse JSON:', e);
//                 }
                
//                 alert(errorMessage + '\n\nCek Console (F12) untuk detail error.');
//                 $('#deleteAllSelectedRecord').prop('disabled', false)
//                     .html('<i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Delete All</span>');
//             }
//         });
//     });
// });
</script>
<style>
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
}

/* Reset table untuk menghilangkan gap */
table {
    border-collapse: collapse;
    border-spacing: 0;
    width: 100%;
    table-layout: auto;
}

/* Style untuk semua cell */
/* td, th {
    border: 1px solid #e2e2e2;
    padding: 12px;
    white-space: nowrap;
    min-width: 80px;
} */

/* Header background */
th {
    background-color: #d4e9f7;
    font-weight: 600;
    text-align: left;
}

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
    left: 45px;
    z-index: 2;
    background-color: inherit;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    min-width: 60px;
}

/* STICKY COLUMNS - AGENDA */
td:nth-child(3), 
th:nth-child(3) {
    position: sticky;
    left: 105px;
    z-index: 2;
    background-color: inherit;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    min-width: 100px;
}

/* STICKY COLUMNS - NO BUKTI */
td:nth-child(4), 
th:nth-child(4) {
    position: sticky;
    left: 205px;
    z-index: 2;
    background-color: inherit;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    min-width: 80px;
}

/* STICKY COLUMNS - TANGGAL */
td:nth-child(5), 
th:nth-child(5) {
    position: sticky;
    left: 285px;
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
.table-wrapper::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #555;
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
.table-responsive {
    -webkit-overflow-scrolling: touch;
}

.font-monospace {
    font-family: 'Courier New', monospace;
}


</style>
@endsection