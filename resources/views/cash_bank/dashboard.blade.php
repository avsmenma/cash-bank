@extends('layouts.index')

@section('content')
<style>
    /* ===== MODERN DASHBOARD FILTER BAR & TABS ===== */
    .cf-filter-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 22px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        margin-bottom: 20px;
    }

    .cf-filter-wrapper {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .cf-filter-left {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
    }

    /* Segmented Control / Tab Switcher */
    .cf-segmented-tab {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 4px;
        display: inline-flex;
        align-items: center;
        height: 44px;
    }

    .cf-tab-btn {
        background: transparent;
        border: none;
        outline: none !important;
        padding: 0 18px;
        border-radius: 9px;
        font-size: 13.5px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .cf-tab-btn:hover {
        color: #1e293b;
    }

    .cf-tab-btn.active {
        background: #ffffff;
        color: #0f172a;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    }

    /* Filter Input Item */
    .cf-filter-item {
        display: flex;
        flex-direction: column;
        margin-bottom: 0;
    }

    .cf-filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .cf-filter-select {
        border-radius: 10px;
        border: 1.5px solid #cbd5e1;
        height: 44px;
        font-size: 13.5px;
        font-weight: 600;
        color: #1e293b;
        background-color: #ffffff;
        min-width: 120px;
        padding-left: 14px;
        padding-right: 32px;
        cursor: pointer;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .cf-filter-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        outline: none;
    }

    /* Right Action Buttons */
    .cf-filter-right {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .btn-cf-action {
        height: 44px;
        padding: 0 22px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
        border: none;
    }

    .btn-cf-primary {
        background: #1d68f0;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(29, 104, 240, 0.25);
    }

    .btn-cf-primary:hover {
        background: #1754c5;
        box-shadow: 0 4px 10px rgba(29, 104, 240, 0.35);
        transform: translateY(-1px);
    }

    .btn-cf-secondary {
        background: #ffffff;
        color: #334155 !important;
        border: 1.5px solid #cbd5e1;
    }

    .btn-cf-secondary:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a !important;
    }

    .btn-cf-excel {
        background: #ffffff;
        color: #059669 !important;
        border: 1.5px solid #059669;
    }

    .btn-cf-excel:hover {
        background: #ecfdf5;
        border-color: #047857;
        color: #047857 !important;
    }

    .cf-content-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
</style>

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
    
    <!-- Filter & Tab Section -->
    <section class="content">
        <div class="cf-filter-panel cb-fullscreen-hide">
            <div class="cf-filter-wrapper">
                <div class="cf-filter-left">
                    <!-- Segmented Tab Switcher -->
                    <div class="cf-segmented-tab">
                        <button type="button" class="cf-tab-btn active" id="tabBtnPD" data-tab="pd">CashFlow PD</button>
                        <button type="button" class="cf-tab-btn" id="tabBtnPvD" data-tab="pvd">CashFlow PvD</button>
                    </div>

                    <!-- TAHUN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label">TAHUN</label>
                        <select name="tahun" id="filterTahun" class="custom-select cf-filter-select" style="min-width: 110px;">
                            @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                <option value="{{ $t }}" {{ $t == ($tahun ?? date('Y')) ? 'selected' : '' }}>
                                    {{ $t }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    
                    <!-- DARI BULAN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label">DARI BULAN</label>
                        <select name="bulan_dari" id="filterBulanDari" class="custom-select cf-filter-select" style="min-width: 140px;">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $noBulan => $namaBulan)
                                <option value="{{ $noBulan }}" {{ $noBulan == ($bulanDari ?? 1) ? 'selected' : '' }}>
                                    {{ $namaBulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- SAMPAI BULAN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label">SAMPAI BULAN</label>
                        <select name="bulan_sampai" id="filterBulanSampai" class="custom-select cf-filter-select" style="min-width: 140px;">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $noBulan => $namaBulan)
                                <option value="{{ $noBulan }}" {{ $noBulan == ($bulanSampai ?? 12) ? 'selected' : '' }}>
                                    {{ $namaBulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Right Action Buttons -->
                <div class="cf-filter-right">
                    <button type="button" id="btnFilter" class="btn-cf-action btn-cf-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" id="btnReset" class="btn-cf-action btn-cf-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <a id="btnExcel" href="{{ route('dashboard.excel') }}" class="btn-cf-action btn-cf-excel">
                        <i class="far fa-file-excel"></i> Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="row">
            <div class="col-md-12">
                <div class="card cf-content-card">
                    <div class="card-body p-0">
                        <div id="tabPanePD" class="cb-fullscreen-table">
                            <div id="pd-content">
                                <div class="text-center p-3">
                                    <i class="fas fa-spinner fa-spin"></i> Memuat data...
                                </div>
                            </div>
                        </div>
                        <div id="tabPanePvD" class="cb-fullscreen-table" style="display: none;">
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
    </section>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    console.log('=== Dashboard Script Loaded ===');
    
    var activeTab = 'pd';

    function updateExcelLink() {
        var tahun = $('#filterTahun').val();
        var bulanDari = $('#filterBulanDari').val();
        var bulanSampai = $('#filterBulanSampai').val();
        
        if (activeTab === 'pd') {
            var url = '{{ route("dashboard.excel") }}' + '?tahun=' + encodeURIComponent(tahun) + '&bulan_dari=' + encodeURIComponent(bulanDari) + '&bulan_sampai=' + encodeURIComponent(bulanSampai);
            $('#btnExcel').attr('href', url);
        } else {
            var url = '{{ route("dashboard.excelPvd") }}' + '?tahunPvd=' + encodeURIComponent(tahun) + '&bulan_dariPvd=' + encodeURIComponent(bulanDari) + '&bulan_sampaiPvd=' + encodeURIComponent(bulanSampai);
            $('#btnExcel').attr('href', url);
        }
    }

    // Tab Switching
    $('#tabBtnPD').on('click', function() {
        if (activeTab === 'pd') return;
        activeTab = 'pd';
        $('#tabBtnPD').addClass('active');
        $('#tabBtnPvD').removeClass('active');
        $('#tabPanePD').show();
        $('#tabPanePvD').hide();
        updateExcelLink();
        loadPD();
    });

    $('#tabBtnPvD').on('click', function() {
        if (activeTab === 'pvd') return;
        activeTab = 'pvd';
        $('#tabBtnPvD').addClass('active');
        $('#tabBtnPD').removeClass('active');
        $('#tabPanePvD').show();
        $('#tabPanePD').hide();
        updateExcelLink();
        loadPvD();
    });

    // Filter Button Click
    $('#btnFilter').on('click', function() {
        var bulanDari = parseInt($('#filterBulanDari').val());
        var bulanSampai = parseInt($('#filterBulanSampai').val());
        
        if (bulanDari > bulanSampai) {
            alert('Bulan Dari tidak boleh lebih besar dari Bulan Sampai');
            return false;
        }
        
        updateExcelLink();
        if (activeTab === 'pd') {
            loadPD();
        } else {
            loadPvD();
        }
    });

    // Reset Button Click
    $('#btnReset').on('click', function() {
        $('#filterTahun').val({{ date('Y') }});
        $('#filterBulanDari').val(1);
        $('#filterBulanSampai').val(12);
        updateExcelLink();
        if (activeTab === 'pd') {
            loadPD();
        } else {
            loadPvD();
        }
    });

    $('#filterTahun, #filterBulanDari, #filterBulanSampai').on('change', function() {
        updateExcelLink();
    });

    function loadPD() {
        var tahun = $('#filterTahun').val();
        var bulanDari = $('#filterBulanDari').val();
        var bulanSampai = $('#filterBulanSampai').val();
        
        $('#pd-content').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
        
        $.ajax({
            url: '{{ route("dashboard.index") }}',
            type: 'GET',
            data: {
                tahun: tahun,
                bulan_dari: bulanDari,
                bulan_sampai: bulanSampai,
                ajax: 1
            },
            success: function(response) {
                $('#pd-content').html(response);
            },
            error: function(xhr) {
                console.error('PD load error:', xhr);
                $('#pd-content').html('<div class="alert alert-danger p-3">Gagal memuat data CashFlow PD</div>');
            }
        });
    }

    function loadPvD() {
        var tahun = $('#filterTahun').val();
        var bulanDari = $('#filterBulanDari').val();
        var bulanSampai = $('#filterBulanSampai').val();
        
        $('#pvd-content').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
        
        $.ajax({
            url: '{{ route("dashboard.data2") }}',
            type: 'GET',
            data: {
                tahunPvd: tahun,
                bulan_dariPvd: bulanDari,
                bulan_sampaiPvd: bulanSampai,
                ajax: 1
            },
            success: function(response) {
                $('#pvd-content').html(response);
            },
            error: function(xhr) {
                console.error('PvD load error:', xhr);
                $('#pvd-content').html('<div class="alert alert-danger p-3">Gagal memuat data CashFlow PvD</div>');
            }
        });
    }

    // Initial load
    updateExcelLink();
    loadPD();
});
</script>

{{-- Print Styles --}}
<style>
@media print {
    .no-print,
    .breadcrumb,
    .btn,
    .btn-cf-action,
    .cf-filter-panel,
    form,
    .card-header,
    .nav-pills,
    select,
    label {
        display: none !important;
    }
    
    .card, .cf-content-card {
        border: none !important;
        box-shadow: none !important;
    }
    
    body {
        background: white !important;
    }
    
    #cashflow-table, #cashflow-table-pvd {
        font-size: 9px !important;
    }
    
    .tab-pane, #tabPanePD, #tabPanePvD {
        display: block !important;
        opacity: 1 !important;
    }
}
</style>
@endpush
@endsection
