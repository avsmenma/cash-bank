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

    .btn-cf-warning {
        background: #ffffff;
        color: #d97706 !important;
        border: 1.5px solid #f59e0b;
    }

    .btn-cf-warning:hover {
        background: #fffbeb;
        border-color: #d97706;
        color: #b45309 !important;
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
                        <select id="tahunMK" class="custom-select cf-filter-select" style="min-width: 110px;">
                            @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- DARI BULAN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label">DARI BULAN</label>
                        <select id="bulanDariMK" class="custom-select cf-filter-select" style="min-width: 140px;">
                            @foreach($bulanList as $no => $nama)
                                <option value="{{ $no }}" {{ $no == $bulanDari ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- SAMPAI BULAN -->
                    <div class="cf-filter-item">
                        <label class="cf-filter-label">SAMPAI BULAN</label>
                        <select id="bulanSampaiMK" class="custom-select cf-filter-select" style="min-width: 140px;">
                            @foreach($bulanList as $no => $nama)
                                <option value="{{ $no }}" {{ $no == $bulanSampai ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Right Action Buttons -->
                <div class="cf-filter-right">
                    <button type="button" id="filterMK" class="btn-cf-action btn-cf-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" id="resetMK" class="btn-cf-action btn-cf-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="button" id="resetWeeksMK" class="btn-cf-action btn-cf-warning" title="Reset semua tanggal minggu ke default">
                        <i class="fas fa-calendar-times"></i> Reset Tanggal Minggu
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Content Card -->
        <div class="card cf-content-card">
            <div class="card-body p-0 position-relative">
                <div id="mk-content">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Memuat data...</p>
                    </div>
                </div>
                <div class="cf-loading-overlay" id="mk-loading">
                    <i class="fas fa-2x fa-spinner fa-spin text-primary"></i>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- ========================================================
     MODAL: Edit Tanggal Minggu
     ======================================================== --}}
<div class="modal fade" id="weekEditModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" style="font-size: 15px; font-weight: 600;"><i class="fas fa-calendar-alt"></i> Ubah Rentang Tanggal</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-2" id="week-modal-info" style="font-size:12px;"></p>
                <div class="alert alert-info p-2" style="font-size:11px; border-radius: 8px;">
                    <i class="fas fa-info-circle"></i>
                    Tanggal akhir W1, W2, W3 menentukan batas setiap minggu.<br>
                    W4 otomatis = setelah W3 hingga akhir bulan.
                </div>

                <div class="form-group mb-2">
                    <label class="font-weight-bold" style="font-size:12px;">W1 berakhir di tanggal:</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text">Tgl 1 s/d</span></div>
                        <input type="number" id="we_w1" class="form-control" min="1" max="15" value="7">
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label class="font-weight-bold" style="font-size:12px;">W2 berakhir di tanggal:</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text">s/d Tgl</span></div>
                        <input type="number" id="we_w2" class="form-control" min="2" max="22" value="14">
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label class="font-weight-bold" style="font-size:12px;">W3 berakhir di tanggal:</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text">s/d Tgl</span></div>
                        <input type="number" id="we_w3" class="form-control" min="3" max="28" value="21">
                    </div>
                </div>

                <div class="bg-light p-2 rounded mt-2" id="week-preview" style="font-size:11px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveWeekEdit">
                    <i class="fas fa-save"></i> Simpan & Reload
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    // ================================================================
    // weekRanges: { bulanNo: { w1_end, w2_end, w3_end } }
    // ================================================================
    var weekRanges = {};
    var _editBulan = null;

    function getWeekCut(bNo) {
        var w = weekRanges[bNo] || { w1_end: 7, w2_end: 14, w3_end: 21 };
        return {
            w1_end: parseInt(w.w1_end) || 7,
            w2_end: parseInt(w.w2_end) || 14,
            w3_end: parseInt(w.w3_end) || 21
        };
    }

    // ================================================================
    // Load data via AJAX
    // ================================================================
    function loadMK() {
        $('#mk-loading').css('display', 'flex');
        $('#mk-content').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Memuat data...</p></div>');

        $.ajax({
            url: '{{ route("dashboard.modal-kerja.data") }}',
            type: 'GET',
            data: {
                tahun: $('#tahunMK').val(),
                bulan_dari: $('#bulanDariMK').val(),
                bulan_sampai: $('#bulanSampaiMK').val(),
                week_ranges: JSON.stringify(weekRanges)
            },
            success: function (html) {
                $('#mk-content').html(html);
                $('#mk-loading').hide();
            },
            error: function () {
                $('#mk-content').html('<div class="alert alert-danger m-2">Gagal memuat data. Silakan coba lagi.</div>');
                $('#mk-loading').hide();
            }
        });
    }

    loadMK();

    $('#filterMK').click(function () {
        var dari = parseInt($('#bulanDariMK').val());
        var sampai = parseInt($('#bulanSampaiMK').val());
        if (dari > sampai) {
            alert('Bulan dari tidak boleh lebih besar dari bulan sampai');
            return;
        }
        // Hapus week ranges yang di luar filter baru
        var newRanges = {};
        for (var b = dari; b <= sampai; b++) {
            if (weekRanges[b]) newRanges[b] = weekRanges[b];
        }
        weekRanges = newRanges;
        loadMK();
    });

    $('#resetMK').click(function () {
        $('#tahunMK').val({{ date('Y') }});
        $('#bulanDariMK').val(1);
        $('#bulanSampaiMK').val({{ date('m') }});
        weekRanges = {};
        loadMK();
    });

    $('#resetWeeksMK').click(function () {
        weekRanges = {};
        loadMK();
    });

    // ================================================================
    // Klik pada label tanggal minggu (event delegation)
    // ================================================================
    var bulanNames = {
        1:'Januari',2:'Februari',3:'Maret',4:'April',5:'Mei',6:'Juni',
        7:'Juli',8:'Agustus',9:'September',10:'Oktober',11:'November',12:'Desember'
    };

    $(document).on('click', '.mk-week-label', function () {
        var bNo = parseInt($(this).data('bulan'));
        _editBulan = bNo;
        var cuts = getWeekCut(bNo);

        $('#week-modal-info').text('Bulan: ' + (bulanNames[bNo] || bNo));
        $('#we_w1').val(cuts.w1_end);
        $('#we_w2').val(cuts.w2_end);
        $('#we_w3').val(cuts.w3_end);
        updatePreview();
        $('#weekEditModal').modal('show');
    });

    // Live preview
    function updatePreview() {
        var w1 = parseInt($('#we_w1').val()) || 7;
        var w2 = parseInt($('#we_w2').val()) || 14;
        var w3 = parseInt($('#we_w3').val()) || 21;
        if (w2 <= w1) w2 = w1 + 1;
        if (w3 <= w2) w3 = w2 + 1;
        $('#week-preview').html(
            '<b>Preview:</b><br>' +
            'W1: Tgl 1 – ' + w1 + '<br>' +
            'W2: Tgl ' + (w1+1) + ' – ' + w2 + '<br>' +
            'W3: Tgl ' + (w2+1) + ' – ' + w3 + '<br>' +
            'W4: Tgl ' + (w3+1) + ' – akhir bulan'
        );
    }

    $('#we_w1, #we_w2, #we_w3').on('input', function () {
        updatePreview();
    });

    $('#saveWeekEdit').click(function () {
        var w1 = parseInt($('#we_w1').val()) || 7;
        var w2 = parseInt($('#we_w2').val()) || 14;
        var w3 = parseInt($('#we_w3').val()) || 21;

        if (w1 < 1 || w1 > 15) { alert('W1 end harus antara 1–15'); return; }
        if (w2 <= w1 || w2 > 22) { alert('W2 end harus lebih besar dari W1 end dan maksimal 22'); return; }
        if (w3 <= w2 || w3 > 28) { alert('W3 end harus lebih besar dari W2 end dan maksimal 28'); return; }

        weekRanges[_editBulan] = { w1_end: w1, w2_end: w2, w3_end: w3 };
        $('#weekEditModal').modal('hide');
        loadMK();
    });
});
</script>
@endpush
@endsection
