<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiImageAnalysesController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/smile-simulation/upload', [AiImageAnalysesController::class, 'store'])
    ->middleware('auth')
    ->name('smile.upload');


Route::get('/smile-simulation', function () {
    return redirect()->route('smile.ai.create');
})->name('smile.create');


Route::get('/', function () {
    return view('home');
})->name('home');


Route::view('/contact', 'contact')->name('contact');
Route::get('/language/{locale}', function (string $locale) {
    if (!in_array($locale, ['ar', 'en'], true)) {
        abort(404);
    }

    session(['locale' => $locale]);

    return redirect()->route('home');
})->name('language.switch');

// MedAssist SDXL simulation routes (Member 5)
require __DIR__.'/smile-ai.routes.php';
