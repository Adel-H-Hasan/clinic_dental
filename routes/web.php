<?php

use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\AppointmentTypesController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SpecialitiesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorAppointmentTypesController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\DoctorSchedulesController;
use App\Http\Controllers\MedicalNotesController;
use App\Http\Controllers\MedicalReportsController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DoctorDashboardController;
// use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;

// Number of middlewares used => 7

Route::get('/', function () {
    return view('welcome');
});

// Admin doctors CRUD routes
Route::middleware(['auth', 'adminOnly'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/specialities', [SpecialitiesController::class, 'index'])->name('specialities.index');
        Route::get('/specialities/create', [SpecialitiesController::class, 'create'])->name('specialities.create');
        Route::post('/specialities', [SpecialitiesController::class, 'store'])->name('specialities.store');
        Route::get('/specialities/{id}', [SpecialitiesController::class, 'edit'])->name('specialities.edit');
        Route::put('/specialities/{id}', [SpecialitiesController::class, 'update'])->name('specialities.update');
        Route::delete('/specialities/{id}', [SpecialitiesController::class, 'destroy'])->name('specialities.destroy');

        Route::resource('/doctors', DoctorController::class);

        Route::resource('/users', UserController::class)
            ->only('index', 'show', 'edit', 'update');

        Route::resource('appointment-types', AppointmentTypesController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

Route::middleware(['auth', 'doctorOnly'])
    ->prefix('doctor')
    ->name('doctor.')
    ->group(function () {
        Route::get('/dashboard', [DoctorDashboardController::class,'index'])->name('dashboard');

        //? singleton() => generates exactly [show, edit, and update]
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

        Route::patch('/appointments/{id}/status', [AppointmentsController::class, 'updateStatus'])
            ->name('appointments.status');

        Route::get('/appointments', [AppointmentsController::class, 'doctorIndex'])
            ->name('appointments.index');
    });

// Patient route
//? verified => user can't go to their pages after register until they verify email
Route::middleware(['auth', 'patientOnly', 'verified'])
    ->prefix('patient')
    ->name('patient.')
    ->group(function () {
        Route::singleton('/profile', PatientProfileController::class);

        Route::get('/reports/{reportId}', [MedicalReportsController::class, 'show'])
            ->name('reports.show');

        Route::get('/appointments', [AppointmentsController::class, 'index'])
            ->name('appointments.index');

        Route::post('/appointments', [AppointmentsController::class, 'store'])
            ->name('appointments.store');

        Route::patch('/appointments/{id}/cancel', [AppointmentsController::class, 'cancel'])
            ->whereNumber('id')
            ->name('appointments.cancel');

        Route::patch('/appointments/{id}/reschedule', [AppointmentsController::class, 'reschedule'])
            ->whereNumber('id')
            ->name('appointments.reschedule');

        Route::get('/appointments/{id}', [AppointmentsController::class, 'show'])
            ->whereNumber('id')
            ->name('appointments.show');
    });

// Auth routes
Route::middleware('guest')
    ->group(function () {
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

        Route::view('email/verify', 'auth.verify-email')->name('verification.notice');
    });

Route::get('email/verify/{id}/{hash}', function (string $id, string $hash) {
    $user = User::findOrFail ($id);

     $expectedHash = sha1($user->getEmailForVerification());
     abort_unless(hash_equals($expectedHash, $hash), 403);

     if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
        event(new Verified($user));
    }

    return redirect()->route('login')
        ->with('status', 'Your email is verified. You can now log in.');
    // Signed: Laravel’s built-in middleware 
    //for checking that the link Laravel generated hasn’t been changed or expired.
})->middleware('signed')->name('verification.verify');;



// When a patient registers, the Registered event triggers the verification email.
// Laravel uses the verification.verify route to generate the signed link in that email.
// When the patient opens the link, this route verifies their email and redirects them to login.

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout.submit');
});


// MedAssist AI Smile Simulation - Abdullah

Route::post('/smile-simulation/upload', [
    \App\Http\Controllers\AiImageAnalysesController::class,
    'store'
])->middleware('auth')->name('smile.upload');

Route::get('/smile-simulation', function () {
    return redirect()->route('smile.ai.create');
})->name('smile.create');

// Home page
Route::get('/home', function () {
    return view('home');
})->name('home');

// Contact page
Route::view('/contact', 'contact')->name('contact');

// Language switcher
Route::get('/language/{locale}', function (string $locale) {
    if (!in_array($locale, ['ar', 'en'], true)) {
        abort(404);
    }

    session(['locale' => $locale]);

    return redirect()->route('home');
})->name('language.switch');

// Smile AI routes
require __DIR__.'/smile-ai.routes.php';