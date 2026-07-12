<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Bank Masuk</title>
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
        <h1>LAPORAN BANK MASUK</h1>
        <div class="sub">Cash Bank — Sistem Monitoring Kas &amp; Bank Regional</div>
        <div class="filter">{{ $filterInfo }} • {{ number_format($data->count(), 0, ',', '.') }} transaksi</div>
        <div class="cetak">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:7%;">Agenda</th>
                <th style="width:7%;">Tanggal</th>
                <th style="width:12%;">Sumber Dana</th>
                <th style="width:11%;">Bank Tujuan</th>
                <th style="width:9%;">Kategori</th>
                <th style="width:7%;">Jenis</th>
                <th style="width:10%;">Dari / Penerima</th>
                <th style="width:19%;">Uraian</th>
                <th style="width:8%;">Debet (Rp)</th>
                <th style="width:7%;">Keterangan</th>
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
                    <td class="ctr">{{ $row->jenisPembayaran->nama_jenis_pembayaran ?? '-' }}</td>
                    <td>{{ $row->penerima ?: '-' }}</td>
                    <td>{{ $row->uraian ?: '-' }}</td>
                    <td class="num">{{ number_format($row->debet ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $row->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="ctr" style="padding:20px; color:#6b7280;">
                        Tidak ada data pada filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($data->count())
            <tfoot>
                <tr>
                    <td colspan="9" style="text-align:right;">TOTAL</td>
                    <td class="num">{{ number_format($data->sum('debet'), 0, ',', '.') }}</td>
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
