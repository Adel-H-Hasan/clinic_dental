<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $patient = $request->user();

        return view('patient.profile.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $patient = $request->user();

        return view('patient.profile.edit', compact('patient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = $request->user();
    
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', 'in:male,female']
        ]);

        $user->fill($data);
        $emailChanged = $user->isDirty('email');
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();
        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::warning('Verification delivery failed', ['exception_type' => get_class($exception)]);
                return redirect()->route('verification.notice')->withErrors([
                    'email' => __('Your email was updated, but the verification message could not be sent. Please request another link.'),
                ]);
            }
            return redirect()->route('verification.notice');
        }

        return redirect()->route('patient.profile.show')->with('success', 'Patient updated successfully');
    }
}
