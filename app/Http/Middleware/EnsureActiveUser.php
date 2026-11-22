<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && (! $user->is_active || $user->trashed())) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => __('Your account is no longer active.')], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => __('Your account is no longer active.'),
            ]);
        }

        return $next($request);
    }
}
