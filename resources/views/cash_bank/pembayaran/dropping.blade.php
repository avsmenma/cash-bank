@extends('layouts/index')
@section('content')
  <div class="container-fuild">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Dashboard Pembayaran</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active">Dashboard Pembayaran</li>
              <li class="breadcrumb-item"><a href="{{route('dashboard.pembayaran.index')}}">Dashboard Versi 2</a></li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <div class="col-md-12">
      <div class="card">
        <div class="card-header p-2 cb-fullscreen-tabs">
          <ul class="nav nav-pills">
            <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Realisasi</a></li>
            <!-- <li class="nav-item"><a class="nav-link" href="#rencana" data-toggle="tab">Rencana</a></li> -->
            <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">CashFlow Realisasi</a></li>
            <!-- <li class="nav-item"><a class="nav-link" id="cashflowGabungan-tab" data-toggle="tab" href="#gabungan">CashFlow Gabungan</a></li> -->
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <div class="active tab-pane cb-fullscreen-table" id="activity">
              <div class="row no-print mb-3 cb-fullscreen-controls">
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
                    <div class="d-flex gap-2 cb-fullscreen-support">
                      <a href="{{ url('/bank-masuk/view/pdf')}}" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-print"></i> Download PDF
                      </a>
                      <a href="{{ url('/bank-masuk/export_excel')}}" class="btn btn-outline-danger">
                        <i class="fas fa-file-excel"></i> Download Excel
                      </a>
                      <button type="button" class="btn btn-outline-success" id="btn-import-dropping" data-toggle="modal"
                        data-target="#modalImportDropping">
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
            <div class="tab-pane cb-fullscreen-table" id="timeline">
              <div class="row no-print mb-3 cb-fullscreen-controls">
                <div class="col-12">
                  <div class="d-flex gap-2 align-items-center">
                    <div class="form-group mb-0">
                      <select class="select2" name="tahun_cashflow" id="tahun_cashflow" style="width: 250px;">
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
            {{-- TAB RENCANA --}}
            <div class="tab-pane cb-fullscreen-table" id="rencana">
              <div class="row no-print mb-3 cb-fullscreen-controls">
                <div class="col-12 justify-content-between">
                  <div class="d-flex justify-content-between">
                    <div class="d-flex gap-2">
                      <div class="form-group mb-0">
                        <select id="rencana_kategori" class="select2" style="width: 200px;">
                          <option value="">-- Pilih Kategori --</option>
                          @foreach($kategori as $k)
                            <option value="{{ $k->id_kategori_kriteria }}">{{ $k->nama_kriteria }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="form-group mb-0">
                        <select id="rencana_sub_kriteria" class="select2" style="width: 200px;">
                          <option value="">-- Pilih Sub Kriteria --</option>
                        </select>
                      </div>
                      <div class="form-group mb-0">
                        <select class="select2" id="tahunRencana">
                          @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                            <option value="{{ $t }}">{{ $t }}</option>
                          @endfor
                        </select>
                      </div>
                    </div>
                    <div class="d-flex gap-2 cb-fullscreen-support">
                      <a href="#" class="btn btn-outline-primary"><i class="fas fa-print"></i> Download PDF</a>
                      <a href="#" class="btn btn-outline-danger"><i class="fas fa-file-excel"></i> Download Excel</a>
                      </a>
                    </div>
                  </div>
                </div>
              </div>


              {{-- RENCANA CONTENT - ID UNIK --}}
              <div id="rencana-content" class="p-3">
                <div class="text-center text-muted">
                  <i class="fas fa-spinner fa-spin"></i> Memuat data rencana...
                </div>
              </div>
            </div>
            {{-- TAB GABUNGAN --}}
            <div class="tab-pane cb-fullscreen-table" id="gabungan">
              <div class="row no-print mb-3 cb-fullscreen-controls">
                <div class="col-12">
                  <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-primary cb-fullscreen-support"><i class="fas fa-print"></i> Download PDF</a>
                    <a href="#" class="btn btn-outline-danger cb-fullscreen-support"><i class="fas fa-file-excel"></i> Download Excel</a>
                    <div class="col-md-3">
                      <select class="select2" id="tahunGabungan">
                        @for($t = date('Y') - 5; $t <= date('Y') + 5; $t++)
                          <option value="{{ $t }}">{{ $t }}</option>
                        @endfor
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              {{-- GABUNGAN CONTENT - ID UNIK --}}
              <div id="gabungan-content" class="p-3">
                <div class="text-center text-muted">
                  <i class="fas fa-spinner fa-spin"></i> Memuat CashFlow data gabungan rencana & realisasi ...
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- MODAL IMPORT DROPPING (PASTE DARI SPREADSHEET) --}}
  <div class="modal fade" id="modalImportDropping" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-file-import"></i> Import Data Dropping/Realisasi</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="card-body">
            <div class="alert alert-info mb-2" id="import-dropping-info"></div>
            <div class="form-group mt-2">
              <label>Paste Data dari Excel</label>
              <p class="text-muted small mb-1">
                Format kolom (tab-separated): <strong>M1 &nbsp; M2 &nbsp; M3 &nbsp; M4 &nbsp; Total</strong><br>
                Setiap baris sesuai urutan item yang tampil di tabel.
              </p>
              <textarea id="dropping-import-text" class="form-control" rows="12"
                placeholder="Paste data dari spreadsheet di sini..."
                style="font-family: monospace; font-size: 12px;"></textarea>
            </div>
            {{-- Preview area --}}
            <div id="dropping-import-preview" class="mt-3" style="display:none;">
              <h6>Preview Data <span id="dropping-import-count" class="badge badge-info">0</span> baris</h6>
              <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-bordered table-sm table-striped" id="dropping-import-table">
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
          <button type="button" class="btn btn-info" id="btn-preview-dropping">
            <i class="fas fa-eye"></i> Preview
          </button>
          <div>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-success" id="btn-submit-import-dropping">
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
          width: 'resolve'
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
            $.get('/dropping/sub-kriteria/' + id, function (res) {
              res.forEach(r => {
                $('#sub_kriteria').append(
                  `<option value="${r.id_sub_kriteria}">${r.nama_sub_kriteria}</option>`
                );
              });
            });
          }
        });
        // ===== SUB KRITERIA RENCANA =====
        $('#rencana_kategori').on('change', function () {
          let id = $(this).val();

          $('#rencana_sub_kriteria').html('<option value="">-- Pilih Sub Kriteria --</option>');
          $('#rencana-content').html('<div class="text-muted text-center">Pilih sub kriteria</div>');

          if (!id) return;

          $.get('/dropping/sub-kriteria/' + id, function (res) {
            res.forEach(r => {
              $('#rencana_sub_kriteria').append(
                `<option value="${r.id_sub_kriteria}">${r.nama_sub_kriteria}</option>`
              );
            });
          });
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

          $.get('/dropping/table', {
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

        // Attach events to cells (no auto-save, user clicks Simpan button)
        function attachCellEvents() {
          // Store original value on focus and remove formatting for editing
          $('.cell').on('focus', function () {
            let raw = $(this).text().replace(/\./g, '');
            $(this).data('original-value', raw);
            if (raw === '0') {
              $(this).text('');
            } else {
              $(this).text(raw);
            }
          });

          // Format on blur (display only, no save)
          $('.cell').on('blur', function () {
            let $cell = $(this);
            let raw = $cell.text().replace(/\D/g, '').trim();
            let nilai = parseInt(raw) || 0;
            let originalValue = parseInt($cell.data('original-value')) || 0;

            // Format the display
            $cell.text(formatNumber(nilai));

            // Mark changed cells
            if (nilai !== originalValue) {
              $cell.addClass('cell-changed');
              $cell.css('background-color', '#fff3cd');
            }
          });

          // Only allow numbers on keypress
          $('.cell').on('keypress', function (e) {
            if ($.inArray(e.keyCode, [8, 9, 27, 13]) !== -1 ||
              (e.keyCode === 65 && e.ctrlKey === true) ||
              (e.keyCode === 67 && e.ctrlKey === true) ||
              (e.keyCode === 86 && e.ctrlKey === true) ||
              (e.keyCode === 88 && e.ctrlKey === true)) {
              return;
            }
            if (e.which < 48 || e.which > 57) {
              e.preventDefault();
            }
          });

          // Live format as user types (on input event)
          $('.cell').on('input', function () {
            let $cell = $(this);
            let sel = window.getSelection();
            let raw = $cell.text().replace(/\D/g, '');

            // Don't format while typing to avoid cursor jumps, just clean non-digits
            // Formatting happens on blur
          });
        }

        // Format number with thousand separator "."
        function formatNumber(num) {
          if (!num || num === 0) return '0';
          return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // ===== SIMPAN BUTTON HANDLER =====
        $(document).on('click', '#btn-simpan-dropping', function () {
          let $btn = $(this);
          let changedCells = $('.cell-changed');

          if (changedCells.length === 0) {
            alert('Tidak ada perubahan untuk disimpan.');
            return;
          }

          let dataToSave = [];
          changedCells.each(function () {
            let $cell = $(this);
            let raw = $cell.text().replace(/\./g, '').trim();
            dataToSave.push({
              item: $cell.data('item'),
              sub_kriteria: $cell.data('sub'),
              bulan: $cell.data('bulan'),
              tahun: $cell.data('tahun'),
              kolom: $cell.data('kolom'),
              nilai: parseInt(raw) || 0
            });
          });

          $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

          $.ajax({
            url: '/dropping/save-batch',
            method: 'POST',
            contentType: 'application/json',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: JSON.stringify({ items: dataToSave }),
            success: function (response) {
              $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
              if (response.success) {
                alert('Data berhasil disimpan!');
                // Reload table to update totals
                loadTable();
              } else {
                alert('Gagal menyimpan: ' + (response.message || 'Unknown error'));
              }
            },
            error: function (xhr) {
              $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
              let errorMsg = 'Gagal menyimpan data. Silakan coba lagi.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
              }
              alert(errorMsg);
              console.error('Save error:', xhr);
            }
          });
        });

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
            url: '/dropping/cashflow',
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


        // ===== TAB RENCANA =====
        $('#rencana-tab').on('shown.bs.tab', function () {
          console.log('Rencana tab shown');
          loadRencana();
        });

        $('#tahunRencana').on('change', function () {
          console.log('Tahun rencana changed:', $(this).val());
          loadRencana();
        });

        function loadRencana() {
          const tahun = $('#tahunRencana').val();
          const sub = $('#rencana_sub_kriteria').val();

          if (!tahun || !sub) return;

          $('#rencana-content').html('<div class="text-center p-3">Memuat data...</div>');

          $.get("{{ route('dropping.rencana') }}", {
            tahun: tahun,
            sub: sub
          }, function (html) {
            $('#rencana-content').html(html);
          }).fail(function () {
            $('#rencana-content').html('<div class="alert alert-danger">Gagal memuat rencana</div>');
          });
        }

        $('#rencana_sub_kriteria, #tahunRencana').on('change', loadRencana);

        // ===== TAB GABUNGAN =====
        $('#cashflowGabungan-tab').on('shown.bs.tab', function () {
          loadGabungan();
        });

        $('#tahunGabungan').on('change', function () {
          loadGabungan();
        });

        function loadGabungan() {
          const tahun = $('#tahunGabungan').val();

          $('#gabungan-content').html(
            '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>'
          );

          $.ajax({
            url: "{{ route('dropping.gabungan') }}",
            type: 'GET',
            data: { tahun: tahun },
            success: function (res) {
              $('#gabungan-content').html(res);
            },
            error: function (xhr) {
              console.error(xhr.responseText);
              $('#gabungan-content').html(
                '<div class="alert alert-danger">Gagal memuat data</div>'
              );
            }
          });
        }
      });

      // ===== IMPORT DROPPING (PASTE DARI SPREADSHEET) =====

      // Simpan daftar item dari tabel yang sedang tampil
      let droppingItemList = [];

      // Update item list setiap kali tabel dimuat
      $(document).on('tableLoaded', function () {
        droppingItemList = [];
        $('#table-wrapper [data-item]').each(function () {
          let itemId = $(this).data('item');
          let itemName = $(this).closest('tr').find('td:first').text().trim();
          if (itemId && !droppingItemList.find(x => x.id == itemId)) {
            droppingItemList.push({ id: itemId, name: itemName });
          }
        });
      });

      $('#modalImportDropping').on('show.bs.modal', function () {
        let sub = $('#sub_kriteria option:selected').text();
        let bulan = $('#bulan option:selected').text();
        let tahun = $('#tahun').val();
        if ($('#sub_kriteria').val() && tahun && $('#bulan').val()) {
          $('#import-dropping-info').html(
            '<i class="fas fa-info-circle"></i> <strong>Sub Kriteria:</strong> ' + sub +
            ' &nbsp;|&nbsp; <strong>Bulan:</strong> ' + bulan +
            ' &nbsp;|&nbsp; <strong>Tahun:</strong> ' + tahun
          );
        } else {
          $('#import-dropping-info').html('<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Harap pilih Sub Kriteria, Bulan, dan Tahun pada filter utama terlebih dahulu.</span>');
        }
        $('#dropping-import-text').val('');
        $('#dropping-import-preview').hide();
        $('#dropping-import-table tbody').empty();
      });

      // Parse angka format Indonesia (misal "6.300.168" -> 6300168)
      function parseAngkaDropping(str) {
        if (!str || str.trim() === '' || str.trim() === '-') return 0;
        let cleaned = str.trim().replace(/\./g, '').replace(/,/g, '.');
        let val = parseInt(cleaned);
        return isNaN(val) ? 0 : val;
      }

      function parseDroppingPasteData() {
        let text = $('#dropping-import-text').val().trim();
        if (!text) return [];
        let lines = text.split('\n');
        let rows = [];
        for (let i = 0; i < lines.length; i++) {
          let line = lines[i];
          // Jangan trim baris agar tab di awal tetap ada
          let cols = line.split('\t');
          // Pad ke minimal 4 kolom
          while (cols.length < 4) cols.push('');
          rows.push({
            M1: parseAngkaDropping(cols[0]),
            M2: parseAngkaDropping(cols[1]),
            M3: parseAngkaDropping(cols[2]),
            M4: parseAngkaDropping(cols[3]),
          });
        }
        return rows;
      }

      // Preview
      $('#btn-preview-dropping').on('click', function () {
        let rows = parseDroppingPasteData();
        if (rows.length === 0) {
          alert('Tidak ada data. Paste data dari spreadsheet terlebih dahulu.');
          return;
        }
        let tbody = $('#dropping-import-table tbody');
        tbody.empty();
        let fmt = (n) => n > 0 ? n.toLocaleString('id-ID') : '-';
        // Ambil nama item dari tabel (berurutan)
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
        $('#dropping-import-count').text(rows.length);
        $('#dropping-import-preview').show();
      });

      // Submit Import
      $('#btn-submit-import-dropping').on('click', function () {
        let subId = $('#sub_kriteria').val();
        let bulan = $('#bulan').val();
        let tahun = $('#tahun').val();

        if (!subId || !bulan || !tahun) {
          alert('Harap pilih Sub Kriteria, Bulan, dan Tahun pada filter utama terlebih dahulu.');
          return;
        }

        let rows = parseDroppingPasteData();
        if (rows.length === 0) {
          alert('Tidak ada data. Paste data dari spreadsheet terlebih dahulu.');
          return;
        }

        let $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengimport...');

        $.ajax({
          url: '{{ route("dropping.import") }}',
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
              $('#modalImportDropping').modal('hide');
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

    </script>
  @endpush
@endsection
