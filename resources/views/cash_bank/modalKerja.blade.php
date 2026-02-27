@extends('layouts.index')

@section('content')
<div class="container-fluid m-3">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Modal Kerja</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Modal Kerja</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                {{-- FILTER CARD --}}
                <div class="card">
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

@push('scripts')
<script>
$(document).ready(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // Load data pertama kali
    loadMK();

    $('#filterMK').click(function () {
        var dari = parseInt($('#bulanDariMK').val());
        var sampai = parseInt($('#bulanSampaiMK').val());
        if (dari > sampai) {
            alert('Bulan dari tidak boleh lebih besar dari bulan sampai');
            return;
        }
        loadMK();
    });

    $('#resetMK').click(function () {
        $('#tahunMK').val({{ date('Y') }}).trigger('change');
        $('#bulanDariMK').val(1);
        $('#bulanSampaiMK').val({{ date('m') }});
        loadMK();
    });

    function loadMK() {
        $('#mk-loading').show();
        $('#mk-content').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Memuat data...</p></div>');

        $.ajax({
            url: '{{ route("dashboard.modal-kerja.data") }}',
            type: 'GET',
            data: {
                tahun: $('#tahunMK').val(),
                bulan_dari: $('#bulanDariMK').val(),
                bulan_sampai: $('#bulanSampaiMK').val()
            },
            success: function (html) {
                $('#mk-content').html(html);
                $('#mk-loading').hide();
            },
            error: function () {
                $('#mk-content').html('<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>');
                $('#mk-loading').hide();
            }
        });
    }
});
</script>
@endpush
@endsection
