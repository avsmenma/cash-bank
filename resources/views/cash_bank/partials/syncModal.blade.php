{{-- Unified Smart Sync & Fix Selisih Modal --}}
<div class="modal fade" id="modalSmartSync" tabindex="-1" role="dialog" aria-labelledby="modalSmartSyncTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow border-0" style="border-radius: 12px; overflow: hidden;">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header py-3 px-4" id="smartSyncHeader" style="background: linear-gradient(135deg, #0d3b6e 0%, #1e5ba0 100%); color: #fff;">
                <h5 class="modal-title font-weight-bold d-flex align-items-center" id="modalSmartSyncTitle" style="font-size: 1.15rem;">
                    <i class="fas fa-sync-alt mr-2" id="smartSyncTitleIcon"></i>
                    <span id="smartSyncTitleText">Sinkronisasi Google Sheets</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" id="smartSyncHeaderClose" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- 1. STAGE CONFIG (SELECTION) --}}
            <div id="smartSyncStageConfig">
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3 py-2 px-3 d-flex align-items-center" style="border-radius: 8px; background-color: #f8f9fa;">
                        <i class="fas fa-info-circle text-primary mr-2 fa-lg"></i>
                        <span class="small text-muted" id="smartSyncSubtitle">
                            Pilih data yang ingin Anda perbarui langsung dari Google Sheets.
                        </span>
                    </div>

                    {{-- Target Checkboxes Container --}}
                    <div class="card border mb-3 shadow-none" style="border-radius: 8px;">
                        <div class="card-header bg-white py-2 px-3 border-bottom font-weight-bold text-secondary small">
                            <i class="fas fa-layer-group mr-1"></i> TARGET SINKRONISASI
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush" style="font-size: 14px;">
                                
                                {{-- Target: Bank Masuk --}}
                                <li class="list-group-item d-flex align-items-center justify-content-between py-3 px-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="chkSyncMasuk" checked>
                                        <label class="custom-control-label font-weight-bold text-dark cursor-pointer" for="chkSyncMasuk">
                                            Bank Masuk
                                        </label>
                                        <div class="small text-muted mt-1">
                                            Unduh dan perbarui seluruh transaksi kas masuk terbaru dari spreadsheet.
                                        </div>
                                    </div>
                                    <div class="text-right pl-3" style="min-width: 170px;">
                                        <span class="badge badge-light border text-secondary px-2 py-1 font-weight-normal" id="statusSyncMasuk">
                                            <i class="fas fa-clock mr-1"></i><span class="sync-time-val">Memuat...</span>
                                        </span>
                                    </div>
                                </li>

                                {{-- Target: Bank Keluar --}}
                                <li class="list-group-item d-flex align-items-center justify-content-between py-3 px-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="chkSyncKeluar" checked>
                                        <label class="custom-control-label font-weight-bold text-dark cursor-pointer" for="chkSyncKeluar">
                                            Bank Keluar
                                        </label>
                                        <div class="small text-muted mt-1">
                                            Unduh dan perbarui transaksi kas keluar serta sinkronkan tanggal bayar ke Agenda.
                                        </div>
                                    </div>
                                    <div class="text-right pl-3" style="min-width: 170px;">
                                        <span class="badge badge-light border text-secondary px-2 py-1 font-weight-normal" id="statusSyncKeluar">
                                            <i class="fas fa-clock mr-1"></i><span class="sync-time-val">Memuat...</span>
                                        </span>
                                    </div>
                                </li>

                                {{-- Target: Agenda Online (Smart Cache) --}}
                                <li class="list-group-item d-flex align-items-center justify-content-between py-3 px-3" style="background-color: #fafbfc;">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="chkSyncAgenda">
                                        <label class="custom-control-label font-weight-bold text-dark cursor-pointer" for="chkSyncAgenda">
                                            Agenda Online <span class="text-muted font-weight-normal">(Opsional)</span>
                                        </label>
                                        <div class="small text-muted mt-1" id="descSyncAgenda">
                                            Sinkronisasi dokumen SPP Agenda Online (opsional).
                                        </div>
                                    </div>
                                    <div class="text-right pl-3" style="min-width: 170px;">
                                        <span class="badge badge-success d-none mb-1 px-2 py-1" id="badgeFreshAgenda">
                                            <i class="fas fa-check-circle mr-1"></i>Up-to-Date
                                        </span>
                                        <div>
                                            <span class="badge badge-light border text-secondary px-2 py-1 font-weight-normal" id="statusSyncAgenda">
                                                <i class="fas fa-clock mr-1"></i><span class="sync-time-val">Memuat...</span>
                                            </span>
                                        </div>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>

                    {{-- Option: Fix Selisih Saldo --}}
                    <div class="card border-warning mb-0" style="border-radius: 8px; background-color: #fffdf5;">
                        <div class="card-body py-2 px-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="chkFixSelisih">
                                <label class="custom-control-label font-weight-bold text-dark cursor-pointer" for="chkFixSelisih">
                                    <i class="fas fa-tools text-warning mr-1"></i> Fix Selisih Saldo (Koreksi Nominal Diubah)
                                </label>
                                <div class="small text-muted mt-1">
                                    Cocokkan nomor bukti / agenda dan koreksi nominal database jika terdapat nilai lama yang diedit langsung di spreadsheet.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light py-2 px-4 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" id="btnSubmitSmartSync">
                        <i class="fas fa-play mr-1"></i> Mulai Sinkronisasi
                    </button>
                </div>
            </div>

            {{-- 2. STAGE PROGRESS (LIVE STREAMING) --}}
            <div id="smartSyncStageProgress" class="d-none">
                <div class="modal-body p-4 text-center">
                    
                    {{-- Large Percent Display --}}
                    <div class="my-3">
                        <span class="display-4 font-weight-bold text-primary" id="smartSyncPercentText" style="letter-spacing: -1px;">0%</span>
                    </div>

                    {{-- Step Heading --}}
                    <h5 class="font-weight-bold text-dark mb-1" id="smartSyncStepTitle">Menghubungkan ke Server...</h5>
                    <p class="text-muted small mb-3" id="smartSyncStepSubtitle">Sedang mempersiapkan koneksi streaming...</p>

                    {{-- Progress Bar --}}
                    <div class="progress mb-3 shadow-inner" style="height: 22px; border-radius: 11px; background-color: #e9ecef;">
                        <div id="smartSyncProgressBar" 
                             class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                             role="progressbar" 
                             style="width: 0%; font-weight: 700; font-size: 12px; transition: width 0.3s ease;">
                            0%
                        </div>
                    </div>

                    {{-- Live Console / Log Box --}}
                    <div class="text-left p-3 border rounded bg-light" 
                         id="smartSyncLogBox" 
                         style="max-height: 140px; min-height: 80px; overflow-y: auto; font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; color: #495057;">
                        <div class="text-secondary"><i class="fas fa-circle-notch fa-spin mr-1"></i> Memulai proses sinkronisasi...</div>
                    </div>

                    <div class="mt-3 text-muted small">
                        <i class="fas fa-lock mr-1 text-warning"></i> Mohon jangan menutup atau memuat ulang halaman saat proses sinkronisasi sedang berjalan.
                    </div>

                </div>
                <div class="modal-footer bg-light py-2 px-4 justify-content-end">
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnAbortSmartSync">
                        <i class="fas fa-stop mr-1"></i> Batalkan
                    </button>
                </div>
            </div>

            {{-- 3. STAGE RESULT (SUMMARY BREAKDOWN) --}}
            <div id="smartSyncStageResult" class="d-none">
                <div class="modal-body p-4 text-center">
                    
                    <div class="my-2 text-success">
                        <i class="fas fa-check-circle" style="font-size: 54px;"></i>
                    </div>

                    <h4 class="font-weight-bold text-dark mb-1">Sinkronisasi Berhasil Selesai!</h4>
                    <p class="text-muted small mb-4" id="smartSyncResultDesc">Semua data terpilih berhasil dicocokkan dan diperbarui di database.</p>

                    {{-- Summary Table / Cards --}}
                    <div class="row text-left mb-3">
                        <div class="col-sm-6 mb-2">
                            <div class="p-3 border rounded bg-light h-100">
                                <div class="font-weight-bold text-primary mb-2">
                                    <i class="fas fa-arrow-circle-down mr-1"></i> Bank Masuk
                                </div>
                                <div class="d-flex justify-content-between small text-muted py-1 border-bottom">
                                    <span>Baris Diperbarui:</span>
                                    <b class="text-dark" id="resMasukUpdated">0</b>
                                </div>
                                <div class="d-flex justify-content-between small text-muted py-1">
                                    <span>Baris Baru Ditambahkan:</span>
                                    <b class="text-dark" id="resMasukInserted">0</b>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 mb-2">
                            <div class="p-3 border rounded bg-light h-100">
                                <div class="font-weight-bold text-danger mb-2">
                                    <i class="fas fa-arrow-circle-up mr-1"></i> Bank Keluar
                                </div>
                                <div class="d-flex justify-content-between small text-muted py-1 border-bottom">
                                    <span>Baris Diperbarui:</span>
                                    <b class="text-dark" id="resKeluarUpdated">0</b>
                                </div>
                                <div class="d-flex justify-content-between small text-muted py-1">
                                    <span>Baris Baru Ditambahkan:</span>
                                    <b class="text-dark" id="resKeluarInserted">0</b>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Fix Selisih Result Banner --}}
                    <div id="resSelisihBanner" class="alert alert-warning py-2 px-3 text-left small mb-0 d-none" style="border-radius: 8px;">
                        <i class="fas fa-tools mr-1 text-warning"></i>
                        <span>Koreksi Selisih: Berhasil memperbaiki nominal pada <strong id="resSelisihCount">0</strong> dokumen!</span>
                    </div>

                </div>
                <div class="modal-footer bg-light py-2 px-4 justify-content-end">
                    <button type="button" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm" id="btnFinishSmartSync">
                        <i class="fas fa-check mr-1"></i> Selesai &amp; Muat Ulang Halaman
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    var eventSource = null;

    // Helper: switch modal stage
    function showStage(stage) {
        $('#smartSyncStageConfig').addClass('d-none');
        $('#smartSyncStageProgress').addClass('d-none');
        $('#smartSyncStageResult').addClass('d-none');

        if (stage === 'config') {
            $('#smartSyncStageConfig').removeClass('d-none');
            $('#smartSyncHeaderClose').removeClass('d-none');
        } else if (stage === 'progress') {
            $('#smartSyncStageProgress').removeClass('d-none');
            $('#smartSyncHeaderClose').addClass('d-none');
        } else if (stage === 'result') {
            $('#smartSyncStageResult').removeClass('d-none');
            $('#smartSyncHeaderClose').removeClass('d-none');
        }
    }

    // Helper: append log line
    function appendLog(msg, isError) {
        var $box = $('#smartSyncLogBox');
        var colorClass = isError ? 'text-danger font-weight-bold' : 'text-dark';
        var time = new Date().toLocaleTimeString('id-ID');
        var line = $('<div>').addClass(colorClass).html('[' + time + '] ' + msg);
        $box.append(line);
        $box.scrollTop($box[0].scrollHeight);
    }

    // Fetch and populate sync status
    function loadSyncStatus(callback) {
        $('.sync-time-val').text('Memuat...');
        $('#badgeFreshAgenda').addClass('d-none');

        $.ajax({
            url: "{{ route('bank.sync-sheets.status') }}",
            type: "GET",
            dataType: "json",
            success: function(res) {
                if (res.success && res.status) {
                    var s = res.status;
                    if (s.bank_masuk) {
                        $('#statusSyncMasuk .sync-time-val').text(s.bank_masuk.human_time);
                    }
                    if (s.bank_keluar) {
                        $('#statusSyncKeluar .sync-time-val').text(s.bank_keluar.human_time);
                    }
                    if (s.agenda) {
                        $('#statusSyncAgenda .sync-time-val').text(s.agenda.human_time);
                        if (s.agenda.is_fresh) {
                            $('#badgeFreshAgenda').removeClass('d-none');
                            $('#chkSyncAgenda').prop('checked', false);
                            $('#descSyncAgenda').html('<span class="text-success"><i class="fas fa-check-circle mr-1"></i>Data Agenda masih baru (&lt; 30 mnt).</span>');
                        } else {
                            $('#badgeFreshAgenda').addClass('d-none');
                            $('#chkSyncAgenda').prop('checked', false);
                            $('#descSyncAgenda').text('Pembaruan data dokumen Agenda Online (opsional).');
                        }
                    }
                }
                if (typeof callback === 'function') callback();
            },
            error: function() {
                $('.sync-time-val').text('Tidak dapat memuat');
                if (typeof callback === 'function') callback();
            }
        });
    }

    // Global opener function
    window.openSmartSyncModal = function(opts) {
        opts = opts || {};
        showStage('config');

        // Mode: Fix Selisih Saldo
        if (opts.fix_selisih) {
            $('#smartSyncHeader').css('background', 'linear-gradient(135deg, #d39e00 0%, #e0a800 100%)');
            $('#smartSyncTitleIcon').attr('class', 'fas fa-tools mr-2');
            $('#smartSyncTitleText').text('Fix Selisih Saldo & Sinkronisasi');
            $('#chkFixSelisih').prop('checked', true);
            $('#smartSyncSubtitle').text('Mode Fix Selisih aktif: Sistem akan mencocokkan dokumen dan mengoreksi perbedaan nominal antara spreadsheet dan database.');
        } else {
            $('#smartSyncHeader').css('background', 'linear-gradient(135deg, #0d3b6e 0%, #1e5ba0 100%)');
            $('#smartSyncTitleIcon').attr('class', 'fas fa-sync-alt mr-2');
            $('#smartSyncTitleText').text('Sinkronisasi Google Sheets');
            $('#chkFixSelisih').prop('checked', false);
            $('#smartSyncSubtitle').text('Pilih data yang ingin Anda perbarui langsung dari Google Sheets.');
        }

        // Target selections
        if (opts.target === 'masuk') {
            $('#chkSyncMasuk').prop('checked', true);
            $('#chkSyncKeluar').prop('checked', false);
        } else if (opts.target === 'keluar') {
            $('#chkSyncMasuk').prop('checked', false);
            $('#chkSyncKeluar').prop('checked', true);
        } else {
            $('#chkSyncMasuk').prop('checked', true);
            $('#chkSyncKeluar').prop('checked', true);
        }

        $('#modalSmartSync').modal('show');
        loadSyncStatus();
    };

    // Submit handler
    $('#btnSubmitSmartSync').on('click', function(e) {
        e.preventDefault();

        var syncMasuk   = $('#chkSyncMasuk').is(':checked');
        var syncKeluar  = $('#chkSyncKeluar').is(':checked');
        var syncAgenda  = $('#chkSyncAgenda').is(':checked');
        var fixSelisih  = $('#chkFixSelisih').is(':checked');

        if (!syncMasuk && !syncKeluar && !syncAgenda) {
            alert('Pilih minimal satu target sinkronisasi.');
            return;
        }

        showStage('progress');
        $('#smartSyncPercentText').text('0%');
        $('#smartSyncProgressBar').css('width', '0%').text('0%');
        $('#smartSyncStepTitle').text('Memulai Sinkronisasi...');
        $('#smartSyncStepSubtitle').text('Menghubungkan streaming progress...');
        $('#smartSyncLogBox').empty();
        appendLog('Inisialisasi koneksi EventSource...');

        var params = $.param({
            sync_agenda: syncAgenda ? 1 : 0,
            sync_masuk: syncMasuk ? 1 : 0,
            sync_keluar: syncKeluar ? 1 : 0,
            fix_selisih: fixSelisih ? 1 : 0
        });

        var streamUrl = "{{ route('bank.sync-sheets.stream') }}?" + params;

        if (eventSource) {
            eventSource.close();
        }

        eventSource = new EventSource(streamUrl);

        eventSource.onmessage = function(e) {
            try {
                var data = JSON.parse(e.data);
                
                // Update percent
                var pct = data.percent || 0;
                $('#smartSyncPercentText').text(pct + '%');
                $('#smartSyncProgressBar').css('width', pct + '%').text(pct + '%');

                // Update text
                if (data.step_name) {
                    $('#smartSyncStepTitle').text(data.step_name);
                }
                if (data.message) {
                    $('#smartSyncStepSubtitle').text(data.message);
                    appendLog(data.message);
                }

                // Completed
                if (data.status === 'completed') {
                    eventSource.close();
                    eventSource = null;

                    var summary = data.summary || {};
                    var bm = summary.bank_masuk || {};
                    var bk = summary.bank_keluar || {};

                    $('#resMasukUpdated').text(bm.updated !== undefined ? bm.updated : '-');
                    $('#resMasukInserted').text(bm.inserted !== undefined ? bm.inserted : '-');
                    $('#resKeluarUpdated').text(bk.updated !== undefined ? bk.updated : '-');
                    $('#resKeluarInserted').text(bk.inserted !== undefined ? bk.inserted : '-');

                    var totalFixed = (bm.fixed_selisih || 0) + (bk.fixed_selisih || 0);
                    if (totalFixed > 0 || fixSelisih) {
                        $('#resSelisihBanner').removeClass('d-none');
                        $('#resSelisihCount').text(totalFixed);
                    } else {
                        $('#resSelisihBanner').addClass('d-none');
                    }

                    setTimeout(function() {
                        showStage('result');
                    }, 500);
                } else if (data.status === 'error') {
                    eventSource.close();
                    eventSource = null;
                    appendLog('ERROR: ' + (data.message || 'Terjadi kesalahan sistem'), true);
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                    showStage('config');
                }
            } catch (err) {
                console.error("Gagal parse SSE data:", err);
            }
        };

        eventSource.onerror = function(err) {
            console.warn("EventSource stream connection closed/interrupted:", err);
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
            appendLog('Koneksi streaming terputus.', true);
        };
    });

    // Abort button
    $('#btnAbortSmartSync').on('click', function() {
        if (confirm('Apakah Anda yakin ingin membatalkan pemantauan proses ini?')) {
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
            showStage('config');
        }
    });

    // Finish / Reload button
    $('#btnFinishSmartSync').on('click', function() {
        $('#modalSmartSync').modal('hide');
        if (typeof window.bmReload === 'function') {
            window.bmReload();
        } else if (typeof window.bkReload === 'function') {
            window.bkReload();
        } else {
            window.location.reload();
        }
    });

});
</script>
@endpush
