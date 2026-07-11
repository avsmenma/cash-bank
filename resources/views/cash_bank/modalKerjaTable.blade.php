@php
    $weekTemplate = ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
    $bulanShort = [
        1=>'JAN', 2=>'FEB', 3=>'MAR', 4=>'APR', 5=>'MEI', 6=>'JUN',
        7=>'JUL', 8=>'AGS', 9=>'SEP', 10=>'OKT', 11=>'NOV', 12=>'DES'
    ];
@endphp

<style>
    /* ============================================================
       MODAL KERJA — Tabulator (tabel ala spreadsheet).
       Kolom bisa ditarik-lebarkan manual; warna header mengikuti
       referensi Excel lama (kuning/biru/hijau/oranye dst).
       ============================================================ */
    #mk-table {
        border: 1px solid #8da0b3;
        border-radius: 8px;
        font-size: 10.5px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    #mk-table .tabulator-header {
        background: #2F4F4F;
        border-bottom: 2px solid #1f3737;
    }
    #mk-table .tabulator-header .tabulator-col,
    #mk-table .tabulator-header .tabulator-col-group {
        background: #2F4F4F;
        color: #fff;
        font-weight: 600;
        font-size: 10px;
        border-color: #ffffff !important;
        border-right: 1px solid #ffffff !important;
    }
    #mk-table .tabulator-header .tabulator-col-group-cols > .tabulator-col:last-child {
        border-right: none !important;
    }
    #mk-table .tabulator-header .tabulator-col .tabulator-col-title {
        text-align: center;
        white-space: normal;
    }

    /* Takik penanda kolom bisa ditarik (hanya kolom terbawah) */
    #mk-table .tabulator-header .tabulator-col-resize-handle {
        width: 6px;
        cursor: col-resize;
        background: none;
    }
    #mk-table .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
        background: linear-gradient(
            to bottom,
            transparent 32%,
            rgba(255, 255, 255, 0.55) 32%,
            rgba(255, 255, 255, 0.55) 68%,
            transparent 68%
        );
        background-size: 2px 100%;
        background-position: center;
        background-repeat: no-repeat;
    }
    #mk-table .tabulator-header .tabulator-col-resize-handle:hover {
        background: rgba(255, 255, 255, 0.30);
    }

    /* Warna header per bagian (referensi Excel) */
    #mk-table .tabulator-col.mk-h-p,
    #mk-table .tabulator-col-group.mk-h-p { background: #FFD700 !important; color: #000 !important; }
    #mk-table .tabulator-col.mk-h-p .tabulator-col-title { color: #000; }
    #mk-table .tabulator-col.mk-h-d,
    #mk-table .tabulator-col-group.mk-h-d { background: #4472C4 !important; color: #fff !important; }
    #mk-table .tabulator-col.mk-h-b,
    #mk-table .tabulator-col-group.mk-h-b { background: #70AD47 !important; color: #fff !important; }
    #mk-table .tabulator-col.mk-h-s { background: #ED7D31 !important; }
    #mk-table .tabulator-col.mk-h-sprev { background: #ff00e6 !important; }
    #mk-table .tabulator-col.mk-h-ms { background: #00f51f !important; color: #000 !important; }
    #mk-table .tabulator-col.mk-h-ms .tabulator-col-title { color: #000; }
    #mk-table .tabulator-col.mk-h-snow { background: #3b82e8 !important; }

    /* Label minggu (klik untuk ubah tanggal) & nomor kolom di header */
    #mk-table .mk-week-label {
        cursor: pointer;
        border-bottom: 1px dashed #333;
    }
    #mk-table .mk-colnum {
        display: block;
        margin-top: 2px;
        background: #D6DCE4;
        color: #000;
        border-radius: 2px;
        font-weight: 600;
        text-align: center;
    }

    /* Garis kolom & baris tegas */
    #mk-table .tabulator-cell {
        border-right: 1px solid #b3bfcc;
        border-color: #b3bfcc;
    }
    #mk-table .tabulator-row {
        border-bottom: 1px solid #b3bfcc;
    }

    /* Kolom total per blok: tebal */
    #mk-table .tabulator-cell.mk-c-bold { font-weight: 700; }
    #mk-table .tabulator-cell.mk-neg { color: #c00000 !important; font-weight: 700; }

    /* ---------- Jenis baris ---------- */
    #mk-table .tabulator-row.mk-r-kategori .tabulator-cell { background: #2F4F4F !important; color: #fff !important; font-weight: 700; }
    #mk-table .tabulator-row.mk-r-sub .tabulator-cell { background: #D9E1F2 !important; font-weight: 700; }
    #mk-table .tabulator-row.mk-r-item .tabulator-cell { background: #ffffff; }
    #mk-table .tabulator-row.mk-r-item:hover .tabulator-cell { background: #f8fbff; }
    #mk-table .tabulator-row.mk-r-subtotal .tabulator-cell { background: #BDD7EE !important; font-weight: 700; }
    #mk-table .tabulator-row.mk-r-kattotal .tabulator-cell { background: #1F3864 !important; color: #fff !important; font-weight: 700; }
    #mk-table .tabulator-row.mk-r-grandtotal .tabulator-cell { background: #C00000 !important; color: #fff !important; font-weight: 700; }
    #mk-table .tabulator-row.mk-r-sumplain .tabulator-cell { background: #ffffff; font-weight: 700; }
    #mk-table .tabulator-row.mk-r-sumgreen .tabulator-cell { background: #00f51f !important; font-weight: 700; }
    #mk-table .tabulator-row.mk-r-sumyellow .tabulator-cell { background: #fff200 !important; font-weight: 700; }
    #mk-table .tabulator-row.mk-r-sumcyan .tabulator-cell { background: #00f3ff !important; font-weight: 700; }
    #mk-table .tabulator-row.mk-r-sumorange .tabulator-cell { background: #f59e0b !important; font-weight: 700; }
    #mk-table .tabulator-row.mk-r-sumsoft .tabulator-cell { background: #c6e0b4 !important; font-weight: 700; }

    @media print {
        #mk-table .tabulator-tableholder {
            overflow: visible !important;
            max-height: none !important;
        }
    }
