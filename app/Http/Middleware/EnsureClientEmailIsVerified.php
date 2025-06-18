<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureClientEmailIsVerified
{
    public function handle($request, Closure $next)
    {
        $client = Auth::guard('client')->user();

        if ($client && ! $client->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
