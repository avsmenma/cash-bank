
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
    @if($data->count())
<table class="table table-bordered table-striped mt-3">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Agenda Tahun</th>
                                    <th>Uraian</th>
                                    <th>Penerima</th>
                                    <th>Sumber Dana</th>
                                    <th>Bank Tujuan</th>
                                    <th>Kategori</th>
                                    <th>Jenis Pembayaran</th>
                                    <th>Debet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $a)
                                <tr>
                                    <td>{{ $a->tanggal }}</td>
                                    <td>{{ $a->agenda_tahun }}</td>
                                    <td>{{ $a->uraian }}</td>
                                    <td>{{ $a->penerima }}</td>
                                    <td>{{ $a->sumberDana->nama_sumber_dana ?? '-' }}</td>
                                    <td>{{ $a->bankTujuan->nama_tujuan ?? '-' }}</td>
                                    <td>{{ $a->kategori->nama_kriteria ?? '-' }}</td>
                                    <td>{{ $a->jenisPembayaran->nama_jenis_pembayaran ?? '-' }}</td>
                                    <td class="text-end">@currency($a->debet)</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="8" class="text-end">TOTAL</th>
                                    <th class="text-end">
                                        {{ number_format($data->sum('debet'),0,',','.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
  @else
                        <p class="text-center mt-3">Data tidak ditemukan</p>
                    @endif
</body>
</html>
