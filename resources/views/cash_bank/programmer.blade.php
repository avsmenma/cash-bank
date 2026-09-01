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
        background: radial-gradient(circle, rgba(229, 62, 62, 0.15) 0%, transparent 70%);
        pointer-events: none;
    }

    .programmer-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(40, 167, 69, 0.1) 0%, transparent 70%);
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
        color: #e53e3e;
        margin-right: 0.5rem;
    }

    .programmer-header p {
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 0;
        position: relative;
        z-index: 1;
    }

    .programmer-header .badge-role {
        background: rgba(229, 62, 62, 0.2);
        color: #fc8181;
        border: 1px solid rgba(229, 62, 62, 0.3);
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* ===== STAT CARDS ===== */
    .stat-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
    }

    .stat-card.active {
        border-color: var(--card-color, #e53e3e);
        box-shadow: 0 4px 16px rgba(229, 62, 62, 0.2);
    }

    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #ffffff;
        flex-shrink: 0;
    }

    .stat-card .stat-info h4 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        line-height: 1;
    }

    .stat-card .stat-info small {
        color: #718096;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* ===== DATA TABLE AREA ===== */
    .data-panel {
        background: #ffffff;
        border-radius: 16px;
        padding: 0;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .data-panel-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .data-panel-header h5 {
        color: #ffffff;
        font-weight: 600;
        margin: 0;
    }

    .data-panel-header h5 i {
        margin-right: 0.5rem;
        opacity: 0.8;
    }

    .data-panel-body {
        padding: 1.5rem;
    }

    /* ===== ACTION BUTTONS ===== */
    .btn-danger-gradient {
        background: linear-gradient(135deg, #e53e3e, #c53030);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(229, 62, 62, 0.3);
    }

    .btn-danger-gradient:hover {
        background: linear-gradient(135deg, #c53030, #9b2c2c);
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(229, 62, 62, 0.4);
        color: #ffffff;
    }

    .btn-outline-danger-custom {
        background: transparent;
        color: #e53e3e;
        border: 1.5px solid #e53e3e;
        border-radius: 8px;
        padding: 0.4rem 1rem;
        font-weight: 600;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }

    .btn-outline-danger-custom:hover {
        background: #e53e3e;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
    }

    .btn-truncate {
        background: linear-gradient(135deg, #742a2a, #9b2c2c);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .btn-truncate:hover {
        background: linear-gradient(135deg, #63171b, #742a2a);
        color: #ffffff;
        box-shadow: 0 4px 16px rgba(116, 42, 42, 0.5);
    }

    /* ===== TABLE STYLING ===== */
    #dataTableProgrammer {
        font-size: 0.85rem;
    }

    #dataTableProgrammer thead th {
        background: #2d3748;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: none;
        white-space: nowrap;
    }

    #dataTableProgrammer tbody tr {
        transition: background 0.2s ease;
    }

    #dataTableProgrammer tbody tr:hover {
        background: #f7fafc !important;
    }

    #dataTableProgrammer tbody tr.selected {
        background: #fff5f5 !important;
        border-left: 3px solid #e53e3e;
    }

    .delete-row-btn {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(229, 62, 62, 0.1);
        color: #e53e3e;
        border: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .delete-row-btn:hover {
        background: #e53e3e;
        color: #ffffff;
        transform: scale(1.1);
    }

    /* ===== MODAL CONFIRM ===== */
    .modal-confirm .modal-content {
        border-radius: 16px;
        border: none;
        overflow: hidden;
    }

    .modal-confirm .modal-header {
        background: linear-gradient(135deg, #e53e3e, #c53030);
        color: #ffffff;
        border: none;
        padding: 1.5rem;
    }

    .modal-confirm .modal-header .close {
        color: #ffffff;
        opacity: 0.8;
    }

    .modal-confirm .modal-body {
        padding: 2rem;
    }

    .confirm-input {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        letter-spacing: 2px;
        text-transform: uppercase;
        transition: border-color 0.3s ease;
    }

    .confirm-input:focus {
        border-color: #e53e3e;
        box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.15);
        outline: none;
    }

    .confirm-input.valid {
        border-color: #e53e3e;
        background: #fff5f5;
    }

    /* ===== NO DATA PLACEHOLDER ===== */
    .no-table-selected {
        text-align: center;
        padding: 4rem 2rem;
        color: #a0aec0;
    }

    .no-table-selected i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .no-table-selected h5 {
        font-weight: 600;
        color: #718096;
    }

    .no-table-selected p {
        font-size: 0.9rem;
    }

    /* ===== LOADING SPINNER ===== */
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
        z-index: 10;
        border-radius: 16px;
    }

    .spinner-overlay .spinner-border {
        width: 3rem;
        height: 3rem;
        color: #e53e3e;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .programmer-header {
            padding: 1.5rem;
        }
        .programmer-header h2 {
            font-size: 1.25rem;
        }
        .stat-card {
            padding: 1rem;
        }
        .stat-card .stat-info h4 {
            font-size: 1.2rem;
        }
    }

    /* ===== ANIMATIONS ===== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.4s ease forwards;
    }

    .stat-card {
        animation: fadeInUp 0.4s ease forwards;
    }

    @keyframes pulse-red {
        0%, 100% { box-shadow: 0 0 0 0 rgba(229, 62, 62, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(229, 62, 62, 0); }
    }

    .pulse-danger {
        animation: pulse-red 2s infinite;
    }

    .keyword-panel {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .keyword-panel-header {
        background: #243447;
        color: #ffffff;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .keyword-panel-header h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
    }

    .keyword-panel-body {
        padding: 1.25rem;
    }

    .keyword-result {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
        background: #f8fafc;
        min-height: 98px;
    }

    .keyword-count {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .keyword-count.match {
        background: #e8f4ff;
        color: #1f5f8b;
    }

    .keyword-count.update {
        background: #fff4e5;
        color: #9a5b00;
    }

    .keyword-sample {
        max-height: 220px;
        overflow: auto;
        margin-top: 0.75rem;
    }

    .keyword-sample table {
        font-size: 0.78rem;
        margin-bottom: 0;
    }

    /* ===== MONTH LOCK COMPONENT ===== */
    .month-lock-container {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .month-lock-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .month-lock-card {
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem;
        text-align: center;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        user-select: none;
    }

    .month-lock-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
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
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ===== HEADER ===== --}}
    <div class="programmer-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h2><i class="fas fa-terminal"></i> Programmer Panel</h2>
                <p class="mt-1">Data Management Console — Hapus data & Import SAP Cash Flow</p>
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

    {{-- ===== STAT CARDS GRID ===== --}}
    <div class="row mb-4">
        @foreach($stats as $key => $stat)
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="stat-card" data-table="{{ $key }}" style="--card-color: {{ $stat['color'] }};"
                 onclick="selectTable('{{ $key }}')">
                <div class="stat-icon" style="background: {{ $stat['color'] }};">
                    <i class="{{ $stat['icon'] }}"></i>
                </div>
                <div class="stat-info">
                    <h4 id="count-{{ $key }}">{{ number_format($stat['count']) }}</h4>
                    <small>{{ $stat['name'] }}</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ===== DATA PANEL ===== --}}
    <div class="data-panel position-relative" id="dataPanel">
        {{-- Header --}}
        <div class="data-panel-header" id="dataPanelHeader" style="display: none;">
            <h5 id="dataPanelTitle"><i class="fas fa-database"></i> <span>Pilih Tabel</span></h5>
            <div class="d-flex gap-2 flex-wrap" style="gap: 0.5rem;">
                <button class="btn-outline-danger-custom" id="btnBulkDelete" onclick="bulkDeleteSelected()" style="display: none;">
                    <i class="fas fa-trash-alt mr-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                </button>
                <button class="btn-truncate" id="btnTruncate" onclick="showTruncateModal()">
                    <i class="fas fa-bomb mr-1"></i> Hapus Semua Data
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="data-panel-body">
            {{-- Placeholder --}}
            <div id="noTableSelected" class="no-table-selected">
                <i class="fas fa-hand-pointer"></i>
                <h5>Pilih Tabel di Atas</h5>
                <p>Klik salah satu card di atas untuk melihat dan mengelola data</p>
            </div>

            {{-- Table Area (hidden initially) --}}
            <div id="tableArea" style="display: none;">
                <div class="table-responsive">
                    <table id="dataTableProgrammer" class="table table-bordered table-sm table-hover w-100">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Loading Spinner --}}
        <div class="spinner-overlay" id="loadingSpinner" style="display: none;">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

