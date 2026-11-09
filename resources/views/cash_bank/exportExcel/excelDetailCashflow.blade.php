<table>
    {{-- Header Judul Dokumen --}}
    <tr>
        <td colspan="10" style="font-size: 13px; font-weight: bold; color: #0d3b6e;">PT PERKEBUNAN NUSANTARA IV REGIONAL V</td>
    </tr>
    <tr>
        <td colspan="10" style="font-size: 15px; font-weight: bold; color: #0d3b6e;">RINCIAN TRANSAKSI ARUS KAS (CASH FLOW)</td>
    </tr>
    <tr>
        <td colspan="10"></td>
    </tr>

    {{-- Metadata Filter & Info --}}
    <tr>
        <td style="font-weight: bold; background-color: #F1F5F9;">Akun / Uraian</td>
        <td colspan="4" style="background-color: #F8FAFC;">: {{ $meta['uraian'] ?? '-' }}</td>
        <td style="font-weight: bold; background-color: #F1F5F9;">Unit / Profit Ctr</td>
        <td colspan="4" style="background-color: #F8FAFC;">: {{ $meta['unit'] ?? '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; background-color: #F1F5F9;">Kode &amp; Ref</td>
        <td colspan="4" style="background-color: #F8FAFC;">: {{ $meta['kode'] ?? '-' }} (Ref: {{ $meta['reference'] ?? '-' }})</td>
        <td style="font-weight: bold; background-color: #F1F5F9;">Periode</td>
        <td colspan="4" style="background-color: #F8FAFC;">: {{ $meta['periode'] ?? '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; background-color: #F1F5F9;">Jumlah Transaksi</td>
        <td colspan="4" style="background-color: #F8FAFC;">: {{ number_format($meta['count'] ?? count($items), 0, ',', '.') }} Transaksi</td>
        <td style="font-weight: bold; background-color: #F1F5F9;">Total Nilai</td>
        <td colspan="4" style="font-weight: bold; color: #0d3b6e; background-color: #F8FAFC;">: Rp {{ number_format($meta['total'] ?? 0, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="10"></td>
    </tr>

    {{-- Header Tabel (Baris 8) --}}
    <tr>
        <th style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">No.</th>
        <th style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">Tgl Posting</th>
        <th style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">No. Dokumen</th>
        <th style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">Unit / Profit Ctr</th>
        <th style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">G/L Account</th>
        <th style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">Nama G/L Account</th>
        <th style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">Offsetting Account</th>
        <th style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">Nama Offsetting Account</th>
        <th style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">Keterangan / Text</th>
        <th style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">Nilai (Rp)</th>
    </tr>

    {{-- Data Baris (Mulai Baris 9) --}}
    @foreach ($items as $idx => $item)
        @php
            $bg = $loop->even ? 'background-color: #F8FAFC;' : '';
            $unitName = $item->bankTujuan?->nama_tujuan ?? $item->nama_profit_center ?? $item->profit_center ?? '-';
            $tglStr = $item->posting_date ? \Carbon\Carbon::parse($item->posting_date)->format('d/m/Y') : '-';
            $textStr = ($item->text && $item->text !== '-') ? $item->text : ($item->uraian ?? '-');
        @endphp
        <tr>
            <td style="{{ $bg }} text-align: center; border: 1px solid #CBD5E1;">{{ $idx + 1 }}</td>
            <td style="{{ $bg }} text-align: center; border: 1px solid #CBD5E1;">{{ $tglStr }}</td>
            <td style="{{ $bg }} text-align: center; font-weight: bold; border: 1px solid #CBD5E1;">{{ $item->document_number ?? '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #CBD5E1;">{{ $unitName }}</td>
            <td style="{{ $bg }} text-align: center; border: 1px solid #CBD5E1;">{{ $item->account ?? '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #CBD5E1;">{{ $item->gl_account_desc ?? '-' }}</td>
            <td style="{{ $bg }} text-align: center; border: 1px solid #CBD5E1;">{{ $item->offsetting_account ?? '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #CBD5E1;">{{ $item->name_of_offsetting_account ?? '-' }}</td>
            <td style="{{ $bg }} border: 1px solid #CBD5E1;">{{ $textStr }}</td>
            <td style="{{ $bg }} text-align: right; border: 1px solid #CBD5E1;">{{ $item->amount + 0 }}</td>
        </tr>
    @endforeach

    {{-- Baris Total --}}
    <tr>
        <td colspan="9" style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: right; border: 1px solid #000000;">TOTAL TRANSAKSI :</td>
        <td style="background-color: #0d3b6e; color: #FFFFFF; font-weight: bold; text-align: right; border: 1px solid #000000;">{{ ($meta['total'] ?? $items->sum('amount')) + 0 }}</td>
    </tr>
</table>
