@extends('layouts.index')

@section('content')
<div class="container-fluid m-3">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Dashboard Pembayaran</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard Pembayaran</li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.pembayaran.index') }}">Dashboard Versi 2</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Filter & Content Section -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link active" id="pd-tab" href="#activity" data-toggle="tab">CashFlow PD</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pvd-tab" href="#timeline" data-toggle="tab">CashFlow PvD</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- TAB 1: CashFlow PD -->
                            <div class="active tab-pane" id="activity">
                                <div class="row align-items-end mb-3">
                                    <div class="form-group mb-0 ml-1">
                                        <label>Tahun:</label>
                                        <select name="tahun" id="tahunPD" class="form-control select2">
                                            @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                                <option value="{{ $t }}" {{ $t == date('Y') ? 'selected' : '' }}>
                                                    {{ $t }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-0 ml-1">
                                        <label>Dari Bulan:</label>
                                        <select name="bulan_dari" id="bulanDariPD" class="form-control">
                                            @foreach([
                                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                            ] as $noBulan => $namaBulan)
                                                <option value="{{ $noBulan }}" {{ $noBulan == 1 ? 'selected' : '' }}>
                                                    {{ $namaBulan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-0 ml-1">
                                        <label>Sampai Bulan:</label>
                                        <select name="bulan_sampai" id="bulanSampaiPD" class="form-control">
                                            @foreach([
                                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                            ] as $noBulan => $namaBulan)
                                                <option value="{{ $noBulan }}" {{ $noBulan == 12 ? 'selected' : '' }}>
                                                    {{ $namaBulan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-0 ml-1">
                                        <button type="button" id="filterPD" class="btn btn-primary btn-block">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>

                                    <div class="form-group mb-0 ml-1">
                                        <button type="button" id="resetPD" class="btn btn-secondary btn-block">
                                            <i class="fas fa-redo"></i> Reset
                                        </button>
                                    </div>
                                    
                                    <div class="form-group mb-0 ml-1">
                                        <a href="{{route('dashboard.excel')}}">
                                            <button type="button" class="btn btn-outline-primary btn-block">
                                                <i class="fas fa-file-excel"></i> EXCEL
                                            </button>
                                        </a>    
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Content for CashFlow PD -->
                                <div id="pd-content">
                                    <div class="text-center p-3">
                                        <i class="fas fa-spinner fa-spin"></i> Memuat data...
                                    </div>
                                </div>
                            </div>
                            
                            <!-- TAB 2: CashFlow PvD -->
                            <div class="tab-pane" id="timeline">
                                <div class="row align-items-end mb-3">
                                    <div class="form-group mb-0 ml-1">
                                        <label>Tahun:</label>
                                        <select name="tahunPvd" id="tahunPvD" class="form-control select2">
                                            @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                                <option value="{{ $t }}" {{ $t == date('Y') ? 'selected' : '' }}>
                                                    {{ $t }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="form-group mb-0 ml-1">
                                        <label>Dari Bulan:</label>
                                        <select name="bulan_dariPvd" id="bulanDariPvD" class="form-control">
                                            @foreach([
                                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                            ] as $noBulan => $namaBulan)
                                                <option value="{{ $noBulan }}" {{ $noBulan == 1 ? 'selected' : '' }}>
                                                    {{ $namaBulan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-0 ml-1">
                                        <label>Sampai Bulan:</label>
                                        <select name="bulan_sampaiPvd" id="bulanSampaiPvD" class="form-control">
                                            @foreach([
                                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                            ] as $noBulan => $namaBulan)
                                                <option value="{{ $noBulan }}" {{ $noBulan == 12 ? 'selected' : '' }}>
                                                    {{ $namaBulan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-0 ml-1">
                                        <button type="button" id="filterPvD" class="btn btn-primary btn-block">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>

                                    <div class="form-group mb-0 ml-1">
                                        <button type="button" id="resetPvD" class="btn btn-secondary btn-block">
                                            <i class="fas fa-redo"></i> Reset
                                        </button>
                                    </div>

                        
                                    
                                    <div class="form-group mb-0 ml-1">
                                        <a href="{{route('dashboard.excelPvd')}}">
                                            <button type="button" class="btn btn-outline-primary btn-block">
                                                <i class="fas fa-file-excel"></i> EXCEL
                                            </button>
                                        </a>    
                                    </div>
                                </div>
                                <hr>
                                
                                <!-- Content for CashFlow PvD -->
                                <div id="pvd-content">
                                    <div class="text-center p-3">
                                        <i class="fas fa-spinner fa-spin"></i> Memuat data...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    console.log('=== Dashboard Script Loaded ===');
    
    // Initialize select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // ===== TAB PD (CashFlow PD) =====
    // Load pertama kali saat halaman dibuka
    loadPD();
    
    // Load saat tab diklik
    $('#pd-tab').on('shown.bs.tab', function () {
        console.log('Tab PD shown');
        loadPD();
    });

    // Filter button PD
    $('#filterPD').click(function() {
        var bulanDari = parseInt($('#bulanDariPD').val());
        var bulanSampai = parseInt($('#bulanSampaiPD').val());
        
        if (bulanDari > bulanSampai) {
            alert('Bulan dari tidak boleh lebih besar dari bulan sampai');
            return false;
        }
        loadPD();
    });

    // Reset button PD
    $('#resetPD').click(function() {
        $('#tahunPD').val({{ date('Y') }}).trigger('change');
        $('#bulanDariPD').val(1);
        $('#bulanSampaiPD').val(12);
        loadPD();
    });

    function loadPD() {
        var tahun = $('#tahunPD').val();
        var bulanDari = $('#bulanDariPD').val();
        var bulanSampai = $('#bulanSampaiPD').val();
        
        console.log('Loading PD:', {tahun, bulanDari, bulanSampai});
        
        $('#pd-content').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
        
        $.ajax({
            url: '{{ route("dashboard.index") }}',
            type: 'GET',
            data: {
                tahun: tahun,
                bulan_dari: bulanDari,
                bulan_sampai: bulanSampai,
                ajax: 1  // Flag untuk request AJAX
            },
            success: function(response) {
                console.log('PD data loaded successfully');
                $('#pd-content').html(response);
            },
            error: function(xhr) {
                console.error('PD load error:', xhr);
                $('#pd-content').html('<div class="alert alert-danger">Gagal memuat data CashFlow PD</div>');
            }
        });
    }

    // ===== TAB PVD (CashFlow PvD) =====
    // Load saat tab diklik
    $('#pvd-tab').on('shown.bs.tab', function () {
        console.log('Tab PvD shown');
        loadPvD();
    });

    // Filter button PvD
    $('#filterPvD').click(function() {
        var bulanDari = parseInt($('#bulanDariPvD').val());
        var bulanSampai = parseInt($('#bulanSampaiPvD').val());
        
        if (bulanDari > bulanSampai) {
            alert('Bulan dari tidak boleh lebih besar dari bulan sampai');
            return false;
        }
        loadPvD();
    });

    // Reset button PvD
    $('#resetPvD').click(function() {
        $('#tahunPvD').val({{ date('Y') }}).trigger('change');
        $('#bulanDariPvD').val(1);
        $('#bulanSampaiPvD').val(12);
        loadPvD();
    });

    function loadPvD() {
        var tahun = $('#tahunPvD').val();
        var bulanDari = $('#bulanDariPvD').val();
        var bulanSampai = $('#bulanSampaiPvD').val();
        
        console.log('Loading PvD:', {tahun, bulanDari, bulanSampai});
        
        $('#pvd-content').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
        
        $.ajax({
            url: '{{ route("dashboard.data2") }}',
            type: 'GET',
            data: {
                tahunPvd: tahun,
                bulan_dariPvd: bulanDari,
                bulan_sampaiPvd: bulanSampai,
                ajax: 1  // Flag untuk request AJAX
            },
            success: function(response) {
                console.log('PvD data loaded successfully');
                $('#pvd-content').html(response);
            },
            error: function(xhr) {
                console.error('PvD load error:', xhr);
                $('#pvd-content').html('<div class="alert alert-danger">Gagal memuat data CashFlow PvD</div>');
            }
        });
    }
});
</script>

{{-- Print Styles --}}
<style>
@media print {
    .no-print,
    .breadcrumb,
    .btn,
    form,
    .card-header,
    .nav-pills,
    select,
    label {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    body {
        background: white !important;
    }
    
    #cashflow-table, #cashflow-table-pvd {
        font-size: 9px !important;
    }
    
    .tab-pane {
        display: block !important;
        opacity: 1 !important;
    }
}
</style>
@endpush
@endsection