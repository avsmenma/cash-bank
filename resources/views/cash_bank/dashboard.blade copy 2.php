@extends('layouts/index')
@section('content')

<div class="container-fuild m-3">
    <!-- Content Header (Page header) -->
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
    <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-money-bill"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Pemasukkan</span>
                <span class="info-box-number">
                  Rp {{ number_format($total_pemasukkan_card ?? 0, 0, ',', '.') }}
                  
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-money-bill"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Pengeluaran</span>
                <span class="info-box-number">Rp {{ number_format($total_pengeluaran_card ?? 0, 0, ',', '.') }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fa-file-download "></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Belum Siap Bayar</span>
                <span class="info-box-number">Rp {{ number_format($agendaBelumSiapBayar ?? 0, 0, ',', '.') }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fa-solid fa-file-archive"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Siap Bayar</span>
                <span class="info-box-number">Rp {{ number_format($agendaSiapBayar ?? 0, 0, ',', '.') }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>

        <div class="row">
          <!-- /.col-md-6 -->
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                  <h3 class="card-title">Grafik Pengeluaran Kategori</h3>
                    <form method="GET" action="{{ route('dashboard') }}">
                        <select name="tahun" class="select2" onchange="this.form.submit()">
                            <option value="">Pilih Tahun</option>
                              @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                </option>
                              @endfor
                        </select>
                    </form>
                </div>
              </div>
              <div class="card-body">
                <div class="position-relative mb-4">
                  <div id="line_chart_kategori"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card">
              <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                  <h3 class="card-title">Grafik Pengeluaran & Pemasukkan</h3>
                    <form method="GET" action="{{ route('dashboard') }}">
                        <select name="tahun" class="select2" onchange="this.form.submit()">
                            <option value=""> Pilih Tahun </option>
                              @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                </option>
                              @endfor
                        </select>
                    </form>
                </div>
              </div>
              <div class="card-body">
                <div class="position-relative mb-4">
                  <div class="" id="line_chart"  style="height: 350px;"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
    <!-- /.content -->
  </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.highcharts.com/12.4.0/highcharts.js"></script>
<script >
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
// horizontal chart
const vendorNames = [
    "Vendor A", "Vendor B", "Vendor C", "Vendor D", "Vendor E",
    "Vendor F", "Vendor G", "Vendor H", "Vendor I", "Vendor J",
    "Vendor K", "Vendor L", "Vendor M", "Vendor N", "Vendor O",
    "Vendor P", "Vendor Q", "Vendor R", "Vendor S", "Vendor T",
    "Vendor U", "Vendor V", "Vendor W", "Vendor X", "Vendor Y",
    "Vendor Z", "Vendor AA", "Vendor AB", "Vendor AC", "Vendor AD"
];

const vendorTotals = [
    1200000, 900000, 1500000, 700000, 450000,
    2000000, 950000, 300000, 1100000, 800000,
    500000, 1300000, 760000, 890000, 620000,
    430000, 2700000, 1200000, 900000, 400000,
    520000, 770000, 2100000, 970000, 880000,
    300000, 650000, 780000, 910000, 540000
];

// --- GRAFIK ---
// --- GRAFIK ---
new Chart(document.getElementById("vendorChart"), {
    type: 'bar',
    data: {
        labels: vendorNames, 
        datasets: [{
            label: 'Total Pengeluaran Per Kategori',
            data: vendorTotals,
            borderWidth: 1,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)'
        }]
    },
    options: {
        indexAxis: 'y', // Chart horizontal
        responsive: true,
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return "Rp " + value.toLocaleString();
                    }
                }
            }
        }
    }
});

    // Highcharts - Line Chart (Pengeluaran & Pemasukkan)
    var totalPengeluaran = <?php echo json_encode($total_pengeluaran ?? []); ?>;
    var totalPemasukkan = <?php echo json_encode($total_pemasukkan ?? []); ?>;
    var bulan = <?php echo json_encode($bulan ?? []); ?>;

    // Debug: cek data di console browser
    console.log('Total Pengeluaran:', totalPengeluaran);
    console.log('Total Pemasukkan:', totalPemasukkan);
    console.log('Bulan:', bulan);
    console.log('Tipe data:', typeof totalPengeluaran, typeof totalPemasukkan, typeof bulan);

    // Pastikan data adalah array of numbers
    var dataPengeluaran = totalPengeluaran.map(function(val) {
        return parseFloat(val) || 0;
    });

    var dataPemasukkan = totalPemasukkan.map(function(val) {
        return parseFloat(val) || 0;
    });

    console.log('Data Pengeluaran (parsed):', dataPengeluaran);
    console.log('Data Pemasukkan (parsed):', dataPemasukkan);

    Highcharts.chart('line_chart', {
        chart: {
            type: 'line',
            height: 300
        },
        title: {
            text: '',
            style: {
                fontSize: '16px',
                fontWeight: 'bold'
            }
        },
        xAxis: {
            categories: bulan,
            crosshair: true
        },
        yAxis: {
            title: {
                text: 'Nominal (Rp)'
            },
            labels: {
                formatter: function() {
                    return 'Rp ' + Highcharts.numberFormat(this.value, 0, ',', '.');
                }
            }
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: false
                },
                enableMouseTracking: true
            }
        },
        tooltip: {
            formatter: function() {
                return '<b>' + this.series.name + '</b><br/>' +
                    this.x + ': <b>Rp ' + Highcharts.numberFormat(this.y, 0, ',', '.') + '</b>';
            }
        },
        legend: {
            enabled: true,
            align: 'center',
            verticalAlign: 'bottom'
        },
       
        series: [
            {
                name: 'Nominal Pengeluaran',
                data: dataPengeluaran,
                color: '#ff004c',
                fillColor: {
                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                    stops: [
                        [0, 'rgba(255, 0, 76, 0.3)'],
                        [1, 'rgba(255, 0, 76, 0.05)']
                    ]
                }
            },
            {
                name: 'Nominal Pemasukkan',
                data: dataPemasukkan,
                color: '#0066ff',
                fillColor: {
                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                    stops: [
                        [0, 'rgba(0, 102, 255, 0.3)'],
                        [1, 'rgba(0, 102, 255, 0.05)']
                    ]
                }
            }
        ],
        credits: {
            enabled: false
        }
    });



