<?php

use App\Http\Controllers\Admin\SmsSingleSendController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin', \App\Http\Middleware\EnsureSmsContractSigned::class],
    'prefix' => 'dashboard/sms/single',
    'as' => 'admin.sms.single.',
], function () {
    Route::get('/', [SmsSingleSendController::class, 'index'])->name('index');
    Route::post('/send', [SmsSingleSendController::class, 'send'])->name('send');
    Route::post('/preview', [SmsSingleSendController::class, 'preview'])->name('preview');
    Route::get('/contacts/search', [SmsSingleSendController::class, 'searchContacts'])->name('contacts.search');
    Route::post('/contacts', [SmsSingleSendController::class, 'storeContact'])->name('contacts.store');
    Route::post('/forward', [SmsSingleSendController::class, 'forward'])->name('forward');
});
