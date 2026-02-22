<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- style css -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" class="css">
    <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
    <script src="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.js"></script>

    <title>Document</title>

    <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
</head>

<body>
    <table class="table table-hover table-bordered align-middle">
        <thead class="table-primary text-center">
            <tr>
                <th style="width: 50px;">No</th>
                <th>Agenda</th>
                <th>No Bukti</th>
                <th>Tanggal</th>
                <th>Sumber Dana</th>
                <th>Bank Tujuan</th>
                <th>Kriteria</th>
                <th>Penerima</th>
                <th>Uraian</th>
                <th>Jenis</th>
                <th>Kredit (Rp)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $row)
                <tr id="employee_ids{{ $row->id_bank_masuk}}">
                    <td class="sticky-col sticky-no text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->agenda_tahun }}</td>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}</td>
                    <td>{{ $row->sumberDana->nama_sumber_dana ?? '-' }}</td>
                    <td>{{ $row->bankTujuan->nama_tujuan ?? '-' }}</td>
                    <td>{{ $row->kategori->nama_kriteria ?? '-' }}</td>
                    <td>{{ $row->penerima }}</td>
                    <td>{{ $row->uraian }}</td>
                    <td>{{ $row->jenisPembayaran->nama_jenis_pembayaran ?? '-' }}</td>
                    <td class="text-end font-monospace">@currency($row->debet)</td>
                    <td>{{ $row->keterangan}}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="17" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Data yang anda cari tidak ada
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
<script type="text/javascript">
    window.print();
</script>

</html>