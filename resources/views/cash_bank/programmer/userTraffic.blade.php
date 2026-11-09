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
        background: radial-gradient(circle, rgba(52, 152, 219, 0.2) 0%, transparent 70%);
        pointer-events: none;
    }

    .programmer-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(155, 89, 182, 0.15) 0%, transparent 70%);
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
        color: #38bdf8;
        margin-right: 0.5rem;
    }

    .programmer-header p {
        color: rgba(255, 255, 255, 0.65);
        margin-bottom: 0;
        position: relative;
        z-index: 1;
    }

    .programmer-header .badge-role {
        background: rgba(56, 189, 248, 0.18);
        color: #38bdf8;
        border: 1px solid rgba(56, 189, 248, 0.35);
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* ===== SUMMARY STAT CARDS ===== */
    .metric-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .metric-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .metric-info h3 {
        font-size: 1.65rem;
        font-weight: 700;
        margin: 0;
        color: #0f172a;
        line-height: 1.2;
    }

    .metric-info small {
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== PANELS ===== */
    .custom-panel {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .custom-panel-header {
        background: #ffffff;
        padding: 1.15rem 1.5rem;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .custom-panel-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.05rem;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .custom-panel-body {
        padding: 1.25rem;
        flex: 1;
        overflow-y: auto;
    }

    /* ===== TABLE STYLING (NAVY/DARK-TECH TOUCH) ===== */
    .user-table, .traffic-table {
        font-size: 0.85rem;
        margin-bottom: 0;
    }

    .table.user-table thead th,
    .table.traffic-table thead th,
    .user-table thead th,
    .traffic-table thead th {
        background: #0d3b6e !important;
        color: #ffffff !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none !important;
        padding: 0.85rem 0.65rem !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    .user-table tbody td, .traffic-table tbody td {
        padding: 0.65rem;
        vertical-align: middle;
        border-color: #f1f5f9;
    }

    .user-table tbody tr:hover, .traffic-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Role Badges */
    .badge-role-admin {
        background: #e0e7ff;
        color: #3730a3;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.72rem;
    }

    .badge-role-va {
        background: #ecfdf5;
        color: #065f46;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.72rem;
    }

    .badge-role-programmer {
        background: #fee2e2;
        color: #991b1b;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.72rem;
    }

    .badge-role-vendor {
        background: #fef3c7;
        color: #92400e;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.72rem;
    }

    /* Password Mask */
    .pwd-container {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #f1f5f9;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    .pwd-mask {
        letter-spacing: 2px;
        color: #64748b;
        font-weight: 700;
    }

    .btn-toggle-pwd {
        background: transparent;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 0 0.15rem;
        font-size: 0.8rem;
        line-height: 1;
    }

    .btn-toggle-pwd:hover {
        color: #0d3b6e;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 2.8rem;
        margin-bottom: 0.75rem;
        opacity: 0.5;
    }

    /* Modals */
    .modal-custom .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 45px rgba(0,0,0,0.2);
        overflow: hidden;
    }

    .modal-custom .modal-header {
        background: #0d3b6e;
        color: #ffffff;
        padding: 1.15rem 1.5rem;
    }

    .modal-custom .modal-header .close {
        color: #ffffff;
        opacity: 0.8;
    }

    .modal-custom .modal-header .close:hover {
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ===== HEADER ===== --}}
    <div class="programmer-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 1rem;">
            <div>
                <h2><i class="fas fa-users-cog"></i> User & Traffic Monitoring</h2>
                <p class="mt-1">Daftar Pengguna Sistem, Pengaturan Akun & Pemantauan Traffic Aktivitas Login Harian</p>
            </div>
            <span class="badge-role"><i class="fas fa-shield-alt mr-1"></i> ROLE: PROGRAMMER</span>
        </div>
    </div>

    {{-- ===== METRIC COUNTER CARDS ===== --}}
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: #e0f2fe; color: #0284c7;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-info">
                    <h3 id="statTotalUsers">{{ number_format($totalUsersCount) }}</h3>
                    <small>Total Pengguna Sistem</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: #dcfce7; color: #16a34a;">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="metric-info">
                    <h3 id="statTotalLogins">{{ number_format($totalLoginsToday) }}</h3>
                    <small id="statTotalLoginsLabel">Total Login Hari Ini</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="metric-card">
                <div class="metric-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="metric-info">
                    <h3 id="statActiveUsers">{{ number_format($uniqueUsersToday) }}</h3>
                    <small id="statActiveUsersLabel">Pengguna Aktif Hari Ini</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MAIN 2-COLUMN SECTION ===== --}}
    <div class="row">

        {{-- KOLOM KIRI: TABEL USER --}}
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="custom-panel">
                <div class="custom-panel-header">
                    <h5><i class="fas fa-id-badge text-primary"></i> Daftar Pengguna Web</h5>
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="userSearchInput" class="form-control border-left-0" placeholder="Cari user / bank..." oninput="filterUserTable()">
                        </div>
                    </div>
                </div>

                <div class="custom-panel-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table user-table table-hover w-100" id="userListTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Username & Role</th>
                                    <th>Unit / Bank</th>
                                    <th>Password</th>
                                    <th style="width: 80px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $u)
                                <tr class="user-row" data-username="{{ strtolower($u->username) }}" data-bank="{{ strtolower($u->bankTujuan?->nama_tujuan ?? '') }}" data-role="{{ strtolower($u->role) }}">
                                    <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $u->username }}</div>
                                        <div>
                                            @if($u->role === 'admin')
                                                <span class="badge-role-admin"><i class="fas fa-user-shield mr-1"></i>ADMIN</span>
                                            @elseif($u->role === 'va')
                                                <span class="badge-role-va"><i class="fas fa-university mr-1"></i>VA</span>
                                            @elseif($u->role === 'programmer')
                                                <span class="badge-role-programmer"><i class="fas fa-code mr-1"></i>PROGRAMMER</span>
                                            @else
                                                <span class="badge-role-vendor"><i class="fas fa-user mr-1"></i>{{ strtoupper($u->role) }}</span>
                                            @endif

                                            @if($u->hasTwoFactorEnabled())
                                                <span class="badge badge-success font-weight-bold ml-1" style="font-size: 0.7rem;" title="2FA Google Authenticator Aktif">
                                                    <i class="fas fa-shield-alt mr-1"></i>2FA ON
                                                </span>
                                            @elseif($u->role === 'programmer')
                                                <span class="badge badge-warning text-dark font-weight-bold ml-1" style="font-size: 0.7rem;" title="2FA Diwajibkan untuk Akun Programmer">
                                                    <i class="fas fa-shield-alt mr-1"></i>2FA WAJIB
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($u->bankTujuan)
                                            <span class="font-weight-bold text-dark">{{ $u->bankTujuan->nama_tujuan }}</span>
                                            @if($u->bankTujuan->sap)
                                                <br><small class="text-muted"><i class="fas fa-tag mr-1"></i>SAP: {{ $u->bankTujuan->sap }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="pwd-container">
                                            <span class="pwd-mask">••••••••</span>
                                            <span class="pwd-plain text-dark font-weight-bold" style="display: none;">12345678</span>
                                            <button type="button" class="btn-toggle-pwd" onclick="togglePassword(this)" title="Intip default password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center" style="gap: 0.35rem;">
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="openResetPasswordModal({{ $u->id }}, '{{ $u->username }}')"
                                                    title="Reset / Ubah Password">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            @if($u->hasTwoFactorEnabled())
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmReset2Fa({{ $u->id }}, '{{ $u->username }}')"
                                                    title="Reset 2FA Google Authenticator">
                                                <i class="fas fa-shield-alt"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Tidak ada user terdaftar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: TABEL TRAFFIC LOGIN HARIAN --}}
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="custom-panel">
                <div class="custom-panel-header">
                    <h5><i class="fas fa-chart-line text-success"></i> Traffic Login Harian</h5>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                        <div class="d-flex align-items-center" style="gap: 0.35rem;">
                            <label class="small mb-0 font-weight-bold text-secondary">Tanggal:</label>
                            <input type="date" id="trafficDateInput" class="form-control form-control-sm font-weight-bold" 
                                   value="{{ $date }}" style="width: 145px; border-radius: 6px;" onchange="loadTrafficData()">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadTrafficData()" title="Muat ulang data">
                            <i class="fas fa-sync-alt" id="refreshTrafficIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="custom-panel-body p-0 position-relative">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table traffic-table table-hover w-100" id="trafficListTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>User & Role</th>
                                    <th class="text-center" style="width: 110px;">Total Login</th>
                                    <th style="width: 140px;">Login Terakhir</th>
                                    <th style="width: 80px;" class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody id="trafficTableBody">
                                @forelse($trafficSummary as $tIdx => $tRow)
                                <tr>
                                    <td class="text-center font-weight-bold text-muted">{{ $tIdx + 1 }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $tRow->username }}</div>
                                        <div>
                                            @if($tRow->role === 'admin')
                                                <span class="badge-role-admin">ADMIN</span>
                                            @elseif($tRow->role === 'va')
                                                <span class="badge-role-va">VA</span>
                                            @elseif($tRow->role === 'programmer')
                                                <span class="badge-role-programmer">PROGRAMMER</span>
                                            @else
                                                <span class="badge-role-vendor">{{ strtoupper($tRow->role) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 0.85rem;">
                                            <i class="fas fa-sign-in-alt mr-1"></i>{{ $tRow->total_login }}x
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            {{ \Carbon\Carbon::parse($tRow->last_login_at)->format('H:i:s') }}
                                        </div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($tRow->last_login_at)->format('d M Y') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="openSessionDetailModal({{ $tRow->user_id }}, '{{ $tRow->username }}')" 
                                                title="Lihat Histori Sesi Login">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr id="noTrafficRow">
                                    <td colspan="5" class="py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-user-clock text-secondary"></i>
                                            <h6>Tidak ada aktivitas login</h6>
                                            <p class="small text-muted mb-0">Belum ada user yang login pada tanggal ini.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Traffic loading spinner overlay --}}
                    <div id="trafficLoadingOverlay" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 50; justify-content: center; align-items: center;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- ===== MODAL RESET PASSWORD ===== --}}
