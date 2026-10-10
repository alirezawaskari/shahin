<?php

use App\Http\Controllers\Admin\SmsContractSignController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin'],
    'prefix' => 'dashboard/sms/contract',
    'as' => 'admin.sms.contract.',
], function () {
    Route::get('/sign', [SmsContractSignController::class, 'index'])->name('sign');
    Route::post('/sign', [SmsContractSignController::class, 'accept'])->name('accept');
});
