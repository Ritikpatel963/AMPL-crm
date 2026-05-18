<?php

use App\Http\Controllers\callingcrm\CampaignController;
use App\Http\Controllers\callingcrm\ContactController;
use App\Http\Controllers\callingcrm\DashboardController;
use App\Http\Controllers\callingcrm\PipelineController;
use App\Http\Controllers\callingcrm\ReportController;
use App\Http\Controllers\callingcrm\TrendController;
use Illuminate\Support\Facades\Route;

Route::prefix('callingcrm')
    ->name('callingcrm.')
    ->middleware(['auth:admin'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
        Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
        Route::get('/contacts/upload', [ContactController::class, 'upload'])->name('contacts.upload');
        Route::post('/contacts/upload', [ContactController::class, 'import'])->name('contacts.import');

        Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
        Route::post('/pipeline', [PipelineController::class, 'store'])->name('pipeline.store');
        Route::get('/pipeline/{campaign}', [PipelineController::class, 'show'])->name('pipeline.show');
        Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
        Route::post('/campaigns/{campaign}/rules', [CampaignController::class, 'storeRule'])->name('campaigns.rules.store');
        Route::delete('/campaigns/{campaign}/rules/{rule}', [CampaignController::class, 'destroyRule'])->name('campaigns.rules.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/trends', [TrendController::class, 'index'])->name('trends.index');
    });
