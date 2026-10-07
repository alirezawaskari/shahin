<?php

use App\Http\Controllers\Admin\SmsSettings\SmsContractController;
use App\Http\Controllers\Admin\SmsSettings\SmsMessageGroupController;
use App\Http\Controllers\Admin\SmsSettings\SmsMessageTemplateController;
use App\Http\Controllers\Admin\SmsSettings\SmsPanelController;
use App\Http\Controllers\Admin\SmsSettings\SmsSenderNumberController;
use App\Http\Controllers\Admin\SmsSettings\SmsSettingsController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'auth:web', 'checkActive', 'checkAdmin'],
    'prefix' => 'dashboard/sms/settings',
    'as' => 'admin.sms.settings.',
], function () {
    // Hub / Overview
    Route::get('/', [SmsSettingsController::class, 'index'])->name('index');

    // 1. Panels
    Route::group(['prefix' => 'panels', 'as' => 'panels.'], function () {
        Route::get('/', [SmsPanelController::class, 'index'])->name('index');
        Route::get('/create', [SmsPanelController::class, 'create'])->name('create');
        Route::post('/', [SmsPanelController::class, 'store'])->name('store');
        Route::get('/{panel}/edit', [SmsPanelController::class, 'edit'])->name('edit');
        Route::put('/{panel}', [SmsPanelController::class, 'update'])->name('update');
        Route::delete('/{panel}', [SmsPanelController::class, 'destroy'])->name('destroy');
        Route::post('/{panel}/toggle-status', [SmsPanelController::class, 'toggleStatus'])->name('toggle-status');
    });

    // 2. Sender Numbers
    Route::group(['prefix' => 'senders', 'as' => 'senders.'], function () {
        Route::get('/', [SmsSenderNumberController::class, 'index'])->name('index');
        Route::get('/create', [SmsSenderNumberController::class, 'create'])->name('create');
        Route::post('/', [SmsSenderNumberController::class, 'store'])->name('store');
        Route::get('/{senderNumber}/edit', [SmsSenderNumberController::class, 'edit'])->name('edit');
        Route::put('/{senderNumber}', [SmsSenderNumberController::class, 'update'])->name('update');
        Route::delete('/{senderNumber}', [SmsSenderNumberController::class, 'destroy'])->name('destroy');
        Route::post('/{senderNumber}/toggle-status', [SmsSenderNumberController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{senderNumber}/assign-businesses', [SmsSenderNumberController::class, 'assignBusinesses'])->name('assign-businesses');
    });

    // 3. Templates
    Route::group(['prefix' => 'templates', 'as' => 'templates.'], function () {
        Route::get('/', [SmsMessageTemplateController::class, 'index'])->name('index');
        Route::get('/create', [SmsMessageTemplateController::class, 'create'])->name('create');
        Route::post('/', [SmsMessageTemplateController::class, 'store'])->name('store');
        Route::get('/{template}/edit', [SmsMessageTemplateController::class, 'edit'])->name('edit');
        Route::put('/{template}', [SmsMessageTemplateController::class, 'update'])->name('update');
        Route::delete('/{template}', [SmsMessageTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/preview', [SmsMessageTemplateController::class, 'preview'])->name('preview');
        Route::post('/{template}/toggle-status', [SmsMessageTemplateController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{template}/history', [SmsMessageTemplateController::class, 'history'])->name('history');
    });

    // 4. Message Groups
    Route::group(['prefix' => 'groups', 'as' => 'groups.'], function () {
        Route::get('/', [SmsMessageGroupController::class, 'index'])->name('index');
        Route::get('/create', [SmsMessageGroupController::class, 'create'])->name('create');
        Route::post('/', [SmsMessageGroupController::class, 'store'])->name('store');
        Route::get('/{group}/edit', [SmsMessageGroupController::class, 'edit'])->name('edit');
        Route::put('/{group}', [SmsMessageGroupController::class, 'update'])->name('update');
        Route::delete('/{group}', [SmsMessageGroupController::class, 'destroy'])->name('destroy');
    });

    // 5. Contracts
    Route::group(['prefix' => 'contracts', 'as' => 'contracts.'], function () {
        Route::get('/', [SmsContractController::class, 'index'])->name('index');
        Route::post('/', [SmsContractController::class, 'store'])->name('store');
        Route::put('/{contractUsage}', [SmsContractController::class, 'update'])->name('update');
        Route::delete('/{contractUsage}', [SmsContractController::class, 'destroy'])->name('destroy');
    });
});
