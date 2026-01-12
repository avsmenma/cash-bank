@php
$bulanList = ['januari','februari','maret','april','mei','juni',
              'juli','agustus','september','oktober','november','desember'];
@endphp
<div class="row">
    <div class="col-12 table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Item Sub Kriteria</th>
                    @foreach($bulanList as $b)
                        <th>{{ ucfirst($b) }}</th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    @php
                        $row = $data[$item->id_item_sub_kriteria] ?? null;
                        $total = 0;
                    @endphp
                    <tr>
                        <td>{{ $item->nama_item_sub_kriteria }}</td>

                        @foreach($bulanList as $b)
                            @php
                                $nilai = $row->$b ?? 0;
                                $total += $nilai;
                            @endphp
                            <td contenteditable
                                class="cell text-right"
                                data-item="{{ $item->id_item_sub_kriteria }}"
                                data-sub="{{ $item->id_sub_kriteria }}"
                                data-kategori="{{ request('kategori') ?? '' }}"
                                data-bulan="{{ $b }}"
                                data-tahun="{{ $tahun }}">
                                {{ number_format($nilai,0,',','.') }}
                            </td>
                        @endforeach

                        <td class="text-right font-weight-bold">
                            {{ number_format($total,0,',','.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
