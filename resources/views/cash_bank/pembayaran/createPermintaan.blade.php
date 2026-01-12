<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th style="width: 200px;">#</th>
                <th style="width: 150px;">M1</th>
                <th style="width: 150px;">M2</th>
                <th style="width: 150px;">M3</th>
                <th style="width: 150px;">M4</th>
                <th style="width: 150px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalM1 = 0;
                $totalM2 = 0;
                $totalM3 = 0;
                $totalM4 = 0;
                $grandTotal = 0;
            @endphp
            
            @foreach($items as $i)
                @php
                    $m1 = $data[$i->id_item_sub_kriteria]['M1'] ?? 0;
                    $m2 = $data[$i->id_item_sub_kriteria]['M2'] ?? 0;
                    $m3 = $data[$i->id_item_sub_kriteria]['M3'] ?? 0;
                    $m4 = $data[$i->id_item_sub_kriteria]['M4'] ?? 0;
                    $rowTotal = $m1 + $m2 + $m3 + $m4;
                    
                    $totalM1 += $m1;
                    $totalM2 += $m2;
                    $totalM3 += $m3;
                    $totalM4 += $m4;
                    $grandTotal += $rowTotal;
                @endphp
                <tr>
                    <td class="font-weight-bold bg-light">{{ $i->nama_item_sub_kriteria }}</td>
                    <td contenteditable="true"
                        class="cell text-right"
                        data-item="{{ $i->id_item_sub_kriteria }}"
                        data-sub="{{ $subKriteriaId }}"
                        data-bulan="{{ $bulan }}"
                        data-tahun="{{ $tahun }}"
                        data-kolom="M1">{{ number_format($m1, 0, ',', '.') }}</td>
                    <td contenteditable="true"
                        class="cell text-right"
                        data-item="{{ $i->id_item_sub_kriteria }}"
                        data-sub="{{ $subKriteriaId }}"
                        data-bulan="{{ $bulan }}"
                        data-tahun="{{ $tahun }}"
                        data-kolom="M2">{{ number_format($m2, 0, ',', '.') }}</td>
                    <td contenteditable="true"
                        class="cell text-right"
                        data-item="{{ $i->id_item_sub_kriteria }}"
                        data-sub="{{ $subKriteriaId }}"
                        data-bulan="{{ $bulan }}"
                        data-tahun="{{ $tahun }}"
                        data-kolom="M3">{{ number_format($m3, 0, ',', '.') }}</td>
                    <td contenteditable="true"
                        class="cell text-right"
                        data-item="{{ $i->id_item_sub_kriteria }}"
                        data-sub="{{ $subKriteriaId }}"
                        data-bulan="{{ $bulan }}"
                        data-tahun="{{ $tahun }}"
                        data-kolom="M4">{{ number_format($m4, 0, ',', '.') }}</td>
                    <td class="text-right font-weight-bold bg-light">{{ number_format($rowTotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="table-dark">
            <tr>
                <th>Total</th>
                <th class="text-right">{{ number_format($totalM1, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($totalM2, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($totalM3, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($totalM4, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($grandTotal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</div>