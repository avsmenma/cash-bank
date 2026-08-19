@extends("layouts/va_layout")
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold">Cash Flow</h1>
                    <small class="text-muted">{{ $va->nama_tujuan }}</small>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('va.dashboard') }}">Virtual Account</a></li>
                        <li class="breadcrumb-item active">Cash Flow</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-money-bill-wave mr-1"></i> Cash Flow — {{ $va->nama_tujuan }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3 text-secondary"></i>
                        <p class="mb-0">Halaman Cash Flow sedang disiapkan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
