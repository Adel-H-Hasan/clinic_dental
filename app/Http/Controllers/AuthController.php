<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('get'))
            return view('auth.login');

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string']
        ]);

        // Laravel looks for an account with that email and is_active = true
        $data['is_active'] = true;

        $signedIn = Auth::attemptWhen($data, function (User $user) {
            if ($user->role === 'patient') {
                return $user->hasVerifiedEmail();
            }

            return true;
        }, $request->boolean('remember'));

        if (! $signedIn) {
            return back()
                ->withErrors(['email' => 'Unable to sign in. If you registered as a patient, verify your email first.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $fallback = match ($request->user()->role) {
            'patient' => route('patient.dashboard'),
            'doctor' => route('doctor.dashboard'),
            'admin' => route('admin.dashboard'),
        };

        // Retain only same-origin destinations within the authenticated role.
        $intended = $request->session()->get('url.intended');
        $prefix = $request->getSchemeAndHttpHost().'/'.$request->user()->role.'/';
        if (!is_string($intended) || !str_starts_with($intended, $prefix) || str_contains($intended, '/..')) {
            $request->session()->forget('url.intended');
        }
        return redirect()->intended($fallback);
    }

    public function register(Request $request)
    {
        if ($request->isMethod('get'))
            return view('auth.register');

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/\A(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\p{P}\p{S}]).*\z/us',
                'confirmed'
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/\A\+[1-9][0-9]{1,14}\z/'
            ],
            'gender' => [
                'nullable',
                'in:male,female'
            ],
            'date_of_birth' => [
                'nullable',
                'date',
                'before_or_equal:today'
            ]
        ]);

        $user = User::create($data);

        try {
            event(new Registered($user));
        } catch (\Throwable $exception) {
            // Never log the transport message: it can contain recipient information.
            \Illuminate\Support\Facades\Log::warning('Verification delivery failed', ['exception_type' => get_class($exception)]);
            return redirect()->route('verification.notice')->withErrors([
                'email' => __('Your account was created, but the verification email could not be sent. Please request another link.'),
            ]);
        }

        return redirect()->route('verification.notice');
    }


    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email']
        ]);

        Password::sendResetLink($data);

        return back()->with('status', 'If an account exists for this email, you will receive a password reset link.');
    }

    public function showResetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email')
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/\A(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\p{P}\p{S}]).*\z/us',
                'confirmed'
            ]
        ]);

        //* Laravel checks the reset link.
        // It finds the user by the email in $data and checks that the token matches and hasn’t expired.
        // If the check fails, Laravel skips the callback and returns a failure status.
        //* Old 'Remember me' cookies can no longer sign the user in. This is separate from the reset token in the email.
        //event(new PasswordReset($user)) ===> announces that the reset succeeded so any registered listeners can react.
        $status = Password::reset($data, function (User $user, string $password){
            // laravel found the user from $data
            $user->password = $password;
            $user->setRememberToken(Str::random(64));
            $user->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PasswordReset)
            return redirect()->route('login')
            ->with('status', 'Your password has been saved. You can now log in.');

        return back()->withErrors([
            'email' => 'This password reset link is invalid or has expired. Please request a new one.',
        ]);
    }

    public function logout(Request $request)
    {
        $locale = app()->getLocale();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerate();
        $request->session()->put('locale', $locale);

        return redirect('/');
    }
}