<div class="modal fade modal-custom" id="modalResetPassword" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-key mr-2"></i> Reset / Ubah Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formResetPassword" onsubmit="submitResetPassword(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="resetUserId" name="user_id">
                    
                    <div class="form-group">
                        <label class="small font-weight-bold text-secondary">Username</label>
                        <input type="text" id="resetUsername" class="form-control bg-light font-weight-bold" readonly>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold text-secondary">Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" id="resetNewPassword" class="form-control" placeholder="Masukkan password baru minimal 4 karakter" required minlength="4">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleInputVisibility('resetNewPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Rekomendasi default sistem: <code>12345678</code></small>
                    </div>

                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#resetNewPassword').val('12345678')">
                            <i class="fas fa-magic mr-1"></i>Isi Default (12345678)
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold" id="btnSubmitReset">
                        <i class="fas fa-save mr-1"></i> Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODAL DETAIL SESI LOGIN ===== --}}
<div class="modal fade modal-custom" id="modalSessionDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-history mr-2"></i> Histori Sesi Login: <span id="sessionModalUsername" class="text-warning"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary font-weight-bold mr-2">Tanggal:</span>
                        <span id="sessionModalDate" class="font-weight-bold text-dark"></span>
                    </div>
                    <div>
                        <span class="badge badge-primary px-2 py-1 font-weight-bold" id="sessionModalCountBadge">0 Sesi</span>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-striped mb-0" style="font-size: 0.85rem;">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="width: 120px;">Waktu</th>
                                <th style="width: 140px;">IP Address</th>
                                <th>Perangkat / Browser (User Agent)</th>
                            </tr>
                        </thead>
                        <tbody id="sessionLogsTableBody">
                            {{-- Rendered via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Tutup</button>
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

    // ===== TOGGLE PASSWORD IN TABLE =====
    function togglePassword(btn) {
        let container = $(btn).closest('.pwd-container');
        let mask = container.find('.pwd-mask');
        let plain = container.find('.pwd-plain');
        let icon = $(btn).find('i');

        if (plain.is(':visible')) {
            plain.hide();
            mask.show();
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            mask.hide();
            plain.show();
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    }

    // ===== TOGGLE INPUT PASSWORD VISIBILITY =====
    function toggleInputVisibility(inputId, btn) {
        let input = document.getElementById(inputId);
        let icon = $(btn).find('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    }

    // ===== FILTER USER TABLE =====
    function filterUserTable() {
        let term = $('#userSearchInput').val().toLowerCase().trim();
        $('.user-row').each(function() {
            let username = String($(this).data('username') ?? '').toLowerCase();
            let bank = String($(this).data('bank') ?? '').toLowerCase();
            let role = String($(this).data('role') ?? '').toLowerCase();
            if (username.includes(term) || bank.includes(term) || role.includes(term)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // ===== RESET PASSWORD MODAL =====
    function openResetPasswordModal(id, username) {
        $('#resetUserId').val(id);
        $('#resetUsername').val(username);
        $('#resetNewPassword').val('');
        $('#modalResetPassword').modal('show');
    }

    function submitResetPassword(e) {
        e.preventDefault();
        let id = $('#resetUserId').val();
        let password = $('#resetNewPassword').val();
        let btn = $('#btnSubmitReset');

        if (!password || password.length < 4) {
            showToast('error', 'Password minimal 4 karakter');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...');

        $.ajax({
            url: `/programmer/user-traffic/reset-password/${id}`,
            type: 'POST',
            data: { password: password },
            success: function(res) {
                $('#modalResetPassword').modal('hide');
                showToast('success', res.message || 'Password berhasil diubah!');
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'Gagal mengubah password');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Password');
            }
        });
    }

    // ===== RESET 2FA =====
    function confirmReset2Fa(id, username) {
        if (!confirm(`Reset 2FA Google Authenticator untuk user '${username}'? User akan diminta scan QR Code ulang saat login.`)) {
            return;
        }

        $.ajax({
            url: `/programmer/user-traffic/reset-2fa/${id}`,
            type: 'POST',
            success: function(res) {
                showToast('success', res.message || '2FA berhasil di-reset!');
                setTimeout(() => location.reload(), 900);
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'Gagal mereset 2FA');
            }
        });
    }

    // ===== LOAD TRAFFIC DATA VIA AJAX =====
    function loadTrafficData() {
        let date = $('#trafficDateInput').val();
        if (!date) return;

        $('#trafficLoadingOverlay').css('display', 'flex');
        $('#refreshTrafficIcon').addClass('fa-spin');

        $.ajax({
            url: `/programmer/user-traffic/traffic-data?date=${date}`,
            type: 'GET',
            success: function(res) {
                // Update metrics
                $('#statTotalLogins').text(Number(res.total_logins || 0).toLocaleString('id-ID'));
                $('#statActiveUsers').text(Number(res.unique_users || 0).toLocaleString('id-ID'));

                // Update labels according to date
                let todayStr = new Date().toISOString().slice(0, 10);
                if (date === todayStr) {
                    $('#statTotalLoginsLabel').text('Total Login Hari Ini');
                    $('#statActiveUsersLabel').text('Pengguna Aktif Hari Ini');
                } else {
                    $('#statTotalLoginsLabel').text(`Total Login (${date})`);
                    $('#statActiveUsersLabel').text(`Pengguna Aktif (${date})`);
                }

                // Render traffic table
                let tbody = $('#trafficTableBody');
                tbody.empty();

                if (!res.traffic || res.traffic.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="5" class="py-5">
                                <div class="empty-state">
                                    <i class="fas fa-user-clock text-secondary"></i>
                                    <h6>Tidak ada aktivitas login</h6>
                                    <p class="small text-muted mb-0">Belum ada user yang login pada tanggal ini.</p>
                                </div>
                            </td>
                        </tr>
                    `);
                } else {
                    res.traffic.forEach((item, idx) => {
                        let roleBadge = '';
                        let r = (item.role || '').toLowerCase();
                        if (r === 'admin') roleBadge = '<span class="badge-role-admin">ADMIN</span>';
                        else if (r === 'va') roleBadge = '<span class="badge-role-va">VA</span>';
                        else if (r === 'programmer') roleBadge = '<span class="badge-role-programmer">PROGRAMMER</span>';
                        else roleBadge = `<span class="badge-role-vendor">${r.toUpperCase()}</span>`;

                        let lastDate = new Date(item.last_login_at);
                        let timeStr = lastDate.toTimeString().split(' ')[0];
                        let dateStr = lastDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

                        let row = `
                            <tr>
                                <td class="text-center font-weight-bold text-muted">${idx + 1}</td>
                                <td>
                                    <div class="font-weight-bold text-dark">${escapeHtml(item.username)}</div>
                                    <div>${roleBadge}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 0.85rem;">
                                        <i class="fas fa-sign-in-alt mr-1"></i>${item.total_login}x
                                    </span>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">${timeStr}</div>
                                    <small class="text-muted">${dateStr}</small>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="openSessionDetailModal(${item.user_id}, '${escapeHtml(item.username)}')"
                                            title="Lihat Histori Sesi Login">
                                        <i class="fas fa-list-ul"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                }
            },
            error: function() {
                showToast('error', 'Gagal memuat data traffic');
            },
            complete: function() {
                $('#trafficLoadingOverlay').hide();
                $('#refreshTrafficIcon').removeClass('fa-spin');
            }
        });
    }

    // ===== OPEN SESSION DETAIL MODAL =====
    function openSessionDetailModal(userId, username) {
        let date = $('#trafficDateInput').val();
        $('#sessionModalUsername').text(username);
        $('#sessionModalDate').text(date);
        $('#sessionLogsTableBody').html('<tr><td colspan="4" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary mr-2"></span> Memuat log sesi...</td></tr>');
        $('#modalSessionDetail').modal('show');

        $.ajax({
            url: `/programmer/user-traffic/session-detail/${userId}?date=${date}`,
            type: 'GET',
            success: function(res) {
                let logs = res.logs || [];
                $('#sessionModalCountBadge').text(`${logs.length} Sesi Login`);
                let tbody = $('#sessionLogsTableBody');
                tbody.empty();

                if (logs.length === 0) {
                    tbody.html('<tr><td colspan="4" class="text-center py-4 text-muted">Tidak ada sesi login tercatat pada tanggal ini.</td></tr>');
                    return;
                }

                logs.forEach((log, i) => {
                    let d = new Date(log.login_at);
                    let timeFormatted = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    let ip = log.ip_address || '-';
                    let ua = log.user_agent || '-';

                    tbody.append(`
                        <tr>
                            <td class="text-center font-weight-bold text-muted">${i + 1}</td>
                            <td class="font-weight-bold text-dark">${timeFormatted}</td>
                            <td><code>${escapeHtml(ip)}</code></td>
                            <td class="small text-muted" style="word-break: break-all;">${escapeHtml(ua)}</td>
                        </tr>
                    `);
                });
            },
            error: function() {
                $('#sessionLogsTableBody').html('<tr><td colspan="4" class="text-center py-4 text-danger">Gagal memuat data sesi.</td></tr>');
            }
        });
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
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
</script>
@endpush
