<?php


use App\Models\ItemSubKriteria;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserSAPController;
use App\Http\Controllers\DroppingController;
use App\Http\Controllers\PenerimaController;
use App\Http\Controllers\BankMasukController;
use App\Http\Controllers\DaftarSPPController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SaldoAwalController;
use App\Http\Controllers\BankKeluarController;
use App\Http\Controllers\DaftarBankController;
use App\Http\Controllers\DetailItemController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\DaftarRekeningController;
use App\Http\Controllers\DetailSubKategoriController;
use App\Http\Controllers\DashboardPembayaranController;
use App\Http\Controllers\DetaiControllerCashFlowController;
use App\Http\Controllers\VADashboardController;
use App\Http\Controllers\RingkasanPembayaranController;
use App\Http\Controllers\ProgrammerController;

Route::get('/', fn() => view('auth.login'));
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::group(['middleware' => ['auth', 'check_role:admin']], function () {
    Route::get('/dashboard-cash-bank', [dashboardController::class, 'index'])
        ->name('dashboard.index');
    Route::get('/dashboard-cash-bank/data2', [dashboardController::class, 'data2'])
        ->name('dashboard.data2');
    Route::get('/dashboard-cash-bank/export_excel', [dashboardController::class, 'export_excel'])
        ->name('dashboard.excel');
    Route::get('/dashboard-cash-bank/export_excelPvd', [dashboardController::class, 'export_excelPvd'])
        ->name('dashboard.excelPvd');
    Route::get('/dashboard-cash-bank/view_pdf', [dashboardController::class, 'view_pdf'])
        ->name('dashboard.pdf');

    Route::get('/dashboard-modal-kerja', [dashboardController::class, 'modalKerja'])
        ->name('dashboard.modal-kerja.index');

    Route::get('/dashboard-modal-kerja/data', [dashboardController::class, 'modalKerjaData'])
        ->name('dashboard.modal-kerja.data');

    Route::get('/dashboard-bank', [dashboardController::class, 'bank'])
        ->name('dashboard.bank.index');

    Route::get('/dashboard-bank/export-excel', [dashboardController::class, 'bankExportExcel'])
        ->name('dashboard.bank.excel');

    Route::get('/dashboard-bank/export-pdf', [dashboardController::class, 'bankExportPdf'])
        ->name('dashboard.bank.pdf');

});

Route::group(['middleware' => ['auth', 'check_role:vendor']], function () {
    Route::get('/userVendor', [UserSAPController::class, 'index'])
        ->name('userVendor.index');
});


// menu - protected routes

Route::middleware(['auth'])->group(function () {
    Route::get('/daftar-spp', [daftarSPPController::class, 'index'])->name('daftar-spp.index');
    Route::get('/daftar-spp/data', [daftarSPPController::class, 'datatable'])->name('daftar-spp.data');
    Route::get('/daftar-spp/data-grouped', [daftarSPPController::class, 'dataGrouped'])->name('daftar-spp.data-grouped');
    Route::get('/user-sap/export_excel', [UserSAPController::class, 'export_excel']);
    Route::get('/user-sap/view/pdf', [UserSAPController::class, 'view_pdf']);
    Route::put('/user-sap/{id}', [UserSAPController::class, 'update']);
});

