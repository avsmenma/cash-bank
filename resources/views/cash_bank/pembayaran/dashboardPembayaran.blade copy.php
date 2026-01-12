@push('style')
<style>
#example {
    table-layout: auto;
}

#example th:first-child,
#example td:first-child {
    min-width: 250px;
}

#example th:not(:first-child),
#example td:not(:first-child) {
    min-width: 100px;
    text-align: right;
}
</style>
@endpush
@php
    function formatMinus($angka) {
        return $angka < 0
            ? '(' . number_format(abs($angka), 0, ',', '.') . ')'
            : number_format($angka, 0, ',', '.');
    }

    
@endphp
<div class="table-responsive">
<table class="table table-bordered table-sm" id="example">

    <thead class="bg-navy text-center">
        <tr>
            @foreach($bulanListFiltered as $bulan)
                <th colspan="4">{{ ucfirst($bulan) }}</th>
            @endforeach
            <th colspan="4">TOTAL</th>
        </tr>
        <tr>
          @foreach($bulanListFiltered as $bulan)
                <th>Rencana</th>
                <th>Realisasi</th>
                <th>Selisih</th>
                <th>%Tase</th>
            @endforeach
            <th>Rencana</th>
            <th>Realisasi</th>
            <th>Selisih</th>
            <th>%Tase</th>
        </tr>
    </thead>

<tbody>

{{-- ================= PENERIMA ================= --}}
<tr class="bg-orange font-weight-bold">
    <td colspan="{{ 1 + count($bulanListFiltered) * 4 }}">PENERIMA</td>
</tr>

@foreach($dataPenerima as $kategori => $bulanData)
<tr>
    <td>{{ $kategori }}</td>
    @foreach($bulanListFiltered as $b)
        @php
            $r  = $bulanData[$b]['rencana'] ?? 0;
            $re = $bulanData[$b]['realisasi'] ?? 0;
            $s  = $bulanData[$b]['selisih'] ?? 0;
            $p  = $bulanData[$b]['persen'] ?? 0;
        @endphp
        <td class="text-right">{{ formatMinus($r,0,',','.') }}</td>
        <td class="text-right">{{ formatMinus($re,0,',','.') }}</td>
        <td class="text-right">{{ formatMinus($s,0,',','.') }}</td>
        <td class="text-right">{{ formatMinus($p,2) }}%</td>
    @endforeach
</tr>
@endforeach

{{-- TOTAL PENERIMA --}}
<tr class="table-info font-weight-bold">
    <td class="text-right">Total Penerima</td>
    @foreach($bulanListFiltered as $b)
        @php
            $r  = $totalPenerima[$b]['rencana'] ?? 0;
            $re = $totalPenerima[$b]['realisasi'] ?? 0;
            $s  = $totalPenerima[$b]['selisih'] ?? 0;
            $p  = $totalPenerima[$b]['persen'] ?? 0;
        @endphp
        <td class="text-right">{{ formatMinus($r,0,',','.') }}</td>
        <td class="text-right">{{ formatMinus($re,0,',','.') }}</td>
        <td class="text-right">{{ formatMinus($s,0,',','.') }}</td>
        <td class="text-right">{{ formatMinus($p,2) }}%</td>
    @endforeach
</tr>

{{-- ================= DROPPING ================= --}}
<tr>
    <td></td>
</tr>
<tr class="bg-cyan font-weight-bold">
    <td colspan="{{ 1 + count($bulanListFiltered) * 4 }}">DROPPING</td>
</tr>

@foreach($dataDropping as $kategori => $subs)
<tr class="table-secondary font-weight-bold">
    <td colspan="{{ 1 + count($bulanListFiltered) * 4 }}">{{ $kategori }}</td>
</tr>

    @foreach($subs as $sub => $items)
    <tr class="table-light">
        <td colspan="{{ 1 + count($bulanListFiltered) * 4 }}">{{ $sub }}</td>
    </tr>

        @foreach($items as $item => $bulanData)
        <tr>
            <td>{{ " - " .$item }}</td>
            @foreach($bulanListFiltered as $b)
                @php
                    $r  = $bulanData[$b]['rencana'] ?? 0;
                    $re = $bulanData[$b]['realisasi'] ?? 0;
                    $s  = $bulanData[$b]['selisih'] ?? 0;
                    $p  = $bulanData[$b]['persen'] ?? 0;
                @endphp
                <td class="text-right">{{ formatMinus($r,0,',','.') }}</td>
                <td class="text-right">{{ formatMinus($re,0,',','.') }}</td>
                <td class="text-right">{{ formatMinus($s,0,',','.') }}</td>
                <td class="text-right">{{ formatMinus($p,2) }}%</td>
            @endforeach
        </tr>
        @endforeach
    @endforeach
@endforeach

{{-- TOTAL DROPPING --}}
<tr class="table-primary font-weight-bold">
    <td class="text-right">Total Dropping</td>
    @foreach($bulanListFiltered as $b)
        @php
            $r  = $totalDropping[$b]['rencana'] ?? 0;
            $re = $totalDropping[$b]['realisasi'] ?? 0;
            $s  = $totalDropping[$b]['selisih'] ?? 0;
            $p  = $totalDropping[$b]['persen'] ?? 0;
        @endphp
        <td class="text-right">{{ formatMinus($r,0,',','.') }}</td>
        <td class="text-right">{{ formatMinus($re,0,',','.') }}</td>
        <td class="text-right">{{ formatMinus($s,0,',','.') }}</td>
        <td class="text-right">{{ formatMinus($p,2) }}%</td>
    @endforeach
</tr>

</tbody>

<tfoot class="bg-dark text-white font-weight-bold">
<tr class="bg-orange">
    <th class="text-right">Selisih Penerima - Dropping</th>
    @foreach($bulanListFiltered as $b)
        @php
            $r  = ($totalPenerima[$b]['rencana'] ?? 0) - ($totalDropping[$b]['rencana'] ?? 0);
            $re = ($totalPenerima[$b]['realisasi'] ?? 0) - ($totalDropping[$b]['realisasi'] ?? 0);
            $s  = $re - $r;
            $p  = $r != 0 ? ($re / $r) * 100 : 0;
        @endphp
        <th class="text-right">{{ formatMinus($r,0,',','.') }}</th>
        <th class="text-right">{{ formatMinus($re,0,',','.') }}</th>
        <th class="text-right">{{ formatMinus($s,0,',','.') }}</th>
        <th class="text-right">{{ formatMinus($p,2) }}%</th>
    @endforeach
</tr>
</tfoot>

</table>
</div>