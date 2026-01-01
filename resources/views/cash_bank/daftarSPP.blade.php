@extends('layouts/index')
@section('content')

<div class="container-fluid mt-3">

    <!-- Title -->
    <h1 class="tittle">Daftar <span style="color: #FF7518">SPP</span></h1>
    <small>Ini daftar SPP pada Aplikasi Agenda Online</small>

    <!-- Search & Filter -->

    <!-- Table -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card rounded-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="justify-content-start align-items-center mb-3">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="?status=belum" class="btn btn-outline-warning">Belum Siap Bayar</a>
                                    <a href="?status=siap" class="btn btn-outline-primary">Siap Bayar</a>
                                    <a href="?status=sudah" class="btn btn-outline-success">Sudah Dibayar</a>
                                    <a href="?status=" class="btn btn-light">Semua</a>
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
</div>
    <script>
        new DataTable('#example');
    </script>
@endsection
