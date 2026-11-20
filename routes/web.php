<?php
use App\Http\Controllers\AppointmentTypesController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('appointment-types', AppointmentTypesController::class)
    ->only(['index', 'store', 'update', 'destroy']);