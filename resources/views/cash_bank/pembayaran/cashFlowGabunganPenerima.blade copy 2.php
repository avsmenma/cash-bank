@push('styles')
<style>
    #example {
    table-layout: auto !important;
    width: 100% !important;
    }

    #example th,
    #example td {
        white-space: nowrap;        /* biar kolom melebar */
        vertical-align: middle;
    }
</style>
@endpush


@php
$footer = [];
foreach ($bulanList as $b => $n) {
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
                    <th rowspan="2">Kategori</th>
                    @foreach($bulanList as $b => $n)
                        <th colspan="2">{{ ucfirst($b) }}</th>
                        <th rowspan="2">Selisih</th>
                        <th rowspan="2">%Tase</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach($bulanList as $b => $n)
                        <th>Rencana</th>
                        <th>Realisasi</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $kategori => $bulan)
                <tr>
                    <td>{{ $kategori }}</td>
                    @foreach($bulan as $b => $v)
                        @php
                            $footer[$b]['rencana']   += $v['rencana'];
                            $footer[$b]['realisasi'] += $v['realisasi'];
                        @endphp
                        <td class="text-right">{{ number_format($v['rencana'],0,',','.') }}</td>
                        <td class="text-right">{{ number_format($v['realisasi'],0,',','.') }}</td>
                        <td class="text-right">{{ number_format($v['selisih'],0,',','.') }}</td>
                        <td class="text-right">{{ number_format($v['persen'],2) }}%</td>
                    @endforeach
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
                    @endphp
                    <th class="text-right">{{ number_format($v['rencana'],0,',','.') }}</th>
                    <th class="text-right">{{ number_format($v['realisasi'],0,',','.') }}</th>
                    <th class="text-right">{{ number_format($selisih,0,',','.') }}</th>
                    <th class="text-right">{{ number_format($persen,2) }}%</th>
                @endforeach
            </tr>
            </tfoot>
        </table>
    </div>
</div>
