<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Bank Keluar</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 10mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10px; color: #1f2937; margin: 16px; }

        .kop { text-align: center; margin-bottom: 12px; }
        .kop h1 { font-size: 15px; margin: 0; color: #1e3a5f; letter-spacing: .5px; }
        .kop .sub { font-size: 10.5px; color: #6b7280; margin-top: 2px; }
        .kop .filter { font-size: 10.5px; margin-top: 6px; font-weight: 600; }
        .kop .cetak { font-size: 9px; color: #9ca3af; margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #b3bfcc; padding: 3px 5px; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }
        thead th { background: #1e3a5f; color: #fff; font-size: 9.5px; text-align: center; padding: 5px 4px; }
        thead { display: table-header-group; }   /* header terulang tiap halaman cetak */
        tbody tr:nth-child(even) td { background: #f8fafc; }
        td.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        td.ctr { text-align: center; }
        tfoot td { background: #1e3a5f; color: #fff; font-weight: 700; }

        .no-print { margin-bottom: 10px; }
        .no-print button { background: #1e3a5f; color: #fff; border: 0; border-radius: 4px; padding: 6px 14px; cursor: pointer; }
        @media print { .no-print { display: none; } body { margin: 0; } }
    </style>
</head>

<body>
    <div class="no-print">
        <button onclick="window.print()">🖨 Cetak / Simpan PDF</button>
    </div>

    <div class="kop">
        <h1>LAPORAN BANK KELUAR</h1>
        <div class="sub">Cash Bank — Sistem Monitoring Kas &amp; Bank Regional</div>
        <div class="filter">{{ $filterInfo }} • {{ number_format($data->count(), 0, ',', '.') }} transaksi</div>
        <div class="cetak">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:6%;">Agenda</th>
                <th style="width:6%;">Tanggal</th>
                <th style="width:10%;">Sumber Dana</th>
                <th style="width:9%;">Bank Tujuan</th>
                <th style="width:8%;">Kriteria</th>
                <th style="width:7%;">Sub Kriteria</th>
                <th style="width:7%;">Item Sub</th>
                <th style="width:6%;">Jenis</th>
                <th style="width:9%;">Penerima</th>
                <th style="width:16%;">Uraian</th>
                <th style="width:7%;">Kredit (Rp)</th>
                <th style="width:6%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $row)
                <tr>
                    <td class="ctr">{{ $index + 1 }}</td>
                    <td class="ctr">{{ $row->agenda_tahun ?: '-' }}</td>
                    <td class="ctr">{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $row->sumberDana->nama_sumber_dana ?? '-' }}</td>
                    <td>{{ $row->bankTujuan->nama_tujuan ?? '-' }}</td>
                    <td>{{ $row->kategori->nama_kriteria ?? '-' }}</td>
                    <td>{{ $row->subKriteria->nama_sub_kriteria ?? '-' }}</td>
                    <td>{{ $row->itemSubKriteria->nama_item_sub_kriteria ?? '-' }}</td>
                    <td class="ctr">{{ $row->jenisPembayaran->nama_jenis_pembayaran ?? '-' }}</td>
                    <td>{{ $row->penerima ?: '-' }}</td>
                    <td>{{ $row->uraian ?: '-' }}</td>
                    <td class="num">{{ number_format($row->kredit ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $row->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="ctr" style="padding:20px; color:#6b7280;">
                        Tidak ada data pada filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($data->count())
            <tfoot>
                <tr>
                    <td colspan="11" style="text-align:right;">TOTAL</td>
                    <td class="num">{{ number_format($data->sum('kredit'), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
</body>

</html>
