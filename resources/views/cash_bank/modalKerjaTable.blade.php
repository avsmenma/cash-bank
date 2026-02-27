@php
    if (!function_exists('fmtMK')) {
        function fmtMK($v) {
            if ($v == 0) return '-';
            return $v < 0
                ? '(' . number_format(abs($v), 0, ',', '.') . ')'
                : number_format($v, 0, ',', '.');
        }
    }
    if (!function_exists('fmtMKBold')) {
        function fmtMKBold($v) {
            if ($v == 0) return '0';
            return $v < 0
                ? '(' . number_format(abs($v), 0, ',', '.') . ')'
                : number_format($v, 0, ',', '.');
        }
    }
    $weekTemplate = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
@endphp

<style>
    #mk-table {
        font-size: 10px;
        white-space: nowrap;
        border-collapse: collapse;
    }
    #mk-table th, #mk-table td {
        padding: 3px 6px;
        border: 1px solid #aaa;
        vertical-align: middle;
    }
    #mk-table .th-uraian { min-width: 300px; max-width: 400px; white-space: normal; }
    #mk-table .col-num   { width: 28px; text-align: center; }

    /* Nilai rupiah: rata KANAN */
    #mk-table .td-val      { text-align: right; min-width: 85px; }
    #mk-table .td-val-bold { text-align: right; font-weight: bold; min-width: 85px; }

    /* Header section warna (referensi Excel) */
    .th-no-uraian  { background: #2F4F4F !important; color: #fff !important; }

    /* Permintaan: kuning */
    .th-permintaan, .th-permintaan-sub {
        background: #FFD700 !important; color: #000 !important;
    }
    /* Dropping: biru */
    .th-dropping, .th-dropping-sub {
        background: #4472C4 !important; color: #fff !important;
    }
    /* Pembayaran: hijau */
    .th-pembayaran, .th-pembayaran-sub {
        background: #70AD47 !important; color: #fff !important;
    }
    /* Saldo: oranye */
    .th-saldo { background: #ED7D31 !important; color: #fff !important; }

    /* Nomor kolom */
    .th-colnum { background: #D6DCE4 !important; color: #000; text-align: center; }

    /* Data sel warna per section */
    .bg-p  { background: #FFFACD; }           /* W1-W4 Permintaan */
    .bg-pt { background: #FFD700; font-weight: bold; } /* Total Permintaan */
    .bg-d  { background: #DDEEFF; }           /* W1-W4 Dropping */
    .bg-dt { background: #4472C4; color: #fff; font-weight: bold; }  /* Total Dropping */
    .bg-b  { background: #E2EFDA; }           /* W1-W4 Pembayaran */
    .bg-bt { background: #70AD47; color: #fff; font-weight: bold; }  /* Total Pembayaran */
    .bg-s     { background: #FCE4D6; font-weight: bold; }
    .bg-s-neg { background: #FCE4D6; font-weight: bold; color: #c00000; }

    /* Row jenis */
    .row-kategori td { background: #2F4F4F !important; color: #fff !important; font-weight: bold; }
    .row-sub td      { background: #D9E1F2 !important; font-weight: bold; }
    .row-item td     { background: #ffffff; }
    .row-subtotal td { background: #BDD7EE !important; font-weight: bold; }
    .row-kattotal td { background: #1F3864 !important; color: #fff !important; font-weight: bold; }
    .row-grandtotal td { background: #C00000 !important; color: #fff !important; font-weight: bold; }

    /* Sticky */
    .sticky-col  { position: sticky; left: 0;    z-index: 2; }
    .sticky-col2 { position: sticky; left: 28px; z-index: 2; }
    .table-scroll { overflow-x: auto; max-width: 100%; }
</style>

@if(empty($bulanAktif))
    <div class="alert alert-info m-3">Tidak ada data untuk filter yang dipilih.</div>
@else

<div class="table-scroll">
<table id="mk-table" class="table table-bordered table-sm">
    <thead>
        {{-- ROW 1 --}}
        <tr>
            <th rowspan="3" class="col-num sticky-col th-no-uraian text-center">No.</th>
            <th rowspan="3" class="th-uraian sticky-col2 th-no-uraian text-center" style="left:28px;">
                Payments for {{ $tahun }} transactions - Accounts
            </th>
            @foreach($bulanAktif as $bNo => $bNama)
                <th colspan="5" class="th-permintaan text-center">Permintaan Weekly-{{ $bNama }}</th>
                <th colspan="5" class="th-dropping text-center">Dropping Weekly-{{ $bNama }}</th>
                <th colspan="5" class="th-pembayaran text-center">Pembayaran Weekly-{{ $bNama }}</th>
                <th rowspan="3" class="th-saldo text-center" style="min-width:90px;">
                    SALDO MODAL KERJA<br>Per {{ $bNama }} {{ $tahun }}
                </th>
            @endforeach
        </tr>
        {{-- ROW 2 --}}
        <tr>
            @foreach($bulanAktif as $bNo => $bNama)
                <th class="th-permintaan-sub text-center">{{ $bNama }}-W1<br><small>(1-7)</small></th>
                <th class="th-permintaan-sub text-center">{{ $bNama }}-W2<br><small>(8-14)</small></th>
                <th class="th-permintaan-sub text-center">{{ $bNama }}-W3<br><small>(15-21)</small></th>
                <th class="th-permintaan-sub text-center">{{ $bNama }}-W4<br><small>(22-31)</small></th>
                <th class="th-permintaan-sub text-center">Weekly-{{ $bNama }}<br><small>(1-31)</small></th>
                <th class="th-dropping-sub text-center">{{ $bNama }}-W1<br><small>(1-7)</small></th>
                <th class="th-dropping-sub text-center">{{ $bNama }}-W2<br><small>(8-14)</small></th>
                <th class="th-dropping-sub text-center">{{ $bNama }}-W3<br><small>(15-21)</small></th>
                <th class="th-dropping-sub text-center">{{ $bNama }}-W4<br><small>(22-31)</small></th>
                <th class="th-dropping-sub text-center">Weekly-{{ $bNama }}<br><small>(1-31)</small></th>
                <th class="th-pembayaran-sub text-center">{{ $bNama }}-W1<br><small>(1-7)</small></th>
                <th class="th-pembayaran-sub text-center">{{ $bNama }}-W2<br><small>(8-14)</small></th>
                <th class="th-pembayaran-sub text-center">{{ $bNama }}-W3<br><small>(15-21)</small></th>
                <th class="th-pembayaran-sub text-center">{{ $bNama }}-W4<br><small>(22-31)</small></th>
                <th class="th-pembayaran-sub text-center">Weekly-{{ $bNama }}<br><small>(1-31)</small></th>
            @endforeach
        </tr>
        {{-- ROW 3: Nomor kolom --}}
        <tr>
            @php $colIdx = 1; @endphp
            @foreach($bulanAktif as $bNo => $bNama)
                @for($c = 0; $c < 15; $c++)
                    <th class="th-colnum">{{ $colIdx++ }}</th>
                @endfor
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php $rowNo = 1; @endphp

        @foreach($allKeys as $kat => $subs)
            @php
                $katTotalP = []; $katTotalD = []; $katTotalB = [];
                foreach($bulanAktif as $bNo => $bNama) {
                    $katTotalP[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                    $katTotalD[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                    $katTotalB[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                }
            @endphp

            {{-- BARIS KATEGORI --}}
            <tr class="row-kategori">
                <td class="col-num sticky-col text-center">{{ $rowNo++ }}</td>
                <td class="sticky-col2" style="left:28px;">{{ $kat }}</td>
                @foreach($bulanAktif as $bNo => $bNama)
                    @for($c=0;$c<16;$c++)<td></td>@endfor
                @endforeach
            </tr>

            @foreach($subs as $sub => $items)
                @php
                    $subTotalP = []; $subTotalD = []; $subTotalB = [];
                    foreach($bulanAktif as $bNo => $bNama) {
                        $subTotalP[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                        $subTotalD[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                        $subTotalB[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                        foreach($items as $item => $_x) {
                            $pV2 = $permintaanData[$bNo][$kat][$sub][$item] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                            $dV2 = $droppingData[$bNo][$kat][$sub][$item]   ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                            $bV2 = $pembayaranData[$bNo][$kat][$sub][$item] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                            foreach(['w1','w2','w3','w4'] as $w) {
                                $subTotalP[$bNo][$w] += $pV2[$w];
                                $subTotalD[$bNo][$w] += $dV2[$w];
                                $subTotalB[$bNo][$w] += $bV2[$w];
                                $katTotalP[$bNo][$w] += $pV2[$w];
                                $katTotalD[$bNo][$w] += $dV2[$w];
                                $katTotalB[$bNo][$w] += $bV2[$w];
                            }
                        }
                    }
                @endphp

                {{-- BARIS SUB KRITERIA --}}
                <tr class="row-sub">
                    <td class="col-num sticky-col text-center"></td>
                    <td class="sticky-col2" style="left:28px;padding-left:14px;">{{ $sub }}</td>
                    @foreach($bulanAktif as $bNo => $bNama)
                        @for($c=0;$c<16;$c++)<td></td>@endfor
                    @endforeach
                </tr>

                @foreach($items as $item => $_x)
                    <tr class="row-item">
                        <td class="col-num sticky-col text-center"></td>
                        <td class="sticky-col2" style="left:28px;padding-left:28px;">- {{ $item }}</td>
                        @foreach($bulanAktif as $bNo => $bNama)
                            @php
                                $pV = $permintaanData[$bNo][$kat][$sub][$item] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                                $dV = $droppingData[$bNo][$kat][$sub][$item]   ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                                $bV = $pembayaranData[$bNo][$kat][$sub][$item] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                                $pTot = array_sum($pV); $dTot = array_sum($dV); $bTot = array_sum($bV);
                                $saldo = $dTot - $bTot;
                            @endphp
                            <td class="td-val bg-p">{{ fmtMK($pV['w1']) }}</td>
                            <td class="td-val bg-p">{{ fmtMK($pV['w2']) }}</td>
                            <td class="td-val bg-p">{{ fmtMK($pV['w3']) }}</td>
                            <td class="td-val bg-p">{{ fmtMK($pV['w4']) }}</td>
                            <td class="td-val-bold bg-pt">{{ fmtMK($pTot) }}</td>
                            <td class="td-val bg-d">{{ fmtMK($dV['w1']) }}</td>
                            <td class="td-val bg-d">{{ fmtMK($dV['w2']) }}</td>
                            <td class="td-val bg-d">{{ fmtMK($dV['w3']) }}</td>
                            <td class="td-val bg-d">{{ fmtMK($dV['w4']) }}</td>
                            <td class="td-val-bold bg-dt">{{ fmtMK($dTot) }}</td>
                            <td class="td-val bg-b">{{ fmtMK($bV['w1']) }}</td>
                            <td class="td-val bg-b">{{ fmtMK($bV['w2']) }}</td>
                            <td class="td-val bg-b">{{ fmtMK($bV['w3']) }}</td>
                            <td class="td-val bg-b">{{ fmtMK($bV['w4']) }}</td>
                            <td class="td-val-bold bg-bt">{{ fmtMK($bTot) }}</td>
                            <td class="td-val-bold {{ $saldo < 0 ? 'bg-s-neg' : 'bg-s' }}">{{ fmtMK($saldo) }}</td>
                        @endforeach
                    </tr>
                @endforeach

                {{-- Sub Total --}}
                <tr class="row-subtotal">
                    <td class="col-num sticky-col text-center"></td>
                    <td class="sticky-col2" style="left:28px;padding-left:14px;"><strong>Sub Total {{ $sub }}</strong></td>
                    @foreach($bulanAktif as $bNo => $bNama)
                        @php
                            $spTot = array_sum($subTotalP[$bNo]);
                            $sdTot = array_sum($subTotalD[$bNo]);
                            $sbTot = array_sum($subTotalB[$bNo]);
                            $sSaldo = $sdTot - $sbTot;
                        @endphp
                        <td class="td-val bg-p">{{ fmtMKBold($subTotalP[$bNo]['w1']) }}</td>
                        <td class="td-val bg-p">{{ fmtMKBold($subTotalP[$bNo]['w2']) }}</td>
                        <td class="td-val bg-p">{{ fmtMKBold($subTotalP[$bNo]['w3']) }}</td>
                        <td class="td-val bg-p">{{ fmtMKBold($subTotalP[$bNo]['w4']) }}</td>
                        <td class="td-val-bold bg-pt">{{ fmtMKBold($spTot) }}</td>
                        <td class="td-val bg-d">{{ fmtMKBold($subTotalD[$bNo]['w1']) }}</td>
                        <td class="td-val bg-d">{{ fmtMKBold($subTotalD[$bNo]['w2']) }}</td>
                        <td class="td-val bg-d">{{ fmtMKBold($subTotalD[$bNo]['w3']) }}</td>
                        <td class="td-val bg-d">{{ fmtMKBold($subTotalD[$bNo]['w4']) }}</td>
                        <td class="td-val-bold bg-dt">{{ fmtMKBold($sdTot) }}</td>
                        <td class="td-val bg-b">{{ fmtMKBold($subTotalB[$bNo]['w1']) }}</td>
                        <td class="td-val bg-b">{{ fmtMKBold($subTotalB[$bNo]['w2']) }}</td>
                        <td class="td-val bg-b">{{ fmtMKBold($subTotalB[$bNo]['w3']) }}</td>
                        <td class="td-val bg-b">{{ fmtMKBold($subTotalB[$bNo]['w4']) }}</td>
                        <td class="td-val-bold bg-bt">{{ fmtMKBold($sbTot) }}</td>
                        <td class="td-val-bold {{ $sSaldo < 0 ? 'bg-s-neg' : 'bg-s' }}">{{ fmtMKBold($sSaldo) }}</td>
                    @endforeach
                </tr>
            @endforeach

            {{-- Total Kategori --}}
            <tr class="row-kattotal">
                <td class="col-num sticky-col text-center">{{ $rowNo++ }}</td>
                <td class="sticky-col2" style="left:28px;"><strong>Total {{ $kat }}</strong></td>
                @foreach($bulanAktif as $bNo => $bNama)
                    @php
                        $kpTot = array_sum($katTotalP[$bNo]);
                        $kdTot = array_sum($katTotalD[$bNo]);
                        $kbTot = array_sum($katTotalB[$bNo]);
                        $kSaldo = $kdTot - $kbTot;
                    @endphp
                    <td class="td-val">{{ fmtMKBold($katTotalP[$bNo]['w1']) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalP[$bNo]['w2']) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalP[$bNo]['w3']) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalP[$bNo]['w4']) }}</td>
                    <td class="td-val">{{ fmtMKBold($kpTot) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalD[$bNo]['w1']) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalD[$bNo]['w2']) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalD[$bNo]['w3']) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalD[$bNo]['w4']) }}</td>
                    <td class="td-val">{{ fmtMKBold($kdTot) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalB[$bNo]['w1']) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalB[$bNo]['w2']) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalB[$bNo]['w3']) }}</td>
                    <td class="td-val">{{ fmtMKBold($katTotalB[$bNo]['w4']) }}</td>
                    <td class="td-val">{{ fmtMKBold($kbTot) }}</td>
                    <td class="td-val">{{ fmtMKBold($kSaldo) }}</td>
                @endforeach
            </tr>
        @endforeach

        {{-- GRAND TOTAL --}}
        @php
            $gTotalP = []; $gTotalD = []; $gTotalB = [];
            foreach($bulanAktif as $bNo => $bNama) {
                $gTotalP[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                $gTotalD[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                $gTotalB[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                foreach($permintaanData[$bNo] ?? [] as $k => $ss) {
                    foreach($ss as $s => $ii) {
                        foreach($ii as $i => $v) {
                            foreach(['w1','w2','w3','w4'] as $w) $gTotalP[$bNo][$w] += $v[$w];
                        }
                    }
                }
                foreach($droppingData[$bNo] ?? [] as $k => $ss) {
                    foreach($ss as $s => $ii) {
                        foreach($ii as $i => $v) {
                            foreach(['w1','w2','w3','w4'] as $w) $gTotalD[$bNo][$w] += $v[$w];
                        }
                    }
                }
                foreach($pembayaranData[$bNo] ?? [] as $k => $ss) {
                    foreach($ss as $s => $ii) {
                        foreach($ii as $i => $v) {
                            foreach(['w1','w2','w3','w4'] as $w) $gTotalB[$bNo][$w] += $v[$w];
                        }
                    }
                }
            }
        @endphp

        <tr class="row-grandtotal">
            <td class="col-num sticky-col text-center"></td>
            <td class="sticky-col2" style="left:28px;"><strong>TOTAL KESELURUHAN</strong></td>
            @foreach($bulanAktif as $bNo => $bNama)
                @php
                    $gP = array_sum($gTotalP[$bNo]);
                    $gD = array_sum($gTotalD[$bNo]);
                    $gB = array_sum($gTotalB[$bNo]);
                    $gSaldo = $gD - $gB;
                @endphp
                <td class="td-val">{{ fmtMKBold($gTotalP[$bNo]['w1']) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalP[$bNo]['w2']) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalP[$bNo]['w3']) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalP[$bNo]['w4']) }}</td>
                <td class="td-val">{{ fmtMKBold($gP) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalD[$bNo]['w1']) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalD[$bNo]['w2']) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalD[$bNo]['w3']) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalD[$bNo]['w4']) }}</td>
                <td class="td-val">{{ fmtMKBold($gD) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalB[$bNo]['w1']) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalB[$bNo]['w2']) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalB[$bNo]['w3']) }}</td>
                <td class="td-val">{{ fmtMKBold($gTotalB[$bNo]['w4']) }}</td>
                <td class="td-val">{{ fmtMKBold($gB) }}</td>
                <td class="td-val">{{ fmtMKBold($gSaldo) }}</td>
            @endforeach
        </tr>

    </tbody>
</table>
</div>

@endif
