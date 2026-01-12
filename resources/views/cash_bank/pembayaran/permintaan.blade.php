@extends('layouts/index')
@section('content')
<div class="container-fuild">
  <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Permintaan</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active"><a href="{{route('dashboard.index')}}">Dashboard Pembayaran</a></li>
              <li class="breadcrumb-item"><a href="{{route('dashboard.pembayaran.index')}}">Dashboard Versi 2</a></li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
  <div class="col-md-12">
    <div class="card">
      <div class="card-header p-2">
        <ul class="nav nav-pills">
          <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Daftar Permintaan/Rencana</a></li>
          <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">CashFlow</a></li>
        </ul>
      </div>
      
      <div class="card-body">
        <div class="tab-content">
          <div class="active tab-pane" id="activity">
            <div class="row no-print mb-3">
              <div class="col-12">
                <div class="d-flex justify-content-between">
                  <div class="d-flex gap-2">
                    <div class="form-group mb-0">
                      <select id="kategori" class="select2" style="width: 200px;">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategori as $k)
                          <option value="{{ $k->id_kategori_kriteria }}">{{ $k->nama_kriteria }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group mb-0">
                      <select id="sub_kriteria" class="select2" style="width: 200px;">
                        <option value="">-- Pilih Sub Kriteria --</option>
                      </select>
                    </div>
                    <div class="form-group mb-0">
                      <select class="select2" name="tahun" id="tahun" style="width: 150px;">
                        <option value="">-- Pilih Tahun --</option>
                        @for($y = date('Y'); $y <= date('Y') + 5; $y++)
                          <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                      </select>
                    </div>
                    <div class="form-group mb-0">
                      <select class="select2" name="bulan" id="bulan" style="width: 150px;">
                        <option value="">-- Pilih Bulan --</option>
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
                    </div>
                  </div>
                  <div class="d-flex gap-2">
                    <a href="{{ url('/bank-masuk/view/pdf')}}" target="_blank" class="btn btn-outline-primary">
                      <i class="fas fa-print"></i> Download PDF
                    </a>
                    <a href="{{ url('/bank-masuk/export_excel')}}" class="btn btn-outline-danger">
                      <i class="fas fa-file-excel"></i> Download Excel
                    </a>
                  </div>
                </div>
              </div>
            </div>
            
            <div id="table-wrapper">
              <div class="text-muted text-center py-5">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <p>Silakan pilih Kategori, Sub Kriteria, Tahun, dan Bulan untuk menampilkan data</p>
              </div>
            </div>
          </div>
          
          <div class="tab-pane" id="timeline">
            <div class="row no-print mb-3">
              <div class="col-12">
                <div class="d-flex gap-2 align-items-center">
                  <div class="form-group mb-0">
                    <select class="select2" name="tahun_cashflow" id="tahun_cashflow" style="width: 150px;">
                      <option value="">-- Pilih Tahun --</option>
                      @for($y = date('Y'); $y <= date('Y') + 5; $y++)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                      @endfor
                    </select>
                  </div>
                  <button id="loadCashflow" class="btn btn-primary">
                    <i class="fas fa-sync"></i> Load Data
                  </button>
                </div>
              </div>
            </div>
            
            <div id="cashflow-wrapper">
              <div class="text-muted text-center py-5">
                <i class="fas fa-chart-line fa-2x mb-2"></i>
                <p>Pilih tahun dan klik "Load Data" untuk menampilkan cashflow</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Load Sub Kriteria based on Kategori
    $('#kategori').change(function () {
        let id = $(this).val();
        $('#sub_kriteria').html('<option value="">-- Pilih Sub Kriteria --</option>');
        $('#table-wrapper').html(`
            <div class="text-muted text-center py-5">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <p>Silakan pilih Kategori, Sub Kriteria, Tahun, dan Bulan untuk menampilkan data</p>
            </div>
        `);

        if(id){
            $.get('permintaan/sub-kriteria/' + id, function(res){
                res.forEach(r => {
                    $('#sub_kriteria').append(
                        `<option value="${r.id_sub_kriteria}">${r.nama_sub_kriteria}</option>`
                    );
                });
            });
        }
    });

    // Load Table
    function loadTable(){
        let sub = $('#sub_kriteria').val();
        let tahun = $('#tahun').val();
        let bulan = $('#bulan').val();

        if(!sub || !tahun || !bulan) {
            return;
        }

        $('#table-wrapper').html(`
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Memuat data...</p>
            </div>
        `);

        $.get('/permintaan/table', {
            sub: sub,
            tahun: tahun,
            bulan: bulan
        }, function(html){
            $('#table-wrapper').html(html);
            
            // Attach blur event to editable cells
            attachCellEvents();
        }).fail(function(){
            $('#table-wrapper').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Gagal memuat data. Silakan coba lagi.
                </div>
            `);
        });
    }

    // Attach events to cells
    function attachCellEvents(){
        // Store original value on focus
        $('.cell').on('focus', function(){
            $(this).data('original-value', $(this).text().replace(/\./g, '').replace(/,/g, '.'));
        });

        $('.cell').on('blur', function(){
            let $cell = $(this);
            let rawValue = $cell.text().replace(/\./g, '').replace(/,/g, '.');
            let nilai = parseFloat(rawValue) || 0;
            let originalValue = parseFloat($cell.data('original-value')) || 0;
            
            // Only save if value changed
            if(nilai === originalValue){
                return;
            }
            
            // Show loading indicator
            $cell.css('opacity', '0.5');

            $.post('/permintaan/save', {
                _token: '{{ csrf_token() }}',
                item: $cell.data('item'),
                sub_kriteria: $cell.data('sub'),
                bulan: $cell.data('bulan'),
                tahun: $cell.data('tahun'),
                kolom: $cell.data('kolom'),
                nilai: nilai
            })
            .done(function(response){
                $cell.css('opacity', '1');
                if(response.success){
                    // Format the number
                    $cell.text(formatNumber(nilai));
                    
                    // Show success animation
                    $cell.addClass('bg-success-light');
                    setTimeout(() => {
                        $cell.removeClass('bg-success-light');
                    }, 1000);
                    
                    // Reload table to update totals
                    setTimeout(() => {
                        loadTable();
                    }, 500);
                }
            })
            .fail(function(){
                $cell.css('opacity', '1');
                alert('Gagal menyimpan data');
                // Restore original value
                $cell.text(formatNumber(originalValue));
            });
        });

        // Format number on input
        $('.cell').on('keypress', function(e){
            // Allow: backspace, delete, tab, escape, enter
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            // Only allow numbers, comma, and period
            if ((e.which < 48 || e.which > 57) && e.which !== 44 && e.which !== 46) {
                e.preventDefault();
            }
        });

        // Remove formatting when editing
        $('.cell').on('focus', function(){
            let value = $(this).text().replace(/\./g, '');
            if(value === '0') {
                $(this).text('');
            }
        });
    }

    // Format number with thousand separator
    function formatNumber(num){
        if(!num || num === 0) return '0';
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Trigger load table on dropdown change
    $('#sub_kriteria, #bulan, #tahun').change(loadTable);
    $(document).on('click', '#loadCashflow', function(){
        console.log('Button clicked'); // Debug
        
        let tahun = $('#tahun_cashflow').val();
        
        console.log('Tahun:', tahun); // Debug
        
        if(!tahun){
            alert('Silakan pilih tahun terlebih dahulu');
            return;
        }

        $('#cashflow-wrapper').html(`
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Memuat data cashflow...</p>
            </div>
        `);

        $.ajax({
            url: '/permintaan/cashflow',
            method: 'GET',
            data: { tahun: tahun },
            success: function(html){
                console.log('Success loading cashflow'); // Debug
                $('#cashflow-wrapper').html(html);
            },
            error: function(xhr, status, error){
                console.error('Error:', error); // Debug
                console.error('Response:', xhr.responseText); // Debug
                $('#cashflow-wrapper').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Gagal memuat data cashflow. 
                        <br><small>Error: ${error}</small>
                    </div>
                `);
            }
        });
    });

    // Auto load cashflow saat tab diklik (opsional)
    $('a[href="#timeline"]').on('shown.bs.tab', function(){
        let tahun = $('#tahun_cashflow').val();
        if(tahun && $('#cashflow-wrapper').find('.text-muted').length > 0){
            $('#loadCashflow').trigger('click');
        }
    });
  });
    
</script>
@endpush
@endsection