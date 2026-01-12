<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    </head>
<body>
    <table class="table table-hover table-bordered align-middle display" id="example">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="select_all_ids"></th>
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
                                    <th>Kredit (Rp)</th>
                                    <th>Nilai (Rp)</th>
                                    <th>Keterangan</th>
                                    <th>No SAP</th>
                                    <th style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $index => $row)
                                <tr id="employee_ids{{ $row->id_bank_keluar}}">
                                    <td class="sticky-col sticky-check text-center">
                                        <input type="checkbox" name="ids" class="checkbox_ids" value="{{ $row->id_bank_keluar }}">
                                    </td>
                                    <td class="sticky-col sticky-no text-center">{{  $index + 1 }}</td>
                                    <td class="sticky-col sticky-agenda">{{ $row->agenda_tahun }}</td>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center">{{ $row->tanggal }}</td>
                                    <td >{{ $row->sumberDana->nama_sumber_dana ?? '-' }}</td>
                                    <td >{{ $row->bankTujuan->nama_tujuan ?? '-' }}</td>
                                    <td >{{ $row->kategori->nama_kriteria ?? '-' }}</td>
                                    <td >{{ $row->subKriteria->nama_sub_kriteria ?? '-' }}</td>
                                    <td >{{ $row->itemSubKriteria->nama_item_sub_kriteria ?? '-' }}</td>
                                    <td >{{ $row->penerima }}</td>
                                    <td>{{ $row->uraian }}</td>
                                    <td>{{ $row->jenisPembayaran->nama_jenis_pembayaran ?? '-' }}</td>
                                    <td class="text-end font-monospace">@currency($row->kredit)</td>
                                    <td class="text-end font-monospace">@currency($row->nilai_rupiah)</td>
                                    <td>{{ $row->keterangan}}</td>
                                    <td>{{ $row->no_sap}}</td>
                                    <td class="text-center">
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
                                                data-sub="{{ $row->id_sub_kriteria }}"
                                                data-item="{{ $row->id_item_sub_kriteria }}"
                                                data-jenis="{{ $row->id_jenis_pembayaran }}"
                                                data-kredit="{{ $row->kredit }}"
                                                data-nilai="{{ $row->nilai_rupiah }}"
                                                data-keterangan="{{ $row->keterangan }}">
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
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>


    <script>
        new DataTable('#example',{
            ordering: false
        });
    </script>
</body>
</html>