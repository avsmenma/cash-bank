@push('styles')
<style>
    #cashflow-table {
    table-layout: auto !important;
    width: 100% !important;
    }

    #cashflow-table td {
        white-space: nowrap;
        vertical-align: middle;
    }
</style>
@endpush
<div class="card-body">
    <div class="mb-3">
        <h5>Cash Flow Permintaan - Tahun {{ $tahun }}</h5>
    </div>

    <div class="table-responsive">
        <table id="cashflow-table" class="table table-bordered table-sm">
            <thead class="bg-navy">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Payments for 2025 transactions - Accounts</th>
                    <th class="text-center">Jan</th>
                    <th class="text-center">Feb</th>
                    <th class="text-center">Mar</th>
                    <th class="text-center">Apr</th>
                    <th class="text-center">Mei</th>
                    <th class="text-center">Jun</th>
                    <th class="text-center">Jul</th>
                    <th class="text-center">Agu</th>
                    <th class="text-center">Sep</th>
                    <th class="text-center">Okt</th>
                    <th class="text-center">Nov</th>
                    <th class="text-center">Des</th>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                
                @forelse($result as $kategoriName => $kategoriData)
                    {{-- Kategori Row --}}
                    <tr class="bg-navy font-weight-bold">
                        <td>{{ $no++ }}</td>
                        <td><u>{{ $kategoriName }}</u></td>
                        <td colspan="13"></td>
                    </tr>

                    {{-- Sub Kriteria Rows --}}
                    @foreach($kategoriData['subs'] as $subName => $subData)
                        <tr class="font-weight-bold">
                            <td></td>
                            <td class="pl-1">{{ $subName }}</td>
                            <td colspan="13"></td>
                        </tr>

                        {{-- Item Rows --}}
                        @foreach($subData['items'] as $itemName => $itemData)
                            <tr>
                                <td></td>
                                <td class="pl-2 text-muted">
                                    <small>{{"- " . $itemName }}</small>
                                </td>
                                @php
                                    $itemRowTotal = 0;
                                @endphp
                                @for($m = 1; $m <= 12; $m++)
                                    @php
                                        $nilai = $itemData[$m] ?? 0;
                                        $itemRowTotal += $nilai;
                                    @endphp
                                    <td class="text-right">{{ number_format($nilai, 0, ',', '.') }}</td>
                                @endfor
                                <td class="text-right">{{ number_format($itemRowTotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-light">
                            <td></td>
                            <td class="pl-1 bg-light">{{"Sub Total ". $subName }}</td>
                            @php
                                $subRowTotal = 0;
                            @endphp
                            @for($m = 1; $m <= 12; $m++)
                                @php
                                    $nilai = $subData['totals'][$m] ?? 0;
                                    $subRowTotal += $nilai;
                                @endphp
                                <td class="text-right">{{ number_format($nilai, 0, ',', '.') }}</td>
                            @endfor
                            <td class="text-right">{{ number_format($subRowTotal, 0, ',', '.') }}</td>
                        </tr>
                       
                    @endforeach
                    <tr class="table-primary font-weight-bold">
                            <td colspan="2">{{ $kategoriName }}</td>
                            @php
                                $kategoriRowTotal = 0;
                            @endphp
                            @for($m = 1; $m <= 12; $m++)
                                @php
                                    $nilai = $kategoriData['totals'][$m] ?? 0;
                                    $kategoriRowTotal += $nilai;
                                @endphp
                                <td class="text-right">{{ number_format($nilai, 0, ',', '.') }}</td>
                            @endfor
                            <td class="text-right">{{ number_format($kategoriRowTotal, 0, ',', '.') }}</td>
                        </tr>
                    

                @empty
                    <tr>
                        <td colspan="15" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i> Tidak ada data untuk tahun {{ $tahun }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-danger font-weight-bold">
                <tr>
                    <th colspan="2">TOTAL Keseluruhan</th>
                    @for($m = 1; $m <= 12; $m++)
                        <th class="text-right">{{ number_format($totals[$m] ?? 0, 0, ',', '.') }}</th>
                    @endfor
                    <th class="text-right">{{ number_format($grandTotal ?? 0, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    $('#cashflow-table').DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "ordering": false,
        "paging": false,
        "searching": true,
        "info": false,
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Export Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> Export PDF',
                className: 'btn btn-danger btn-sm',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            }
        ]
    });
});
</script>
@endpush
