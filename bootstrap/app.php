<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            if ($user->role === 'patient' && !$user->hasVerifiedEmail()) {
                return route('verification.notice');
            }
            return route(match ($user->role) {
                'admin' => 'admin.dashboard',
                'doctor' => 'doctor.dashboard',
                default => 'patient.dashboard',
            });
        });

        // Language middleware - Abdullah
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            // Immediately revoke access to sessions belonging to disabled accounts.
            \App\Http\Middleware\EnsureActiveUser::class,
        ]);

        // Role middleware - Team
        $middleware->alias([
            'adminOnly' => \App\Http\Middleware\AdminOnly::class,
            'doctorOnly' => \App\Http\Middleware\DoctorOnly::class,
            'patientOnly' => \App\Http\Middleware\PatientOnly::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();