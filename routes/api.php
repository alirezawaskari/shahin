<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('sms')->middleware(\App\Http\Middleware\VerifySmsWebhookSecret::class)->group(function () {
    Route::match(['get', 'post'], '/webhook', [\App\Http\Controllers\Api\SmsWebhookController::class, 'handle'])->name('api.sms.webhook');
    Route::match(['get', 'post'], '/webhook/delivery', [\App\Http\Controllers\Api\SmsWebhookController::class, 'delivery'])->name('api.sms.webhook.delivery');
    Route::match(['get', 'post'], '/webhook/inbound', [\App\Http\Controllers\Api\SmsWebhookController::class, 'inbound'])->name('api.sms.webhook.inbound');
});

