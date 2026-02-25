@extends('layouts/va_layout')
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Rekening Koran</h1>
                </div>

                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('va.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Rekening Koran</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Rekening Koran</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted text-center py-5">
                                <i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>
                                Halaman Rekening Koran — konten akan ditambahkan nanti.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection