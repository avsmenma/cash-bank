
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
                <th colspan="3" class="text-center">Total S/D {{ $namaBulan }}</th>
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
        // INITIALIZE KATEGORI TOTALS - INI YANG HILANG!
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
    <tr class="bg-yellow">
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
            // INITIALIZE SUB KATEGORI TOTALS
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
            <tr class="item-row">
                <td></td>
                <td style="padding-left: 40px;">{{"- " . $itemKriteria }}</td>
                @php
                    $totalPermintaanItem = 0;
                    $totalDroppingItem = 0;
                    $totalPembayaranItem = 0;
                @endphp
                
                {{-- LOOP PERTAMA: Hitung Total --}}
                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                    @php
                        $permintaan = $data['permintaan'][$noBulan] ?? 0;
                        $dropping = $data['dropping'][$noBulan] ?? 0;
                        $pembayaran = $data['pembayaran'][$noBulan] ?? 0;
                        
                        // SEKARANG $kategoriTotalPermintaan[$noBulan] SUDAH TERSEDIA!
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
                @endforeach
                
                {{-- AMBIL ID --}}
                @php
                    $idKategori = null;
                    $idSubKriteria = null;
                    $idItemKriteria = null;
                    
                    foreach(['permintaan', 'dropping', 'pembayaran'] as $tipe) {
                        if(isset($result[$tipe]) && is_array($result[$tipe])) {
                            foreach($result[$tipe] as $resultItem) {
                                if(isset($resultItem['kategori'], $resultItem['sub_kriteria'], $resultItem['item_kriteria'], 
                                         $resultItem['id_kategori_kriteria'], $resultItem['id_sub_kriteria'], $resultItem['id_item_sub_kriteria'])) {
                                    if($resultItem['kategori'] == $kategori && 
                                       $resultItem['sub_kriteria'] == $subKriteria && 
                                       $resultItem['item_kriteria'] == $itemKriteria) {
                                        $idKategori = $resultItem['id_kategori_kriteria'];
                                        $idSubKriteria = $resultItem['id_sub_kriteria'];
                                        $idItemKriteria = $resultItem['id_item_sub_kriteria'];
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                @endphp
                
                {{-- LOOP KEDUA: Buat Cell --}}
                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                    @php
                        $permintaanCell = $data['permintaan'][$noBulan] ?? 0;
                        $droppingCell = $data['dropping'][$noBulan] ?? 0;
                        $pembayaranCell = $data['pembayaran'][$noBulan] ?? 0;
                    @endphp
                    
                    <td class="text-center p-0" style="vertical-align: middle;">
                        @if(($permintaanCell > 0 || $droppingCell > 0 || $pembayaranCell > 0) && $idKategori)
                            <a href="{{ route('cashflow.detail', [
                                'bulan' => $noBulan,
                                'tahun' => $tahun,
                                'week' => 1,
                                'id_kategori' => $idKategori,
                                'id_sub_kriteria' => $idSubKriteria,
                                'id_item' => $idItemKriteria,
                                'nama_item' => $itemKriteria
                            ]) }}" class="d-block text-decoration-none p-2">
                                <table class="w-100 mb-0">
                                    <tr>
                                        <td class="text-right" style="border: none; font-size: 10px;">
                                            <span class="text-primary">{{ $permintaanCell > 0 ? number_format($permintaanCell, 0, ',', '.') : '-' }}</span>
                                        </td>
                                        <td class="text-right" style="border: none; font-size: 10px;">
                                            <span class="text-success">{{ $droppingCell > 0 ? number_format($droppingCell, 0, ',', '.') : '-' }}</span>
                                        </td>
                                        <td class="text-right" style="border: none; font-size: 10px;">
                                            <span class="text-danger">{{ $pembayaranCell > 0 ? number_format($pembayaranCell, 0, ',', '.') : '-' }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </a>
                        @else
                            <table class="w-100 mb-0">
                                <tr>
                                    <td class="text-right" style="border: none; font-size: 10px;">-</td>
                                    <td class="text-right" style="border: none; font-size: 10px;">-</td>
                                    <td class="text-right" style="border: none; font-size: 10px;">-</td>
                                </tr>
                            </table>
                        @endif
                    </td>
                @endforeach
                
                {{-- Total Tahun --}}
                <td class="text-right">{{ number_format($totalPermintaanItem, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalDroppingItem, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalPembayaranItem, 0, ',', '.') }}</td>
                
                {{-- Persentase --}}
                @php
                    $persenPermintaan = $totalPermintaanItem > 0 ? ($totalPembayaranItem / $totalPermintaanItem * 100) : 0;
                    $persenDropping = $totalDroppingItem > 0 ? ($totalPembayaranItem / $totalDroppingItem * 100) : 0;
                @endphp
                <td class="text-right">{{ number_format($persenPermintaan, 2, ',', '.') }}%</td>
                <td class="text-right">{{ number_format($persenDropping, 2, ',', '.') }}%</td>
            </tr>
            
            @php
                $kategoriTotalPermintaanAll += $totalPermintaanItem;
                $kategoriTotalDroppingAll += $totalDroppingItem;
                $kategoriTotalPembayaranAll += $totalPembayaranItem;
                
                $subKategoriTotalPermintaanAll += $totalPermintaanItem;
                $subKategoriTotalDroppingAll += $totalDroppingItem;
                $subKategoriTotalPembayaranAll += $totalPembayaranItem;
            @endphp
        @endforeach {{-- items --}}
        
        {{-- Total Sub Kategori --}}
        <tr class="table-info bg-orange">
            <td></td>
            <td><strong>Sub Total {{ $subKriteria }}</strong></td>
            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                <td class="text-right"><strong>{{ number_format($subKategoriTotalPermintaan[$noBulan], 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($subKategoriTotalDropping[$noBulan], 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($subKategoriTotalPembayaran[$noBulan], 0, ',', '.') }}</strong></td>
            @endforeach
            <td class="text-right"><strong>{{ number_format($subKategoriTotalPermintaanAll, 0, ',', '.') }}</strong></td>
            <td class="text-right"><strong>{{ number_format($subKategoriTotalDroppingAll, 0, ',', '.') }}</strong></td>
            <td class="text-right"><strong>{{ number_format($subKategoriTotalPembayaranAll, 0, ',', '.') }}</strong></td>
            @php
                $persenSubKatPermintaan = $subKategoriTotalPermintaanAll > 0 ? ($subKategoriTotalPembayaranAll / $subKategoriTotalPermintaanAll * 100) : 0;
                $persenSubKatDropping = $subKategoriTotalDroppingAll > 0 ? ($subKategoriTotalPembayaranAll / $subKategoriTotalDroppingAll * 100) : 0;
            @endphp
            <td class="text-right"><strong>{{ number_format($persenSubKatPermintaan, 2, ',', '.') }}%</strong></td>
            <td class="text-right"><strong>{{ number_format($persenSubKatDropping, 2, ',', '.') }}%</strong></td>
        </tr>
    @endforeach {{-- subKriterias --}}
    
    {{-- Total Kategori --}}
    <tr class="bg-navy">
        <td></td>
        <td><strong>Total {{ $kategori }}</strong></td>
        @foreach($bulanListFiltered as $noBulan => $namaBulan)
            <td class="text-right"><strong>{{ number_format($kategoriTotalPermintaan[$noBulan], 0, ',', '.') }}</strong></td>
            <td class="text-right"><strong>{{ number_format($kategoriTotalDropping[$noBulan], 0, ',', '.') }}</strong></td>
            <td class="text-right"><strong>{{ number_format($kategoriTotalPembayaran[$noBulan], 0, ',', '.') }}</strong></td>
        @endforeach
        <td class="text-right"><strong>{{ number_format($kategoriTotalPermintaanAll, 0, ',', '.') }}</strong></td>
        <td class="text-right"><strong>{{ number_format($kategoriTotalDroppingAll, 0, ',', '.') }}</strong></td>
        <td class="text-right"><strong>{{ number_format($kategoriTotalPembayaranAll, 0, ',', '.') }}</strong></td>
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
@endforeach {{-- organizedData --}}
                
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