<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- style css -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" class="css">
    <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
    <script src="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.js"></script>


    <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
    <title>Bank Masuk</title>
</head>

<body>
    <table class="table table-hover table-bordered align-middle">
        <thead class="table-primary text-center">
            <tr>
                <!-- <th style="width: 40px;"><input type="checkbox" id="select_all_ids"></th> -->
                <th style="width: 50px;">No</th>
                <th>Agenda</th>
                <th>No Bukti</th>
                <th>Tanggal</th>
                <th>Sumber Dana</th>
                <th>Bank Tujuan</th>
                <th>Kriteria</th>
                <th>Sub Kriteria</th>
                <th>Item Sub</th>
                <th>Penerima</th>
                <th>Uraian</th>
                <th>Jenis</th>
                <th>Nilai (Rp)</th>
                <th>Kredit (Rp)</th>
                <th>Keterangan</th>
                <!-- <th style="width: 100px;">Aksi</th> -->
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $row)
                <tr id="employee_ids{{ $row->id_bank_keluar}}">
                    <!-- <td class="sticky-col sticky-check text-center">
                                            <input type="checkbox" name="ids" class="checkbox_ids" value="{{ $row->id_bank_keluar }}">
                                        </td> -->
                    <td class="sticky-col sticky-no text-center">{{ $index + 1 }}</td>
                    <td class="sticky-col sticky-agenda">{{ $row->agenda_tahun }}</td>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}</td>
                    <td class="text-center">{{ $row->sumberDana->nama_sumber_dana ?? '-' }}</td>
                    <td class="text-center">{{ $row->bankTujuan->nama_tujuan ?? '-' }}</td>
                    <td class="text-center">{{ $row->kategori->nama_kriteria ?? '-' }}</td>
                    <td class="text-center">{{ $row->subKriteria->nama_sub_kriteria ?? '-' }}</td>
                    <td class="text-center">{{ $row->itemSubKriteria->nama_item_sub_kriteria ?? '-' }}</td>
                    <td class="text-center">{{ $row->penerima }}</td>
                    <td>{{ $row->uraian }}</td>
                    <td>{{ $row->jenisPembayaran->nama_jenis_pembayaran ?? '-' }}</td>
                    <td class="text-end font-monospace">@currency($row->nilai_rupiah)</td>
                    <td class="text-end font-monospace">@currency($row->kredit)</td>
                    <td>{{ $row->keterangan}}</td>
                    <!-- <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <button type="button"
                                                    class="btn bg-primary btn-sm text-white"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editKeluar"
                                                    data-id="{{ $row->id_bank_keluar }}"
                                                    data-agenda="{{ $row->agenda_tahun }}"
                                                    data-penerima="{{ $row->penerima }}"
                                                    data-uraian="{{ $row->uraian }}"
                                                    data-tanggal="{{ $row->tanggal }}"
                                                    data-bank="{{ $row->id_bank_tujuan }}"
                                                    data-sumber="{{ $row->id_sumber_dana }}"
                                                    data-kategori="{{ $row->id_kategori_kriteria }}"
                                                    data-jenis="{{ $row->id_jenis_pembayaran }}"
                                                    data-kredit="{{ $row->kredit }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form action="{{ route('bank-keluar.destroy', $row->id_bank_keluar) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn bg-danger btn-sm text-white"
                                                        onclick="return confirm('Yakin ingin menghapus?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td> -->
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