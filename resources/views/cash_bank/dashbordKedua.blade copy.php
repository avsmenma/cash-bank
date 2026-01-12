@push('styles')
<style>
    #cashflow-table {
        table-layout: auto !important;
        font-size: 10px;
    }

    #cashflow-table th,
    #cashflow-table td {
        white-space: nowrap;
        vertical-align: middle;
        padding: 6px 4px;
        border: 1px solid #dee2e6;
    }
    
    .header-bulan {
        text-align: center;
        font-weight: bold;
        font-size: 11px;
    }
    
    .bg-januari { background-color: #1e3a8a !important; color: white; }
    .bg-februari { background-color: #f97316 !important; color: white; }
    .bg-maret { background-color: #16a34a !important; color: white; }
    .bg-april { background-color: #3b82f6 !important; color: white; }
    .bg-mei { background-color: #a855f7 !important; color: white; }
    
    .col-permintaan { background-color: #fef3c7; }
    .col-dropping { background-color: #ccfbf1; }
    .col-pembayaran { background-color: #fed7aa; }
    
    .kategori-row {
        background-color: #e5e7eb;
        font-weight: bold;
    }
    
    .sub-kategori-row {
        background-color: #f3f4f6;
        font-weight: 600;
    }
    
    .item-row {
        padding-left: 30px;
    }
    
    .total-kategori {
        background-color: #93c5fd;
        font-weight: bold;
    }
    
    .total-section {
        background-color: #86efac;
        font-weight: bold;
    }
    
    .no-col {
        width: 40px;
        text-align: center;
    }
    
    .description-col {
        min-width: 300px;
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
        6 => 'bg-primary',
        7 => 'bg-success',
        8 => 'bg-warning',
        9 => 'bg-danger',
        10 => 'bg-info',
        11 => 'bg-secondary',
        12 => 'bg-dark'
    ];
@endphp

<div class="row">
    <div class="col-12 table-responsive">
        <table class="table table-bordered table-sm" id="cashflow-table">
            <thead>
                <!-- Header Bulan -->
                <tr>
                    <th rowspan="3" class="no-col">No.</th>
                    <th rowspan="3" class="description-col">Payments for 2025 transactions - Accounts</th>
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        @php
                        $colorClass = $bulanColors[$noBulan] ?? 'bg-primary';
                        @endphp
                        <th colspan="3" class="header-bulan {{ $colorClass }}">
                            {{ ucfirst($namaBulan) }} {{ $tahun }}
                        </th>
                    @endforeach
                    <th rowspan="3" class="kolom-total" style="width: 150px;">Total<br>Tahun {{ $tahun }}</th>
                    <th rowspan="2" class="kolom-total" style="width: 150px;">%Tase Pembayaran Thdp	</th>
                </tr>
                
                <!-- Sub Header: Permintaan RD, Dropping HO, Pembayaran -->
                <tr>
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        <th class="text-center col-permintaan">Permintaan<br>RD</th>
                        <th class="text-center col-dropping">Dropping<br>HO</th>
                        <th class="text-center col-pembayaran">Pembayaran</th>
                    @endforeach
                </tr>
                
                <!-- Nomor Kolom -->
                <tr>
                    @php $colNum = 1; @endphp
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        <th class="text-center col-permintaan">{{ $colNum++ }}</th>
                        <th class="text-center col-dropping">{{ $colNum++ }}</th>
                        <th class="text-center col-pembayaran">{{ $colNum++ }}</th>
                    @endforeach
                </tr>
            </thead>
            
            <tbody>
                @php
                    $rowNumber = 1;
                    $currentKategori = null;
                    $currentSubKategori = null;
                    
                    // Initialize totals
                    $subtotalPerKategori = [];
                    $subtotalPermintaan = [];
                    $subtotalDropping = [];
                    $subtotalPembayaran = [];
                    
                    foreach($bulanListFiltered as $b => $n) {
                        $subtotalPermintaan[$b] = 0;
                        $subtotalDropping[$b] = 0;
                        $subtotalPembayaran[$b] = 0;
                    }
                @endphp
                
                @if(isset($result['pembayaran']))
                    @foreach($result['pembayaran'] as $item)
                        @php
                            $showKategoriHeader = ($currentKategori !== $item['kategori']);
                            $showSubKategoriHeader = ($currentSubKategori !== $item['sub_kriteria']);
                            
                            // Show total for previous kategori
                            if ($showKategoriHeader && $currentKategori !== null) {
                                @endphp
                                <tr class="total-kategori">
                                    <td></td>
                                    <td><strong>Total {{ $currentKategori }}</strong></td>
                                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                        @php
                                            $permintaan = $subtotalPerKategori[$currentKategori]['permintaan'][$noBulan] ?? 0;
                                            $dropping = $subtotalPerKategori[$currentKategori]['dropping'][$noBulan] ?? 0;
                                            $pembayaran = $subtotalPerKategori[$currentKategori]['pembayaran'][$noBulan] ?? 0;
                                        @endphp
                                        <td class="text-right">{{ number_format($permintaan, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($dropping, 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($pembayaran, 0, ',', '.') }}</td>
                                    @endforeach
                                </tr>
                                @php
                            }
                            
                            // Initialize kategori totals
                            if ($showKategoriHeader) {
                                if (!isset($subtotalPerKategori[$item['kategori']])) {
                                    $subtotalPerKategori[$item['kategori']] = [
                                        'permintaan' => [],
                                        'dropping' => [],
                                        'pembayaran' => []
                                    ];
                                    foreach($bulanListFiltered as $b => $n) {
                                        $subtotalPerKategori[$item['kategori']]['permintaan'][$b] = 0;
                                        $subtotalPerKategori[$item['kategori']]['dropping'][$b] = 0;
                                        $subtotalPerKategori[$item['kategori']]['pembayaran'][$b] = 0;
                                    }
                                }
                            }
                        @endphp
                        
                        {{-- Header Kategori --}}
                        @if($showKategoriHeader)
                            <tr class="kategori-row">
                                <td class="text-center">{{ $rowNumber++ }}</td>
                                <td><strong>{{ $item['kategori'] }}</strong></td>
                                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                    <td colspan="3"></td>
                                @endforeach
                            </tr>
                            @php $currentKategori = $item['kategori']; @endphp
                        @endif
                        
                        {{-- Sub Kategori Header --}}
                        @if($showSubKategoriHeader)
                            <tr class="sub-kategori-row">
                                <td></td>
                                <td style="padding-left: 20px;">{{ $item['sub_kriteria'] }}</td>
                                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                    <td colspan="3"></td>
                                @endforeach
                            </tr>
                            @php $currentSubKategori = $item['sub_kriteria']; @endphp
                        @endif
                        
                        {{-- Item Detail Row --}}
                        <tr class="item-row">
                            <td></td>
                            <td style="padding-left: 40px;">{{ $item['item_kriteria'] }}</td>
                            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                @php
                                    // Get permintaan data (you need to add this to your controller)
                                    $permintaan = $item['data_permintaan'][$noBulan] ?? 0;
                                    
                                    // Get dropping data
                                    $dropping = 0;
                                    if(isset($result['dropping'])) {
                                        foreach($result['dropping'] as $dropItem) {
                                            if($dropItem['item_kriteria'] == $item['item_kriteria']) {
                                                $dropping = $dropItem['data'][$noBulan] ?? 0;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // Get pembayaran data
                                    $pembayaran = $item['data'][$noBulan] ?? 0;
                                    
                                    // Add to totals
                                    $subtotalPermintaan[$noBulan] += $permintaan;
                                    $subtotalDropping[$noBulan] += $dropping;
                                    $subtotalPembayaran[$noBulan] += $pembayaran;
                                    
                                    // Add to kategori totals
                                    $subtotalPerKategori[$currentKategori]['permintaan'][$noBulan] += $permintaan;
                                    $subtotalPerKategori[$currentKategori]['dropping'][$noBulan] += $dropping;
                                    $subtotalPerKategori[$currentKategori]['pembayaran'][$noBulan] += $pembayaran;
                                @endphp
                                <td class="text-right col-permintaan">{{ $permintaan > 0 ? number_format($permintaan, 0, ',', '.') : '-' }}</td>
                                <td class="text-right col-dropping">{{ $dropping > 0 ? number_format($dropping, 0, ',', '.') : '-' }}</td>
                                <td class="text-right col-pembayaran">{{ $pembayaran > 0 ? number_format($pembayaran, 0, ',', '.') : '-' }}</td>
                            @endforeach
                        </tr>
                        
                        {{-- Show total for last kategori --}}
                        @if($loop->last && $currentKategori !== null)
                            <tr class="total-kategori">
                                <td></td>
                                <td><strong>Total {{ $currentKategori }}</strong></td>
                                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                    @php
                                        $permintaan = $subtotalPerKategori[$currentKategori]['permintaan'][$noBulan] ?? 0;
                                        $dropping = $subtotalPerKategori[$currentKategori]['dropping'][$noBulan] ?? 0;
                                        $pembayaran = $subtotalPerKategori[$currentKategori]['pembayaran'][$noBulan] ?? 0;
                                    @endphp
                                    <td class="text-right">{{ number_format($permintaan, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($dropping, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($pembayaran, 0, ',', '.') }}</td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                @endif
                
                {{-- GRAND TOTAL --}}
                <tr class="total-section">
                    <td></td>
                    <td><strong>TOTAL KESELURUHAN</strong></td>
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        <td class="text-right"><strong>{{ number_format($subtotalPermintaan[$noBulan], 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($subtotalDropping[$noBulan], 0, ',', '.') }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($subtotalPembayaran[$noBulan], 0, ',', '.') }}</strong></td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>