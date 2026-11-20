<?php

use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\DoctorSchedulesController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Todo: create the middlewares

Route::get('/', function () {
    return view('welcome');
});

// Admin doctors CRUD routes
Route::middleware(['auth', 'adminOnly'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('/doctors', DoctorController::class);

        Route::resource('/users', UserController::class)
            ->only('index', 'show', 'edit', 'update');
    });


Route::middleware(['auth', 'doctorOnly'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        Route::singleton('/profile', DoctorProfileController::class);

        Route::get('/schedules', [DoctorSchedulesController::class, 'index'])
            ->name('schedules.index');

        Route::post('/schedules', [DoctorSchedulesController::class, 'store'])
            ->name('schedules.store');

        Route::get('/schedules/{id}/edit', [DoctorSchedulesController::class, 'edit'])
            ->name('schedules.edit');

        Route::put('/schedules/{id}', [DoctorSchedulesController::class, 'update'])
            ->name('schedules.update');

        Route::delete('/schedules/{id}', [DoctorSchedulesController::class, 'destroy'])
            ->name('schedules.destroy');
    });

// Patient route
//? singleton() => generates exactly show, edit, and update
Route::middleware(['auth', 'patientOnly'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function () {
        Route::singleton('/profile', PatientProfileController::class);
    });

// Auth routes
//? match() => combine get and post
Route::middleware('guest')
    ->group(function () {
        //Todo: use $request->isMethod('get')
        Route::match(['get', 'post'], '/login', [AuthController::class, 'login'])
            ->name('login');

        Route::match(['get', 'post'], '/register', [AuthController::class, 'register'])
            ->name('register');

        Route::view('/forgot-password', 'auth.forgot-password')
            ->name('password.request');

        Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
            ->name('password.email');

        Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])
            ->name('password.reset');

        Route::post('/reset-password', [AuthController::class, 'resetPassword'])
            ->name('password.update');
    });

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout.submit');
});
