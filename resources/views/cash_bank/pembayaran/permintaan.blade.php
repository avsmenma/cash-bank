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
            <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Daftar
                Permintaan/Rencana</a></li>
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
                      <button type="button" class="btn btn-outline-success" id="btn-import-permintaan" data-toggle="modal"
                        data-target="#modalImportPermintaan">
                        <i class="fas fa-file-upload"></i> Import Excel
                      </button>
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

  {{-- MODAL IMPORT PERMINTAAN (PASTE DARI SPREADSHEET) --}}
  <div class="modal fade" id="modalImportPermintaan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-file-import"></i> Import Data Permintaan/Rencana</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="card-body">
            <div class="alert alert-info mb-2" id="import-permintaan-info"></div>
            <div class="form-group mt-2">
              <label>Paste Data dari Excel</label>
              <p class="text-muted small mb-1">
                Format kolom (tab-separated): <strong>M1 &nbsp; M2 &nbsp; M3 &nbsp; M4 &nbsp; Total</strong><br>
                Setiap baris sesuai urutan item yang tampil di tabel.
              </p>
              <textarea id="permintaan-import-text" class="form-control" rows="12"
                placeholder="Paste data dari spreadsheet di sini..."
                style="font-family: monospace; font-size: 12px;"></textarea>
            </div>
            {{-- Preview area --}}
            <div id="permintaan-import-preview" class="mt-3" style="display:none;">
              <h6>Preview Data <span id="permintaan-import-count" class="badge badge-info">0</span> baris</h6>
              <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-bordered table-sm table-striped" id="permintaan-import-table">
                  <thead class="bg-navy">
                    <tr>
                      <th>No</th>
                      <th>Item</th>
                      <th class="text-right">M1</th>
                      <th class="text-right">M2</th>
                      <th class="text-right">M3</th>
                      <th class="text-right">M4</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-info" id="btn-preview-permintaan">
            <i class="fas fa-eye"></i> Preview
          </button>
          <div>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-success" id="btn-submit-import-permintaan">
              <i class="fas fa-upload"></i> Import Data
            </button>
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

          if (id) {
            $.get('permintaan/sub-kriteria/' + id, function (res) {
              res.forEach(r => {
                $('#sub_kriteria').append(
                  `<option value="${r.id_sub_kriteria}">${r.nama_sub_kriteria}</option>`
                );
              });
            });
          }
        });

        // Load Table
        function loadTable() {
          let sub = $('#sub_kriteria').val();
          let tahun = $('#tahun').val();
          let bulan = $('#bulan').val();

          if (!sub || !tahun || !bulan) {
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
          }, function (html) {
            $('#table-wrapper').html(html);

            // Attach blur event to editable cells
            attachCellEvents();
          }).fail(function () {
            $('#table-wrapper').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Gagal memuat data. Silakan coba lagi.
                        </div>
                    `);
          });
        }

        // Attach events to cells
        function attachCellEvents() {
          // Store original value on focus
          $('.cell').on('focus', function () {
            $(this).data('original-value', $(this).text().replace(/\./g, '').replace(/,/g, '.'));
          });

          $('.cell').on('blur', function () {
            let $cell = $(this);
            let rawValue = $cell.text().replace(/\./g, '').replace(/,/g, '.');
            let nilai = parseFloat(rawValue) || 0;
            let originalValue = parseFloat($cell.data('original-value')) || 0;

            // Only save if value changed
            if (nilai === originalValue) {
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
              .done(function (response) {
                $cell.css('opacity', '1');
                if (response.success) {
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
              .fail(function () {
                $cell.css('opacity', '1');
                alert('Gagal menyimpan data');
                // Restore original value
                $cell.text(formatNumber(originalValue));
              });
          });

          // Format number on input
          $('.cell').on('keypress', function (e) {
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
          $('.cell').on('focus', function () {
            let value = $(this).text().replace(/\./g, '');
            if (value === '0') {
              $(this).text('');
            }
          });
        }

        // Format number with thousand separator
        function formatNumber(num) {
          if (!num || num === 0) return '0';
          return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Trigger load table on dropdown change
        $('#sub_kriteria, #bulan, #tahun').change(loadTable);
        $(document).on('click', '#loadCashflow', function () {
          console.log('Button clicked'); // Debug

          let tahun = $('#tahun_cashflow').val();

          console.log('Tahun:', tahun); // Debug

          if (!tahun) {
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
            success: function (html) {
              console.log('Success loading cashflow'); // Debug
              $('#cashflow-wrapper').html(html);
            },
            error: function (xhr, status, error) {
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
        $('a[href="#timeline"]').on('shown.bs.tab', function () {
          let tahun = $('#tahun_cashflow').val();
          if (tahun && $('#cashflow-wrapper').find('.text-muted').length > 0) {
            $('#loadCashflow').trigger('click');
          }
        });

        // ===== IMPORT PERMINTAAN (PASTE DARI SPREADSHEET) =====

        $('#modalImportPermintaan').on('show.bs.modal', function () {
          let sub = $('#sub_kriteria option:selected').text();
          let bulan = $('#bulan option:selected').text();
          let tahun = $('#tahun').val();
          if ($('#sub_kriteria').val() && tahun && $('#bulan').val()) {
            $('#import-permintaan-info').html(
              '<i class="fas fa-info-circle"></i> <strong>Sub Kriteria:</strong> ' + sub +
              ' &nbsp;|&nbsp; <strong>Bulan:</strong> ' + bulan +
              ' &nbsp;|&nbsp; <strong>Tahun:</strong> ' + tahun
            );
          } else {
            $('#import-permintaan-info').html('<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Harap pilih Sub Kriteria, Bulan, dan Tahun pada filter utama terlebih dahulu.</span>');
          }
          $('#permintaan-import-text').val('');
          $('#permintaan-import-preview').hide();
          $('#permintaan-import-table tbody').empty();
        });

        // Parse angka format Indonesia
        function parseAngkaPermintaan(str) {
          if (!str || str.trim() === '' || str.trim() === '-') return 0;
          let cleaned = str.trim().replace(/\./g, '').replace(/,/g, '.');
          let val = parseInt(cleaned);
          return isNaN(val) ? 0 : val;
        }

        function parsePermintaanPasteData() {
          let text = $('#permintaan-import-text').val().trim();
          if (!text) return [];
          let lines = text.split('\n');
          let rows = [];
          for (let i = 0; i < lines.length; i++) {
            let line = lines[i];
            let cols = line.split('\t');
            while (cols.length < 4) cols.push('');
            rows.push({
              M1: parseAngkaPermintaan(cols[0]),
              M2: parseAngkaPermintaan(cols[1]),
              M3: parseAngkaPermintaan(cols[2]),
              M4: parseAngkaPermintaan(cols[3]),
            });
          }
          return rows;
        }

        // Preview
        $('#btn-preview-permintaan').on('click', function () {
          let rows = parsePermintaanPasteData();
          if (rows.length === 0) {
            alert('Tidak ada data. Paste data dari spreadsheet terlebih dahulu.');
            return;
          }
          let tbody = $('#permintaan-import-table tbody');
          tbody.empty();
          let fmt = (n) => n > 0 ? n.toLocaleString('id-ID') : '-';
          let itemNames = [];
          $('#table-wrapper tbody tr').each(function () {
            let name = $(this).find('td:first').text().trim();
            if (name) itemNames.push(name);
          });
          rows.forEach(function (r, i) {
            let itemName = itemNames[i] || ('Baris ' + (i + 1));
            tbody.append(`
                  <tr>
                    <td>${i + 1}</td>
                    <td>${itemName}</td>
                    <td class="text-right">${fmt(r.M1)}</td>
                    <td class="text-right">${fmt(r.M2)}</td>
                    <td class="text-right">${fmt(r.M3)}</td>
                    <td class="text-right">${fmt(r.M4)}</td>
                  </tr>
                `);
          });
          $('#permintaan-import-count').text(rows.length);
          $('#permintaan-import-preview').show();
        });

        // Submit Import
        $('#btn-submit-import-permintaan').on('click', function () {
          let subId = $('#sub_kriteria').val();
          let bulan = $('#bulan').val();
          let tahun = $('#tahun').val();

          if (!subId || !bulan || !tahun) {
            alert('Harap pilih Sub Kriteria, Bulan, dan Tahun pada filter utama terlebih dahulu.');
            return;
          }

          let rows = parsePermintaanPasteData();
          if (rows.length === 0) {
            alert('Tidak ada data. Paste data dari spreadsheet terlebih dahulu.');
            return;
          }

          let $btn = $(this);
          $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengimport...');

          $.ajax({
            url: '{{ route("permintaan.import") }}',
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: JSON.stringify({
              sub_kriteria: subId,
              bulan: bulan,
              tahun: tahun,
              rows: rows
            }),
            success: function (res) {
              $btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Import Data');
              if (res.success) {
                $('#modalImportPermintaan').modal('hide');
                alert(res.message);
                loadTable();
              } else {
                alert('Gagal: ' + res.message);
              }
            },
            error: function (xhr) {
              $btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Import Data');
              let msg = xhr.responseJSON ? (xhr.responseJSON.message || JSON.stringify(xhr.responseJSON)) : 'Terjadi kesalahan.';
              alert('Import gagal: ' + msg);
            }
          });
        });

      });

    </script>
  @endpush
@endsection