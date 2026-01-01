<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
        <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

        <link rel="stylesheet"  href="{{ asset('css/style.css') }}" class="css">
       <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
        <script src="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.js"></script>

        <title>Document</title>

        <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css"> -->
</head>
<body>
     {{-- INFO FILTER AKTIF --}}
                    @php
                        $filterInfo = [];
                        if(request('tahun')) $filterInfo[] = 'Tahun: '.request('tahun');
                       
                        if (request('bulan')) {
                            $bulan = (int) request('bulan');
                            if ($bulan >= 1 && $bulan <= 12) {
                                $filterInfo[] = 'Bulan: ' . \Carbon\Carbon::create()->month($bulan)->format('F');
                            }
                        }
                        if(request('tanggal') && count(request('tanggal')) > 0) $filterInfo[] = count(request('tanggal')).' Tanggal';
                        if(request('bank_tujuan')) {
                            $bankName = $bankTujuanList->where('id_bank_tujuan', request('bank_tujuan'))->first()->nama_tujuan ?? '';
                            $filterInfo[] = 'Bank: '.$bankName;
                        }
                        if(request('sumber_dana') && count(request('sumber_dana')) > 0) {
                            $sdNames = $sumberDanaList->whereIn('id_sumber_dana', request('sumber_dana'))->pluck('nama_sumber_dana')->take(2)->toArray();
                            $filterInfo[] = 'SD: '.implode(', ', $sdNames).(count(request('sumber_dana')) > 2 ? ' +lainnya' : '');
                        }
                        if(request('kategori') && count(request('kategori')) > 0) {
                            $filterInfo[] = count(request('kategori')).' Kategori';
                        }
                        if(request('id_jenis_pembayaran')) {
                            $jpName = $jenisPembayaranList->where('id_jenis_pembayaran', request('id_jenis_pembayaran'))->first()->nama_jenis_pembayaran ?? '';
                            $filterInfo[] = 'JP: '.$jpName;
                        }
                    @endphp

                    {{-- REKAP SALDO BANK --}}
                    @if(request('rekapanVA') === 'bank')
                        <div class="alert alert-info mt-3">
                            <strong>Rekapan Saldo Virtual Account (VA)</strong><br>
                            <small>
                                @if(count($filterInfo) > 0)
                                    Filter: {{ implode(' | ', $filterInfo) }}
                                @else
                                    Menampilkan semua data
                                @endif
                            </small>
                        </div>

                        <table class="table table-bordered table-striped mt-2">
                            <thead class="text-center table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Akun VA</th>
                                    <!-- @if($showDebet)
                                        <th>Debet</th>
                                        <th>Kredit</th>
                                    @else
                                        <th>Kredit</th>
                                    @endif -->
                                    <th>Saldo VA</th>
                                    @if($showSAP)
                                        <th>Saldo SAP</th>
                                        <th>Selisih</th>
                                    @endif
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapVA as $i => $row)
                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td>{{ $row['bank'] ?? '-' }}</td>
                                        <!-- @if($showDebet)
                                            <td class="text-end">@currency($row['debet'] ?? 0)</td>
                                        @endif -->
                                        <!-- <td class="text-end">@currency($row['kredit'] ?? 0)</td> -->
                                        <td class="text-end fw-bold">@currency($row['saldo_va'] ?? 0)</td>
                                        @if($showSAP)
                                            <td class="text-end">@currency($row['saldo_sap'] ?? 0)</td>
                                            <td class="text-end fw-bold">@currency($row['selisih'] ?? 0)</td>
                                        @endif
                                        <td>{{ $row['keterangan'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $showDebet && $showSAP ? 8 : ($showDebet ? 6 : ($showSAP ? 6 : 4)) }}" class="text-center text-muted py-3">
                                            Tidak ada data dengan filter yang dipilih
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($rekapVA) > 0)
                                <tfoot class="table-secondary">
                                    <tr>
                                        <!-- <th colspan="{{ $showDebet ? 3 : 2 }}" class="text-end">TOTAL:</th> -->
                                        <th colspan="2" class="text-end">TOTAL:</th>
                                        <th class="text-end">@currency(collect($rekapVA)->sum('saldo_va'))</th>
                                        <th colspan="{{ $showSAP ? 3 : 1 }}"></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @endif

                    {{-- REKAP SALDO VA --}}
                    @if(request('rekapanVA') === 'va')
                        <div class="alert alert-info mt-3">
                            <strong>📊 Rekapan Saldo Bank</strong><br>
                            <small>
                                @if(count($filterInfo) > 0)
                                    Filter: {{ implode(' | ', $filterInfo) }}
                                @else
                                    Menampilkan semua data
                                @endif
                            </small>
                        </div>

                        <table class="table table-bordered table-striped mt-2">
                            <thead class="text-center table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Akun Bank</th>
                                    <!-- @if($showDebet)
                                        <th>Debet</th>
                                        <th>Kredit</th>
                                    @else -->
                                        <!-- <th>Kredit</th>
                                    @endif -->
                                    <th>Saldo Bank</th>
                                    <!-- @if($showSAP)
                                        <th>Saldo SAP</th>
                                        <th>Selisih</th>
                                    @endif -->
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapVA as $i => $row)
                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td>{{ $row['bank'] ?? '-' }}</td>
                                        <!-- @if($showDebet)
                                            <td class="text-end">@currency($row['debet'] ?? 0)</td>
                                        @endif -->
                                        <!-- <td class="text-end">@currency($row['kredit'] ?? 0)</td> -->
                                        <td class="text-end fw-bold">@currency($row['saldo_va'] ?? 0)</td>
                                        <!-- @if($showSAP) -->
                                            <!-- <td class="text-end">@currency($row['saldo_sap'] ?? 0)</td> -->
                                            <!-- <td class="text-end">@currency($row['selisih'] ?? 0)</td> -->
                                        <!-- @endif -->
                                        <td>{{ $row['keterangan'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-3">
                                        <!-- <td colspan="{{ $showDebet && $showSAP ? 8 : ($showDebet ? 6 : ($showSAP ? 6 : 4)) }}" class="text-center text-muted py-3"> -->
                                            Tidak ada data dengan filter yang dipilih
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($rekapVA) > 0)
                                <tfoot class="table-secondary">
                                    <tr>
                                        <!-- <th colspan="{{ $showDebet ? 3 : 2 }}" class="text-end">TOTAL:</th> -->
                                        <th colspan="2" class="text-end">TOTAL:</th>
                                        <th class="text-end">@currency(collect($rekapVA)->sum('saldo_va'))</th>
                                        <th colspan="{{ $showSAP ? 3 : 1 }}"></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @endif

                    {{-- REKAP KATEGORI FULL --}}
                    @if(request('rekapanVA') === 'kategori-full')
                        <div class="alert alert-info mt-3">
                            <strong>Rekapan Kategori Full</strong><br>
                            <small>
                                @if(count($filterInfo) > 0)
                                    Filter: {{ implode(' | ', $filterInfo) }}
                                @else
                                    Menampilkan semua data
                                @endif
                            </small>
                        </div>

                        <table class="table table-bordered table-striped mt-2" >
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>Kategori</th>
                                    <th>Sub Kriteria</th>
                                    <th>Item</th>
                                    <th>Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapVA as $kategori => $subs)
                                    @php
                                        $rowspanKategori = 0;
                                        $totalKategori = 0;
                                        
                                        foreach ($subs as $sub => $items) {
                                            $rowspanKategori += count($items) + 1;
                                            foreach ($items as $item) {
                                                if (isset($item['kredit'])) {
                                                    $totalKategori += floatval($item['kredit']);
                                                }
                                            }
                                        }
                                        $rowspanKategori += 1;
                                        $firstKategori = false;
                                    @endphp

                                    @foreach($subs as $sub => $items)
                                        @php
                                            $rowspanSub = count($items) + 1;
                                            $totalSub = array_sum(array_column($items, 'kredit'));
                                            $firstSub = false;
                                        @endphp

                                        @foreach($items as $item)
                                            <tr>
                                                @if(!$firstKategori)
                                                    <td rowspan="{{ $rowspanKategori }}" class="kolom-kategori">{{ $kategori }}</td>
                                                    @php $firstKategori = true; @endphp
                                                @endif

                                                @if(!$firstSub)
                                                    <td rowspan="{{ $rowspanSub }}" class="kolom-kategori">{{ $sub }}</td>
                                                    @php $firstSub = true; @endphp
                                                @endif
                                                <td >{{ $item['item'] ?? '-' }}</td>
                                                 <td class="text-end">
                                                    <a href="{{ route('detail-item.index', [
                                                        'kategori' => $kategori,
                                                        'sub' => $sub,
                                                        'item' => $item['item'] ?? '-',
                                                        'tahun' => request('tahun'),
                                                        'bulan' => request('bulan'),
                                                        'tanggal' => request('tanggal'),
                                                        'bank_tujuan' => request('bank_tujuan'),
                                                        'sumber_dana' => request('sumber_dana'),
                                                        'id_jenis_pembayaran' => request('id_jenis_pembayaran'),
                                                        
                                                    ]) }}" 
                                                    class="text-decoration-none text-dark ">
                                                        @currency($item['kredit'] ?? 0)
                                                    </a>
                                                </td>
                                                
                                                <!-- <td class="text-end ">@currency($item['kredit'] ?? 0)</td> -->
                                            </tr>
                                        @endforeach

                                        <tr class="table-light">
                                            <td class="text-end fw-bold">TOTAL {{ $sub }}</td>
                                            <td class="text-end">
                                                    <a href="{{ route('detail-sub.index', [
                                                        'kategori' => $kategori,
                                                        'sub' => $sub,
                                                        'tahun' => request('tahun'),
                                                        'bulan' => request('bulan'),
                                                        'tanggal' => request('tanggal'),
                                                        'bank_tujuan' => request('bank_tujuan'),
                                                        'sumber_dana' => request('sumber_dana'),
                                                        'id_jenis_pembayaran' => request('id_jenis_pembayaran'),
                                                        
                                                    ]) }}" 
                                                    class="text-decoration-none text-end fw-bold">
                                                       @currency($totalSub)
                                                    </a>
                                                </td>
                                            <!-- <td class="text-end fw-bold">@currency($totalSub)</td> -->
                                        </tr>
                                    @endforeach

                                    <tr class="table-danger">
                                        <td class="text-end fw-bold" colspan="2">TOTAL {{ strtoupper($kategori) }}</td>
                                        <td class="text-end fw-bold">@currency($totalKategori)</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            Tidak ada data kategori dengan filter yang dipilih
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif

                    {{-- TABEL DATA NORMAL --}}
                    @if(!request('rekapanVA'))
                        <div class="alert alert-info mt-3">
                            <small>Filter: {{ implode(' | ', $filterInfo ?? ['Semua Data']) }}</small>
                        </div>

                        <table class="table table-bordered table-striped">
                            <thead class="text-center table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No Agenda</th>
                                    
                                    {{-- Kolom Bank Tujuan - tampil jika difilter --}}
                                    @if($bankTujuanId || request('bank_tujuan') === '-- Pilih Bank Tujuan --')
                                        <th>Bank Tujuan</th>
                                    @endif
                                    
                                    {{-- Kolom Sumber Dana - tampil jika difilter --}}
                                    @if($sumberDanaIds && count($sumberDanaIds) > 0)
                                        <th>Sumber Dana</th>
                                    @endif
                                    
                                    {{-- Kolom Kategori - tampil jika difilter --}}
                                    @if($kategoriIds && count($kategoriIds) > 0)
                                        <th>Kategori</th>
                                    @endif
                                    
                                    {{-- Kolom Jenis Pembayaran - tampil jika difilter --}}
                                    @if(request('id_jenis_pembayaran'))
                                        <th>Jenis Pembayaran</th>
                                    @endif
                                    
                                    <th>Penerima</th>
                                    <th>Uraian</th>
                                    
                                    {{-- Kolom Debet - tampil di mode 0-1 filter --}}
                                    @if($showDebet)
                                        <th>Debet</th>
                                    @endif
                                    
                                    <th>Kredit</th>
                                    
                                    {{-- Kolom Saldo Akhir - tampil di mode 0-1 filter --}}
                                    @if($showSaldoAkhir)
                                        <th>Saldo Akhir</th>
                                    @endif
                                    
                                    {{-- Kolom No SAP --}}
                                    @if($showSAP)
                                        <th>No SAP</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $index => $row)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                        <td>{{ $row->agenda_tahun ?? '-' }}</td>
                                        
                                        {{-- Data Bank Tujuan --}}
                                        @if($bankTujuanId)
                                            <td>{{ $row->nama_tujuan ?? '-' }}</td>
                                        @endif
                                        
                                        {{-- Data Sumber Dana --}}
                                        @if($sumberDanaIds && count($sumberDanaIds) > 0)
                                            <td>{{ $row->nama_sumber_dana ?? '-' }}</td>
                                        @endif
                                        
                                        {{-- Data Kategori --}}
                                        @if($kategoriIds && count($kategoriIds) > 0)
                                            <td>{{ $row->nama_kriteria ?? '-' }}</td>
                                        @endif
                                        
                                        {{-- Data Jenis Pembayaran --}}
                                        @if(request('id_jenis_pembayaran'))
                                            <td>{{ $row->nama_jenis_pembayaran ?? '-' }}</td>
                                        @endif
                                        
                                        <td>{{ $row->penerima ?? '-' }}</td>
                                        <!-- <td>{{ $row->nama_tujuan ?? '-' }}</td>
                                        <td>{{ $row->nama_sumber_dana ?? '-' }}</td>
                                        <td>{{ $row->nama_kriteria ?? '-' }}</td> -->
                                        <td>{{ $row->uraian }}</td>
                                        
                                        @if($showDebet)
                                            <td class="text-end">@currency($row->debet)</td>
                                        @endif
                                        
                                        <td class="text-end">@currency($row->kredit)</td>
                                        
                                        @if($showSaldoAkhir)
                                            <td class="text-end">@currency($row->saldo_akhir)</td>
                                        @endif
                                        
                                        @if($showSAP)
                                            <td class="text-center">{{ $row->no_sap ?? '-' }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="20" class="text-center text-muted py-3">
                                            Tidak ada data dengan filter yang dipilih
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            
                            {{-- FOOTER: Total --}}
                            @if($data->count() > 0)
                                <tfoot class="table-secondary">
                                    @php
                                        // Hitung jumlah kolom dinamis
                                        $baseColumns = 4; // No, Tanggal, Penerima, Uraian
                                        $dynamicColumns = 0;
                                        
                                        if($bankTujuanId) $dynamicColumns++;
                                        if($sumberDanaIds && count($sumberDanaIds) > 0) $dynamicColumns++;
                                        if($kategoriIds && count($kategoriIds) > 0) $dynamicColumns++;
                                        if($idJenisPembayaran) $dynamicColumns++;
                                        if($showDebet) $dynamicColumns++;
                                        
                                        $totalColspan = $baseColumns + $dynamicColumns;
                                    @endphp
                                    
                                    {{-- Total Saldo Akhir (hanya untuk mode 0-1 filter) --}}
                                    @if($showSaldoAkhir)
                                        <tr>
                                            <th colspan="{{ $totalColspan }}" class="text-end">TOTAL :</th>
                                            <th class="text-end">@currency($data->sum('debet'))</th>
                                            <th class="text-end">@currency($data->sum('kredit'))</th>
                                            <th class="text-end">@currency($data->last()->saldo_akhir)</th>
                                            <!-- <th class="text-end">@currency($data->last()->saldo_akhir)</th> -->
                                            @if($showSAP)
                                                <th></th>
                                            @endif
                                        </tr>
                                    @endif
                                    
                                    <!-- {{-- Total Kredit --}}
                                    <tr>
                                        <th colspan="{{ $totalColspan }}" class="text-end">TOTAL KREDIT:</th>
                                        <th class="text-end">@currency($data->sum('kredit'))</th>
                                        @if($showSaldoAkhir)
                                            <th></th>
                                        @endif
                                        @if($showSAP)
                                            <th></th>
                                        @endif
                                    </tr>
                                    
                                    {{-- Total Debet (hanya untuk mode 0-1 filter) --}}
                                    @if($showDebet)
                                        <tr>
                                            <th colspan="{{ $totalColspan }}" class="text-end">TOTAL DEBET:</th>
                                            <th class="text-end">@currency($data->sum('debet'))</th>
                                            @if($showSaldoAkhir)
                                                <th></th>
                                            @endif
                                            @if($showSAP)
                                                <th></th>
                                            @endif
                                        </tr> -->
                                    <!-- @endif -->
                                </tfoot>
                            @endif
                        </table>
                    @endif
</body>
</html>