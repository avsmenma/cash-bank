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

    // Helper untuk get nilai mingguan dari section data per bulan
    // $sectionData = permintaanData / droppingData / pembayaranData
    // return merged array of w1-w4 across all active months
    // Actually kita render per bulan, jadi helper ini tidak dipakai global

    // --- Pre-compute date labels per bulan ---
    // W1: 1-7, W2: 8-14, W3: 15-21, W4: 22-akhir
    $weekLabels = ['w1'=>'W1','w2'=>'W2','w3'=>'W3','w4'=>'W4'];
@endphp

@push('styles')
<style>
    #mk-table {
        font-size: 10px;
        white-space: nowrap;
        border-collapse: collapse;
    }
    #mk-table th, #mk-table td {
        padding: 3px 5px;
        border: 1px solid #ccc;
        vertical-align: middle;
    }
    #mk-table .th-uraian { min-width: 300px; max-width: 350px; white-space: normal; }
    #mk-table .col-num   { width: 30px; text-align: center; }
    #mk-table .td-right  { text-align: right; min-width: 80px; }
    #mk-table .td-right-bold { text-align: right; font-weight: bold; min-width: 80px; }

    /* Header warna */
    .th-permintaan { background: #FFF2CC; color:#000; }
    .th-dropping   { background: #DDEBF7; color:#000; }
    .th-pembayaran { background: #E2EFDA; color:#000; }
    .th-saldo      { background: #FCE4D6; color:#000; }

    /* Section rows */
    .row-kategori { background: #FFF2CC; font-weight: bold; }
    .row-sub      { background: #f5f5f5; font-style: italic; }
    .row-item     { background: #ffffff; }
    .row-total-section { background: #BDD7EE; font-weight: bold; }
    .row-selisih  { background: #FFEB9C; font-weight: bold; }
    .row-saldo    { background: #FFC7CE; font-weight: bold; }

    .sticky-col { position: sticky; left: 0; z-index: 2; background: #fff; }
    .sticky-col2 { position: sticky; left: 30px; z-index: 2; background: #fff; }
    .table-scroll { overflow-x: auto; max-width: 100%; }
</style>
@endpush

{{-- ====================================================
     Bangun struktur lengkap: per bulan aktif
     ==================================================== --}}
@php
    $noUrutan = 0;
@endphp

@if(empty($bulanAktif))
    <div class="alert alert-info m-3">Tidak ada data untuk filter yang dipilih.</div>
@else

<div class="table-scroll">
<table border="1" id="mk-table" class="table table-bordered table-sm">
    <thead>
        {{-- ROW 1: Judul Bulan --}}
        <tr>
            <th rowspan="3" class="col-num sticky-col text-center" style="background:#fff;">No.</th>
            <th rowspan="3" class="th-uraian sticky-col2 text-center" style="left:30px;background:#FFF2CC;">Payments for {{ $tahun }} transactions - Accounts</th>
            @foreach($bulanAktif as $bNo => $bNama)
                <th colspan="5" class="th-permintaan text-center">Permintaan Weekly-{{ $bNama }}</th>
                <th colspan="5" class="th-dropping text-center">Dropping Weekly-{{ $bNama }}</th>
                <th colspan="5" class="th-pembayaran text-center">Pembayaran Weekly-{{ $bNama }}</th>
                <th rowspan="3" class="th-saldo text-center" style="min-width:90px;">SALDO MODAL KERJA<br>Per {{ $bNama }} {{ $tahun }}</th>
            @endforeach
        </tr>
        {{-- ROW 2: Label Minggu --}}
        <tr>
            @foreach($bulanAktif as $bNo => $bNama)
                @foreach(['th-permintaan','th-dropping','th-pembayaran'] as $cls)
                    <th class="{{ $cls }} text-center">{{ $bNama }}-W1<br><small>(Tgl 1-7)</small></th>
                    <th class="{{ $cls }} text-center">{{ $bNama }}-W2<br><small>(Tgl 8-14)</small></th>
                    <th class="{{ $cls }} text-center">{{ $bNama }}-W3<br><small>(Tgl 15-21)</small></th>
                    <th class="{{ $cls }} text-center">{{ $bNama }}-W4<br><small>(Tgl 22-31)</small></th>
                    <th class="{{ $cls }} text-center">Weekly-{{ $bNama }}<br><small>(Tgl 1-31)</small></th>
                @endforeach
            @endforeach
        </tr>
        {{-- ROW 3: Nomor kolom --}}
        <tr>
            @php $colIdx = 1; @endphp
            @foreach($bulanAktif as $bNo => $bNama)
                @for($c = 0; $c < 15; $c++)
                    <th class="text-center" style="background:#ddd;">{{ $colIdx++ }}</th>
                @endfor
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php $rowNo = 1; @endphp

        @foreach($allKeys as $kat => $subs)
            @php
                // Grand total per kategori per bulan per section
                $katTotalP = [];
                $katTotalD = [];
                $katTotalB = [];
                foreach($bulanAktif as $bNo => $bNama) {
                    $katTotalP[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                    $katTotalD[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                    $katTotalB[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                }
            @endphp

            {{-- BARIS KATEGORI --}}
            <tr class="row-kategori">
                <td class="col-num sticky-col text-center">{{ $rowNo++ }}</td>
                <td class="sticky-col2" style="left:30px;font-weight:bold;">{{ $kat }}</td>
                @foreach($bulanAktif as $bNo => $bNama)
                    @for($c=0;$c<16;$c++)<td></td>@endfor
                @endforeach
            </tr>

            @foreach($subs as $sub => $items)
                @php
                    $subTotalP = [];
                    $subTotalD = [];
                    $subTotalB = [];
                    foreach($bulanAktif as $bNo => $bNama) {
                        $subTotalP[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                        $subTotalD[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                        $subTotalB[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                    }
                @endphp

                {{-- BARIS SUB KRITERIA --}}
                <tr class="row-sub">
                    <td class="col-num sticky-col text-center"></td>
                    <td class="sticky-col2" style="left:30px;padding-left:15px;">{{ $sub }}</td>
                    @foreach($bulanAktif as $bNo => $bNama)
                        @for($c=0;$c<16;$c++)<td></td>@endfor
                    @endforeach
                </tr>

                @foreach($items as $item => $_)
                    {{-- nilai per bulan per section --}}
                    @php
                        // Track total sub
                        foreach($bulanAktif as $bNo => $bNama) {
                            $pVal = $permintaanData[$bNo][$kat][$sub][$item] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                            $dVal = $droppingData[$bNo][$kat][$sub][$item]   ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                            $bVal = $pembayaranData[$bNo][$kat][$sub][$item] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];

                            foreach(['w1','w2','w3','w4'] as $w) {
                                $subTotalP[$bNo][$w] += $pVal[$w];
                                $subTotalD[$bNo][$w] += $dVal[$w];
                                $subTotalB[$bNo][$w] += $bVal[$w];
                                $katTotalP[$bNo][$w] += $pVal[$w];
                                $katTotalD[$bNo][$w] += $dVal[$w];
                                $katTotalB[$bNo][$w] += $bVal[$w];
                            }
                        }
                    @endphp

                    <tr class="row-item">
                        <td class="col-num sticky-col text-center"></td>
                        <td class="sticky-col2" style="left:30px;padding-left:30px;">- {{ $item }}</td>
                        @foreach($bulanAktif as $bNo => $bNama)
                            @php
                                $pV = $permintaanData[$bNo][$kat][$sub][$item] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                                $dV = $droppingData[$bNo][$kat][$sub][$item]   ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                                $bV = $pembayaranData[$bNo][$kat][$sub][$item] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                                $pTot = $pV['w1']+$pV['w2']+$pV['w3']+$pV['w4'];
                                $dTot = $dV['w1']+$dV['w2']+$dV['w3']+$dV['w4'];
                                $bTot = $bV['w1']+$bV['w2']+$bV['w3']+$bV['w4'];
                                // saldo per item = permintaan - pembayaran (atau bisa memakai dropping - pembayaran tergantung definisi)
                                // Mengikuti referensi: kolom terakhir adalah Saldo Modal Kerja per bulan
                                $saldo = $dTot - $bTot;
                            @endphp
                            <td class="td-right">{{ fmtMK($pV['w1']) }}</td>
                            <td class="td-right">{{ fmtMK($pV['w2']) }}</td>
                            <td class="td-right">{{ fmtMK($pV['w3']) }}</td>
                            <td class="td-right">{{ fmtMK($pV['w4']) }}</td>
                            <td class="td-right-bold th-permintaan">{{ fmtMK($pTot) }}</td>
                            <td class="td-right">{{ fmtMK($dV['w1']) }}</td>
                            <td class="td-right">{{ fmtMK($dV['w2']) }}</td>
                            <td class="td-right">{{ fmtMK($dV['w3']) }}</td>
                            <td class="td-right">{{ fmtMK($dV['w4']) }}</td>
                            <td class="td-right-bold th-dropping">{{ fmtMK($dTot) }}</td>
                            <td class="td-right">{{ fmtMK($bV['w1']) }}</td>
                            <td class="td-right">{{ fmtMK($bV['w2']) }}</td>
                            <td class="td-right">{{ fmtMK($bV['w3']) }}</td>
                            <td class="td-right">{{ fmtMK($bV['w4']) }}</td>
                            <td class="td-right-bold th-pembayaran">{{ fmtMK($bTot) }}</td>
                            <td class="td-right-bold th-saldo">{{ fmtMK($saldo) }}</td>
                        @endforeach
                    </tr>
                @endforeach

                {{-- Sub Total --}}
                <tr class="row-total-section">
                    <td class="col-num sticky-col text-center"></td>
                    <td class="sticky-col2" style="left:30px;padding-left:15px;"><strong>Sub Total {{ $sub }}</strong></td>
                    @foreach($bulanAktif as $bNo => $bNama)
                        @php
                            $spTot = array_sum($subTotalP[$bNo]);
                            $sdTot = array_sum($subTotalD[$bNo]);
                            $sbTot = array_sum($subTotalB[$bNo]);
                            $sSaldo = $sdTot - $sbTot;
                        @endphp
                        <td class="td-right">{{ fmtMKBold($subTotalP[$bNo]['w1']) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalP[$bNo]['w2']) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalP[$bNo]['w3']) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalP[$bNo]['w4']) }}</td>
                        <td class="td-right-bold th-permintaan">{{ fmtMKBold($spTot) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalD[$bNo]['w1']) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalD[$bNo]['w2']) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalD[$bNo]['w3']) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalD[$bNo]['w4']) }}</td>
                        <td class="td-right-bold th-dropping">{{ fmtMKBold($sdTot) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalB[$bNo]['w1']) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalB[$bNo]['w2']) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalB[$bNo]['w3']) }}</td>
                        <td class="td-right">{{ fmtMKBold($subTotalB[$bNo]['w4']) }}</td>
                        <td class="td-right-bold th-pembayaran">{{ fmtMKBold($sbTot) }}</td>
                        <td class="td-right-bold th-saldo">{{ fmtMKBold($sSaldo) }}</td>
                    @endforeach
                </tr>
            @endforeach

            {{-- Total Kategori --}}
            <tr style="background:#BDD7EE;font-weight:bold;">
                <td class="col-num sticky-col text-center">{{ $rowNo++ }}</td>
                <td class="sticky-col2" style="left:30px;"><strong>Total {{ $kat }}</strong></td>
                @foreach($bulanAktif as $bNo => $bNama)
                    @php
                        $kpTot = array_sum($katTotalP[$bNo]);
                        $kdTot = array_sum($katTotalD[$bNo]);
                        $kbTot = array_sum($katTotalB[$bNo]);
                        $kSaldo = $kdTot - $kbTot;
                    @endphp
                    <td class="td-right">{{ fmtMKBold($katTotalP[$bNo]['w1']) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalP[$bNo]['w2']) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalP[$bNo]['w3']) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalP[$bNo]['w4']) }}</td>
                    <td class="td-right-bold th-permintaan">{{ fmtMKBold($kpTot) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalD[$bNo]['w1']) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalD[$bNo]['w2']) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalD[$bNo]['w3']) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalD[$bNo]['w4']) }}</td>
                    <td class="td-right-bold th-dropping">{{ fmtMKBold($kdTot) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalB[$bNo]['w1']) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalB[$bNo]['w2']) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalB[$bNo]['w3']) }}</td>
                    <td class="td-right">{{ fmtMKBold($katTotalB[$bNo]['w4']) }}</td>
                    <td class="td-right-bold th-pembayaran">{{ fmtMKBold($kbTot) }}</td>
                    <td class="td-right-bold th-saldo">{{ fmtMKBold($kSaldo) }}</td>
                @endforeach
            </tr>
        @endforeach

        {{-- ================================
             GRAND TOTAL KESELURUHAN
             ================================ --}}
        @php
            $gTotalP = [];
            $gTotalD = [];
            $gTotalB = [];
            foreach($bulanAktif as $bNo => $bNama) {
                $gTotalP[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                $gTotalD[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                $gTotalB[$bNo] = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
                foreach($permintaanData[$bNo]  ?? [] as $k => $subs) {
                    foreach($subs as $s => $items) {
                        foreach($items as $i => $v) {
                            foreach(['w1','w2','w3','w4'] as $w) $gTotalP[$bNo][$w] += $v[$w];
                        }
                    }
                }
                foreach($droppingData[$bNo]   ?? [] as $k => $subs) {
                    foreach($subs as $s => $items) {
                        foreach($items as $i => $v) {
                            foreach(['w1','w2','w3','w4'] as $w) $gTotalD[$bNo][$w] += $v[$w];
                        }
                    }
                }
                foreach($pembayaranData[$bNo] ?? [] as $k => $subs) {
                    foreach($subs as $s => $items) {
                        foreach($items as $i => $v) {
                            foreach(['w1','w2','w3','w4'] as $w) $gTotalB[$bNo][$w] += $v[$w];
                        }
                    }
                }
            }
        @endphp

        <tr style="background:#FF0000;color:#fff;font-weight:bold;">
            <td class="col-num sticky-col text-center" style="background:#FF0000;color:#fff;"></td>
            <td class="sticky-col2" style="left:30px;background:#FF0000;color:#fff;"><strong>TOTAL KESELURUHAN</strong></td>
            @foreach($bulanAktif as $bNo => $bNama)
                @php
                    $gP = array_sum($gTotalP[$bNo]);
                    $gD = array_sum($gTotalD[$bNo]);
                    $gB = array_sum($gTotalB[$bNo]);
                    $gSaldo = $gD - $gB;
                @endphp
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalP[$bNo]['w1']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalP[$bNo]['w2']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalP[$bNo]['w3']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalP[$bNo]['w4']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gP) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalD[$bNo]['w1']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalD[$bNo]['w2']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalD[$bNo]['w3']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalD[$bNo]['w4']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gD) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalB[$bNo]['w1']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalB[$bNo]['w2']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalB[$bNo]['w3']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gTotalB[$bNo]['w4']) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gB) }}</td>
                <td class="td-right" style="color:#fff;">{{ fmtMKBold($gSaldo) }}</td>
            @endforeach
        </tr>

    </tbody>
</table>
</div>

@endif
