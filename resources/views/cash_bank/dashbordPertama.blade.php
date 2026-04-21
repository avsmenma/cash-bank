<style>
    /* ============================================================
       PROFESSIONAL DASHBOARD TABLE — Refined Corporate Palette
       ============================================================ */
    #cashflow-table {
        table-layout: auto !important;
        font-size: 11.5px;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .06), 0 1px 2px rgba(16, 24, 40, .04);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    #cashflow-table thead th {
        background: #1e3a5f !important;
        color: #f0f4f8 !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
        font-size: 10.5px;
        border-bottom: 2px solid #0f2137;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    #cashflow-table th,
    #cashflow-table td {
        white-space: nowrap;
        vertical-align: middle;
        padding: 7px 10px;
        border-color: #e4e7ec;
    }
    #cashflow-table tbody tr {
        transition: background-color .15s ease;
    }
    #cashflow-table tbody tr:hover {
        filter: brightness(.97);
    }

    /* ---------- SECTION HEADERS ---------- */
    .sec-penerimaan {
        background: #0d9488 !important;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 11.5px;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 2px solid #0b7c73;
    }
    .sec-dropping {
        background: #1e40af !important;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 11.5px;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 2px solid #1a369a;
    }
    .sec-pembayaran {
        background: #9333ea !important;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 11.5px;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 2px solid #7e22ce;
    }

    /* ---------- ROW TYPES ---------- */
    .row-kat-header td {
        background-color: #f0f4f8 !important;
        font-weight: 700;
        color: #1e3a5f !important;
        border-left: 3px solid #3b82f6;
        font-size: 11px;
    }
    .row-item td {
        background-color: #ffffff !important;
        color: #344054 !important;
    }
    .row-item:hover td {
        background-color: #f8fafc !important;
    }
    .row-sub-total td {
        background-color: #eef2f7 !important;
        font-weight: 600;
        color: #1e3a5f !important;
        border-top: 1px solid #cbd5e1;
    }
    .row-gaji-group td {
        background-color: #f0f4f8 !important;
        font-weight: 700;
        color: #1e3a5f !important;
    }

    /* ---------- TOTAL ROWS (dark bg → white text) ---------- */
    .row-total-penerimaan td,
    .row-total-penerimaan td strong {
        background: #0d9488 !important;
        color: #ffffff !important;
        font-weight: 700;
        text-align: right;
        border-top: 2px solid #0b7c73;
    }
    .row-total-dropping td,
    .row-total-dropping td strong {
        background: #1e40af !important;
        color: #ffffff !important;
        font-weight: 700;
        border-top: 2px solid #1a369a;
    }
    .row-total-pembayaran td,
    .row-total-pembayaran td strong {
        background: #9333ea !important;
        color: #ffffff !important;
        font-weight: 700;
        border-top: 2px solid #7e22ce;
    }

    /* ---------- SELISIH / DIFF ROWS (light bg → dark text) ---------- */
    .row-selisih-pdn-drop td,
    .row-selisih-pdn-drop td strong {
        background-color: #fef3c7 !important;
        font-weight: 600;
        color: #1a1a1a !important;
        border-left: 3px solid #f59e0b;
    }
    .row-selisih-pay-pdn td,
    .row-selisih-pay-pdn td strong {
        background-color: #fef3c7 !important;
        font-weight: 600;
        color: #1a1a1a !important;
        border-left: 3px solid #f59e0b;
    }
    .row-selisih-pay-drop td,
    .row-selisih-pay-drop td strong {
        background-color: #fef3c7 !important;
        font-weight: 600;
        color: #1a1a1a !important;
        border-left: 3px solid #f59e0b;
    }
    /* Negative values in selisih rows stay red for emphasis */
    .row-selisih-pdn-drop td.neg,
    .row-selisih-pay-pdn td.neg,
    .row-selisih-pay-drop td.neg {
        color: #dc2626 !important;
    }

    /* ---------- SUMMARY ROWS (light bg → dark text) ---------- */
    .row-summary-cpo td,
    .row-summary-cpo td strong,
    .row-summary-dtbs td,
    .row-summary-dtbs td strong,
    .row-summary-ptbs td,
    .row-summary-ptbs td strong {
        background-color: #f1f5f9 !important;
        font-weight: 600;
        color: #1a1a1a !important;
        border-top: 1px solid #cbd5e1;
    }

    /* ---------- VALUE & TOTAL COLUMN ---------- */
    .val { text-align: right; }
    .neg { color: #dc2626 !important; font-weight: 600; }

    .kolom-total {
        background: #1e3a5f !important;
        color: #f0f4f8 !important;
    }

    /* ---------- SPACER ROWS ---------- */
    #cashflow-table tr td[colspan] {
        border-left: none;
        border-right: none;
    }
