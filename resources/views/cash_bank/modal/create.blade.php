<div class="modal fade" id="ModalCreateKeluar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data <span style="color:#FF7518">Bank Keluar</span></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formBankKeluar">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="form-label">Daftar Nomor Agenda</label>
                        <textarea
                            name="agenda_numbers"
                            id="agendaNumbersKeluar"
                            class="form-control"
                            rows="8"
                            placeholder="Masukkan nomor agenda (satu per baris atau dipisahkan koma)&#10;Contoh:&#10;0003_2026&#10;0004_2026&#10;0005_2026"></textarea>
                        <small class="text-muted">Format: <code>0003_2026</code>, <code>0004_2026</code> atau satu per baris.</small>
                    </div>
                    <div class="alert alert-info py-2 mb-0" style="font-size:12.5px;">
                        Sistem akan menarik data dari Agenda Online. Jika uraian diawali nomor virtual account kebun,
                        kolom Bank Tujuan pada preview akan otomatis terpilih.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnPreviewAgendaKeluar">
                        <i class="fas fa-search mr-1"></i> Preview Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalPreviewAgendaKeluar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background:#0d3b6e; color:#fff;">
                <h5 class="modal-title"><i class="fas fa-list-alt mr-2"></i>Preview Dokumen Bank Keluar</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="d-flex align-items-center px-3 py-2"
                     style="background:#f8f9fa; border-bottom:1px solid #dee2e6; gap:12px; flex-wrap:wrap;">
                    <span class="badge badge-primary px-3 py-2" id="badgeTotalAgendaKeluar">Total: 0 dokumen</span>
                    <span class="badge badge-success px-3 py-2" id="badgeSaveAgendaKeluar">Siap simpan: 0</span>
                    <span class="badge badge-warning px-3 py-2" id="badgeWarnAgendaKeluar" style="display:none;">0 peringatan</span>
                    <small class="text-muted ml-auto">Bank Tujuan dapat disesuaikan sebelum disimpan.</small>
                </div>
                <div style="max-height:460px; overflow-y:auto; overflow-x:auto;">
                    <table class="table table-bordered table-sm mb-0 agenda-preview-table" style="font-size:11px; min-width:1500px;">
                        <thead>
                            <tr style="background:#0d3b6e; color:#fff; position:sticky; top:0; z-index:1;">
                                <th style="width:40px;">No</th>
                                <th>Agenda</th>
                                <th>Tanggal</th>
                                <th style="min-width:230px;">Bank Tujuan</th>
                                <th>Kriteria</th>
                                <th>Sub Kriteria</th>
                                <th>Item Sub Kriteria</th>
                                <th>Jenis Pembayaran</th>
                                <th>Penerima</th>
                                <th style="min-width:260px;">Uraian</th>
                                <th class="text-right">Kredit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="previewAgendaKeluarBody">
                            <tr><td colspan="12" class="text-center text-muted py-4">Belum ada data preview.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btnConfirmAgendaKeluar" disabled>
                    <i class="fas fa-save mr-1"></i>Simpan Hasil Preview
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .agenda-preview-table thead th {
        color: #fff !important;
    }
