<style>
    .penerima-grouped-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .penerima-grouped-table th,
    .penerima-grouped-table td {
        border: 1px solid #bbb;
        padding: 4px 8px;
        white-space: nowrap;
        vertical-align: middle;
    }
    /* Kolom Kontrak dikunci lebarnya; teks panjang dibungkus ke baris berikutnya.
       min-width wajib ada agar kolom tidak diperas browser saat ruang sempit. */
    .penerima-grouped-table th.col-kontrak,
    .penerima-grouped-table td.col-kontrak {
        width: 180px;
        min-width: 180px;
        max-width: 180px;
        white-space: normal;
        overflow-wrap: break-word;
    }
    .row-month-title td {
        background-color: #1a5632 !important;
        color: #fff !important;
        font-weight: bold;
        font-size: 14px;
        padding: 8px 10px !important;
    }
    .row-header th {
        background-color: #2d7a4a !important;
        color: #fff !important;
        font-weight: bold;
        text-align: center;
        padding: 6px 8px !important;
    }
    .row-kategori-header td {
        background-color: #e8f5e9 !important;
        color: #1a5632 !important;
        font-weight: bold;
        font-size: 13px;
        padding: 6px 10px !important;
    }
    .row-subtotal td {
        background-color: #fff3cd !important;
        color: #333 !important;
        font-weight: bold;
        border-top: 2px solid #c9a825;
    }
    .row-grand-total td {
        background-color: #1a5632 !important;
        color: #fff !important;
        font-weight: bold;
        font-size: 13px;
        border-top: 2px solid #0d3b1f;
    }
    .penerima-grouped-table tbody tr.row-data:hover {
        background-color: #e3f0e8 !important;
    }
    .penerima-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #888;
    }
    .penerima-empty-state i {
        font-size: 40px;
        margin-bottom: 10px;
        display: block;
    }
</style>