</div>

{{-- ===== DELETE CONFIRM MODAL ===== --}}
<div class="modal fade modal-confirm" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i> Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <p id="deleteMessage" class="mb-3" style="font-size: 1rem;"></p>
                <p class="text-muted small">Data yang sudah dihapus <strong>tidak dapat dikembalikan</strong>.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn" onclick="executeDelete()">
                    <i class="fas fa-trash-alt mr-1"></i> Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== TRUNCATE CONFIRM MODAL ===== --}}
<div class="modal fade modal-confirm" id="truncateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bomb mr-2"></i> HAPUS SEMUA DATA</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                </div>
                <h5 class="text-danger font-weight-bold" id="truncateTableName"></h5>
                <p class="text-muted mb-4">Semua data di tabel ini akan dihapus secara <strong>permanen</strong>.</p>
                <p class="mb-2" style="font-weight: 600;">Ketik <code style="font-size: 1.1rem; color: #e53e3e;">HAPUS</code> untuk konfirmasi:</p>
                <input type="text" class="form-control confirm-input" id="truncateConfirmInput"
                       placeholder="Ketik HAPUS" autocomplete="off">
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger px-4" id="confirmTruncateBtn"
                        onclick="executeTruncate()" disabled>
                    <i class="fas fa-bomb mr-1"></i> Hapus Semua
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ===== STATE =====
    let currentTable = null;
    let currentTableName = '';
    let dtInstance = null;
    let currentColumns = [];
    let currentPrimaryKey = 'id';
    let pendingDeleteAction = null;
    let latestKeywordPreview = null;

    const tableMap = @json($tableMap);
    const keywordReferences = @json($keywordReferences);

    // ===== CSRF Setup =====
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

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
                if (currentTable === 'bank_keluar' && dtInstance) dtInstance.ajax.reload(null, false);
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

    // ===== SELECT TABLE =====
    function selectTable(tableKey) {
        // Update active card
        $('.stat-card').removeClass('active');
        $(`.stat-card[data-table="${tableKey}"]`).addClass('active');

        currentTable = tableKey;
        currentTableName = tableMap[tableKey].name;
        currentPrimaryKey = tableMap[tableKey].primary_key;

        // Show header + table area
        $('#dataPanelHeader').show();
        $('#dataPanelTitle span').text(currentTableName);
        $('#noTableSelected').hide();
        $('#tableArea').show();
        $('#btnBulkDelete').hide();
        $('#selectedCount').text('0');

        loadData();
    }

    // ===== LOAD DATA via DataTables =====
    function loadData() {
        showLoading(true);

        // Destroy existing DataTable
        if (dtInstance) {
            dtInstance.destroy();
            $('#dataTableProgrammer thead').empty();
            $('#dataTableProgrammer tbody').empty();
        }

        // First fetch to get columns
        $.ajax({
            url: `/programmer/data/${currentTable}`,
            data: { draw: 1, start: 0, length: 1 },
            success: function(response) {
                currentColumns = response.columns || [];

                if (currentColumns.length === 0) {
                    showLoading(false);
                    return;
                }

                // Build thead
                let headerHtml = '<tr><th style="width: 30px;"><input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)"></th>';
                currentColumns.forEach(col => {
                    headerHtml += `<th>${col}</th>`;
                });
                headerHtml += '<th style="width: 50px;">Aksi</th></tr>';
                $('#dataTableProgrammer thead').html(headerHtml);

                // Init DataTable with server-side
                dtInstance = $('#dataTableProgrammer').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: `/programmer/data/${currentTable}`,
                        type: 'GET',
                        dataSrc: function(json) {
                            showLoading(false);
                            return json.data;
                        },
                        error: function() {
                            showLoading(false);
                        }
                    },
                    columns: buildColumns(),
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    order: [],
                    deferRender: true,
                    language: {
                        processing: "Memproses...",
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ baris",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                        infoEmpty: "Tidak ada data",
                        infoFiltered: "(difilter dari _MAX_ total data)",
                        emptyTable: "Tidak ada data yang tersedia",
                        zeroRecords: "Tidak ada data yang cocok",
                        paginate: {
                            first: "Pertama",
                            previous: "Sebelumnya",
                            next: "Selanjutnya",
                            last: "Terakhir"
                        }
                    },
                    responsive: false,
                    scrollX: true,
                    scrollY: '60vh',
                    scroller: {
                        loadingIndicator: true,
                        displayBuffer: 9
                    },
                    drawCallback: function() {
                        updateSelectedCount();
                    }
                });
            },
            error: function() {
                showLoading(false);
                showToast('error', 'Gagal memuat data');
            }
        });
    }

    // ===== BUILD COLUMN DEFINITIONS =====
    function buildColumns() {
        let cols = [];

        // Checkbox column
        cols.push({
            data: null,
            orderable: false,
            searchable: false,
            render: function(data) {
                let pk = data[currentPrimaryKey] || data.id;
                return `<input type="checkbox" class="row-checkbox" value="${pk}" onclick="updateSelectedCount()">`;
            }
        });

        // Data columns
        currentColumns.forEach(col => {
            cols.push({
                data: col,
                defaultContent: '-',
                render: function(data) {
                    if (data === null || data === undefined) return '<span class="text-muted">-</span>';
                    let str = String(data);
                    if (str.length > 60) str = str.substring(0, 60) + '...';
                    return $('<span>').text(str).html();
                }
            });
        });

        // Action column
        cols.push({
            data: null,
            orderable: false,
            searchable: false,
            render: function(data) {
                let pk = data[currentPrimaryKey] || data.id;
                return `<button class="delete-row-btn" onclick="confirmDeleteSingle(${pk})" title="Hapus">
                            <i class="fas fa-trash-alt"></i>
                        </button>`;
            }
        });

        return cols;
    }

    // ===== SELECT ALL =====
    function toggleSelectAll(el) {
        $('.row-checkbox').prop('checked', el.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        let count = $('.row-checkbox:checked').length;
        $('#selectedCount').text(count);
        if (count > 0) {
            $('#btnBulkDelete').show();
        } else {
            $('#btnBulkDelete').hide();
        }
    }

    // ===== DELETE SINGLE =====
    function confirmDeleteSingle(id) {
        pendingDeleteAction = { type: 'single', id: id };
        $('#deleteMessage').html(`Apakah Anda yakin ingin menghapus data dengan ID <strong>${id}</strong> dari tabel <strong>${currentTableName}</strong>?`);
        $('#deleteModal').modal('show');
    }

    // ===== BULK DELETE =====
    function bulkDeleteSelected() {
        let ids = [];
        $('.row-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            showToast('warning', 'Pilih minimal 1 data');
            return;
        }

        pendingDeleteAction = { type: 'bulk', ids: ids };
        $('#deleteMessage').html(`Apakah Anda yakin ingin menghapus <strong>${ids.length} data</strong> dari tabel <strong>${currentTableName}</strong>?`);
        $('#deleteModal').modal('show');
    }

    // ===== EXECUTE DELETE (from modal confirm) =====
    function executeDelete() {
        $('#deleteModal').modal('hide');
        showLoading(true);

        if (pendingDeleteAction.type === 'single') {
            $.ajax({
                url: `/programmer/delete/${currentTable}/${pendingDeleteAction.id}`,
                type: 'DELETE',
                success: function(res) {
                    showLoading(false);
                    showToast('success', res.message);
                    updateCardCount(currentTable, res.newCount);
                    dtInstance.ajax.reload(null, false);
                },
                error: function(xhr) {
                    showLoading(false);
                    showToast('error', xhr.responseJSON?.error || 'Gagal menghapus');
                }
            });
        } else if (pendingDeleteAction.type === 'bulk') {
            $.ajax({
                url: `/programmer/bulk-delete/${currentTable}`,
                type: 'DELETE',
                data: { ids: pendingDeleteAction.ids },
                success: function(res) {
                    showLoading(false);
                    showToast('success', res.message);
                    updateCardCount(currentTable, res.newCount);
                    dtInstance.ajax.reload(null, false);
                    $('#selectAllCheckbox').prop('checked', false);
                    updateSelectedCount();
                },
                error: function(xhr) {
                    showLoading(false);
                    showToast('error', xhr.responseJSON?.error || 'Gagal menghapus');
                }
            });
        }

        pendingDeleteAction = null;
    }

    // ===== TRUNCATE =====
    function showTruncateModal() {
        if (!currentTable) return;
        $('#truncateTableName').text(`Tabel: ${currentTableName}`);
        $('#truncateConfirmInput').val('');
        $('#confirmTruncateBtn').prop('disabled', true);
        $('#truncateModal').modal('show');
    }

    // Enable truncate button only when user types "HAPUS"
    $('#truncateConfirmInput').on('input', function() {
        let val = $(this).val().toUpperCase().trim();
        if (val === 'HAPUS') {
            $(this).addClass('valid');
            $('#confirmTruncateBtn').prop('disabled', false);
        } else {
            $(this).removeClass('valid');
            $('#confirmTruncateBtn').prop('disabled', true);
        }
    });

    function executeTruncate() {
        $('#truncateModal').modal('hide');
        showLoading(true);

        $.ajax({
            url: `/programmer/truncate/${currentTable}`,
            type: 'DELETE',
            success: function(res) {
                showLoading(false);
                showToast('success', res.message);
                updateCardCount(currentTable, 0);
                dtInstance.ajax.reload(null, false);
            },
            error: function(xhr) {
                showLoading(false);
                showToast('error', xhr.responseJSON?.error || 'Gagal menghapus semua data');
            }
        });
    }

    // ===== UPDATE CARD COUNT =====
    function updateCardCount(tableKey, newCount) {
        $(`#count-${tableKey}`).text(Number(newCount).toLocaleString('id-ID'));
    }

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

    // ===== LOADING =====
    function showLoading(show) {
        if (show) {
            $('#loadingSpinner').show();
        } else {
            $('#loadingSpinner').hide();
        }
    }
</script>
@endpush
