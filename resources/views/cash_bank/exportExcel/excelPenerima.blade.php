<table>
    @php $globalNo = 1; @endphp
    @foreach($grouped as $bulanNum => $kategoriGroup)
        {{-- Month title --}}
        <tr>
            <td colspan="12" style="background-color: #1a5632; color: #ffffff; font-weight: bold; font-size: 14px;">
                PENERIMAAN ATAS PENJUALAN CPO, KERNEL, SIR 20, TBS, KSO & LAINNYA — {{ $bulanNames[$bulanNum] ?? '' }} {{ $tahun }}
            </td>
        </tr>
        {{-- Column headers --}}
        <tr>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">No</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">Penerimaan</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">Kontrak</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">Pembeli</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">Tgl. Diterima</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">No. Rekg. Penerima</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">Volume (Kg)</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">Harga (Rp)</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">Nilai</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">PPN</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">Pot. PPh</th>
            <th style="background-color: #2d7a4a; color: #ffffff; font-weight: bold;">Nilai Inc. PPN</th>
        </tr>

        @php
            $monthTotalVolume = 0;
            $monthTotalNilai = 0;
            $monthTotalPpn = 0;
            $monthTotalPotppn = 0;
            $monthTotalInc = 0;
            $catNo = 1;
        @endphp

        @foreach($kategoriGroup as $kategoriName => $rows)
            {{-- Kategori header --}}
            <tr>
                <td colspan="12" style="background-color: #e8f5e9; font-weight: bold; color: #1a5632;">
                    {{ strtoupper($kategoriName) }}
                </td>
            </tr>

            @php
                $subVolume = 0;
                $subNilai = 0;
                $subPpn = 0;
                $subPotppn = 0;
                $subInc = 0;
            @endphp

            @foreach($rows as $row)
                @php
                    $nilaiIncPpn = $row->nilai_inc_ppn ?? 0;
                    $subVolume += $row->volume;
                    $subNilai += $row->nilai;
                    $subPpn += $row->ppn;
                    $subPotppn += $row->potppn;
                    $subInc += $nilaiIncPpn;
                @endphp
                <tr>
                    <td>{{ $catNo++ }}</td>
                    <td>{{ $kategoriName }}</td>
                    <td>{{ $row->kontrak }}</td>
                    <td>{{ $row->pembeli }}</td>
                    <td>{{ ($row->tanggal && $row->tanggal !== '0000-00-00') ? \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d M Y') : '-' }}</td>
                    <td>{{ $row->no_reg }}</td>
                    <td style="text-align: right;">{{ number_format($row->volume, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row->harga, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row->nilai, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row->ppn, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row->potppn, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($nilaiIncPpn, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            {{-- Subtotal per kategori --}}
            @php
                $monthTotalVolume += $subVolume;
                $monthTotalNilai += $subNilai;
                $monthTotalPpn += $subPpn;
                $monthTotalPotppn += $subPotppn;
                $monthTotalInc += $subInc;
            @endphp
            <tr>
                <td colspan="6" style="background-color: #fff3cd; font-weight: bold;">JUMLAH {{ strtoupper($kategoriName) }}</td>
                <td style="background-color: #fff3cd; font-weight: bold; text-align: right;">{{ number_format($subVolume, 0, ',', '.') }}</td>
                <td style="background-color: #fff3cd; font-weight: bold;"></td>
                <td style="background-color: #fff3cd; font-weight: bold; text-align: right;">{{ number_format($subNilai, 0, ',', '.') }}</td>
                <td style="background-color: #fff3cd; font-weight: bold; text-align: right;">{{ number_format($subPpn, 0, ',', '.') }}</td>
                <td style="background-color: #fff3cd; font-weight: bold; text-align: right;">{{ number_format($subPotppn, 0, ',', '.') }}</td>
                <td style="background-color: #fff3cd; font-weight: bold; text-align: right;">{{ number_format($subInc, 0, ',', '.') }}</td>
            </tr>
        @endforeach

        {{-- Grand total per month --}}
        <tr>
            <td colspan="6" style="background-color: #1a5632; color: #ffffff; font-weight: bold;">TOTAL {{ $bulanNames[$bulanNum] ?? '' }}</td>
            <td style="background-color: #1a5632; color: #ffffff; font-weight: bold; text-align: right;">{{ number_format($monthTotalVolume, 0, ',', '.') }}</td>
            <td style="background-color: #1a5632; color: #ffffff; font-weight: bold;"></td>
            <td style="background-color: #1a5632; color: #ffffff; font-weight: bold; text-align: right;">{{ number_format($monthTotalNilai, 0, ',', '.') }}</td>
            <td style="background-color: #1a5632; color: #ffffff; font-weight: bold; text-align: right;">{{ number_format($monthTotalPpn, 0, ',', '.') }}</td>
            <td style="background-color: #1a5632; color: #ffffff; font-weight: bold; text-align: right;">{{ number_format($monthTotalPotppn, 0, ',', '.') }}</td>
            <td style="background-color: #1a5632; color: #ffffff; font-weight: bold; text-align: right;">{{ number_format($monthTotalInc, 0, ',', '.') }}</td>
        </tr>
        {{-- Spacer row between months --}}
        <tr><td colspan="12"></td></tr>
    @endforeach
</table>