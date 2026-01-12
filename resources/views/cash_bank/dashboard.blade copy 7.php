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
                                <a class="nav-link active" href="#activity" data-toggle="tab">CashFlow PD</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#timeline" data-toggle="tab">CashFlow PvD</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- TAB 1: CashFlow PD -->
                            <div class="active tab-pane" id="activity">
                                <form method="GET" action="{{ route('dashboard.index') }}" class="mb-3">
                                    <div class="row align-items-end">
                                        <div class="col-md-1">
                                            <label>Tahun:</label>
                                            <select name="tahun" class="form-control select2">
                                                @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                                                        {{ $t }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <label>Dari Bulan:</label>
                                            <select name="bulan_dari" class="form-control">
                                                @foreach([
                                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                                    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                                    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                ] as $noBulan => $namaBulan)
                                                    <option value="{{ $noBulan }}" {{ $bulanDari == $noBulan ? 'selected' : '' }}>
                                                        {{ $namaBulan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <label>Sampai Bulan:</label>
                                            <select name="bulan_sampai" class="form-control">
                                                @foreach([
                                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                                    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                                    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                ] as $noBulan => $namaBulan)
                                                    <option value="{{ $noBulan }}" {{ $bulanSampai == $noBulan ? 'selected' : '' }}>
                                                        {{ $namaBulan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-1">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-filter"></i> Filter
                                            </button>
                                        </div>

                                        <div class="col-md-1">
                                            <a href="{{ route('dashboard.index') }}" class="btn btn-secondary btn-block">
                                                <i class="fas fa-redo"></i> Reset
                                            </a>
                                        </div>

                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-outline-danger btn-block" onclick="window.print()">
                                                <i class="fas fa-print"></i> PDF
                                            </button>
                                        </div>
                                        
                                        <div class="col-md-1">
                                            <a href="{{route('dashboard.excel')}}">
                                                <button type="button" class="btn btn-outline-primary btn-block">
                                                    <i class="fas fa-file-excel"></i> EXCEL
                                                </button>
                                            </a>    
                                        </div>
                                    </div>
                                </form>
                                
                                <hr>
                                
                                <!-- Content for CashFlow PD -->
                                @include('cash_bank.dashbordPertama', [
                                    'result' => $result,
                                    'bulanListFiltered' => $bulanListFiltered,
                                    'tahun' => $tahun
                                ])
                            </div>
                            
                            <!-- TAB 2: CashFlow PvD -->
                            <div class="tab-pane" id="timeline">
                                <form method="GET" action="{{ route('dashboard.data2') }}" class="mb-3">
                                    <div class="row align-items-end">
                                        <div class="col-md-1">
                                            <label>Tahun:</label>
                                            <select name="tahunPvd" class="form-control select2">
                                                @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                                                        {{ $t }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Dari Bulan:</label>
                                            <select name="bulan_dariPvd" class="form-control">
                                                @foreach([
                                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                                    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                                    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                ] as $noBulan => $namaBulan)
                                                    <option value="{{ $noBulan }}" {{ $bulanDari == $noBulan ? 'selected' : '' }}>
                                                        {{ $namaBulan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <label>Sampai Bulan:</label>
                                            <select name="bulan_sampaiPvd" class="form-control">
                                                @foreach([
                                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                                                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                                    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                                    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                ] as $noBulan => $namaBulan)
                                                    <option value="{{ $noBulan }}" {{ $bulanSampai == $noBulan ? 'selected' : '' }}>
                                                        {{ $namaBulan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-1">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-filter"></i> Filter
                                            </button>
                                        </div>

                                        <div class="col-md-1">
                                            <a href="{{ route('dashboard.data2') }}" class="btn btn-secondary btn-block">
                                                <i class="fas fa-redo"></i> Reset
                                            </a>
                                        </div>

                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-outline-danger btn-block" onclick="window.print()">
                                                <i class="fas fa-print"></i> PDF
                                            </button>
                                        </div>
                                        
                                        <div class="col-md-1">
                                            <a href="{{route('dashboard.excel')}}">
                                                <button type="button" class="btn btn-outline-primary btn-block">
                                                    <i class="fas fa-file-excel"></i> EXCEL
                                                </button>
                                            </a>    
                                        </div>
                                    </div>
                                </form>
                                <hr>
                                @include('cash_bank.dashbordKedua', [
                                    'result' => $result,
                                    'bulanListFiltered' => $bulanListFiltered,
                                    'tahun' => $tahun
                                ])
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
    // Initialize select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Form validation
    $('form').on('submit', function(e) {
        var bulanDari = parseInt($('select[name="bulan_dari"]').val());
        var bulanSampai = parseInt($('select[name="bulan_sampai"]').val());
        
        if (bulanDari > bulanSampai) {
            e.preventDefault();
            alert('Bulan dari tidak boleh lebih besar dari bulan sampai');
            return false;
        }
    });
    $('form').on('submit', function(e) {
        var bulanDari = parseInt($('select[name="bulan_dariPvd"]').val());
        var bulanSampai = parseInt($('select[name="bulan_sampaiPvd"]').val());
        
        if (bulanDari > bulanSampai) {
            e.preventDefault();
            alert('Bulan dari tidak boleh lebih besar dari bulan sampai');
            return false;
        }
    });
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
    .nav-pills {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    body {
        background: white !important;
    }
    
    #cashflow-table {
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