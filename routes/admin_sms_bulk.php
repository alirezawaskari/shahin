<?php

use App\Http\Controllers\Admin\SmsBulkSendController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin', \App\Http\Middleware\EnsureSmsContractSigned::class],
    'prefix' => 'dashboard/sms/bulk',
    'as' => 'admin.sms.bulk.',
], function () {
    Route::get('/', [SmsBulkSendController::class, 'index'])->name('index');
    Route::post('/preview', [SmsBulkSendController::class, 'preview'])->name('preview');
    Route::post('/send', [SmsBulkSendController::class, 'send'])->name('send');
    Route::post('/excel-numbers', [SmsBulkSendController::class, 'parseExcel'])->name('excel');
    Route::post('/queue/save', [SmsBulkSendController::class, 'saveToQueue'])->name('queue.save');
    Route::get('/queue', [SmsBulkSendController::class, 'queue'])->name('queue');
    Route::post('/queue/{campaign}/send', [SmsBulkSendController::class, 'sendQueued'])->name('queue.send');
    Route::delete('/queue/{campaign}', [SmsBulkSendController::class, 'destroyQueued'])->name('queue.destroy');
    Route::get('/campaigns', [SmsBulkSendController::class, 'campaigns'])->name('campaigns');
    Route::get('/campaigns/{campaign}', [SmsBulkSendController::class, 'statusView'])->name('campaigns.show');
    Route::get('/campaigns/{campaign}/status', [SmsBulkSendController::class, 'statusApi'])->name('campaigns.status');
});