</style>

@if(empty($bulanAktif))
    <div class="alert alert-info m-3">Tidak ada data untuk filter yang dipilih.</div>
@else

@php
    // ================================================================
    // Rakit BARIS sebagai data Tabulator (logika hitung lama utuh).
    // Field per bulan: m{no}_p1..p4,pt / d1..d4,dt / b1..b4,bt / s,sprev,ms,snow
    // ================================================================
    $mkRows = [];
    $mkPush = function ($type, $no, $uraian, $cells = []) use (&$mkRows) {
        $mkRows[] = array_merge(['type' => $type, 'no' => $no, 'uraian' => $uraian], $cells);
    };

    $mkCells = function ($perBulan) use ($bulanAktif) {
        // $perBulan: [bNo => ['p'=>[w1..w4],'d'=>..,'b'=>.., 'sprev'=>..,'ms'=>..,'snow'=>..]]
        $cells = [];
        foreach ($bulanAktif as $bNo => $bNama) {
            $p = $perBulan[$bNo]['p'] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
            $d = $perBulan[$bNo]['d'] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
            $b = $perBulan[$bNo]['b'] ?? ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0];
            $pTot = array_sum($p); $dTot = array_sum($d); $bTot = array_sum($b);
            foreach ([1,2,3,4] as $i) {
                $cells["m{$bNo}_p{$i}"] = $p['w'.$i];
                $cells["m{$bNo}_d{$i}"] = $d['w'.$i];
                $cells["m{$bNo}_b{$i}"] = $b['w'.$i];
            }
            $cells["m{$bNo}_pt"] = $pTot;
            $cells["m{$bNo}_dt"] = $dTot;
            $cells["m{$bNo}_bt"] = $bTot;
            $cells["m{$bNo}_s"] = $dTot - $bTot;
            $cells["m{$bNo}_sprev"] = $perBulan[$bNo]['sprev'] ?? 0;
            $cells["m{$bNo}_ms"] = $perBulan[$bNo]['ms'] ?? 0;
            $cells["m{$bNo}_snow"] = $perBulan[$bNo]['snow'] ?? 0;
        }
        return $cells;
    };

    $rowNo = 1;
    foreach ($allKeys as $kat => $subs) {
        $katTotal = [];
        foreach ($bulanAktif as $bNo => $n) $katTotal[$bNo] = ['p' => $weekTemplate, 'd' => $weekTemplate, 'b' => $weekTemplate];

        $mkPush('kategori', $rowNo++, $kat);

        foreach ($subs as $sub => $items) {
            $subTotal = [];
            foreach ($bulanAktif as $bNo => $n) $subTotal[$bNo] = ['p' => $weekTemplate, 'd' => $weekTemplate, 'b' => $weekTemplate];

            $mkPush('sub', null, $sub);

            foreach ($items as $item => $_x) {
                $perBulan = [];
                foreach ($bulanAktif as $bNo => $n) {
                    $pV = $permintaanData[$bNo][$kat][$sub][$item] ?? $weekTemplate;
                    $dV = $droppingData[$bNo][$kat][$sub][$item] ?? $weekTemplate;
                    $bV = $pembayaranData[$bNo][$kat][$sub][$item] ?? $weekTemplate;
                    $perBulan[$bNo] = ['p' => $pV, 'd' => $dV, 'b' => $bV, 'sprev' => '-', 'ms' => '-', 'snow' => '-'];
                    foreach (['w1','w2','w3','w4'] as $w) {
                        $subTotal[$bNo]['p'][$w] += $pV[$w];
                        $subTotal[$bNo]['d'][$w] += $dV[$w];
                        $subTotal[$bNo]['b'][$w] += $bV[$w];
                        $katTotal[$bNo]['p'][$w] += $pV[$w];
                        $katTotal[$bNo]['d'][$w] += $dV[$w];
                        $katTotal[$bNo]['b'][$w] += $bV[$w];
                    }
                }
                $mkPush('item', null, $item === '' ? '' : '- ' . $item, $mkCells($perBulan));
            }

            $mkPush('subtotal', null, 'Sub Total ' . $sub, $mkCells($subTotal));
        }

        $mkPush('kattotal', null, 'Total ' . $kat, $mkCells($katTotal));
    }

    // ---------- GRAND TOTAL (dihitung dari seluruh data mentah) ----------
    $gTotal = [];
    foreach ($bulanAktif as $bNo => $n) {
        $gTotal[$bNo] = ['p' => $weekTemplate, 'd' => $weekTemplate, 'b' => $weekTemplate];
        foreach (['p' => $permintaanData, 'd' => $droppingData, 'b' => $pembayaranData] as $kunci => $sumber) {
            foreach ($sumber[$bNo] ?? [] as $k => $ss) {
                foreach ($ss as $s => $ii) {
                    foreach ($ii as $i => $v) {
                        foreach (['w1','w2','w3','w4'] as $w) $gTotal[$bNo][$kunci][$w] += $v[$w];
                    }
                }
            }
        }
    }
    $mkPush('grandtotal', null, 'TOTAL KESELURUHAN', $mkCells($gTotal));

    // ---------- BARIS RINGKASAN (nilai bulanan tunggal di kolom terakhir blok bulan) ----------
    $summaryRows = [
        ['type' => 'sumplain',  'label' => 'Saldo Awal (Modal Sendiri)',                            'key' => 'saldo_awal_modal_sendiri'],
        ['type' => 'sumplain',  'label' => 'Pembayaran Menggunakan Modal Sendiri',                  'key' => 'pembayaran_modal_sendiri_tahun_lalu'],
        ['type' => 'sumplain',  'label' => 'Penerimaan Pengembalian Dana Kebun-Unit & Angsuran',    'key' => 'penerimaan_pengembalian_dana'],
        ['type' => 'sumplain',  'label' => 'Biaya Admin Bank & Lainnya',                            'key' => 'biaya_admin_bank_lainnya'],
        ['type' => 'sumgreen',  'label' => 'Saldo Akhir (Modal Sendiri)',                           'key' => 'saldo_akhir_modal_sendiri'],
        ['type' => 'sumplain',  'label' => 'Saldo Awal Modal Kerja (01 / Awal Bulan)',              'key' => 'saldo_awal_modal_kerja'],
        ['type' => 'sumyellow', 'label' => 'Saldo Awal Bank Opex (01 / Awal Bulan)',                'key' => 'saldo_awal_bank_opex'],
        ['type' => 'sumplain',  'label' => 'Pembayaran Menggunakan Modal Kerja',                    'key' => 'pembayaran_modal_kerja'],
        ['type' => 'sumplain',  'label' => 'Penerimaan Dana Modal Kerja',                           'key' => 'penerimaan_modal_kerja'],
        ['type' => 'sumyellow', 'label' => 'Sisa Modal Kerja Tersedia',                             'key' => 'sisa_modal_kerja'],
        ['type' => 'sumcyan',   'label' => 'Saldo Akhir Bank Opex (Akhir Bulan)',                   'key' => 'saldo_akhir_bank_opex'],
        ['type' => 'sumorange', 'label' => 'Posisi Saldo Di Rekg Opex Operasional (Mandiri 408)',   'key' => 'posisi_rek_408'],
        ['type' => 'sumorange', 'label' => 'Posisi Saldo Di Rekg TBS (Mandiri 200)',                'key' => 'posisi_rek_200'],
        ['type' => 'sumsoft',   'label' => 'Jumlah Saldo Opex',                                     'key' => 'jumlah_saldo_opex'],
    ];
    foreach ($summaryRows as $summary) {
        $cells = [];
        foreach ($bulanAktif as $bNo => $n) {
            $cells["m{$bNo}_snow"] = $modalKerjaSummaryData[$summary['key']][$bNo] ?? 0;
        }
        $mkPush($summary['type'], null, $summary['label'], $cells);
    }

    // ================================================================
    // Rakit DEFINISI KOLOM (judul HTML: label minggu klik-ubah + nomor kolom)
    // ================================================================
    $mkCols = [
        ['title' => 'No.', 'field' => 'no', 'frozen' => true, 'width' => 46, 'hozAlign' => 'center', 'headerHozAlign' => 'center'],
        ['title' => 'Payments for ' . $tahun . ' transactions - Accounts', 'field' => 'uraian', 'frozen' => true, 'minWidth' => 280, 'widthGrow' => 2, 'headerHozAlign' => 'center'],
    ];

    $colIdx = 1;
    foreach ($bulanAktif as $bNo => $bNama) {
        $wc = $weekCuts[$bNo] ?? ['w1_start'=>1,'w1_end'=>7,'w2_start'=>8,'w2_end'=>14,'w3_start'=>15,'w3_end'=>21,'w4_start'=>22,'w4_end'=>31];

        $currentMonthDate = \Carbon\Carbon::create((int) $tahun, (int) $bNo, 1);
        $previousMonthDate = $currentMonthDate->copy()->subMonth();
        $prevShort = $bulanShort[(int) $previousMonthDate->month] ?? strtoupper($previousMonthDate->format('M'));
        $currentShort = $bulanShort[(int) $currentMonthDate->month] ?? strtoupper($currentMonthDate->format('M'));
        $prevYearShort = $previousMonthDate->format('y');
        $currentYearShort = $currentMonthDate->format('y');

        $weekLeaf = function ($prefix, $i) use ($bNama, $bNo, $wc, &$colIdx) {
            $title = $bNama . '-W' . $i
                . '<br><small class="mk-week-label" data-bulan="' . $bNo . '" data-week="w' . $i . '" title="Klik untuk ubah tanggal">'
                . '(' . $wc['w' . $i . '_start'] . '-' . $wc['w' . $i . '_end'] . ')</small>'
                . '<span class="mk-colnum">' . $colIdx++ . '</span>';
            return ['title' => $title, 'titleFormatter' => 'html', 'field' => $prefix . $i, 'minWidth' => 84];
        };
        $totalLeaf = function ($field) use ($bNama, &$colIdx) {
            $title = 'Weekly-' . $bNama . '<br><small>(1-31)</small><span class="mk-colnum">' . $colIdx++ . '</span>';
            return ['title' => $title, 'titleFormatter' => 'html', 'field' => $field, 'minWidth' => 92, 'cssClass' => 'mk-c-bold'];
        };

        $mkCols[] = ['title' => 'Permintaan Weekly-' . $bNama, 'cssClass' => 'mk-h-p', 'columns' => [
            array_merge($weekLeaf("m{$bNo}_p", 1), ['cssClass' => 'mk-h-p']),
            array_merge($weekLeaf("m{$bNo}_p", 2), ['cssClass' => 'mk-h-p']),
            array_merge($weekLeaf("m{$bNo}_p", 3), ['cssClass' => 'mk-h-p']),
            array_merge($weekLeaf("m{$bNo}_p", 4), ['cssClass' => 'mk-h-p']),
            array_merge($totalLeaf("m{$bNo}_pt"), ['cssClass' => 'mk-h-p mk-c-bold']),
        ]];
        $mkCols[] = ['title' => 'Dropping Weekly-' . $bNama, 'cssClass' => 'mk-h-d', 'columns' => [
            array_merge($weekLeaf("m{$bNo}_d", 1), ['cssClass' => 'mk-h-d']),
            array_merge($weekLeaf("m{$bNo}_d", 2), ['cssClass' => 'mk-h-d']),
            array_merge($weekLeaf("m{$bNo}_d", 3), ['cssClass' => 'mk-h-d']),
            array_merge($weekLeaf("m{$bNo}_d", 4), ['cssClass' => 'mk-h-d']),
            array_merge($totalLeaf("m{$bNo}_dt"), ['cssClass' => 'mk-h-d mk-c-bold']),
        ]];
        $mkCols[] = ['title' => 'Pembayaran Weekly-' . $bNama, 'cssClass' => 'mk-h-b', 'columns' => [
            array_merge($weekLeaf("m{$bNo}_b", 1), ['cssClass' => 'mk-h-b']),
            array_merge($weekLeaf("m{$bNo}_b", 2), ['cssClass' => 'mk-h-b']),
            array_merge($weekLeaf("m{$bNo}_b", 3), ['cssClass' => 'mk-h-b']),
            array_merge($weekLeaf("m{$bNo}_b", 4), ['cssClass' => 'mk-h-b']),
            array_merge($totalLeaf("m{$bNo}_bt"), ['cssClass' => 'mk-h-b mk-c-bold']),
        ]];

        $mkCols[] = ['title' => 'SALDO MODAL KERJA<br>Per ' . $bNama . ' ' . $tahun, 'titleFormatter' => 'html',
            'field' => "m{$bNo}_s", 'cssClass' => 'mk-h-s mk-c-bold', 'minWidth' => 100];
        $mkCols[] = ['title' => 'SALDO<br>MOKER SD<br>' . $prevShort . ' ' . $prevYearShort
            . '<br><small><i>Per ' . $previousMonthDate->endOfMonth()->format('d') . ' ' . $prevShort . ' ' . $prevYearShort . '</i></small>',
            'titleFormatter' => 'html', 'field' => "m{$bNo}_sprev", 'cssClass' => 'mk-h-sprev mk-c-bold', 'minWidth' => 100];
        $mkCols[] = ['title' => 'PENGGUNAAN<br>MODAL<br>SENDIRI<br><small><i>' . $bNama . ' ' . $tahun . '</i></small>',
            'titleFormatter' => 'html', 'field' => "m{$bNo}_ms", 'cssClass' => 'mk-h-ms mk-c-bold', 'minWidth' => 110];
        $mkCols[] = ['title' => 'SALDO MOKER<br>SD ' . $currentShort . ' ' . $currentYearShort
            . '<br><small><i>Per ' . $currentMonthDate->endOfMonth()->format('d') . ' ' . $currentShort . ' ' . $currentYearShort . '</i></small>',
            'titleFormatter' => 'html', 'field' => "m{$bNo}_snow", 'cssClass' => 'mk-h-snow mk-c-bold', 'minWidth' => 100];
    }
@endphp

<div id="mk-table"></div>

<script>
(function () {
    var mkRows = @json($mkRows);
    var mkCols = @json($mkCols);

    // Format ala fmtMK: item 0 -> '-', lainnya 0 -> '0'; negatif -> (x);
    // saldo negatif diberi warna merah; string ('-') diteruskan apa adanya.
    function mkFmt(cell) {
        var v = cell.getValue();
        if (v === null || v === undefined || v === '') return '';
        if (typeof v === 'string') return v;
        v = Number(v);
        var d = cell.getRow().getData();
        if (v === 0) return d.type === 'item' ? '-' : '0';
        var s = Math.round(Math.abs(v)).toLocaleString('id-ID');
        if (v < 0) {
            if (cell.getField().slice(-2) === '_s') cell.getElement().classList.add('mk-neg');
            return '(' + s + ')';
        }
        return s;
    }

    // Pasang formatter & perataan pada semua kolom nilai (rekursif)
    function decorate(cols) {
        cols.forEach(function (c) {
            if (c.columns) { decorate(c.columns); return; }
            if (c.field && c.field !== 'no' && c.field !== 'uraian') {
                c.formatter = mkFmt;
                c.hozAlign = 'right';
                c.headerHozAlign = 'center';
                c.widthGrow = 1;
            }
        });
    }
    decorate(mkCols);

    var mkTypes = ['kategori', 'sub', 'item', 'subtotal', 'kattotal', 'grandtotal',
        'sumplain', 'sumgreen', 'sumyellow', 'sumcyan', 'sumorange', 'sumsoft'];

    function mkRowFormatter(row) {
        var t = row.getData().type;
        var el = row.getElement();
        mkTypes.forEach(function (k) {
            el.classList.toggle('mk-r-' + k, t === k);
        });
    }

    function initMk() {
        var el = document.getElementById('mk-table');
        if (!el || !window.Tabulator) return;

        var table = new Tabulator(el, {
            data: mkRows,
            columns: mkCols,
            layout: 'fitColumns',
            height: 'calc(100vh - 185px)',
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            columnDefaults: { headerSort: false },
            rowFormatter: mkRowFormatter,
            placeholder: 'Tidak ada data'
        });

        // Aktifkan tahan-klik geser horizontal (handler global layouts/index)
        table.on('tableBuilt', function () {
            var holder = el.querySelector('.tabulator-tableholder');
            if (holder) holder.classList.add('drag-scroll');
        });
    }

    if (window.Tabulator) {
        initMk();
    } else {
        document.addEventListener('DOMContentLoaded', initMk);
    }
})();
</script>

@endif
