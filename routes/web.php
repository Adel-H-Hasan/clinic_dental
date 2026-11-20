<?php

use App\Http\Controllers\Admin\SpecialitiesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\DoctorDashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/specialities',[SpecialitiesController::class,'index'])
->name('admin.specialities.index');

Route::get('/admin/specialities/create',[SpecialitiesController::class,'create'])
->name('admin.specialities.create');

Route::post('/admin/specialities',[SpecialitiesController::class,'store'])
->name('admin.specialities.store');

Route::get('/admin/specialities/{id}',[SpecialitiesController::class,'edit'])
->name('admin.specialities.edit');

Route::put('admin/specialities/{id}',[SpecialitiesController::class,'update'])
->name('admin.specialities.update');

Route::delete('/admin/specialities/{id}',[SpecialitiesController::class,'destroy'])
->name('admin.specialities.destroy');

Route::get('admin/dashboard',[DashboardController::class,'index'])
->name('admin.dashboard');

Route::get('doctor/dashboard',[DoctorDashboardController::class,'index'])
->name('doctor.dashboard');