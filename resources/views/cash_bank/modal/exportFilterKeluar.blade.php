{{-- ============ MODAL FILTER EXPORT BANK KELUAR (PDF & EXCEL) ============ --}}
<div class="modal fade" id="ModalExportKeluar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header" style="background:#0d3b6e; color:#fff;">
        <h5 class="modal-title"><i class="fas fa-download mr-2"></i>Export Data Bank Keluar</h5>
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
            <select id="exkTahun" class="form-control form-control-sm exk-filter">
              @foreach ($exportYears as $y)
                <option value="{{ $y }}">{{ $y }}</option>
              @endforeach
              <option value="">Semua Tahun</option>
            </select>
          </div>
          <div class="form-group col-6">
            <label style="font-size:12.5px;">Bulan</label>
            <select id="exkBulan" class="form-control form-control-sm exk-filter">
              <option value="">Semua Bulan</option>
              @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nm)
                <option value="{{ $i + 1 }}">{{ $nm }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-12 mb-2">
            <label style="font-size:12.5px;">Kriteria</label>
            <select id="exkKategori" class="form-control form-control-sm exk-filter">
              <option value="">Semua Kriteria</option>
              @foreach ($kategoriKriteria as $kk)
                <option value="{{ $kk->id_kategori_kriteria }}">{{ $kk->nama_kriteria }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-12 mb-2">
            <label style="font-size:12.5px;">Sumber Dana</label>
            <select id="exkSumber" class="form-control form-control-sm exk-filter">
              <option value="">Semua Sumber Dana</option>
              @foreach ($sumberDana as $sd)
                <option value="{{ $sd->id_sumber_dana }}">{{ $sd->nama_sumber_dana }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div id="exkCountInfo" class="alert alert-info py-2 mb-2" style="font-size:12.5px;">
          Menghitung jumlah baris...
        </div>
        <div id="exkPdfWarn" class="alert alert-warning py-2 mb-0" style="font-size:12px; display:none;">
          ⚠ Data melebihi batas aman PDF (3.000 baris). Persempit filter,
          atau gunakan Export Excel untuk data besar.
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm" id="btnDoExportKeluar" disabled>
          <i class="fas fa-download mr-1"></i>Export
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    var exkMode = 'excel';           // 'pdf' | 'excel' — diisi dari tombol pemicu
    var exkCount = 0;
    var EXK_PDF_MAX = 3000;          // selaras batas $maxRows di controller

    function exkParams() {
        var p = new URLSearchParams();
        if ($('#exkTahun').val()) p.append('tahun', $('#exkTahun').val());
        if ($('#exkBulan').val()) p.append('bulan', $('#exkBulan').val());
        if ($('#exkKategori').val()) p.append('kategori', $('#exkKategori').val());
        if ($('#exkSumber').val()) p.append('sumber_dana', $('#exkSumber').val());
        return p.toString();
    }

    function exkRefreshButton() {
        var btn = $('#btnDoExportKeluar');
        var overPdf = exkMode === 'pdf' && exkCount > EXK_PDF_MAX;
        $('#exkPdfWarn').toggle(overPdf);
        btn.prop('disabled', exkCount === 0 || overPdf);
        btn.html(exkMode === 'pdf'
            ? '<i class="fas fa-print mr-1"></i>Export PDF (' + exkCount.toLocaleString('id-ID') + ' baris)'
            : '<i class="fas fa-file-excel mr-1"></i>Export Excel (' + exkCount.toLocaleString('id-ID') + ' baris)');
    }

    function exkUpdateCount() {
        $('#exkCountInfo').text('Menghitung jumlah baris...');
        $('#btnDoExportKeluar').prop('disabled', true);
        $.get('{{ route("bank-keluar.exportCount") }}?' + exkParams(), function (res) {
            exkCount = res.total || 0;
            $('#exkCountInfo').html(exkCount === 0
                ? 'Tidak ada data pada filter ini.'
                : '<strong>' + exkCount.toLocaleString('id-ID') + '</strong> baris akan diexport.');
            exkRefreshButton();
        }).fail(function () {
            $('#exkCountInfo').text('Gagal menghitung jumlah baris.');
        });
    }

    // Mode (pdf/excel) diambil dari tombol yang membuka modal
    $('#ModalExportKeluar').on('show.bs.modal', function (e) {
        exkMode = $(e.relatedTarget).data('mode') || 'excel';
        exkUpdateCount();
    });

    $(document).on('change', '.exk-filter', exkUpdateCount);

    $(document).on('click', '#btnDoExportKeluar', function () {
        var qs = exkParams();
        if (exkMode === 'pdf') {
            window.open('{{ url("/bank-keluar/view/pdf") }}' + (qs ? '?' + qs : ''), '_blank');
        } else {
            window.location.href = '{{ url("/bank-keluar/export_excel") }}' + (qs ? '?' + qs : '');
        }
        $('#ModalExportKeluar').modal('hide');
    });
});
</script>
@endpush
