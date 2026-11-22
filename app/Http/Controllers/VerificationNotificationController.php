<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

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
            } catch (\Throwable $exception) {
                Log::warning('Verification resend failed', ['exception_type' => get_class($exception)]);
            }
        }
        // Identical response for unknown, verified, throttled, and eligible addresses.
        return back()->with('status', $status);
    }
}
