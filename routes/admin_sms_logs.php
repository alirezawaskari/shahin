<?php

use App\Http\Controllers\Admin\SmsLogController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Admin SMS Logs
Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin', \App\Http\Middleware\EnsureSmsContractSigned::class],
    'prefix' => 'dashboard/sms/logs',
    'as' => 'admin.sms.logs.',
], function () {
    Route::get('/', [SmsLogController::class, 'index'])->name('index');
    Route::get('/{smsMessage}', [SmsLogController::class, 'show'])->name('show');
});

// Customer Account Area - SMS Messages
Route::group([
    'middleware' => ['web', 'auth:web'],
    'prefix' => 'profile',
], function () {
    Route::get('/messages', [ProfileController::class, 'messages'])->name('profile.messages');
});
