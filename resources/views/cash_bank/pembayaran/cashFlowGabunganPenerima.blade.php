@push('styles')
<style>
    #example {
        table-layout: auto !important;
        width: 100% !important;
    }

    #example th,
    #example td {
        white-space: nowrap;
        vertical-align: middle;
    }
</style>
@endpush

@php
$footer = [];
foreach ($bulanListFiltered as $b => $n) {
    $footer[$b] = [
        'rencana' => 0,
        'realisasi' => 0
    ];
}
// Total keseluruhan footer
$footerGrandTotal = [
    'rencana' => 0,
    'realisasi' => 0,
    'selisih' => 0,
    'persen' => 0
];
@endphp

<div class="row">
    <div class="col-12 table-responsive">
        <table class="table table-bordered" id="example">
            <thead class="bg-navy text-center">
                <tr>
                    <th rowspan="2">Kategori</th>
                    @foreach($bulanListFiltered as $b => $n)
                        <th colspan="4">{{ ucfirst($b) }}</th>
                    @endforeach
                    <th colspan="4">Total {{$tahun}}</th>
                </tr>
                <tr>
                    @foreach($bulanListFiltered as $b => $n)
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
                @foreach($data as $kategori => $bulan)
                @php
                    $totalRencana = 0;
                    $totalRealisasi = 0;
                @endphp
                <tr>
                    <td>{{ $kategori }}</td>
                    @foreach($bulanListFiltered as $b => $n)
                        @php
                            $v = $bulan[$b] ?? ['rencana' => 0, 'realisasi' => 0, 'selisih' => 0, 'persen' => 0];
                            $footer[$b]['rencana']   += $v['rencana'];
                            $footer[$b]['realisasi'] += $v['realisasi'];
                            // total tahunan per kategori
                            $totalRencana   += $v['rencana'];
                            $totalRealisasi += $v['realisasi'];
                        @endphp
                        <td class="text-right">{{ number_format($v['rencana'],0,',','.') }}</td>
                        <td class="text-right">{{ number_format($v['realisasi'],0,',','.') }}</td>
                        <td class="text-right">{{ number_format($v['selisih'],0,',','.') }}</td>
                        <td class="text-right">{{ number_format($v['persen'],2) }}%</td>
                    @endforeach
                    @php
                        $totalSelisih = $totalRealisasi - $totalRencana;
                        $totalPersen  = $totalRencana > 0
                            ? ($totalRealisasi / $totalRencana) * 100
                            : 0;
                    @endphp

                    <td class="text-right font-weight-bold">
                        {{ number_format($totalRencana,0,',','.') }}
                    </td>
                    <td class="text-right font-weight-bold">
                        {{ number_format($totalRealisasi,0,',','.') }}
                    </td>
                    <td class="text-right font-weight-bold">
                        {{ number_format($totalSelisih,0,',','.') }}
                    </td>
                    <td class="text-right font-weight-bold">
                        {{ number_format($totalPersen,2) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-light font-weight-bold">
                <tr class="table-warning">
                    <th>Total</th>
                    @foreach($footer as $b => $v)
                        @php
                            $selisih = $v['realisasi'] - $v['rencana'];
                            $persen  = $v['rencana'] > 0 ? ($v['realisasi']/$v['rencana'])*100 : 0;
                            
                            // Akumulasi untuk grand total
                            $footerGrandTotal['rencana'] += $v['rencana'];
                            $footerGrandTotal['realisasi'] += $v['realisasi'];
                        @endphp
                        <th class="text-right">{{ number_format($v['rencana'],0,',','.') }}</th>
                        <th class="text-right">{{ number_format($v['realisasi'],0,',','.') }}</th>
                        <th class="text-right">{{ number_format($selisih,0,',','.') }}</th>
                        <th class="text-right">{{ number_format($persen,2) }}%</th>
                    @endforeach
                    @php
                        $footerGrandTotal['selisih'] = $footerGrandTotal['realisasi'] - $footerGrandTotal['rencana'];
                        $footerGrandTotal['persen'] = $footerGrandTotal['rencana'] > 0 
                            ? ($footerGrandTotal['realisasi'] / $footerGrandTotal['rencana']) * 100 
                            : 0;
                    @endphp
                    <th class="text-right bg-warning">{{ number_format($footerGrandTotal['rencana'],0,',','.') }}</th>
                    <th class="text-right bg-warning">{{ number_format($footerGrandTotal['realisasi'],0,',','.') }}</th>
                    <th class="text-right bg-warning">{{ number_format($footerGrandTotal['selisih'],0,',','.') }}</th>
                    <th class="text-right bg-warning">{{ number_format($footerGrandTotal['persen'],2) }}%</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>