</style>


@php
    /**
     * Helper: format angka, nilai negatif tampil (xxx)
     */
    if (!function_exists('fmtDash')) {
        function fmtDash($v) {
            if ($v == 0) return '-';
            return $v < 0
                ? '(' . number_format(abs($v), 0, ',', '.') . ')'
                : number_format($v, 0, ',', '.');
        }
    }
    if (!function_exists('fmtBold')) {
        function fmtBold($v) {
            return $v < 0
                ? '(' . number_format(abs($v), 0, ',', '.') . ')'
                : number_format($v, 0, ',', '.');
        }
    }

    // ================================================================
    // Urutan kategori Penerimaan
    // ================================================================
    $pnOrder = [
        'Penjualan CPO'                   => 1,
        'Penjualan Kernel'                => 2,
        'Hapor'                           => 3,
        'Penjualan TBS'                   => 4,
        'Penjualan Cangkang'              => 5,
        'Penjualan Karet'                 => 6,
        'KSO (Titip Olah/Revenue Sharing)'=> 7,
        'KSU Batubara'                    => 8,
        'Lainnya'                         => 9,
    ];
    uksort($result['penerima'], function($a,$b) use($pnOrder){
        $pA = $pnOrder[$a] ?? 99; $pB = $pnOrder[$b] ?? 99;
        return $pA <=> $pB;
    });

    // ================================================================
    // Aggregate Dropping & Pembayaran ke level [kategori][sub_kriteria][bulan]
    // ================================================================
    $dropAgg = [];
    foreach ($result['dropping'] as $item) {
        $kat = $item['kategori']; $sub = $item['sub_kriteria'];
        foreach ($bulanListFiltered as $b => $n) {
            $dropAgg[$kat][$sub][$b] = ($dropAgg[$kat][$sub][$b] ?? 0) + ($item['data'][$b] ?? 0);
        }
    }

    $payAgg = [];
    foreach ($result['pembayaran'] as $item) {
        $kat = $item['kategori']; $sub = $item['sub_kriteria'];
        foreach ($bulanListFiltered as $b => $n) {
            $payAgg[$kat][$sub][$b] = ($payAgg[$kat][$sub][$b] ?? 0) + ($item['data'][$b] ?? 0);
        }
    }

    // ================================================================
    // Urutan kategori untuk Dropping & Pembayaran
    // ================================================================
    $katOrder = [
        'Kebutuhan Gaji, Upah dan Tunjangan'           => 1,
        'Kebutuhan Pembayaran Aktivitas Ekploitasi'     => 2,
        'Kebutuhan Pekerjaan Aktivitas Investasi'       => 3,
    ];
    // Kategori yang DIGRUP (ditampilkan sebagai 1 baris total saja)
    $groupedKat = ['Kebutuhan Gaji, Upah dan Tunjangan'];

    // Urutan sub_kriteria dalam tiap kategori
    $subOrder = [
        'Pembelian TBS'              => 1,
        'Operasional Produksi'       => 2,
        'Biaya Usaha dan lainnya'    => 3,
        'Pajak'                      => 4,
        'Investasi On Farm'          => 1,
        'Investasi Off Farm'         => 2,
        'Pembayaran investasi lainnya'=> 3,
    ];

    // Sort kategori
    foreach ([&$dropAgg, &$payAgg] as &$agg) {
        uksort($agg, function($a,$b) use($katOrder){
            return ($katOrder[$a] ?? 99) <=> ($katOrder[$b] ?? 99);
        });
        foreach ($agg as $kat => &$subs) {
            uksort($subs, function($a,$b) use($subOrder){
                $pA = $subOrder[$a] ?? 99; $pB = $subOrder[$b] ?? 99;
                if ($pA !== $pB) return $pA <=> $pB;
                return strcmp($a,$b);
            });
        }
    }
    unset($agg, $subs);

    // ================================================================
    // Helper: hitung total per bulan untuk satu kategori
    // ================================================================
    $sumKat = function($agg, $kat, $bulanListFiltered) {
        $res = [];
        foreach ($bulanListFiltered as $b => $n) { $res[$b] = 0; }
        foreach ($agg[$kat] ?? [] as $sub => $bData) {
            foreach ($bulanListFiltered as $b => $n) { $res[$b] += ($bData[$b] ?? 0); }
        }
        return $res;
    };
    $sumSub = function($agg, $kat, $sub, $bulanListFiltered) {
        $res = [];
        foreach ($bulanListFiltered as $b => $n) { $res[$b] = ($agg[$kat][$sub][$b] ?? 0); }
        return $res;
    };

    // ================================================================
    // GRAND TOTALS
    // ================================================================
    $totalPenerima  = []; foreach ($bulanListFiltered as $b => $n) $totalPenerima[$b] = 0;
    foreach ($result['penerima'] as $kat => $bData) {
        foreach ($bulanListFiltered as $b => $n) $totalPenerima[$b] += ($bData[$b] ?? 0);
    }
    $totalDropAll   = []; foreach ($bulanListFiltered as $b => $n) $totalDropAll[$b] = 0;
    foreach ($dropAgg as $kat => $subs) {
        foreach ($subs as $sub => $bData) {
            foreach ($bulanListFiltered as $b => $n) $totalDropAll[$b] += ($bData[$b] ?? 0);
        }
    }
    $totalPayAll    = []; foreach ($bulanListFiltered as $b => $n) $totalPayAll[$b] = 0;
    foreach ($payAgg as $kat => $subs) {
        foreach ($subs as $sub => $bData) {
            foreach ($bulanListFiltered as $b => $n) $totalPayAll[$b] += ($bData[$b] ?? 0);
        }
    }

    // Summary: CPO + Kernel dari penerima
    $cpnKernel = []; foreach ($bulanListFiltered as $b => $n) $cpnKernel[$b] = 0;
    foreach (['Penjualan CPO','Penjualan Kernel'] as $cpk) {
        foreach ($bulanListFiltered as $b => $n) {
            $cpnKernel[$b] += ($result['penerima'][$cpk][$b] ?? 0);
        }
    }
    // Summary: Dropping TBS & Pembayaran TBS
    $dropTBS = []; $payTBS = [];
    foreach ($bulanListFiltered as $b => $n) { $dropTBS[$b] = 0; $payTBS[$b] = 0; }
    foreach ($dropAgg as $kat => $subs) {
        foreach ($subs as $sub => $bData) {
            if (stripos($sub,'TBS') !== false || $sub === 'Pembelian TBS') {
                foreach ($bulanListFiltered as $b => $n) $dropTBS[$b] += ($bData[$b] ?? 0);
            }
        }
    }
    foreach ($payAgg as $kat => $subs) {
        foreach ($subs as $sub => $bData) {
            if (stripos($sub,'TBS') !== false || $sub === 'Pembelian TBS') {
                foreach ($bulanListFiltered as $b => $n) $payTBS[$b] += ($bData[$b] ?? 0);
            }
        }
    }

    $nCol = count($bulanListFiltered) + 2; // uraian + bulan + total
