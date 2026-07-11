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
    $bulanShort = [
        1=>'JAN', 2=>'FEB', 3=>'MAR', 4=>'APR', 5=>'MEI', 6=>'JUN',
        7=>'JUL', 8=>'AGS', 9=>'SEP', 10=>'OKT', 11=>'NOV', 12=>'DES'
    ];
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
    /* Saldo */
    .th-saldo { background: #ED7D31 !important; color: #fff !important; }
    .th-saldo-prev { background: #ff00e6 !important; color: #fff !important; }
    .th-modal-sendiri { background: #00f51f !important; color: #fff !important; }
    .th-saldo-now { background: #3b82e8 !important; color: #fff !important; }

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
    .bg-sprev { background: #f8dcff; font-weight: bold; }
    .bg-ms    { background: #e4ffe6; font-weight: bold; }
    .bg-snow  { background: #dbeafe; font-weight: bold; }

    /* Row jenis */
    .row-kategori td { background: #2F4F4F !important; color: #fff !important; font-weight: bold; }
    .row-sub td      { background: #D9E1F2 !important; font-weight: bold; }
    .row-item td     { background: #ffffff; }
    .row-subtotal td { background: #BDD7EE !important; font-weight: bold; }
    .row-kattotal td { background: #1F3864 !important; color: #fff !important; font-weight: bold; }
    .row-grandtotal td { background: #C00000 !important; color: #fff !important; font-weight: bold; }
    .row-ui-summary td { background: #fff; }
    .row-ui-summary .summary-label { font-weight: 600; text-align: right; }
    .row-ui-summary-green td { background: #00f51f !important; font-weight: bold; }
    .row-ui-summary-yellow td { background: #fff200 !important; font-weight: bold; }
    .row-ui-summary-cyan td { background: #00f3ff !important; font-weight: bold; }
    .row-ui-summary-orange td { background: #f59e0b !important; font-weight: bold; }
    .row-ui-summary-softgreen td { background: #c6e0b4 !important; font-weight: bold; }

    /* Sticky */
    .table-scroll {
        border: 1px solid #8da0b3;
        max-height: calc(100vh - 185px);
        max-width: 100%;
        overflow: auto;
        position: relative;
        scrollbar-gutter: stable;
    }

    .sticky-col  { position: sticky; left: 0;    z-index: 6; }
    .sticky-col2 { position: sticky; left: 28px; z-index: 6; }

    #mk-table thead th {
        position: sticky;
        z-index: 30;
        background-clip: padding-box;
        box-shadow: inset 0 -1px 0 #8796a8;
    }

    #mk-table thead tr:nth-child(1) th {
        height: 28px;
        top: 0;
    }

    #mk-table thead tr:nth-child(2) th {
        height: 45px;
        top: 28px;
    }

    #mk-table thead tr:nth-child(3) th {
        height: 24px;
        top: 73px;
    }

    #mk-table thead th[rowspan="3"] {
        top: 0;
        z-index: 55;
        box-shadow: inset 0 -1px 0 #8796a8, 1px 0 0 #8796a8;
    }

    #mk-table thead th.sticky-col {
        z-index: 65;
    }

    #mk-table thead th.sticky-col2 {
        z-index: 64;
    }
</style>

@if(empty($bulanAktif))
    <div class="alert alert-info m-3">Tidak ada data untuk filter yang dipilih.</div>
@else

{{-- drag-scroll: tahan-klik lalu geser untuk scroll HORIZONTAL saja
     (handler global di layouts/index hanya menggeser scrollLeft) --}}
<div class="table-scroll drag-scroll">
<table id="mk-table" class="table table-bordered table-sm">
    <thead>
        {{-- ROW 1 --}}
        <tr>
            <th rowspan="3" class="col-num sticky-col th-no-uraian text-center">No.</th>
            <th rowspan="3" class="th-uraian sticky-col2 th-no-uraian text-center" style="left:28px;">
                Payments for {{ $tahun }} transactions - Accounts
            </th>
            @foreach($bulanAktif as $bNo => $bNama)
                @php
                    $currentMonthDate = \Carbon\Carbon::create((int) $tahun, (int) $bNo, 1);
                    $previousMonthDate = $currentMonthDate->copy()->subMonth();
                    $prevShort = $bulanShort[(int) $previousMonthDate->month] ?? strtoupper($previousMonthDate->format('M'));
                    $currentShort = $bulanShort[(int) $currentMonthDate->month] ?? strtoupper($currentMonthDate->format('M'));
                    $prevYearShort = $previousMonthDate->format('y');
                    $currentYearShort = $currentMonthDate->format('y');
                @endphp
                <th colspan="5" class="th-permintaan text-center">Permintaan Weekly-{{ $bNama }}</th>
                <th colspan="5" class="th-dropping text-center">Dropping Weekly-{{ $bNama }}</th>
                <th colspan="5" class="th-pembayaran text-center">Pembayaran Weekly-{{ $bNama }}</th>
                <th rowspan="3" class="th-saldo text-center" style="min-width:90px;">
                    SALDO MODAL KERJA<br>Per {{ $bNama }} {{ $tahun }}
                </th>
                <th rowspan="3" class="th-saldo-prev text-center" style="min-width:100px;">
                    SALDO<br>MOKER SD<br>{{ $prevShort }} {{ $prevYearShort }}<br>
                    <small><i>Per {{ $previousMonthDate->endOfMonth()->format('d') }} {{ $prevShort }} {{ $prevYearShort }}</i></small>
                </th>
                <th rowspan="3" class="th-modal-sendiri text-center" style="min-width:110px;">
                    PENGGUNAAN<br>MODAL<br>SENDIRI<br>
                    <small><i>{{ $bNama }} {{ $tahun }}</i></small>
                </th>
                <th rowspan="3" class="th-saldo-now text-center" style="min-width:100px;">
                    SALDO MOKER<br>SD {{ $currentShort }} {{ $currentYearShort }}<br>
                    <small><i>Per {{ $currentMonthDate->endOfMonth()->format('d') }} {{ $currentShort }} {{ $currentYearShort }}</i></small>
                </th>
            @endforeach
        </tr>
        {{-- ROW 2 --}}
        <tr>
            @foreach($bulanAktif as $bNo => $bNama)
                @php $wc = $weekCuts[$bNo] ?? ['w1_start'=>1,'w1_end'=>7,'w2_start'=>8,'w2_end'=>14,'w3_start'=>15,'w3_end'=>21,'w4_start'=>22,'w4_end'=>31]; @endphp
                <th class="th-permintaan-sub text-center">{{ $bNama }}-W1<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w1"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #333;">
                        ({{ $wc['w1_start'] }}-{{ $wc['w1_end'] }})
                    </small>
                </th>
                <th class="th-permintaan-sub text-center">{{ $bNama }}-W2<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w2"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #333;">
                        ({{ $wc['w2_start'] }}-{{ $wc['w2_end'] }})
                    </small>
                </th>
                <th class="th-permintaan-sub text-center">{{ $bNama }}-W3<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w3"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #333;">
                        ({{ $wc['w3_start'] }}-{{ $wc['w3_end'] }})
                    </small>
                </th>
                <th class="th-permintaan-sub text-center">{{ $bNama }}-W4<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w4"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #333;">
                        ({{ $wc['w4_start'] }}-{{ $wc['w4_end'] }})
                    </small>
                </th>
                <th class="th-permintaan-sub text-center">Weekly-{{ $bNama }}<br><small>(1-31)</small></th>

                <th class="th-dropping-sub text-center">{{ $bNama }}-W1<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w1"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #adf;">
                        ({{ $wc['w1_start'] }}-{{ $wc['w1_end'] }})
                    </small>
                </th>
                <th class="th-dropping-sub text-center">{{ $bNama }}-W2<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w2"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #adf;">
                        ({{ $wc['w2_start'] }}-{{ $wc['w2_end'] }})
                    </small>
                </th>
                <th class="th-dropping-sub text-center">{{ $bNama }}-W3<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w3"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #adf;">
                        ({{ $wc['w3_start'] }}-{{ $wc['w3_end'] }})
                    </small>
                </th>
                <th class="th-dropping-sub text-center">{{ $bNama }}-W4<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w4"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #adf;">
                        ({{ $wc['w4_start'] }}-{{ $wc['w4_end'] }})
                    </small>
                </th>
                <th class="th-dropping-sub text-center">Weekly-{{ $bNama }}<br><small>(1-31)</small></th>

                <th class="th-pembayaran-sub text-center">{{ $bNama }}-W1<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w1"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #bfb;">
                        ({{ $wc['w1_start'] }}-{{ $wc['w1_end'] }})
                    </small>
                </th>
                <th class="th-pembayaran-sub text-center">{{ $bNama }}-W2<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w2"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #bfb;">
                        ({{ $wc['w2_start'] }}-{{ $wc['w2_end'] }})
                    </small>
                </th>
                <th class="th-pembayaran-sub text-center">{{ $bNama }}-W3<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w3"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #bfb;">
                        ({{ $wc['w3_start'] }}-{{ $wc['w3_end'] }})
                    </small>
                </th>
                <th class="th-pembayaran-sub text-center">{{ $bNama }}-W4<br>
                    <small class="mk-week-label" data-bulan="{{ $bNo }}" data-week="w4"
                        title="Klik untuk ubah tanggal" style="cursor:pointer;border-bottom:1px dashed #bfb;">
                        ({{ $wc['w4_start'] }}-{{ $wc['w4_end'] }})
                    </small>
                </th>
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
                    @for($c=0;$c<19;$c++)<td></td>@endfor
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
                        @for($c=0;$c<19;$c++)<td></td>@endfor
                    @endforeach
                </tr>

                @foreach($items as $item => $_x)
                    <tr class="row-item">
                        <td class="col-num sticky-col text-center"></td>
                        <td class="sticky-col2" style="left:28px;padding-left:28px;">{{ $item === '' ? '' : '- ' . $item }}</td>
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
                            <td class="td-val-bold bg-sprev">-</td>
                            <td class="td-val-bold bg-ms">-</td>
                            <td class="td-val-bold bg-snow">-</td>
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
                        <td class="td-val-bold bg-sprev">0</td>
                        <td class="td-val-bold bg-ms">0</td>
                        <td class="td-val-bold bg-snow">0</td>
                    @endforeach
                </tr>
            @endforeach

            {{-- Total Kategori --}}
            <tr class="row-kattotal">
                <td class="col-num sticky-col text-center"></td>
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
                    <td class="td-val">0</td>
                    <td class="td-val">0</td>
                    <td class="td-val">0</td>
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
                <td class="td-val">0</td>
                <td class="td-val">0</td>
                <td class="td-val">0</td>
            @endforeach
        </tr>

        @php
            // Setiap nilai ringkasan = 1 angka bulanan (satuan ribuan), dihitung
            // penuh di controller (single source of truth). Dirender sebagai satu
            // sel gabungan (colspan=19) per blok bulan.
            $summaryRows = [
                ['class' => 'row-ui-summary',          'label' => 'Saldo Awal (Modal Sendiri)',                              'key' => 'saldo_awal_modal_sendiri'],
                ['class' => 'row-ui-summary',          'label' => 'Pembayaran Menggunakan Modal Sendiri',                    'key' => 'pembayaran_modal_sendiri_tahun_lalu'],
                ['class' => 'row-ui-summary',          'label' => 'Penerimaan Pengembalian Dana Kebun-Unit & Angsuran',     'key' => 'penerimaan_pengembalian_dana'],
                ['class' => 'row-ui-summary',          'label' => 'Biaya Admin Bank & Lainnya',                             'key' => 'biaya_admin_bank_lainnya'],
                ['class' => 'row-ui-summary-green',    'label' => 'Saldo Akhir (Modal Sendiri)',                            'key' => 'saldo_akhir_modal_sendiri'],
                ['class' => 'row-ui-summary',          'label' => 'Saldo Awal Modal Kerja (01 / Awal Bulan)',               'key' => 'saldo_awal_modal_kerja'],
                ['class' => 'row-ui-summary-yellow',   'label' => 'Saldo Awal Bank Opex (01 / Awal Bulan)',                 'key' => 'saldo_awal_bank_opex'],
                ['class' => 'row-ui-summary',          'label' => 'Pembayaran Menggunakan Modal Kerja',                     'key' => 'pembayaran_modal_kerja'],
                ['class' => 'row-ui-summary',          'label' => 'Penerimaan Dana Modal Kerja',                            'key' => 'penerimaan_modal_kerja'],
                ['class' => 'row-ui-summary-yellow',   'label' => 'Sisa Modal Kerja Tersedia',                              'key' => 'sisa_modal_kerja'],
                ['class' => 'row-ui-summary-cyan',     'label' => 'Saldo Akhir Bank Opex (Akhir Bulan)',                    'key' => 'saldo_akhir_bank_opex'],
                ['class' => 'row-ui-summary-orange',   'label' => 'Posisi Saldo Di Rekg Opex Operasional (Mandiri 408)',   'key' => 'posisi_rek_408'],
                ['class' => 'row-ui-summary-orange',   'label' => 'Posisi Saldo Di Rekg TBS (Mandiri 200)',                'key' => 'posisi_rek_200'],
                ['class' => 'row-ui-summary-softgreen','label' => 'Jumlah Saldo Opex',                                     'key' => 'jumlah_saldo_opex'],
            ];
        @endphp

        @foreach($summaryRows as $summary)
            <tr class="{{ $summary['class'] }}">
                <td class="col-num sticky-col text-center"></td>
                <td class="sticky-col2 summary-label" style="left:28px;">{{ $summary['label'] }}</td>
                @foreach($bulanAktif as $bNo => $bNama)
                    @php $summaryValue = $modalKerjaSummaryData[$summary['key']][$bNo] ?? 0; @endphp
                    <td class="td-val-bold" colspan="19" style="text-align:right;">{{ fmtMKBold($summaryValue) }}</td>
                @endforeach
            </tr>
        @endforeach

    </tbody>
</table>
</div>

@endif
