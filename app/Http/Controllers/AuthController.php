<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function login(Request $request) {
        if ($request->isMethod('get'))
            return view ('auth.login');

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string']
        ]);

        // Laravel looks for an account with that email and is_active = true
        $data['is_active'] = true;

        if (! Auth::attempt($data))
            return back ()
        ->withErrors(['email', 'Unable to sign in with these credentials.'])
        ->onlyInput('email');

        Auth::attempt($data);
        return redirect()->intended('/');
    }

    public function register(Request $request) {
        if ($request->isMethod('get'))
            return view ('auth.register');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today']
        ]);

        $user = User::create ($data);

        event(new Registered($user));

        Auth::login($user);

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
