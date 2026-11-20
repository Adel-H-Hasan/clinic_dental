<?php

use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorAppointmentTypesController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\DoctorSchedulesController;
use App\Http\Controllers\MedicalNotesController;
use App\Http\Controllers\MedicalReportsController;
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

        Route::get('/services', [DoctorAppointmentTypesController::class, 'index'])
            ->name('services.index');

        Route::post('/services', [DoctorAppointmentTypesController::class, 'store'])
            ->name('services.store');

        Route::get('/services/{id}/edit', [DoctorAppointmentTypesController::class, 'edit'])
            ->name('services.edit');

        Route::put('/services/{id}', [DoctorAppointmentTypesController::class, 'update'])
            ->name('services.update');

        Route::delete('/services/{id}', [DoctorAppointmentTypesController::class, 'destroy'])
            ->name('services.destroy');

        Route::get('/appointments/{id}/medical-notes', [MedicalNotesController::class, 'index'])
            ->name('appointments.medical-notes.index');

        Route::post('/appointments/{id}/medical-notes', [MedicalNotesController::class, 'store'])
            ->name('appointments.medical-notes.store');

        Route::get('/appointments/{id}/medical-notes/{noteId}/edit', [MedicalNotesController::class, 'edit'])
            ->name('appointments.medical-notes.edit');

        Route::put('/appointments/{id}/medical-notes/{noteId}', [MedicalNotesController::class, 'update'])
            ->name('appointments.medical-notes.update');

        Route::post('/appointments/{id}/medical-notes/{noteId}/review', [MedicalNotesController::class, 'review'])
            ->name('appointments.medical-notes.review');

        Route::post('/medical-notes/{noteId}/report', [MedicalReportsController::class, 'store'])
            ->name('medical-notes.report.store');

        Route::get('/medical-notes/{noteId}/report/review', [MedicalReportsController::class, 'review'])
            ->name('medical-notes.report.review');

        Route::post('/medical-notes/{noteId}/report/approve', [MedicalReportsController::class, 'approve'])
            ->name('medical-notes.report.approve');
    });

// Patient route
//? singleton() => generates exactly show, edit, and update
Route::middleware(['auth', 'patientOnly'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function () {
        Route::singleton('/profile', PatientProfileController::class);

        Route::get('/reports/{reportId}', [MedicalReportsController::class, 'show'])
            ->name('reports.show');
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