// ---- KATEGORI ----
// new Chart(document.getElementById('chartKategori'), {
//     type: 'bar',
//     data: {
//         labels: kategori.map(d => 'Bulan ' + d.bulan),
//         datasets: [{
//             label: 'Total',
//             data: kategori.map(d => d.total),
//             backgroundColor: 'rgba(75, 192, 192, 0.5)',
//             borderColor: 'rgba(75, 192, 192,1)'
//         }]
//     }
// });


var kategoriNama = <?php echo json_encode($kategori_nama); ?>;
var kategoriTotal = <?php echo json_encode($kategori_total); ?>;
var bulan = <?php echo json_encode($bulan); ?>;
Highcharts.chart('line_chart_kategori', {
    chart: {
        type: 'column',
        height: 350
    },
    title: {
        text: ''
    },
    xAxis: {
        categories: bulan
    },
    yAxis: {
        title: {
            text: 'Nominal (Rp)'
        },
        labels: {
            formatter: function() {
                return 'Rp ' + Highcharts.numberFormat(this.value, 0, ',', '.');
            }
        }
    },
    tooltip: {
        shared: true,
        formatter: function() {
            let s = '<b>' + this.x + '</b><br>';
            this.points.forEach(function(point) {
                s += point.series.name + ': <b>Rp ' +
                    Highcharts.numberFormat(point.y, 0, ',', '.') + '</b><br>';
            });
            return s;
        }
    },
    plotOptions: {
        column: {
            borderRadius: 4,
            pointPadding: 0.1,
            groupPadding: 0.15
        }
    },
    series: kategoriNama.map((nama, index) => ({
        name: nama,
        data: kategoriTotal[index],
    })),
    credits: {
        enabled: false
    }
});

</script>
@endpush
@endsection