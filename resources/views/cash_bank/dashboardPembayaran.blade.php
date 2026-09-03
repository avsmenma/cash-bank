@extends('layouts/index')
@section('content')
<style>
    /* ===== MODERN DASHBOARD FILTER BAR ===== */
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

    .cf-content-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
    .cf-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.75);
        z-index: 50;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
</style>

<div class="container-fluid pt-3 px-3">
    <section class="content">
        <!-- Modern Filter Toolbar -->
        <div class="cf-filter-panel cb-fullscreen-hide">
            <div class="cf-filter-wrapper">
                <div class="cf-filter-left">
                    <!-- TAHUN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label">TAHUN</label>
                        <select name="tahun" id="tahun" class="custom-select cf-filter-select" style="min-width: 110px;">
                            @for($y = date('Y') - 5; $y <= date('Y') + 5; $y++)
                                <option value="{{ $y }}" {{ $y == ($tahun ?? date('Y')) ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- DARI BULAN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label">DARI BULAN</label>
                        <select name="bulan_dari" id="bulanDari" class="custom-select cf-filter-select" style="min-width: 140px;">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $noBulan => $namaBulan)
                                <option value="{{ $noBulan }}" {{ $noBulan == ($bulanDari ?? request('bulan_dari', 1)) ? 'selected' : '' }}>
                                    {{ $namaBulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- SAMPAI BULAN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label">SAMPAI BULAN</label>
                        <select name="bulan_sampai" id="bulanSampai" class="custom-select cf-filter-select" style="min-width: 140px;">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $noBulan => $namaBulan)
                                <option value="{{ $noBulan }}" {{ $noBulan == ($bulanSampai ?? request('bulan_sampai', 12)) ? 'selected' : '' }}>
                                    {{ $namaBulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Right Action Buttons -->
                <div class="cf-filter-right">
                    <button type="button" class="btn-cf-action btn-cf-primary" id="btn-filter">
                        <i class="fas fa-filter"></i> Filter Data
                    </button>
                    <button type="button" class="btn-cf-action btn-cf-secondary" id="btn-reset">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Content Card -->
        <div class="card cf-content-card">
            <div class="card-body p-0 position-relative">
                <div id="table-wrapper">
                    @include('cash_bank.pembayaran.dashboardPembayaran')
                </div>
                <div class="cf-loading-overlay" id="loading">
                    <i class="fas fa-2x fa-spinner fa-spin text-primary"></i>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Event: Tombol Filter
    $('#btn-filter').on('click', function() {
        loadData();
    });

    // Event: Tombol reset
    $('#btn-reset').on('click', function() {
        $('#tahun').val({{ date('Y') }});
        $('#bulanDari').val(1);
        $('#bulanSampai').val(12);
        loadData();
    });

    function loadData() {
        let tahun = $('#tahun').val();
        let bulanDari = parseInt($('#bulanDari').val());
        let bulanSampai = parseInt($('#bulanSampai').val());

        if (bulanDari > bulanSampai) {
            alert('Bulan Dari tidak boleh lebih besar dari Bulan Sampai');
            return false;
        }
        
        $('#loading').css('display', 'flex');

        $.ajax({
            url: '{{ route("dashboard.pembayaran.data") }}',
            type: 'GET',
            data: { 
                tahun: tahun,
                bulan_dari: bulanDari,
                bulan_sampai: bulanSampai
            },
            success: function(response) {
                $('#table-wrapper').html(response);
                $('#loading').hide();
            },
            error: function(xhr) {
                $('#loading').hide();
                alert('Gagal memuat data');
            }
        });
    }
});
</script>
@endpush

{{-- Print Styles --}}
<style>
@media print {
    .no-print,
    .breadcrumb,
    .btn,
    .btn-cf-action,
    .cf-filter-panel,
    form,
    .card-header {
        display: none !important;
    }
    
    .card, .cf-content-card {
        border: none !important;
        box-shadow: none !important;
    }
    
    body {
        background: white !important;
    }
    
    #cashflow-table {
        font-size: 9px !important;
    }
}
</style>
@endsection
