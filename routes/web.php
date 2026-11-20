<?php

use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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

Route::get('email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    //? mark this user’s email as verified.
    // Sets their email_verified_at from null to the current date and time.
    // If the email is already verified, it keeps the original timestamp.
    $request->fulfill();

    return redirect()->intended(route('patient.profile.show'));
    
    // Laravel’s built-in middleware for checking that the link Laravel generated hasn’t been changed or expired.
})->middleware(['auth', 'signed'])->name('verification.verify');;

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout.submit');
    
    Route::view ('email/verify', 'auth.verify-email')->name('verification.notice');
});

// Add a route for the verification link
// User model now implements //? MustVerifyEmail,
// register() already calls //? event(new Registered($user)).
// Laravel needs a route named //? verification.
// verify to build the link it puts in the email.
