<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
        <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        

        <link rel="stylesheet"  href="{{ asset('css/style.css') }}" class="css">
       <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
        <script src="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.js"></script>

        <title>Document</title>

        <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css"> -->
</head>
<body>
     <table class="table table-bordered table-striped table-hover" id="printArea">
                            <thead class="text-center table-primary">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Tanggal</th>
                                    <th>Agenda</th>
                                    <th>Nama Kategori</th>
                                    <th>Sub Kategori</th>
                                    <th>Item Kategori</th>
                                    <th>Bank Tujuan</th>
                                    <th>Sumber Dana</th>
                                    <th>Penerima</th>
                                    <th>Uraian</th>
                                    <th>Jenis Pembayaran</th>
                                    <th>Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $index => $row)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}
                                    </td>
                                    <td>{{ $row->agenda_tahun ?? '-' }}</td>
                                    <td>{{ $row->nama_kriteria ?? '-' }}</td>
                                    <td>{{ $row->nama_sub_kriteria ?? '-' }}</td>
                                    <td>{{ $row->nama_item_sub_kriteria ?? '-' }}</td>
                                    <td>{{ $row->nama_tujuan ?? '-' }}</td>
                                    <td>{{ $row->nama_sumber_dana ?? '-' }}</td>
                                    <td>{{ $row->penerima ?? '-' }}</td>
                                    <td>{{ $row->uraian ?? '-' }}</td>
                                    <td>{{ $row->nama_jenis_pembayaran ?? '-' }}</td>
                                    <td class="text-end">
                                        <strong>@currency($row->kredit ?? 0)</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        <strong>Data tidak ditemukan</strong>
                                        <p class="mb-0 small">Tidak ada transaksi untuk sub kategori ini dengan filter yang dipilih</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            
                            @if($data->count() > 0)
                            <tfoot class="table-warning">
                                <tr>
                                    <th colspan="11" class="text-end fw-bold">
                                        TOTAL KREDIT {{ strtoupper($sub) }}:
                                    </th>
                                    <th class="text-end fw-bold">
                                        @currency($totalKredit)
                                    </th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
</body>
</html>