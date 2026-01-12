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
                    <th rowspan="4" style="vertical-align: middle;" class="text-center">No.</th>
                    <th rowspan="4" style="min-width: 400px; max-width:500px;vertical-align: middle;" class="text-center">Payments for {{ $tahun }} transactions - Accounts</th>
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        @php
                            $colorClass = $bulanColors[$noBulan] ?? 'bg-primary';
                        @endphp
                        <th colspan="4" style="min-width: 400px; vertical-align: middle;" class="text-center {{ $colorClass }}">
                            {{ strtoupper($namaBulan) }} {{ $tahun }}
                        </th>
                    @endforeach
                    <th colspan="4" class="text-center bg-warning">SALDO AKHIR<br/>S/D {{ strtoupper(end($bulanListFiltered)) }}</th>
                </tr>
                
                <!-- Row 2: Week Headers -->
                <tr>
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        <th class="text-center" style="min-width:80px;">Week 1</th>
                        <th class="text-center" style="min-width:80px;">Week 2</th>
                        <th class="text-center" style="min-width:80px;">Week 3</th>
                        <th class="text-center" style="min-width:80px;">Week 4</th>
                    @endforeach
                    <th class="text-center bg-warning" style="min-width:80px;">Week 1</th>
                    <th class="text-center bg-warning" style="min-width:80px;">Week 2</th>
                    <th class="text-center bg-warning" style="min-width:80px;">Week 3</th>
                    <th class="text-center bg-warning" style="min-width:80px;">Week 4</th>
                </tr>
                
                <!-- Row 3: Tipe Data (Permintaan/Dropping/Pembayaran) -->
                <tr>
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        @for($week = 1; $week <= 4; $week++)
                            <th class="text-center" style="font-size: 9px; padding: 5px;">
                                <div style="white-space: nowrap;">Perm | Drop | Bayar</div>
                            </th>
                        @endfor
                    @endforeach
                    @for($week = 1; $week <= 4; $week++)
                        <th class="text-center bg-warning" style="font-size: 9px; padding: 5px;">
                            <div style="white-space: nowrap;">Perm | Drop | Bayar</div>
                        </th>
                    @endfor
                </tr>
                
                <!-- Row 4: Nomor Kolom -->
                <tr>
                    @php $colNum = 1; @endphp
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        @for($week = 1; $week <= 4; $week++)
                            <th style="vertical-align: middle;" class="text-center">{{ $colNum++ }}</th>
                        @endfor
                    @endforeach
                    @for($week = 1; $week <= 4; $week++)
                        <th style="vertical-align: middle;" class="text-center bg-warning">{{ $colNum++ }}</th>
                    @endfor
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
                    <tr class="bg-yellow">
                        <td class="text-center">{{ $rowNumber++ }}</td>
                        <td><strong>{{ $kategori }}</strong></td>
                        @foreach($bulanListFiltered as $noBulan => $namaBulan)
                            @for($week = 1; $week <= 4; $week++)
                                <td></td>
                            @endfor
                        @endforeach
                        @for($week = 1; $week <= 4; $week++)
                            <td></td>
                        @endfor
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
                            <td style="padding-left: 20px;">{{ $subKriteria }}</td>
                            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                @for($week = 1; $week <= 4; $week++)
                                    <td></td>
                                @endfor
                            @endforeach
                            @for($week = 1; $week <= 4; $week++)
                                <td></td>
                            @endfor
                        </tr>
                        
                        @foreach($items as $itemKriteria => $data)
                            {{-- Item Detail Row --}}
                            <tr class="item-row">
                                <td></td>
                                <td style="padding-left: 40px;">{{"- " . $itemKriteria }}</td>
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

                                        // Get ID untuk link
                                        $idKategori = null;
                                        $idSubKriteria = null;
                                        $idItemKriteria = null;
                                        
                                        // Cari ID dari salah satu data yang ada
                                        foreach(['permintaan', 'dropping', 'pembayaran'] as $tipe) {
                                            if(isset($result[$tipe])) {
                                                foreach($result[$tipe] as $item) {
                                                    if($item['kategori'] == $kategori && 
                                                       $item['sub_kriteria'] == $subKriteria && 
                                                       $item['item_kriteria'] == $itemKriteria) {
                                                        $idKategori = $item['id_kategori_kriteria'];
                                                        $idSubKriteria = $item['id_sub_kriteria'];
                                                        $idItemKriteria = $item['id_item_sub_kriteria'];
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    
                                    {{-- KOLOM PER MINGGU - 4 kolom per bulan --}}
                                    @for($week = 1; $week <= 4; $week++)
                                        <td class="text-center p-1" style="vertical-align: middle; font-size: 10px;">
                                            @if($permintaan > 0 || $dropping > 0 || $pembayaran > 0)
                                                <a href="{{ route('cashflow.detail', [
                                                    'bulan' => $noBulan,
                                                    'tahun' => $tahun,
                                                    'week' => $week,
                                                    'id_kategori' => $idKategori,
                                                    'id_sub_kriteria' => $idSubKriteria,
                                                    'id_item' => $idItemKriteria,
                                                    'nama_item' => $itemKriteria,
                                                    'nilai_permintaan' => $permintaan,
                                                    'nilai_dropping' => $dropping,
                                                    'nilai_pembayaran' => $pembayaran
                                                ]) }}" 
                                                class="d-block text-decoration-none" 
                                                style="cursor: pointer; line-height: 1.2;"
                                                title="Klik untuk detail {{ $namaBulan }} Minggu {{ $week }}">
                                                    <span class="text-primary d-block">{{ $permintaan > 0 ? number_format($permintaan/4, 0, ',', '.') : '-' }}</span>
                                                    <span class="text-success d-block">{{ $dropping > 0 ? number_format($dropping/4, 0, ',', '.') : '-' }}</span>
                                                    <span class="text-danger d-block">{{ $pembayaran > 0 ? number_format($pembayaran/4, 0, ',', '.') : '-' }}</span>
                                                </a>
                                            @else
                                                <span class="d-block">-</span>
                                                <span class="d-block">-</span>
                                                <span class="d-block">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                @endforeach
                                
                                {{-- Total Tahun untuk Item (4 kolom) --}}
                                @for($week = 1; $week <= 4; $week++)
                                    <td class="text-center bg-warning p-1" style="vertical-align: middle; font-size: 10px; line-height: 1.2;">
                                        <span class="text-primary d-block">{{ number_format($totalPermintaanItem/4, 0, ',', '.') }}</span>
                                        <span class="text-success d-block">{{ number_format($totalDroppingItem/4, 0, ',', '.') }}</span>
                                        <span class="text-danger d-block">{{ number_format($totalPembayaranItem/4, 0, ',', '.') }}</span>
                                    </td>
                                @endfor
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
                            <td style="padding-left: 20px;"><strong>Sub Total {{ $subKriteria }}</strong></td>
                            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                @for($week = 1; $week <= 4; $week++)
                                    <td class="text-center p-1" style="vertical-align: middle; font-size: 10px; line-height: 1.2;">
                                        <strong class="text-primary d-block">{{ number_format($subKategoriTotalPermintaan[$noBulan]/4, 0, ',', '.') }}</strong>
                                        <strong class="text-success d-block">{{ number_format($subKategoriTotalDropping[$noBulan]/4, 0, ',', '.') }}</strong>
                                        <strong class="text-danger d-block">{{ number_format($subKategoriTotalPembayaran[$noBulan]/4, 0, ',', '.') }}</strong>
                                    </td>
                                @endfor
                            @endforeach
                            
                            {{-- Total Tahun Sub Kategori --}}
                            @for($week = 1; $week <= 4; $week++)
                                <td class="text-center bg-warning p-1" style="vertical-align: middle; font-size: 10px; line-height: 1.2;">
                                    <strong class="text-primary d-block">{{ number_format($subKategoriTotalPermintaanAll/4, 0, ',', '.') }}</strong>
                                    <strong class="text-success d-block">{{ number_format($subKategoriTotalDroppingAll/4, 0, ',', '.') }}</strong>
                                    <strong class="text-danger d-block">{{ number_format($subKategoriTotalPembayaranAll/4, 0, ',', '.') }}</strong>
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                    
                    {{-- Total Kategori --}}
                    <tr class="bg-navy">
                        <td></td>
                        <td style="vertical-align: middle;"><strong>Total {{ $kategori }}</strong></td>
                        @foreach($bulanListFiltered as $noBulan => $namaBulan)
                            @for($week = 1; $week <= 4; $week++)
                                <td class="text-center p-1" style="vertical-align: middle; font-size: 10px; line-height: 1.2;">
                                    <strong class="text-white d-block">{{ number_format($kategoriTotalPermintaan[$noBulan]/4, 0, ',', '.') }}</strong>
                                    <strong class="text-white d-block">{{ number_format($kategoriTotalDropping[$noBulan]/4, 0, ',', '.') }}</strong>
                                    <strong class="text-white d-block">{{ number_format($kategoriTotalPembayaran[$noBulan]/4, 0, ',', '.') }}</strong>
                                </td>
                            @endfor
                        @endforeach
                        
                        {{-- Total Tahun Kategori --}}
                        @for($week = 1; $week <= 4; $week++)
                            <td class="text-center bg-warning p-1" style="vertical-align: middle; font-size: 10px; line-height: 1.2;">
                                <strong class="d-block">{{ number_format($kategoriTotalPermintaanAll/4, 0, ',', '.') }}</strong>
                                <strong class="d-block">{{ number_format($kategoriTotalDroppingAll/4, 0, ',', '.') }}</strong>
                                <strong class="d-block">{{ number_format($kategoriTotalPembayaranAll/4, 0, ',', '.') }}</strong>
                            </td>
                        @endfor
                    </tr>
                    
                    @php
                        $grandTotalPermintaanAll += $kategoriTotalPermintaanAll;
                        $grandTotalDroppingAll += $kategoriTotalDroppingAll;
                        $grandTotalPembayaranAll += $kategoriTotalPembayaranAll;
                    @endphp
                @endforeach
                
                {{-- GRAND TOTAL --}}
                <tr class="total-section bg-dark">
                    <td></td>
                    <td><strong>TOTAL KESELURUHAN</strong></td>
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        @for($week = 1; $week <= 4; $week++)
                            <td class="text-center p-1" style="vertical-align: middle; font-size: 10px; line-height: 1.2;">
                                <strong class="text-white d-block">{{ number_format($grandTotalPermintaan[$noBulan]/4, 0, ',', '.') }}</strong>
                                <strong class="text-white d-block">{{ number_format($grandTotalDropping[$noBulan]/4, 0, ',', '.') }}</strong>
                                <strong class="text-white d-block">{{ number_format($grandTotalPembayaran[$noBulan]/4, 0, ',', '.') }}</strong>
                            </td>
                        @endfor
                    @endforeach
                    
                    {{-- Total Tahun Grand Total --}}
                    @for($week = 1; $week <= 4; $week++)
                        <td class="text-center bg-warning p-1" style="vertical-align: middle; font-size: 10px; line-height: 1.2;">
                            <strong class="d-block">{{ number_format($grandTotalPermintaanAll/4, 0, ',', '.') }}</strong>
                            <strong class="d-block">{{ number_format($grandTotalDroppingAll/4, 0, ',', '.') }}</strong>
                            <strong class="d-block">{{ number_format($grandTotalPembayaranAll/4, 0, ',', '.') }}</strong>
                        </td>
                    @endfor
                </tr>
            </tbody>
        </table>
    </div>
</div>