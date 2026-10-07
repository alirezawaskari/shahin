<?php

use App\Http\Controllers\Admin\SmsAiCampaignController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin'],
    'prefix' => 'dashboard/sms/ai',
    'as' => 'admin.sms.ai.',
], function () {
    Route::get('/', [SmsAiCampaignController::class, 'index'])->name('index');
    Route::post('/propose-audience', [SmsAiCampaignController::class, 'proposeAudience'])->name('proposeAudience');
    Route::post('/approve-audience', [SmsAiCampaignController::class, 'approveAudience'])->name('approveAudience');
    Route::post('/draft-message', [SmsAiCampaignController::class, 'draftMessage'])->name('draftMessage');
    Route::post('/calculate-cost', [SmsAiCampaignController::class, 'calculateCost'])->name('calculateCost');
    Route::post('/confirm-and-launch', [SmsAiCampaignController::class, 'confirmAndLaunch'])->name('confirmAndLaunch');
    Route::get('/campaigns', [SmsAiCampaignController::class, 'campaigns'])->name('campaigns');
    Route::get('/campaigns/{campaign}', [SmsAiCampaignController::class, 'statusView'])->name('campaigns.show');
});
