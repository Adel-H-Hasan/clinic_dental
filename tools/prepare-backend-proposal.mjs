import fs from 'node:fs';
import {spawnSync} from 'node:child_process';
const root='storage/app/stabilization-20261121/proposed';
fs.mkdirSync(root+'/app/Http/Controllers',{recursive:true});fs.mkdirSync(root+'/routes',{recursive:true});fs.mkdirSync(root+'/bootstrap',{recursive:true});
let auth=fs.readFileSync('app/Http/Controllers/AuthController.php','utf8');
auth=auth.replace('        return redirect()->intended($fallback);',`        // Retain only same-origin destinations within the authenticated role.
        $intended = $request->session()->get('url.intended');
        $prefix = $request->getSchemeAndHttpHost().'/'.$request->user()->role.'/';
        if (!is_string($intended) || !str_starts_with($intended, $prefix) || str_contains($intended, '/..')) {
            $request->session()->forget('url.intended');
        }
        return redirect()->intended($fallback);`);
auth=auth.replace('        event(new Registered($user));',`        try {
            event(new Registered($user));
        } catch (\\Throwable $exception) {
            // Never log the transport message: it can contain recipient information.
            \\Illuminate\\Support\\Facades\\Log::warning('Verification delivery failed', ['exception_type' => get_class($exception)]);
            return redirect()->route('verification.notice')->withErrors([
                'email' => __('Your account was created, but the verification email could not be sent. Please request another link.'),
            ]);
        }`);
auth=auth.replace("return redirect()->route('verification.notice');;", "return redirect()->route('verification.notice');");
fs.writeFileSync(root+'/app/Http/Controllers/AuthController.php',auth);
fs.writeFileSync(root+'/app/Http/Controllers/VerificationNotificationController.php',`<?php

namespace App\\Http\\Controllers;

use App\\Models\\User;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Log;
use Illuminate\\Support\\Facades\\RateLimiter;

class VerificationNotificationController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $key = 'verification-resend:'.hash('sha256', strtolower($data['email']));
        $status = __('If an eligible account exists, a verification link will be sent. Check your inbox or try again later.');
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->with('status', $status);
        }
        RateLimiter::hit($key, 600);
        $user = User::where('email', $data['email'])->where('role', 'patient')->where('is_active', true)->first();
        if ($user && !$user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\\Throwable $exception) {
                Log::warning('Verification resend failed', ['exception_type' => get_class($exception)]);
            }
        }
        // Identical response for unknown, verified, throttled, and eligible addresses.
        return back()->with('status', $status);
    }
}
`);
let routes=fs.readFileSync('routes/web.php','utf8');
routes=routes.replace("        Route::view('email/verify', 'auth.verify-email')->name('verification.notice');",'');
routes=routes.replace("Route::get('email/verify/{id}/{hash}'",`Route::view('email/verify', 'auth.verify-email')->name('verification.notice');
Route::post('email/verification-notification', \\App\\Http\\Controllers\\VerificationNotificationController::class)
    ->middleware('throttle:6,1')->name('verification.send');

Route::get('email/verify/{id}/{hash}'`);
routes=routes.replace("})->middleware('signed')->name('verification.verify');;", "})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');");
fs.writeFileSync(root+'/routes/web.php',routes);
let bootstrap=fs.readFileSync('bootstrap/app.php','utf8');
bootstrap=bootstrap.replace('// Language middleware - Abdullah',`$middleware->redirectUsersTo(function (\\Illuminate\\Http\\Request $request) {
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

        // Language middleware - Abdullah`);
fs.writeFileSync(root+'/bootstrap/app.php',bootstrap);
let patch='';
for(const file of ['app/Http/Controllers/AuthController.php','app/Http/Controllers/VerificationNotificationController.php','routes/web.php','bootstrap/app.php']) {
 const result=spawnSync('git',['diff','--no-index','--',fs.existsSync(file)?file:'NUL',root+'/'+file],{encoding:'utf8'});
 patch+=result.stdout;
}
fs.writeFileSync('docs/taqweem/BACKEND_PENDING.diff',patch);
