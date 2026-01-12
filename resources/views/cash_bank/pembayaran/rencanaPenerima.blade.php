

@if(isset($kategori) && $kategori->count() > 0)
@php
$bulanList = ['januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];
$totalPerBulan = array_fill_keys($bulanList, 0);
$grandTotal = 0;
@endphp
<div class="row">
    <div class="col-12 table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Kategori</th>
                    @foreach($bulanList as $b)
                        <th>{{ ucfirst($b) }}</th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kategori as $k)
                    @php
                        $row = $data[$k->id_kategori_kriteria] ?? null;
                    
                        $total = 0;
                    @endphp
                    <tr>
                        <td>{{ $k->nama_kriteria }}</td>

                        @foreach($bulanList as $b)
                            @php
                                $nilai = $row->$b ?? 0;
                                $total += $nilai;
                                $totalPerBulan[$b] += $nilai;
                                $grandTotal += $nilai;
                            @endphp
                            <td contenteditable="true"
                                class="cell text-right"
                                data-id="{{ $row->id_rencana_penerima ?? '' }}"
                                data-kategori="{{ $k->id_kategori_kriteria }}"
                                data-bulan="{{ $b }}"
                                data-tahun="{{ $tahun }}">
                                {{ number_format($nilai, 0, ',', '.') }}
                            </td>
                        @endforeach

                        <td class="text-right font-weight-bold">
                            {{ number_format($total, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-dark">
            <tr>
                <th>Total</th>
                 @foreach($bulanList as $b)
                    <th class="text-right">
                        {{ number_format($totalPerBulan[$b], 0, ',', '.') }}
                    </th>
                @endforeach
                 <th class="text-right">
                    {{ number_format($grandTotal, 0, ',', '.') }}
                </th>
            </tr>
        </tfoot>
        </table>
        @else
        <div class="alert alert-info">
            Tidak ada data kategori. Silakan tambah kategori terlebih dahulu.
        </div>
        @endif
        </div>
    </div>
</div>