Route::middleware(['auth'])->group(function () {

    Route::prefix('bank-keluar')->name('bank-keluar.')->group(function () {

        Route::get('/report', [BankKeluarController::class, 'report'])
            ->name('report');
        Route::get('/report/data', [BankKeluarController::class, 'reportData'])
            ->name('report.data');

        // Import CSV lama (fuzzy-match) — khusus role programmer
        Route::post('/importExcel', [BankKeluarController::class, 'importExcel'])
            ->name('importExcel')->middleware('check_role:programmer');

        Route::post('/preview-import', [BankKeluarController::class, 'previewImport'])
            ->name('previewImport')->middleware('check_role:programmer');

        Route::post('/confirm-import', [BankKeluarController::class, 'confirmImport'])
            ->name('confirmImport')->middleware('check_role:programmer');

        // Import mandiri berbasis template (semua user halaman ini):
        // preview dulu (tanpa simpan), lalu konfirmasi untuk menyimpan
        Route::get('/template-import', [BankKeluarController::class, 'downloadTemplateImport'])
            ->name('templateImport');

        Route::post('/preview-template', [BankKeluarController::class, 'previewTemplate'])
            ->name('previewTemplate');

        Route::post('/confirm-template', [BankKeluarController::class, 'confirmTemplate'])
            ->name('confirmTemplate');

        Route::post('/preview-agenda', [BankKeluarController::class, 'previewAgenda'])
            ->name('previewAgenda');

        Route::post('/confirm-agenda', [BankKeluarController::class, 'confirmAgenda'])
            ->name('confirmAgenda');

        Route::get('/export_excel', [BankKeluarController::class, 'export_excel'])
            ->name('export_excel');

        // Jumlah baris sesuai filter export — untuk modal konfirmasi export
        Route::get('/export-count', [BankKeluarController::class, 'exportCount'])
            ->name('exportCount');

        Route::get('/report_export_excel', [BankKeluarController::class, 'report_export_excel'])
            ->name('report_export_excel');

        Route::get('/reportKeluarPdf', [BankKeluarController::class, 'reportKeluarPdf'])
            ->name('reportKeluarPdf');

        Route::get('/view/pdf', [BankKeluarController::class, 'view_pdf'])
            ->name('view_pdf');

        Route::get('/ajax', [BankKeluarController::class, 'ajax'])
            ->name('ajax');

        Route::delete('/selected-employee', [BankKeluarController::class, 'deleteAll'])
            ->name('delete');

    });
    Route::get('/bank-keluar/data', [BankKeluarController::class, 'datatable'])
        ->name('bank-keluar.data');

    Route::get('/detail-transaksi', [BankKeluarController::class, 'getDetailTransaksi'])
        ->name('bank-keluar.detail-transaksi');

    Route::get('/export-detail', [BankKeluarController::class, 'exportDetailTransaksi'])
        ->name('bank-keluar.export-detail');

    Route::get('/get-kategori-kriteria/{id}', [BankKeluarController::class, 'getKriteria']);
    Route::get('/get-sub-kriteria/{id}', [BankKeluarController::class, 'getSub']);
    Route::get('/get-item-sub-kriteria/{id}', [BankKeluarController::class, 'getItem']);
    Route::get('/get-dokumen-detail/{id}', [BankKeluarController::class, 'getDokumenDetail']);
    Route::resource('bank-keluar', BankKeluarController::class);

});

