@extends("layouts/index")
@section('content')

<div class="container-fluid mt-4">
    <section class="content-header">
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
        <div class="card shadow mb-3" style="border-top:4px solid #0d3b6e;">
            <div class="card-body py-3">
                <form action="{{ route('bank-keluar.report') }}" method="GET" id="filterForm">
                    <div class="row align-items-end g-2">
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Tahun</label>
                            <select name="tahun" class="form-control form-control-sm" onchange="submitForm()" style="min-width:80px;">
                                @foreach($tahunList as $t)
                                    <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Bulan</label>
                            <select name="bulan" class="form-control form-control-sm" onchange="submitForm()" style="min-width:110px;">
                                <option value="">Semua Bulan</option>
                                @foreach($bulanList as $b)
                                    <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="mb-1 small font-weight-bold text-secondary">Bank Tujuan</label>
                            <select name="bank_tujuan" class="form-control form-control-sm" onchange="submitForm()" style="min-width:160px;">
                                <option value="">Semua Bank Tujuan</option>
                                @foreach($bankTujuanList as $bank)
                                    <option value="{{ $bank->id_bank_tujuan }}" {{ request('bank_tujuan') == $bank->id_bank_tujuan ? 'selected' : '' }}>
                                        {{ $bank->nama_tujuan }}
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

        {{-- TABLE --}}
        <div class="card shadow" style="border:none;">
            {{-- Show Entries + Info --}}
            <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2">
                <div class="d-flex align-items-center">
                    <label class="mb-0 small font-weight-bold text-secondary mr-2">Tampilkan</label>
                    <select id="showEntriesSelect" class="form-control form-control-sm" style="width:90px;" onchange="changePageSize()">
                        <option value="10" selected>10</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="all">Semua</option>
                    </select>
                    <label class="mb-0 small font-weight-bold text-secondary ml-2">entri</label>
                </div>
                <div id="rkEntriesInfo" class="small text-secondary"></div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered mb-0" id="rkTable">
                    <thead>
                        <tr>
                            <th class="rk-th text-center">No</th>
                            <th class="rk-th text-center">No Agenda</th>
                            <th class="rk-th text-center">Tanggal</th>
                            <th class="rk-th text-center">No Bukti</th>
                            <th class="rk-th text-center">Bank Tujuan</th>
                            <th class="rk-th text-center">Penerima / Dari</th>
                            <th class="rk-th text-center">Uraian</th>
                            <th class="rk-th text-center">Debet</th>
                            <th class="rk-th text-center">Kredit</th>
                            <th class="rk-th text-center">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = 1;
                            $tglDari   = request('tgl_dari');
                            $tglSampai = request('tgl_sampai');
                        @endphp
                        @forelse($data as $row)
                            @php
                                if ($tglDari && $row->tanggal < $tglDari) continue;
                                if ($tglSampai && $row->tanggal > $tglSampai) continue;
                                $isDebet = ($row->jenis ?? '') === 'MASUK';
                                $saldo = $row->saldo_akhir ?? 0;
                            @endphp
                            <tr class="{{ $isDebet ? 'rk-row-debet' : 'rk-row-kredit' }}">
                                <td class="text-center rk-td">{{ $no++ }}</td>
                                <td class="rk-td">{{ $row->no_agenda ?? '-' }}</td>
                                <td class="text-center rk-td">
                                    {{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="rk-td">{{ $row->no_sap ?? '-' }}</td>
                                <td class="rk-td">{{ $row->nama_tujuan ?? '-' }}</td>
                                <td class="rk-td">{{ $row->penerima ?? '-' }}</td>
                                <td class="rk-td rk-uraian" title="{{ $row->uraian }}">{{ $row->uraian ?? '-' }}</td>
                                <td class="text-right rk-td rk-debet">
                                    {{ $row->debet > 0 ? number_format($row->debet, 0, ',', '.') : '' }}
                                </td>
                                <td class="text-right rk-td rk-kredit">
                                    {{ $row->kredit > 0 ? number_format($row->kredit, 0, ',', '.') : '' }}
                                </td>
                                <td class="text-right rk-td rk-saldo {{ $saldo < 0 ? 'rk-neg' : '' }}">
                                    @if($saldo !== null)
                                        {{ $saldo < 0
                                            ? '(' . number_format(abs($saldo), 0, ',', '.') . ')'
                                            : number_format($saldo, 0, ',', '.') }}
                                    @else -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    Tidak ada data untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($data) > 0)
                    <tfoot>
                        <tr class="rk-footer">
                            <td colspan="7" class="text-right font-weight-bold">TOTAL</td>
                            <td class="text-right font-weight-bold rk-debet">
                                {{ number_format($data->sum('debet'), 0, ',', '.') }}
                            </td>
                            <td class="text-right font-weight-bold rk-kredit">
                                {{ number_format($data->sum('kredit'), 0, ',', '.') }}
                            </td>
                            <td class="text-right font-weight-bold rk-saldo">
                                @php $finalSaldo = $data->last()->saldo_akhir ?? 0; @endphp
                                {{ $finalSaldo < 0
                                    ? '(' . number_format(abs($finalSaldo), 0, ',', '.') . ')'
                                    : number_format($finalSaldo, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            {{-- Pagination Controls --}}
            <div class="d-flex align-items-center justify-content-between px-3 py-2" id="rkPaginationWrap">
                <div id="rkPageInfo" class="small text-secondary"></div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="rkPagination"></ul>
                </nav>
            </div>
        </div>
    </section>
</div>

<style>
/* ====== HEADER ====== */
.rk-th {
    background: #0d3b6e !important;
    color: #fff !important;
    font-size: 11.5px;
    font-weight: 600;
    padding: 9px 8px;
    white-space: nowrap;
    vertical-align: middle;
    border-color: #1a5276 !important;
}

/* ====== CELLS ====== */
.rk-td {
    font-size: 11.5px;
    padding: 5px 8px;
    vertical-align: middle;
    white-space: nowrap;
    border-color: #d0dce8 !important;
}
.rk-uraian {
    white-space: normal;
    min-width: 220px;
    max-width: 400px;
}

/* ====== ROWS ====== */
tbody tr { background-color: #ffffff; }
tbody tr:hover { background-color: #f5f8fc; }

/* ====== NILAI ====== */
.rk-debet  { color: #1a7a3d; font-weight: 600; }
.rk-kredit { color: #c0392b; font-weight: 600; }
.rk-saldo  { color: #0d3b6e; font-weight: 700; }
.rk-neg    { color: #c0392b !important; }

/* ====== FOOTER ====== */
.rk-footer td {
    background: #1b4f72 !important;
    color: #fff !important;
    font-size: 12px;
    padding: 8px;
    border-color: #1a5276 !important;
}
.rk-footer .rk-debet  { color: #a9dfbf !important; }
.rk-footer .rk-kredit { color: #f1948a !important; }
.rk-footer .rk-saldo  { color: #fad7a0 !important; }

/* ====== LEGEND DOT ====== */
</style>

<script>
function submitForm()  { document.getElementById('filterForm').submit(); }
function resetFilter() {
    const tahun = document.querySelector('[name="tahun"]')?.value;
    window.location.href = '{{ route("bank-keluar.report") }}?tahun=' + (tahun || '');
}

// ===== Show Entries + Pagination =====
(function() {
    var currentPage = 1;
    var pageSize    = 10;

    function getRows() {
        // semua <tr> di tbody, kecuali baris kosong (colspan)
        return Array.from(document.querySelectorAll('#rkTable tbody tr')).filter(function(tr) {
            return !tr.querySelector('td[colspan]');
        });
    }

    function render() {
        var rows = getRows();
        var total = rows.length;

        // sembunyikan / tampilkan baris
        rows.forEach(function(tr, i) {
            if (pageSize === 'all') {
                tr.style.display = '';
            } else {
                var start = (currentPage - 1) * pageSize;
                var end   = start + pageSize;
                tr.style.display = (i >= start && i < end) ? '' : 'none';
            }
        });

        // info entri
        var infoEl   = document.getElementById('rkEntriesInfo');
        var pageEl   = document.getElementById('rkPageInfo');
        if (total === 0) {
            if (infoEl) infoEl.textContent = '';
            if (pageEl) pageEl.textContent = '';
            document.getElementById('rkPagination').innerHTML = '';
            return;
        }

        var start = pageSize === 'all' ? 1 : (currentPage - 1) * pageSize + 1;
        var end   = pageSize === 'all' ? total : Math.min(currentPage * pageSize, total);
        var text  = 'Menampilkan ' + start + ' – ' + end + ' dari ' + total + ' entri';
        if (infoEl) infoEl.textContent = text;
        if (pageEl) pageEl.textContent = text;

        // pagination buttons
        var pagUl = document.getElementById('rkPagination');
        pagUl.innerHTML = '';
        if (pageSize === 'all') return;

        var totalPages = Math.ceil(total / pageSize);
        if (totalPages <= 1) return;

        function makeItem(label, page, disabled, active) {
            var li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            var a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.innerHTML = label;
            a.addEventListener('click', function(e) {
                e.preventDefault();
                if (!disabled) { currentPage = page; render(); }
            });
            li.appendChild(a);
            return li;
        }

        pagUl.appendChild(makeItem('&laquo;', currentPage - 1, currentPage === 1, false));

        // tampilkan max 5 halaman di tengah
        var startP = Math.max(1, currentPage - 2);
        var endP   = Math.min(totalPages, startP + 4);
        startP     = Math.max(1, endP - 4);
        for (var p = startP; p <= endP; p++) {
            pagUl.appendChild(makeItem(p, p, false, p === currentPage));
        }

        pagUl.appendChild(makeItem('&raquo;', currentPage + 1, currentPage === totalPages, false));
    }

    window.changePageSize = function() {
        var sel = document.getElementById('showEntriesSelect');
        pageSize    = sel.value === 'all' ? 'all' : parseInt(sel.value);
        currentPage = 1;
        render();
    };

    // init
    document.addEventListener('DOMContentLoaded', function() { render(); });
    render(); // fallback jika DOM sudah siap
})();
</script>

@endsection
