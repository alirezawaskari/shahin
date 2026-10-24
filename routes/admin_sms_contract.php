<?php

use App\Http\Controllers\Admin\SmsContractSignController;
use App\Http\Controllers\Admin\SmsSettings\SmsContractController as SmsSettingsContractController;
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
    // Re-upload of the files staff rejected, after signing. Only those files can be replaced.
    Route::post('/sign/reupload', [SmsContractSignController::class, 'reupload'])->middleware('throttle:10,1')->name('reupload');
    // Staff review of a client file of a business (super admin only, checked in the controller): approve, or reject with a reason.
    Route::post('/files/{media}/approve', [SmsSettingsContractController::class, 'approveFile'])->name('files.approve');
    Route::post('/files/{media}/reject', [SmsSettingsContractController::class, 'rejectFile'])->name('files.reject');
    // Staff switch for one business's SMS panel (super admin only, checked in the controller).
    Route::post('/panels/{business}/{type}/disable', [SmsSettingsContractController::class, 'disablePanel'])->whereNumber('business')->where('type', 'otp|advertising')->name('panels.disable');
    Route::post('/panels/{business}/{type}/enable', [SmsSettingsContractController::class, 'enablePanel'])->whereNumber('business')->where('type', 'otp|advertising')->name('panels.enable');
});
