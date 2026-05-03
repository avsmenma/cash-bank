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

        {{-- TABLE --}}
        <div class="card shadow cb-fullscreen-table" style="border:none;">
            {{-- Show Entries + Info --}}
            <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2 cb-fullscreen-hide">
                <div class="d-flex align-items-center">
                    <label class="mb-0 small font-weight-bold text-secondary mr-2">Tampilkan</label>
                    <select id="showEntriesSelect" name="per_page" form="filterForm" class="form-control form-control-sm" style="width:90px;">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                    <label class="mb-0 small font-weight-bold text-secondary ml-2">entri</label>
                </div>
                <div id="rkEntriesInfo" class="small text-secondary">
                    @if($data->total() > 0)
                        Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} entri
                    @endif
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered mb-0" id="rkTable">
                    <thead>
                        <tr>
                            <th class="rk-th text-center">No</th>
                            <th class="rk-th text-center">No Agenda</th>
                            <th class="rk-th text-center">Tanggal</th>
                            <th class="rk-th text-center">No Bukti</th>
                            <th class="rk-th text-center">Sumber Dana</th>
                            <th class="rk-th text-center">Penerima / Dari</th>
                            <th class="rk-th text-center">Uraian</th>
                            <th class="rk-th text-center">Debet</th>
                            <th class="rk-th text-center">Kredit</th>
                            <th class="rk-th text-center">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr class="rk-footer">
                            <td colspan="7" class="text-right font-weight-bold">TOTAL</td>
                            <td class="text-right font-weight-bold rk-debet">
                                {{ number_format($totalDebet ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-right font-weight-bold rk-kredit">
                                {{ number_format($totalKredit ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-right font-weight-bold rk-saldo">
                                @if($finalSaldo !== null)
                                    {{ $finalSaldo < 0
                                        ? '(' . number_format(abs($finalSaldo), 0, ',', '.') . ')'
                                        : number_format($finalSaldo, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- Pagination Controls --}}
            <div class="d-none align-items-center justify-content-between px-3 py-2 cb-fullscreen-hide" id="rkPaginationWrap">
                <div id="rkPageInfo" class="small text-secondary">
                    @if($data->total() > 0)
                        Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} entri
                    @endif
                </div>
                <nav>
                    {{ $data->links('pagination::bootstrap-4') }}
                </nav>
            </div>
        </div>
    </section>
</div>

<style>
#rkTable {
    table-layout: fixed;
    min-width: 1820px;
}
#rkTable th:nth-child(1), #rkTable td:nth-child(1) { width: 55px; }
#rkTable th:nth-child(2), #rkTable td:nth-child(2) { width: 120px; }
#rkTable th:nth-child(3), #rkTable td:nth-child(3) { width: 110px; }
#rkTable th:nth-child(4), #rkTable td:nth-child(4) { width: 120px; }
#rkTable th:nth-child(5), #rkTable td:nth-child(5) { width: 250px; }
#rkTable th:nth-child(6), #rkTable td:nth-child(6) { width: 170px; }
#rkTable th:nth-child(7), #rkTable td:nth-child(7) { width: 600px; }
#rkTable th:nth-child(8), #rkTable td:nth-child(8),
#rkTable th:nth-child(9), #rkTable td:nth-child(9),
#rkTable th:nth-child(10), #rkTable td:nth-child(10) { width: 130px; }

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
    overflow: hidden;
    text-overflow: clip;
    border-color: #d0dce8 !important;
}
.rk-sumber {
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: normal;
    vertical-align: top;
    line-height: 1.35;
    overflow: visible;
}
.rk-penerima {
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: normal;
    vertical-align: top;
    line-height: 1.35;
    overflow: visible;
}
.rk-uraian {
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: normal;
    vertical-align: top;
    line-height: 1.35;
    overflow: visible;
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


</script>

@push('scripts')
<script>
$(function () {
    if (!$.fn.DataTable) return;

    var initialLength = parseInt($('#showEntriesSelect').val(), 10) || 50;
    var reportParams = @json(request()->query());
    var $entriesInfo = $('#rkEntriesInfo, #rkPageInfo');

    var table = $('#rkTable').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        ordering: false,
        searching: false,
        autoWidth: false,
        scrollX: true,
        scrollY: '60vh',
        scroller: {
            loadingIndicator: true,
            displayBuffer: 9
        },
        dom: 'rt',
        pageLength: initialLength,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: "{{ route('bank-keluar.report.data') }}",
            data: function (d) {
                $.extend(d, reportParams);
                d.per_page = $('#showEntriesSelect').val() || initialLength;
            }
        },
        columns: [
            { data: 'no', className: 'text-center rk-td' },
            { data: 'no_agenda', className: 'rk-td' },
            { data: 'tanggal', className: 'text-center rk-td' },
            { data: 'no_sap', className: 'rk-td' },
            { data: 'nama_sumber_dana', className: 'rk-td rk-sumber' },
            { data: 'penerima', className: 'rk-td rk-penerima' },
            {
                data: 'uraian',
                className: 'rk-td rk-uraian',
                createdCell: function (td, cellData) {
                    td.title = $('<div>').html(cellData || '').text();
                }
            },
            { data: 'debet', className: 'text-right rk-td rk-debet' },
            { data: 'kredit', className: 'text-right rk-td rk-kredit' },
            { data: 'saldo_akhir', className: 'text-right rk-td rk-saldo' }
        ],
        language: {
            emptyTable: 'Tidak ada data untuk filter yang dipilih.',
            processing: 'Memuat data...'
        }
    });

    function updateInfo() {
        var info = table.page.info();
        if (!info.recordsDisplay) {
            $entriesInfo.text('Tidak ada data');
            return;
        }
        $entriesInfo.text('Menampilkan ' + (info.start + 1) + ' - ' + info.end + ' dari ' + info.recordsDisplay + ' entri');
    }

    table.on('xhr.dt', function (e, settings, json) {
        if (!json || !json.totals) return;
        $('#rkTable tfoot .rk-debet').text(json.totals.debet);
        $('#rkTable tfoot .rk-kredit').text(json.totals.kredit);
        $('#rkTable tfoot .rk-saldo').text(json.totals.saldo);
    });

    table.on('draw', updateInfo);

    $('#showEntriesSelect').on('change', function () {
        table.page.len(parseInt(this.value, 10) || 50).draw(false);
    });
});
</script>
@endpush

@endsection
