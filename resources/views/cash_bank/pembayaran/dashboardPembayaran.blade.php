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
    function formatMinus($angka, $desimal = 0)
    {
        return $angka < 0
            ? '(' . number_format(abs($angka), $desimal, ',', '.') . ')'
            : number_format($angka, $desimal, ',', '.');
    }
@endphp

<div class="table-responsive">
    <table class="table table-bordered table-sm" id="example">

        {{-- ================= HEADER ================= --}}
        <thead class="bg-navy text-center">
            <tr>
                <th rowspan="2" style="min-width:400px; vertical-align: middle">
                    URAIAN
                </th>

                @foreach ($bulanListFiltered as $bulan)
                    <th colspan="4">{{ ucfirst($bulan) }}</th>
                @endforeach

                <th colspan="4">TOTAL</th>
            </tr>

            <tr>
                @foreach ($bulanListFiltered as $bulan)
                    <th>Rencana</th>
                    <th>Realisasi</th>
                    <th>Selisih</th>
                    <th>%</th>
                @endforeach

                <th>Rencana</th>
                <th>Realisasi</th>
                <th>Selisih</th>
                <th>%</th>
            </tr>
        </thead>

        <tbody>

            {{-- ================= PENERIMA ================= --}}
            <tr class="bg-orange font-weight-bold">
                <td colspan="{{ 1 + count($bulanListFiltered) * 4 + 4 }}">
                    PENERIMA
                </td>
            </tr>

            @foreach ($dataPenerima as $kategori => $bulanData)
                @php
                    $totalRencana   = 0;
                    $totalRealisasi = 0;
                @endphp

                <tr>
                    <td>{{ $kategori }}</td>

                    @foreach ($bulanListFiltered as $b)
                        @php
                            $r  = $bulanData[$b]['rencana'] ?? 0;
                            $re = $bulanData[$b]['realisasi'] ?? 0;
                            $s  = $bulanData[$b]['selisih'] ?? 0;
                            $p  = $bulanData[$b]['persen'] ?? 0;

                            $totalRencana   += $r;
                            $totalRealisasi += $re;
                        @endphp

                        <td>{{ formatMinus($r) }}</td>
                        <td>{{ formatMinus($re) }}</td>
                        <td>{{ formatMinus($s) }}</td>
                        <td>{{ formatMinus($p, 2) }}%</td>
                    @endforeach

                    @php
                        $totalSelisih = $totalRealisasi - $totalRencana;
                        $totalPersen  = $totalRencana > 0
                            ? ($totalRealisasi / $totalRencana) * 100
                            : 0;
                    @endphp

                    <td>{{ formatMinus($totalRencana) }}</td>
                    <td>{{ formatMinus($totalRealisasi) }}</td>
                    <td>{{ formatMinus($totalSelisih) }}</td>
                    <td>{{ formatMinus($totalPersen, 2) }}%</td>
                </tr>
            @endforeach

            {{-- ================= TOTAL PENERIMA ================= --}}
            @php
                $totalRencana   = 0;
                $totalRealisasi = 0;
            @endphp

            <tr class="table-info font-weight-bold">
                <td class="text-right">Total Penerima</td>

                @foreach ($bulanListFiltered as $b)
                    @php
                        $r  = $totalPenerima[$b]['rencana'] ?? 0;
                        $re = $totalPenerima[$b]['realisasi'] ?? 0;
                        $s  = $totalPenerima[$b]['selisih'] ?? 0;
                        $p  = $totalPenerima[$b]['persen'] ?? 0;

                        $totalRencana   += $r;
                        $totalRealisasi += $re;
                    @endphp

                    <td>{{ formatMinus($r) }}</td>
                    <td>{{ formatMinus($re) }}</td>
                    <td>{{ formatMinus($s) }}</td>
                    <td>{{ formatMinus($p, 2) }}%</td>
                @endforeach

                @php
                    $totalSelisih = $totalRealisasi - $totalRencana;
                    $totalPersen  = $totalRencana > 0
                        ? ($totalRealisasi / $totalRencana) * 100
                        : 0;
                @endphp

                <td>{{ formatMinus($totalRencana) }}</td>
                <td>{{ formatMinus($totalRealisasi) }}</td>
                <td>{{ formatMinus($totalSelisih) }}</td>
                <td>{{ formatMinus($totalPersen, 2) }}%</td>
            </tr>

            {{-- ================= DROPPING ================= --}}
            <tr class="bg-cyan font-weight-bold">
                <td colspan="{{ 1 + count($bulanListFiltered) * 4 + 4 }}">
                    DROPPING
                </td>
            </tr>

            @foreach ($dataDropping as $kategori => $subs)
                <tr class="table-secondary font-weight-bold">
                    <td colspan="{{ 1 + count($bulanListFiltered) * 4 + 4 }}">
                        {{ $kategori }}
                    </td>
                </tr>

                @foreach ($subs as $sub => $items)
                    <tr class="table-light">
                        <td colspan="{{ 1 + count($bulanListFiltered) * 4 + 4 }}">
                            {{ $sub }}
                        </td>
                    </tr>

                    @foreach ($items as $item => $bulanData)
                        @php
                            $totalRencana   = 0;
                            $totalRealisasi = 0;
                        @endphp

                        <tr>
                            <td>- {{ $item }}</td>

                            @foreach ($bulanListFiltered as $b)
                                @php
                                    $r  = $bulanData[$b]['rencana'] ?? 0;
                                    $re = $bulanData[$b]['realisasi'] ?? 0;
                                    $s  = $bulanData[$b]['selisih'] ?? 0;
                                    $p  = $bulanData[$b]['persen'] ?? 0;

                                    $totalRencana   += $r;
                                    $totalRealisasi += $re;
                                @endphp

                                <td>{{ formatMinus($r) }}</td>
                                <td>{{ formatMinus($re) }}</td>
                                <td>{{ formatMinus($s) }}</td>
                                <td>{{ formatMinus($p, 2) }}%</td>
                            @endforeach

                            @php
                                $totalSelisih = $totalRealisasi - $totalRencana;
                                $totalPersen  = $totalRencana > 0
                                    ? ($totalRealisasi / $totalRencana) * 100
                                    : 0;
                            @endphp

                            <td>{{ formatMinus($totalRencana) }}</td>
                            <td>{{ formatMinus($totalRealisasi) }}</td>
                            <td>{{ formatMinus($totalSelisih) }}</td>
                            <td>{{ formatMinus($totalPersen, 2) }}%</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach

            {{-- ================= TOTAL DROPPING ================= --}}
            @php
                $totalRencana   = 0;
                $totalRealisasi = 0;
            @endphp

            <tr class="table-primary font-weight-bold">
                <td class="text-right">Total Dropping</td>

                @foreach ($bulanListFiltered as $b)
                    @php
                        $r  = $totalDropping[$b]['rencana'] ?? 0;
                        $re = $totalDropping[$b]['realisasi'] ?? 0;
                        $s  = $totalDropping[$b]['selisih'] ?? 0;
                        $p  = $totalDropping[$b]['persen'] ?? 0;

                        $totalRencana   += $r;
                        $totalRealisasi += $re;
                    @endphp

                    <td>{{ formatMinus($r) }}</td>
                    <td>{{ formatMinus($re) }}</td>
                    <td>{{ formatMinus($s) }}</td>
                    <td>{{ formatMinus($p, 2) }}%</td>
                @endforeach

                @php
                    $totalSelisih = $totalRealisasi - $totalRencana;
                    $totalPersen  = $totalRencana > 0
                        ? ($totalRealisasi / $totalRencana) * 100
                        : 0;
                @endphp

                <td>{{ formatMinus($totalRencana) }}</td>
                <td>{{ formatMinus($totalRealisasi) }}</td>
                <td>{{ formatMinus($totalSelisih) }}</td>
                <td>{{ formatMinus($totalPersen, 2) }}%</td>
            </tr>

        </tbody>

        {{-- ================= FOOTER ================= --}}
        <tfoot class="bg-dark text-white font-weight-bold">
            @php
                $totalRencana   = 0;
                $totalRealisasi = 0;
            @endphp

            <tr class="bg-orange">
                <th class="text-right">
                    Selisih Penerima - Dropping
                </th>

                @foreach ($bulanListFiltered as $b)
                    @php
                        $r  = ($totalPenerima[$b]['rencana'] ?? 0)
                            - ($totalDropping[$b]['rencana'] ?? 0);

                        $re = ($totalPenerima[$b]['realisasi'] ?? 0)
                            - ($totalDropping[$b]['realisasi'] ?? 0);

                        $s = $re - $r;
                        $p = $r != 0 ? ($re / $r) * 100 : 0;

                        $totalRencana   += $r;
                        $totalRealisasi += $re;
                    @endphp

                    <th>{{ formatMinus($r) }}</th>
                    <th>{{ formatMinus($re) }}</th>
                    <th>{{ formatMinus($s) }}</th>
                    <th>{{ formatMinus($p, 2) }}%</th>
                @endforeach

                @php
                    $totalSelisih = $totalRealisasi - $totalRencana;
                    $totalPersen  = $totalRencana != 0
                        ? ($totalRealisasi / $totalRencana) * 100
                        : 0;
                @endphp

                <th>{{ formatMinus($totalRencana) }}</th>
                <th>{{ formatMinus($totalRealisasi) }}</th>
                <th>{{ formatMinus($totalSelisih) }}</th>
                <th>{{ formatMinus($totalPersen, 2) }}%</th>
            </tr>
        </tfoot>

    </table>
</div>