Route::middleware(['auth'])->group(function () {
    Route::prefix('bank-masuk')->name('bank-masuk.')->group(function () {

        Route::get('/report', [BankMasukController::class, 'report'])
            ->name('report');

        // Import CSV lama (fuzzy-match) — khusus role programmer
        Route::post('/importExcel', [BankMasukController::class, 'importExcel'])
            ->name('importExcel')->middleware('check_role:programmer');

        Route::post('/preview-import', [BankMasukController::class, 'previewImport'])
            ->name('previewImport')->middleware('check_role:programmer');

        Route::post('/confirm-import', [BankMasukController::class, 'confirmImport'])
            ->name('confirmImport')->middleware('check_role:programmer');

        // Import mandiri berbasis template (semua user halaman ini):
        // preview dulu (tanpa simpan), lalu konfirmasi untuk menyimpan
        Route::get('/template-import', [BankMasukController::class, 'downloadTemplateImport'])
            ->name('templateImport');

        Route::post('/preview-template', [BankMasukController::class, 'previewTemplate'])
            ->name('previewTemplate');

        Route::post('/confirm-template', [BankMasukController::class, 'confirmTemplate'])
            ->name('confirmTemplate');

        Route::get('/export_excel', [BankMasukController::class, 'export_excel']);

        // Jumlah baris sesuai filter export — untuk modal konfirmasi export
        Route::get('/export-count', [BankMasukController::class, 'exportCount'])
            ->name('exportCount');

        Route::get('/report_export_excel', [BankMasukController::class, 'report_export_excel'])
            ->name('report_export_excel');

        Route::get('/reportMasukPdf', [BankMasukController::class, 'reportMasukPdf'])
            ->name('reportMasukPdf');

        Route::get('/view/pdf', [BankMasukController::class, 'view_pdf']);

        // delete multiple
        Route::delete('/selected-employee', [BankMasukController::class, 'deleteAll'])
            ->name('delete');

        Route::get('/bank-masuk/ajax', [BankMasukController::class, 'ajax'])
            ->name('bank-masuk.ajax');


    });
    Route::get('/bank-masuk/data', [BankMasukController::class, 'datatable'])
        ->name('bank-masuk.data');

    Route::resource('bank-masuk', BankMasukController::class);

    Route::get('/sub-kriteria/{id}', [BankMasukController::class, 'getSubKriteria']);
    Route::get('/item-sub-kriteria/{id}', [BankMasukController::class, 'getItemSubKriteria']);


});

Route::middleware(['auth'])->group(function () {
    Route::prefix('detail-item')->name('detail-item.')->group(function () {

        Route::get('/', [DetailItemController::class, 'index'])
            ->name('index');

        Route::get('/export_excel', [DetailItemController::class, 'export_excel'])
            ->name('export_excel');

        Route::get('/view_pdf', [DetailItemController::class, 'view_pdf'])
            ->name('view_pdf');

    });
    Route::prefix('detail-sub')->name('detail-sub.')->group(function () {

        Route::get('/', [DetailSubKategoriController::class, 'index'])
            ->name('index');

        Route::get('/export_excel', [DetailSubKategoriController::class, 'export_excel'])
            ->name('export_excel');

        Route::get('/view_pdf', [DetailSubKategoriController::class, 'view_pdf'])
            ->name('view_pdf');

    });

});
Route::middleware(['auth'])->group(function () {
    Route::resource('daftarRekening', DaftarRekeningController::class);
    Route::get('/daftarBank/data', [DaftarBankController::class, 'datatable'])->name('daftarBank.data');
    Route::patch('/daftarBank/{id}/sap', [DaftarBankController::class, 'updateSap'])->name('daftarBank.updateSap');
    Route::resource('daftarBank', DaftarBankController::class);
    Route::get('/daftarBank/{id}/detail', [DaftarBankController::class, 'showDetail'])
        ->name('daftarBank.detail');
    Route::get('/daftarBank/{id}/export-detail-excel', [VADashboardController::class, 'exportExcelById'])
        ->name('daftarBank.exportDetail');

    Route::resource('saldoAwal', SaldoAwalController::class);

});

