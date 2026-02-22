@extends("layouts/index")
@section('content')
@push('styles')
<style>
    #tableDetailVA {
        table-layout: auto !important;
        width: 100% !important;
    }

    #tableDetailVA th,
    #tableDetailVA td {
        white-space: nowrap;
        vertical-align: middle;
    }

    .text-debet {
        color: #28a745;
        font-weight: 600;
    }

    .text-kredit {
        color: #dc3545;
        font-weight: 600;
    }

    .text-saldo {
        font-weight: 700;
    }
</style>
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Detail Transaksi VA</h1>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('daftarBank.index') }}">Daftar VA</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Main content -->
                <div class="invoice p-3 mb-3">

                    <!-- Header -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h4>
                                <i class="fas fa-university"></i>
                                {{ $va->nama_tujuan }}
                            </h4>
                            <p class="text-muted mb-0">Buku Pembantu (Ledger) — Gabungan Bank Masuk & Bank Keluar</p>
                        </div>
                    </div>

                    <div class="row no-print mb-3">
                        <div class="col-12">
                            <a href="{{ route('daftarBank.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12 table-responsive">
                            <table id="tableDetailVA" class="table table-bordered table-hover table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th style="width: 40px;">No</th>
                                        <th>Tanggal</th>
                                        <th>Bank Tujuan</th>
                                        <th>Penerima/Dari</th>
                                        <th>Uraian</th>
                                        <th class="text-right">Debet</th>
                                        <th class="text-right">Kredit</th>
                                        <th class="text-right">Saldo Akhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($transactions as $i => $trx)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($trx['tanggal'])->format('d/m/Y') }}</td>
                                            <td>{{ $va->nama_tujuan }}</td>
                                            <td>{{ $trx['penerima'] ?? '-' }}</td>
                                            <td>{{ $trx['uraian'] ?? '-' }}</td>
                                            <td class="text-right {{ $trx['debet'] > 0 ? 'text-debet' : '' }}">
                                                {{ $trx['debet'] > 0 ? number_format($trx['debet'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="text-right {{ $trx['kredit'] > 0 ? 'text-kredit' : '' }}">
                                                {{ $trx['kredit'] > 0 ? number_format($trx['kredit'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="text-right text-saldo">
                                                {{ number_format($trx['saldo'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                                Belum ada transaksi untuk VA ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($transactions->count() > 0)
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td colspan="5" class="text-right">Total</td>
                                            <td class="text-right text-debet">
                                                {{ number_format($transactions->sum('debet'), 0, ',', '.') }}
                                            </td>
                                            <td class="text-right text-kredit">
                                                {{ number_format($transactions->sum('kredit'), 0, ',', '.') }}
                                            </td>
                                            <td class="text-right text-saldo">
                                                {{ number_format($transactions->last()['saldo'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endif
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
            $('#tableDetailVA').DataTable({
                ordering: true,
                paging: true,
                searching: true,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [0] }
                ]
            });
        });
    </script>
@endpush

@endsection