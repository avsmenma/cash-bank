<style>
    #example.dashboard-payment-table {
        table-layout: auto;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12.5px;
    }

    #example.dashboard-payment-table th,
    #example.dashboard-payment-table td {
        border-color: #d5dce5;
        padding: 7px 9px;
        vertical-align: middle;
    }

    #example.dashboard-payment-table th:first-child,
    #example.dashboard-payment-table td:first-child {
        min-width: 500px;
        text-align: left;
        white-space: normal;
    }

    #example.dashboard-payment-table th:not(:first-child),
    #example.dashboard-payment-table td:not(:first-child) {
        min-width: 108px;
        text-align: right;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    #example.dashboard-payment-table thead th {
        background: #1f3d5a !important;
        color: #fff !important;
        border-color: #17324b;
        font-weight: 700;
        text-align: center !important;
    }

    #example.dashboard-payment-table .dp-section td {
        background: #1597a7 !important;
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: .2px;
        border-top: 2px solid #0f7280;
        border-bottom: 2px solid #0f7280;
    }

    #example.dashboard-payment-table .dp-category-row td {
        background: #d7dde3 !important;
        color: #172033;
        font-size: 14px;
        font-weight: 800;
        border-top: 2px solid #9aa7b4;
        border-bottom: 1px solid #aeb8c4;
    }

    #example.dashboard-payment-table .dp-category-label {
        display: block;
        border-left: 5px solid #334e68;
        padding: 3px 0 3px 10px;
    }

    #example.dashboard-payment-table .dp-sub-row td {
        background: #eef4fb !important;
        color: #17324b;
        font-weight: 800;
        border-top: 2px solid #7f95ad;
        border-bottom: 1px solid #c6d3e1;
    }

    #example.dashboard-payment-table .dp-sub-label {
        display: block;
        margin-left: 16px;
        padding: 4px 0 4px 12px;
        border-left: 4px solid #2f80aa;
    }

    #example.dashboard-payment-table .dp-item-row td {
        background: #fff;
        border-bottom: 1px solid #e3e8ef;
    }

    #example.dashboard-payment-table .dp-item-row td:first-child {
        color: #1f2937;
        padding-left: 58px;
    }

    #example.dashboard-payment-table .dp-item-row:hover td {
        background: #f8fbff;
    }

    #example.dashboard-payment-table .dp-total-row td,
    #example.dashboard-payment-table .dp-footer-row th {
        font-weight: 800;
    }
</style>

@php
    function formatMinus($angka, $desimal = 0)
    {
        return $angka < 0
            ? '(' . number_format(abs($angka), $desimal, ',', '.') . ')'
            : number_format($angka, $desimal, ',', '.');
    }
@endphp

