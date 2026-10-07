<?php

use App\Http\Controllers\Admin\SmsBulkSendController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin'],
    'prefix' => 'dashboard/sms/bulk',
    'as' => 'admin.sms.bulk.',
], function () {
    Route::get('/', [SmsBulkSendController::class, 'index'])->name('index');
    Route::post('/preview', [SmsBulkSendController::class, 'preview'])->name('preview');
    Route::post('/send', [SmsBulkSendController::class, 'send'])->name('send');
    Route::get('/campaigns', [SmsBulkSendController::class, 'campaigns'])->name('campaigns');
    Route::get('/campaigns/{campaign}', [SmsBulkSendController::class, 'statusView'])->name('campaigns.show');
    Route::get('/campaigns/{campaign}/status', [SmsBulkSendController::class, 'statusApi'])->name('campaigns.status');
});
