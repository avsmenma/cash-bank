<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('adminLTE/dist/css/adminlte.min.css') }}">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    
    <!-- Virtual Select -->
<link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
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
                <a href="index3.html" class="nav-link">Home</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">Contact</a>
            </li> -->
        </ul>

        <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
            <li class="nav-item">
                <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                    <i class="bi bi-person"></i> {{ auth()->user()->name }}
                </a>
            </li>
            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <a type="submit" class="nav-link" data-widget="navbar-search" href="#" role="button">
                                    <i class="nav-icon  fa-arrow-right "></i>
                                </a>
                    </form>

            <!-- Messages Dropdown Menu -->
            
    </nav>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link">
        <img src="{{ asset('images/logoPTPNNew.png') }}" alt="PTPN Logo" class="align-center" style="opacity: .8" width="50" height="50">
        <span class="brand-text font-weight-bold text-center">Cash <span>Bank</span></span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">

        <!-- Sidebar Menu -->
        <nav class="mt-5">
            <ul class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview"
                role="menu"
                data-accordion="false">

                <!-- DASHBOARD -->
                <!-- <li class="nav-item">
                    <a href="#" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                       <i class="nav-icon fas fa-copy"></i>
                        <p>
                            Dashboard
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nnav-item menu-open">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} ">
                                <p>Dashboard Pertama</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('bank-masuk.index') }}" class="nav-link">
                                <p>Dashboard Kedua</p>
                            </a>
                        </li>
                    </ul>
                </li> -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>
                        Dashboard
                        <i class="right fas fa-angle-left"></i>
                    </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link">
                            <!-- <i class="far fa-circle nav-icon"></i> -->
                            <p>Dashboard v1</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link">
                            <!-- <i class="far fa-circle nav-icon"></i> -->
                            <p>Dashboard v2</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- DAFTAR SPP -->
                <li class="nav-item">
                    <a href="{{ route('daftar-spp.index') }}" class="nav-link">
                        <i class="fa-regular fa-file"></i>
                        <p>Daftar SPP</p>
                    </a>
                </li>

                <!-- DAFTAR VA -->
                <li class="nav-item">
                    <a href="{{ route('daftarBank.index') }}" class="nav-link">
                        <i class="nav-icon fa-coins"></i>
                        <p>Daftar VA</p>
                    </a>
                </li>

                <!-- DAFTAR BANK -->
                <li class="nav-item">
                    <a href="#" class="nav-link {{ request()->routeIs('bank-masuk.index','bank-keluar.index') ? 'active' : '' }}"">
                        <i class="nav-icon fas fa-university"></i>
                        <p>
                            Daftar Bank
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('bank-masuk.index') }}" class="nav-link {{ request()->routeIs('bank-masuk.index') ? 'active' : '' }}">
                                
                                <p>Bank Masuk</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <p>
                                    Bank Keluar
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item"><a href="{{ route('penerima') }}" class="nav-link"><i class="bi bi-clipboard2-check"></i><p>Penerimaan</p></a></li>
                                <li class="nav-item"><a href="{{ route('dropping') }}" class="nav-link"><p>Dropping</p></a></li>
                                <li class="nav-item">
                                    <a href="{{ route('bank-keluar.index') }}" class="nav-link">
                                        <p>Pembayaran</p>
                                    </a>
                                </li>
                            </ul>
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
    </div>

</div>

<!-- jQuery (HARUS PALING PERTAMA) -->
<script src="{{ asset('adminLTE/plugins/jquery/jquery.min.js') }}"></script>

<!-- Bootstrap -->
<script src="{{ asset('adminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- overlayScrollbars -->
<script src="{{ asset('adminLTE/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>

<!-- AdminLTE -->
<script src="{{ asset('adminLTE/dist/js/adminlte.js') }}"></script>

<!-- DataTables Core -->
<script src="{{ asset('adminLTE/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('adminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

<!-- DataTables Responsive -->
<script src="{{ asset('adminLTE/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('adminLTE/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<!-- JSZip (required for Excel export) -->
<script src="{{ asset('adminLTE/plugins/jszip/jszip.min.js') }}"></script>

<!-- PDFMake (required for PDF export) -->
<script src="{{ asset('adminLTE/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('adminLTE/plugins/pdfmake/vfs_fonts.js') }}"></script>

<!-- DataTables Buttons -->
<script src="{{ asset('adminLTE/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('adminLTE/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('adminLTE/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('adminLTE/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('adminLTE/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

<!-- Virtual Select -->
<script src="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.js"></script>
@stack('scripts')

</body>
</html>
