@extends('layouts/index')
@section('content')

<div class="container-fluid mt-3">

    <!-- Title -->
  
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Daftar SPP</h1>
            </div>
         
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
              <li class="breadcrumb-item active">Daftar SPP</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid ">
            <div class="row">
            <div class="col-12 ">
                <!-- Main content -->
                <div class="invoice p-3 mb-3">
                    
                <!-- title row -->
                    <div class="row no-print">
                        <div class="col-12 gap-4">
                            <a href="?status=belum"
                                    class="btn {{ request('status') == 'belum' ? 'bg-warning text-white' : 'btn-outline-warning' }}">
                                        Belum Siap Bayar
                                    </a>

                                    <a href="?status=siap"
                                    class="btn {{ request('status') == 'siap' ? 'bg-primary text-white' : 'btn-outline-primary' }}">
                                        Siap Bayar
                                    </a>

                                    <a href="?status=sudah"
                                    class="btn {{ request('status') == 'sudah' ? 'bg-success text-white' : 'btn-outline-success' }}">
                                        Sudah Dibayar
                                    </a>

                                    <a href="?status="
                                    class="btn {{ request('status') == null ? 'bg-dark text-white' : 'btn-light' }}">
                                        Semua
                                    </a>
                        </div>
                    </div>
                    <hr>
                     <div class="row">
                        <div class="col-12 table-responsive">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr id="employee_ids">
                                        <th>No Agenda</th>
                                        <th>No SPP</th>
                                        <th>Tanggal SPP</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Tanggal SPK</th>
                                        <th>Tanggal BA</th>
                                        <th>Dibayar Kepada</th>
                                        <th>Uraian SPP</th>
                                        <th>Nilai Rupiah</th>
                                        <th>Posisi Dokumen</th>
                                        <th>Umur Dokumen (Tanggal Masuk)</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <!-- /.col -->
                    </div> 
                </div>
                
                <!-- /.invoice -->
            </div><!-- /.col -->
            </div><!-- /.row -->
            
        </div><!-- /.container-fluid -->
    </section>


    <!-- Search & Filter -->

    <!-- Table -->
    <!-- <div class="row mt-3">
        <div class="col-md-12">
            <div class="card rounded-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="justify-content-start align-items-center mb-3">
                            <!-- <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="?status=belum" class="btn btn-outline-warning">Belum Siap Bayar</a>
                                    <a href="?status=siap" class="btn btn-outline-primary">Siap Bayar</a>
                                    <a href="?status=sudah" class="btn btn-outline-success">Sudah Dibayar</a>
                                    <a href="?status=" class="btn btn-light">Semua</a>
                                </div>
                            </div> -->
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <div class="d-flex gap-2 flex-wrap">

                                    <a href="?status=belum"
                                    class="btn {{ request('status') == 'belum' ? 'bg-warning text-white' : 'btn-outline-warning' }}">
                                        Belum Siap Bayar
                                    </a>

                                    <a href="?status=siap"
                                    class="btn {{ request('status') == 'siap' ? 'bg-primary text-white' : 'btn-outline-primary' }}">
                                        Siap Bayar
                                    </a>

                                    <a href="?status=sudah"
                                    class="btn {{ request('status') == 'sudah' ? 'bg-success text-white' : 'btn-outline-success' }}">
                                        Sudah Dibayar
                                    </a>

                                    <a href="?status="
                                    class="btn {{ request('status') == null ? 'bg-dark text-white' : 'btn-light' }}">
                                        Semua
                                    </a>

                                </div>
                            </div>
                    </div>
                    <hr>

                    <div class="table-responsive mt-3">
                        <table class="table table-hover table-bordered align-middle display" id="example">
                            <thead class="text-center table-primary ">
                                <tr>
                                    <th>No</th>
                                    <th>Nomor_Agenda</th>
                                    <th>SPP</th>
                                    <th>Tanggal SPP</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Tanggal SPK</th>
                                    <th>Tanggal BA</th>
                                    <th>Dibayar Kepada</th>
                                    <th>Uraian SPP</th>
                                    <th>Nilai Rupiah</th>
                                    <th>Posisi Dokumen</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody >
                                @foreach ($data as $index => $row)
                                <tr >
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->nomor_agenda }}</td>
                                        <td>{{ $row-> nomor_spp}}</td>
                                        <td>{{ $row->tanggal_spp }}</td>
                                        <td>{{ $row->tanggal_masuk }}</td>
                                        <td>{{ $row->tanggal_spk }}</td>
                                        <td>{{ $row->tanggal_berita_acara }}</td>
                                        <td>{{ $row->dibayar_kepada }}</td>
                                        <td>{{ $row->uraian_spp }}</td>
                                        <td>{{ 'Rp' . number_format($row->nilai_rupiah, 0, ',', '.') }}</td>
                                        <td>{{ $row->current_handler}}</td>
                                        <td>{{ $row->status_pembayaran}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->
    <script>
        new DataTable('#example');
    </script>
@endsection
