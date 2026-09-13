@extends('layouts.index')

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
        min-width: 130px;
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

    .cf-filter-right {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .btn-cf-action {
        height: 44px;
        padding: 0 20px;
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
        background: rgba(255, 255, 255, 0.85);
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
                        <label class="cf-filter-label"><i class="fas fa-calendar mr-1"></i> TAHUN</label>
                        <select id="tahunMKSap" class="custom-select cf-filter-select" style="min-width: 110px;">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- DARI BULAN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label"><i class="fas fa-calendar-alt mr-1"></i> DARI BULAN</label>
                        <select id="bulanDariMKSap" class="custom-select cf-filter-select" style="min-width: 140px;">
                            @foreach($bulanList as $no => $nama)
                                <option value="{{ $no }}" {{ $no == $bulanDari ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- SAMPAI BULAN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label"><i class="fas fa-calendar-alt mr-1"></i> SAMPAI BULAN</label>
                        <select id="bulanSampaiMKSap" class="custom-select cf-filter-select" style="min-width: 140px;">
                            @foreach($bulanList as $no => $nama)
                                <option value="{{ $no }}" {{ $no == $bulanSampai ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- CAKUPAN UNIT / KEBUN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label"><i class="fas fa-building mr-1"></i> UNIT / KEBUN</label>
                        <select id="unitMKSap" class="custom-select cf-filter-select" style="min-width: 220px;">
                            <option value="all" {{ $unitSelected === 'all' ? 'selected' : '' }}>Semua Unit (Global)</option>
                            <option value="ro" {{ $unitSelected === 'ro' ? 'selected' : '' }}>Regional Office (Kantor Direksi)</option>
                            <optgroup label="Unit / Kebun">
                                @foreach($units as $u)
                                    <option value="{{ $u->id_bank_tujuan }}" {{ (string)$u->id_bank_tujuan === $unitSelected ? 'selected' : '' }}>
                                        {{ $u->nama_tujuan }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                </div>

                <!-- Right Action Buttons -->
                <div class="cf-filter-right">
                    <button type="button" id="filterMKSap" class="btn-cf-action btn-cf-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" id="resetMKSap" class="btn-cf-action btn-cf-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Content Card -->
        <div class="card cf-content-card">
            <div class="card-body p-0 position-relative" style="min-height: 250px;">
                <div id="mk-sap-content"></div>
                <div class="cf-loading-overlay" id="mk-sap-loading">
                    <div class="text-center">
                        <i class="fas fa-2x fa-spinner fa-spin text-primary"></i>
                        <p class="mt-2 text-muted" style="font-size: 13px; font-weight: 500;">Memuat data realisasi Modal Kerja (SAP)...</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    var defaultTahun = '{{ $tahun }}';
    var defaultBulanDari = '{{ $bulanDari }}';
    var defaultBulanSampai = '{{ $bulanSampai }}';
    var defaultUnit = 'all';

    function loadMKSapData() {
        var tahun = $('#tahunMKSap').val();
        var bulanDari = parseInt($('#bulanDariMKSap').val());
        var bulanSampai = parseInt($('#bulanSampaiMKSap').val());
        var unit = $('#unitMKSap').val();

        if (bulanDari > bulanSampai) {
            Swal.fire({
                icon: 'warning',
                title: 'Periksa Rentang Bulan',
                text: 'Bulan awal tidak boleh lebih besar dari bulan akhir.'
            });
            return;
        }

        $('#mk-sap-loading').css('display', 'flex');

        $.ajax({
            url: '{{ route("dashboard.modal-kerja-sap.data") }}',
            type: 'GET',
            data: {
                tahun: tahun,
                bulan_dari: bulanDari,
                bulan_sampai: bulanSampai,
                unit: unit
            },
            success: function (response) {
                $('#mk-sap-content').html(response);
            },
            error: function (xhr, status, error) {
                console.error("Gagal memuat data:", error);
                $('#mk-sap-content').html(
                    '<div class="alert alert-danger m-3">' +
                    '<i class="fas fa-exclamation-circle mr-1"></i> Terjadi kesalahan saat memuat data Modal Kerja (SAP). Silakan coba lagi.' +
                    '</div>'
                );
            },
            complete: function () {
                $('#mk-sap-loading').hide();
            }
        });
    }

    // Trigger load pertama kali
    loadMKSapData();

    // Event Filter
    $('#filterMKSap').on('click', function () {
        loadMKSapData();
    });

    // Event Reset
    $('#resetMKSap').on('click', function () {
        $('#tahunMKSap').val(defaultTahun);
        $('#bulanDariMKSap').val(defaultBulanDari);
        $('#bulanSampaiMKSap').val(defaultBulanSampai);
        $('#unitMKSap').val(defaultUnit);
        loadMKSapData();
    });

    // Validasi interaktif rentang bulan
    $('#bulanDariMKSap').on('change', function () {
        var dari = parseInt($(this).val());
        var sampai = parseInt($('#bulanSampaiMKSap').val());
        if (dari > sampai) {
            $('#bulanSampaiMKSap').val(dari);
        }
    });

    $('#bulanSampaiMKSap').on('change', function () {
        var sampai = parseInt($(this).val());
        var dari = parseInt($('#bulanDariMKSap').val());
        if (sampai < dari) {
            $('#bulanDariMKSap').val(sampai);
        }
    });
});
</script>
@endpush

@endsection
