<?php

use App\Http\Controllers\SmileAiController;
use Illuminate\Support\Facades\Route;

Route::prefix('smile-ai')->name('smile.ai.')->group(function () {
    Route::get('/', [SmileAiController::class, 'create'])->name('create');
    Route::post('/generate', [SmileAiController::class, 'generate'])->middleware(['auth', 'patientOnly', 'verified', 'throttle:3,1'])->name('generate');
    Route::get('/result/{id}', [SmileAiController::class, 'show'])->where('id', '[a-f0-9-]{36}')->middleware(['auth', 'patientOnly', 'verified'])->name('show');
    Route::get('/result/{id}/image/{side}', [SmileAiController::class, 'image'])->where('id', '[a-f0-9-]{36}')->middleware(['auth', 'patientOnly', 'verified'])->name('image');
    Route::get('/result/{id}/download', [SmileAiController::class, 'download'])->where('id', '[a-f0-9-]{36}')->middleware(['auth', 'patientOnly', 'verified'])->name('download');
    Route::get('/gallery', [SmileAiController::class, 'gallery'])->middleware(['auth', 'patientOnly', 'verified'])->name('gallery');
});
