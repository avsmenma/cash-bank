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

                        {{-- Search Bar (semua data tampil bertahap saat scroll, tanpa pagination) --}}
                        <div class="d-flex justify-content-end align-items-center flex-wrap mb-2 cb-fullscreen-hide" style="gap:10px;">
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
        var searchTimer = null;

        // Infinite scroll: semua data dimuat bertahap per 100 baris — chunk
        // berikutnya di-fetch saat scroll mendekati dasar tabel, lalu di-append.
        var CHUNK_SIZE = 100;
        var PREFETCH_PX = 600;
        var loadedPage = 1;
        var totalRecords = 0;
        var loadingNext = false;

        function filterParams(page) {
            return {
                tahun: $('#filterTahunSPP').val(),
                status: currentStatus,
                bulan_dari: $('#filterBulanDariSPP').val(),
                bulan_sampai: $('#filterBulanSampaiSPP').val(),
                per_page: CHUNK_SIZE,
                page: page,
                search: $('#sppSearch').val(),
                infinite: 1
            };
        }

        function loadedCount() {
            return $('#sppTableBody tr.spp-row-data').length;
        }

        function updateLoadInfo(errorText) {
            var $info = $('#sppLoadInfo');
            if (!$info.length) return;
            if (errorText) {
                $info.text(errorText);
                return;
            }
            var count = loadedCount();
            $info.text(count >= totalRecords
                ? 'Semua ' + totalRecords.toLocaleString('id-ID') + ' data telah dimuat.'
                : count.toLocaleString('id-ID') + ' dari ' + totalRecords.toLocaleString('id-ID') +
                  ' data dimuat — scroll ke bawah untuk memuat berikutnya.');
        }

        function removeLoaderRow() {
            $('#sppTableBody tr.spp-loader-row').remove();
        }

        function showLoaderRow(message, isError) {
            removeLoaderRow();
            var colspan = $('#sppScrollBox thead th').length || 13;
            var $row = $('<tr class="spp-loader-row' + (isError ? ' has-error' : '') + '">' +
                '<td colspan="' + colspan + '">' + message + '</td></tr>');
            if (isError) {
                $row.on('click', function () { loadNextChunk(true); });
            }
            $('#sppTableBody').append($row);
        }

        function loadNextChunk(force) {
            var box = document.getElementById('sppScrollBox');
            if (!box || loadingNext) return;
            if (loadedCount() >= totalRecords) return;
            if (!force && box.scrollTop + box.clientHeight < box.scrollHeight - PREFETCH_PX) return;

            loadingNext = true;
            showLoaderRow('<i class="fas fa-spinner fa-spin"></i> Memuat data berikutnya...', false);

            $.ajax({
                url: "{{ route('daftar-spp.data-grouped') }}",
                method: 'GET',
                data: filterParams(loadedPage + 1),
                success: function (rowsHtml) {
                    removeLoaderRow();
                    $('#sppTableBody').append(rowsHtml);
                    loadedPage++;
                    updateLoadInfo();
                    // Layar tinggi: pastikan viewport terisi tanpa menunggu scroll baru.
                    window.requestAnimationFrame(function () { loadNextChunk(false); });
                },
                error: function (xhr) {
                    console.error('SPP chunk error:', xhr);
                    showLoaderRow('<i class="fas fa-exclamation-triangle"></i> Gagal memuat data berikutnya. Klik di sini untuk mencoba lagi.', true);
                },
                complete: function () {
                    loadingNext = false;
                }
            });
        }

        function bindScrollBox() {
            var box = document.getElementById('sppScrollBox');
            if (!box) return;
            totalRecords = parseInt(box.dataset.total, 10) || 0;
            loadedPage = parseInt(box.dataset.page, 10) || 1;
            updateLoadInfo();
            box.addEventListener('scroll', function () { loadNextChunk(false); }, { passive: true });
            loadNextChunk(false);
        }

        function loadSPP() {
            $('#spp-content').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2 text-muted">Memuat data...</p></div>');

            $.ajax({
                url: "{{ route('daftar-spp.data-grouped') }}",
                method: 'GET',
                data: filterParams(1),
                success: function (response) {
                    $('#spp-content').html(response);
                    bindScrollBox();
                },
                error: function (xhr) {
                    console.error('SPP error:', xhr);
                    $('#spp-content').html('<div class="alert alert-danger">Gagal memuat data SPP</div>');
                }
            });
        }

        // Load on page load
        loadSPP();

        // Search with debounce
        $('#sppSearch').on('input', function () {
            if (searchTimer) clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                loadSPP();
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

            loadSPP();
        });

        // Terapkan filter
        $('#terapkanFilterSPP').on('click', function () {
            loadSPP();
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
            loadSPP();
        });
    });
</script>
@endpush
@endsection
