@extends("layouts/index")
@section('content')
    @push('styles')
        <style>
            #tableDetailVA {
                table-layout: auto !important;
                width: 100% !important;
            }

            #tableDetailVA th,
            #tableDetailVA td {
                white-space: nowrap;
                vertical-align: middle;
            }

            .text-debet {
                color: #28a745;
                font-weight: 600;
            }

            .text-kredit {
                color: #dc3545;
                font-weight: 600;
            }

            .text-saldo {
                font-weight: 700;
            }

            .btn-download-excel {
                color: #dc3545;
                background-color: #fff;
                border: 2px solid #dc3545;
                font-weight: 600;
                padding: 5px 14px;
                font-size: 14px;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
            }

            .btn-download-excel:hover {
                background-color: #dc3545;
                color: #fff;
            }

            .btn-download-excel i {
                margin-right: 5px;
            }
        </style>
    @endpush

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detail Transaksi VA</h1>
                </div>

                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('daftarBank.index') }}">Daftar VA</a></li>
                        <li class="breadcrumb-item active">Detail</li>
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

                        <!-- Header -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <h4>
                                    <i class="fas fa-university"></i>
                                    {{ $va->nama_tujuan }}
                                </h4>
                                <p class="text-muted mb-0">Buku Pembantu (Ledger) — Gabungan Bank Masuk & Bank Keluar</p>
                            </div>
                        </div>

                        <div class="row no-print mb-3">
                            <div class="col-12">
                                <a href="{{ route('daftarBank.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-12 table-responsive">
                                <table id="tableDetailVA" class="table table-bordered table-hover table-striped">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>Tanggal</th>
                                            <th>Bank Tujuan</th>
                                            <th>Penerima/Dari</th>
                                            <th>Uraian</th>
                                            <th class="text-right">Debet</th>
                                            <th class="text-right">Kredit</th>
                                            <th class="text-right">Saldo Akhir</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transactions as $i => $trx)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($trx['tanggal'])->translatedFormat('d F Y') }}</td>
                                                <td>{{ $va->nama_tujuan }}</td>
                                                <td>{{ $trx['penerima'] ?? '-' }}</td>
                                                <td>{{ $trx['uraian'] ?? '-' }}</td>
                                                <td class="text-right {{ $trx['debet'] > 0 ? 'text-debet' : '' }}">
                                                    {{ $trx['debet'] > 0 ? number_format($trx['debet'], 0, ',', '.') : '-' }}
                                                </td>
                                                <td class="text-right {{ $trx['kredit'] > 0 ? 'text-kredit' : '' }}">
                                                    {{ $trx['kredit'] > 0 ? number_format($trx['kredit'], 0, ',', '.') : '-' }}
                                                </td>
                                                <td class="text-right text-saldo">
                                                    {{ number_format($trx['saldo'], 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                                    Belum ada transaksi untuk VA ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($transactions->count() > 0)
                                        <tfoot>
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="5" class="text-right">Total</td>
                                                <td class="text-right text-debet">
                                                    {{ number_format($transactions->sum('debet'), 0, ',', '.') }}
                                                </td>
                                                <td class="text-right text-kredit">
                                                    {{ number_format($transactions->sum('kredit'), 0, ',', '.') }}
                                                </td>
                                                <td class="text-right text-saldo">
                                                    {{ number_format($transactions->last()['saldo'], 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
        <script>
            $(function () {
                var table = $('#tableDetailVA').DataTable({
                    ordering: true,
                    paging: true,
                    searching: true,
                    order: [[1, 'asc']],
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                    columnDefs: [
                        { orderable: false, targets: [0] }
                    ]
                });

                // Insert Download Excel button next to Show entries
                var btnHtml = '<button type="button" class="btn btn-download-excel ml-2" id="btnDownloadExcel">' +
                    '<i class="fas fa-file-excel"></i> Download Excel</button>';
                $('#tableDetailVA_length').css('display', 'inline-flex').css('align-items', 'center').append(btnHtml);

                // Download Excel click handler
                $(document).on('click', '#btnDownloadExcel', function () {
                    var wb = XLSX.utils.book_new();
                    var wsData = [];
                    var vaName = @json($va->nama_tujuan);

                    // Title row
                    wsData.push(['Detail Transaksi VA - ' + vaName]);
                    wsData.push([]);

                    // Header row
                    wsData.push(['No', 'Tanggal', 'Bank Tujuan', 'Penerima/Dari', 'Uraian', 'Debet', 'Kredit', 'Saldo Akhir']);

                    // Get currently displayed rows
                    var rows = table.rows({ search: 'applied', page: 'current' }).nodes();

                    $(rows).each(function (idx) {
                        var cells = $(this).find('td');
                        wsData.push([
                            cells.eq(0).text().trim(),
                            cells.eq(1).text().trim(),
                            cells.eq(2).text().trim(),
                            cells.eq(3).text().trim(),
                            cells.eq(4).text().trim(),
                            cells.eq(5).text().trim(),
                            cells.eq(6).text().trim(),
                            cells.eq(7).text().trim()
                        ]);
                    });

                    // Total row from tfoot
                    var tfoot = $('#tableDetailVA tfoot tr');
                    if (tfoot.length) {
                        var footCells = tfoot.find('td');
                        wsData.push([
                            '', '', '', '', 'Total',
                            footCells.eq(1).text().trim(),
                            footCells.eq(2).text().trim(),
                            footCells.eq(3).text().trim()
                        ]);
                    }

                    var ws = XLSX.utils.aoa_to_sheet(wsData);

                    // Set column widths
                    ws['!cols'] = [
                        { wch: 5 },
                        { wch: 22 },
                        { wch: 28 },
                        { wch: 22 },
                        { wch: 55 },
                        { wch: 16 },
                        { wch: 16 },
                        { wch: 16 }
                    ];

                    // Merge title row
                    ws['!merges'] = [
                        { s: { r: 0, c: 0 }, e: { r: 0, c: 7 } }
                    ];

                    XLSX.utils.book_append_sheet(wb, ws, 'Detail VA');

                    var slug = vaName.replace(/[^a-zA-Z0-9]/g, '_');
                    XLSX.writeFile(wb, 'Detail_VA_' + slug + '.xlsx');
                });
            });
        </script>
    @endpush

@endsection