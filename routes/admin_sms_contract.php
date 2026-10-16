<?php

use App\Http\Controllers\Admin\SmsContractSignController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin'],
    'prefix' => 'dashboard/sms/contract',
    'as' => 'admin.sms.contract.',
], function () {
    Route::get('/sign', [SmsContractSignController::class, 'index'])->name('sign');
    // Both are brute-force / SMS-pumping sensitive: the service rate-limits per user, this per client.
    Route::post('/sign/otp', [SmsContractSignController::class, 'sendOtp'])->middleware('throttle:6,1')->name('otp');
    Route::post('/sign', [SmsContractSignController::class, 'accept'])->middleware('throttle:10,1')->name('accept');
});
