@extends('layouts/index')
@section('content')
@push('styles')
<style>
    #example2 {
    table-layout: auto !important;
    width: 100% !important;
    }

    #example2 th,
    #example2 td {
        white-space: nowrap;        /* biar kolom melebar */
        vertical-align: middle;
    }
</style>
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Daftar SPP</h1>
            </div>
         
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
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
                                        <th>No</th>
                                        <th>No Agenda</th>
                                        <th>Tanggal Masuk</th>
                                        <th>No SPP</th>
                                        <th>Tanggal SPP</th>
                                        <th>Uraian SPP</th>
                                        <th>Tanggal SPK</th>
                                        <th>Tanggal Berakhir SPK</th>
                                        <th>Tanggal BA</th>
                                        <th>Dibayar Kepada</th>
                                        <th>Nilai Rupiah</th>
                                        <th>Posisi Dokumen</th>
                                        <th>Status Pembayaran</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <!-- /.col -->
                    </div> 
                </div>
                
                <!-- /.invoice -->
            </div>
            </div>
            
        </div>
    </section>
@push('scripts')
<script>
   $(function () {
    let table = $('#example2').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('daftar-spp.data') }}",
            data: function (d) {
                d.status = '{{ request("status") }}';
            }
        },
        columns: [
            { data: 'DT_RowIndex',orderable:false, searchable:false  },
            { data: 'nomor_agenda' },
            { data: 'tanggal_masuk' },
            { data: 'nomor_spp' },
            { data: 'tanggal_spp' },
            { data: 'uraian_spp' },
            { data: 'tanggal_spk' },
            { data: 'tanggal_berakhir_spk' },
            { data: 'tanggal_berita_acara' },
            { data: 'dibayar_kepada' },
            { data: 'nilai_rupiah' },
            { data: 'current_handler' },
            { data: 'status_pembayaran' },
            
        ]
    });
});
</script>
@endpush
@endsection
