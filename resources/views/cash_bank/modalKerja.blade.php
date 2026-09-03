@extends('layouts.index')

@section('content')
<div class="container-fluid pt-3 px-3">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                {{-- FILTER CARD --}}
                <div class="card cb-fullscreen-hide">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-filter"></i> Filter Data</h3>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-end mb-0">
                            <div class="form-group mb-0 ml-1">
                                <label>Tahun:</label>
                                <select id="tahunMK" class="form-control select2" style="min-width:90px;">
                                    @for($t = date('Y') - 3; $t <= date('Y') + 2; $t++)
                                        <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-group mb-0 ml-1">
                                <label>Dari Bulan:</label>
                                <select id="bulanDariMK" class="form-control">
                                    @foreach($bulanList as $no => $nama)
                                        <option value="{{ $no }}" {{ $no == $bulanDari ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-0 ml-1">
                                <label>Sampai Bulan:</label>
                                <select id="bulanSampaiMK" class="form-control">
                                    @foreach($bulanList as $no => $nama)
                                        <option value="{{ $no }}" {{ $no == $bulanSampai ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-0 ml-1">
                                <button type="button" id="filterMK" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                            <div class="form-group mb-0 ml-1">
                                <button type="button" id="resetMK" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                            <div class="form-group mb-0 ml-1">
                                <button type="button" id="resetWeeksMK" class="btn btn-outline-warning btn-sm" title="Reset semua tanggal minggu ke default">
                                    <i class="fas fa-calendar-times"></i> Reset Tanggal Minggu
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABLE CARD --}}
                <div class="card">
                    <div class="card-body p-1">
                        <div id="mk-content">
                            <div class="text-center p-4">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Memuat data...</p>
                            </div>
                        </div>
                        <div class="overlay" id="mk-loading" style="display:none;">
                            <i class="fas fa-2x fa-sync fa-spin"></i>
                        </div>
                    </div>
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
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-calendar-alt"></i> Ubah Rentang Tanggal</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-2" id="week-modal-info" style="font-size:12px;"></p>
                <div class="alert alert-info p-2" style="font-size:11px;">
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
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

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
        $('#mk-loading').show();
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
        $('#tahunMK').val({{ date('Y') }}).trigger('change');
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
