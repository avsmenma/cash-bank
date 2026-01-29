<?php


use App\Models\ItemSubKriteria;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
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

// Route::post('/logout',[AuthController::class, 'logout']);
// Route::get('/login',fn()=> view('auth.login'))->name('login');
// Route::get('/', function () {
//     return redirect()->route('dashboard');
// });
// Route::get('/userVendor',fn()=> view('cash_bank.user.usersVendor'))->name('userVendor');
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

});
// Route::group(['middleware' => ['auth','check_role:admin']], function(){
//     Route::get('/dashboard-pembayaran', [DashboardPembayaranController::class, 'index'])
//         ->name('dashboard-pembayaran');
// });
Route::group(['middleware' => ['auth', 'check_role:vendor']], function () {
    Route::get('/userVendor', [UserSAPController::class, 'index'])
        ->name('userVendor.index');
});


// menu - protected routes

Route::middleware(['auth'])->group(function () {
    Route::get('/daftar-spp', [daftarSPPController::class, 'index'])->name('daftar-spp.index');
    Route::get('/daftar-spp/data', [daftarSPPController::class, 'datatable'])->name('daftar-spp.data');
    Route::get('/user-sap/export_excel', [UserSAPController::class, 'export_excel']);
    Route::get('/user-sap/view/pdf', [UserSAPController::class, 'view_pdf']);
    Route::put('/user-sap/{id}', [UserSAPController::class, 'update']);
});
// Route::get('/reportMasuk', function () {
//     return view('cash_bank.reportMasuk');
// })->name('report-masuk');
// Route::get('/reportKeluar', function () {
//     return view('cash_bank.reportKeluar');
// })->name('report-keluar');
// Route::get('/login', function () {
//     return view('layouts.login');
// })->name('login');
// Route::get('/logout', function () {
//     Session::flush();
//     return redirect()->route('login');
// })->name('logout');


// // BANK KELUAR
// Route::get('/bank-keluar/report', [BankKeluarController::class, 'report'])
// ->name('bank-keluar.report');
// Route::post('/bank-keluar/importExcel', [BankKeluarController::class, 'importExcel'])
// ->name('bank-keluar.importExcel');
// Route::get('/bank-keluar/export_excel', [BankKeluarController::class, 'export_excel']);
// Route::get('/bank-keluar/report_export_excel', [BankKeluarController::class, 'report_export_excel'])->name('bank-keluar.report_export_excel');
// Route::get('/bank-keluar/reportKeluarPdf', [BankKeluarController::class, 'reportKeluarPdf'])->name('bank-keluar.reportKeluarPdf');
// Route::get('/bank-keluar/view/pdf', [BankKeluarController::class,'view_pdf']);
// Route::get('/detail-transaksi', [BankKeluarController::class, 'getDetailTransaksi'])
// ->name('bank-keluar.detail-transaksi');
// Route::get('/export-detail', [BankKeluarController::class, 'exportDetailTransaksi'])
// ->name('bank-keluar.export-detail');
// Route::get('/bank-keluar/ajax', [BankKeluarController::class, 'ajax'])->name('bank-keluar.ajax');
// Route::get('/get-kategori-kriteria/{id}', [BankKeluarController::class, 'getKriteria']);
// Route::get('/bank-masuk/ajax', [BankMasukController::class, 'ajax'])->name('bank-masuk.ajax');
// Route::get('/get-sub-kriteria/{id}', [BankKeluarController::class, 'getSub']);
// Route::get('/get-item-sub-kriteria/{id}', [BankKeluarController::class, 'getItem']);
// Route::get('/get-dokumen-detail/{id}', [BankKeluarController::class, 'getDokumenDetail']);
// Route::delete('/selected-employee', [BankKeluarController::class, 'deleteAll'])
//     ->name('bank-keluar.delete');