@endphp

<div class="row">
<div class="col-12 table-responsive">
<table class="table table-bordered table-sm" id="cashflow-table">
    <thead class="text-center">
        <tr>
            <th rowspan="2" style="min-width:260px;vertical-align:middle;">URAIAN</th>
            <th colspan="{{ count($bulanListFiltered) }}">TAHUN {{ $tahun }}</th>
            <th rowspan="2" class="kolom-total" style="min-width:120px;vertical-align:middle;">Sd {{ collect($bulanListFiltered)->last() }} Tahun {{ $tahun }}</th>
        </tr>
        <tr>
            @foreach($bulanListFiltered as $noBulan => $namaBulan)
                <th style="min-width:120px;">{{ $namaBulan }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>

        {{-- ================================================================
             PENERIMAAN
             ================================================================ --}}
        <tr>
            <td colspan="{{ $nCol }}" class="sec-penerimaan">PENERIMAAN</td>
        </tr>

        @php $totalPnAll = 0; @endphp
        @foreach($result['penerima'] as $kat => $bData)
            @php $rowTotal = 0; @endphp
            <tr class="row-item">
                <td>- {{ $kat }}</td>
                @foreach($bulanListFiltered as $b => $nm)
                    @php $v = $bData[$b] ?? 0; $rowTotal += $v; @endphp
                    <td class="val">{{ fmtDash($v) }}</td>
                @endforeach
                @php $totalPnAll += $rowTotal; @endphp
                <td class="val font-weight-bold">{{ fmtBold($rowTotal) }}</td>
            </tr>
        @endforeach

        {{-- TOTAL PENERIMAAN --}}
        <tr class="row-total-penerimaan">
            <td class="text-right"><strong>TOTAL PENERIMAAN</strong></td>
            @php $gTP = 0; @endphp
            @foreach($bulanListFiltered as $b => $nm)
                <td class="val">{{ fmtBold($totalPenerima[$b]) }}</td>
                @php $gTP += $totalPenerima[$b]; @endphp
            @endforeach
            <td class="val">{{ fmtBold($gTP) }}</td>
        </tr>

        <tr><td colspan="{{ $nCol }}" style="background:#f5f5f5;height:6px;"></td></tr>

        {{-- ================================================================
             DROPPING HO
             ================================================================ --}}
        <tr>
            <td colspan="{{ $nCol }}" class="sec-dropping">DROPPING HO</td>
        </tr>

        @foreach($dropAgg as $kat => $subs)
            @php $katTotPerBulan = $sumKat($dropAgg, $kat, $bulanListFiltered); @endphp

            @if(in_array($kat, $groupedKat))
                {{-- KATEGORI DIGRUP: Tampilkan 1 baris total saja --}}
                @php $gKat = 0; @endphp
                <tr class="row-gaji-group">
                    <td><strong>{{ $kat }}</strong></td>
                    @foreach($bulanListFiltered as $b => $nm)
                        <td class="val">{{ fmtBold($katTotPerBulan[$b]) }}</td>
                        @php $gKat += $katTotPerBulan[$b]; @endphp
                    @endforeach
                    <td class="val">{{ fmtBold($gKat) }}</td>
                </tr>
            @else
                {{-- KATEGORI DITAMPILKAN DETAIL --}}
                <tr class="row-kat-header">
                    <td colspan="{{ $nCol }}">{{ $kat }}</td>
                </tr>
                @php $subTotAll = 0; $subTotPerBulan = []; foreach($bulanListFiltered as $b=>$n) $subTotPerBulan[$b]=0; @endphp
                @foreach($subs as $sub => $bData)
                    @php $rowTot = 0; @endphp
                    <tr class="row-item">
                        <td>- {{ $sub }}</td>
                        @foreach($bulanListFiltered as $b => $nm)
                            @php $v = $bData[$b] ?? 0; $rowTot += $v; $subTotPerBulan[$b] += $v; @endphp
                            <td class="val">{{ fmtDash($v) }}</td>
                        @endforeach
                        @php $subTotAll += $rowTot; @endphp
                        <td class="val font-weight-bold">{{ fmtBold($rowTot) }}</td>
                    </tr>
                @endforeach
                <tr class="row-sub-total">
                    <td>Jumlah {{ $kat }}</td>
                    @foreach($bulanListFiltered as $b => $nm)
                        <td class="val">{{ fmtBold($subTotPerBulan[$b]) }}</td>
                    @endforeach
                    <td class="val">{{ fmtBold($subTotAll) }}</td>
                </tr>
            @endif
        @endforeach

        {{-- TOTAL DROPPING HO --}}
        <tr class="row-total-dropping">
            <td class="text-right"><strong>TOTAL DROPPING HO</strong></td>
            @php $gTD = 0; @endphp
            @foreach($bulanListFiltered as $b => $nm)
                <td class="val">{{ fmtBold($totalDropAll[$b]) }}</td>
                @php $gTD += $totalDropAll[$b]; @endphp
            @endforeach
            <td class="val">{{ fmtBold($gTD) }}</td>
        </tr>

        {{-- SELISIH PENERIMAAN TERHADAP DROPPING --}}
        <tr class="row-selisih-pdn-drop">
            <td><strong>SELISIH PENERIMAAN TERHADAP DROPPING</strong></td>
            @php $gSPD = 0; @endphp
            @foreach($bulanListFiltered as $b => $nm)
                @php $sel = $totalPenerima[$b] - $totalDropAll[$b]; $gSPD += $sel; @endphp
                <td class="val {{ $sel < 0 ? 'neg' : '' }}">{{ fmtDash($sel) }}</td>
            @endforeach
            <td class="val {{ $gSPD < 0 ? 'neg' : '' }}">{{ fmtDash($gSPD) }}</td>
        </tr>

        <tr><td colspan="{{ $nCol }}" style="background:#f5f5f5;height:6px;"></td></tr>

        {{-- ================================================================
             PEMBAYARAN
             ================================================================ --}}
        <tr>
            <td colspan="{{ $nCol }}" class="sec-pembayaran">PEMBAYARAN</td>
        </tr>

        @foreach($payAgg as $kat => $subs)
            @php $katTotPerBulanP = $sumKat($payAgg, $kat, $bulanListFiltered); @endphp

            @if(in_array($kat, $groupedKat))
                @php $gKatP = 0; @endphp
                <tr class="row-gaji-group">
                    <td><strong>{{ $kat }}</strong></td>
                    @foreach($bulanListFiltered as $b => $nm)
                        <td class="val">{{ fmtBold($katTotPerBulanP[$b]) }}</td>
                        @php $gKatP += $katTotPerBulanP[$b]; @endphp
                    @endforeach
                    <td class="val">{{ fmtBold($gKatP) }}</td>
                </tr>
            @else
                <tr class="row-kat-header">
                    <td colspan="{{ $nCol }}">{{ $kat }}</td>
                </tr>
                @php $subTotAllP = 0; $subTotPerBulanP = []; foreach($bulanListFiltered as $b=>$n) $subTotPerBulanP[$b]=0; @endphp
                @foreach($subs as $sub => $bData)
                    @php $rowTotP = 0; @endphp
                    <tr class="row-item">
                        <td>- {{ $sub }}</td>
                        @foreach($bulanListFiltered as $b => $nm)
                            @php $v = $bData[$b] ?? 0; $rowTotP += $v; $subTotPerBulanP[$b] += $v; @endphp
                            <td class="val">{{ fmtDash($v) }}</td>
                        @endforeach
                        @php $subTotAllP += $rowTotP; @endphp
                        <td class="val font-weight-bold">{{ fmtBold($rowTotP) }}</td>
                    </tr>
                @endforeach
                <tr class="row-sub-total">
                    <td>Jumlah {{ $kat }}</td>
                    @foreach($bulanListFiltered as $b => $nm)
                        <td class="val">{{ fmtBold($subTotPerBulanP[$b]) }}</td>
                    @endforeach
                    <td class="val">{{ fmtBold($subTotAllP) }}</td>
                </tr>
            @endif
        @endforeach

        {{-- TOTAL PEMBAYARAN --}}
        <tr class="row-total-pembayaran">
            <td class="text-right"><strong>TOTAL PEMBAYARAN</strong></td>
            @php $gTP2 = 0; @endphp
            @foreach($bulanListFiltered as $b => $nm)
                <td class="val">{{ fmtBold($totalPayAll[$b]) }}</td>
                @php $gTP2 += $totalPayAll[$b]; @endphp
            @endforeach
            <td class="val">{{ fmtBold($gTP2) }}</td>
        </tr>

        {{-- SELISIH PEMBAYARAN TERHADAP PENERIMAAN --}}
        <tr class="row-selisih-pay-pdn">
            <td><strong>SELISIH PEMBAYARAN TERHADAP PENERIMAAN</strong></td>
            @php $gSeiPP = 0; @endphp
            @foreach($bulanListFiltered as $b => $nm)
                @php $s2 = $totalPenerima[$b] - $totalPayAll[$b]; $gSeiPP += $s2; @endphp
                <td class="val {{ $s2 < 0 ? 'neg' : '' }}">{{ fmtDash($s2) }}</td>
            @endforeach
            <td class="val {{ $gSeiPP < 0 ? 'neg' : '' }}">{{ fmtDash($gSeiPP) }}</td>
        </tr>

        {{-- SELISIH PEMBAYARAN TERHADAP DROPPING --}}
        <tr class="row-selisih-pay-drop">
            <td><strong>SELISIH PEMBAYARAN TERHADAP DROPPING</strong></td>
            @php $gSeiDP = 0; @endphp
            @foreach($bulanListFiltered as $b => $nm)
                @php $s3 = $totalDropAll[$b] - $totalPayAll[$b]; $gSeiDP += $s3; @endphp
                <td class="val {{ $s3 < 0 ? 'neg' : '' }}">{{ fmtDash($s3) }}</td>
            @endforeach
            <td class="val {{ $gSeiDP < 0 ? 'neg' : '' }}">{{ fmtDash($gSeiDP) }}</td>
        </tr>

        <tr><td colspan="{{ $nCol }}" style="height:6px;"></td></tr>

        {{-- ================================================================
             BARIS SUMMARY KUNING
             ================================================================ --}}
        <tr class="row-summary-cpo">
            <td><strong>PENERIMAAN PENJUALAN CPO &amp; KERNEL</strong></td>
            @php $gCPK = 0; @endphp
            @foreach($bulanListFiltered as $b => $nm)
                <td class="val">{{ fmtBold($cpnKernel[$b]) }}</td>
                @php $gCPK += $cpnKernel[$b]; @endphp
            @endforeach
            <td class="val">{{ fmtBold($gCPK) }}</td>
        </tr>

        <tr class="row-summary-dtbs">
            <td><strong>DROPPING TBS</strong></td>
            @php $gDTBS = 0; @endphp
            @foreach($bulanListFiltered as $b => $nm)
                <td class="val">{{ fmtBold($dropTBS[$b]) }}</td>
                @php $gDTBS += $dropTBS[$b]; @endphp
            @endforeach
            <td class="val">{{ fmtBold($gDTBS) }}</td>
        </tr>

        <tr class="row-summary-ptbs">
            <td><strong>PEMBAYARAN TBS</strong></td>
            @php $gPTBS = 0; @endphp
            @foreach($bulanListFiltered as $b => $nm)
                <td class="val">{{ fmtBold($payTBS[$b]) }}</td>
                @php $gPTBS += $payTBS[$b]; @endphp
            @endforeach
            <td class="val">{{ fmtBold($gPTBS) }}</td>
        </tr>

    </tbody>
</table>
</div>
</div>