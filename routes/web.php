<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\pdfController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankMasukController;
use App\Http\Controllers\daftarSPPController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\saldoAwalController;
use App\Http\Controllers\BankKeluarController;
use App\Http\Controllers\daftarBankController;
use App\Http\Controllers\DetailItemController;
use App\Http\Controllers\daftarRekeningController;
use App\Http\Controllers\DetailSubKategoriController;
use App\Http\Controllers\UserSAPController;

// Route::post('/logout',[AuthController::class, 'logout']);
// Route::get('/login',fn()=> view('auth.login'))->name('login');
// Route::get('/', function () {
//     return redirect()->route('dashboard');
// });
// Route::get('/userVendor',fn()=> view('cash_bank.user.usersVendor'))->name('userVendor');
Route::get('/', fn () => view('auth.login'));
Route::get('/login',fn()=> view('auth.login'))->name('login');
Route::post('/login',[AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');
Route::group(['middleware' => ['auth','check_role:admin']], function(){
    Route::get('/dashboard-cash-bank', [dashboardController::class, 'index'])
        ->name('dashboard');
});
Route::group(['middleware' => ['auth','check_role:vendor']], function(){
    Route::get('/userVendor', [UserSAPController::class, 'index'])
    ->name('userVendor.index');
});


// menu
Route::get('/dashboard-cash-bank', [dashboardController::class, 'index'])
->name('dashboard')->middleware('auth');
Route::get('/daftar-spp', [daftarSPPController::class, 'index'])->name('daftar-spp.index');
Route::get('/user-sap/export_excel', [UserSAPController::class, 'export_excel']);
Route::get('/user-sap/view/pdf', [UserSAPController::class,'view_pdf']);
Route::put('/user-sap/{id}', [UserSAPController::class, 'update']);
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
    Route::get('/bank-keluar/data', [BankKeluarController::class, 'data'])
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

    Route::resource('daftarBank', DaftarBankController::class);

    Route::resource('saldoAwal', SaldoAwalController::class);

});
Route::get('/penerima',fn()=> view('cash_bank.pembayaran.penerima'))->name('penerima');
Route::get('/dropping',fn()=> view('cash_bank.pembayaran.dropping'))->name('dropping');


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