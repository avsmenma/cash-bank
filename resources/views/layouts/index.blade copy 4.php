<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <title >Dashboard</title> -->
    <link rel="icon" href="{{ asset('images/logoPTPNNew.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- AdminLTE -->
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- select 2 -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">

    <!-- picker -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}">
    <!-- Virtual Select -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/dropzone/min/dropzone.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
    
    @stack('styles')
    <style>
        .sidebar .nav-item{
            /* margin-left: 10px; */
            margin-bottom: 20px;
        }
        .sidebar .nav .nav-treeview{
            margin-left: 20px;
        }

    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- NAVBAR -->
    <nav class="main-header navbar navbar-expand navbar-light">
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
    <aside class="main-sidebar sidebar-dark-green elevation-2">
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
                    <li class="nav-item">
                        <a href="#" class="nav-link {{ request()->routeIs('dashboard.*')? 'active' : ''}}" ">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>
                                Dashboard
                            </p>
                            <i class="right fas fa-angle-left"></i>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard')? 'active' : ''}}">
                                    <p>Dashboard V1</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard')? 'active' : ''}}">
                                    <p>Dashboard V2</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- DAFTAR SPP -->
                    <li class="nav-item">
                        <a href="{{ route('daftar-spp.index') }}" class="nav-link {{ request()->routeIs('daftar-spp.*')? 'active' : ''}}">
                            <i class="nav-icon fas fa-copy"></i>
                            <p>Daftar SPP</p>
                        </a>
                    </li>

                    <!-- DAFTAR VA -->
                    <li class="nav-item">
                        <a href="{{ route('daftarBank.index') }}" class="nav-link {{ request()->routeIs('daftarBank.*')? 'active' : ''}}"">
                            <i class="nav-icon fas fa-th"></i>
                            <p>Daftar VA</p>
                        </a>
                    </li>

                    <!-- DAFTAR BANK -->
                    <li class="nav-item">
                        <a href="#" class="nav-link {{ request()->routeIs('bank-masuk.index','bank-keluar.index') ? 'active' : '' }}"">
                            <i class="nav-icon fas fa-university"></i>
                            <p>
                                Daftar Bank
                            </p>
                            <i class="right fas fa-angle-left"></i>
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
                                    </p>
                                    <i class="right fas fa-angle-left"></i>
                                </a>

                                <ul class="nav nav-treeview">
                                    <li class="nav-item"><a href="{{ route('penerima.index') }}" class="nav-link"><p>Penerimaan</p></a></li>
                                    <li class="nav-item"><a href="{{ route('permintaan.index') }}" class="nav-link"><p>Permintaan</p></a></li>
                                    <li class="nav-item"><a href="{{ route('dropping.index') }}" class="nav-link"><p>Dropping</p></a></li>
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
<script src="{{ asset('adminLTE/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>

<!--  DataTables Core -->
<script src="{{ asset('adminLTE/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('adminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

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


@stack('scripts')

</body>
</html>