<div class="table-responsive">
    <table class="table table-bordered table-sm dashboard-payment-table" id="example">

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
            <tr class="dp-section font-weight-bold">
                <td colspan="{{ 1 + count($bulanListFiltered) * 4 + 4 }}">
                    PENERIMA
                </td>
            </tr>

            @foreach ($dataPenerima as $kategori => $bulanData)
                @php
                    $totalRencana = 0;
                    $totalRealisasi = 0;
                @endphp

                <tr>
                    <td>{{ $kategori }}</td>

                    @foreach ($bulanListFiltered as $b)
                        @php
                            $r = $bulanData[$b]['rencana'] ?? 0;
                            $re = $bulanData[$b]['realisasi'] ?? 0;
                            $s = $bulanData[$b]['selisih'] ?? 0;
                            $p = $bulanData[$b]['persen'] ?? 0;

                            $totalRencana += $r;
                            $totalRealisasi += $re;
                        @endphp

                        <td class="text-right">{{ formatMinus($r) }}</td>
                        <td class="text-right">{{ formatMinus($re) }}</td>
                        <td class="text-right">{{ formatMinus($s) }}</td>
                        <td class="text-right">{{ formatMinus($p, 2) }}%</td>
                    @endforeach

                    @php
                        $totalSelisih = $totalRealisasi - $totalRencana;
                        $totalPersen = $totalRencana > 0
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
                $totalRencana = 0;
                $totalRealisasi = 0;
            @endphp

            <tr class="table-info font-weight-bold dp-total-row">
                <td class="text-right">Total Penerima</td>

                @foreach ($bulanListFiltered as $b)
                    @php
                        $r = $totalPenerima[$b]['rencana'] ?? 0;
                        $re = $totalPenerima[$b]['realisasi'] ?? 0;
                        $s = $totalPenerima[$b]['selisih'] ?? 0;
                        $p = $totalPenerima[$b]['persen'] ?? 0;

                        $totalRencana += $r;
                        $totalRealisasi += $re;
                    @endphp

                    <td class="text-right">{{ formatMinus($r) }}</td>
                    <td class="text-right">{{ formatMinus($re) }}</td>
                    <td class="text-right">{{ formatMinus($s) }}</td>
                    <td class="text-right">{{ formatMinus($p, 2) }}%</td>
                @endforeach

                @php
                    $totalSelisih = $totalRealisasi - $totalRencana;
                    $totalPersen = $totalRencana > 0
                        ? ($totalRealisasi / $totalRencana) * 100
                        : 0;
                @endphp

                <td class="text-right">{{ formatMinus($totalRencana) }}</td>
                <td class="text-right">{{ formatMinus($totalRealisasi) }}</td>
                <td class="text-right">{{ formatMinus($totalSelisih) }}</td>
                <td class="text-right">{{ formatMinus($totalPersen, 2) }}%</td>
            </tr>

            {{-- ================= DROPPING ================= --}}
            <tr class="dp-section font-weight-bold">
                <td colspan="{{ 1 + count($bulanListFiltered) * 4 + 4 }}">
                    DROPPING
                </td>
            </tr>

            @foreach ($dataDropping as $kategori => $subs)
                <tr class="dp-category-row">
                    <td colspan="{{ 1 + count($bulanListFiltered) * 4 + 4 }}">
                        <span class="dp-category-label">{{ $kategori }}</span>
                    </td>
                </tr>

                @foreach ($subs as $sub => $items)
                    <tr class="dp-sub-row">
                        <td colspan="{{ 1 + count($bulanListFiltered) * 4 + 4 }}">
                            <span class="dp-sub-label">{{ $sub }}</span>
                        </td>
                    </tr>

                    @foreach ($items as $item => $bulanData)
                        @php
                            $totalRencana = 0;
                            $totalRealisasi = 0;
                        @endphp

                        <tr class="dp-item-row">
                            <td>{{ $item === '' ? '' : '- ' . $item }}</td>

                            @foreach ($bulanListFiltered as $b)
                                @php
                                    $r = $bulanData[$b]['rencana'] ?? 0;
                                    $re = $bulanData[$b]['realisasi'] ?? 0;
                                    $s = $bulanData[$b]['selisih'] ?? 0;
                                    $p = $bulanData[$b]['persen'] ?? 0;

                                    $totalRencana += $r;
                                    $totalRealisasi += $re;
                                @endphp

                                <td class="text-right">{{ formatMinus($r) }}</td>
                                <td class="text-right">{{ formatMinus($re) }}</td>
                                <td class="text-right">{{ formatMinus($s) }}</td>
                                <td class="text-right">{{ formatMinus($p, 2) }}%</td>
                            @endforeach

                            @php
                                $totalSelisih = $totalRealisasi - $totalRencana;
                                $totalPersen = $totalRencana > 0
                                    ? ($totalRealisasi / $totalRencana) * 100
                                    : 0;
                            @endphp

                            <td class="text-right">{{ formatMinus($totalRencana) }}</td>
                            <td class="text-right">{{ formatMinus($totalRealisasi) }}</td>
                            <td class="text-right">{{ formatMinus($totalSelisih) }}</td>
                            <td class="text-right">{{ formatMinus($totalPersen, 2) }}%</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach

            {{-- ================= TOTAL DROPPING ================= --}}
            @php
                $totalRencana = 0;
                $totalRealisasi = 0;
            @endphp

            <tr class="table-primary font-weight-bold dp-total-row">
                <td class="text-right">Total Dropping</td>

                @foreach ($bulanListFiltered as $b)
                    @php
                        $r = $totalDropping[$b]['rencana'] ?? 0;
                        $re = $totalDropping[$b]['realisasi'] ?? 0;
                        $s = $totalDropping[$b]['selisih'] ?? 0;
                        $p = $totalDropping[$b]['persen'] ?? 0;

                        $totalRencana += $r;
                        $totalRealisasi += $re;
                    @endphp

                    <td class="text-right">{{ formatMinus($r) }}</td>
                    <td class="text-right">{{ formatMinus($re) }}</td>
                    <td class="text-right">{{ formatMinus($s) }}</td>
                    <td class="text-right">{{ formatMinus($p, 2) }}%</td>
                @endforeach

                @php
                    $totalSelisih = $totalRealisasi - $totalRencana;
                    $totalPersen = $totalRencana > 0
                        ? ($totalRealisasi / $totalRencana) * 100
                        : 0;
                @endphp

                <td class="text-right">{{ formatMinus($totalRencana) }}</td>
                <td class="text-right">{{ formatMinus($totalRealisasi) }}</td>
                <td class="text-right">{{ formatMinus($totalSelisih) }}</td>
                <td class="text-right">{{ formatMinus($totalPersen, 2) }}%</td>
            </tr>

        </tbody>

        {{-- ================= FOOTER ================= --}}
        <tfoot class="bg-dark text-white font-weight-bold">
            @php
                $totalRencana = 0;
                $totalRealisasi = 0;
            @endphp

            <tr class="bg-orange dp-footer-row">
                <th class="text-right">
                    Selisih Penerima - Dropping
                </th>

                @foreach ($bulanListFiltered as $b)
                    @php
                        $r = ($totalPenerima[$b]['rencana'] ?? 0)
                            - ($totalDropping[$b]['rencana'] ?? 0);

                        $re = ($totalPenerima[$b]['realisasi'] ?? 0)
                            - ($totalDropping[$b]['realisasi'] ?? 0);

                        $s = $re - $r;
                        $p = $r != 0 ? ($re / $r) * 100 : 0;

                        $totalRencana += $r;
                        $totalRealisasi += $re;
                    @endphp

                    <th>{{ formatMinus($r) }}</th>
                    <th>{{ formatMinus($re) }}</th>
                    <th>{{ formatMinus($s) }}</th>
                    <th>{{ formatMinus($p, 2) }}%</th>
                @endforeach

                @php
                    $totalSelisih = $totalRealisasi - $totalRencana;
                    $totalPersen = $totalRencana != 0
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
