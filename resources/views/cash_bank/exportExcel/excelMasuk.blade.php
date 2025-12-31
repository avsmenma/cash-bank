<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        
        <!-- style css -->
        <link rel="stylesheet" href="https://cdn.datatables.net/2.3.6/css/dataTables.dataTables.css">
        <link rel="stylesheet"  href="{{ asset('css/style.css') }}" class="css">
        <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
        <script src="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.js"></script>

        <title>Bank Keluar</title>

        <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>
    </head>
    <body>
    <table class="table table-hover table-bordered align-middle display" id="example">
        <thead class="table-primary text-center">
            <tr class="text-center">
                <th style="width: 40px;"><input type="checkbox" id="select_all_ids"></th>
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
                <th>Debet (Rp)</th>
                <th>Keterangan</th>
                <th style="width: 100px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $row)
            <tr id="employee_ids{{ $row->id_bank_masuk}}">
                <td class="sticky-col sticky-check text-center">
                    <input type="checkbox" name="ids" class="checkbox_ids" value="{{ $row->id_bank_masuk }}">
                </td>
                <td class="sticky-col sticky-no text-center">{{ method_exists($data, 'firstItem') 
                    ? $data->firstItem() + $index 
                    : $index + 1 }}</td>
                <td class="text-center">{{ $row->agenda_tahun }}</td>
                <td class="text-center">{{ $row->id_bank_masuk }}</td>
                <td class="text-center">{{ $row->tanggal }}</td>
                <td class="text-center">{{ $row->sumberDana->nama_sumber_dana ?? '-' }}</td>
                <td class="text-center">{{ $row->bankTujuan->nama_tujuan ?? '-' }}</td>
                <td class="text-center">{{ $row->kategori->nama_kriteria ?? '-' }}</td>
                <td class="text-center">{{ $row->penerima }}</td>
                <td>{{ $row->uraian }}</td>
                <td>{{ $row->jenisPembayaran->nama_jenis_pembayaran ?? '-' }}</td>
                <td class="text-end font-monospace">@currency($row->debet)</td>
                <td>{{ $row->keterangan}}</td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <button
                    class="btn btn-primary btn-edit"
                    data-bs-toggle="modal"
                    data-bs-target="#edit"
                    data-id="{{ $row->id_bank_masuk }}"
                    data-agenda="{{ $row->agenda_tahun }}"
                    data-penerima="{{ $row->penerima }}"
                    data-uraian="{{ $row->uraian }}"
                    data-tanggal="{{ $row->tanggal }}"
                    data-bank="{{ $row->id_bank_tujuan }}"
                    data-sumber="{{ $row->id_sumber_dana }}"
                    data-kategori="{{ $row->id_kategori_kriteria }}"
                    data-jenis="{{ $row->id_jenis_pembayaran }}"
                    data-debet="{{ $row->debet }}"
                    >
                    <i class="bi bi-pencil-square text-white"></i>
                    </button>
                        <form action="{{ route('bank-masuk.destroy', $row->id_bank_masuk) }}"
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
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <script>
        new DataTable('#example');

        
    </script>
                        
</body>
</html>