@extends('layouts.index')

@section('content')
<div class="container-fluid m-3">
    <!-- Content Header -->
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
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filter Data</h3>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-end mb-3">
                            <div class="form-group mb-0 ml-1">
                                <label>Tahun:</label>
                                <select name="tahun" id="tahunMK" class="form-control select2">
                                    @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                        <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group mb-0 ml-1">
                                <label>Dari Bulan:</label>
                                <select name="bulan_dari" id="bulanDariMK" class="form-control">
                                    @foreach($bulanList as $noBulan => $namaBulan)
                                        <option value="{{ $noBulan }}" {{ $noBulan == $bulanDari ? 'selected' : '' }}>{{ $namaBulan }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-0 ml-1">
                                <label>Sampai Bulan:</label>
                                <select name="bulan_sampai" id="bulanSampaiMK" class="form-control">
                                    @foreach($bulanList as $noBulan => $namaBulan)
                                        <option value="{{ $noBulan }}" {{ $noBulan == $bulanSampai ? 'selected' : '' }}>{{ $namaBulan }}</option>
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

                <div class="card">
                    <div class="card-body">
                        <div id="mk-content">
                            <div class="text-center p-5 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p>Halaman Modal Kerja - Konten akan dikembangkan.</p>
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
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    $('#filterMK').click(function() {
        var bulanDari = parseInt($('#bulanDariMK').val());
        var bulanSampai = parseInt($('#bulanSampaiMK').val());
        if (bulanDari > bulanSampai) {
            alert('Bulan dari tidak boleh lebih besar dari bulan sampai');
            return false;
        }
        // Load data akan dikembangkan
    });

    $('#resetMK').click(function() {
        $('#tahunMK').val({{ date('Y') }}).trigger('change');
        $('#bulanDariMK').val(1);
        $('#bulanSampaiMK').val(12);
    });
});
</script>
@endpush
@endsection
