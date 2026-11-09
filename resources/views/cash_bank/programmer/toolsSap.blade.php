@extends('layouts.index')

@push('styles')
<style>
    /* ===== PROGRAMMER DASHBOARD — DARK-TECH THEME ===== */
    .programmer-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 16px;
        padding: 2rem 2.5rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    .programmer-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(40, 167, 69, 0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    .programmer-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(52, 152, 219, 0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    .programmer-header h2 {
        color: #ffffff;
        font-weight: 700;
        font-size: 1.75rem;
        position: relative;
        z-index: 1;
    }

    .programmer-header h2 i {
        color: #2ecc71;
        margin-right: 0.5rem;
    }

    .programmer-header p {
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 0;
        position: relative;
        z-index: 1;
    }

    .programmer-header .badge-role {
        background: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
        border: 1px solid rgba(46, 204, 113, 0.3);
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* ===== KEYWORD & TOOL PANELS ===== */
    .keyword-panel {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .keyword-panel-header {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        color: #ffffff;
        padding: 1.25rem 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .keyword-panel-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
    }

    .keyword-panel-body {
        padding: 1.5rem;
    }

    .keyword-result {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
    }

    .keyword-count {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .keyword-count.match {
        background: #ebf8ff;
        color: #2b6cb0;
        border: 1px solid #bee3f8;
    }

    .keyword-count.update {
        background: #f0fff4;
        color: #276749;
        border: 1px solid #c6f6d5;
    }

    .keyword-sample {
        max-height: 240px;
        overflow-y: auto;
        margin-top: 0.75rem;
    }

    .keyword-sample table {
        font-size: 0.78rem;
        margin-bottom: 0;
    }

    /* ===== MONTH LOCK COMPONENT ===== */
    .month-lock-container {
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .month-lock-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 0.75rem;
        margin-top: 0.75rem;
    }

    .month-lock-card {
        border-radius: 10px;
        padding: 0.85rem 0.65rem;
        text-align: center;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        border: 2px solid transparent;
        user-select: none;
    }

    .month-lock-card:hover {
        transform: translateY(-2px);
    }

    .month-lock-card.locked {
        background: #fff5f5;
        border-color: #feb2b2;
        box-shadow: 0 2px 8px rgba(229, 62, 62, 0.12);
    }

    .month-lock-card.unlocked {
        background: #f0fff4;
        border-color: #9ae6b4;
        box-shadow: 0 2px 8px rgba(56, 161, 105, 0.12);
    }

    .month-lock-card .month-title {
        font-weight: 700;
        font-size: 0.88rem;
        margin-bottom: 0.35rem;
    }

    .month-lock-card.locked .month-title {
        color: #9b2c2c;
    }

    .month-lock-card.unlocked .month-title {
        color: #22543d;
    }

    .month-lock-card .lock-icon {
        font-size: 1.35rem;
        margin-bottom: 0.35rem;
        transition: transform 0.2s ease;
    }

    .month-lock-card.locked .lock-icon {
        color: #e53e3e;
    }

    .month-lock-card.unlocked .lock-icon {
        color: #38a169;
    }

    .month-lock-card:hover .lock-icon {
        transform: scale(1.2);
    }

    .month-lock-card .month-count {
        font-size: 0.72rem;
        color: #718096;
        font-weight: 600;
    }

    .spinner-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.85);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ===== HEADER ===== --}}
    <div class="programmer-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h2><i class="fas fa-tools"></i> Tools SAP & Mapper</h2>
                <p class="mt-1">Import SAP Cash Flow, Proteksi Lock Bulan & Standarisasi Reference Key</p>
            </div>
            <span class="badge-role"><i class="fas fa-shield-alt mr-1"></i> ROLE: PROGRAMMER</span>
        </div>
    </div>

    {{-- ===== IMPORT CASH FLOW SAP PANEL ===== --}}
    <div class="keyword-panel mb-4">
        <div class="keyword-panel-header" style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);">
            <h5><i class="fas fa-file-excel mr-2 text-warning"></i>Import Cash Flow & Standarisasi Reference Key (SAP)</h5>
            <span class="badge badge-warning text-dark font-weight-bold"><i class="fas fa-shield-alt mr-1"></i>Proteksi Lock Bulan Aktif</span>
        </div>
        <div class="keyword-panel-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Month Lock Controller Bar --}}
            <div class="month-lock-container">
                <div class="d-flex align-items-center justify-content-between flex-wrap pb-2 border-bottom mb-2" style="gap: 0.75rem;">
                    <div class="d-flex align-items-center flex-wrap" style="gap: 0.75rem;">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-lock text-danger mr-1"></i> Pengaturan Lock Bulan Cash Flow
                        </h6>
                        <div class="d-flex align-items-center" style="gap: 0.35rem;">
                            <label class="small mb-0 font-weight-bold text-secondary">Tahun:</label>
                            <select class="form-control form-control-sm font-weight-bold" id="lockTahunSelect" style="width: 100px; border-radius: 6px;" onchange="loadMonthLocks(this.value)">
                                @foreach($availableYears as $yr)
                                    <option value="{{ $yr }}" {{ $yr == $selectedLockYear ? 'selected' : '' }}>{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                        <span id="lockSummaryBadge">
                            {{-- Live summary --}}
                        </span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-danger font-weight-bold" onclick="batchUpdateLocks('lock_all')" title="Kunci semua bulan 1-12">
                                <i class="fas fa-lock mr-1"></i> Kunci Semua
                            </button>
                            <button type="button" class="btn btn-outline-success font-weight-bold" onclick="batchUpdateLocks('unlock_all')" title="Buka semua bulan 1-12">
                                <i class="fas fa-lock-open mr-1"></i> Buka Semua
                            </button>
                            <button type="button" class="btn btn-outline-primary font-weight-bold" onclick="batchUpdateLocks('lock_until', 7)" title="Kunci Januari s/d Juli">
                                <i class="fas fa-calendar-check mr-1"></i> Kunci Jan - Jul
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Explanatory Banner --}}
                <div class="alert alert-info py-2 px-3 mb-2 d-flex align-items-center rounded-lg" style="font-size: 0.82rem; background: #ebf8ff; border: 1px solid #bee3f8; color: #2b6cb0;">
                    <i class="fas fa-info-circle fa-lg mr-2 flex-shrink-0 text-primary"></i>
                    <div>
                        <strong>Aturan Proteksi Lock Bulan:</strong> Bulan berstatus <span class="badge badge-danger"><i class="fas fa-lock"></i> Terkunci</span> <strong>tidak akan terhapus</strong> saat mencentang <em>"Timpa data lama (Truncate)"</em> dan data baris bulan tersebut pada file Excel akan <strong>dilewati</strong>. Hanya bulan yang <span class="badge badge-success"><i class="fas fa-lock-open"></i> Terbuka</span> yang akan di-replace/diimport.
                    </div>
                </div>

                {{-- Month Grid Container --}}
                <div class="position-relative">
                    <div id="monthLockGrid" class="month-lock-grid">
                        {{-- Loaded via JavaScript --}}
                    </div>
                    <div id="monthLockLoading" class="spinner-overlay" style="display: none; border-radius: 8px;">
                        <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
                    </div>
                </div>
            </div>

            <form action="{{ route('programmer.importCashflow') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-end">
                    <div class="col-lg-5 mb-3">
                        <label class="small font-weight-bold text-dark"><i class="fas fa-file-invoice-dollar text-success mr-1"></i>File Mentah Transaksi Cashflow (.xlsx)</label>
                        <input type="file" name="file_cashflow" class="form-control-file border p-2 rounded bg-light" accept=".xlsx,.xls">
                        <small class="text-muted d-block mt-1">Kosongkan jika ingin menggunakan file server: <code>docs/Cashflow.xlsx</code></small>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="small font-weight-bold text-dark"><i class="fas fa-book text-info mr-1"></i>File Standarisasi Reference Key (.xlsx)</label>
                        <input type="file" name="file_standarisasi" class="form-control-file border p-2 rounded bg-light" accept=".xlsx,.xls">
                        <small class="text-muted d-block mt-1">Kosongkan jika ingin menggunakan file server: <code>docs/Standarisasi Reffkey (1).xlsx</code></small>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <div class="form-check mb-2 bg-light p-2 rounded border">
                            <input class="form-check-input ml-1" type="checkbox" name="truncate_first" id="chkTruncateFirst" value="1" checked>
                            <label class="form-check-label small font-weight-bold ml-4" for="chkTruncateFirst">
                                Timpa data lama (Truncate)
                            </label>
                            <small class="text-muted d-block ml-4" style="font-size: 0.72rem;">Hanya menimpa bulan <strong>Terbuka (🔓)</strong>. Bulan <strong>Terkunci (🔒)</strong> aman.</small>
                        </div>
                        <button type="submit" class="btn btn-success btn-block font-weight-bold shadow-sm" onclick="return confirm('Mulai proses import data Cash Flow? Data bulan terkunci tidak akan terhapus.')">
                            <i class="fas fa-upload mr-1"></i> Mulai Import Cash Flow
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== KEYWORD MAPPER ===== --}}
    <div class="keyword-panel">
        <div class="keyword-panel-header">
            <h5><i class="fas fa-tags mr-2"></i>Keyword Mapper Bank Keluar</h5>
            <span class="badge badge-light text-dark">Backup otomatis sebelum update</span>
        </div>
        <div class="keyword-panel-body">
            <form id="keywordMapperForm">
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <label class="small font-weight-bold">Kata Kunci</label>
                        <textarea class="form-control" name="keywords" id="keywordInput" rows="6"
                            placeholder="Contoh:&#10;Pembelian TBS&#10;Kebun A"></textarea>
                    </div>
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold">Mode</label>
                                <select class="form-control" name="match_mode" id="keywordMatchMode">
                                    <option value="all">Semua kata kunci</option>
                                    <option value="any">Salah satu kata kunci</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold">Jenis Pembayaran</label>
                                <select class="form-control keyword-select" name="id_jenis_pembayaran" id="keywordJenis">
                                    <option value="">Pilih Jenis Pembayaran</option>
                                    @foreach($keywordReferences['jenis'] as $jenis)
                                        <option value="{{ $jenis->id_jenis_pembayaran }}">{{ $jenis->nama_jenis_pembayaran }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold">Kriteria</label>
                                <select class="form-control keyword-select" name="id_kategori_kriteria" id="keywordKategori">
                                    <option value="">Pilih Kriteria</option>
                                    @foreach($keywordReferences['kategori'] as $kategori)
                                        <option value="{{ $kategori->id_kategori_kriteria }}">{{ $kategori->nama_kriteria }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold">Sub Kriteria</label>
                                <select class="form-control keyword-select" name="id_sub_kriteria" id="keywordSub" disabled>
                                    <option value="">Pilih Sub Kriteria</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold">Item Sub Kriteria</label>
                                <select class="form-control keyword-select" name="id_item_sub_kriteria" id="keywordItem" disabled>
                                    <option value="">Pilih Item</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
                            <button type="button" class="btn btn-primary" id="btnKeywordPreview">
                                <i class="fas fa-search mr-1"></i>Preview
                            </button>
                            <button type="button" class="btn btn-success" id="btnKeywordUpdate" disabled>
                                <i class="fas fa-save mr-1"></i>Update Data
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="keyword-result mt-3" id="keywordResult">
                <span class="text-muted">Belum ada preview.</span>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // ===== CSRF Setup =====
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    const keywordReferences = @json($keywordReferences);

    // ===== MONTH LOCK CASHFLOW =====
    let currentLockYear = {{ $selectedLockYear ?? date('Y') }};
    let monthLockData = @json($cashflowLocks ?? []);

    function renderMonthLocks(months) {
        let container = $('#monthLockGrid');
        container.empty();
        let lockedCount = 0;

        if (!months || Object.keys(months).length === 0) {
            container.html('<div class="col-12 text-center text-muted py-3">Tidak ada data lock.</div>');
            return;
        }

        Object.keys(months).forEach(m => {
            let item = months[m];
            let isLocked = Boolean(item.is_locked);
            if (isLocked) lockedCount++;

            let card = $(`
                <div class="month-lock-card ${isLocked ? 'locked' : 'unlocked'}" 
                     onclick="toggleMonthLock(${currentLockYear}, ${item.bulan}, ${isLocked})"
                     title="Klik untuk ${isLocked ? 'membuka (Unlock)' : 'mengunci (Lock)'} ${item.nama_bulan} ${currentLockYear}">
                    <div class="lock-icon">
                        <i class="fas ${isLocked ? 'fa-lock' : 'fa-lock-open'}"></i>
                    </div>
                    <div class="month-title">${item.nama_bulan}</div>
                    <span class="badge ${isLocked ? 'badge-danger' : 'badge-success'} px-2 py-1" style="font-size: 0.7rem; font-weight: 700;">
                        <i class="fas ${isLocked ? 'fa-lock' : 'fa-lock-open'} mr-1"></i>${isLocked ? 'TERKUNCI' : 'TERBUKA'}
                    </span>
                    <div class="month-count mt-1">
                        <i class="fas fa-database mr-1"></i>${Number(item.total_rows || 0).toLocaleString('id-ID')} baris
                    </div>
                </div>
            `);
            container.append(card);
        });

        let unlockedCount = 12 - lockedCount;
        $('#lockSummaryBadge').html(`
            <span class="badge badge-danger mr-1 px-2 py-1"><i class="fas fa-lock mr-1"></i>${lockedCount} Bulan Terkunci</span>
            <span class="badge badge-success px-2 py-1"><i class="fas fa-lock-open mr-1"></i>${unlockedCount} Bulan Terbuka</span>
        `);
    }

    function loadMonthLocks(tahun) {
        currentLockYear = tahun;
        $('#monthLockLoading').show();
        $.ajax({
            url: `/programmer/cashflow-locks?tahun=${tahun}`,
            type: 'GET',
            success: function(res) {
                $('#monthLockLoading').hide();
                monthLockData = res.months;
                renderMonthLocks(res.months);
            },
            error: function() {
                $('#monthLockLoading').hide();
                showToast('error', 'Gagal memuat status lock bulan');
            }
        });
    }

    function toggleMonthLock(tahun, bulan, currentlyLocked) {
        let newStatus = !currentlyLocked;
        $.ajax({
            url: '/programmer/cashflow-locks/toggle',
            type: 'POST',
            data: {
                tahun: tahun,
                bulan: bulan,
                is_locked: newStatus ? 1 : 0
            },
            success: function(res) {
                showToast('success', res.message);
                monthLockData = res.months;
                renderMonthLocks(res.months);
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'Gagal mengubah status lock');
            }
        });
    }

    function batchUpdateLocks(action, untilMonth = null) {
        $.ajax({
            url: '/programmer/cashflow-locks/batch',
            type: 'POST',
            data: {
                tahun: currentLockYear,
                action: action,
                until_month: untilMonth
            },
            success: function(res) {
                showToast('success', res.message);
                monthLockData = res.months;
                renderMonthLocks(res.months);
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'Gagal memperbarui lock');
            }
        });
    }

    $(document).ready(function() {
        renderMonthLocks(monthLockData);
    });

    // ===== KEYWORD MAPPER =====
    let latestKeywordPreview = null;
    $('.keyword-select').select2({ theme: 'bootstrap4', width: '100%' });

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function refreshKeywordSubOptions() {
        const kategoriId = $('#keywordKategori').val();
        const subs = keywordReferences.subByKategori[kategoriId] || [];
        let html = '<option value="">Pilih Sub Kriteria</option>';
        subs.forEach(item => {
            html += `<option value="${item.id_sub_kriteria}">${escapeHtml(item.nama_sub_kriteria)}</option>`;
        });
        $('#keywordSub').html(html).prop('disabled', !kategoriId).val('').trigger('change.select2');
        refreshKeywordItemOptions();
    }

    function refreshKeywordItemOptions() {
        const subId = $('#keywordSub').val();
        const items = keywordReferences.itemBySub[subId] || [];
        let html = '<option value="">Pilih Item</option>';
        items.forEach(item => {
            html += `<option value="${item.id_item_sub_kriteria}">${escapeHtml(item.nama_item_sub_kriteria)}</option>`;
        });
        $('#keywordItem').html(html).prop('disabled', !subId).val('').trigger('change.select2');
    }

    $('#keywordKategori').on('change', refreshKeywordSubOptions);
    $('#keywordSub').on('change', refreshKeywordItemOptions);
    $('#keywordMapperForm').on('input change', 'textarea, select', function() {
        latestKeywordPreview = null;
        $('#btnKeywordUpdate').prop('disabled', true);
    });

    function collectKeywordForm() {
        return {
            keywords: $('#keywordInput').val(),
            match_mode: $('#keywordMatchMode').val(),
            id_kategori_kriteria: $('#keywordKategori').val(),
            id_sub_kriteria: $('#keywordSub').val(),
            id_item_sub_kriteria: $('#keywordItem').val(),
            id_jenis_pembayaran: $('#keywordJenis').val()
        };
    }

    function renderKeywordResult(res) {
        let html = `
            <div>
                <span class="keyword-count match"><i class="fas fa-filter"></i>${Number(res.matched || 0).toLocaleString('id-ID')} cocok</span>
                <span class="keyword-count update"><i class="fas fa-pen"></i>${Number(res.will_update || 0).toLocaleString('id-ID')} akan diubah</span>
            </div>
        `;

        if (res.samples && res.samples.length) {
            html += '<div class="keyword-sample"><table class="table table-bordered table-sm"><thead><tr><th>ID</th><th>Agenda</th><th>Uraian</th><th>Kriteria Saat Ini</th></tr></thead><tbody>';
            res.samples.forEach(row => {
                const current = [
                    row.nama_kriteria || '-',
                    row.nama_sub_kriteria || '-',
                    row.nama_item_sub_kriteria || '-',
                    row.nama_jenis_pembayaran || '-'
                ].join(' | ');
                html += `<tr>
                    <td>${escapeHtml(row.id_bank_keluar)}</td>
                    <td>${escapeHtml(row.agenda_tahun || '-')}</td>
                    <td>${escapeHtml(row.uraian || '-')}</td>
                    <td>${escapeHtml(current)}</td>
                </tr>`;
            });
            html += '</tbody></table></div>';
        }

        $('#keywordResult').html(html);
        $('#btnKeywordUpdate').prop('disabled', Number(res.will_update || 0) === 0);
    }

    $('#btnKeywordPreview').on('click', function() {
        const btn = $(this);
        latestKeywordPreview = null;
        $('#btnKeywordUpdate').prop('disabled', true);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Preview...');

        $.ajax({
            url: '{{ route("programmer.keywordPreview") }}',
            type: 'POST',
            data: collectKeywordForm(),
            success: function(res) {
                latestKeywordPreview = res;
                renderKeywordResult(res);
                showToast('success', 'Preview selesai');
            },
            error: function(xhr) {
                $('#keywordResult').html('<span class="text-danger">' + escapeHtml(xhr.responseJSON?.message || 'Preview gagal') + '</span>');
                showToast('error', xhr.responseJSON?.message || 'Preview gagal');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-search mr-1"></i>Preview');
            }
        });
    });

    $('#btnKeywordUpdate').on('click', function() {
        if (!latestKeywordPreview || Number(latestKeywordPreview.will_update || 0) === 0) return;
        if (!confirm(`Update ${latestKeywordPreview.will_update} data Bank Keluar?`)) return;

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Update...');

        $.ajax({
            url: '{{ route("programmer.keywordUpdate") }}',
            type: 'POST',
            data: collectKeywordForm(),
            success: function(res) {
                showToast('success', `${res.message} Backup: ${res.backup_table || '-'}`);
                $('#keywordResult').append(`<div class="mt-2 text-success font-weight-bold">Backup: ${escapeHtml(res.backup_table || '-')}</div>`);
                latestKeywordPreview = null;
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || xhr.responseJSON?.error || 'Update gagal');
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Update Data');
            },
            complete: function(xhr) {
                if (xhr.status >= 200 && xhr.status < 300) {
                    btn.prop('disabled', true).html('<i class="fas fa-save mr-1"></i>Update Data');
                }
            }
        });
    });

    // ===== TOAST NOTIFICATION =====
    function showToast(type, message) {
        let bgColor = type === 'success' ? '#28a745' : type === 'error' ? '#e53e3e' : '#f39c12';
        let icon = type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'exclamation-triangle';

        let toast = $(`
            <div style="
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 99999;
                background: ${bgColor};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 10px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.25);
                font-weight: 600;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                max-width: 400px;
                opacity: 0;
                transform: translateX(100px);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            ">
                <i class="fas fa-${icon}"></i> ${message}
            </div>
        `);

        $('body').append(toast);
        setTimeout(() => toast.css({ opacity: 1, transform: 'translateX(0)' }), 50);
        setTimeout(() => {
            toast.css({ opacity: 0, transform: 'translateX(100px)' });
            setTimeout(() => toast.remove(), 400);
        }, 3500);
    }
</script>
@endpush
