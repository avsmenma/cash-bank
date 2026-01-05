    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        
        <!-- style css -->
        <link rel="stylesheet"  href="{{ asset('css/style.css') }}" class="css">
        <link rel="stylesheet" href="https://cdn.datatables.net/2.3.6/css/dataTables.dataTables.css">
       <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
        <script src="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.js"></script>

        <title>Dashboard</title>

        <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://cdn.datatables.net/2.3.6/js/dataTables.js"></script>
        <!-- <script src="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.js"></script> -->
    </head>
    <body>
        <!-- SIDEBAR -->
        <div class="wrapper">
            <aside id="sidebar">
                <div class="d-flex">
                    <button id="toggle-btn" type="button">
                        <!-- <i class="bi bi-columns-gap"></i> -->
                        <img src="{{ asset('images/logoPTPNNew.png') }}" alt="logo PTP" width="50" height="50" >
                    </button>
                    <div class="sidebar-logo">
                        <a href="#">Cash Bank</a>
                    </div>
                </div>
                <hr style="color:white;">
                <ul class="sidebar-nav">
                    <li class="sidebar-item">
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-house"></i>
                            <span class="deskripsi">Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('daftar-spp.index') }}" class="sidebar-link {{ request()->routeIs('daftar-spp.*')? 'active' : '' }}">
                            <i class="bi bi-file-earmark-medical-fill"></i>
                            <span class="deskripsi">Daftar SPP</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="" class="sidebar-link has-dropdown collapsed {{ request()->routeIs('bank-masuk.index','bank-keluar.index') ? 'active' : '' }}"  data-bs-toggle="collapse" data-bs-target="#bank" aria-expanded="{{ request()->routeIs('bank-masuk.*','bank-keluar.*') ? 'true' : 'false' }}" aria-controls="bank">
                    <i class="bi bi-bank"></i>
                        <span class="deskripsi">Daftar Bank </span>
                        </a>

                        <ul id="bank" class="sidebar-dropdown list-item collapse {{ request()->routeIs('bank-masuk.index','bank-keluar.index') ? 'active' : '' }}"  data-bs-parent="#sidebar">
                            <li class="sidebar-item">
                                <a href="{{ route('bank-masuk.index')}} " class="sidebar-link {{ request()->routeIs('bank-masuk.index') ? 'active' : '' }}">
                                    <i class="bi bi-wallet-fill"></i>
                                    Bank Masuk
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="{{ route('bank-keluar.index') }}" class="sidebar-link {{ request()->routeIs('bank-keluar.index')? 'active' : '' }}">
                                    <i class="bi bi-wallet2"></i>
                                Bank Keluar
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('daftarBank.index') }}" class="sidebar-link {{ request()->routeIs('daftarBank.*')? 'active' : '' }}">
                           <i class="bi bi-cash-coin"></i>
                            <span class="deskripsi">Daftar Bank Tujuan</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" 
                        class="sidebar-link has-dropdown collapsed 
                        {{ request()->routeIs('bank-masuk.report','bank-keluar.report') ? 'active' : '' }}"  
                        data-bs-toggle="collapse" 
                        data-bs-target="#report" 
                        aria-expanded="{{ request()->routeIs('bank-masuk.report','bank-keluar.report') ? 'true' : 'false' }}" 
                        aria-controls="report">

                            <i class="bi bi-file-bar-graph"></i>
                            <span class="deskripsi">Daftar Report</span>
                        </a>

                        <ul id="report" 
                            class="sidebar-dropdown list-item collapse 
                            {{ request()->routeIs('bank-masuk.report','bank-keluar.report') ? 'show' : '' }}"  
                            data-bs-parent="#sidebar">

                            <li class="sidebar-item">
                                <a href="{{ route('bank-masuk.report') }}"
                                class="sidebar-link {{ request()->routeIs('bank-masuk.report') ? 'active' : '' }}">
                                    <i class="bi bi-file-bar-graph"></i>
                                    Report Bank Masuk
                                </a>
                            </li>

                            <li class="sidebar-item">
                                <a href="{{ route('bank-keluar.report') }}"
                                class="sidebar-link {{ request()->routeIs('bank-keluar.report') ? 'active' : '' }}">
                                    <i class="bi bi-file-bar-graph"></i>
                                    Report Bank Keluar
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </aside>
            <div class="main p-1" >
                <!-- HEADER -->
                <header class="header d-flex justify-content-between align-items-center p-3">
                    <div class="search-box d-flex align-items-center">
                        <!-- <button type="button" class="btn border-0"><i class="bi bi-search"></i></button> -->
                        
                        
                    </div>
                    <div class="header-icons d-flex align-items-center gap-2">
                        <!-- <button type="button" class="btn border-0">
                            <i class="bi bi-bell"></i>
                        </button> -->

                        <button type="button" class="btn border-0">
                            <i class="bi bi-person"></i> {{ auth()->user()->name }}
                        </button>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn border-0">
                                <i class="bi bi-box-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </header>

                @yield('content')
                <link rel="stylesheet" href="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.css">
                <script src="https://unpkg.com/virtual-select-plugin@1.0.37/dist/virtual-select.min.js"></script>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0/dist/js/bootstrap-select.min.js"></script>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            // sidebar toggler 
            const toggleBtn = document.querySelector("#toggle-btn");
            toggleBtn.addEventListener("click",function(){
                document.querySelector("#sidebar").classList.toggle('expand');
            })

        </script>
    </body>
    </html>