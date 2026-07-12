{{-- ============ MODAL FILTER EXPORT BANK MASUK (PDF & EXCEL) ============ --}}
<div class="modal fade" id="ModalExportMasuk" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header" style="background:#0d3b6e; color:#fff;">
        <h5 class="modal-title"><i class="fas fa-download mr-2"></i>Export Data Bank Masuk</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-3" style="font-size:12.5px;">
          Pilih rentang data yang akan diexport. Jumlah baris dihitung otomatis
          sebelum file dibuat.
        </p>
        <div class="row">
          <div class="form-group col-6">
            <label style="font-size:12.5px;">Tahun</label>
            <select id="exmTahun" class="form-control form-control-sm exm-filter">
              @foreach ($exportYears as $y)
                <option value="{{ $y }}">{{ $y }}</option>
              @endforeach
              <option value="">Semua Tahun</option>
            </select>
          </div>
          <div class="form-group col-6">
            <label style="font-size:12.5px;">Bulan</label>
            <select id="exmBulan" class="form-control form-control-sm exm-filter">
              <option value="">Semua Bulan</option>
              @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nm)
                <option value="{{ $i + 1 }}">{{ $nm }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-12 mb-2">
            <label style="font-size:12.5px;">Kategori</label>
            <select id="exmKategori" class="form-control form-control-sm exm-filter">
              <option value="">Semua Kategori</option>
              @foreach ($kategoriKriteria as $kk)
                <option value="{{ $kk->id_kategori_kriteria }}">{{ $kk->nama_kriteria }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-12 mb-2">
            <label style="font-size:12.5px;">Sumber Dana</label>
            <select id="exmSumber" class="form-control form-control-sm exm-filter">
              <option value="">Semua Sumber Dana</option>
              @foreach ($sumberDana as $sd)
                <option value="{{ $sd->id_sumber_dana }}">{{ $sd->nama_sumber_dana }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div id="exmCountInfo" class="alert alert-info py-2 mb-2" style="font-size:12.5px;">
          Menghitung jumlah baris...
        </div>
        <div id="exmPdfWarn" class="alert alert-warning py-2 mb-0" style="font-size:12px; display:none;">
          ⚠ Data melebihi batas aman PDF (3.000 baris). Persempit filter,
          atau gunakan Export Excel untuk data besar.
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm" id="btnDoExportMasuk" disabled>
          <i class="fas fa-download mr-1"></i>Export
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    var exmMode = 'excel';           // 'pdf' | 'excel' — diisi dari tombol pemicu
    var exmCount = 0;
    var EXM_PDF_MAX = 3000;          // selaras batas $maxRows di controller

    function exmParams() {
        var p = new URLSearchParams();
        if ($('#exmTahun').val()) p.append('tahun', $('#exmTahun').val());
        if ($('#exmBulan').val()) p.append('bulan', $('#exmBulan').val());
        if ($('#exmKategori').val()) p.append('kategori', $('#exmKategori').val());
        if ($('#exmSumber').val()) p.append('sumber_dana', $('#exmSumber').val());
        return p.toString();
    }

    function exmRefreshButton() {
        var btn = $('#btnDoExportMasuk');
        var overPdf = exmMode === 'pdf' && exmCount > EXM_PDF_MAX;
        $('#exmPdfWarn').toggle(overPdf);
        btn.prop('disabled', exmCount === 0 || overPdf);
        btn.html(exmMode === 'pdf'
            ? '<i class="fas fa-print mr-1"></i>Export PDF (' + exmCount.toLocaleString('id-ID') + ' baris)'
            : '<i class="fas fa-file-excel mr-1"></i>Export Excel (' + exmCount.toLocaleString('id-ID') + ' baris)');
    }

    function exmUpdateCount() {
        $('#exmCountInfo').text('Menghitung jumlah baris...');
        $('#btnDoExportMasuk').prop('disabled', true);
        $.get('{{ route("bank-masuk.exportCount") }}?' + exmParams(), function (res) {
            exmCount = res.total || 0;
            $('#exmCountInfo').html(exmCount === 0
                ? 'Tidak ada data pada filter ini.'
                : '<strong>' + exmCount.toLocaleString('id-ID') + '</strong> baris akan diexport.');
            exmRefreshButton();
        }).fail(function () {
            $('#exmCountInfo').text('Gagal menghitung jumlah baris.');
        });
    }

    // Mode (pdf/excel) diambil dari tombol yang membuka modal
    $('#ModalExportMasuk').on('show.bs.modal', function (e) {
        exmMode = $(e.relatedTarget).data('mode') || 'excel';
        exmUpdateCount();
    });

    $(document).on('change', '.exm-filter', exmUpdateCount);

    $(document).on('click', '#btnDoExportMasuk', function () {
        var qs = exmParams();
        if (exmMode === 'pdf') {
            window.open('{{ url("/bank-masuk/view/pdf") }}' + (qs ? '?' + qs : ''), '_blank');
        } else {
            window.location.href = '{{ url("/bank-masuk/export_excel") }}' + (qs ? '?' + qs : '');
        }
        $('#ModalExportMasuk').modal('hide');
    });
});
</script>
@endpush
