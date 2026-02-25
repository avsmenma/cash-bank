<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/logoPTPNNew.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="{{ asset('adminLTE/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminLTE/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet"
        href="{{ asset('adminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- select 2 -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">

    @stack('styles')
    <style>
        .sidebar .nav-item {
            margin-bottom: 20px;
        }

        .sidebar .nav .nav-treeview {
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
            </ul>

            <ul class="navbar-nav ml-auto">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn border-0">
                        <i class=" fa fa-compress-arrows-alt"></i> Logout
                    </button>
                </form>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-light-green elevation-2">
            <!-- Brand Logo -->
            <a href="{{ route('va.dashboard') }}" class="brand-link">
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

                        <!-- VIRTUAL ACCOUNT -->
                        <li class="nav-item">
                            <a href="{{ route('va.dashboard') }}"
                                class="nav-link {{ request()->routeIs('va.dashboard') ? 'active' : ''}}">
                                <i class="nav-icon fas fa-th"></i>
                                <p>Virtual Account</p>
                            </a>
                        </li>

                        <!-- REKENING KORAN -->
                        <li class="nav-item">
                            <a href="{{ route('va.rekening-koran') }}"
                                class="nav-link {{ request()->routeIs('va.rekening-koran') ? 'active' : ''}}">
                                <i class="nav-icon fas fa-file-invoice"></i>
                                <p>Rekening Koran</p>
                            </a>
                        </li>

                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- CONTENT -->
        <div class="content-wrapper p-3">
            @yield('content')

            @yield('script')
        </div>

    </div>
    <!--  jQuery -->
    <script src="{{ asset('adminLTE/plugins/jquery/jquery.min.js') }}"></script>

    <!--  Bootstrap 4 -->
    <script src="{{ asset('adminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!--  Plugin inti UI -->
    <script src="{{ asset('adminLTE/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('adminLTE/dist/js/adminlte.js') }}"></script>

    <!--  Select2 -->
    <script src="{{ asset('adminLTE/plugins/select2/js/select2.full.min.js') }}"></script>

    <!--  Moment.js -->
    <script src="{{ asset('adminLTE/plugins/moment/moment.min.js') }}"></script>

    <!--  Tempus Dominus -->
    <script
        src="{{ asset('adminLTE/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>

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

    <!--  Custom File Input -->
    <script src="{{ asset('adminLTE/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script src="{{ asset('adminLTE/plugins/chart.js/Chart.min.js')}}"></script>

    @stack('scripts')

</body>

</html>