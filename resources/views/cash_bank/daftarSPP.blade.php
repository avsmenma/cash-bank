@extends('layouts/index')
@section('content')
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Daftar SPP</h1>
            </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
              <li class="breadcrumb-item active">Daftar SPP</li>
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="invoice p-3 mb-3">

                        {{-- Filter Bar --}}
                        <div class="row no-print">
                            <div class="col-12">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                    {{-- Status Buttons (toggle: klik lagi untuk deselect) --}}
                                    <button class="btn btn-sm spp-status-btn" data-status="belum"
                                        data-active-bg="#ffc107" data-active-color="#fff"
                                        data-inactive-border="#ffc107" data-inactive-color="#856404"
                                        style="border:1px solid #ffc107; color:#856404; background:#fff;">
                                        <i class="fas fa-clock mr-1"></i> Belum Siap Bayar
                                    </button>
                                    <button class="btn btn-sm spp-status-btn" data-status="siap"
                                        data-active-bg="#007bff" data-active-color="#fff"
                                        data-inactive-border="#007bff" data-inactive-color="#004085"
                                        style="border:1px solid #007bff; color:#004085; background:#fff;">
                                        <i class="fas fa-check-circle mr-1"></i> Siap Bayar
                                    </button>
                                    <button class="btn btn-sm spp-status-btn" data-status="sudah"
                                        data-active-bg="#28a745" data-active-color="#fff"
                                        data-inactive-border="#28a745" data-inactive-color="#155724"
                                        style="border:1px solid #28a745; color:#155724; background:#fff;">
                                        <i class="fas fa-check-double mr-1"></i> Sudah Dibayar
                                    </button>

                                    {{-- Separator --}}
                                    <div style="width:1px;height:28px;background:#ccc;margin:0 4px;"></div>

                                    {{-- Filter Tahun --}}
                                    <select class="form-control form-control-sm" id="filterTahunSPP" style="width:90px;">
                                        @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                                            <option value="{{ $t }}" {{ $t == date('Y') ? 'selected' : '' }}>{{ $t }}</option>
                                        @endfor
                                    </select>

                                    {{-- Filter Dari Bulan --}}
                                    <select class="form-control form-control-sm" id="filterBulanDariSPP" style="width:115px;">
                                        <option value="">Dari Bulan</option>
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>

                                    {{-- Filter Sampai Bulan --}}
                                    <select class="form-control form-control-sm" id="filterBulanSampaiSPP" style="width:115px;">
                                        <option value="">Sampai Bulan</option>
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>

                                    {{-- Tombol Terapkan --}}
                                    <button class="btn btn-primary btn-sm" id="terapkanFilterSPP">
                                        <i class="fas fa-filter"></i> Terapkan
                                    </button>

                                    {{-- Tombol Reset --}}
                                    <button class="btn btn-secondary btn-sm" id="resetFilterSPP">
                                        <i class="fas fa-times"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Show Entries & Search Bar --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-2" style="gap:10px;">
                            <div class="d-flex align-items-center" style="gap:6px; font-size:13px; color:#444; font-weight:500;">
                                Show
                                <select class="form-control form-control-sm" id="sppPerPage" style="width:70px; display:inline-block;">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="-1">Semua</option>
                                </select>
                                entries
                            </div>
                            <div>
                                <input type="text" class="form-control form-control-sm" id="sppSearch"
                                    placeholder="Search..." style="width:220px; display:inline-block;">
                            </div>
                        </div>

                        {{-- Content Area --}}
                        <div id="spp-content">
                            <div class="text-center p-5">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2 text-muted">Memuat data...</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

@push('scripts')
<script>
    $(document).ready(function () {
        var currentStatus = '';
        var currentPage = 1;
        var searchTimer = null;

        function loadSPP(page) {
            page = page || 1;
            currentPage = page;

            var tahun = $('#filterTahunSPP').val();
            var bulanDari = $('#filterBulanDariSPP').val();
            var bulanSampai = $('#filterBulanSampaiSPP').val();
            var perPage = $('#sppPerPage').val();
            var search = $('#sppSearch').val();

            $('#spp-content').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2 text-muted">Memuat data...</p></div>');

            $.ajax({
                url: "{{ route('daftar-spp.data-grouped') }}",
                method: 'GET',
                data: {
                    tahun: tahun,
                    status: currentStatus,
                    bulan_dari: bulanDari,
                    bulan_sampai: bulanSampai,
                    per_page: perPage,
                    page: page,
                    search: search
                },
                success: function (response) {
                    $('#spp-content').html(response);
                },
                error: function (xhr) {
                    console.error('SPP error:', xhr);
                    $('#spp-content').html('<div class="alert alert-danger">Gagal memuat data SPP</div>');
                }
            });
        }

        // Load on page load
        loadSPP(1);

        // Pagination clicks (delegated)
        $(document).on('click', '.spp-page-btn:not(:disabled)', function () {
            var page = $(this).data('page');
            if (page) loadSPP(page);
        });

        // Per page change
        $('#sppPerPage').on('change', function () {
            loadSPP(1);
        });

        // Search with debounce
        $('#sppSearch').on('input', function () {
            if (searchTimer) clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                loadSPP(1);
            }, 400);
        });

        // Status button clicks (toggle behavior)
        $('.spp-status-btn').on('click', function () {
            var $btn = $(this);
            var clickedStatus = $btn.data('status');

            // Toggle: if already active, deselect (show all)
            if (currentStatus === clickedStatus) {
                currentStatus = '';
            } else {
                currentStatus = clickedStatus;
            }

            // Reset all buttons to inactive
            $('.spp-status-btn').each(function () {
                var $b = $(this);
                $b.css({
                    'background': '#fff',
                    'color': $b.data('inactive-color'),
                    'border-color': $b.data('inactive-border')
                });
            });

            // Highlight active button
            if (currentStatus) {
                var $active = $('.spp-status-btn[data-status="' + currentStatus + '"]');
                $active.css({
                    'background': $active.data('active-bg'),
                    'color': $active.data('active-color')
                });
            }

            loadSPP(1);
        });

        // Terapkan filter
        $('#terapkanFilterSPP').on('click', function () {
            loadSPP(1);
        });

        // Reset filter
        $('#resetFilterSPP').on('click', function () {
            $('#filterTahunSPP').val({{ date('Y') }});
            $('#filterBulanDariSPP').val('');
            $('#filterBulanSampaiSPP').val('');
            $('#sppSearch').val('');
            currentStatus = '';
            $('.spp-status-btn').each(function () {
                var $b = $(this);
                $b.css({
                    'background': '#fff',
                    'color': $b.data('inactive-color'),
                    'border-color': $b.data('inactive-border')
                });
            });
            loadSPP(1);
        });
    });
</script>
@endpush
@endsection
