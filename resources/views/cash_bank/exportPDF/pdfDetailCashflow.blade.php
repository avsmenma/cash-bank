<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Transaksi Arus Kas - {{ $meta['uraian'] ?? 'Detail' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            padding: 15px 20px;
            background: #ffffff;
        }

        /* Kop & Judul Laporan */
        .kop-container {
            text-align: center;
            border-bottom: 2.5px double #0d3b6e;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-perusahaan {
            font-size: 13px;
            font-weight: bold;
            color: #0d3b6e;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .kop-judul {
            font-size: 15px;
            font-weight: bold;
            color: #0d3b6e;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Kartu Info Metadata */
        .info-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #0d3b6e;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .info-card table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .info-card td {
            padding: 2.5px 4px;
            border: none;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
            width: 120px;
        }
        .info-val {
            color: #0f172a;
            font-weight: 600;
        }
        .badge-total {
            background: #0d3b6e;
            color: #ffffff;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 3px;
            display: inline-block;
        }

        /* Tabel Data Transaksi */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-bottom: 12px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
        }
        table.data-table thead th {
            background-color: #0d3b6e !important;
            color: #ffffff !important;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            font-size: 10.5px;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.data-table tfoot td {
            background-color: #0d3b6e !important;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #0d3b6e;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-danger { color: #dc2626 !important; }
        .sub-desc { font-size: 9.5px; color: #64748b; margin-top: 1px; }

        /* Tanda Tangan & Footer */
        .footer-section {
            margin-top: 14px;
            width: 100%;
            page-break-inside: avoid;
        }
        .footer-left {
            float: left;
            width: 50%;
            font-size: 10px;
            color: #64748b;
        }
        .footer-right {
            float: right;
            width: 40%;
            text-align: center;
            font-size: 11px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .clearfix::after {
            content: '';
            display: table;
            clear: both;
        }

        /* Print Settings */
        @page {
            size: A4 landscape;
            margin: 10mm 12mm;
        }
        @media print {
            body {
                padding: 0;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    {{-- KOP LAPORAN --}}
    <div class="kop-container">
        <div class="kop-perusahaan">PT PERKEBUNAN NUSANTARA IV REGIONAL V</div>
        <div class="kop-judul">RINCIAN TRANSAKSI ARUS KAS (CASH FLOW)</div>
    </div>

    {{-- METADATA INFO --}}
    <div class="info-card">
        <table>
            <tr>
                <td class="info-label">Akun / Uraian</td>
                <td style="width: 10px;">:</td>
                <td class="info-val" style="width: 40%;">{{ $meta['uraian'] ?? '-' }}</td>

                <td class="info-label">Unit / Profit Center</td>
                <td style="width: 10px;">:</td>
                <td class="info-val">{{ $meta['unit'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Kode &amp; Reference</td>
                <td>:</td>
                <td class="info-val">{{ $meta['kode'] ?? '-' }} <span style="color:#64748b; font-weight:normal;">(Ref: {{ $meta['reference'] ?? '-' }})</span></td>

                <td class="info-label">Periode</td>
                <td>:</td>
                <td class="info-val">{{ $meta['periode'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Jumlah Transaksi</td>
                <td>:</td>
                <td class="info-val">{{ number_format($meta['count'] ?? count($items), 0, ',', '.') }} Transaksi</td>

                <td class="info-label">Total Realisasi</td>
                <td>:</td>
                <td class="info-val">
                    @php
                        $tot = $meta['total'] ?? $items->sum('amount');
                    @endphp
                    <span class="badge-total">
                        Rp {{ $tot < 0 ? '(' . number_format(abs($tot), 0, ',', '.') . ')' : number_format($tot, 0, ',', '.') }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- TABEL DATA TRANSAKSI --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No.</th>
                <th style="width: 75px;">Tgl Posting</th>
                <th style="width: 85px;">No. Dokumen</th>
                <th style="width: 110px;">Unit / Profit Ctr</th>
                <th style="width: 140px;">G/L Account</th>
                <th style="width: 140px;">Offsetting Account</th>
                <th>Keterangan / Text</th>
                <th style="width: 115px;">Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $idx => $item)
                @php
                    $unitName = $item->bankTujuan?->nama_tujuan ?? $item->nama_profit_center ?? $item->profit_center ?? '-';
                    $tglStr = $item->posting_date ? \Carbon\Carbon::parse($item->posting_date)->format('d/m/Y') : '-';
                    $textStr = ($item->text && $item->text !== '-') ? $item->text : ($item->uraian ?? '-');
                    $amt = (float) $item->amount;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $tglStr }}</td>
                    <td class="text-center font-bold">{{ $item->document_number ?? '-' }}</td>
                    <td>{{ $unitName }}</td>
                    <td>
                        <span class="font-bold">{{ $item->account ?? '-' }}</span>
                        @if(!empty($item->gl_account_desc) && $item->gl_account_desc !== '-')
                            <div class="sub-desc">{{ $item->gl_account_desc }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="font-bold">{{ $item->offsetting_account ?? '-' }}</span>
                        @if(!empty($item->name_of_offsetting_account) && $item->name_of_offsetting_account !== '-')
                            <div class="sub-desc">{{ $item->name_of_offsetting_account }}</div>
                        @endif
                    </td>
                    <td>{{ $textStr }}</td>
                    <td class="text-right font-bold {{ $amt < 0 ? 'text-danger' : '' }}">
                        @if($amt < 0)
                            ({{ number_format(abs($amt), 0, ',', '.') }})
                        @elseif($amt > 0)
                            {{ number_format($amt, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">
                        Tidak ada transaksi ditemukan untuk rincian ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            @php
                $totAmount = (float) ($meta['total'] ?? $items->sum('amount'));
            @endphp
            <tr>
                <td colspan="7" class="text-right font-bold" style="padding-right: 12px;">TOTAL TRANSAKSI :</td>
                <td class="text-right font-bold">
                    @if($totAmount < 0)
                        ({{ number_format(abs($totAmount), 0, ',', '.') }})
                    @else
                        {{ number_format($totAmount, 0, ',', '.') }}
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- FOOTER & TANDA TANGAN --}}
    <div class="footer-section clearfix">
        <div class="footer-left">
            <p>Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i:s') }} WIB</p>
            <p>Sistem Informasi Cash &amp; Bank Regional V</p>
        </div>
        <div class="footer-right">
            <p>Pontianak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <br><br><br>
            <p class="signature-name">Herry Wahyudi</p>
            <p>Kepala Bagian Akuntansi &amp; Keuangan</p>
        </div>
    </div>

    <script type="text/javascript">
        window.onload = function() {
            if (!navigator.webdriver && !window.location.search.includes('no_print=1')) {
                window.print();
            }
        };
    </script>
</body>
</html>
