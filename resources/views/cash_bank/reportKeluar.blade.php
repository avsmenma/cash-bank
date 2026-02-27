@extends("layouts/index")
@section('content')

<div class="container-fluid mt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Rekening Koran</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.pembayaran.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Rekening Koran</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        {{-- FILTER BAR --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <form action="{{ route('bank-keluar.report') }}" method="GET" id="filterForm" class="row align-items-end g-2">
                    {{-- Tahun --}}
                    <div class="col-auto">
                        <label class="form-label mb-0 small">Tahun</label>
                        <select name="tahun" class="form-control form-control-sm select2" onchange="submitForm()">
                            @foreach($tahunList as $t)
                                <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bulan --}}
                    <div class="col-auto">
                        <label class="form-label mb-0 small">Bulan</label>
                        <select name="bulan" class="form-control form-control-sm select2" onchange="submitForm()">
                            <option value="">Semua Bulan</option>
                            @foreach($bulanList as $b)
                                <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($b)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bank Tujuan --}}
                    <div class="col-auto">
                        <label class="form-label mb-0 small">Bank Tujuan</label>
                        <select name="bank_tujuan" class="form-control form-control-sm select2" onchange="submitForm()">
                            <option value="">Semua Bank Tujuan</option>
                            @foreach($bankTujuanList as $bank)
                                <option value="{{ $bank->id_bank_tujuan }}" {{ request('bank_tujuan') == $bank->id_bank_tujuan ? 'selected' : '' }}>
                                    {{ $bank->nama_tujuan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal awal - akhir --}}
                    <div class="col-auto">
                        <label class="form-label mb-0 small">Dari Tanggal</label>
                        <input type="date" name="tgl_dari" class="form-control form-control-sm"
                               value="{{ request('tgl_dari') }}" onchange="submitForm()">
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-0 small">Sampai Tanggal</label>
                        <input type="date" name="tgl_sampai" class="form-control form-control-sm"
                               value="{{ request('tgl_sampai') }}" onchange="submitForm()">
                    </div>

                    {{-- Reset --}}
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="resetFilter()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>

                    {{-- Export --}}
                    <div class="col-auto ml-auto">
                        <a href="{{ route('bank-keluar.report_export_excel', request()->all()) }}"
                           class="btn btn-sm btn-success">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="card shadow-sm">
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered table-sm mb-0" id="rkTable" style="font-size:12px;white-space:nowrap;">
                    <thead style="background:#f8f9fa;">
                        <tr class="text-center">
                            <th style="width:40px;">No</th>
                            <th style="min-width:80px;">No Agenda</th>
                            <th style="min-width:90px;">Tanggal</th>
                            <th style="min-width:100px;">No Bukti</th>
                            <th style="min-width:150px;">Bank Tujuan</th>
                            <th style="min-width:150px;">Penerima / Dari</th>
                            <th style="min-width:250px;">Uraian</th>
                            <th style="min-width:120px;">Debet</th>
                            <th style="min-width:120px;">Kredit</th>
                            <th style="min-width:130px;">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = 1;
                            $tglDari   = request('tgl_dari');
                            $tglSampai = request('tgl_sampai');
                        @endphp
                        @forelse($data as $row)
                            @php
                                // Filter tanggal tambahan (dari/sampai) jika ada
                                if ($tglDari && $row->tanggal < $tglDari) continue;
                                if ($tglSampai && $row->tanggal > $tglSampai) continue;

                                $isDebet = ($row->jenis ?? '') === 'MASUK';
                                $rowStyle = $isDebet ? 'background:#f0fff4;' : '';
                                $saldo = $row->saldo_akhir ?? 0;
                            @endphp
                            <tr style="{{ $rowStyle }}">
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $row->no_agenda ?? '-' }}</td>
                                <td class="text-center">
                                    {{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}
                                </td>
                                <td>{{ $row->no_sap ?? '-' }}</td>
                                <td>{{ $row->nama_tujuan ?? '-' }}</td>
                                <td>{{ $row->penerima ?? '-' }}</td>
                                <td>{{ $row->uraian ?? '-' }}</td>
                                <td class="text-right {{ $isDebet ? 'text-success font-weight-bold' : '' }}">
                                    {{ $row->debet > 0 ? number_format($row->debet, 0, ',', '.') : '' }}
                                </td>
                                <td class="text-right {{ !$isDebet && $row->kredit > 0 ? 'text-danger' : '' }}">
                                    {{ $row->kredit > 0 ? number_format($row->kredit, 0, ',', '.') : '' }}
                                </td>
                                <td class="text-right font-weight-bold {{ $saldo < 0 ? 'text-danger' : '' }}">
                                    @if($saldo !== null)
                                        {{ $saldo < 0 ? '(' . number_format(abs($saldo), 0, ',', '.') . ')' : number_format($saldo, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3">Tidak ada data untuk filter yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($data) > 0)
                    <tfoot>
                        <tr class="font-weight-bold" style="background:#e9ecef;">
                            <td colspan="7" class="text-right">TOTAL</td>
                            <td class="text-right text-success">
                                {{ number_format($data->sum('debet'), 0, ',', '.') }}
                            </td>
                            <td class="text-right text-danger">
                                {{ number_format($data->sum('kredit'), 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                @php $finalSaldo = $data->last()->saldo_akhir ?? 0; @endphp
                                {{ $finalSaldo < 0 ? '(' . number_format(abs($finalSaldo), 0, ',', '.') . ')' : number_format($finalSaldo, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </section>
</div>

<style>
    #rkTable th { vertical-align: middle; }
    #rkTable td { vertical-align: middle; padding: 4px 6px; }
    #rkTable td.text-right { font-variant-numeric: tabular-nums; }
</style>

<script>
function submitForm() { document.getElementById('filterForm').submit(); }
function resetFilter() {
    const tahun = document.querySelector('[name="tahun"]')?.value;
    window.location.href = '{{ route("bank-keluar.report") }}?tahun=' + (tahun || '');
}
document.addEventListener('DOMContentLoaded', function() {
    if (window.$) {
        $('.select2').select2({ theme: 'bootstrap4', width: 'resolve' });
    }
});
</script>

@endsection