</style>
<script>
$(function () {
    var previewRowsAgendaKeluar = [];
    var bankOptionsAgendaKeluar = [];

    function showInfo(title, message, type) {
        if ($('#modalInfo').length) {
            $('#modalInfoTitle').text(title);
            $('#modalInfoIcon').attr('class', 'fas ' + (type === 'success' ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-warning') + ' mr-2');
            $('#modalInfoMsg').text(message);
            $('#modalInfo').modal('show');
        } else {
            alert(message);
        }
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderBankSelect(row, index) {
        if (!row.can_save) return '-';
        if (row.warning_message === 'Nomor VA di awal uraian tidak cocok dengan master Bank Tujuan') {
            return '';
        }

        var html = '<select class="form-control form-control-sm agenda-bank-select" data-index="' + index + '">';
        html += '<option value="">-- Pilih Bank Tujuan --</option>';
        bankOptionsAgendaKeluar.forEach(function (bank) {
            var selected = String(row.bank_tujuan_id || '') === String(bank.id) ? ' selected' : '';
            html += '<option value="' + bank.id + '"' + selected + '>' + escapeHtml(bank.text) + '</option>';
        });
        html += '</select>';

        return html;
    }

    function renderPreview(rows) {
        if (!rows.length) {
            $('#previewAgendaKeluarBody').html('<tr><td colspan="12" class="text-center text-muted py-4">Tidak ada dokumen ditemukan.</td></tr>');
            return;
        }

        var html = '';
        rows.forEach(function (row, index) {
            var bg = row.warning ? 'background:#fff8dc;' : '';
            var status = row.can_save
                ? (row.warning ? '<span class="badge badge-warning">Perlu cek</span>' : '<span class="badge badge-success">OK</span>')
                : '<span class="badge badge-danger">Tidak dapat disimpan</span>';
            var warning = row.warning_message && row.warning_message !== 'Nomor VA di awal uraian tidak cocok dengan master Bank Tujuan'
                ? '<div class="text-muted mt-1" style="font-size:10px;">' + escapeHtml(row.warning_message) + '</div>'
                : '';

            html += '<tr style="' + bg + '">'
                + '<td class="text-center">' + row.no + '</td>'
                + '<td>' + escapeHtml(row.agenda || '-') + '</td>'
                + '<td>' + escapeHtml(row.tanggal || '-') + '</td>'
                + '<td>' + renderBankSelect(row, index) + warning + '</td>'
                + '<td>' + escapeHtml(row.kategori || '-') + '</td>'
                + '<td>' + escapeHtml(row.sub_kriteria || '-') + '</td>'
                + '<td>' + escapeHtml(row.item_sub_kriteria || '-') + '</td>'
                + '<td>' + escapeHtml(row.jenis_pembayaran || '-') + '</td>'
                + '<td>' + escapeHtml(row.penerima || '-') + '</td>'
                + '<td style="max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + escapeHtml(row.uraian || '-') + '">' + escapeHtml(row.uraian || '-') + '</td>'
                + '<td class="text-right">' + escapeHtml(row.kredit || '0') + '</td>'
                + '<td>' + status + '</td>'
                + '</tr>';
        });

        $('#previewAgendaKeluarBody').html(html);
    }

    $('#ModalCreateKeluar').on('show.bs.modal', function () {
        $('#agendaNumbersKeluar').val('');
        previewRowsAgendaKeluar = [];
        bankOptionsAgendaKeluar = [];
    });

    $(document).on('click', '#btnPreviewAgendaKeluar', function () {
        var agendaNumbers = $('#agendaNumbersKeluar').val().trim();

        if (!agendaNumbers) {
            showInfo('Perhatian', 'Masukkan minimal satu nomor agenda.', 'warning');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memuat...');

        $('#previewAgendaKeluarBody').html('<tr><td colspan="12" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Menarik data dokumen...</td></tr>');
        $('#btnConfirmAgendaKeluar').prop('disabled', true);
        $('#ModalCreateKeluar').modal('hide');
        setTimeout(function () { $('#ModalPreviewAgendaKeluar').modal('show'); }, 350);

        $.ajax({
            url: '{{ route("bank-keluar.previewAgenda") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                agenda_numbers: agendaNumbers
            },
            success: function (res) {
                previewRowsAgendaKeluar = res.rows || [];
                bankOptionsAgendaKeluar = res.bank_options || [];

                $('#badgeTotalAgendaKeluar').text('Total: ' + (res.total || 0) + ' dokumen');
                $('#badgeSaveAgendaKeluar').text('Siap simpan: ' + (res.savable || 0));
                $('#badgeWarnAgendaKeluar')
                    .toggle((res.warnings || 0) > 0)
                    .text((res.warnings || 0) + ' peringatan');
                $('#btnConfirmAgendaKeluar').prop('disabled', (res.savable || 0) === 0);

                renderPreview(previewRowsAgendaKeluar);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menarik data dokumen.';
                $('#previewAgendaKeluarBody').html('<tr><td colspan="12" class="text-center text-danger py-4">' + escapeHtml(msg) + '</td></tr>');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="fas fa-search mr-1"></i> Preview Dokumen');
            }
        });
    });

    $(document).on('click', '#btnConfirmAgendaKeluar', function () {
        var btn = $(this);
        var bankTujuan = {};

        $('.agenda-bank-select').each(function () {
            bankTujuan[$(this).data('index')] = $(this).val();
        });

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: '{{ route("bank-keluar.confirmAgenda") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                bank_tujuan: bankTujuan
            },
            success: function (res) {
                $('#ModalPreviewAgendaKeluar').modal('hide');
                if ($.fn.DataTable.isDataTable('#example3')) {
                    $('#example3').DataTable().ajax.reload(null, false);
                }
                showInfo('Berhasil', res.success || 'Data berhasil disimpan.', 'success');
            },
            error: function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Gagal menyimpan data.';
                showInfo('Gagal', msg, 'warning');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Simpan Hasil Preview');
            }
        });
    });
});
</script>
@endpush
