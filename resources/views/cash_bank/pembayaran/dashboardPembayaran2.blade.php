
@push('style')
#example {
    table-layout: auto;
}

#example th:first-child,
#example td:first-child {
    min-width: 350px;
}
@endpush
<div class="table-responsive">
    <table class="table table-bordered table-sm" id="example">

    <thead class="bg-navy text-center">
        <tr>
            <th rowspan="2">URAIAN</th>
            @foreach($bulanList as $bulan)
                <th colspan="4">{{ ucfirst($bulan) }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($bulanList as $bulan)
                <th>Rencana</th>
                <th>Realisasi</th>
                <th>Selisih</th>
                <th>%</th>
            @endforeach
        </tr>
    </thead>

    <tbody>

    {{-- ================= PENERIMA ================= --}}
    <tr class="table-primary font-weight-bold">
        <td colspan="{{ 1 + count($bulanList) * 4 }}">PENERIMA</td>
    </tr>

    @foreach($dataPenerima as $kategori => $bulanData)
    <tr>
        <td>{{ $kategori }}</td>
        @foreach($bulanList as $b)
            @php
                $r  = $bulanData[$b]['rencana'] ?? 0;
                $re = $bulanData[$b]['realisasi'] ?? 0;
                $s  = $bulanData[$b]['selisih'] ?? 0;
                $p  = $bulanData[$b]['persen'] ?? 0;
            @endphp
            <td class="text-right">{{ number_format($r,0,',','.') }}</td>
            <td class="text-right">{{ number_format($re,0,',','.') }}</td>
            <td class="text-right">{{ number_format($s,0,',','.') }}</td>
            <td class="text-right">{{ number_format($p,2) }}%</td>
        @endforeach
    </tr>
    @endforeach

    {{-- TOTAL PENERIMA --}}
    <tr class="table-info font-weight-bold">
        <td>Total Penerima</td>
        @foreach($bulanList as $b)
            @php
                $r  = $totalPenerima[$b]['rencana'] ?? 0;
                $re = $totalPenerima[$b]['realisasi'] ?? 0;
                $s  = $totalPenerima[$b]['selisih'] ?? 0;
                $p  = $totalPenerima[$b]['persen'] ?? 0;
            @endphp
            <td class="text-right">{{ number_format($r,0,',','.') }}</td>
            <td class="text-right">{{ number_format($re,0,',','.') }}</td>
            <td class="text-right">{{ number_format($s,0,',','.') }}</td>
            <td class="text-right">{{ number_format($p,2) }}%</td>
        @endforeach
    </tr>

    {{-- ================= DROPPING ================= --}}
    <tr class="table-danger font-weight-bold">
        <td colspan="{{ 1 + count($bulanList) * 4 }}">DROPPING</td>
    </tr>

    @foreach($dataDropping as $kategori => $subs)
    <tr class="table-secondary font-weight-bold">
        <td colspan="{{ 1 + count($bulanList) * 4 }}">{{ $kategori }}</td>
    </tr>

        @foreach($subs as $sub => $items)
        <tr class="table-light">
            <td colspan="{{ 1 + count($bulanList) * 4 }}">{{ $sub }}</td>
        </tr>

            @foreach($items as $item => $bulanData)
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $item }}</td>
                @foreach($bulanList as $b)
                    @php
                        $r  = $bulanData[$b]['rencana'] ?? 0;
                        $re = $bulanData[$b]['realisasi'] ?? 0;
                        $s  = $bulanData[$b]['selisih'] ?? 0;
                        $p  = $bulanData[$b]['persen'] ?? 0;
                    @endphp
                    <td class="text-right">{{ number_format($r,0,',','.') }}</td>
                    <td class="text-right">{{ number_format($re,0,',','.') }}</td>
                    <td class="text-right">{{ number_format($s,0,',','.') }}</td>
                    <td class="text-right">{{ number_format($p,2) }}%</td>
                @endforeach
            </tr>
            @endforeach
        @endforeach
    @endforeach

    {{-- TOTAL DROPPING --}}
    <tr class="table-warning font-weight-bold">
        <td>Total Dropping</td>
        @foreach($bulanList as $b)
            @php
                $r  = $totalDropping[$b]['rencana'] ?? 0;
                $re = $totalDropping[$b]['realisasi'] ?? 0;
                $s  = $totalDropping[$b]['selisih'] ?? 0;
                $p  = $totalDropping[$b]['persen'] ?? 0;
            @endphp
            <td class="text-right">{{ number_format($r,0,',','.') }}</td>
            <td class="text-right">{{ number_format($re,0,',','.') }}</td>
            <td class="text-right">{{ number_format($s,0,',','.') }}</td>
            <td class="text-right">{{ number_format($p,2) }}%</td>
        @endforeach
    </tr>

    {{-- SELISIH PENERIMA - DROPPING --}}
    <tr class="bg-dark text-white font-weight-bold">
        <th>Selisih Penerima - Dropping</th>
        @foreach($bulanList as $b)
            @php
                $r  = ($totalPenerima[$b]['rencana'] ?? 0) - ($totalDropping[$b]['rencana'] ?? 0);
                $re = ($totalPenerima[$b]['realisasi'] ?? 0) - ($totalDropping[$b]['realisasi'] ?? 0);
                $s  = $re - $r;
                $p  = $r != 0 ? ($re / $r) * 100 : 0;
            @endphp
            <th class="text-right">{{ number_format($r,0,',','.') }}</th>
            <th class="text-right">{{ number_format($re,0,',','.') }}</th>
            <th class="text-right">{{ number_format($s,0,',','.') }}</th>
            <th class="text-right">{{ number_format($p,2) }}%</th>
        @endforeach
    </tr>

    {{-- ================= PEMBAYARAN ================= --}}
    <tr class="table-success font-weight-bold">
        <td colspan="{{ 1 + count($bulanList) * 4 }}">PEMBAYARAN</td>
    </tr>

    @foreach($dataPembayaran as $kategori => $subs)
    <tr class="table-secondary font-weight-bold">
        <td colspan="{{ 1 + count($bulanList) * 4 }}">{{ $kategori }}</td>
    </tr>

        @foreach($subs as $sub => $items)
        <tr class="table-light">
            <td colspan="{{ 1 + count($bulanList) * 4 }}">{{ $sub }}</td>
        </tr>

            @foreach($items as $item => $bulanData)
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $item }}</td>
                @foreach($bulanList as $b)
                    @php
                        $r  = $bulanData[$b]['rencana'] ?? 0;
                        $re = $bulanData[$b]['realisasi'] ?? 0;
                        $s  = $bulanData[$b]['selisih'] ?? 0;
                        $p  = $bulanData[$b]['persen'] ?? 0;
                    @endphp
                    <td class="text-right">{{ number_format($r,0,',','.') }}</td>
                    <td class="text-right">{{ number_format($re,0,',','.') }}</td>
                    <td class="text-right">{{ number_format($s,0,',','.') }}</td>
                    <td class="text-right">{{ number_format($p,2) }}%</td>
                @endforeach
            </tr>
            @endforeach
        @endforeach
    @endforeach

    {{-- TOTAL PEMBAYARAN --}}
    <tr class="table-success font-weight-bold">
        <td>Total Pembayaran</td>
        @foreach($bulanList as $b)
            @php
                $r  = $totalPembayaran[$b]['rencana'] ?? 0;
                $re = $totalPembayaran[$b]['realisasi'] ?? 0;
                $s  = $totalPembayaran[$b]['selisih'] ?? 0;
                $p  = $totalPembayaran[$b]['persen'] ?? 0;
            @endphp
            <td class="text-right">{{ number_format($r,0,',','.') }}</td>
            <td class="text-right">{{ number_format($re,0,',','.') }}</td>
            <td class="text-right">{{ number_format($s,0,',','.') }}</td>
            <td class="text-right">{{ number_format($p,2) }}%</td>
        @endforeach
    </tr>

    </tbody>

    <tfoot class="bg-secondary text-white font-weight-bold">
    {{-- SELISIH PEMBAYARAN - PENERIMA --}}
    <tr>
        <th>Selisih Pembayaran - Penerima</th>
        @foreach($bulanList as $b)
            @php
                $r  = ($totalPembayaran[$b]['rencana'] ?? 0) - ($totalPenerima[$b]['rencana'] ?? 0);
                $re = ($totalPembayaran[$b]['realisasi'] ?? 0) - ($totalPenerima[$b]['realisasi'] ?? 0);
                $s  = $re - $r;
                $p  = $r != 0 ? ($re / $r) * 100 : 0;
            @endphp
            <th class="text-right">{{ number_format($r,0,',','.') }}</th>
            <th class="text-right">{{ number_format($re,0,',','.') }}</th>
            <th class="text-right">{{ number_format($s,0,',','.') }}</th>
            <th class="text-right">{{ number_format($p,2) }}%</th>
        @endforeach
    </tr>

    {{-- SELISIH PEMBAYARAN - DROPPING --}}
    <tr>
        <th>Selisih Pembayaran - Dropping</th>
        @foreach($bulanList as $b)
            @php
                $r  = ($totalPembayaran[$b]['rencana'] ?? 0) - ($totalDropping[$b]['rencana'] ?? 0);
                $re = ($totalPembayaran[$b]['realisasi'] ?? 0) - ($totalDropping[$b]['realisasi'] ?? 0);
                $s  = $re - $r;
                $p  = $r != 0 ? ($re / $r) * 100 : 0;
            @endphp
            <th class="text-right">{{ number_format($r,0,',','.') }}</th>
            <th class="text-right">{{ number_format($re,0,',','.') }}</th>
            <th class="text-right">{{ number_format($s,0,',','.') }}</th>
            <th class="text-right">{{ number_format($p,2) }}%</th>
        @endforeach
    </tr>
    </tfoot>

    </table>
    </div>
@push('script')
    <script>
        $('#example2').DataTable({
            autoWidth: false,
            scrollX: true,
            paging: false,
            searching: false,
            ordering: false,
            info: false
        });
    </script>
@endpush