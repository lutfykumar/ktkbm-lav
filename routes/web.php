<?php

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PanduanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root route
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/panduan', [PanduanController::class, 'index'])->name('panduan');

    // AJAX helpers
    Route::post('/ajax/search_menu', [AjaxController::class, 'searchMenu'])->name('ajax.search_menu');
    Route::post('/ajax/search-menu', [AjaxController::class, 'searchMenu']);
    Route::post('/ajax/get_all_roles', [AjaxController::class, 'getAllRoles']);
    Route::post('/ajax/get_all_pelabuhan', [AjaxController::class, 'getAllPelabuhan']);
    Route::post('/ajax/get_all_kapal', [AjaxController::class, 'getAllKapal']);
    Route::post('/ajax/get_all_alat', [AjaxController::class, 'getAllAlat']);
    Route::post('/ajax/get_all_aktifitas', [AjaxController::class, 'getAllAktifitas']);
    Route::post('/ajax/get_all_dermaga', [AjaxController::class, 'getAllDermaga']);
    Route::post('/ajax/get_all_jenis_satuan', [AjaxController::class, 'getAllJenisSatuan']);
    Route::post('/ajax/get_all_client', [AjaxController::class, 'getAllClient']);
    Route::post('/ajax/get_all_users_by_role', [AjaxController::class, 'getAllUsersByRole']);
    Route::post('/ajax/get_all_header', [AjaxController::class, 'getAllHeader']);
    Route::post('/ajax/get_all_parent', [AjaxController::class, 'getAllParent']);
    Route::post('/ajax/get_all_krk', [AjaxController::class, 'getAllKrk']);
    Route::post('/ajax/get_all_catatan', [AjaxController::class, 'getAllCatatan']);
    Route::post('/ajax/store_krk', [AjaxController::class, 'storeKrk']);
    Route::post('/ajax/get_all_workorder_done', [AjaxController::class, 'getAllWorkorderDone']);

    // Master Data Routes
    $masterModules = [
        'aktifitas' => \App\Http\Controllers\Master\AktifitasController::class,
        'alat' => \App\Http\Controllers\Master\AlatController::class,
        'catatan' => \App\Http\Controllers\Master\CatatanController::class,
        'client' => \App\Http\Controllers\Master\ClientController::class,
        'dermaga' => \App\Http\Controllers\Master\DermagaController::class,
        'kapal' => \App\Http\Controllers\Master\KapalController::class,
        'krk' => \App\Http\Controllers\Master\KrkController::class,
        'pelabuhan' => \App\Http\Controllers\Master\PelabuhanController::class,
        'satuan' => \App\Http\Controllers\Master\SatuanController::class,
    ];

    foreach ($masterModules as $prefix => $controller) {
        Route::prefix('master/' . $prefix)->name('master.' . $prefix . '.')->group(function () use ($controller) {
            Route::get('/', [$controller, 'index'])->name('index');
            Route::get('/table', [$controller, 'table'])->name('table');
            Route::post('/store', [$controller, 'store'])->name('store');
            Route::post('/edit', [$controller, 'edit'])->name('edit');
            Route::post('/update', [$controller, 'update'])->name('update');
            Route::post('/delete', [$controller, 'delete'])->name('delete');
            Route::post('/restore', [$controller, 'restore'])->name('restore');
        });
    }

    // Workorder Routes
    Route::prefix('workorder')->name('workorder.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WorkorderController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/table', [\App\Http\Controllers\WorkorderController::class, 'table'])->name('table');
        Route::post('/get_no_work_order', [\App\Http\Controllers\WorkorderController::class, 'getNoWorkOrder'])->name('get_no_work_order');
        Route::post('/store', [\App\Http\Controllers\WorkorderController::class, 'store'])->name('store');
        Route::post('/detail', [\App\Http\Controllers\WorkorderController::class, 'detail'])->name('detail');
        Route::post('/edit', [\App\Http\Controllers\WorkorderController::class, 'edit'])->name('edit');
        Route::post('/update', [\App\Http\Controllers\WorkorderController::class, 'update'])->name('update');
        Route::post('/update_status', [\App\Http\Controllers\WorkorderController::class, 'updateStatus'])->name('update_status');
        Route::post('/delete', [\App\Http\Controllers\WorkorderController::class, 'delete'])->name('delete');
        Route::post('/restore', [\App\Http\Controllers\WorkorderController::class, 'restore'])->name('restore');
    });

    // Report Routes
    Route::prefix('report/workorder')->name('report.workorder.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Report\WorkorderReportController::class, 'index'])->name('index');
        Route::post('/get_report_data', [\App\Http\Controllers\Report\WorkorderReportController::class, 'getReportData'])->name('get_report_data');
        Route::post('/get_kegiatan_kapal', [\App\Http\Controllers\Report\WorkorderReportController::class, 'getKegiatanKapal'])->name('get_kegiatan_kapal');
    });

    // Accounting Invoice Routes
    Route::prefix('accounting/invoice')->name('accounting.invoice.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Accounting\InvoiceController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/table', [\App\Http\Controllers\Accounting\InvoiceController::class, 'table'])->name('table');
        Route::get('/create/{wo_id?}', [\App\Http\Controllers\Accounting\InvoiceController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\Accounting\InvoiceController::class, 'store'])->name('store');
        Route::get('/detail/{id}', [\App\Http\Controllers\Accounting\InvoiceController::class, 'detail'])->name('detail');
        Route::get('/edit/{id}', [\App\Http\Controllers\Accounting\InvoiceController::class, 'edit'])->name('edit');
        Route::post('/update', [\App\Http\Controllers\Accounting\InvoiceController::class, 'update'])->name('update');
        Route::post('/delete', [\App\Http\Controllers\Accounting\InvoiceController::class, 'delete'])->name('delete');
        Route::post('/restore', [\App\Http\Controllers\Accounting\InvoiceController::class, 'restore'])->name('restore');
        Route::post('/update_payment', [\App\Http\Controllers\Accounting\InvoiceController::class, 'updatePayment'])->name('update_payment');
        Route::post('/cancel', [\App\Http\Controllers\Accounting\InvoiceController::class, 'cancel'])->name('cancel');
        Route::get('/print_invoice/{id}', [\App\Http\Controllers\Accounting\InvoiceController::class, 'printInvoice'])->name('print_invoice');
    });

    // Pengaturan: Roles
    Route::prefix('pengaturan/roles')->name('pengaturan.roles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Pengaturan\RolesController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/table', [\App\Http\Controllers\Pengaturan\RolesController::class, 'table'])->name('table');
        Route::post('/store', [\App\Http\Controllers\Pengaturan\RolesController::class, 'store'])->name('store');
        Route::post('/edit', [\App\Http\Controllers\Pengaturan\RolesController::class, 'edit'])->name('edit');
        Route::post('/update', [\App\Http\Controllers\Pengaturan\RolesController::class, 'update'])->name('update');
        Route::post('/delete', [\App\Http\Controllers\Pengaturan\RolesController::class, 'delete'])->name('delete');
    });

    // Pengaturan: Pengguna
    Route::prefix('pengaturan/pengguna')->name('pengaturan.pengguna.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Pengaturan\PenggunaController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/table', [\App\Http\Controllers\Pengaturan\PenggunaController::class, 'table'])->name('table');
        Route::post('/store', [\App\Http\Controllers\Pengaturan\PenggunaController::class, 'store'])->name('store');
        Route::post('/edit', [\App\Http\Controllers\Pengaturan\PenggunaController::class, 'edit'])->name('edit');
        Route::post('/update', [\App\Http\Controllers\Pengaturan\PenggunaController::class, 'update'])->name('update');
        Route::post('/change_password', [\App\Http\Controllers\Pengaturan\PenggunaController::class, 'changePassword'])->name('change_password');
        Route::post('/delete', [\App\Http\Controllers\Pengaturan\PenggunaController::class, 'delete'])->name('delete');
        Route::post('/restore', [\App\Http\Controllers\Pengaturan\PenggunaController::class, 'restore'])->name('restore');
    });

    // Pengaturan: Menu
    Route::prefix('pengaturan/menu')->name('pengaturan.menu.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Pengaturan\MenuController::class, 'index'])->name('index');
        Route::post('/update', [\App\Http\Controllers\Pengaturan\MenuController::class, 'update'])->name('update');
    });

    // Pengaturan: Akses
    Route::prefix('pengaturan/akses')->name('pengaturan.akses.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Pengaturan\AksesController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/table', [\App\Http\Controllers\Pengaturan\AksesController::class, 'table'])->name('table');
        Route::post('/store', [\App\Http\Controllers\Pengaturan\AksesController::class, 'store'])->name('store');
        Route::post('/edit', [\App\Http\Controllers\Pengaturan\AksesController::class, 'edit'])->name('edit');
        Route::post('/update', [\App\Http\Controllers\Pengaturan\AksesController::class, 'update'])->name('update');
        Route::post('/delete', [\App\Http\Controllers\Pengaturan\AksesController::class, 'delete'])->name('delete');
    });

    // Pengaturan: Aplikasi
    Route::prefix('pengaturan/aplikasi')->name('pengaturan.aplikasi.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Pengaturan\AplikasiController::class, 'index'])->name('index');
        Route::get('/get_settings', [\App\Http\Controllers\Pengaturan\AplikasiController::class, 'getSettings'])->name('get_settings');
        Route::post('/update_settings', [\App\Http\Controllers\Pengaturan\AplikasiController::class, 'updateSettings'])->name('update_settings');
        Route::post('/reset_setting', [\App\Http\Controllers\Pengaturan\AplikasiController::class, 'resetSetting'])->name('reset_setting');
        Route::post('/upload_logo', [\App\Http\Controllers\Pengaturan\AplikasiController::class, 'uploadLogo'])->name('upload_logo');
        Route::get('/export_settings', [\App\Http\Controllers\Pengaturan\AplikasiController::class, 'exportSettings'])->name('export_settings');
        Route::post('/import_settings', [\App\Http\Controllers\Pengaturan\AplikasiController::class, 'importSettings'])->name('import_settings');
    });

    // Pengaturan: Cron
    Route::prefix('pengaturan/cron')->name('pengaturan.cron.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Pengaturan\CronController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/get_data', [\App\Http\Controllers\Pengaturan\CronController::class, 'getData'])->name('get_data');
        Route::get('/statistics', [\App\Http\Controllers\Pengaturan\CronController::class, 'statistics'])->name('statistics');
        Route::post('/detail', [\App\Http\Controllers\Pengaturan\CronController::class, 'detail'])->name('detail');
        Route::post('/toggle_status', [\App\Http\Controllers\Pengaturan\CronController::class, 'toggleStatus'])->name('toggle_status');
        Route::post('/execute', [\App\Http\Controllers\Pengaturan\CronController::class, 'execute'])->name('execute');
        Route::post('/execution_logs', [\App\Http\Controllers\Pengaturan\CronController::class, 'executionLogs'])->name('execution_logs');
    });

    // Master Email Templates
    Route::prefix('master/email')->name('master.email.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Master\EmailController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/table', [\App\Http\Controllers\Master\EmailController::class, 'table'])->name('table');
        Route::get('/create', [\App\Http\Controllers\Master\EmailController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\Master\EmailController::class, 'store'])->name('store');
        Route::get('/edit', [\App\Http\Controllers\Master\EmailController::class, 'edit'])->name('edit');
        Route::post('/update', [\App\Http\Controllers\Master\EmailController::class, 'update'])->name('update');
        Route::post('/delete', [\App\Http\Controllers\Master\EmailController::class, 'delete'])->name('delete');
        Route::get('/preview', [\App\Http\Controllers\Master\EmailController::class, 'preview'])->name('preview');
        Route::post('/duplicate', [\App\Http\Controllers\Master\EmailController::class, 'duplicate'])->name('duplicate');
    });

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::post('/store_info', [ProfileController::class, 'store_info'])->name('store_info');
        Route::post('/store_password', [ProfileController::class, 'store_password'])->name('store_password');
        Route::get('/detail/{id}', [ProfileController::class, 'detail'])->name('detail');
    });

    // Tasks: Aktifitas Lapangan
    Route::prefix('activity')->name('activity.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ActivityController::class, 'index'])->name('index');
        Route::post('/result', [\App\Http\Controllers\ActivityController::class, 'result'])->name('result');
        Route::get('/form_detail/{id}', [\App\Http\Controllers\ActivityController::class, 'form_detail'])->name('form_detail');
        Route::get('/list_detail/{id}', [\App\Http\Controllers\ActivityController::class, 'list_detail'])->name('list_detail');
        Route::post('/store_detail', [\App\Http\Controllers\ActivityController::class, 'store_detail'])->name('store_detail');
        Route::post('/update_status', [\App\Http\Controllers\ActivityController::class, 'update_status'])->name('update_status');
        Route::post('/delete_detail', [\App\Http\Controllers\ActivityController::class, 'delete_detail'])->name('delete_detail');
    });

    // Logs: User Activity
    Route::prefix('logs/aktifitas')->name('logs.aktifitas.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Logs\AktifitasController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/table', [\App\Http\Controllers\Logs\AktifitasController::class, 'table'])->name('table');
        Route::post('/detail', [\App\Http\Controllers\Logs\AktifitasController::class, 'detail'])->name('detail');
    });

    // Logs: API Request
    Route::prefix('logs/api')->name('logs.api.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Logs\ApiController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/get_data', [\App\Http\Controllers\Logs\ApiController::class, 'getData'])->name('get_data');
        Route::post('/detail', [\App\Http\Controllers\Logs\ApiController::class, 'detail'])->name('detail');
    });

    // Logs: Email
    Route::prefix('logs/email')->name('logs.email.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Logs\EmailController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/table', [\App\Http\Controllers\Logs\EmailController::class, 'table'])->name('table');
        Route::post('/detail', [\App\Http\Controllers\Logs\EmailController::class, 'detail'])->name('detail');
        Route::post('/resend', [\App\Http\Controllers\Logs\EmailController::class, 'resend'])->name('resend');
    });

    // Logs: Errors
    Route::prefix('logs/errors')->name('logs.errors.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Logs\ErrorsController::class, 'index'])->name('index');
        Route::match(['get', 'post'], '/get_data', [\App\Http\Controllers\Logs\ErrorsController::class, 'getData'])->name('get_data');
        Route::post('/detail', [\App\Http\Controllers\Logs\ErrorsController::class, 'detail'])->name('detail');
        Route::post('/resolve', [\App\Http\Controllers\Logs\ErrorsController::class, 'resolve'])->name('resolve');
    });
});

// Legacy Auth URL Compatibility Aliases
Route::get('auth/login', [AuthenticatedSessionController::class, 'create']);
Route::post('auth/login', [AuthenticatedSessionController::class, 'store']);
Route::match(['get', 'post'], 'auth/logout', [AuthenticatedSessionController::class, 'destroy']);
Route::match(['get', 'post'], 'logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

require __DIR__.'/auth.php';
