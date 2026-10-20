<?php

use App\Http\Controllers\Admin\SmsFinancialController;
use App\Http\Controllers\Admin\SmsReportController;
use App\Http\Middleware\EnsureSmsOtpAccess;
use Illuminate\Support\Facades\Route;

// OTP credit and OTP reports: Titanium / superAdmin only (EnsureSmsOtpAccess middleware).
Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin', \App\Http\Middleware\EnsureSmsContractSigned::class],
    'prefix' => 'dashboard/sms',
    'as' => 'admin.sms.',
], function () {

    // Financial Routes
    Route::group(['prefix' => 'financial', 'as' => 'financial.'], function () {
        // OTP Balance
        Route::get('/otp', [SmsFinancialController::class, 'otpView'])->middleware(EnsureSmsOtpAccess::class)->name('otp.index');
        Route::post('/otp/topup', [SmsFinancialController::class, 'otpTopUp'])->middleware(EnsureSmsOtpAccess::class)->name('otp.topup');

        // Advertising Balance
        Route::get('/advertising', [SmsFinancialController::class, 'advertisingView'])->name('advertising.index');
        Route::post('/advertising/topup', [SmsFinancialController::class, 'advertisingTopUp'])->name('advertising.topup');

        // Read-only landing after the payment module handled the bank callback (credits nothing)
        Route::get('/topup/result/{payment}', [SmsFinancialController::class, 'topUpResult'])->name('topup.result');

        // Centralized Balance Guard Check API
        Route::post('/check-balance', [SmsFinancialController::class, 'checkBalance'])->name('check');
    });

    // Report Routes
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        // OTP Reports
        Route::get('/otp', [SmsReportController::class, 'otpReport'])->middleware(EnsureSmsOtpAccess::class)->name('otp.index');
        Route::get('/otp/export', [SmsReportController::class, 'otpReportExport'])->middleware(EnsureSmsOtpAccess::class)->name('otp.export');

        // Advertising Reports (per-sender)
        Route::get('/advertising', [SmsReportController::class, 'advertisingReport'])->name('advertising.index');
        Route::get('/advertising/export', [SmsReportController::class, 'advertisingReportExport'])->name('advertising.export');

        // Super-Admin Cross-Business Reports
        Route::get('/cross-business', [SmsReportController::class, 'superAdminReport'])->name('superadmin.index');
        Route::get('/cross-business/export', [SmsReportController::class, 'superAdminReportExport'])->name('superadmin.export');
    });
});
