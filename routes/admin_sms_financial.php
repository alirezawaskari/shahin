<?php

use App\Http\Controllers\Admin\SmsFinancialController;
use App\Http\Controllers\Admin\SmsReportController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin', \App\Http\Middleware\EnsureSmsContractSigned::class],
    'prefix' => 'dashboard/sms',
    'as' => 'admin.sms.',
], function () {

    // Financial Routes
    Route::group(['prefix' => 'financial', 'as' => 'financial.'], function () {
        // OTP Balance
        Route::get('/otp', [SmsFinancialController::class, 'otpView'])->name('otp.index');
        Route::post('/otp/topup', [SmsFinancialController::class, 'otpTopUp'])->name('otp.topup');

        // Advertising Balance
        Route::get('/advertising/{senderNumber?}', [SmsFinancialController::class, 'advertisingView'])->name('advertising.index');
        Route::post('/advertising/topup', [SmsFinancialController::class, 'advertisingTopUp'])->name('advertising.topup');

        // Centralized Balance Guard Check API
        Route::post('/check-balance', [SmsFinancialController::class, 'checkBalance'])->name('check');
    });

    // Report Routes
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        // OTP Reports
        Route::get('/otp', [SmsReportController::class, 'otpReport'])->name('otp.index');
        Route::get('/otp/export', [SmsReportController::class, 'otpReportExport'])->name('otp.export');

        // Advertising Reports (per-sender)
        Route::get('/advertising', [SmsReportController::class, 'advertisingReport'])->name('advertising.index');
        Route::get('/advertising/export', [SmsReportController::class, 'advertisingReportExport'])->name('advertising.export');

        // Super-Admin Cross-Business Reports
        Route::get('/cross-business', [SmsReportController::class, 'superAdminReport'])->name('superadmin.index');
        Route::get('/cross-business/export', [SmsReportController::class, 'superAdminReportExport'])->name('superadmin.export');
    });
});
