
@if(isset($kategori) && $kategori->count() > 0)
@php
$bulanList = ['januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];
$totalPerBulan = array_fill_keys($bulanList, 0);
$grandTotal = 0;
@endphp
<div class="row">
    <div class="col-12 table-responsive">
        <table class="table table-bordered" id="tabel-rencana-penerima">
            <thead class="table-dark">
                <tr>
                    <th>Kategori</th>
                    @foreach($bulanList as $b)
                        <th>{{ ucfirst($b) }}</th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kategori as $k)
                    @php
                        $row = $data[$k->id_kategori_kriteria] ?? null;
                        $total = 0;
                    @endphp
                    <tr data-kategori="{{ $k->id_kategori_kriteria }}">
                        <td>{{ $k->nama_kriteria }}</td>

                        @foreach($bulanList as $b)
                            @php
                                $nilai = $row->$b ?? 0;
                                $total += $nilai;
                                $totalPerBulan[$b] += $nilai;
                                $grandTotal += $nilai;
                            @endphp
                            <td contenteditable="true"
                                class="cell-rencana text-right"
                                data-id="{{ $row->id_rencana_penerima ?? '' }}"
                                data-kategori="{{ $k->id_kategori_kriteria }}"
                                data-bulan="{{ $b }}"
                                data-tahun="{{ $tahun }}"
                                data-original="{{ number_format($nilai, 0, ',', '.') }}"
                                style="min-width:90px; cursor:text;">
                                {{ number_format($nilai, 0, ',', '.') }}
                            </td>
                        @endforeach

                        <td class="text-right font-weight-bold row-total" style="min-width:90px;">
                            {{ number_format($total, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <th>Total</th>
                    @foreach($bulanList as $b)
                        <th class="text-right col-total-{{ $b }}">
                            {{ number_format($totalPerBulan[$b], 0, ',', '.') }}
                        </th>
                    @endforeach
                    <th class="text-right grand-total">
                        {{ number_format($grandTotal, 0, ',', '.') }}
                    </th>
                </tr>
            </tfoot>
        </table>

        {{-- Highlight cell yang berubah --}}
        <style>
            .cell-rencana { transition: background 0.15s; }
            .cell-rencana.cell-changed {
                background-color: #fff3cd !important;
                border: 1.5px solid #ffc107 !important;
            }
            .cell-rencana:focus { outline: 2px solid #007bff; }
        </style>

        <script>
        (function () {
            function unformat(str) {
                return parseInt(String(str || '0').replace(/\./g, '').replace(/,/g, '')) || 0;
            }

            var tabel = document.getElementById('tabel-rencana-penerima');
            if (!tabel) return;

            // Highlight cell berubah & update row/col total real-time
            tabel.addEventListener('input', function (e) {
                var cell = e.target;
                if (!cell.classList.contains('cell-rencana')) return;

                var text     = cell.innerText.trim();
                var original = cell.getAttribute('data-original');
                cell.classList.toggle('cell-changed', text !== original);

                // Update row total
                var row = cell.closest('tr');
                var rowCells = row.querySelectorAll('.cell-rencana');
                var rowTotal = 0;
                rowCells.forEach(function (c) { rowTotal += unformat(c.innerText); });
                var rowTotalEl = row.querySelector('.row-total');
                if (rowTotalEl) rowTotalEl.textContent = rowTotal.toLocaleString('id-ID');

                // Update column total
                var bulan = cell.getAttribute('data-bulan');
                var allColCells = tabel.querySelectorAll('[data-bulan="' + bulan + '"]');
                var colTotal = 0;
                allColCells.forEach(function (c) { colTotal += unformat(c.innerText); });
                var colFooter = tabel.querySelector('.col-total-' + bulan);
                if (colFooter) colFooter.textContent = colTotal.toLocaleString('id-ID');

                // Update grand total
                var allCells = tabel.querySelectorAll('.cell-rencana');
                var grand = 0;
                allCells.forEach(function (c) { grand += unformat(c.innerText); });
                var grandEl = tabel.querySelector('.grand-total');
                if (grandEl) grandEl.textContent = grand.toLocaleString('id-ID');
            });

            // Select all text on focus (mudah overwrite)
            tabel.addEventListener('focus', function (e) {
                var cell = e.target;
                if (!cell.classList.contains('cell-rencana')) return;
                setTimeout(function () {
                    var range = document.createRange();
                    range.selectNodeContents(cell);
                    var sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }, 0);
            }, true);

            // Hanya izinkan angka saat mengetik
            tabel.addEventListener('keypress', function (e) {
                if (!e.target.classList.contains('cell-rencana')) return;
                if (!/[0-9]/.test(String.fromCharCode(e.charCode))) {
                    e.preventDefault();
                }
            });

            // Enter → pindah ke cell berikutnya
            tabel.addEventListener('keydown', function (e) {
                if (!e.target.classList.contains('cell-rencana')) return;
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var cells = Array.from(tabel.querySelectorAll('.cell-rencana'));
                    var idx = cells.indexOf(e.target);
                    if (idx >= 0 && idx < cells.length - 1) cells[idx + 1].focus();
                }
            });
        })();
        </script>
@else
    <div class="alert alert-info">
        Tidak ada data kategori. Silakan tambah kategori terlebih dahulu.
    </div>
@endif
