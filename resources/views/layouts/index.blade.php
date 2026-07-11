<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <title >Dashboard</title> -->
    <link rel="icon" href="{{ asset('images/logoPTPNNew.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="{{ asset('adminLTE/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE/dist/css/adminlte.min.css') }}">
    <!-- Dark Theme Override -->
    <link rel="stylesheet" href="{{ asset('css/dark-theme.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- AdminLTE -->

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet"
        href="{{ asset('adminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-scroller/css/scroller.bootstrap4.min.css') }}">
    <!-- select 2 -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">

    <!-- picker -->
    <link rel="stylesheet"
        href="{{ asset('adminLTE/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
    <!-- Virtual Select -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/dropzone/min/dropzone.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">

    @stack('styles')
    <style>
        /* ============================================================
           TATA LETAK HEADER ALA PROJECT LM:
           - Header memanjang penuh sampai pojok kiri atas (di atas sidebar).
           - Tombol buka/tutup sidebar di pojok kiri, di kanannya logo + judul.
           - Sidebar mulai tepat di bawah header.
           ============================================================ */
        /* margin-left ditulis ulang dengan spesifisitas lebih tinggi karena
           AdminLTE memasang margin-left ber-!important saat sidebar-collapse */
        .main-header,
        body.sidebar-mini .main-header,
        body.sidebar-mini.sidebar-collapse .main-header {
            margin-left: 0 !important;
            position: sticky;
            top: 0;
            z-index: 1040;
        }
        body.layout-fixed .main-sidebar {
            top: calc(3.5rem + 1px) !important;
        }
        /* Menu sidebar mulai rapat di bawah header (tanpa ruang kosong) */
        .main-sidebar .sidebar {
            padding-top: 6px;
        }

        /* Tombol buka/tutup sidebar bergaya kotak (ala LM) */
        .main-header .nav-link[data-widget="pushmenu"] {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 8px;
            background: rgba(255, 255, 255, .08);
        }
        .main-header .nav-link[data-widget="pushmenu"]:hover {
            background: rgba(255, 255, 255, .16);
        }

        /* Brand (logo + judul + subjudul) di header */
        .cb-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 10px;
            min-width: 0;
        }
        .cb-brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #fff;
            padding: 3px;
            flex: none;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .12);
        }
        .cb-brand-mark img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }
        .cb-brand-text { line-height: 1.2; min-width: 0; }
        .cb-brand-name {
            font-weight: 700;
            font-size: 14.5px;
            color: #fff;
            white-space: nowrap;
        }
        .cb-brand-name span { color: #28a745; }
        .cb-brand-sub {
            font-size: 11px;
            color: rgba(255, 255, 255, .6);
            white-space: nowrap;
            letter-spacing: .02em;
        }
        @media (max-width: 576px) {
            .cb-brand-sub { display: none; }
        }

        /* ============================================================
           SIDEBAR TERTUTUP:
           - TIDAK terbuka saat disentuh kursor (hover-expand AdminLTE mati;
             buka/tutup hanya lewat tombol di pojok kiri atas).
           - Submenu yang sedang terbuka ikut disembunyikan (hanya ikon
             menu utama yang tampil).
           - Arahkan kursor ke menu utama -> submenu muncul sebagai panel
             mengapung (flyout) di sebelah kanan, seperti project LM.
           ============================================================ */
        body.sidebar-mini.sidebar-collapse .main-sidebar:hover,
        body.sidebar-mini.sidebar-collapse .main-sidebar.sidebar-focused {
            width: 4.6rem !important;
        }
        body.sidebar-mini.sidebar-collapse .main-sidebar:hover .sidebar .nav-sidebar > .nav-item > .nav-link p,
        body.sidebar-mini.sidebar-collapse .main-sidebar:hover .nav-sidebar > .nav-item > .nav-link > span,
        body.sidebar-mini.sidebar-collapse .main-sidebar:hover .nav-sidebar > .nav-item > .nav-link .right,
        body.sidebar-mini.sidebar-collapse .main-sidebar.sidebar-focused .sidebar .nav-sidebar > .nav-item > .nav-link p,
        body.sidebar-mini.sidebar-collapse .main-sidebar.sidebar-focused .nav-sidebar > .nav-item > .nav-link > span {
            display: none !important;
            visibility: hidden !important;
        }

        /* Submenu disembunyikan total saat sidebar tertutup (termasuk menu-open) */
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar .nav-treeview {
            display: none !important;
        }

        /* Saat tertutup, lebar item menu benar-benar disempitkan seukuran ikon.
           PENTING: overflow sidebar dibuka untuk flyout, jadi isi menu tidak lagi
           "disembunyikan" oleh overflow — tanpa aturan ini hover menu menjulur
           keluar sidebar dan flyout muncul jauh dari ikon. */
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item,
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item > .nav-link {
            width: 3.6rem !important;
        }
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item > .nav-link {
            overflow: hidden;
            text-align: center;
        }
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item > .nav-link .nav-icon,
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item > .nav-link > i:first-child {
            margin: 0 auto;
        }

        /* Flyout: sidebar boleh "meluber" agar panel tidak terpotong.
           Termasuk lapisan pembungkus plugin OverlayScrollbars (os-*) yang
           memaksa overflow hidden — tanpa ini flyout tergunting di dalam sidebar. */
        body.sidebar-mini.sidebar-collapse .main-sidebar,
        body.sidebar-mini.sidebar-collapse .main-sidebar .sidebar,
        body.sidebar-mini.sidebar-collapse .main-sidebar .os-host,
        body.sidebar-mini.sidebar-collapse .main-sidebar .os-host-overflow,
        body.sidebar-mini.sidebar-collapse .main-sidebar .os-padding,
        body.sidebar-mini.sidebar-collapse .main-sidebar .os-viewport,
        body.sidebar-mini.sidebar-collapse .main-sidebar .os-content {
            overflow: visible !important;
        }
        body.sidebar-mini.sidebar-collapse .nav-sidebar > .nav-item {
            position: relative;
        }

        /* Panel flyout bergaya LM: putih, judul menu induk di atas, item rapi */
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item:hover > .nav-treeview {
            display: block !important;
            position: absolute;
            left: calc(100% + 6px);
            top: -6px;
            min-width: 210px;
            margin-left: 0 !important;
            padding: 6px !important;
            background: #ffffff !important;
            border: 1px solid #d5dce5;
            border-radius: 10px;
            box-shadow: 0 12px 34px rgba(0, 0, 0, .18);
            z-index: 1050;
        }
        /* Judul flyout = nama menu induk (dari atribut data-flyout) */
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item:hover > .nav-treeview::before {
            content: attr(data-flyout);
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #6a7681;
            padding: 4px 10px 8px;
            margin-bottom: 4px;
            border-bottom: 1px solid #e4e9ee;
        }
        /* Jembatan tak terlihat menutup celah 6px agar flyout tidak berkedip */
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item:hover > .nav-treeview::after {
            content: "";
            position: absolute;
            top: 0;
            left: -12px;
            width: 12px;
            height: 100%;
        }
        /* Item di dalam flyout: teks gelap di panel putih */
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item:hover > .nav-treeview .nav-link {
            width: auto !important;
            padding: 8px 12px !important;
            white-space: nowrap;
            border-radius: 7px;
            border-left: none !important;
            color: #33414d !important;
            background: transparent !important;
        }
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item:hover > .nav-treeview .nav-link:hover {
            background: #eef4f0 !important;
            color: #14342a !important;
        }
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item:hover > .nav-treeview .nav-link.active {
            background: #e8f5e9 !important;
            color: #1a5632 !important;
            font-weight: 600;
        }
        body.sidebar-mini.sidebar-collapse .main-sidebar .nav-sidebar > .nav-item:hover > .nav-treeview .nav-link p {
            display: inline !important;
            visibility: visible !important;
            width: auto !important;
            margin-left: 0 !important;
            animation: none !important;
            color: inherit !important;
        }

        .sidebar .nav-item {
            /* margin-left: 10px; */
            margin-bottom: 20px;
        }

        .sidebar .nav .nav-treeview {
            margin-left: 20px;
        }

        .cb-fullscreen-toggle {
            width: 48px;
            height: 32px;
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 4px;
            background: rgba(255,255,255,.08);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
        }
        .cb-fullscreen-toggle:hover,
        .cb-fullscreen-toggle:focus {
            background: rgba(255,255,255,.16);
            color: #fff;
            outline: none;
        }
        body.cb-table-fullscreen {
            overflow: hidden;
            background: #fff;
        }
        body.cb-table-fullscreen .main-header,
        body.cb-table-fullscreen .cb-fullscreen-hide,
        body.cb-table-fullscreen .content-header,
        body.cb-table-fullscreen .page-title-card,
        body.cb-table-fullscreen .filter-card-ringkasan,
        body.cb-table-fullscreen .no-print,
        body.cb-table-fullscreen .card-header {
            display: none !important;
        }
        body.cb-table-fullscreen .cb-fullscreen-table .cb-fullscreen-controls {
            display: flex !important;
            margin-bottom: 12px !important;
        }
        body.cb-table-fullscreen .card-header.cb-fullscreen-tabs {
            display: block !important;
            background: #fff !important;
            border-bottom: 1px solid #dee2e6 !important;
            padding: 10px 12px !important;
            margin: 0 !important;
            position: relative;
            z-index: 2;
        }
        body.cb-table-fullscreen .card-header.cb-fullscreen-tabs .nav {
            display: flex !important;
            flex-wrap: wrap;
            gap: 6px;
        }
        body.cb-table-fullscreen .card-header.cb-fullscreen-tabs .nav-link {
            padding: 8px 14px;
        }
        body.cb-table-fullscreen .cb-fullscreen-table .cb-fullscreen-support {
            display: none !important;
        }
        body.cb-table-fullscreen .select2-container--open,
        body.cb-table-fullscreen .select2-dropdown {
            z-index: 1060 !important;
        }
        body.cb-table-fullscreen .main-sidebar {
            display: block !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            width: 64px !important;
            min-height: 100vh !important;
            z-index: 1045 !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            scrollbar-width: none;
            -ms-overflow-style: none;
            transition: width .18s ease, box-shadow .18s ease;
        }
        body.cb-table-fullscreen .main-sidebar::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
        body.cb-table-fullscreen .main-sidebar:hover,
        body.cb-table-fullscreen .main-sidebar:focus-within {
            width: 250px !important;
            box-shadow: 8px 0 22px rgba(15, 23, 42, .25);
        }
        body.cb-table-fullscreen .main-sidebar .brand-link {
            height: 64px;
            padding: 8px 10px;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        body.cb-table-fullscreen .main-sidebar .brand-link img {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
        }
        body.cb-table-fullscreen .main-sidebar .brand-text,
        body.cb-table-fullscreen .main-sidebar .nav-sidebar .nav-link p,
        body.cb-table-fullscreen .main-sidebar .nav-sidebar .nav-link .right {
            opacity: 0;
            pointer-events: none;
            transition: opacity .12s ease;
            white-space: nowrap;
        }
        body.cb-table-fullscreen .main-sidebar:hover .brand-text,
        body.cb-table-fullscreen .main-sidebar:focus-within .brand-text,
        body.cb-table-fullscreen .main-sidebar:hover .nav-sidebar .nav-link p,
        body.cb-table-fullscreen .main-sidebar:focus-within .nav-sidebar .nav-link p,
        body.cb-table-fullscreen .main-sidebar:hover .nav-sidebar .nav-link .right,
        body.cb-table-fullscreen .main-sidebar:focus-within .nav-sidebar .nav-link .right {
            opacity: 1;
            pointer-events: auto;
        }
        body.cb-table-fullscreen .main-sidebar .sidebar {
            overflow-x: hidden;
            padding-left: 8px;
            padding-right: 8px;
        }
        body.cb-table-fullscreen .main-sidebar .nav-sidebar .nav-link {
            width: 232px;
            min-height: 42px;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        body.cb-table-fullscreen .main-sidebar .nav-sidebar .nav-icon,
        body.cb-table-fullscreen .main-sidebar .nav-sidebar > .nav-item > .nav-link > i:first-child {
            flex: 0 0 32px;
            width: 32px;
            margin-right: 10px;
            text-align: center;
        }
        body.cb-table-fullscreen .main-sidebar .nav-treeview {
            margin-left: 0 !important;
            width: 232px;
        }
        body.cb-table-fullscreen .main-sidebar:not(:hover):not(:focus-within) .nav-treeview {
            display: none !important;
        }
        body.cb-table-fullscreen .content-wrapper {
            margin-left: 64px !important;
            width: calc(100vw - 64px) !important;
            max-width: calc(100vw - 64px) !important;
            padding: 0 !important;
            min-height: 100vh !important;
            height: 100vh !important;
            overflow: hidden !important;
            background: #fff !important;
        }
        body.cb-table-fullscreen .content,
        body.cb-table-fullscreen .content-wrapper > .container-fluid,
        body.cb-table-fullscreen .content-wrapper > .container-fuild,
        body.cb-table-fullscreen .content-wrapper > div,
        body.cb-table-fullscreen .container-fuild,
        body.cb-table-fullscreen .content .container-fluid {
            width: 100% !important;
            max-width: none !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body.cb-table-fullscreen .cb-fullscreen-table {
            width: 100% !important;
            max-width: 100% !important;
            height: 100vh !important;
            min-height: 100vh !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: #fff !important;
            padding: 12px !important;
            overflow: hidden !important;
        }
        body.cb-table-fullscreen .cb-fullscreen-table .dataTables_wrapper {
            height: 100%;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        body.cb-table-fullscreen .cb-fullscreen-table .dt-buttons {
            display: none !important;
        }
        body.cb-table-fullscreen .cb-fullscreen-table .dataTables_scroll {
            flex: 1 1 auto;
            min-height: 0;
        }
        body.cb-table-fullscreen .cb-fullscreen-table .dataTables_scrollBody {
            height: calc(100vh - 72px) !important;
            max-height: calc(100vh - 72px) !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: auto !important;
        }
        body.cb-table-fullscreen .cb-fullscreen-table .table-responsive {
            height: calc(100vh - 24px) !important;
            max-height: calc(100vh - 24px) !important;
            overflow: auto !important;
        }
        body.cb-table-fullscreen .card-header.cb-fullscreen-tabs + .card-body .cb-fullscreen-table {
            height: calc(100vh - 64px) !important;
            min-height: calc(100vh - 64px) !important;
        }
        body.cb-table-fullscreen .card-header.cb-fullscreen-tabs + .card-body .cb-fullscreen-table .table-responsive,
        body.cb-table-fullscreen .card-header.cb-fullscreen-tabs + .card-body .cb-fullscreen-table .table-scroll {
            height: calc(100vh - 112px) !important;
            max-height: calc(100vh - 112px) !important;
        }
        body.cb-table-fullscreen .cb-fullscreen-table > .table-responsive,
        body.cb-table-fullscreen .cb-fullscreen-table .tbl-scroll-rk,
        body.cb-table-fullscreen .cb-fullscreen-table .table-scroll {
            height: calc(100vh - 24px) !important;
            max-height: calc(100vh - 24px) !important;
            overflow: auto !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <script>
        // Terapkan status sidebar tersimpan SEBELUM render agar kondisi
        // buka/tutup konsisten antar-halaman dan tidak berkedip.
        try {
            if (localStorage.getItem('cb-sidebar-collapsed') === '1') {
                document.body.classList.add('sidebar-collapse');
            }
        } catch (e) {}
    </script>
    @php
        $isProgrammer = auth()->check() && auth()->user()->role === 'programmer';
    @endphp
    <div class="wrapper">
        <!-- NAVBAR -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button" title="Buka/Tutup menu"
                        aria-label="Buka/Tutup menu"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            {{-- Logo + judul aplikasi (ala project LM) --}}
            <div class="cb-brand">
                <div class="cb-brand-mark">
                    <img src="{{ asset('images/logoPTPNNew.png') }}" alt="Logo PTPN">
                </div>
                <div class="cb-brand-text">
                    <div class="cb-brand-name">Cash <span>Bank</span></div>
                    <div class="cb-brand-sub">Sistem Monitoring Kas &amp; Bank Regional</div>
                </div>
            </div>

            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->
                @if(request()->routeIs('bank-masuk.*', 'bank-keluar.*', 'daftarBank.*', 'rekening-koran.*', 'ringkasan.*', 'penerima.*', 'dropping.*', 'permintaan.*', 'daftar-spp.*', 'dashboard.*'))
                    <li class="nav-item d-flex align-items-center">
                        <button type="button" class="cb-fullscreen-toggle" id="cbBankFullscreenToggle" title="Mode layar penuh tabel" aria-label="Mode layar penuh tabel" aria-pressed="false">
                            <i class="fas fa-expand"></i>
                        </button>
                    </li>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn border-0">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>

                <!-- Messages Dropdown Menu -->

        </nav>
        <aside class="main-sidebar sidebar-dark-green elevation-4">
            <!-- Sidebar (brand/logo pindah ke header) -->
            <div class="sidebar">

                <!-- Sidebar Menu -->
                <nav class="mt-1">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        @if($isProgrammer)
                        <li class="nav-item">
                            <a href="{{ route('programmer.index') }}"
                                class="nav-link {{ request()->routeIs('programmer.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-terminal"></i>
                                <p>Programmer Panel</p>
                            </a>
                        </li>
                        @else
                        <li
                            class="nav-item {{ request()->routeIs('dashboard.*', 'dashboard-pembayaran.*', 'dashboard.bank.*') ? 'menu-open menu-is-opening' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->routeIs('dashboard.*', 'dashboard-pembayaran.*', 'dashboard.bank.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                                <i class="right fas fa-angle-left"></i>
                            </a>

                            <ul class="nav nav-treeview" data-flyout="Dashboard">
                                <li class="nav-item">
                                    <a href="{{ route('dashboard.index') }}"
                                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                        <p>Pembayaran</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('dashboard.pembayaran.index') }}"
                                        class="nav-link {{ request()->routeIs('dashboard.pembayaran.index') ? 'active' : ''}}">
                                        <p>PD & PvD</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('dashboard.modal-kerja.index') }}"
                                        class="nav-link {{ request()->routeIs('dashboard.modal-kerja.index') ? 'active' : ''}}">
                                        <p>Modal Kerja</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('dashboard.bank.index') }}"
                                        class="nav-link {{ request()->routeIs('dashboard.bank.index') ? 'active' : ''}}">
                                        <p>Bank</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- DAFTAR SPP -->
                        <li class="nav-item">
                            <a href="{{ route('daftar-spp.index') }}"
                                class="nav-link {{ request()->routeIs('daftar-spp.*') ? 'active' : ''}}">
                                <i class="nav-icon fas fa-copy"></i>
                                <p>Daftar SPP</p>
                            </a>
                        </li>


                        <li class="nav-item">
                            <a href="{{ route('permintaan.index') }}"
                                class="nav-link {{ request()->routeIs('permintaan.*') ? 'active' : ''}}">
                                <i class='far fa-money-bill-alt'></i>
                                <p>Permintaan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dropping.index') }}"
                                class="nav-link {{ request()->routeIs('dropping.*') ? 'active' : ''}}">
                                <i class="nav-icon fas fa-th"></i>
                                <p>Dropping</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('penerima.index') }}"
                                class="nav-link {{ request()->routeIs('penerima.*') ? 'active' : ''}}">
                                <i class="nav-icon fas  fa-book-medical"></i>
                                <p>Penerimaan</p>
                            </a>
                        </li>

                        <!-- RINGKASAN PEMBAYARAN -->
                        <li class="nav-item">
                            <a href="{{ route('ringkasan.index') }}"
                                class="nav-link {{ request()->routeIs('ringkasan.*') ? 'active' : ''}}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Ringkasan Pembayaran</p>
                            </a>
                        </li>


                        <!-- DAFTAR BANK -->
                        <li
                            class="nav-item {{ request()->routeIs('bank-masuk.*', 'bank-keluar.*', 'rekening-koran.*', 'daftarBank.*') ? 'menu-open menu-is-opening' : '' }}">
                            <a href="#"
                                class="nav-link {{ request()->routeIs('bank-masuk.*', 'bank-keluar.*', 'rekening-koran.*', 'daftarBank.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-university"></i>
                                <p>
                                    Transaksi Bank
                                </p>
                                <i class="right fas fa-angle-left"></i>
                            </a>

                            <ul class="nav nav-treeview" data-flyout="Transaksi Bank">
                                <li class="nav-item">
                                    <a href="{{ route('bank-masuk.index') }}"
                                        class="nav-link {{ request()->routeIs('bank-masuk.index') ? 'active' : '' }}">
                                        <p>Input Bank Masuk</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('bank-keluar.index') }}"
                                        class="nav-link {{ request()->routeIs('bank-keluar.index') ? 'active' : '' }}">
                                        <p>Input Bank Keluar</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('daftarBank.index') }}"
                                        class="nav-link {{ request()->routeIs('daftarBank.*') ? 'active' : '' }}">
                                        <p>Virtual Account</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('bank-keluar.report') }}"
                                        class="nav-link {{ request()->routeIs('bank-keluar.report') ? 'active' : '' }}">
                                        <p>Rekening Koran</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- SIDEBAR -->
        <!-- <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="#" class="brand-link text-center">
            <span class="brand-text font-weight-light">Dashboard</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column">
                    <li class="nav-item">
                        <a href="#" class="nav-link active">
                            <i class="nav-icon fas fa-university"></i>
                            <p>Bank Keluar</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside> -->

        <!-- CONTENT -->
        <div class="content-wrapper p-3">
            @yield('content')

            @yield('script')
        </div>

    </div>
    <!--  jQuery (WAJIB PALING PERTAMA) -->
    <script src="{{ asset('adminLTE/plugins/jquery/jquery.min.js') }}"></script>

    <!--  Bootstrap 4 -->
    <script src="{{ asset('adminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!--  Plugin inti UI -->
    <script src="{{ asset('adminLTE/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('adminLTE/dist/js/adminlte.js') }}"></script>

    <!--  Select2 -->
    <script src="{{ asset('adminLTE/plugins/select2/js/select2.full.min.js') }}"></script>

    <!--  Moment.js (SEBELUM DateTimePicker) -->
    <script src="{{ asset('adminLTE/plugins/moment/moment.min.js') }}"></script>

    <!--  Tempus Dominus (DateTimePicker) -->
    <script
        src="{{ asset('adminLTE/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>

    <!--  DataTables Core -->
    <script src="{{ asset('adminLTE/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/datatables-scroller/js/dataTables.scroller.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/datatables-scroller/js/scroller.bootstrap4.min.js') }}"></script>

    <!--  DataTables Responsive -->
    <script src="{{ asset('adminLTE/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!--  Muat data bertahap saat scroll (pengganti pagination) -->
    <script src="{{ asset('js/cb-infinite-table.js') }}?v=1"></script>

    <!--  DataTables Buttons -->
    <script src="{{ asset('adminLTE/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

    <!--  Export dependency -->
    <script src="{{ asset('adminLTE/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/pdfmake/vfs_fonts.js') }}"></script>

    <!--  Optional Plugins -->
    <script src="{{ asset('adminLTE/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/dropzone/min/dropzone.min.js') }}"></script>

    <!--  Virtual Select -->
    <script src="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.js"></script>

    <!--  Custom File Input -->
    <script src="{{ asset('adminLTE/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/chart.js/Chart.min.js')}}"></script>

    @yield('modals')

    {{-- Global drag-to-scroll for horizontal table containers --}}
    <script>
    (function() {
        function enableDragScroll(el) {
            if (el._dragScrollEnabled) return;
            el._dragScrollEnabled = true;
            var isDown = false, startX = 0, scrollLeft = 0, rafId = null, pointerId = null;

            function canDrag(el) {
                return el.scrollWidth > el.clientWidth + 2;
            }

            function stopDrag() {
                isDown = false;
                pointerId = null;
                el.classList.remove('drag-scrolling');
                document.body.classList.remove('drag-scroll-active');
                if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
            }

            el.addEventListener('pointerdown', function(e) {
                if (e.pointerType === 'mouse' && e.button !== 0) return;
                if (e.target.closest('a, button, input, select, textarea, label, .btn, .checkbox_ids, .select2, [contenteditable]')) return;
                if (!canDrag(el)) return;
                isDown = true;
                pointerId = e.pointerId;
                el.classList.add('drag-scrolling');
                document.body.classList.add('drag-scroll-active');
                startX = e.clientX;
                scrollLeft = el.scrollLeft;
                if (el.setPointerCapture) {
                    try { el.setPointerCapture(e.pointerId); } catch (err) {}
                }
                e.preventDefault();
            });

            el.addEventListener('pointermove', function(e) {
                if (!isDown) return;
                e.preventDefault();
                var walk = (e.clientX - startX) * 1.5;
                if (rafId) cancelAnimationFrame(rafId);
                rafId = requestAnimationFrame(function() {
                    el.scrollLeft = scrollLeft - walk;
                    rafId = null;
                });
            });

            el.addEventListener('pointerup', stopDrag);
            el.addEventListener('pointercancel', stopDrag);
            el.addEventListener('lostpointercapture', stopDrag);

            document.addEventListener('pointerup', function(e) {
                if (pointerId === null || e.pointerId === pointerId) stopDrag();
            });
        }

        function initAll() {
            document.querySelectorAll('.table-responsive, .drag-scroll, .dataTables_scrollBody').forEach(enableDragScroll);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }

        // Debounced MutationObserver — only fires initAll after DOM settles
        var debounceTimer = null;
        var observer = new MutationObserver(function() {
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(initAll, 300);
        });
        observer.observe(document.body, { childList: true, subtree: true });
    })();
    </script>
    <style>
        .drag-scrolling { cursor: grabbing !important; user-select: none; }
        .drag-scroll-active { user-select: none; }
        .table-responsive, .drag-scroll, .dataTables_scrollBody { cursor: grab; }
        .drag-scroll-active .table-responsive,
        .drag-scroll-active .drag-scroll,
        .drag-scroll-active .dataTables_scrollBody { cursor: grabbing !important; }
    </style>
    <script>
    (function() {
        var toggle = document.getElementById('cbBankFullscreenToggle');
        if (!toggle) return;

        function resizeDataTables() {
            if (!window.jQuery || !jQuery.fn || !jQuery.fn.dataTable) return;
            jQuery('.cb-fullscreen-table table.dataTable').each(function() {
                if (!jQuery.fn.DataTable.isDataTable(this)) return;
                var table = jQuery(this).DataTable();
                table.columns.adjust();
                if (table.scroller && typeof table.scroller.measure === 'function') {
                    table.scroller.measure();
                }
                table.draw(false);
            });
        }

        function isVisible(el) {
            return Boolean(el && (el.offsetWidth || el.offsetHeight || el.getClientRects().length));
        }

        function applyAutoFullscreenTarget(active) {
            document.querySelectorAll('.cb-fullscreen-auto-target').forEach(function(el) {
                el.classList.remove('cb-fullscreen-auto-target', 'cb-fullscreen-table');
            });
            if (!active || document.querySelector('.cb-fullscreen-table')) return;

            var preferredSelectors = [
                '#spp-content',
                '#realisasi-content',
                '#rencana-content',
                '#cashflow-content',
                '#gabungan-content',
                '#table-wrapper',
                '#cashflow-wrapper',
                '#pd-content',
                '#pvd-content',
                '#mk-content',
                '.table-card-rk'
            ];

            for (var i = 0; i < preferredSelectors.length; i++) {
                var target = document.querySelector(preferredSelectors[i]);
                if (isVisible(target)) {
                    target.classList.add('cb-fullscreen-table', 'cb-fullscreen-auto-target');
                    return;
                }
            }

            var visibleTables = Array.prototype.slice.call(document.querySelectorAll('.content-wrapper table'))
                .filter(isVisible);
            if (!visibleTables.length) return;

            var table = visibleTables[0];
            var wrapper = table.closest('.table-responsive, .table-scroll, .tbl-scroll-rk, .card, .invoice') || table.parentElement;
            if (wrapper) {
                wrapper.classList.add('cb-fullscreen-table', 'cb-fullscreen-auto-target');
            }
        }

        function setActive(active) {
            applyAutoFullscreenTarget(active);
            document.body.classList.toggle('cb-table-fullscreen', active);
            try {
                sessionStorage.setItem('cbTableFullscreen', active ? '1' : '0');
                sessionStorage.setItem('cbBankTableFullscreen', active ? '1' : '0');
            } catch (e) {}
            toggle.setAttribute('aria-pressed', active ? 'true' : 'false');
            toggle.innerHTML = active
                ? '<i class="fas fa-compress"></i>'
                : '<i class="fas fa-expand"></i>';
            setTimeout(resizeDataTables, 80);
            setTimeout(resizeDataTables, 240);
        }

        toggle.addEventListener('click', function() {
            var active = !document.body.classList.contains('cb-table-fullscreen');
            setActive(active);
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && document.body.classList.contains('cb-table-fullscreen')) {
                setActive(false);
            }
        });

        try {
            if (sessionStorage.getItem('cbTableFullscreen') === '1' || sessionStorage.getItem('cbBankTableFullscreen') === '1') {
                setActive(true);
            }
        } catch (e) {}
    })();
    </script>

    <script>
    // Simpan status buka/tutup sidebar agar bertahan antar-halaman.
    // AdminLTE PushMenu memancarkan event ini saat tombol toggle diklik.
    $(document).on('collapsed.lte.pushmenu', function () {
        try { localStorage.setItem('cb-sidebar-collapsed', '1'); } catch (e) {}
    });
    $(document).on('shown.lte.pushmenu', function () {
        try { localStorage.setItem('cb-sidebar-collapsed', '0'); } catch (e) {}
    });
    </script>

    @stack('scripts')

</body>

</html>