@if(isset($grouped) && count($grouped) > 0)
    <div class="table-responsive">
        <table class="penerima-grouped-table">


            @foreach($grouped as $bulanNum => $kategoriGroup)
                <tbody>
                    {{-- Month title row --}}
                    <tr class="row-month-title">
                        <td colspan="14">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            {{ $judulPenerimaan ?? 'PENERIMAAN ATAS PENJUALAN CPO, KERNEL, SIR 20, TBS, KSO & LAINNYA' }} — {{ $bulanNames[$bulanNum] ?? '' }} {{ $tahun }}
                        </td>
                    </tr>
                    {{-- Column header row --}}
                    <tr class="row-header">
                        <th style="width:30px;"><input type="checkbox" class="select-all-month" data-month="{{ $bulanNum }}"></th>
                        <th style="width:30px;">No</th>
                        <th>Penerimaan</th>
                        <th class="col-kontrak">Kontrak</th>
                        <th>Pembeli</th>
                        <th>Tgl. Diterima</th>
                        <th>No. Rekg. Penerima</th>
                        <th>Volume (Kg)</th>
                        <th>Harga (Rp)</th>
                        <th>Nilai</th>
                        <th>PPN</th>
                        <th>Pot PPh</th>
                        <th>Nilai Inc. PPN</th>
                        <th style="width:90px;">Aksi</th>
                    </tr>

                    @php
                        $monthTotalVolume = 0;
                        $monthTotalNilai = 0;
                        $monthTotalPpn = 0;
                        $monthTotalPotppn = 0;
                        $monthTotalInc = 0;
                    @endphp

                    @foreach($kategoriGroup as $kategoriName => $rows)
                        {{-- Kategori header row --}}
                        <tr class="row-kategori-header">
                            <td colspan="14">
                                <i class="fas fa-layer-group mr-1"></i>{{ strtoupper($kategoriName) }}
                            </td>
                        </tr>

                        @php
                            $catNo = 1;
                            $subVolume = 0;
                            $subNilai = 0;
                            $subPpn = 0;
                            $subPotppn = 0;
                            $subInc = 0;
                        @endphp

                        @foreach($rows as $row)
                            @php
                                $nilaiIncPpn = $row->nilai_inc_ppn ?? 0;
                                $subVolume += $row->volume;
                                $subNilai += $row->nilai;
                                $subPpn += $row->ppn;
                                $subPotppn += $row->potppn;
                                $subInc += $nilaiIncPpn;
                            @endphp
                            <tr class="row-data">
                                <td class="text-center">
                                    <input type="checkbox" class="checkbox_ids" name="ids[]" value="{{ $row->id_penerima }}">
                                </td>
                                <td class="text-center">{{ $catNo++ }}</td>
                                <td>{{ $kategoriName }}</td>
                                <td class="col-kontrak">{{ $row->kontrak }}</td>
                                <td>{{ $row->pembeli }}</td>
                                <td class="text-center">{{ ($row->tanggal && $row->tanggal !== '0000-00-00') ? \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d M Y') : '-' }}</td>
                                <td>{{ $row->no_reg }}</td>
                                <td class="text-right">{{ number_format($row->volume, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($row->harga, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($row->nilai, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($row->ppn, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($row->potppn, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($nilaiIncPpn, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-xs"
                                        data-toggle="modal"
                                        data-target="#editPenerima"
                                        data-id="{{ $row->id_penerima }}"
                                        data-pembeli="{{ $row->pembeli }}"
                                        data-kontrak="{{ $row->kontrak }}"
                                        data-no_reg="{{ $row->no_reg }}"
                                        data-harga="{{ $row->harga }}"
                                        data-tanggal="{{ $row->tanggal }}"
                                        data-volume="{{ $row->volume }}"
                                        data-nilai="{{ $row->nilai }}"
                                        data-kategori="{{ $row->id_kategori_kriteria }}"
                                        data-ppn="{{ $row->ppn }}"
                                        data-potppn="{{ $row->potppn }}"
                                        data-nilai_inc_ppn="{{ $row->nilai_inc_ppn ?? 0 }}"
                                        ><i class="fas fa-edit"></i></button>
                                    <form action="{{ route('penerima.destroy', $row->id_penerima) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Subtotal row per kategori --}}
                        @php
                            $monthTotalVolume += $subVolume;
                            $monthTotalNilai += $subNilai;
                            $monthTotalPpn += $subPpn;
                            $monthTotalPotppn += $subPotppn;
                            $monthTotalInc += $subInc;
                        @endphp
                        <tr class="row-subtotal">
                            <td colspan="7" class="text-left">JUMLAH {{ strtoupper($kategoriName) }}</td>
                            <td class="text-right">{{ number_format($subVolume, 0, ',', '.') }}</td>
                            <td class="text-right"></td>
                            <td class="text-right">{{ number_format($subNilai, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($subPpn, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($subPotppn, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($subInc, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    @endforeach

                    {{-- Grand total row for this month --}}
                    <tr class="row-grand-total">
                        <td colspan="7" class="text-left">TOTAL {{ $bulanNames[$bulanNum] ?? '' }}</td>
                        <td class="text-right">{{ number_format($monthTotalVolume, 0, ',', '.') }}</td>
                        <td class="text-right"></td>
                        <td class="text-right">{{ number_format($monthTotalNilai, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($monthTotalPpn, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($monthTotalPotppn, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($monthTotalInc, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>

                    {{-- Spacer row between months --}}
                    <tr><td colspan="14" style="border:none; height:16px; background:#f0f0f0;"></td></tr>
                </tbody>
            @endforeach
        </table>
    </div>

    <script>
        // Select all checkboxes within a month
        $(document).off('click', '.select-all-month').on('click', '.select-all-month', function () {
            var isChecked = $(this).prop('checked');
            $(this).closest('tbody').find('.checkbox_ids').prop('checked', isChecked);
        });
    </script>
@else
    <div class="penerima-empty-state">
        <i class="fas fa-inbox"></i>
        <p>Tidak ada data penerima untuk periode ini.</p>
    </div>
@endif