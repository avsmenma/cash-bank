@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detail Cashflow - {{ $namaItem }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.cashbank') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Info Box -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ $namaBulan }} {{ $tahun }} - {{ $namaItem }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Permintaan</span>
                                            <span class="info-box-number">Rp {{ number_format($totalPermintaanBulan, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Dropping</span>
                                            <span class="info-box-number">Rp {{ number_format($totalDroppingBulan, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-danger">
                                        <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Pembayaran</span>
                                            <span class="info-box-number">Rp {{ number_format($totalPembayaranBulan, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Per Minggu -->
            @foreach($dataPerMinggu as $minggu => $weekData)
            <div class="card">
                <div class="card-header bg-navy">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-week"></i> 
                        Minggu {{ $minggu }} ({{ $weekData['periode'] }})
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>Rp {{ number_format($weekData['permintaan']['total'], 0, ',', '.') }}</h3>
                                    <p>Permintaan RD</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>Rp {{ number_format($weekData['dropping']['total'], 0, ',', '.') }}</h3>
                                    <p>Dropping HO</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>Rp {{ number_format($weekData['pembayaran']['total'], 0, ',', '.') }}</h3>
                                    <p>Pembayaran</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs untuk Permintaan, Dropping, Pembayaran -->
                    <ul class="nav nav-tabs" id="minggu{{ $minggu }}Tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="permintaan-tab-{{ $minggu }}" data-toggle="tab" href="#permintaan-{{ $minggu }}" role="tab">
                                <i class="fas fa-file-invoice text-primary"></i> Permintaan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="dropping-tab-{{ $minggu }}" data-toggle="tab" href="#dropping-{{ $minggu }}" role="tab">
                                <i class="fas fa-hand-holding-usd text-success"></i> Dropping
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pembayaran-tab-{{ $minggu }}" data-toggle="tab" href="#pembayaran-{{ $minggu }}" role="tab">
                                <i class="fas fa-money-bill-wave text-danger"></i> Pembayaran
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="minggu{{ $minggu }}TabContent">
                        <!-- Tab Permintaan -->
                        <div class="tab-pane fade show active" id="permintaan-{{ $minggu }}" role="tabpanel">
                            @if($weekData['permintaan']['data']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th>No</th>
                                            <th>Kategori</th>
                                            <th>Sub Kriteria</th>
                                            <th>Item</th>
                                            <th>Nilai (M{{ $minggu }})</th>
                                            <th>Total (M1+M2+M3+M4)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($weekData['permintaan']['data'] as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->kategori->nama_kriteria ?? '-' }}</td>
                                            <td>{{ $item->subKriteria->nama_sub_kriteria ?? '-' }}</td>
                                            <td>{{ $item->itemSubKriteria->nama_item_sub_kriteria ?? '-' }}</td>
                                            <td class="text-right">Rp {{ number_format($item->{'M'.$minggu} ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format(($item->M1 + $item->M2 + $item->M3 + $item->M4), 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="4" class="text-right">Total:</th>
                                            <th class="text-right">Rp {{ number_format($weekData['permintaan']['total'], 0, ',', '.') }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Tidak ada data permintaan untuk minggu ini.
                            </div>
                            @endif
                        </div>

                        <!-- Tab Dropping -->
                        <div class="tab-pane fade" id="dropping-{{ $minggu }}" role="tabpanel">
                            @if($weekData['dropping']['data']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead class="bg-success">
                                        <tr>
                                            <th>No</th>
                                            <th>Kategori</th>
                                            <th>Sub Kriteria</th>
                                            <th>Item</th>
                                            <th>Nilai (M{{ $minggu }})</th>
                                            <th>Total (M1+M2+M3+M4)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($weekData['dropping']['data'] as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->kategori->nama_kriteria ?? '-' }}</td>
                                            <td>{{ $item->subKriteria->nama_sub_kriteria ?? '-' }}</td>
                                            <td>{{ $item->itemSubKriteria->nama_item_sub_kriteria ?? '-' }}</td>
                                            <td class="text-right">Rp {{ number_format($item->{'M'.$minggu} ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format(($item->M1 + $item->M2 + $item->M3 + $item->M4), 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="4" class="text-right">Total:</th>
                                            <th class="text-right">Rp {{ number_format($weekData['dropping']['total'], 0, ',', '.') }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Tidak ada data dropping untuk minggu ini.
                            </div>
                            @endif
                        </div>

                        <!-- Tab Pembayaran -->
                        <div class="tab-pane fade" id="pembayaran-{{ $minggu }}" role="tabpanel">
                            @if($weekData['pembayaran']['data']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover">
                                    <thead class="bg-danger">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Kategori</th>
                                            <th>Sub Kriteria</th>
                                            <th>Item</th>
                                            <th>Keterangan</th>
                                            <th>Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($weekData['pembayaran']['data'] as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                                            <td>{{ $item->kategori->nama_kriteria ?? '-' }}</td>
                                            <td>{{ $item->subKriteria->nama_sub_kriteria ?? '-' }}</td>
                                            <td>{{ $item->itemSubKriteria->nama_item_sub_kriteria ?? '-' }}</td>
                                            <td>{{ $item->keterangan ?? '-' }}</td>
                                            <td class="text-right">Rp {{ number_format($item->nilai_rupiah ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="6" class="text-right">Total:</th>
                                            <th class="text-right">Rp {{ number_format($weekData['pembayaran']['total'], 0, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Tidak ada data pembayaran untuk minggu ini.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Button Kembali -->
            <div class="row">
                <div class="col-md-12">
                    <a href="{{ route('dashboard.cashbank') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection