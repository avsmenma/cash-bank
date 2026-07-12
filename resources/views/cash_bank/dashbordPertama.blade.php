<style>
    /* ============================================================
       CASHFLOW — Tabulator (tabel ala spreadsheet seperti project LM).
       Kolom bisa ditarik-lebarkan manual lewat garis antar kolom header.
       ============================================================ */
    #cashflow-table {
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        font-size: 12px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    #cashflow-table .tabulator-header {
        background: #1e3a5f;
        border-bottom: 2px solid #0f2137;
    }
    #cashflow-table .tabulator-header .tabulator-col,
    #cashflow-table .tabulator-header .tabulator-col-group {
        background: #1e3a5f !important;
        color: #f0f4f8 !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-size: 11px;
        /* garis header putih; border-right dipasang eksplisit karena tema
           tidak memberi garis pada elemen grup (batas antar bulan) */
        border-color: #ffffff !important;
        border-right: 1px solid #ffffff !important;
    }
    /* Kolom terakhir di dalam grup tidak perlu garis sendiri —
       garis batas sudah dipegang grup induknya (hindari garis dobel) */
    #cashflow-table .tabulator-header .tabulator-col-group-cols > .tabulator-col:last-child {
        border-right: none !important;
    }
    #cashflow-table .tabulator-header .tabulator-col .tabulator-col-title {
        color: #f0f4f8;
        text-align: center;
    }

    /* Penanda kolom bisa ditarik-lebarkan: takik putih HANYA di kolom
       terbawah (bukan di tiap tingkat grup, agar header tidak ramai) */
    #cashflow-table .tabulator-header .tabulator-col-resize-handle {
        width: 6px;
        cursor: col-resize;
        background: none;
    }
    #cashflow-table .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
        background: linear-gradient(
            to bottom,
            transparent 32%,
            rgba(255, 255, 255, 0.45) 32%,
            rgba(255, 255, 255, 0.45) 68%,
            transparent 68%
        );
        background-size: 2px 100%;
        background-position: center;
        background-repeat: no-repeat;
    }
    #cashflow-table .tabulator-header .tabulator-col-resize-handle:hover {
        background: rgba(255, 255, 255, 0.30);
    }

    /* Garis kolom & baris tegas agar mudah ditelusuri */
    #cashflow-table .tabulator-cell {
        border-right: 1px solid #b3bfcc;
        border-color: #b3bfcc;
    }
    #cashflow-table .tabulator-row {
        border-bottom: 1px solid #b3bfcc;
    }

    /* ---------- Jenis baris (diberi kelas oleh rowFormatter) ---------- */
    #cashflow-table .tabulator-row.cf-r-section .tabulator-cell {
        background: rgba(30, 58, 95, 0.07);
        color: #1e3a5f;
        font-weight: 700;
        letter-spacing: .5px;
    }
    #cashflow-table .tabulator-row.cf-r-kat .tabulator-cell {
        background: #f0f4f8;
        color: #1e3a5f;
        font-weight: 700;
    }
    #cashflow-table .tabulator-row.cf-r-kat .tabulator-cell:first-child {
        border-left: 3px solid #3b82f6;
    }
    #cashflow-table .tabulator-row.cf-r-item .tabulator-cell {
        background: #ffffff;
        color: #344054;
    }
    #cashflow-table .tabulator-row.cf-r-item:hover .tabulator-cell {
        background: #f8fafc;
    }
    #cashflow-table .tabulator-row.cf-r-subtotal .tabulator-cell,
    #cashflow-table .tabulator-row.cf-r-gaji .tabulator-cell {
        background: #eef2f7;
        color: #1e3a5f;
        font-weight: 600;
        border-top: 1px solid #cbd5e1;
    }
    #cashflow-table .tabulator-row.cf-r-total .tabulator-cell {
        background: #1e3a5f;
        color: #ffffff;
        font-weight: 700;
    }
    #cashflow-table .tabulator-row.cf-r-selisih .tabulator-cell {
        background: #fef3c7;
        color: #1a1a1a;
        font-weight: 600;
    }
    #cashflow-table .tabulator-row.cf-r-selisih .tabulator-cell:first-child {
        border-left: 3px solid #f59e0b;
    }
    #cashflow-table .tabulator-row.cf-r-summary .tabulator-cell {
        background: #f1f5f9;
        color: #1a1a1a;
        font-weight: 600;
        border-top: 1px solid #cbd5e1;
    }
    #cashflow-table .tabulator-cell.cf-neg {
        color: #dc2626 !important;
        font-weight: 600;
    }
    #cashflow-table .tabulator-cell.cf-col-total {
        font-weight: 700;
    }

    @media print {
        #cashflow-table .tabulator-tableholder {
            overflow: visible !important;
            max-height: none !important;
        }
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
    // Aggregate Permintaan, Dropping & Pembayaran ke level [kategori][sub_kriteria][bulan]
    // ================================================================
    $permAgg = [];
    foreach (($result['permintaan'] ?? []) as $item) {
        $kat = $item['kategori']; $sub = $item['sub_kriteria'];
        foreach ($bulanListFiltered as $b => $n) {
            $permAgg[$kat][$sub][$b] = ($permAgg[$kat][$sub][$b] ?? 0) + ($item['data'][$b] ?? 0);
        }
    }

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
    // Urutan kategori untuk Permintaan, Dropping & Pembayaran
    // ================================================================
    $katOrder = [
        'Kebutuhan Gaji, Upah dan Tunjangan'           => 1,
        'Kebutuhan Pembayaran Aktivitas Ekploitasi'     => 2,
        'Payment Requirement for Exploitation Activity' => 2,
        'Kebutuhan Pekerjaan Aktivitas Investasi'       => 3,
        'Kebutuhan Pembayaran Pekerjaan Aktivitas Investasi' => 3,
    ];
    // Kategori yang digrup ditampilkan sebagai 1 baris total saja.
    // Gaji harus dibuka detailnya supaya Karyawan Pimpinan, Karyawan Pelaksana,
    // dan Gaji Honor dari menu Dropping tampil seperti kategori lainnya.
    $groupedKat = [];

    // Urutan sub_kriteria dalam tiap kategori
    $subOrder = [
        'Karyawan Pimpinan'           => 1,
        'Karyawan Pelaksana'          => 2,
        'Gaji Honor'                  => 3,
        'Pembelian TBS'               => 1,
        'Purchase Volume'             => 1,
        'Biaya Usaha dan Lainnya'     => 2,
        'Biaya Usaha dan lainnya'     => 2,
        'Pajak'                       => 3,
        'Operasional Produksi'        => 4,
        'Investasi On Farm'           => 1,
        'Investasi Off Farm'          => 2,
        'Pembayaran investasi lainnya'=> 3,
    ];

    // Sort kategori
    foreach ([&$permAgg, &$dropAgg, &$payAgg] as &$agg) {
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

    $permAgg = \App\Support\DashboardKriteriaHierarchy::sortCategorySub($permAgg);
    $dropAgg = \App\Support\DashboardKriteriaHierarchy::sortCategorySub($dropAgg);
    $payAgg = \App\Support\DashboardKriteriaHierarchy::sortCategorySub($payAgg);

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
    $totalPermAll   = []; foreach ($bulanListFiltered as $b => $n) $totalPermAll[$b] = 0;
    foreach ($permAgg as $kat => $subs) {
        foreach ($subs as $sub => $bData) {
            foreach ($bulanListFiltered as $b => $n) $totalPermAll[$b] += ($bData[$b] ?? 0);
        }
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

{{-- ================================================================
     TAB BAR: filter tampilan per bagian.
     Tab SEMUA menampilkan kondisi lengkap (termasuk baris SELISIH dan
     ringkasan kuning yang membandingkan antar bagian); tab lain hanya
     menampilkan tabel bagiannya masing-masing.
     ================================================================ --}}
<style>
    .cf-tabbar {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }
    .cf-tabbar .cf-tab {
        border: 1px solid #d0d5dd;
        background: #fff;
        color: #344054;
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s ease;
    }
    .cf-tabbar .cf-tab:hover { background: #f0f4f8; }
    .cf-tabbar .cf-tab.active {
        background: #1e3a5f;
        border-color: #1e3a5f;
        color: #fff;
    }
</style>
<div class="row">
<div class="col-12">
    <div class="cf-tabbar" id="cfTabBar">
        <button type="button" class="cf-tab active" data-cf-tab="semua">Semua</button>
        <button type="button" class="cf-tab" data-cf-tab="penerimaan">Penerimaan</button>
        <button type="button" class="cf-tab" data-cf-tab="permintaan">Permintaan</button>
        <button type="button" class="cf-tab" data-cf-tab="dropping">Dropping HO</button>
        <button type="button" class="cf-tab" data-cf-tab="pembayaran">Pembayaran</button>
    </div>
</div>
</div>
@php
    // ================================================================
    // Susun baris tabel sebagai DATA untuk Tabulator (menggantikan
    // tabel HTML lama). Semua perhitungan di atas tetap dipakai.
    // ================================================================
    $cfRows = [];
    $cfPush = function ($section, $type, $uraian, $vals = null, $total = null) use (&$cfRows, $bulanListFiltered) {
        $row = ['section' => $section, 'type' => $type, 'uraian' => $uraian, 'total' => $total];
        foreach ($bulanListFiltered as $b => $n) {
            $row['m' . $b] = $vals === null ? null : ($vals[$b] ?? 0);
        }
        $cfRows[] = $row;
    };

    // Satu bagian (Permintaan/Dropping/Pembayaran): kategori -> sub -> jumlah
    $cfBagian = function ($agg, $sectionKey) use ($cfPush, $sumKat, $groupedKat, $bulanListFiltered) {
        foreach ($agg as $kat => $subs) {
            if (in_array($kat, $groupedKat)) {
                $katTot = $sumKat($agg, $kat, $bulanListFiltered);
                $cfPush($sectionKey, 'gaji', $kat, $katTot, array_sum($katTot));
            } else {
                $cfPush($sectionKey, 'kat', $kat);
                $subTotB = [];
                foreach ($bulanListFiltered as $b => $n) $subTotB[$b] = 0;
                $subAll = 0;
                foreach ($subs as $sub => $bData) {
                    $vals = [];
                    $rt = 0;
                    foreach ($bulanListFiltered as $b => $n) {
                        $v = $bData[$b] ?? 0;
                        $vals[$b] = $v;
                        $rt += $v;
                        $subTotB[$b] += $v;
                    }
                    $subAll += $rt;
                    $cfPush($sectionKey, 'item', '- ' . $sub, $vals, $rt);
                }
                $cfPush($sectionKey, 'subtotal', 'Jumlah ' . $kat, $subTotB, $subAll);
            }
        }
    };

    // ---------- PENERIMAAN ----------
    $cfPush('penerimaan', 'section', 'PENERIMAAN');
    foreach ($result['penerima'] as $kat => $bData) {
        $vals = [];
        $rowTotal = 0;
        foreach ($bulanListFiltered as $b => $n) { $v = $bData[$b] ?? 0; $vals[$b] = $v; $rowTotal += $v; }
        $cfPush('penerimaan', 'item', '- ' . $kat, $vals, $rowTotal);
    }
    $cfPush('penerimaan', 'total', 'TOTAL PENERIMAAN', $totalPenerima, array_sum($totalPenerima));

    // ---------- PERMINTAAN ----------
    $cfPush('permintaan', 'section', 'PERMINTAAN');
    $cfBagian($permAgg, 'permintaan');
    $cfPush('permintaan', 'total', 'TOTAL PERMINTAAN', $totalPermAll, array_sum($totalPermAll));

    // ---------- DROPPING HO ----------
    $cfPush('dropping', 'section', 'DROPPING HO');
    $cfBagian($dropAgg, 'dropping');
    $cfPush('dropping', 'total', 'TOTAL DROPPING HO', $totalDropAll, array_sum($totalDropAll));

    // ---------- SELISIH & RINGKASAN (hanya tab Semua) ----------
    $selPD = [];
    foreach ($bulanListFiltered as $b => $n) $selPD[$b] = $totalPenerima[$b] - $totalDropAll[$b];
    $cfPush('semua-only', 'selisih', 'SELISIH PENERIMAAN TERHADAP DROPPING', $selPD, array_sum($selPD));

    // ---------- PEMBAYARAN ----------
    $cfPush('pembayaran', 'section', 'PEMBAYARAN');
    $cfBagian($payAgg, 'pembayaran');
    $cfPush('pembayaran', 'total', 'TOTAL PEMBAYARAN', $totalPayAll, array_sum($totalPayAll));

    $selPP = [];
    foreach ($bulanListFiltered as $b => $n) $selPP[$b] = $totalPenerima[$b] - $totalPayAll[$b];
    $cfPush('semua-only', 'selisih', 'SELISIH PEMBAYARAN TERHADAP PENERIMAAN', $selPP, array_sum($selPP));

    $selDP = [];
    foreach ($bulanListFiltered as $b => $n) $selDP[$b] = $totalDropAll[$b] - $totalPayAll[$b];
    $cfPush('semua-only', 'selisih', 'SELISIH PEMBAYARAN TERHADAP DROPPING', $selDP, array_sum($selDP));

    $cfPush('semua-only', 'summary', 'PENERIMAAN PENJUALAN CPO & KERNEL', $cpnKernel, array_sum($cpnKernel));
    $cfPush('semua-only', 'summary', 'DROPPING TBS', $dropTBS, array_sum($dropTBS));
    $cfPush('semua-only', 'summary', 'PEMBAYARAN TBS', $payTBS, array_sum($payTBS));

    // Definisi kolom bulan untuk Tabulator
    $cfMonths = [];
    foreach ($bulanListFiltered as $b => $nm) $cfMonths[] = ['field' => 'm' . $b, 'title' => $nm];
@endphp

<div class="row">
<div class="col-12">
    <div id="cashflow-table"></div>
</div>
</div>

<script>
(function () {
    var cfRows = @json($cfRows);
    var cfMonths = @json($cfMonths);
    var cfTitleTahun = @json('TAHUN ' . $tahun);
    var cfTitleTotal = @json('Sd ' . collect($bulanListFiltered)->last() . ' Tahun ' . $tahun);

    // Format angka: 0 -> '-' (baris item/selisih), negatif -> (x) + merah utk selisih
    function cfFormatter(cell) {
        var v = cell.getValue();
        if (v === null || v === undefined || v === '') return '';
        v = Number(v);
        var d = cell.getRow().getData();
        if (v === 0) return (d.type === 'item' || d.type === 'selisih') ? '-' : '0';
        var s = Math.round(Math.abs(v)).toLocaleString('id-ID');
        if (v < 0 && d.type === 'selisih') cell.getElement().classList.add('cf-neg');
        return v < 0 ? '(' + s + ')' : s;
    }

    function buildColumns() {
        var monthCols = cfMonths.map(function (m) {
            return {
                title: m.title,
                field: m.field,
                hozAlign: 'right',
                width: 105,
                widthGrow: 1,
                formatter: cfFormatter,
                headerHozAlign: 'center'
            };
        });
        return [
            { title: 'URAIAN', field: 'uraian', frozen: true, width: 260, widthGrow: 2, headerHozAlign: 'center' },
            { title: cfTitleTahun, columns: monthCols },
            {
                title: cfTitleTotal,
                field: 'total',
                hozAlign: 'right',
                width: 130,
                widthGrow: 1,
                formatter: cfFormatter,
                headerHozAlign: 'center',
                cssClass: 'cf-col-total'
            }
        ];
    }

    // Kelas per jenis baris untuk pewarnaan (pola rowFormatter project LM)
    function cfRowFormatter(row) {
        var t = row.getData().type;
        var el = row.getElement();
        ['section', 'kat', 'item', 'subtotal', 'gaji', 'total', 'selisih', 'summary'].forEach(function (k) {
            el.classList.toggle('cf-r-' + k, t === k);
        });
    }

    var cfCurrentTab = 'semua';

    function initCF() {
        var el = document.getElementById('cashflow-table');
        if (!el || !window.Tabulator) return;

        // Bila user sudah pernah menarik kolom, pakai lebar simpanannya apa
        // adanya (fitData); bila belum, kolom otomatis mengisi lebar wadah.
        var userSized = !!localStorage.getItem('tabulator-cb-cashflow-pd-columns');

        var table = new Tabulator(el, {
            persistence: { columns: ['width'] },   // lebar kolom tarikan user tersimpan permanen (localStorage)
            persistenceID: 'cb-cashflow-pd',
            data: cfRows,
            columns: buildColumns(),
            layout: userSized ? 'fitData' : 'fitColumns',
            height: '68vh',                 // header otomatis menempel saat scroll
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            columnDefaults: { headerSort: false, minWidth: 30 },   // kolom bebas dikecilkan sampai 30px
            rowFormatter: cfRowFormatter,
            placeholder: 'Tidak ada data'
        });

        // Tab bar: filter baris per bagian (Semua menampilkan seluruhnya)
        var tabBar = document.getElementById('cfTabBar');
        if (tabBar) {
            tabBar.addEventListener('click', function (e) {
                var btn = e.target.closest('.cf-tab');
                if (!btn) return;
                cfCurrentTab = btn.getAttribute('data-cf-tab');
                tabBar.querySelectorAll('.cf-tab').forEach(function (b) {
                    b.classList.toggle('active', b === btn);
                });
                if (cfCurrentTab === 'semua') {
                    table.clearFilter(true);
                } else {
                    table.setFilter(function (data) {
                        return data.section === cfCurrentTab;
                    });
                }
            });
        }
    }

    // Saat render penuh halaman, script ini jalan sebelum Tabulator dimuat;
    // saat dimuat via AJAX, Tabulator sudah tersedia dan langsung jalan.
    if (window.Tabulator) {
        initCF();
    } else {
        document.addEventListener('DOMContentLoaded', initCF);
    }
})();
</script>
