@extends("layouts/index")
@section('content')

<div class="container-fluid mt-4">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Report Keluar</h1>
            </div>
         
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('dashboard.pembayaran.index') }}">Dashboard Pembayaran</a></li>
              <li class="breadcrumb-item"><a href="{{ route('bank-keluar.index') }}"> Bank Keluar </a></li>
              <li class="breadcrumb-item active">Report Keluar</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <section>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card rounded-3 border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('bank-keluar.report') }}" method="GET" id="filterForm">
                        <div class="row g-3 align-items-end">

                            <!-- Tahun -->
                            <div class="col-md-3">
                                <label class="form-label">Tahun</label>
                                <select name="tahun" class="form-control select2" onchange="submitForm()">
                                    @foreach($tahunList as $t)
                                        <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>
                                            {{ $t }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Bulan -->
                            <div class="col-md-3">
                                <label class="form-label">Bulan</label>
                                <select name="bulan" class="form-control select2" onchange="submitForm()">
                                    <option value="">Semua Jenis Bulan</option>
                                    @foreach($bulanList as $b)
                                        <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($b)->format('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tanggal (Multi-select Dropdown) -->
                            <div class="col-md-3">
                                <label class="form-label">Tanggal</label>
                                <div class="dropdown">
                                    <button class="form-control select2 text-start" type="button" data-toggle="dropdown">
                                        <span id="tanggalText">-- Pilih Tanggal --</span>
                                    </button>

                                    <div class="dropdown-menu w-100 p-2" data-bs-auto-close="outside" style="max-height: 300px; overflow-y: auto;">
                                        <div class="form-check fw-bold">
                                            <input class="form-check-input" type="checkbox" id="tanggalAll">
                                            <label class="form-check-label">Pilih Semua</label>
                                        </div>
                                        <hr class="my-1">

                                        @foreach($tanggalList as $tt)
                                        <div class="form-check">
                                            <input class="form-check-input tanggal-item"
                                                type="checkbox"
                                                name="tanggal[]"
                                                value="{{ $tt }}"
                                                {{ in_array($tt, request('tanggal', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                {{ \Carbon\Carbon::parse($tt)->format('d-m-Y') }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Tujuan -->
                            <div class="col-md-3">
                                <label class="form-label">Bank Tujuan</label>
                                <select name="bank_tujuan" class="form-control select2" onchange="submitForm()">
                                    <option value="" disable>Semua Bank Tujuan</option>
                                    @foreach($bankTujuanList as $bank)
                                        <option value="{{ $bank->id_bank_tujuan }}" {{ request('bank_tujuan') == $bank->id_bank_tujuan ? 'selected' : '' }}>
                                            {{ $bank->nama_tujuan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sumber Dana -->
                            <div class="col-md-3">
                                <label class="form-label">Sumber Dana</label>
                                <div class="dropdown">
                                    <button class="form-control select2 text-start" type="button" data-toggle="dropdown">
                                        <span id="sdText">Semua Sumber Dana</span>
                                    </button>

                                    <div class="dropdown-menu w-100 p-2" data-bs-auto-close="outside">
                                        <div class="form-check fw-bold">
                                            <input class="form-check-input" type="checkbox" id="sdAll">
                                            <label class="form-check-label">Pilih Semua</label>
                                        </div>
                                        <hr class="my-1">

                                        @foreach($sumberDanaList as $sd)
                                        <div class="form-check">
                                            <input class="form-check-input sd-item"
                                                type="checkbox"
                                                name="sumber_dana[]"
                                                value="{{ $sd->id_sumber_dana }}"
                                                {{ in_array($sd->id_sumber_dana, request('sumber_dana', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label">{{ $sd->nama_sumber_dana }}</label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Kategori -->
                            <div class="col-md-3">
                                <label class="form-label">Kategori</label>
                                <div class="dropdown">
                                    <button class="form-control select2 text-start" type="button" data-toggle="dropdown">
                                        <span id="kategoriText">Semua Kategori</span>
                                    </button>

                                    <div class="dropdown-menu w-100 p-2" data-bs-auto-close="outside">
                                        <div class="form-check fw-bold">
                                            <input class="form-check-input" type="checkbox" id="kategoriAll">
                                            <label class="form-check-label">Pilih Semua</label>
                                        </div>
                                        <hr class="my-1">

                                        @foreach($kategoriList as $k)
                                        <div class="form-check">
                                            <input class="form-check-input kategori-item"
                                                type="checkbox"
                                                name="kategori[]"
                                                value="{{ $k->id_kategori_kriteria }}"
                                                {{ in_array($k->id_kategori_kriteria, request('kategori', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label">{{ $k->nama_kriteria }}</label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Jenis Pembayaran -->
                            <div class="col-md-3">
                                <label class="form-label">Jenis Pembayaran</label>
                                <select name="id_jenis_pembayaran" class="form-control select2" onchange="submitForm()">
                                    <option value="">Semua Jenis Pembayaran</option>
                                    @foreach($jenisPembayaranList as $rjs)
                                        @if($rjs->id_jenis_pembayaran)
                                            <option value="{{ $rjs->id_jenis_pembayaran }}"
                                                {{ request('id_jenis_pembayaran') == $rjs->id_jenis_pembayaran ? 'selected' : '' }}>
                                                {{ $rjs->nama_jenis_pembayaran }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <!-- Rekapan -->
                            <div class="col-md-3">
                                <label class="form-label">Rekapan</label>
                                <select name="rekapanVA" class="form-control select2" onchange="submitForm()">
                                    <option value="">-- Pilih Rekapan --</option>
                                    <option value="va" {{ request('rekapanVA') == 'va' ? 'selected' : '' }}>Saldo Bank</option>
                                    <option value="bank" {{ request('rekapanVA') == 'bank' ? 'selected' : '' }}>Saldo VA</option>
                                    <option value="kategori-full" {{ request('rekapanVA') == 'kategori-full' ? 'selected' : '' }}>Kategori Full</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card rounded-3 border-0 shadow-sm" id="printArea">
                <div class="card-body table-responsive">
                    <div class="row">
                        <div class="col-12 d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                            <h5 class="tittle">Daftar Bank Keluar</h5>
                            <div>
                                <!-- <a href="#" onclick="window.print()" class="btn-export m-3 bg-success" style="border-radius: 10px; padding:15px;color: white;">
                                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                                </a>    -->
                                <a href="{{ route('bank-keluar.report_export_excel', [
                                        'tahun' => request('tahun'),
                                        'id_jenis_pembayaran' => request('id_jenis_pembayaran'),
                                        'kategori' => request('kategori'),
                                        'sumber_dana' => request('sumber_dana'),
                                        'bankTujuan' => request('bankTujuan'),
                                        'bulan' => request('bulan'),
                                        'tanggal' => request('tanggal'),
                                        'rekapanVA' => request('rekapanVA')
                                    ]) }}" class="btn btn-outline-success"><i class="bi bi-file-excel-fill"></i>
                                        Download Excel
                                    </a>
                                <a href="{{ route('bank-keluar.reportKeluarPdf', [
                                        'tahun' => request('tahun'),
                                        'id_jenis_pembayaran' => request('id_jenis_pembayaran'),
                                        'kategori' => request('kategori'),
                                        'sumber_dana' => request('sumber_dana'),
                                        'bankTujuan' => request('bankTujuan'),
                                        'bulan' => request('bulan'),
                                        'tanggal' => request('tanggal'),
                                        'rekapanVA' => request('rekapanVA')
                                    ]) }}" class="btn btn-outline-primary" target = "_blank"><i class="bi bi-printer"></i>
                                        Download PDF
                                    </a>
                                    <!-- </a> -->
                                <!-- <a href="#" onclick="window.print()" class="btn-export bg-danger" style="border-radius: 10px; padding:15px;color: white;">
                                    <i class="bi bi-printer"></i> Export PDF
                                </a> -->
                                <button type="button" class="btn btn-sm bg-primary btn-outline-secondary text-white" onclick="resetAllFilters()" style="margin:10px;">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>   
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                    @include('cash_bank.exportExcel.excelReportKeluar', ['data' => $data])
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #printArea, #printArea * {
        visibility: visible;
    }

    #printArea .btn-export {
        display: none !important;
        visibility: hidden !important;
    }

    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}

.kolom-kategori{
    width: 1%;
    white-space: nowrap;
    vertical-align: top;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    const form = document.getElementById('filterForm');

    // Sumber Dana Multi-select
    const sdItems = document.querySelectorAll('.sd-item');
    const sdAll = document.getElementById('sdAll');
    const sdText = document.getElementById('sdText');

    function updateSdText() {
        const checked = document.querySelectorAll('.sd-item:checked').length;
        sdText.textContent = checked ? checked + ' dipilih' : 'Semua Sumber Dana';
    }

    sdAll.addEventListener('change', () => {
        sdItems.forEach(i => i.checked = sdAll.checked);
        updateSdText();
        submitForm();
    });

    sdItems.forEach(i => i.addEventListener('change', () => {
        updateSdText();
        submitForm();
    }));

    updateSdText();

    // Kategori Multi-select
    const kategoriItems = document.querySelectorAll('.kategori-item');
    const kategoriAll = document.getElementById('kategoriAll');
    const kategoriText = document.getElementById('kategoriText');

    function updateKategoriText() {
        const checked = document.querySelectorAll('.kategori-item:checked').length;
        kategoriText.textContent = checked ? checked + ' dipilih' : 'Semua Kategori';
    }

    kategoriAll.addEventListener('change', () => {
        kategoriItems.forEach(i => i.checked = kategoriAll.checked);
        updateKategoriText();
        submitForm();
    });

    kategoriItems.forEach(i => i.addEventListener('change', () => {
        updateKategoriText();
        submitForm();
    }));

    updateKategoriText();

    // Tanggal Multi-select
    const tanggalItems = document.querySelectorAll('.tanggal-item');
    const tanggalAll = document.getElementById('tanggalAll');
    const tanggalText = document.getElementById('tanggalText');

    function updateTanggalText() {
        const checked = document.querySelectorAll('.tanggal-item:checked').length;
        tanggalText.textContent = checked ? checked + ' dipilih' : 'Semua Tanggal';
    }

    tanggalAll.addEventListener('change', () => {
        tanggalItems.forEach(i => i.checked = tanggalAll.checked);
        updateTanggalText();
        submitForm();
    });

    tanggalItems.forEach(i => i.addEventListener('change', () => {
        updateTanggalText();
        submitForm();
    }));

    updateTanggalText();
});

// Fungsi submit form
function submitForm() {
    document.getElementById('filterForm').submit();
}

// Reset all filters
function resetAllFilters() {
    const form = document.getElementById('filterForm');
    const tahun = form.querySelector('[name="tahun"]')?.value;
    window.location.href = form.action + '?tahun=' + tahun;
}
</script>

<style>
    /* Responsive adjustments */
@media (max-width: 576px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
}

@media (max-width: 768px) {
    table {
        font-size: 12px;
    }

    th, td {
        white-space: nowrap;
    }

    .tittle {
        font-size: 1.2rem;
    }
}

</style>

@endsection