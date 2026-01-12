@push('style')
<style>
    #example {
    table-layout: auto !important;
    width: 100% !important;
    }


    #example td {
        white-space: nowrap;        /* biar kolom melebar */
        vertical-align: middle;
    }
</style>
@endpush

@php
$kategoriTotal = [];

foreach ($data as $kategori => $subs) {
    foreach ($bulanList as $b) {
        $kategoriTotal[$kategori][$b] = [
            'rencana' => 0,
            'realisasi' => 0,
        ];
    }
}
$footer = [];
foreach ($bulanList as $b) {
    $footer[$b] = [
        'rencana' => 0,
        'realisasi' => 0
    ];
}
@endphp

<div class="row">
    <div class="col-12 table-responsive">
    <table class="table table-bordered" id="example">

    <thead class="bg-navy text-center">
        <tr>
            <th rowspan="2" colspan="5">Kategori</th>
            @foreach($bulanList as $b => $n)
                <th colspan="4">{{ ucfirst($b) }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($bulanList as $b => $n)
                <th>Rencana</th>
                <th>Realisasi</th>
                <th>Selisih</th>
                <th>%</th>
            @endforeach
        </tr>
    </thead>

        <tbody>
        @foreach($data as $kategori => $subs)
            <tr class=" font-weight-bold">
                <td colspan="{{ 1 + (count($bulanList) * 4) }}">{{ $kategori }}</td>
            </tr>

            @foreach($subs as $sub => $items)
                <tr class="table-light">
                    <td colspan="{{ 1 + (count($bulanList) * 4) }}">{{ $sub }}</td>
                </tr>

                @foreach($items as $item => $bulanData)
                    <tr>
                        <td colspan="5">{{ $item }}</td>

                        @foreach($bulanList as $b)
                            @php
                                $r  = $bulanData[$b]['rencana'] ?? 0;
                                $re = $bulanData[$b]['realisasi'] ?? 0;
                                $s  = $bulanData[$b]['selisih'] ?? 0;
                                $p  = $bulanData[$b]['persen'] ?? 0;
                                $kategoriTotal[$kategori][$b]['rencana']   += $r;
                                $kategoriTotal[$kategori][$b]['realisasi'] += $re;
                                $footer[$b]['rencana']   += $r;
                                $footer[$b]['realisasi'] += $re;

                            @endphp
                            <td>{{ number_format($r,0,',','.') }}</td>
                            <td>{{ number_format($re,0,',','.') }}</td>
                            <td>{{ number_format($s,0,',','.') }}</td>
                            <td>{{ number_format($p,2) }}%</td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
            <tr class="table-secondary font-weight-bold">
                <td colspan="5">Total {{ $kategori }}</td>

                @foreach($bulanList as $b)
                    @php
                        $r  = $kategoriTotal[$kategori][$b]['rencana'];
                        $re = $kategoriTotal[$kategori][$b]['realisasi'];
                        $s  = $re - $r;
                        $p  = $r > 0 ? ($re / $r) * 100 : 0;
                        
                    @endphp
                    <td class="text-right">{{ number_format($r,0,',','.') }}</td>
                    <td class="text-right">{{ number_format($re,0,',','.') }}</td>
                    <td class="text-right">{{ number_format($s,0,',','.') }}</td>
                    <td class="text-right">{{ number_format($p,2) }}%</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>

        <tfoot class="table-warning font-weight-bold">
        <tr>
            <th colspan="5">Total</th>
            @foreach($bulanList as $b)
                @php
                    $r = $footer[$b]['rencana'] ?? 0;
                    $re = $footer[$b]['realisasi'] ?? 0;
                    $s = $re - $r;
                    $p = $r > 0 ? ($re / $r) * 100 : 0;
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
</div>
