<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

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
        });

        if (! $signedIn) {
            return back()
                ->withErrors(['email' => 'Unable to sign in. If you registered as a patient, verify your email first.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $fallback = match ($request->user()->role) {
            'patient' => route('patient.profile.index'),
            'doctor' => route('doctor.dashboard'),
            'admin' => route('admin.dashboard'),
        };

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

        event(new Registered($user));

        return redirect()->route('verification.notice');;
    }


    public function sendResetLink(Request $request) {}

    public function showResetPasswordForm() {}

    public function resetPassword() {}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerate();

        return redirect('/');
    }
}
