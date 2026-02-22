<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapan Pembayaran</title>
</head>

<body>
    <div class="table-wrapper">
        <table class="table table-bordered  table-hover align-middle mb-0" id="printArea">
            <thead class="table-primary">
                <tr class="text-center">
                    <th width="50" class="py-3">No</th>
                    <th class="py-3">Tanggal</th>
                    <th class="py-3">Agenda</th>
                    <th class="py-3">No Bukti</th>
                    <th class="py-3">Bank Tujuan</th>
                    <th class="py-3">Penerima</th>
                    <th class="py-3">Debet</th>
                    <th class="py-3">Kredit</th>
                    <th class="py-3">Saldo Akhir</th>
                    <th class="py-3">No SAP</th>
                    <th width="80" class="py-3">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($data as $i => $row)
                    <tr class="text-center">
                        <td class="fw-semibold">{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}</td>
                        <td>{{ $row->agenda ?? '-' }}</td>
                        <td>{{ $row->no_bukti ?? '-' }}</td>
                        <td>{{ $row->nama_tujuan ?? '-' }}</td>
                        <td>{{ $row->penerima ?? '-' }}</td>
                        <td class="text-end">@currency($row->debet ?? 0)</td>
                        <td class="text-end">@currency($row->kredit ?? 0)</td>
                        <td class="fw-bold text-end">
                            @currency($row->saldo_akhir ?? 0)
                        </td>
                        <td class="text-center">{{ $row->no_sap ?? '-' }}</td>

                        {{-- AKSI HANYA UNTUK BANK KELUAR --}}
                        <td class="aksi">
                            @if($row->id_bank_keluar)
                                <button class="btn btn-sm btn-outline-primary btn-edit-sap" data-bs-toggle="modal"
                                    data-bs-target="#editSAP" data-id="{{ $row->id_bank_keluar }}"
                                    data-no_sap="{{ $row->no_sap }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                            @else
                                <span class="text-muted"></span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-2">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Tidak ada data transaksi
                        </td>
                    </tr>
                @endforelse
            </tbody>

            {{-- FOOTER --}}
            <tfoot class="table-warning">
                <tr class="text-center">
                    <td colspan="8" class="fw-bold py-3 text-uppercase">
                        Saldo Akhir
                    </td>
                    <td class="fw-bold text-danger fs-5 py-3 text-end">
                        @currency($data->last()->saldo_akhir ?? 0)
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>

</html>