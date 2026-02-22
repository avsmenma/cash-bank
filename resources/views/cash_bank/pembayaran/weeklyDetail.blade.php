@php
    function formatMinusWeekly($angka, $desimal = 0)
    {
        if ($angka == 0)
            return '0';
        return $angka < 0
            ? '(' . number_format(abs($angka), $desimal, ',', '.') . ')'
            : number_format($angka, $desimal, ',', '.');
    }
@endphp

{{-- ================= PENERIMA ================= --}}
<h6 class="font-weight-bold text-primary mb-3">
    <i class="fas fa-calendar-alt"></i> {{ $namaBulan }} Tahun {{ $tahun }}
</h6>

<div class="table-responsive">
    <table class="table table-bordered table-sm" id="weeklyTable">
        <thead>
            <tr class="bg-warning text-dark text-center font-weight-bold">
                <th rowspan="2" style="vertical-align:middle; min-width:200px;">URAIAN</th>
                <th colspan="4">{{ $namaBulan }} Tahun {{ $tahun }}</th>
                <th rowspan="2" style="vertical-align:middle; background:#4472C4; color:#fff;">Jumlah</th>
            </tr>
            <tr class="bg-warning text-dark text-center font-weight-bold">
                <th>Minggu I</th>
                <th>Minggu II</th>
                <th>Minggu III</th>
                <th>Minggu IV</th>
            </tr>
        </thead>
        <tbody>
            {{-- PENERIMAAN --}}
            <tr class="bg-warning font-weight-bold">
                <td colspan="6">PENERIMAAN</td>
            </tr>

            @php
                $totalW1 = 0;
                $totalW2 = 0;
                $totalW3 = 0;
                $totalW4 = 0;
            @endphp

            @forelse ($penerimaData as $kategori => $weeks)
                @php
                    $jumlah = $weeks['w1'] + $weeks['w2'] + $weeks['w3'] + $weeks['w4'];
                    $totalW1 += $weeks['w1'];
                    $totalW2 += $weeks['w2'];
                    $totalW3 += $weeks['w3'];
                    $totalW4 += $weeks['w4'];
                @endphp
                <tr>
                    <td>- {{ $kategori }}</td>
                    <td class="text-right">{{ $weeks['w1'] > 0 ? formatMinusWeekly($weeks['w1']) : '' }}</td>
                    <td class="text-right">{{ $weeks['w2'] > 0 ? formatMinusWeekly($weeks['w2']) : '' }}</td>
                    <td class="text-right">{{ $weeks['w3'] > 0 ? formatMinusWeekly($weeks['w3']) : '' }}</td>
                    <td class="text-right">{{ $weeks['w4'] > 0 ? formatMinusWeekly($weeks['w4']) : '' }}</td>
                    <td class="text-right font-weight-bold">{{ $jumlah > 0 ? formatMinusWeekly($jumlah) : '0' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Tidak ada data penerimaan</td>
                </tr>
            @endforelse

            <tr class="font-weight-bold bg-light">
                <td class="text-right">TOTAL PENERIMAAN</td>
                <td class="text-right">{{ $totalW1 > 0 ? formatMinusWeekly($totalW1) : '0' }}</td>
                <td class="text-right">{{ $totalW2 > 0 ? formatMinusWeekly($totalW2) : '0' }}</td>
                <td class="text-right">{{ $totalW3 > 0 ? formatMinusWeekly($totalW3) : '0' }}</td>
                <td class="text-right">{{ $totalW4 > 0 ? formatMinusWeekly($totalW4) : '0' }}</td>
                <td class="text-right">{{ formatMinusWeekly($totalW1 + $totalW2 + $totalW3 + $totalW4) }}</td>
            </tr>

            {{-- DROPPING --}}
            <tr class="bg-info font-weight-bold text-white">
                <td colspan="6">DROPPING</td>
            </tr>

            @php
                $totalDW1 = 0;
                $totalDW2 = 0;
                $totalDW3 = 0;
                $totalDW4 = 0;
            @endphp

            @forelse ($droppingData as $label => $weeks)
                @php
                    $jumlah = $weeks['w1'] + $weeks['w2'] + $weeks['w3'] + $weeks['w4'];
                    $totalDW1 += $weeks['w1'];
                    $totalDW2 += $weeks['w2'];
                    $totalDW3 += $weeks['w3'];
                    $totalDW4 += $weeks['w4'];
                @endphp
                <tr>
                    <td>- {{ $label }}</td>
                    <td class="text-right">{{ $weeks['w1'] > 0 ? formatMinusWeekly($weeks['w1']) : '' }}</td>
                    <td class="text-right">{{ $weeks['w2'] > 0 ? formatMinusWeekly($weeks['w2']) : '' }}</td>
                    <td class="text-right">{{ $weeks['w3'] > 0 ? formatMinusWeekly($weeks['w3']) : '' }}</td>
                    <td class="text-right">{{ $weeks['w4'] > 0 ? formatMinusWeekly($weeks['w4']) : '' }}</td>
                    <td class="text-right font-weight-bold">{{ $jumlah > 0 ? formatMinusWeekly($jumlah) : '0' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Tidak ada data dropping</td>
                </tr>
            @endforelse

            <tr class="font-weight-bold bg-light">
                <td class="text-right">TOTAL DROPPING</td>
                <td class="text-right">{{ $totalDW1 > 0 ? formatMinusWeekly($totalDW1) : '0' }}</td>
                <td class="text-right">{{ $totalDW2 > 0 ? formatMinusWeekly($totalDW2) : '0' }}</td>
                <td class="text-right">{{ $totalDW3 > 0 ? formatMinusWeekly($totalDW3) : '0' }}</td>
                <td class="text-right">{{ $totalDW4 > 0 ? formatMinusWeekly($totalDW4) : '0' }}</td>
                <td class="text-right">{{ formatMinusWeekly($totalDW1 + $totalDW2 + $totalDW3 + $totalDW4) }}</td>
            </tr>
        </tbody>
    </table>
</div>