<?php

use App\Http\Controllers\Admin\SmsDashboardController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin', \App\Http\Middleware\EnsureSmsContractSigned::class],
    'prefix' => 'dashboard/sms',
    'as' => 'admin.sms.dashboard.',
], function () {
    Route::get('/', [SmsDashboardController::class, 'index'])->name('index');
});
