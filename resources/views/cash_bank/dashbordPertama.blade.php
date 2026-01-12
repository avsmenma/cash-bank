@push('styles')
<style>
    #cashflow-table {
        table-layout: auto !important;
        /* width: 100% !important; */
        font-size: 11px;
        
    }

    .kolom-total{
        background-color: #f6fa05;
    }
    .kolom-dropping{
        background-color: #05f2fa;
    }
    .kolom-pembayaran{
        background-color: #faac05;
    }

    #cashflow-table th,
    #cashflow-table td {
        white-space: nowrap;
        vertical-align: middle;
        padding: 8px 5px;
    }
    
    .kategori-row{
        background-color: #e0e0e0;
    }
    

</style>
@endpush
@php
    function formatMinus($angka) {
        return $angka < 0
            ? '(' . number_format(abs($angka), 0, ',', '.') . ')'
            : number_format($angka, 0, ',', '.');
    }
@endphp

<div class="row">
    <div class="col-12 table-responsive">
        <table border="2" class="table table-bordered  table-sm" id="cashflow-table">
            <thead class="text-center">
                <tr>
                    <th rowspan="2"class="table-warning" style="min-width: 250px; vertical-align: middle;">URAIAN</th>
                    <th colspan="{{ count($bulanListFiltered) }}" class="table-primary">TAHUN {{ $tahun }}</th>
                    <th rowspan="2" class="table-success" style="width: 150px;">Total<br>Tahun {{ $tahun }}</th>
                </tr>
                <tr>
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        <th class="table-primary" style="width: 150px;">{{ $namaBulan }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{-- SECTION PENERIMA --}}
                <tr class="section-header fw-bold">
                    <td colspan="{{ count($bulanListFiltered) + 2 }}" class="kolom-total"><strong>PENERIMAAN</strong></td>
                </tr>
                
                @php
                    $subtotalPenerima = [];
                    foreach($bulanListFiltered as $b => $n) {
                        $subtotalPenerima[$b] = 0;
                    }
                @endphp
                
                @foreach($result['penerima'] as $kategori => $bulanData)
                    @php
                        $totalKategori = 0;
                    @endphp
                    <tr class="kategori-row2">
                        <td>{{ $kategori }}</td>
                        @foreach($bulanListFiltered as $noBulan => $namaBulan)
                            @php
                                $nilai = $bulanData[$noBulan] ?? 0;
                                $totalKategori += $nilai;
                                $subtotalPenerima[$noBulan] += $nilai;
                            @endphp
                            <td class="text-right">{{ number_format($nilai, 0, ',', '.') }}</td>
                        @endforeach
                        <td class="text-right font-weight-bold">{{ number_format($totalKategori, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                
                {{-- TOTAL PENERIMAAN --}}
                <tr class="total-row table-success">
                    <td class="text-right"><strong>TOTAL PENERIMAAN</strong></td>
                    @php $totalPenerimaanAll = 0; @endphp
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        @php $totalPenerimaanAll += $subtotalPenerima[$noBulan]; @endphp
                        <td class="text-right">{{ number_format($subtotalPenerima[$noBulan], 0, ',', '.') }}</td>
                    @endforeach
                    <td class="text-right">{{ number_format($totalPenerimaanAll, 0, ',', '.') }}</td>
                </tr>

                <tr>
                    <td></td>
                </tr>
                {{-- SECTION DROPPING --}}
                <tr class="section-header">
                    <td colspan="{{ count($bulanListFiltered) + 2 }}" class="kolom-dropping"><strong>DROPPING HO</strong></td>
                </tr>
                
                @php
                    $subtotalDropping = [];
                    $subtotalPerKategori = []; // Untuk tracking total per kategori
                    foreach($bulanListFiltered as $b => $n) {
                        $subtotalDropping[$b] = 0;
                    }
                    $currentKategori = null;
                @endphp

                @if(isset($result['dropping']) && count($result['dropping']) > 0)
                    @foreach($result['dropping'] as $key => $item)
                        @php
                            $totalItem = 0;
                            $showKategoriHeader = ($currentKategori !== $item['kategori']);
                            
                            // Jika kategori berubah dan bukan pertama kali, tampilkan total kategori sebelumnya
                            $showKategoriTotal = false;
                            if ($currentKategori !== null && $showKategoriHeader) {
                                $showKategoriTotal = true;
                            }
                            
                            // Inisialisasi array untuk kategori baru
                            if ($showKategoriHeader && !isset($subtotalPerKategori[$item['kategori']])) {
                                $subtotalPerKategori[$item['kategori']] = [];
                                foreach($bulanListFiltered as $b => $n) {
                                    $subtotalPerKategori[$item['kategori']][$b] = 0;
                                }
                            }
                        @endphp
                        
                        {{-- TAMPILKAN TOTAL KATEGORI SEBELUMNYA --}}
                        @if($showKategoriTotal)
                            <tr class="table-info font-weight-bold">
                                <td style="padding-left: 15px;">
                                Jumlah {{ $currentKategori }}
                                </td>
                                @php $totalKategoriAll = 0; @endphp
                                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                    @php 
                                        $nilaiKat = $subtotalPerKategori[$currentKategori][$noBulan] ?? 0;
                                        $totalKategoriAll += $nilaiKat;
                                    @endphp
                                    <td class="text-right">{{ number_format($nilaiKat, 0, ',', '.') }}</td>
                                @endforeach
                                <td class="text-right">{{ number_format($totalKategoriAll, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        
                        {{-- HEADER KATEGORI BARU --}}
                        @if($showKategoriHeader)
                            <tr class="kategori-row">
                                <td colspan="{{ count($bulanListFiltered) + 2 }}">
                                    <strong>{{ $item['kategori'] }}</strong>
                                </td>
                            </tr>
                            @php $currentKategori = $item['kategori']; @endphp
                        @endif
                        
                        {{-- DETAIL ROW --}}
                        <tr class="detail-row">
                            <td>
                                {{ $item['sub_kriteria'] }}
                            </td>
                        </tr>
                        <tr class="detail-row">
                            <td class="text-info"> 
                                {{ "- " . $item['item_kriteria'] }}
                            </td>
                            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                @php
                                    $nilai = $item['data'][$noBulan] ?? 0;
                                    $totalItem += $nilai;
                                    $subtotalDropping[$noBulan] += $nilai;
                                    
                                    // Tambahkan ke subtotal kategori
                                    if (!isset($subtotalPerKategori[$currentKategori][$noBulan])) {
                                        $subtotalPerKategori[$currentKategori][$noBulan] = 0;
                                    }
                                    $subtotalPerKategori[$currentKategori][$noBulan] += $nilai;
                                @endphp
                                <td class="text-right">{{ number_format($nilai, 0, ',', '.') }}</td>
                            @endforeach
                            <td class="text-right">{{ number_format($totalItem, 0, ',', '.') }}</td>
                        </tr>
                        
                        {{-- TAMPILKAN TOTAL KATEGORI TERAKHIR (di akhir loop) --}}
                        @if($loop->last && $currentKategori !== null)
                            <tr class="table-info font-weight-bold">
                                <td style="padding-left: 15px;">
                                Jumlah {{ $currentKategori }}
                                </td>
                                @php $totalKategoriAll = 0; @endphp
                                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                    @php 
                                        $nilaiKat = $subtotalPerKategori[$currentKategori][$noBulan] ?? 0;
                                        $totalKategoriAll += $nilaiKat;
                                    @endphp
                                    <td class="text-right">{{ number_format($nilaiKat, 0, ',', '.') }}</td>
                                @endforeach
                                <td class="text-right">{{ number_format($totalKategoriAll, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                    
                    {{-- TOTAL DROPPING KESELURUHAN --}}
                    <tr class="total-row table-success">
                        <td class="text-right"><strong> TOTAL DROPPING HO</strong></td>
                        @php $totalDroppingAll = 0; @endphp
                        @foreach($bulanListFiltered as $noBulan => $namaBulan)
                            @php $totalDroppingAll += $subtotalDropping[$noBulan]; @endphp
                            <td class="text-right">{{ number_format($subtotalDropping[$noBulan], 0, ',', '.') }}</td>
                        @endforeach
                        <td class="text-right">{{ number_format($totalDroppingAll, 0, ',', '.') }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="{{ count($bulanListFiltered) + 2 }}" class="text-center text-muted">
                            Tidak ada data dropping
                        </td>
                    </tr>
                @endif

                {{-- SELISIH PENERIMAAN DAN DROPPING --}}
                <tr class="kolom-total font-weight-bold ">
                    <td >SELISIH PENERIMAAN TERHADAP DROPPING HO</td>
                    @php $selisihAll = 0; @endphp
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        @php 
                            $selisih = $subtotalPenerima[$noBulan] - $subtotalDropping[$noBulan];
                            $selisihAll += $selisih;
                        @endphp
                        <td class="text-right"> {{ formatMinus($selisih) }}</td>
                    @endforeach
                    <td class="text-right"> {{ formatMinus($selisihAll) }}</td>
                </tr>

                <tr>
                    <td></td>
                </tr>
                {{-- SECTION PEMBAYARAN --}}
                <tr class="section-header">
                    <td colspan="{{ count($bulanListFiltered) + 2 }}" class="kolom-dropping">PEMBAYARAN</td>
                </tr>

                @php
                    $subtotalPembayaran = [];
                    $subtotalPerKategoriPembayaran = [];
                    foreach($bulanListFiltered as $b => $n) {
                        $subtotalPembayaran[$b] = 0;
                    }
                    $currentKategoriPembayaran = null;
                @endphp

                @if(isset($result['pembayaran']) && count($result['pembayaran']) > 0)
                    @foreach($result['pembayaran'] as $key => $item)
                        @php
                            $totalItem = 0;
                            $showKategoriHeader = ($currentKategoriPembayaran !== $item['kategori']);
                            
                            // Tampilkan total kategori sebelumnya
                            $showKategoriTotal = false;
                            if ($currentKategoriPembayaran !== null && $showKategoriHeader) {
                                $showKategoriTotal = true;
                            }
                            
                            // Inisialisasi array untuk kategori baru
                            if ($showKategoriHeader && !isset($subtotalPerKategoriPembayaran[$item['kategori']])) {
                                $subtotalPerKategoriPembayaran[$item['kategori']] = [];
                                foreach($bulanListFiltered as $b => $n) {
                                    $subtotalPerKategoriPembayaran[$item['kategori']][$b] = 0;
                                }
                            }
                        @endphp
                        
                        {{-- TAMPILKAN TOTAL KATEGORI SEBELUMNYA --}}
                        @if($showKategoriTotal)
                            <tr class="table-info font-weight-bold">
                                <td style="padding-left: 15px;">
                                    Jumlah {{ $currentKategoriPembayaran }}
                                </td>
                                @php $totalKategoriAll = 0; @endphp
                                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                    @php 
                                        $nilaiKat = $subtotalPerKategoriPembayaran[$currentKategoriPembayaran][$noBulan] ?? 0;
                                        $totalKategoriAll += $nilaiKat;
                                    @endphp
                                    <td class="text-right">{{ number_format($nilaiKat, 0, ',', '.') }}</td>
                                @endforeach
                                <td class="text-right">{{ number_format($totalKategoriAll, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        
                        {{-- HEADER KATEGORI BARU --}}
                        @if($showKategoriHeader)
                            <tr class="kategori-row">
                                <td colspan="{{ count($bulanListFiltered) + 2 }}">
                                    <strong>{{ $item['kategori'] }}</strong>
                                </td>
                            </tr>
                            @php $currentKategoriPembayaran = $item['kategori']; @endphp
                        @endif
                        
                        {{-- DETAIL ROW --}}
                        <tr class="text-info">
                            <td>{{ $item['sub_kriteria'] }}</td>
                        </tr>
                        <tr class="detail-row">
                            <td>
                               
                               {{ "- " . $item['item_kriteria'] }}
                            </td>
                            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                @php
                                    $nilai = $item['data'][$noBulan] ?? 0;
                                    $totalItem += $nilai;
                                    $subtotalPembayaran[$noBulan] += $nilai;
                                    
                                    // Tambahkan ke subtotal kategori
                                    if (!isset($subtotalPerKategoriPembayaran[$currentKategoriPembayaran][$noBulan])) {
                                        $subtotalPerKategoriPembayaran[$currentKategoriPembayaran][$noBulan] = 0;
                                    }
                                    $subtotalPerKategoriPembayaran[$currentKategoriPembayaran][$noBulan] += $nilai;
                                @endphp
                                <td class="text-right">{{ number_format($nilai, 0, ',', '.') }}</td>
                            @endforeach
                            <td class="text-right">{{ number_format($totalItem, 0, ',', '.') }}</td>
                        </tr>
                        
                        {{-- TAMPILKAN TOTAL KATEGORI TERAKHIR --}}
                        @if($loop->last && $currentKategoriPembayaran !== null)
                            <tr class="table-info font-weight-bold">
                                <td style="padding-left: 15px;">
                                    Jumlah {{ $currentKategoriPembayaran }}
                                </td>
                                @php $totalKategoriAll = 0; @endphp
                                @foreach($bulanListFiltered as $noBulan => $namaBulan)
                                    @php 
                                        $nilaiKat = $subtotalPerKategoriPembayaran[$currentKategoriPembayaran][$noBulan] ?? 0;
                                        $totalKategoriAll += $nilaiKat;
                                    @endphp
                                    <td class="text-right">{{ number_format($nilaiKat, 0, ',', '.') }}</td>
                                @endforeach
                                <td class="text-right">{{ number_format($totalKategoriAll, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                @else
                    <tr>
                        <td colspan="{{ count($bulanListFiltered) + 2 }}" class="text-center text-muted">
                            Tidak ada data pembayaran
                        </td>
                    </tr>
                @endif

                {{-- TOTAL PEMBAYARAN --}}
                <tr class="total-row table-success">
                    <td class="text-right"><strong>TOTAL PEMBAYARAN</strong></td>
                    @php $totalPembayaranAll = 0; @endphp
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        @php $totalPembayaranAll += $subtotalPembayaran[$noBulan]; @endphp
                        <td class="text-right">{{ number_format($subtotalPembayaran[$noBulan], 0, ',', '.') }}</td>
                    @endforeach
                    <td class="text-right">{{ number_format($totalPembayaranAll, 0, ',', '.') }}</td>
                </tr>
                {{-- SELISIH DROPPING DAN PEMBAYARAN --}}
                <tr class="kolom-total font-weight-bold">
                    <td>SELISIH DROPPING TERHADAP PEMBAYARAN</td>
                    @php $selisihDropPembayaranAll = 0; @endphp
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        @php 
                            $selisihDropPembayaran = $subtotalDropping[$noBulan] - $subtotalPembayaran[$noBulan];
                            $selisihDropPembayaranAll += $selisihDropPembayaran;
                        @endphp
                        <td class="text-right">{{ formatMinus($selisihDropPembayaran) }}</td>
                    @endforeach
                    <td class="text-right">{{ formatMinus($selisihDropPembayaranAll) }}</td>
                </tr>

                {{-- SELISIH PENERIMAAN DAN PEMBAYARAN --}}
                <tr class="bg-danger text-white font-weight-bold">
                    <td>SELISIH PENERIMAAN TERHADAP PEMBAYARAN</td>
                    @php $selisihPenerimaanPembayaranAll = 0; @endphp
                    @foreach($bulanListFiltered as $noBulan => $namaBulan)
                        @php 
                            $selisihPenerimaanPembayaran = $subtotalPenerima[$noBulan] - $subtotalPembayaran[$noBulan];
                            $selisihPenerimaanPembayaranAll += $selisihPenerimaanPembayaran;
                        @endphp
                        <td class="text-right">{{ formatMinus($selisihPenerimaanPembayaran) }}</td>
                    @endforeach
                    <td class="text-right">{{ formatMinus($selisihPenerimaanPembayaranAll) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>