@extends('layouts/index')
@section('content')

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="tittle">Detail <span style="color: #FF7518">Report Transaksi</span></h1>
                <small class="text-muted">Detail transaksi berdasarkan item yang dipilih</small>
            </div>
            <a href="{{ route('bank-keluar.report', [
        'tahun' => request('tahun'),
        'rekapanVA' => 'kategori-full'
    ]) }}" class="btn">
                <i class="bi bi-arrow-left"></i> Kembali ke Report
            </a>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card rounded-3 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            Detail Transaksi {{ $sub }}
                            </h5>
                            <div>
                                <a href="{{ route('detailKategori.export', [
        'kategori' => $kategori,
        'tahun' => $tahun
    ]) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                                </a>
                                <a href="#" onclick="window.print()" class="btn btn-primary btn-sm">
                                    <i class="bi bi-printer"></i> Export PDF
                                </a>
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive" id="printArea">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="text-center table-primary">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Tanggal</th>
                                        <th>Agenda</th>
                                        <th>Nama Kategori</th>
                                        <th>Bank Tujuan</th>
                                        <th>Sumber Dana</th>
                                        <th>Penerima</th>
                                        <th>Jenis Pembayaran</th>
                                        <th>Uraian</th>
                                        <th>Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $row)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}
                                            </td>
                                            <td>{{ $row->agenda_tahun ?? '-' }}</td>
                                            <td>{{ $row->nama_sub_kriteria ?? '-' }}</td>
                                            <td>{{ $row->nama_item_sub_kriteria ?? '-' }}</td>
                                            <td>{{ $row->nama_tujuan ?? '-' }}</td>
                                            <td>{{ $row->nama_sumber_dana ?? '-' }}</td>
                                            <td>{{ $row->penerima ?? '-' }}</td>
                                            <td>{{ $row->uraian ?? '-' }}</td>
                                            <td class="text-end">
                                                <strong>@currency($row->kredit ?? 0)</strong>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                <strong>Data tidak ditemukan</strong>
                                                <p class="mb-0 small">Tidak ada transaksi untuk kategori ini</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                @if($data->count() > 0)
                                    <tfoot class="table-warning">
                                        <tr>
                                            <th colspan="9" class="text-end fw-bold">
                                                TOTAL KREDIT:
                                            </th>
                                            <th class="text-end fw-bold">
                                                @currency($totalKredit)
                                            </th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>

@endsection