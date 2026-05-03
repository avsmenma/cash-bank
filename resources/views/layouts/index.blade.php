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
        body.cb-table-fullscreen .cb-fullscreen-hide {
            display: none !important;
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
            transition: width .18s ease, box-shadow .18s ease;
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
            padding: 0 !important;
            min-height: 100vh !important;
            height: 100vh !important;
            overflow: hidden !important;
            background: #fff !important;
        }
        body.cb-table-fullscreen .content,
        body.cb-table-fullscreen .content-wrapper > .container-fluid,
        body.cb-table-fullscreen .content .container-fluid {
            width: 100% !important;
            max-width: none !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body.cb-table-fullscreen .cb-fullscreen-table {
            width: 100% !important;
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
        }
        body.cb-table-fullscreen .cb-fullscreen-table .table-responsive {
            height: calc(100vh - 24px) !important;
            max-height: calc(100vh - 24px) !important;
            overflow: auto !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- NAVBAR -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <!-- <li class="nav-item d-none d-sm-inline-block">
                <a href="index3.html" class="nav-link">Dashboard</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">Contact</a>
            </li> -->
            </ul>

            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->
                @if(request()->routeIs('bank-masuk.*', 'bank-keluar.*', 'daftarBank.*', 'rekening-koran.*'))
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
            <!-- Brand Logo -->
            <a href="#" class="brand-link">
                <img src="{{ asset('images/logoPTPNNew.png') }}" alt="PTPN Logo" class="align-center"
                    style="opacity: .8" width="50" height="50">
                <span class="brand-text font-weight-bold text-center">Cash <span>Bank</span></span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">

                <!-- Sidebar Menu -->
                <nav class="mt-5">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
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

                            <ul class="nav nav-treeview">
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

                            <ul class="nav nav-treeview">
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
            var isDown = false, startX, scrollLeft, rafId = null;

            el.addEventListener('mousedown', function(e) {
                if (e.target.closest('a, button, input, select, textarea, label, .btn, .checkbox_ids, .select2, [contenteditable]')) return;
                isDown = true;
                el.classList.add('drag-scrolling');
                startX = e.pageX - el.getBoundingClientRect().left - window.scrollX;
                scrollLeft = el.scrollLeft;
                e.preventDefault();
            });

            el.addEventListener('mouseleave', function() {
                isDown = false;
                el.classList.remove('drag-scrolling');
                if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
            });

            el.addEventListener('mouseup', function() {
                isDown = false;
                el.classList.remove('drag-scrolling');
                if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
            });

            el.addEventListener('mousemove', function(e) {
                if (!isDown) return;
                e.preventDefault();
                var x = e.pageX - el.getBoundingClientRect().left - window.scrollX;
                var walk = (x - startX) * 1.5;
                if (rafId) cancelAnimationFrame(rafId);
                rafId = requestAnimationFrame(function() {
                    el.scrollLeft = scrollLeft - walk;
                    rafId = null;
                });
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
        .table-responsive, .drag-scroll, .dataTables_scrollBody { cursor: grab; }
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

        function setActive(active) {
            document.body.classList.toggle('cb-table-fullscreen', active);
            try {
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
            if (active && document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen().catch(function() {
                    setActive(true);
                });
                setActive(true);
                return;
            }
            if (!active && document.fullscreenElement && document.exitFullscreen) {
                document.exitFullscreen();
                return;
            }
            setActive(active);
        });

        document.addEventListener('fullscreenchange', function() {
            setActive(Boolean(document.fullscreenElement));
        });
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && document.body.classList.contains('cb-table-fullscreen') && !document.fullscreenElement) {
                setActive(false);
            }
        });

        try {
            if (sessionStorage.getItem('cbBankTableFullscreen') === '1') {
                setActive(true);
            }
        } catch (e) {}
    })();
    </script>

    @stack('scripts')

</body>

</html>
