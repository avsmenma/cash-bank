@extends("layouts/index")
@section('content')

<div class="container-fluid mt-4">
    <section class="content-header cb-fullscreen-hide">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="font-weight:700;color:#0d3b6e;">
                        <i class="fas fa-landmark mr-2"></i>Rekening Koran
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.pembayaran.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Rekening Koran</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        {{-- FILTER BAR --}}
        <div class="card shadow mb-3 cb-fullscreen-hide" style="border-top:4px solid #0d3b6e;">
            <div class="card-body py-3">
                <form action="{{ route('bank-keluar.report') }}" method="GET" id="filterForm">
                    <div class="row align-items-end g-2">
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Tahun</label>
                            <select name="tahun" class="form-control form-control-sm" onchange="submitForm()" style="min-width:80px;">
                                @foreach($tahunList as $t)
                                    <option value="{{ $t }}" {{ (string) $tahun === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Bulan</label>
                            <select name="bulan" class="form-control form-control-sm" onchange="submitForm()" style="min-width:110px;">
                                <option value="">Semua Bulan</option>
                                @foreach($bulanList as $b)
                                    <option value="{{ $b }}" {{ (string) $bulan === (string) $b ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Sumber Dana</label>
                            <select name="sumber_dana[]" class="form-control form-control-sm" onchange="submitForm()" style="min-width:180px;">
                                <option value="">Semua Sumber Dana</option>
                                @foreach($sumberDanaList as $sd)
                                    <option value="{{ $sd->id_sumber_dana }}" {{ collect($sumberDanaIds ?? [])->contains($sd->id_sumber_dana) ? 'selected' : '' }}>
                                        {{ $sd->nama_sumber_dana }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Dari Tanggal</label>
                            <input type="date" name="tgl_dari" class="form-control form-control-sm"
                                   value="{{ request('tgl_dari') }}" onchange="submitForm()">
                        </div>
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Sampai Tanggal</label>
                            <input type="date" name="tgl_sampai" class="form-control form-control-sm"
                                   value="{{ request('tgl_sampai') }}" onchange="submitForm()">
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilter()">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                        <div class="col-auto ml-auto">
                            <a href="{{ route('bank-keluar.report_export_excel', request()->all()) }}"
                               class="btn btn-sm btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE (Tabulator, muat bertahap saat scroll) --}}
        <div class="card shadow cb-fullscreen-table" style="border:none;">
            <div class="d-flex align-items-center justify-content-end px-3 pt-3 pb-2 cb-fullscreen-hide">
                <div id="rkEntriesInfo" class="small text-secondary">Memuat data...</div>
            </div>
            <div class="card-body p-0">
                <div id="rkTable"></div>
                {{-- Footer TOTAL (nilai dari server, konsisten dgn versi lama) --}}
                <div class="rk-total-bar">
                    <span class="rk-total-label">TOTAL</span>
                    <span>Debet: <b id="rkTotDebet">{{ number_format($totalDebet ?? 0, 0, ',', '.') }}</b></span>
                    <span>Kredit: <b id="rkTotKredit">{{ number_format($totalKredit ?? 0, 0, ',', '.') }}</b></span>
                    <span>Saldo Akhir: <b id="rkTotSaldo">@if($finalSaldo !== null){{ $finalSaldo < 0 ? '(' . number_format(abs($finalSaldo), 0, ',', '.') . ')' : number_format($finalSaldo, 0, ',', '.') }}@else - @endif</b></span>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* REKENING KORAN — Tabulator ala spreadsheet */
#rkTable { border: 1px solid #d0dce8; font-size: 11.5px; font-family: 'Segoe UI', system-ui, sans-serif; }
#rkTable .tabulator-header { background: #0d3b6e; border-bottom: 2px solid #082948; }
#rkTable .tabulator-header .tabulator-col {
    background: #0d3b6e !important; color: #fff !important; font-weight: 600; font-size: 11.5px;
    border-color: #ffffff !important; border-right: 1px solid #ffffff !important;
}
#rkTable .tabulator-header .tabulator-col .tabulator-col-title { color: #fff; text-align: center; }
#rkTable .tabulator-header .tabulator-col-resize-handle { width: 7px; cursor: col-resize; background: none; }
#rkTable .tabulator-header .tabulator-col:not(.tabulator-col-group) > .tabulator-col-resize-handle {
    background: linear-gradient(to bottom, transparent 32%, rgba(255,255,255,.45) 32%, rgba(255,255,255,.45) 68%, transparent 68%)
        center / 2px 100% no-repeat;
}
#rkTable .tabulator-header .tabulator-col-resize-handle:hover { background: rgba(255,255,255,.3); }
#rkTable .tabulator-cell { border-right: 1px solid #c3d2e0; border-color: #c3d2e0; }
#rkTable .tabulator-row { border-bottom: 1px solid #c3d2e0; }
#rkTable .tabulator-row:hover .tabulator-cell { background: #f5f8fc; }

#rkTable .rk-wrap { white-space: normal !important; overflow-wrap: anywhere; line-height: 1.35; }
#rkTable .rk-debet { color: #1a7a3d; font-weight: 600; }
#rkTable .rk-kredit { color: #c0392b; font-weight: 600; }
#rkTable .rk-saldo { color: #0d3b6e; font-weight: 700; }

.rk-total-bar {
    display: flex; gap: 26px; align-items: center; justify-content: flex-end;
    background: #1b4f72; color: #fff; font-size: 12.5px; padding: 9px 14px;
}
.rk-total-bar .rk-total-label { margin-right: auto; font-weight: 700; letter-spacing: .4px; }
.rk-total-bar #rkTotDebet { color: #a9dfbf; }
.rk-total-bar #rkTotKredit { color: #f1948a; }
.rk-total-bar #rkTotSaldo { color: #fad7a0; }

@media print { #rkTable .tabulator-tableholder { overflow: visible !important; max-height: none !important; } }
</style>

<script>
function submitForm()  { document.getElementById('filterForm').submit(); }
function resetFilter() {
    const tahun = document.querySelector('[name="tahun"]')?.value;
    window.location.href = '{{ route("bank-keluar.report") }}?tahun=' + (tahun || '');
}
</script>

@push('scripts')
<script>
(function () {
    var reportParams = @json(request()->query());
    var rkTotal = null;

    function init() {
        var el = document.getElementById('rkTable');
        if (!el || !window.Tabulator) return;

        function col(title, field, opts) {
            return Object.assign({ title: title, field: field, headerHozAlign: 'center', minWidth: 90, widthGrow: 1, headerSort: false }, opts || {});
        }

        var table = new Tabulator(el, {
            layout: 'fitColumns',
            height: '62vh',
            columnHeaderVertAlign: 'middle',
            movableColumns: false,
            placeholder: 'Tidak ada data untuk filter yang dipilih.',
            columns: [
                col('No', 'no', { width: 56, hozAlign: 'center' }),
                col('No Agenda', 'no_agenda', { minWidth: 110 }),
                col('Tanggal', 'tanggal', { hozAlign: 'center', minWidth: 100 }),
                col('No Bukti', 'no_sap', { minWidth: 105 }),
                col('Sumber Dana', 'nama_sumber_dana', { minWidth: 200, widthGrow: 2, cssClass: 'rk-wrap', variableHeight: true, formatter: 'html' }),
                col('Penerima / Dari', 'penerima', { minWidth: 150, widthGrow: 2, cssClass: 'rk-wrap', variableHeight: true, formatter: 'html' }),
                col('Uraian', 'uraian', { minWidth: 320, widthGrow: 4, cssClass: 'rk-wrap', variableHeight: true, formatter: 'html', tooltip: true }),
                col('Debet', 'debet', { hozAlign: 'right', minWidth: 115, cssClass: 'rk-debet' }),
                col('Kredit', 'kredit', { hozAlign: 'right', minWidth: 115, cssClass: 'rk-kredit' }),
                col('Saldo Akhir', 'saldo_akhir', { hozAlign: 'right', minWidth: 120, cssClass: 'rk-saldo' })
            ],

            // Muat bertahap 100 baris saat scroll — endpoint lama (start/length) tetap dipakai
            progressiveLoad: 'scroll',
            paginationSize: 100,
            ajaxURL: @json(route('bank-keluar.report.data')),
            ajaxURLGenerator: function (url, config, params) {
                var size = params.size || 100;
                var usp = new URLSearchParams();
                Object.keys(reportParams).forEach(function (k) {
                    var v = reportParams[k];
                    if (Array.isArray(v)) { v.forEach(function (x) { usp.append(k + '[]', x); }); }
                    else if (v !== null && v !== undefined) { usp.append(k, v); }
                });
                usp.append('draw', params.page);
                usp.append('start', (params.page - 1) * size);
                usp.append('length', size);
                return url + '?' + usp.toString();
            },
            ajaxResponse: function (url, params, response) {
                if (response && response.totals) {
                    document.getElementById('rkTotDebet').textContent = response.totals.debet;
                    document.getElementById('rkTotKredit').textContent = response.totals.kredit;
                    document.getElementById('rkTotSaldo').textContent = response.totals.saldo;
                }
                rkTotal = parseInt((response && response.recordsFiltered) || 0, 10);
                var size = params.size || 100;
                return {
                    last_page: Math.max(1, Math.ceil(rkTotal / size)),
                    data: (response && response.data) || []
                };
            },

            rowFormatter: function (row) {
                var cls = row.getData().DT_RowClass;
                if (cls) row.getElement().classList.add(cls);
            }
        });

        function updateInfo() {
            var info = document.getElementById('rkEntriesInfo');
            if (!info || rkTotal === null) return;
            var loaded = table.getDataCount();
            if (rkTotal === 0) info.textContent = 'Tidak ada data.';
            else if (loaded >= rkTotal) info.textContent = 'Semua ' + rkTotal.toLocaleString('id-ID') + ' data telah dimuat.';
            else info.textContent = loaded.toLocaleString('id-ID') + ' dari ' + rkTotal.toLocaleString('id-ID') + ' data dimuat — scroll ke bawah untuk memuat berikutnya.';
        }
        table.on('dataProcessed', updateInfo);
        table.on('renderComplete', updateInfo);
    }

    if (window.Tabulator) {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }
})();
</script>
@endpush

@endsection
