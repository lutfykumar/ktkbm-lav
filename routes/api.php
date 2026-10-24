<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\MasterController;
use App\Http\Controllers\Api\V1\NotificationsController;
use App\Http\Controllers\Api\V1\SyncController;
use App\Http\Controllers\Api\V1\WorkordersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public Auth
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgot_password']);

    // Protected Routes (auth.jwt)
    Route::middleware('auth.jwt')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/change-password', [AuthController::class, 'change_password']);
        Route::post('auth/fcm-token', [AuthController::class, 'fcm_token']);
        Route::get('auth/profile', [AuthController::class, 'profile']);
        Route::post('auth/update-profile', [AuthController::class, 'update_profile']);

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Work Orders
        Route::get('workorders', [WorkordersController::class, 'index']);
        Route::post('workorders', [WorkordersController::class, 'create']);
        Route::post('workorders/create', [WorkordersController::class, 'create']);
        Route::get('workorders/{id}', [WorkordersController::class, 'detail']);
        Route::match(['PUT', 'POST', 'PATCH'], 'workorders/{id}/update', [WorkordersController::class, 'update']);
        Route::match(['PUT', 'POST'], 'workorders/{id}/status', [WorkordersController::class, 'update_status']);
        Route::post('workorders/{id}/submit-review', [WorkordersController::class, 'submit_review']);
        Route::match(['GET', 'POST'], 'workorders/{id}/reports', [WorkordersController::class, 'reports']);

        // Reports
        Route::match(['DELETE', 'POST'], 'reports/{id}', [WorkordersController::class, 'delete_report']);
        Route::match(['POST', 'PUT', 'PATCH'], 'reports/{id}/update', [WorkordersController::class, 'update_report']);
        Route::post('reports/{id}/photos', [WorkordersController::class, 'upload_photos']);
        Route::match(['POST', 'PUT', 'PATCH'], 'workorders/{id}/reports/{report_id}/update', [WorkordersController::class, 'update_report']);

        // Master Data
        Route::prefix('master')->group(function () {
            // Pelabuhan
            Route::get('pelabuhan', [MasterController::class, 'pelabuhan']);
            Route::post('pelabuhan/create', [MasterController::class, 'create_pelabuhan']);
            Route::match(['POST', 'PUT'], 'pelabuhan/update', [MasterController::class, 'update_pelabuhan']);

            // KRK
            Route::get('krk', [MasterController::class, 'krk']);
            Route::post('krk/create', [MasterController::class, 'create_krk']);
            Route::post('create_krk', [MasterController::class, 'create_krk']);
            Route::match(['POST', 'PUT'], 'krk/update', [MasterController::class, 'update_krk']);

            // Satuan
            Route::get('satuan', [MasterController::class, 'satuan']);
            Route::post('satuan/create', [MasterController::class, 'create_satuan']);
            Route::match(['POST', 'PUT'], 'satuan/update', [MasterController::class, 'update_satuan']);

            // Catatan
            Route::get('catatan', [MasterController::class, 'catatan']);
            Route::post('catatan/create', [MasterController::class, 'create_catatan']);
            Route::match(['POST', 'PUT'], 'catatan/update', [MasterController::class, 'update_catatan']);

            // Kapal
            Route::get('kapal', [MasterController::class, 'kapal']);
            Route::post('kapal/create', [MasterController::class, 'create_kapal']);
            Route::match(['POST', 'PUT'], 'kapal/update', [MasterController::class, 'update_kapal']);

            // Client
            Route::get('client', [MasterController::class, 'client']);
            Route::post('client/create', [MasterController::class, 'create_client']);
            Route::match(['POST', 'PUT'], 'client/update', [MasterController::class, 'update_client']);

            // Dermaga
            Route::get('dermaga', [MasterController::class, 'dermaga']);
            Route::post('dermaga/create', [MasterController::class, 'create_dermaga']);
            Route::match(['POST', 'PUT'], 'dermaga/update', [MasterController::class, 'update_dermaga']);

            // Alat
            Route::get('alat', [MasterController::class, 'alat']);
            Route::post('alat/create', [MasterController::class, 'create_alat']);
            Route::match(['POST', 'PUT'], 'alat/update', [MasterController::class, 'update_alat']);

            // Aktifitas
            Route::get('aktifitas', [MasterController::class, 'aktifitas']);
            Route::post('aktifitas/create', [MasterController::class, 'create_aktifitas']);
            Route::match(['POST', 'PUT'], 'aktifitas/update', [MasterController::class, 'update_aktifitas']);
        });

        // Notifications
        Route::get('notifications', [NotificationsController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationsController::class, 'unread_count']);
        Route::post('notifications/{id}/read', [NotificationsController::class, 'mark_read']);
        Route::post('notifications/read-all', [NotificationsController::class, 'mark_all_read']);

        // Sync
        Route::get('sync/pull', [SyncController::class, 'pull']);
        Route::post('sync/push', [SyncController::class, 'push']);
    });
});
