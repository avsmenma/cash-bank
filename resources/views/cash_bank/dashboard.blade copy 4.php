{{-- Filter Section - Tambahkan class no-print --}}
<section class="content no-print">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-end flex-wrap">
                <div class="d-flex gap-2 flex-wrap">
                  <div class="form-group m-2">
                    <label>Tahun:</label>
                    <select class="form-control select2" id="tahunGabungan" style="min-width: 120px;">
                        @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                            <option value="{{ $t }}" {{ $t == ($tahun ?? date('Y')) ? 'selected' : '' }}>{{ $t }}</option>
                        @endfor
                    </select>
                  </div>
                  <div class="form-group m-2">
                    <label>Dari Bulan:</label>
                    <select class="form-control" id="bulanDari" style="min-width: 120px;">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $i == ($bulanDari ?? 1) ? 'selected' : '' }}>
                                {{ ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$i] }}
                            </option>
                        @endfor
                    </select>
                  </div>
                  <div class="form-group m-2">
                    <label>Sampai Bulan:</label>
                    <select class="form-control" id="bulanSampai" style="min-width: 120px;">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $i == ($bulanSampai ?? 12) ? 'selected' : '' }}>
                                {{ ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$i] }}
                            </option>
                        @endfor
                    </select>
                  </div>
                  <div class="form-group m-2">
                      <label>&nbsp;</label>
                      <button class="btn btn-primary btn-block" id="filterGabungan">
                          <i class="fas fa-filter"></i> Filter
                      </button>
                  </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" id="btnPrint">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="btn btn-outline-success" id="btnExcel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Content Section --}}
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <div class="col-12">
            <h5>CashFlow Pembayaran (Penerima, Dropping, Pembayaran)</h5>
            
            {{-- Content Area untuk AJAX --}}
            <div id="gabungan-content">
                @include('cash_bank.dashbordPertama')
            </div>
          </div>
        </div>
        
        {{-- Loading Overlay --}}
        <div class="overlay" id="loading" style="display: none;">
          <i class="fas fa-2x fa-sync fa-spin"></i>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Filter button click
    $('#filterGabungan').click(function() {
        loadGabungan();
    });
    
    // Enter key
    $('#tahunGabungan, #bulanDari, #bulanSampai').keypress(function(e) {
        if(e.which == 13) {
            loadGabungan();
        }
    });
    
    // Print
    $('#btnPrint').click(function() {
        window.print();
    });
    
    // Excel
    $('#btnExcel').click(function() {
        var tahun = $('#tahunGabungan').val();
        var bulanDari = $('#bulanDari').val();
        var bulanSampai = $('#bulanSampai').val();
        
        window.location.href = '{{ route("dashboard") }}?export=excel&tahun=' + tahun + '&bulan_dari=' + bulanDari + '&bulan_sampai=' + bulanSampai;
    });
    
    function loadGabungan() {
        var tahun = $('#tahunGabungan').val();
        var bulanDari = $('#bulanDari').val();
        var bulanSampai = $('#bulanSampai').val();
        
        if(parseInt(bulanDari) > parseInt(bulanSampai)) {
            alert('Bulan dari tidak boleh lebih besar dari bulan sampai!');
            return;
        }
        
        $('#loading').show();
        $('#gabungan-content').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i><br><br>Memuat data...</div>');
        
        $.ajax({
            url: '{{ route("dashboard") }}',
            type: 'GET',
            data: {
                tahun: tahun,
                bulan_dari: bulanDari,
                bulan_sampai: bulanSampai
            },
            success: function(response) {
                $('#loading').hide();
                $('#gabungan-content').html(response);
            },
            error: function(xhr, status, error) {
                $('#loading').hide();
                $('#gabungan-content').html(
                    '<div class="alert alert-danger">' +
                    '<i class="fas fa-exclamation-triangle"></i> Gagal memuat data. ' + error +
                    '</div>'
                );
            }
        });
    }
});
</script>

<style>
@media print {
    .no-print,
    .breadcrumb,
    .btn,
    .overlay {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    #cashflow-table {
        font-size: 8px !important;
    }
}
</style>
@endpush