Route::middleware(['auth'])->group(function () {

    Route::prefix('bank-keluar')->name('bank-keluar.')->group(function () {

        Route::get('/report', [BankKeluarController::class, 'report'])
            ->name('report');

        Route::post('/importExcel', [BankKeluarController::class, 'importExcel'])
            ->name('importExcel');

        Route::get('/export_excel', [BankKeluarController::class, 'export_excel'])
            ->name('export_excel');

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


// BANK MASUK
// Route::get('/bank-masuk/report', [BankMasukController::class, 'report'])
// ->name('bank-masuk.report');
// Route::post('/bank-masuk/importExcel', [BankMasukController::class, 'importExcel'])
// ->name('bank-masuk.importExcel');
// Route::get('/bank-masuk/export_excel', [BankMasukController::class, 'export_excel']);
// Route::get('/bank-masuk/report_export_excel', [BankMasukController::class, 'report_export_excel'])->name('bank-masuk.report_export_excel');
// Route::get('/bank-masuk/reportMasukPdf', [BankMasukController::class, 'reportMasukPdf'])->name('bank-masuk.reportMasukPdf');
// Route::get('/bank-masuk/view/pdf', [BankMasukController::class,'view_pdf']);
// Route::resource('bank-masuk', BankMasukController::class);
// Route::get('/sub-kriteria/{id}', [BankMasukController::class, 'getSubKriteria']);
// Route::get('/item-sub-kriteria/{id}', [BankMasukController::class, 'getItemSubKriteria']);
// Route::delete('/selected-employee',[BankMasukController::class,'deleteAll'])->name('bank-masuk.delete');


Route::middleware(['auth'])->group(function () {
    Route::prefix('bank-masuk')->name('bank-masuk.')->group(function () {

        Route::get('/report', [BankMasukController::class, 'report'])
            ->name('report');

        Route::post('/importExcel', [BankMasukController::class, 'importExcel'])
            ->name('importExcel');

        Route::get('/export_excel', [BankMasukController::class, 'export_excel']);

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

        // Route::get('/export', [DetailItemController::class, 'export'])->name('export');
    });
    Route::prefix('detail-sub')->name('detail-sub.')->group(function () {

        Route::get('/', [DetailSubKategoriController::class, 'index'])
            ->name('index');

        Route::get('/export_excel', [DetailSubKategoriController::class, 'export_excel'])
            ->name('export_excel');

        Route::get('/view_pdf', [DetailSubKategoriController::class, 'view_pdf'])
            ->name('view_pdf');

        // Route::get('/export', [DetailSubKategoriController::class, 'export'])->name('export');
    });

});
Route::middleware(['auth'])->group(function () {
    Route::resource('daftarRekening', DaftarRekeningController::class);
    Route::get('/daftarBank/data', [DaftarBankController::class, 'datatable'])->name('daftarBank.data');
    Route::resource('daftarBank', DaftarBankController::class);

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

    Route::get('/penerima/data', [PenerimaController::class, 'datatable'])
        ->name('penerima.data');

    Route::get('/penerima/export_excel', [PenerimaController::class, 'export_excel'])
        ->name('penerima.export_excel');

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
    Route::delete('/permintaan/delete', [PermintaanController::class, 'deleteData'])->name('permintaan.delete');
    Route::get('/permintaan/sub-kriteria/{id}', [PermintaanController::class, 'getSub']);
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
    Route::post('/dropping/saveRencana', [DroppingController::class, 'saveRencana'])
        ->name('dropping.rencana.saveRencana');
    Route::delete('/dropping/delete', [DroppingController::class, 'deleteData'])->name('dropping.delete');
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
// Route::get('/dashboard-detail-pd',fn()=> view('cash_bank.detailPd_detailPvD.detailPvd'))->name('dashboard-detail-pd');
// Route::get('/penerima',fn()=> view('cash_bank.pembayaran.penerima'))->name('penerima');

Route::middleware(['auth'])->group(function () {
    Route::get('/cashflow', [DetaiControllerCashFlowController::class, 'index'])->name('cashflow.index');
    Route::get('/cashflow/detail', [DetaiControllerCashFlowController::class, 'detail'])->name('cashflow.detail');
});
// DAFTAR BANK
// Route::resource('daftarRekening', daftarRekeningController::class);
// Route::resource('daftarBank', daftarBankController::class);
// Route::resource('saldoAwal', saldoAwalController::class);
// Route::get('/get-nomor_rekening/{id}', [daftarRekeningController::class, 'getRekeningByBank']);

// // DETAILL
// Route::prefix('detail-item')->name('detailItem.')->group(function () {
//     Route::get('/', [DetailItemController::class, 'index'])->name('index');
//     // Route::get('/export', [DetailItemController::class, 'export'])->name('export');
// });
// Route::prefix('detail-sub')->name('detailSub.')->group(function () {
//     Route::get('/', [DetailSubKategoriController::class, 'index'])->name('index');
//     // Route::get('/export', [DetailSubKategoriController::class, 'export'])->name('export');
// });
// Route::get('/detail-item/export_excel', [DetailItemController::class, 'export_excel'])->name('detail-item.export_excel');
// Route::get('/detail-item/view_pdf', [DetailItemController::class, 'view_pdf'])->name('detail-item.view_pdf');
// Route::get('/detail-sub/export_excel', [DetailSubKategoriController::class, 'export_excel'])->name('detail-sub.export_excel');
// Route::get('/detail-sub/view_pdf', [DetailSubKategoriController::class, 'view_pdf'])->name('detail-sub.view_pdf');

// EXPORT PDF
// Route::get('/Export-pdf',[pdfController::class, 'exportPdf']);


// Route::prefix('detail-kategori')->name('detailKategori.')->group(function () {
//     Route::get('/', [DetailSubKategoriController::class, 'index'])->name('index');
//     Route::get('/export', [DetailSubKategoriController::class, 'export'])->name('export');
// });



// Route::get('/userSAP',fn()=> view('userSAP.userSAP'))->name('userSAP');