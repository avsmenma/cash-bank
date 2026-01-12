
@push('styles')
<style>
    #cashflow-table-pvd {
        table-layout: auto !important;
        font-size: 10px;
    }

    #cashflow-table-pvd td.text-right {
    white-space: nowrap;
    }
    #cashflow-table-pvd {
        table-layout: fixed;
        width: 100%;
        align: center;
    }
    .col-permintaan,
    .col-dropping,
    .col-pembayaran {
        min-width: 100px;
        max-width: 100px;
    }

   
</style>
@endpush
@php
    $bulanColors = [
        1 => 'bg-januari',
        2 => 'bg-februari', 
        3 => 'bg-maret',
        4 => 'bg-april',
        5 => 'bg-mei',
        6 => 'bg-juni',
        7 => 'bg-juli',
        8 => 'bg-agustus',
        9 => 'bg-september',
        10 => 'bg-oktober',
        11 => 'bg-november',
        12 => 'bg-desember'
    ];
@endphp

<div class="row">
    <div class="col-12 table-responsive">
        <table class="table table-bordered table-sm" id="cashflow-table-pvd">
            <thead class="bg-navy">
                <!-- Row 1: Header Bulan -->
                <tr>
                <th rowspan="3" style="vertical-align: middle;" class="text-center">No.</th>
                <th rowspan="3" style="min-width: 400px; max-width:500px;vertical-align: middle;" class="text-center">Payments for {{ $tahun }} transactions - Accounts</th>
                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                    @php
                        $colorClass = $bulanColors[$noBulan] ?? 'bg-primary';
                    @endphp
                    <th colspan="3" style="min-width: 250px; vertical-align: middle;" class="text-center">
                        {{ ucfirst($namaBulan) }} {{ $tahun }}
                    </th>
                @endforeach
                    <th colspan="3" class="text-center">Total</th>
                    <th colspan="2" class="text-center">%Tase Pembayaran Thdp</th>
            </tr>
            
            <!-- Row 2: Sub Header Permintaan RD, Dropping HO, Pembayaran -->
            <tr>
                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                    <th style="min-width:150px;vertical-align: middle;" class="text-center">Permintaan RD</th>
                    <th style="min-width:150px;vertical-align: middle;" class="text-center">Dropping HO</th>
                    <th style="min-width:150px;vertical-align: middle;" class="text-center">Pembayaran</th>
                @endforeach
                <th class="col-permintaan">Permintaan RD</th>
                <th style="min-width:150px;vertical-align: middle;" class="text-center">Dropping HO</th>
                <th style="min-width:150px;vertical-align: middle;" class="text-center">Pembayaran</th>
                <th style="min-width:150px;vertical-align: middle;" class="text-center">Permintaan</th>
                <th style="min-width:150px;vertical-align: middle;" class="text-center">Dropping</th>
            </tr>
                
                <tr>
                    @php $colNum = 1; @endphp
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        <th style="vertical-align: middle;" class="text-center">{{ $colNum++ }}</th>
                        <th style="vertical-align: middle;" class="text-center">{{ $colNum++ }}</th>
                        <th style="vertical-align: middle;" class="text-center">{{ $colNum++ }}</th>
                    @endforeach
                    <th style="vertical-align: middle;" class="text-center">{{ $colNum++ }}</th>
                    <th style="vertical-align: middle;" class="text-center">{{ $colNum++ }}</th>
                    <th style="vertical-align: middle;" class="text-center">{{ $colNum++ }}</th>
                    <th style="vertical-align: middle;" class="text-center">{{ $colNum++ }}</th>
                    <th style="vertical-align: middle;" class="text-center">{{ $colNum++ }}</th>
                </tr>
            </thead>
            
            <tbody>
                @php
                    $rowNumber = 1;
                    $currentKategori = null;
                    $currentSubKategori = null;
                    
                    // Reorganize data by kategori -> sub_kriteria -> item_kriteria
                    $organizedData = [];
                    
                    // Combine dropping and pembayaran data
                    if (isset($result['dropping'])) {
                        foreach($result['dropping'] as $key => $item) {
                            $kategori = $item['kategori'];
                            $subKriteria = $item['sub_kriteria'];
                            $itemKriteria = $item['item_kriteria'];
                            
                            if (!isset($organizedData[$kategori])) {
                                $organizedData[$kategori] = [];
                            }
                            if (!isset($organizedData[$kategori][$subKriteria])) {
                                $organizedData[$kategori][$subKriteria] = [];
                            }
                            if (!isset($organizedData[$kategori][$subKriteria][$itemKriteria])) {
                                $organizedData[$kategori][$subKriteria][$itemKriteria] = [
                                    'permintaan' => [],
                                    'dropping' => [],
                                    'pembayaran' => []
                                ];
                            }
                            
                            $organizedData[$kategori][$subKriteria][$itemKriteria]['dropping'] = $item['data'];
                        }
                    }
                    
                    // Add permintaan data
                    if (isset($result['permintaan'])) {
                        foreach($result['permintaan'] as $key => $item) {
                            $kategori = $item['kategori'];
                            $subKriteria = $item['sub_kriteria'];
                            $itemKriteria = $item['item_kriteria'];
                            
                            if (!isset($organizedData[$kategori])) {
                                $organizedData[$kategori] = [];
                            }
                            if (!isset($organizedData[$kategori][$subKriteria])) {
                                $organizedData[$kategori][$subKriteria] = [];
                            }
                            if (!isset($organizedData[$kategori][$subKriteria][$itemKriteria])) {
                                $organizedData[$kategori][$subKriteria][$itemKriteria] = [
                                    'permintaan' => [],
                                    'dropping' => [],
                                    'pembayaran' => []
                                ];
                            }
                            
                            $organizedData[$kategori][$subKriteria][$itemKriteria]['permintaan'] = $item['data'];
                        }
                    }
                    
                    // Add pembayaran data
                    if (isset($result['pembayaran'])) {
                        foreach($result['pembayaran'] as $key => $item) {
                        $kategori = $item['kategori'];
                        $subKriteria = $item['sub_kriteria'];
                        $itemKriteria = $item['item_kriteria'];
                        
                        if (!isset($organizedData[$kategori])) {
                            $organizedData[$kategori] = [];
                        }
                        if (!isset($organizedData[$kategori][$subKriteria])) {
                            $organizedData[$kategori][$subKriteria] = [];
                        }
                        if (!isset($organizedData[$kategori][$subKriteria][$itemKriteria])) {
                            $organizedData[$kategori][$subKriteria][$itemKriteria] = [
                                'permintaan' => [],
                                'dropping' => [],
                                'pembayaran' => []
                            ];
                        }
                        
                        $organizedData[$kategori][$subKriteria][$itemKriteria]['pembayaran'] = $item['data'];
                    }
                }
                    
                    // Initialize totals
                    $grandTotalPermintaan = [];
                    $grandTotalDropping = [];
                    $grandTotalPembayaran = [];
                    foreach($bulanListFiltered as $b => $n) {
                        $grandTotalPermintaan[$b] = 0;
                        $grandTotalDropping[$b] = 0;
                        $grandTotalPembayaran[$b] = 0;
                    }
                    
                    $grandTotalPermintaanAll = 0;
                    $grandTotalDroppingAll = 0;
                    $grandTotalPembayaranAll = 0;
                @endphp
                
                @foreach($organizedData as $kategori => $subKriterias)
                    @php
                        $kategoriTotalPermintaan = [];
                        $kategoriTotalDropping = [];
                        $kategoriTotalPembayaran = [];
                        foreach($bulanListFiltered as $b => $n) {
                            $kategoriTotalPermintaan[$b] = 0;
                            $kategoriTotalDropping[$b] = 0;
                            $kategoriTotalPembayaran[$b] = 0;
                        }
                        $kategoriTotalPermintaanAll = 0;
                        $kategoriTotalDroppingAll = 0;
                        $kategoriTotalPembayaranAll = 0;
                    @endphp
                    
                    {{-- Header Kategori --}}
                    <tr  class="bg-yellow">
                        <td class="text-center">{{ $rowNumber++ }}</td>
                        <td><strong>{{ $kategori }}</strong></td>
                        @foreach($bulanListFiltered as $noBulan => $namaBulan)
                            <td colspan="3"></td>
                        @endforeach
                        <td colspan="3"></td>
                        <td colspan="2"></td>
                    </tr>
                    
                    @foreach($subKriterias as $subKriteria => $items)
                        @php
                            // Initialize sub kategori totals
                            $subKategoriTotalPermintaan = [];
                            $subKategoriTotalDropping = [];
                            $subKategoriTotalPembayaran = [];
                            foreach($bulanListFiltered as $b => $n) {
                                $subKategoriTotalPermintaan[$b] = 0;
                                $subKategoriTotalDropping[$b] = 0;
                                $subKategoriTotalPembayaran[$b] = 0;
                            }
                            $subKategoriTotalPermintaanAll = 0;
                            $subKategoriTotalDroppingAll = 0;
                            $subKategoriTotalPembayaranAll = 0;
                        @endphp
                        
                        {{-- Sub Kategori Header --}}
                        <tr class="sub-kategori-row">
                            <td></td>
                            <td>{{ $subKriteria }}</td>
                            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                <td colspan="3"></td>
                            @endforeach
                            <td colspan="3"></td>
                            <td colspan="2"></td>
                        </tr>
                        
                        @foreach($items as $itemKriteria => $data)
                            {{-- Item Detail Row --}}
                            <tr class="item-row">
                                <td></td>
                                <td>{{"- " . $itemKriteria }}</td>
                                @php
                                    $totalPermintaanItem = 0;
                                    $totalDroppingItem = 0;
                                    $totalPembayaranItem = 0;
                                @endphp
                                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                    @php
                                        // Get data from organized structure
                                        $permintaan = $data['permintaan'][$noBulan] ?? 0;
                                        $dropping = $data['dropping'][$noBulan] ?? 0;
                                        $pembayaran = $data['pembayaran'][$noBulan] ?? 0;
                                        
                                        // Add to totals
                                        $kategoriTotalPermintaan[$noBulan] += $permintaan;
                                        $kategoriTotalDropping[$noBulan] += $dropping;
                                        $kategoriTotalPembayaran[$noBulan] += $pembayaran;
                                        
                                        $subKategoriTotalPermintaan[$noBulan] += $permintaan;
                                        $subKategoriTotalDropping[$noBulan] += $dropping;
                                        $subKategoriTotalPembayaran[$noBulan] += $pembayaran;
                                        
                                        $grandTotalPermintaan[$noBulan] += $permintaan;
                                        $grandTotalDropping[$noBulan] += $dropping;
                                        $grandTotalPembayaran[$noBulan] += $pembayaran;
                                        
                                        $totalPermintaanItem += $permintaan;
                                        $totalDroppingItem += $dropping;
                                        $totalPembayaranItem += $pembayaran;
                                    @endphp
                                    <td class="text-right col-permintaan">{{ $permintaan > 0 ? number_format($permintaan, 0, ',', '.') : '-' }}</td>
                                    <td class="text-right col-dropping">{{ $dropping > 0 ? number_format($dropping, 0, ',', '.') : '-' }}</td>
                                    <td class="text-right col-pembayaran">{{ $pembayaran > 0 ? number_format($pembayaran, 0, ',', '.') : '-' }}</td>
                                @endforeach
                                
                                {{-- Total Tahun untuk Item --}}
                                <td class="text-right col-permintaan">{{ number_format($totalPermintaanItem, 0, ',', '.') }}</td>
                                <td class="text-right col-dropping">{{ number_format($totalDroppingItem, 0, ',', '.') }}</td>
                                <td class="text-right col-pembayaran">{{ number_format($totalPembayaranItem, 0, ',', '.') }}</td>
                                
                                {{-- Persentase --}}
                                @php
                                    $persenPermintaan = $totalPermintaanItem > 0 ? ($totalPembayaranItem / $totalPermintaanItem * 100) : 0;
                                    $persenDropping = $totalDroppingItem > 0 ? ($totalPembayaranItem / $totalDroppingItem * 100) : 0;
                                @endphp
                                <td class="text-right col-persentase">{{ number_format($persenPermintaan, 2, ',', '.') }}%</td>
                                <td class="text-right col-persentase">{{ number_format($persenDropping, 2, ',', '.') }}%</td>
                            </tr>
                            
                            @php
                                $kategoriTotalPermintaanAll += $totalPermintaanItem;
                                $kategoriTotalDroppingAll += $totalDroppingItem;
                                $kategoriTotalPembayaranAll += $totalPembayaranItem;
                                
                                $subKategoriTotalPermintaanAll += $totalPermintaanItem;
                                $subKategoriTotalDroppingAll += $totalDroppingItem;
                                $subKategoriTotalPembayaranAll += $totalPembayaranItem;
                            @endphp
                        @endforeach
                        
                        {{-- Total Sub Kategori --}}
                        <tr class="table-info bg-orange" style="vertical-align:middle;">
                            <td></td>
                            <td   ><strong>Sub Total {{ $subKriteria }}</strong></td>
                            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                <td class="text-right" style="vertical-align: middle;"><strong>{{ number_format($subKategoriTotalPermintaan[$noBulan], 0, ',', '.') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($subKategoriTotalDropping[$noBulan], 0, ',', '.') }}</strong></td>
                                <td class="text-right"><strong>{{ number_format($subKategoriTotalPembayaran[$noBulan], 0, ',', '.') }}</strong></td>
                            @endforeach
                            
                            {{-- Total Tahun Sub Kategori --}}
                            <td class="text-right"><strong>{{ number_format($subKategoriTotalPermintaanAll, 0, ',', '.') }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($subKategoriTotalDroppingAll, 0, ',', '.') }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($subKategoriTotalPembayaranAll, 0, ',', '.') }}</strong></td>
                            
                            {{-- Persentase Sub Kategori --}}
                            @php
                                $persenSubKatPermintaan = $subKategoriTotalPermintaanAll > 0 ? ($subKategoriTotalPembayaranAll / $subKategoriTotalPermintaanAll * 100) : 0;
                                $persenSubKatDropping = $subKategoriTotalDroppingAll > 0 ? ($subKategoriTotalPembayaranAll / $subKategoriTotalDroppingAll * 100) : 0;
                            @endphp
                            <td class="text-right"><strong>{{ number_format($persenSubKatPermintaan, 2, ',', '.') }}%</strong></td>
                            <td class="text-right"><strong>{{ number_format($persenSubKatDropping, 2, ',', '.') }}%</strong></td>
                        </tr>
                    @endforeach
                    
                    {{-- Total Kategori --}}
                    <tr class="bg-navy">
                        <td></td>
                        <td style="vertical-align: middle;"><strong>Total {{ $kategori }}</strong></td>
                        @foreach($bulanListFiltered as $noBulan => $namaBulan)
                            <td class="text-right"><strong>{{ number_format($kategoriTotalPermintaan[$noBulan], 0, ',', '.') }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($kategoriTotalDropping[$noBulan], 0, ',', '.') }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($kategoriTotalPembayaran[$noBulan], 0, ',', '.') }}</strong></td>
                        @endforeach
                        
                        {{-- Total Tahun Kategori --}}
                        <td class="text-right"><strong>{{ number_format($kategoriTotalPermintaanAll, 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($kategoriTotalDroppingAll, 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($kategoriTotalPembayaranAll, 0, ',', '.') }}</strong></td>
                        
                        {{-- Persentase Kategori --}}
                        @php
                            $persenKatPermintaan = $kategoriTotalPermintaanAll > 0 ? ($kategoriTotalPembayaranAll / $kategoriTotalPermintaanAll * 100) : 0;
                            $persenKatDropping = $kategoriTotalDroppingAll > 0 ? ($kategoriTotalPembayaranAll / $kategoriTotalDroppingAll * 100) : 0;
                        @endphp
                        <td class="text-right"><strong>{{ number_format($persenKatPermintaan, 2, ',', '.') }}%</strong></td>
                        <td class="text-right"><strong>{{ number_format($persenKatDropping, 2, ',', '.') }}%</strong></td>
                    </tr>
                    
                    @php
                        $grandTotalPermintaanAll += $kategoriTotalPermintaanAll;
                        $grandTotalDroppingAll += $kategoriTotalDroppingAll;
                        $grandTotalPembayaranAll += $kategoriTotalPembayaranAll;
                    @endphp
                @endforeach
                
                {{-- GRAND TOTAL --}}
                <tr class="total-section">
                    <td></td>
                    <td><strong>TOTAL KESELURUHAN</strong></td>
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        <td class="text-right"><strong>{{ number_format($grandTotalPermintaan[$noBulan], 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($grandTotalDropping[$noBulan], 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($grandTotalPembayaran[$noBulan], 0, ',', '.') }}</strong></td>
                    @endforeach
                    
                    {{-- Total Tahun Grand Total --}}
                    <td class="text-right"><strong>{{ number_format($grandTotalPermintaanAll, 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($grandTotalDroppingAll, 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($grandTotalPembayaranAll, 0, ',', '.') }}</strong></td>
                    
                    {{-- Persentase Grand Total --}}
                    @php
                        $persenGrandPermintaan = $grandTotalPermintaanAll > 0 ? ($grandTotalPembayaranAll / $grandTotalPermintaanAll * 100) : 0;
                        $persenGrandDropping = $grandTotalDroppingAll > 0 ? ($grandTotalPembayaranAll / $grandTotalDroppingAll * 100) : 0;
                    @endphp
                    <td class="text-right"><strong>{{ number_format($persenGrandPermintaan, 2, ',', '.') }}%</strong></td>
                    <td class="text-right"><strong>{{ number_format($persenGrandDropping, 2, ',', '.') }}%</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@push('scripts')
<script type="text/javascript">
    window.print();
</script>
@endpush