Route::middleware(['auth'])->group(function () {
    Route::prefix('penerima')->name('penerima.')->group(function () {

        // delete multiple
        Route::delete('/selected-employee', [PenerimaController::class, 'deleteAll'])
            ->name('delete');

        Route::get('/bank-masuk/ajax', [PenerimaController::class, 'ajax'])
            ->name('bank-masuk.ajax');

    });

    Route::get('/penerima/cashflow', [PenerimaController::class, 'cashFlow'])
        ->name('penerima.cashflow');
    Route::get('/penerima/rencana', [PenerimaController::class, 'rencana'])
        ->name('penerima.rencana');
    Route::get('/penerima/gabungan', [PenerimaController::class, 'gabungan'])
        ->name('penerima.gabungan');
    Route::get('/penerima/export_excel_rencana', [PenerimaController::class, 'export_excel_rencana'])
        ->name('penerima.export_excel_rencana');
    Route::get('/penerima/export_pdf_rencana', [PenerimaController::class, 'export_pdf_rencana'])
        ->name('penerima.export_pdf_rencana');

    Route::get('/penerima/export_excel_cashFlow', [PenerimaController::class, 'export_excel_cashFlow'])
        ->name('penerima.export_excel_cashFlow');
    Route::get('/penerima/export_pdf_cashFlow', [PenerimaController::class, 'export_pdf_cashFlow'])
        ->name('penerima.export_pdf_cashFlow');

    Route::get('/penerima/export_excel_gabungan', [PenerimaController::class, 'export_excel_gabungan'])
        ->name('penerima.export_excel_gabungan');
    Route::get('/penerima/export_pdf_gabungan', [PenerimaController::class, 'export_pdf_gabungan'])
        ->name('penerima.export_pdf_gabungan');

    Route::post('/penerima/save', [PenerimaController::class, 'save'])
        ->name('penerima.rencana.save');

    Route::post('/penerima/save-batch', [PenerimaController::class, 'saveBatch'])
        ->name('penerima.rencana.saveBatch');

    Route::get('/penerima/data-grouped', [PenerimaController::class, 'dataGrouped'])
        ->name('penerima.data-grouped');

    Route::get('/penerima/data', [PenerimaController::class, 'datatable'])
        ->name('penerima.data');

    Route::get('/penerima/export_excel', [PenerimaController::class, 'export_excel'])
        ->name('penerima.export_excel');

    Route::get('/penerima/export_pdf', [PenerimaController::class, 'export_pdf'])
        ->name('penerima.export_pdf');

    Route::post('/penerima/import', [PenerimaController::class, 'importData'])
        ->name('penerima.import');

    Route::resource('penerima', PenerimaController::class);

});
Route::middleware(['auth'])->group(function () {
    Route::prefix('permintaan')->name('permintaan.')->group(function () {

        // delete multiple
        Route::delete('/selected-employee', [PermintaanController::class, 'deleteAll'])
            ->name('delete');

    });
    Route::get('/permintaan/cashflow', [PermintaanController::class, 'cashFlow'])->name('permintaan.cashflow');
    Route::get('/permintaan/table', [PermintaanController::class, 'getTable']);
    Route::get('/permintaan/table', [PermintaanController::class, 'getTable'])->name('permintaan.table');
    Route::post('/permintaan/save', [PermintaanController::class, 'saveData'])->name('permintaan.save');
    Route::post('/permintaan/save-batch', [PermintaanController::class, 'saveBatch'])->name('permintaan.saveBatch');
    Route::delete('/permintaan/delete', [PermintaanController::class, 'deleteData'])->name('permintaan.delete');
    Route::get('/permintaan/sub-kriteria/{id}', [PermintaanController::class, 'getSub']);
    Route::post('/permintaan/import', [PermintaanController::class, 'importExcel'])->name('permintaan.import');
    Route::get('/permintaan/export_excel', [PermintaanController::class, 'export_excel'])->name('permintaan.export_excel');
    Route::get('/permintaan/export_pdf', [PermintaanController::class, 'export_pdf'])->name('permintaan.export_pdf');
    Route::resource('permintaan', PermintaanController::class);
    Route::get('/dropping/gabungan', [DroppingController::class, 'gabungan'])
        ->name('dropping.gabungan');

});
Route::middleware(['auth'])->group(function () {
    Route::prefix('dropping')->name('dropping.')->group(function () {

        // delete multiple
        Route::delete('/selected-employee', [DroppingController::class, 'deleteAll'])
            ->name('delete');

    });
    Route::get('/dropping/sub-kriteria/{id}', [DroppingController::class, 'getSub']);
    Route::get('/dropping/cashflow', [DroppingController::class, 'cashFlow'])->name('dropping.cashflow');
    Route::get('/dropping/rencana', [DroppingController::class, 'rencana'])
        ->name('dropping.rencana');
    Route::get('/dropping/table', [DroppingController::class, 'getTable'])->name('dropping.table');
    Route::post('/dropping/save', [DroppingController::class, 'saveData'])->name('dropping.save');
    Route::post('/dropping/save-batch', [DroppingController::class, 'saveBatch'])->name('dropping.saveBatch');
    Route::post('/dropping/saveRencana', [DroppingController::class, 'saveRencana'])
        ->name('dropping.rencana.saveRencana');
    Route::delete('/dropping/delete', [DroppingController::class, 'deleteData'])->name('dropping.delete');
    Route::post('/dropping/import', [DroppingController::class, 'importExcel'])->name('dropping.import');
    Route::get('/dropping/export_excel', [DroppingController::class, 'export_excel'])->name('dropping.export_excel');
    Route::get('/dropping/export_pdf', [DroppingController::class, 'export_pdf'])->name('dropping.export_pdf');
    Route::resource('dropping', DroppingController::class);

});
// routes/web.php

