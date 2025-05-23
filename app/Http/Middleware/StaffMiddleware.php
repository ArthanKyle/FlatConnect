<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Auth::user() instanceof \App\Models\Staff) {
            return redirect()->route('login'); // Redirect unauthorized users
        }

        return $next($request);
    }
}
