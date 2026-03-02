@extends('layouts/index')
@section('content')

<div class="container-fluid mt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="font-weight:700;color:#0d3b6e;">
                        <i class="fas fa-university mr-2"></i>Dashboard Bank
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Bank</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">

        {{-- FILTER TAHUN --}}
        <div class="card shadow mb-3" style="border-top:4px solid #0d3b6e;">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('dashboard.bank.index') }}" class="d-flex align-items-center">
                    <label class="mb-0 small font-weight-bold text-secondary mr-2">Tahun</label>
                    <select name="tahun" class="form-control form-control-sm mr-2" style="width:100px;" onchange="this.form.submit()">
                        @foreach($tahunList as $t)
                            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                    <span class="small text-muted">Data tahun {{ $tahun }}</span>
                </form>
            </div>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow" style="border-top:4px solid #1a7a3d;">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="small text-muted font-weight-bold text-uppercase mb-1">Total Bank Masuk (Debet)</div>
                                <div class="h5 mb-0 font-weight-bold text-success">
                                    Rp {{ number_format($totalDebet, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-success" style="font-size:2rem;"><i class="fas fa-arrow-circle-down"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow" style="border-top:4px solid #c0392b;">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="small text-muted font-weight-bold text-uppercase mb-1">Total Bank Keluar (Kredit)</div>
                                <div class="h5 mb-0 font-weight-bold text-danger">
                                    Rp {{ number_format($totalKredit, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-danger" style="font-size:2rem;"><i class="fas fa-arrow-circle-up"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                @php $saldo = $totalDebet - $totalKredit; @endphp
                <div class="card shadow" style="border-top:4px solid #0d3b6e;">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="small text-muted font-weight-bold text-uppercase mb-1">Saldo Akhir</div>
                                <div class="h5 mb-0 font-weight-bold {{ $saldo >= 0 ? 'text-primary' : 'text-danger' }}">
                                    @if($saldo < 0)(Rp {{ number_format(abs($saldo), 0, ',', '.') }})
                                    @else Rp {{ number_format($saldo, 0, ',', '.') }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-primary" style="font-size:2rem;"><i class="fas fa-balance-scale"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHART BULANAN --}}
        <div class="card shadow mb-4" style="border:none;">
            <div class="card-header" style="background:#0d3b6e;color:#fff;font-weight:600;">
                <i class="fas fa-chart-bar mr-2"></i>Grafik Bank Masuk vs Bank Keluar per Bulan ({{ $tahun }})
            </div>
            <div class="card-body">
                <canvas id="bankChart" height="100"></canvas>
            </div>
        </div>

        {{-- TABEL PER BULAN --}}
        <div class="card shadow mb-4" style="border:none;">
            <div class="card-header" style="background:#0d3b6e;color:#fff;font-weight:600;">
                <i class="fas fa-table mr-2"></i>Rekap Bulanan ({{ $tahun }})
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr style="background:#1b4f72;color:#fff;font-size:12px;">
                            <th class="text-center">Bulan</th>
                            <th class="text-right">Bank Masuk (Debet)</th>
                            <th class="text-right">Bank Keluar (Kredit)</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody style="font-size:12px;">
                        @php $runSaldo = 0; @endphp
                        @foreach($bulanList as $bNo => $bNama)
                            @php
                                $masuk  = $masukPerBulan[$bNo]  ?? 0;
                                $keluar = $keluarPerBulan[$bNo] ?? 0;
                                $selisih = $masuk - $keluar;
                                $runSaldo += $selisih;
                            @endphp
                            @if($masuk > 0 || $keluar > 0)
                            <tr>
                                <td class="font-weight-bold">{{ $bNama }}</td>
                                <td class="text-right text-success">{{ $masuk > 0 ? number_format($masuk, 0, ',', '.') : '-' }}</td>
                                <td class="text-right text-danger">{{ $keluar > 0 ? number_format($keluar, 0, ',', '.') : '-' }}</td>
                                <td class="text-right {{ $selisih >= 0 ? 'text-primary' : 'text-danger' }} font-weight-bold">
                                    @if($selisih < 0)({{ number_format(abs($selisih), 0, ',', '.') }})
                                    @else {{ number_format($selisih, 0, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#1b4f72;color:#fff;font-weight:700;font-size:12px;">
                            <td>TOTAL</td>
                            <td class="text-right" style="color:#a9dfbf;">{{ number_format($totalDebet, 0, ',', '.') }}</td>
                            <td class="text-right" style="color:#f1948a;">{{ number_format($totalKredit, 0, ',', '.') }}</td>
                            <td class="text-right" style="color:#fad7a0;">
                                @php $tot = $totalDebet - $totalKredit; @endphp
                                @if($tot < 0)({{ number_format(abs($tot), 0, ',', '.') }})
                                @else {{ number_format($tot, 0, ',', '.') }}
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- TABEL PER SUMBER DANA --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow" style="border:none;">
                    <div class="card-header" style="background:#1a7a3d;color:#fff;font-weight:600;">
                        <i class="fas fa-arrow-circle-down mr-1"></i>Bank Masuk per Sumber Dana ({{ $tahun }})
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" style="font-size:12px;">
                            <thead>
                                <tr style="background:#1d8348;color:#fff;">
                                    <th>Sumber Dana</th>
                                    <th class="text-right">Total Debet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sumberDanaMasuk as $sd)
                                <tr>
                                    <td>{{ $sd->nama_sumber_dana }}</td>
                                    <td class="text-right text-success font-weight-bold">{{ number_format($sd->total_debet, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow" style="border:none;">
                    <div class="card-header" style="background:#c0392b;color:#fff;font-weight:600;">
                        <i class="fas fa-arrow-circle-up mr-1"></i>Bank Keluar per Sumber Dana ({{ $tahun }})
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" style="font-size:12px;">
                            <thead>
                                <tr style="background:#a93226;color:#fff;">
                                    <th>Sumber Dana</th>
                                    <th class="text-right">Total Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sumberDanaKeluar as $sd)
                                <tr>
                                    <td>{{ $sd->nama_sumber_dana }}</td>
                                    <td class="text-right text-danger font-weight-bold">{{ number_format($sd->total_kredit, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var labels  = @json(array_values($bulanList));
    var masuk   = @json(array_map(fn($b) => $masukPerBulan[$b] ?? 0, array_keys($bulanList)));
    var keluar  = @json(array_map(fn($b) => $keluarPerBulan[$b] ?? 0, array_keys($bulanList)));

    var ctx = document.getElementById('bankChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Bank Masuk (Debet)',
                    data: masuk,
                    backgroundColor: 'rgba(26,122,61,0.75)',
                    borderColor: '#1a7a3d',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Bank Keluar (Kredit)',
                    data: keluar,
                    backgroundColor: 'rgba(192,57,43,0.75)',
                    borderColor: '#c0392b',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': Rp ' +
                                Number(ctx.parsed.y).toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(val) {
                            return 'Rp ' + Number(val).toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>

@endsection