// Dashboard Pembayaran
Route::middleware(['auth'])->prefix('dashboard-pembayaran')->name('dashboard.pembayaran.')->group(function () {
    // Halaman utama (sudah include data)
    Route::get('/', [DashboardPembayaranController::class, 'index'])->name('index');

    // AJAX: Load data tabel (untuk refresh tanpa reload halaman)
    Route::get('/data', [DashboardPembayaranController::class, 'data'])->name('data');
    Route::get('/data2', [DashboardPembayaranController::class, 'data2'])->name('data2');

    // Export PDF
    Route::get('/export-pdf', [DashboardPembayaranController::class, 'exportPdf'])->name('pdf');

    // Export Excel
    Route::get('/export-excel', [DashboardPembayaranController::class, 'exportExcel'])->name('excel');

});

Route::middleware(['auth'])->group(function () {
    Route::get('/cashflow', [DetaiControllerCashFlowController::class, 'index'])->name('cashflow.index');
    Route::get('/cashflow/detail', [DetaiControllerCashFlowController::class, 'detail'])->name('cashflow.detail');
});

Route::group(['middleware' => ['auth', 'check_role:va'], 'prefix' => 'va'], function () {
    Route::get('/dashboard', [VADashboardController::class, 'index'])->name('va.dashboard');
    Route::get('/cashflow', [VADashboardController::class, 'cashflow'])->name('va.cashflow');
    Route::get('/export-excel', [VADashboardController::class, 'exportExcel'])->name('va.export-excel');
});

// RINGKASAN PEMBAYARAN HIERARKI
Route::middleware(['auth'])->prefix('ringkasan-pembayaran')->name('ringkasan.')->group(function () {
    Route::get('/', [RingkasanPembayaranController::class, 'index'])->name('index');
    Route::get('/detail', [RingkasanPembayaranController::class, 'detail'])->name('detail');
});

// PROGRAMMER PANEL — Data Management
Route::group(['middleware' => ['auth', 'check_role:programmer'], 'prefix' => 'programmer'], function () {
    Route::get('/', [ProgrammerController::class, 'index'])->name('programmer.index');
    Route::get('/data/{table}', [ProgrammerController::class, 'getData'])->name('programmer.data');
    Route::post('/keyword-preview', [ProgrammerController::class, 'keywordPreview'])->name('programmer.keywordPreview');
    Route::post('/keyword-update', [ProgrammerController::class, 'keywordUpdate'])->name('programmer.keywordUpdate');
    Route::delete('/delete/{table}/{id}', [ProgrammerController::class, 'deleteRecord'])->name('programmer.delete');
    Route::delete('/bulk-delete/{table}', [ProgrammerController::class, 'bulkDelete'])->name('programmer.bulkDelete');
    Route::delete('/truncate/{table}', [ProgrammerController::class, 'truncateTable'])->name('programmer.truncate');
});
